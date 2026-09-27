<?php

namespace App\Services;

use App\Enums\AssignmentType;
use App\Models\ProjectAssignment;
use App\Models\ProjectSubmission;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectGalleryService
{
    /**
     * Unified entry point to validate and store submissions for all assignment types.
     *
     * @throws ValidationException
     */
    public function validateAndStore(
        UploadedFile $file,
        ProjectAssignment $assignment,
        User $student,
        int $slotNumber,
        string $projectTitle
    ): ProjectSubmission {
        $type = $assignment->assignment_type ?? AssignmentType::Web;
        $maxBytes = $assignment->max_file_size_mb * 1024 * 1024;

        // 1. Validasi ukuran
        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'project_file' => 'Ukuran file melebihi batas ' . $assignment->max_file_size_mb . ' MB',
            ]);
        }

        // 2. Validasi ekstensi
        $ext = strtolower($file->getClientOriginalExtension());
        $allowed = $assignment->getEffectiveExtensions();
        if (!empty($allowed) && !in_array($ext, $allowed)) {
            throw ValidationException::withMessages([
                'project_file' => 'Format file tidak diizinkan. Format yang diterima: ' . implode(', ', $allowed),
            ]);
        }

        // 3. Handle per tipe
        return match($type) {
            AssignmentType::Web =>
                $this->handleWebUpload(
                    $file, $assignment, $student,
                    $slotNumber, $projectTitle
                ),
            AssignmentType::Document,
            AssignmentType::Image,
            AssignmentType::General =>
                $this->handleSingleFileUpload(
                    $file, $assignment, $student,
                    $slotNumber, $projectTitle, $ext
                ),
        };
    }

    /**
     * Handle web archive upload (ZIP/RAR extraction & indexing).
     */
    private function handleWebUpload(
        UploadedFile $file,
        ProjectAssignment $assignment,
        User $student,
        int $slotNumber,
        string $projectTitle
    ): ProjectSubmission {
        $result = $this->validateAndExtractWeb($file, $assignment);

        return $this->storeSubmission(
            $result['tmp_path'],
            $assignment,
            $student,
            $slotNumber,
            $projectTitle,
            $file->getClientOriginalName(),
            $file->getSize(),
            $result['meta']
        );
    }

    /**
     * Handle single file upload for Document, Image, and General types.
     */
    private function handleSingleFileUpload(
        UploadedFile $file,
        ProjectAssignment $assignment,
        User $student,
        int $slotNumber,
        string $projectTitle,
        string $ext
    ): ProjectSubmission {
        // 1. Tentukan path tujuan
        $relPath = 'projects/' . $assignment->id . '/' . $student->id . '/' . $slotNumber . '/';
        $destDir = storage_path('app/' . $relPath);

        // 2. Hapus folder lama jika ada (TOTAL)
        if (File::isDirectory($destDir)) {
            File::deleteDirectory($destDir);
        }

        // 3. Buat folder tujuan
        File::makeDirectory($destDir, 0755, true);

        // 4. Simpan file dengan nama 'file.{ext}'
        $filename = 'file.' . $ext;
        $file->move($destDir, $filename);

        // 5. Generate metadata
        $type = $assignment->assignment_type;
        $fileSize = file_exists($destDir . $filename) ? filesize($destDir . $filename) : $file->getSize();
        $meta = [
            'has_css'    => false,
            'has_js'     => false,
            'has_images' => $type === AssignmentType::Image,
            'has_audio'  => false,
            'has_video'  => false,
            'file_ext'   => $ext,
            'file_list'  => [[
                'name' => $filename,
                'size' => $fileSize,
                'ext'  => $ext,
            ]],
        ];

        // 6. Upsert ke DB
        return ProjectSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id'    => $student->id,
                'slot_number'   => $slotNumber,
            ],
            [
                'title'             => $projectTitle,
                'original_filename' => $file->getClientOriginalName(),
                'storage_path'      => $relPath,
                'file_size_bytes'   => $fileSize,
                'has_css'           => false,
                'has_js'            => false,
                'has_images'        => $meta['has_images'],
                'has_audio'         => false,
                'has_video'         => false,
                'uploaded_at'       => now(),
            ]
        );
    }

    /**
     * Validate uploaded web project archive file, extract to temporary directory,
     * check for index.html, and scan metadata.
     *
     * @throws ValidationException
     */
    public function validateAndExtractWeb(UploadedFile $file, ProjectAssignment $assignment): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['zip', 'rar'])) {
            throw ValidationException::withMessages([
                'project_file' => 'File harus berformat .zip atau .rar',
            ]);
        }

        $maxBytes = $assignment->max_file_size_mb * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'project_file' => "Ukuran file melebihi batas {$assignment->max_file_size_mb} MB",
            ]);
        }

        $tmpPath = storage_path('app/tmp/' . uniqid('proj_', true));
        File::makeDirectory($tmpPath, 0755, true);

        if ($extension === 'zip') {
            if (!class_exists('ZipArchive')) {
                File::deleteDirectory($tmpPath);
                throw ValidationException::withMessages([
                    'project_file' => 'Ekstensi PHP ZipArchive belum aktif di server hosting ini. Silakan aktifkan ekstensi "zip" di menu PHP Extensions pada cPanel hosting Anda.',
                ]);
            }
            $zip = new \ZipArchive();
            $opened = $zip->open($file->getPathname());
            if ($opened !== true) {
                File::deleteDirectory($tmpPath);
                throw ValidationException::withMessages([
                    'project_file' => 'File ZIP tidak valid atau rusak.',
                ]);
            }
            $zip->extractTo($tmpPath);
            $zip->close();
        } elseif ($extension === 'rar') {
            if (!class_exists('RarArchive')) {
                File::deleteDirectory($tmpPath);
                throw ValidationException::withMessages([
                    'project_file' => 'Format RAR tidak didukung di server ini. Silakan gunakan format .zip.',
                ]);
            }
            $rar = \RarArchive::open($file->getPathname());
            if ($rar === false) {
                File::deleteDirectory($tmpPath);
                throw ValidationException::withMessages([
                    'project_file' => 'File RAR tidak valid atau rusak.',
                ]);
            }
            $entries = $rar->getEntries();
            foreach ($entries as $entry) {
                $entry->extract($tmpPath);
            }
            $rar->close();
        }

        // Check for index.html in root or 1 level subfolder
        $indexPath = $tmpPath . '/index.html';
        if (!file_exists($indexPath)) {
            $dirs = glob($tmpPath . '/*', GLOB_ONLYDIR);
            if (count($dirs) === 1) {
                $subDir = $dirs[0];
                if (file_exists($subDir . '/index.html')) {
                    $tempMove = storage_path('app/tmp/' . uniqid('move_', true));
                    rename($subDir, $tempMove);
                    File::deleteDirectory($tmpPath);
                    rename($tempMove, $tmpPath);
                    $indexPath = $tmpPath . '/index.html';
                }
            }
        }

        if (!file_exists($indexPath)) {
            File::deleteDirectory($tmpPath);
            throw ValidationException::withMessages([
                'project_file' => 'File ZIP harus mengandung index.html di folder utama.',
            ]);
        }

        // Scan content for metadata
        $files = File::allFiles($tmpPath);
        $extensions = collect($files)->map(fn($f) => strtolower($f->getExtension()))->toArray();

        $meta = [
            'has_css'    => in_array('css', $extensions),
            'has_js'     => in_array('js', $extensions),
            'has_images' => !empty(array_intersect(['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'], $extensions)),
            'has_audio'  => !empty(array_intersect(['mp3', 'wav', 'ogg'], $extensions)),
            'has_video'  => !empty(array_intersect(['mp4', 'webm', 'ogv'], $extensions)),
            'file_list'  => collect($files)->map(fn($f) => [
                'name' => str_replace('\\', '/', $f->getRelativePathname()),
                'size' => $f->getSize(),
                'ext'  => strtolower($f->getExtension()),
            ])->values()->toArray(),
            'total_files' => count($files),
        ];

        return [
            'tmp_path' => $tmpPath,
            'meta'     => $meta,
            'valid'    => true,
        ];
    }

    /**
     * Store submission permanently into storage/app/projects/ and upsert database record.
     */
    public function storeSubmission(
        string $tmpPath,
        ProjectAssignment $assignment,
        User $student,
        int $slotNumber,
        string $projectTitle,
        string $originalFilename,
        int $fileSizeBytes,
        array $meta
    ): ProjectSubmission {
        $relPath = 'projects/' . $assignment->id . '/' . $student->id . '/' . $slotNumber . '/';
        $destPath = storage_path('app/' . $relPath);

        // Delete old submission folder completely
        if (File::isDirectory($destPath)) {
            File::deleteDirectory($destPath);
        }

        File::makeDirectory($destPath, 0755, true);

        // Copy files from tmp to dest
        File::copyDirectory($tmpPath, $destPath);
        File::deleteDirectory($tmpPath);

        return ProjectSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id'    => $student->id,
                'slot_number'   => $slotNumber,
            ],
            [
                'title'             => $projectTitle,
                'original_filename' => $originalFilename,
                'storage_path'      => $relPath,
                'file_size_bytes'   => $fileSizeBytes,
                'has_css'           => (bool) ($meta['has_css'] ?? false),
                'has_js'            => (bool) ($meta['has_js'] ?? false),
                'has_images'        => (bool) ($meta['has_images'] ?? false),
                'has_audio'         => (bool) ($meta['has_audio'] ?? false),
                'has_video'         => (bool) ($meta['has_video'] ?? false),
                'uploaded_at'       => now(),
            ]
        );
    }

    /**
     * Serve project files safely with path traversal protection and secure headers.
     */
    public function serveFile(ProjectSubmission $submission, string $filePath = ''): BinaryFileResponse
    {
        if (empty($filePath)) {
            $filePath = $this->getMainFilename($submission);
        }

        $filePath = ltrim($filePath, '/');
        $filePath = preg_replace('/\.\.+/', '', $filePath);
        $filePath = preg_replace('/[^a-zA-Z0-9\/_\-\.]/', '', $filePath);

        if (empty($filePath)) {
            $filePath = $this->getMainFilename($submission);
        }

        $baseDir = storage_path('app/' . rtrim($submission->storage_path, '/') . '/');
        $fullPath = $baseDir . $filePath;

        $realBase = realpath($baseDir);
        $realPath = realpath($fullPath);

        if (!$realPath || !$realBase || !str_starts_with($realPath, $realBase)) {
            abort(404, 'File tidak ditemukan.');
        }

        if (!file_exists($realPath) || is_dir($realPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'html'  => 'text/html; charset=UTF-8',
            'htm'   => 'text/html; charset=UTF-8',
            'css'   => 'text/css; charset=UTF-8',
            'js'    => 'application/javascript; charset=UTF-8',
            'json'  => 'application/json',
            'pdf'   => 'application/pdf',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'webp'  => 'image/webp',
            'svg'   => 'image/svg+xml',
            'ico'   => 'image/x-icon',
            'mp3'   => 'audio/mpeg',
            'wav'   => 'audio/wav',
            'ogg'   => 'audio/ogg',
            'mp4'   => 'video/mp4',
            'webm'  => 'video/webm',
            'ogv'   => 'video/ogg',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'   => 'application/msword',
            'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ppt'   => 'application/vnd.ms-powerpoint',
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'   => 'application/vnd.ms-excel',
            'zip'   => 'application/zip',
            'rar'   => 'application/x-rar-compressed',
        ];

        $mime = $mimeTypes[$ext] ?? (mime_content_type($realPath) ?: 'application/octet-stream');

        $type = $submission->assignment->assignment_type ?? AssignmentType::Web;

        $headers = ['Content-Type' => $mime];

        if ($type === AssignmentType::Web) {
            $headers['X-Frame-Options'] = 'SAMEORIGIN';
            $headers['Content-Security-Policy'] = "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:; img-src 'self' data: https:; media-src 'self' data: https:; font-src 'self' data: https:;";
        } elseif ($type === AssignmentType::Document) {
            if ($ext === 'pdf') {
                $headers['Content-Disposition'] = 'inline';
            } else {
                $headers['Content-Disposition'] = 'attachment; filename="' . $submission->original_filename . '"';
            }
        } elseif ($type === AssignmentType::Image) {
            $headers['Content-Disposition'] = 'inline';
        } else {
            // General
            $headers['Content-Disposition'] = 'attachment; filename="' . $submission->original_filename . '"';
        }

        return response()->file($realPath, $headers);
    }

    /**
     * Get the main filename to serve for a submission.
     */
    public function getMainFilename(ProjectSubmission $submission): string
    {
        $type = $submission->assignment->assignment_type ?? AssignmentType::Web;

        if ($type === AssignmentType::Web) {
            return 'index.html';
        }

        $dir = storage_path('app/' . rtrim($submission->storage_path, '/') . '/');
        $files = glob($dir . 'file.*');

        if (!empty($files)) {
            return basename($files[0]);
        }

        return 'file';
    }

    /**
     * Delete a project submission and completely remove its extracted files from storage.
     */
    public function deleteSubmission(ProjectSubmission $submission): bool
    {
        $dirPath = storage_path('app/' . rtrim($submission->storage_path, '/'));
        if (File::isDirectory($dirPath)) {
            File::deleteDirectory($dirPath);
        }

        return (bool) $submission->delete();
    }

    /**
     * Create a backup archive ZIP of all student submissions for an assignment.
     */
    public function createArchiveZip(ProjectAssignment $assignment): ?string
    {
        $submissions = $assignment->submissions()
            ->with('student.classroom')
            ->orderBy('slot_number')
            ->get();

        if ($submissions->isEmpty()) {
            return null;
        }

        $tmpDir = storage_path('app/tmp');
        if (!File::isDirectory($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }

        $zipFilename = 'arsip_' . $assignment->slug . '_' . date('Ymd_His') . '_' . uniqid() . '.zip';
        $zipPath = $tmpDir . '/' . $zipFilename;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($submissions as $submission) {
            $student = $submission->student;
            $className = $student?->classroom?->name ?? 'Tanpa_Kelas';
            $nis = $student?->nis ?? 'NoNIS';
            $studentName = $student?->name ?? 'Siswa';
            $slot = $submission->slot_number;

            // Sanitize folder name for ZIP entry
            $rawFolderName = "{$className}_{$nis}_{$studentName}_Slot{$slot}";
            $safeFolderName = preg_replace('/[\\\\\/:\*\?"<>\|]/', '_', $rawFolderName);
            $safeFolderName = trim(preg_replace('/\s+/', ' ', $safeFolderName));

            $submissionPath = storage_path('app/' . rtrim($submission->storage_path, '/'));

            if (File::isDirectory($submissionPath)) {
                $zip->addEmptyDir($safeFolderName);

                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($submissionPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($files as $file) {
                    $filePathname = $file->getPathname();
                    $rel = substr($filePathname, strlen($submissionPath) + 1);
                    $rel = str_replace('\\', '/', $rel);
                    $zipEntry = $safeFolderName . '/' . $rel;

                    if ($file->isDir()) {
                        $zip->addEmptyDir($zipEntry);
                    } elseif ($file->isFile()) {
                        $zip->addFile($filePathname, $zipEntry);
                    }
                }
            }
        }

        $zip->close();

        return file_exists($zipPath) ? $zipPath : null;
    }

    /**
     * Clear all submissions for an assignment, deleting physical storage files and DB records.
     */
    public function clearAllSubmissions(ProjectAssignment $assignment): int
    {
        $assignmentDir = storage_path("app/projects/{$assignment->id}");
        if (File::isDirectory($assignmentDir)) {
            File::deleteDirectory($assignmentDir);
        }

        return (int) $assignment->submissions()->delete();
    }
}
