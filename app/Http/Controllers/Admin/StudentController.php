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
                        $newPassword = \Illuminate\Support\Str::random(8);
                        
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
     * Delete all students
     */
    public function deleteAllStudents()
    {
        try {
            $students = User::where('role', 'student')->get();
            $count = $students->count();

            foreach ($students as $student) {
                $student->delete();
            }

            return redirect()->route('admin.students.index')
                ->with('success', "All {$count} students have been permanently deleted.");
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Error deleting students: ' . $e->getMessage());
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
        
        if ($zip->open($zipFile->getRealPath()) === TRUE) {
            $storagePath = storage_path('app/public/profiles');
            
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $successCount = 0;
            $errorCount = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileinfo = pathinfo($filename);

                // Skip directories and non-image files
                if (str_ends_with($filename, '/') || !isset($fileinfo['extension'])) {
                    continue;
                }

                $extension = strtolower($fileinfo['extension']);
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    continue;
                }

                // Extract content
                $content = $zip->getFromIndex($i);
                $image = @imagecreatefromstring($content);

                if ($image) {
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $maxSize = 400;

                    // Resize if larger than maxSize
                    if ($width > $maxSize || $height > $maxSize) {
                        if ($width > $height) {
                            $newWidth = $maxSize;
                            $newHeight = intval($height * ($maxSize / $width));
                        } else {
                            $newHeight = $maxSize;
                            $newWidth = intval($width * ($maxSize / $height));
                        }

                        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                        
                        // Preserve transparency for PNG
                        if ($extension === 'png') {
                            imagealphablending($resizedImage, false);
                            imagesavealpha($resizedImage, true);
                        }

                        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagedestroy($image);
                        $image = $resizedImage;
                    }

                    // Save as JPG with 80% quality (or original extension if preferred, but JPG is consistent)
                    $targetFilename = $fileinfo['basename']; // Exact name from ZIP
                    $targetPath = $storagePath . '/' . $targetFilename;
                    
                    $saved = false;
                    if ($extension === 'png') {
                        $saved = imagepng($image, $targetPath, 8); // Compression level 8
                    } elseif ($extension === 'webp') {
                        $saved = imagewebp($image, $targetPath, 80);
                    } else {
                        $saved = imagejpeg($image, $targetPath, 80);
                    }

                    if ($saved) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }

                    imagedestroy($image);
                } else {
                    $errorCount++;
                }
            }

            $zip->close();

            return redirect()->back()->with('success', "Berhasil memproses {$successCount} foto. " . ($errorCount > 0 ? "Gagal memproses {$errorCount} file." : ""));
        } else {
            return redirect()->back()->with('error', 'Gagal membuka file ZIP.');
        }
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
