<?php

namespace App\Services;

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
     * Validate uploaded zip/rar file, extract to temporary directory,
     * check for index.html, and scan metadata.
     *
     * @throws ValidationException
     */
    public function validateAndExtract(UploadedFile $file, ProjectAssignment $assignment): array
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
    public function serveFile(ProjectSubmission $submission, string $filePath = 'index.html'): BinaryFileResponse
    {
        $filePath = ltrim($filePath, '/');
        $filePath = preg_replace('/\.\.+/', '', $filePath);
        $filePath = preg_replace('/[^a-zA-Z0-9\/_\-\.]/', '', $filePath);

        if (empty($filePath)) {
            $filePath = 'index.html';
        }

        $baseDir = storage_path('app/' . rtrim($submission->storage_path, '/') . '/');
        $fullPath = $baseDir . $filePath;

        $realBase = realpath($baseDir);
        $realPath = realpath($fullPath);

        if (!$realPath || !$realBase || !str_starts_with($realPath, $realBase) || !file_exists($realPath) || is_dir($realPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'html'  => 'text/html; charset=UTF-8',
            'htm'   => 'text/html; charset=UTF-8',
            'css'   => 'text/css; charset=UTF-8',
            'js'    => 'application/javascript; charset=UTF-8',
            'json'  => 'application/json',
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
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'otf'   => 'font/otf',
        ];

        $mime = $mimeTypes[$ext] ?? (mime_content_type($realPath) ?: 'application/octet-stream');

        $headers = [
            'Content-Type'            => $mime,
            'X-Frame-Options'         => 'SAMEORIGIN',
            'Content-Security-Policy' => "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:; img-src 'self' data: https:; media-src 'self' data: https:; font-src 'self' data: https:;",
        ];

        return response()->file($realPath, $headers);
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
}

