<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentExport;
use App\Exports\RemappingTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStudentRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Imports\StudentImport;
use App\Imports\RemappingImport;
use App\Models\ClassRoom;
use App\Models\MigrationLog;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    use AuthorizesRequests;
    /**
     * Reset all student passwords
     */
    public function resetAllPasswords()
    {
        try {
            $startTime = microtime(true);
            
            // Safety Limits: No hashing means we can handle much larger chunks
            set_time_limit(600);
            ini_set('memory_limit', '512M');

            $count = 0;
            
            // Fast Reset: Skip Hash::make() in the loop for extreme speed
            User::where('role', 'student')->select('id')->chunkById(500, function ($students) use (&$count) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($students, &$count) {
                    foreach ($students as $student) {
                        $newPassword = 'pass123';
                        
                        // Direct update with PLAIN_ prefix (No-Hash)
                        \Illuminate\Support\Facades\DB::table('users')->where('id', $student->id)->update([
                            'password' => 'PLAIN_' . $newPassword,
                            'password_display' => $newPassword,
                            'updated_at' => now(),
                        ]);
                        
                        $count++;
                    }
                });
            });

            $duration = round(microtime(true) - $startTime, 2);

            return redirect()->route('admin.students.index')
                ->with('success', "Reset password massal selesai secara instan dalam {$duration} detik untuk {$count} siswa. Password akan otomatis diamankan saat siswa login.");
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Error resetting passwords: ' . $e->getMessage());
        }
    }

    /**
     * Confirmation page before deleting all students
     */
    public function confirmDeleteAllStudents()
    {
        $studentCount = User::where('role', 'student')->count();
        $photoCount = User::where('role', 'student')->whereNotNull('photo')->where('photo', '!=', '')->count();

        $cascadeCounts = [];
        $cascadeWarning = null;

        try {
            $dbName = config('database.connections.mysql.database');
            if (config('database.default') === 'mysql' && $dbName) {
                $fkList = DB::select("
                    SELECT 
                        kcu.TABLE_NAME as table_name,
                        kcu.COLUMN_NAME as column_name
                    FROM information_schema.KEY_COLUMN_USAGE kcu
                    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                        ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                        AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
                    WHERE kcu.REFERENCED_TABLE_NAME = 'users'
                        AND kcu.CONSTRAINT_SCHEMA = ?
                        AND rc.DELETE_RULE = 'CASCADE'
                ", [$dbName]);

                foreach ($fkList as $fk) {
                    $count = DB::table($fk->table_name)->whereIn($fk->column_name, function ($q) {
                        $q->select('id')->from('users')->where('role', 'student');
                    })->count();

                    if ($count > 0) {
                        $cascadeCounts[$fk->table_name] = ($cascadeCounts[$fk->table_name] ?? 0) + $count;
                    }
                }
            }
        } catch (\Throwable $e) {
            $cascadeWarning = 'Tidak dapat memuat rincian relasi data otomatis (' . $e->getMessage() . '). Namun seluruh data terkait siswa tetap akan ikut terhapus.';
        }

        return view('admin.students.delete-all-confirm', compact('studentCount', 'photoCount', 'cascadeCounts', 'cascadeWarning'));
    }

    /**
     * Delete all students
     */
    public function deleteAllStudents(Request $request)
    {
        // Validasi di SERVER
        if ($request->input('confirm_text') !== 'HAPUS SEMUA SISWA' || !$request->boolean('understand')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Konfirmasi tidak sesuai. Harap centang kotak persetujuan dan ketik "HAPUS SEMUA SISWA" dengan benar.');
        }

        // Cek Pemilihan Pilketos yang sedang open
        if (class_exists(\App\Models\ElectionPeriod::class) && \App\Models\ElectionPeriod::where('status', 'open')->exists()) {
            return redirect()->back()
                ->with('error', 'Tindakan ditolak: Pemilihan sedang berlangsung (terdapat periode Pilketos yang berstatus aktif/open).');
        }

        // Cek Ujian yang sedang berlangsung
        $activeExamSession = false;
        if (class_exists(\App\Models\ExamSession::class)) {
            $activeExamSession = \App\Models\ExamSession::where('status', 'active')->exists();
        }
        if ($activeExamSession || (class_exists(\App\Models\ExamAttempt::class) && \App\Models\ExamAttempt::where('status', 'in_progress')->exists())) {
            return redirect()->back()
                ->with('error', 'Tindakan ditolak: Ujian sedang berlangsung (terdapat sesi ujian yang sedang aktif).');
        }

        try {
            $students = User::where('role', 'student')->get();
            $count = $students->count();

            foreach ($students as $student) {
                $student->delete();
            }

            // Catat log aplikasi
            Log::warning('Hapus massal siswa dieksekusi', [
                'admin_id'       => auth()->id(),
                'students_count' => $count,
                'timestamp'      => now()->toIso8601String(),
            ]);

            return redirect()->route('admin.students.index')
                ->with('success', "Semua data ({$count} siswa) beserta berkas foto dan data terkait berhasil dihapus permanen.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Terjadi kesalahan saat menghapus siswa: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        // Default view: only Aktif students (Alumni and Nonaktif have separate pages)
        $statusFilter = $request->input('status', 'Aktif');

        $query = User::where('role', 'student')
            ->where('status', $statusFilter);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('grade', $request->input('grade'));
        }

        // Get grades for filter dropdown (only for current status group)
        $classes = User::where('role', 'student')
            ->where('status', $statusFilter)
            ->distinct()
            ->whereNotNull('grade')
            ->orderBy('grade')
            ->pluck('grade');

        // Status counts for tab badges
        $statusCounts = [
            'Aktif'    => User::where('role', 'student')->where('status', 'Aktif')->count(),
            'Nonaktif' => User::where('role', 'student')->where('status', 'Nonaktif')->count(),
        ];

        // Pagination: Sort by Grade and Class Group
        $students = $query->orderBy('grade')
            ->orderBy('class_group')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Unmapped counter: only Aktif students without class_group need re-mapping
        $unmappedCount = ($statusFilter === 'Aktif')
            ? User::where('role', 'student')->where('status', 'Aktif')->whereNull('class_group')->count()
            : 0;

        return view('admin.students.index', compact('students', 'classes', 'unmappedCount', 'statusFilter', 'statusCounts'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Store a newly created student
     */
    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = $data['nis'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profiles', $filename, 'public');
            $data['photo'] = $filename;
        }

        $result = StudentService::createStudent($data);

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan!')
            ->with('password', $result['password'])
            ->with('nis', $result['student']->nis);
    }

    /**
     * Display the specified student
     */
    public function show(User $student)
    {
        $this->authorize('view', $student);

        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(User $student)
    {
        $this->authorize('update', $student);

        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified student
     */
    public function update(UpdateStudentRequest $request, User $student)
    {
        $this->authorize('update', $student);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($student->photo && Storage::disk('public')->exists('profiles/' . $student->photo)) {
                Storage::disk('public')->delete('profiles/' . $student->photo);
            }

            $file = $request->file('photo');
            $filename = $student->nis . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profiles', $filename, 'public');
            $data['photo'] = $filename;
        }

        // Only update fields that are not email-related
        // Email is auto-generated and shouldn't be updated
        unset($data['email']);

        // Handle manual password update if provided
        if (!empty($data['password'])) {
            $data['password_display'] = $data['password'];
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $student->update($data);

        return redirect()->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui!');
    }

    /**
     * Delete the specified student
     */
    public function destroy(User $student)
    {
        $this->authorize('delete', $student);

        $nis = $student->nis;
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', "Data siswa {$nis} berhasil dihapus!");
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('admin.students.import');
    }

    /**
     * Import students from Excel
     */
    public function import(ImportStudentRequest $request)
    {
        $importer = new StudentImport();
        Excel::import($importer, $request->file('file'));

        return redirect()->route('admin.students.importResult')->with('import_data', [
            'success_count' => $importer->successCount,
            'skipped_count' => $importer->skippedCount,
            'failure_count' => $importer->failureCount,
            'import_errors' => $importer->errors,
            'students' => $importer->students,
            'skipped' => $importer->skipped,
            'duration' => $importer->duration,
        ]);
    }

    /**
     * Show import result
     */
    public function importResult()
    {
        $data = session('import_data');

        if (!$data) {
            return redirect()->route('admin.students.importForm');
        }

        return view('admin.students.import_result', $data);
    }

    /**
     * Export students to Excel
     */
    public function export(Request $request)
    {
        $scope    = $request->input('scope', 'all');
        $filename = $scope === 'unmapped'
            ? 'students-remapping-template-' . date('Y-m-d') . '.xlsx'
            : 'students-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new StudentExport($scope), $filename);
    }

    /**
     * Reset password for a student
     */
    public function resetPassword(User $student)
    {
        $this->authorize('update', $student);

        $newPassword = StudentService::resetPassword($student);

        return back()
            ->with('success', 'Password berhasil diatur ulang!')
            ->with('password', $newPassword)
            ->with('nis', $student->nis);
    }

    /**
     * Toggle student active status
     */
    public function toggleActive(User $student)
    {
        $this->authorize('update', $student);

        $newStatus = $student->status === 'Aktif' ? 'Nonaktif' : 'Aktif';

        $student->update([
            'status' => $newStatus,
        ]);

        return back()->with('success', "Status siswa berhasil " . ($newStatus === 'Aktif' ? 'diaktifkan' : 'dinonaktifkan') . "!");
    }

    /**
     * Show upload photos form.
     */
    public function uploadPhotosForm()
    {
        return view('admin.students.upload-photos');
    }

    /**
     * Normalisasi nama untuk pencocokan foto siswa.
     */
    private function normalizeName(string $s): string
    {
        // Ambil nama tanpa ekstensi jika masih ada ekstensi
        $s = pathinfo($s, PATHINFO_FILENAME);
        // Samakan variasi apostrof (’ ‘ ` ´) -> '
        $s = str_replace(['’', '‘', '`', '´'], "'", $s);
        // NBSP dan spasi unicode -> spasi biasa
        $s = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $s);
        // Trim dan gabungkan spasi berulang
        $s = trim(preg_replace('/\s+/u', ' ', $s));
        // mb_strtolower UTF-8
        $s = mb_strtolower($s, 'UTF-8');
        return $s;
    }

    /**
     * Handle ZIP photo upload and processing.
     */
    public function uploadPhotos(Request $request)
    {
        // Detect if POST data is lost due to post_max_size exceed
        if ($request->isMethod('post') && empty($_POST) && empty($_FILES) && $request->header('Content-Length') > 0) {
            $maxPost = ini_get('post_max_size');
            return redirect()->back()->with('error', "Ukuran file terlalu besar! Server Anda membatasi total unggahan maksimal {$maxPost}. Silakan kompres ZIP Anda atau hubungi admin.");
        }

        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:20480', // Max 20MB
        ]);

        if (!extension_loaded('gd')) {
            return redirect()->back()->with('error', 'Ekstensi PHP GD tidak terpasang. Harap hubungi administrator.');
        }

        $zipFile = $request->file('zip_file');
        $zip = new \ZipArchive();

        if ($zip->open($zipFile->getRealPath()) !== TRUE) {
            return redirect()->back()->with('error', 'Gagal membuka file ZIP.');
        }

        $storageDisk = Storage::disk('public');
        $storageDisk->makeDirectory('profiles');
        $storagePath = $storageDisk->path('profiles');

        // A2. Peta pencarian (bangun SEKALI di awal)
        $students = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->select('id', 'name', 'nis', 'photo', 'class_id')
            ->with('classroom:id,name')
            ->get();

        $byName = $students->groupBy(function ($u) {
            return $this->normalizeName($u->name);
        });

        $byNis = $students->filter(function ($u) {
            return !empty($u->nis);
        })->keyBy(function ($u) {
            return trim((string)$u->nis);
        });

        $successCount = 0;
        $notFoundList = [];
        $ambiguousList = [];
        $failedList = [];
        $skippedList = [];

        $imageEntriesCount = 0;
        $maxImageEntries = 500;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $basename = basename($filename);

            // Lewati folder
            if (str_ends_with($filename, '/')) {
                continue;
            }

            // Lewati entri __MACOSX/ dan basename berawalan ._
            if (str_starts_with($filename, '__MACOSX/') || str_contains($filename, '/__MACOSX/') || str_starts_with($basename, '._')) {
                continue;
            }

            // Cek ekstensi
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            // Batas maksimal 500 gambar per ZIP
            $imageEntriesCount++;
            if ($imageEntriesCount > $maxImageEntries) {
                $skippedList[] = "{$basename} (melebihi batas maksimal {$maxImageEntries} gambar)";
                continue;
            }

            // Lewati entri > 8 MB
            $stat = $zip->statIndex($i);
            $size = $stat['size'] ?? 0;
            if ($size > 8 * 1024 * 1024) {
                $skippedList[] = "{$basename} (ukuran file " . round($size / (1024 * 1024), 2) . " MB melebihi batas 8 MB)";
                continue;
            }

            // Kunci pencarian
            $key = $this->normalizeName($basename);

            $matchedStudent = null;
            if (ctype_digit($key) && $byNis->has($key)) {
                // Cocok via NIS
                $matchedStudent = $byNis->get($key);
            } else {
                // Cocok via Nama
                $candidates = $byName->get($key, collect());
                if ($candidates->count() === 1) {
                    $matchedStudent = $candidates->first();
                } elseif ($candidates->count() === 0) {
                    $notFoundList[] = $basename;
                    continue; // File tidak cocok TIDAK disimpan sama sekali
                } else {
                    // Ambigu (> 1 siswa)
                    $candDetails = $candidates->map(function ($c) {
                        $cls = $c->classroom->name ?? '-';
                        return "{$c->name} (NIS: {$c->nis}, Kelas: {$cls})";
                    })->implode('; ');
                    $ambiguousList[] = "{$basename} -> Kandidat: [{$candDetails}]";
                    continue; // JANGAN menebak, TIDAK disimpan
                }
            }

            if (!$matchedStudent) {
                $notFoundList[] = $basename;
                continue;
            }

            // Baca konten file
            $content = $zip->getFromIndex($i);
            if ($content === false) {
                $failedList[] = "{$basename} (gagal membaca konten berkas dari ZIP)";
                continue;
            }

            // Guard memori (hosting 128MB)
            $imgInfo = @getimagesizefromstring($content);
            if (!$imgInfo || ($imgInfo[0] * $imgInfo[1] > 20000000)) {
                $failedList[] = "{$basename} (gambar terlalu besar / tidak valid)";
                continue;
            }

            $image = @imagecreatefromstring($content);
            if (!$image) {
                $failedList[] = "{$basename} (data gambar tidak valid atau rusak)";
                continue;
            }

            // Resize jika dimensi melebihi 400px
            $width = imagesx($image);
            $height = imagesy($image);
            $maxSize = 400;

            if ($width > $maxSize || $height > $maxSize) {
                if ($width > $height) {
                    $newWidth = $maxSize;
                    $newHeight = intval($height * ($maxSize / $width));
                } else {
                    $newHeight = $maxSize;
                    $newWidth = intval($width * ($maxSize / $height));
                }

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve alpha channel for PNG
                if ($ext === 'png') {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                }

                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resizedImage;
            }

            // Tentukan nama berkas simpanan: "{nis}.{ext}" atau "u{id}.{ext}"
            $saveExt = in_array($ext, ['jpg', 'jpeg']) ? 'jpg' : $ext;
            if (!empty($matchedStudent->nis)) {
                $targetFilename = trim($matchedStudent->nis) . '.' . $saveExt;
            } else {
                $targetFilename = 'u' . $matchedStudent->id . '.' . $saveExt;
            }

            $targetPath = $storagePath . '/' . $targetFilename;

            // Simpan gambar dengan format & kualitas sesuai
            $saved = false;
            if ($saveExt === 'png') {
                $saved = imagepng($image, $targetPath, 8);
            } elseif ($saveExt === 'webp') {
                $saved = imagewebp($image, $targetPath, 80);
            } else {
                $saved = imagejpeg($image, $targetPath, 80);
            }

            imagedestroy($image);

            if ($saved) {
                // Hapus foto lama jika berbeda
                $oldPhoto = $matchedStudent->photo;
                if (!empty($oldPhoto) && $oldPhoto !== $targetFilename) {
                    $isProtected = preg_match('/^\d{9,}_\d+\./', $oldPhoto) || preg_match('/^(teacher|principal|tu|staff)_/i', $oldPhoto);
                    if (!$isProtected) {
                        $isUsedByOther = DB::table('users')
                            ->where('id', '!=', $matchedStudent->id)
                            ->where('photo', $oldPhoto)
                            ->exists();

                        if (!$isUsedByOther && $storageDisk->exists('profiles/' . $oldPhoto)) {
                            $storageDisk->delete('profiles/' . $oldPhoto);
                        }
                    }
                }

                // Update kolom photo di database tanpa memicu event model
                DB::table('users')->where('id', $matchedStudent->id)->update(['photo' => $targetFilename]);
                $matchedStudent->photo = $targetFilename; // Update in-memory
                $successCount++;
            } else {
                $failedList[] = "{$basename} (gagal menyimpan berkas ke folder penyimpanan)";
            }
        }

        $zip->close();

        $report = [
            'berhasil'        => $successCount,
            'tidak_ditemukan' => $notFoundList,
            'ambigu'          => $ambiguousList,
            'gagal'           => $failedList,
            'dilewati'        => $skippedList,
        ];

        return redirect()->back()
            ->with('success', "Proses selesai. Berhasil memperbarui {$successCount} foto siswa.")
            ->with('photo_report', $report);
    }

    // ── Annual Migration Methods ───────────────────────────────────────────────

    /**
     * Show the annual migration management page.
     */
    public function migration()
    {
        // Get active students from Grade 10 and 11 for manual retention selection
        $students = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->whereIn('grade', ['10', '11'])
            ->orderBy('grade', 'desc')
            ->orderBy('name')
            ->get();

        $stats = [
            'total_active' => User::where('role', 'student')->where('status', 'Aktif')->count(),
            'grade_10'     => User::where('role', 'student')->where('status', 'Aktif')->where('grade', '10')->count(),
            'grade_11'     => User::where('role', 'student')->where('status', 'Aktif')->where('grade', '11')->count(),
            'grade_12'     => User::where('role', 'student')->where('status', 'Aktif')->where('grade', '12')->count(),
            'unmapped'     => User::where('role', 'student')->where('status', 'Aktif')->whereNull('class_id')->count(),
        ];

        $recentLogs = MigrationLog::with('executor')
            ->orderByDesc('executed_at')
            ->take(5)
            ->get();

        return view('admin.students.migration', compact('students', 'stats', 'recentLogs'));
    }

    /**
     * Execute the annual migration process.
     */
    public function executeAnnualMigration(Request $request)
    {
        // Validation: Ensure there are active students to migrate
        $activeCount = User::where('role', 'student')->where('status', 'Aktif')->count();
        if ($activeCount === 0) {
            return back()->with('error', 'Tidak ada siswa aktif yang dapat dimigrasi.');
        }

        $retentionIds = (array) $request->input('retention_ids', []);
        $academicYear = date('Y') . '/' . (date('Y') + 1);

        try {
            DB::transaction(function () use ($retentionIds, $academicYear) {
                // 1. GRADUATE GRADE 12: Status -> Alumni, class_id = NULL
                $graduatingCount = User::where('role', 'student')
                    ->where('status', 'Aktif')
                    ->where('grade', '12')
                    ->update([
                        'status'      => 'Alumni',
                        'class_id'    => null,
                        'class_group' => null,
                        'alumni_year' => $academicYear,
                    ]);

                // 2. PROMOTE GRADE 10 & 11 (Normal): Upgrade Grade, Set class_id = NULL
                // Grade 11 -> 12
                $promote11Count = User::where('role', 'student')
                    ->where('status', 'Aktif')
                    ->where('grade', '11')
                    ->whereNotIn('id', $retentionIds)
                    ->update([
                        'grade_level' => 12,
                        'grade'       => '12',
                        'class_id'    => null,
                        'class_group' => null,
                    ]);

                // Grade 10 -> 11
                $promote10Count = User::where('role', 'student')
                    ->where('status', 'Aktif')
                    ->where('grade', '10')
                    ->whereNotIn('id', $retentionIds)
                    ->update([
                        'grade_level' => 11,
                        'grade'       => '11',
                        'class_id'    => null,
                        'class_group' => null,
                    ]);

                // 3. HANDLE RETENTION: Keep Grade, but set class_id = NULL for re-mapping
                $retentionCount = 0;
                if (!empty($retentionIds)) {
                    $retentionCount = User::whereIn('id', $retentionIds)
                        ->where('role', 'student')
                        ->where('status', 'Aktif')
                        ->update([
                            'class_id'    => null,
                            'class_group' => null,
                        ]);
                }

                // 4. LOGGING: Record migration history
                MigrationLog::create([
                    'action_type'    => 'promote',
                    'executed_by'    => Auth::id(),
                    'affected_count' => ($graduatingCount + $promote11Count + $promote10Count + $retentionCount),
                    'academic_year'  => $academicYear,
                    'notes' => [
                        'graduated' => $graduatingCount,
                        'promoted'  => ($promote11Count + $promote10Count),
                        'retained'  => $retentionCount,
                        'retention_ids' => $retentionIds,
                    ],
                    'executed_at' => now(),
                ]);

                // 5. RESET CACHE: Clear relevant caches
                \Illuminate\Support\Facades\Cache::flush();
            });

            return redirect()->route('admin.students.index')
                ->with('success', 'Migrasi tahunan berhasil dieksekusi! Semua siswa telah diproses. Silakan gunakan fitur Ekspor/Impor di Daftar Siswa untuk melakukan pemetaan kelas baru (Re-mapping).');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengeksekusi migrasi: ' . $e->getMessage());
        }
    }

}
