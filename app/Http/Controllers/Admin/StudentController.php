<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStudentRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Imports\StudentImport;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
        $query = User::where('role', 'student');

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

        // Get grades for filter dropdown
        $classes = User::where('role', 'student')
            ->distinct()
            ->whereNotNull('grade')
            ->orderBy('grade')
            ->pluck('grade');

        // Pagination
        $students = $query->orderBy('nis')->paginate(15)->withQueryString();

        return view('admin.students.index', compact('students', 'classes'));
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
            'errors' => $importer->errors,
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
    public function export()
    {
        return Excel::download(new StudentExport(), 'students-' . date('Y-m-d') . '.xlsx');
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

        $student->update([
            'is_active' => !$student->is_active,
        ]);

        $status = $student->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Status siswa berhasil " . ($student->is_active ? 'diaktifkan' : 'dinonaktifkan') . "!");
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
}
