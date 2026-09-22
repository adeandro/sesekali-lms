<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectAssignment;
use App\Models\ProjectSubmission;
use App\Services\ProjectGalleryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProjectSubmissionController extends Controller
{
    public function index()
    {
        $student = auth()->user();

        $assignments = ProjectAssignment::available()
            ->with(['subject', 'submissions' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->latest()
            ->get()
            ->filter(function ($assignment) use ($student) {
                return $assignment->isAvailableFor($student);
            });

        return view('student.projects.index', compact('assignments'));
    }

    public function show(ProjectAssignment $assignment)
    {
        $student = auth()->user();

        if (!$assignment->isAvailableFor($student)) {
            abort(403, 'Tugas project ini tidak tersedia untuk kelas Anda atau waktu pengerjaan telah berakhir.');
        }

        $submissions = $assignment->submissions()
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('slot_number');

        return view('student.projects.show', compact('assignment', 'submissions'));
    }

    public function upload(Request $request, ProjectAssignment $assignment, ProjectGalleryService $galleryService)
    {
        $student = auth()->user();

        if (!$assignment->isAvailableFor($student)) {
            abort(403, 'Tugas project ini tidak tersedia untuk kelas Anda.');
        }

        // 1. Cek jika ukuran data POST melebihi post_max_size server (PHP mengosongkan request body)
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        if ($contentLength > 0 && empty($request->all()) && empty($request->allFiles())) {
            $postMax = ini_get('post_max_size');
            $msg = "Ukuran file yang diunggah melebihi batas 'post_max_size' server hosting ({$postMax}). Silakan naikkan batas post_max_size di cPanel PHP Selector atau unggah file yang lebih kecil.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $msg,
                    'errors'  => ['project_file' => [$msg]],
                ], 422);
            }
            return back()->withInput()->withErrors(['project_file' => $msg]);
        }

        // 2. Cek jika file gagal di level PHP (misal upload_max_filesize di cPanel terlalu kecil atau disk penuh)
        if ($request->hasFile('project_file')) {
            $uploaded = $request->file('project_file');
            if (!$uploaded->isValid()) {
                $error = $uploaded->getError();
                $serverUploadMax = ini_get('upload_max_filesize');

                $message = match ($error) {
                    UPLOAD_ERR_INI_SIZE => "File gagal diunggah: Ukuran file melebihi batas upload server hosting (upload_max_filesize saat ini: {$serverUploadMax}, batas tugas: {$assignment->max_file_size_mb} MB). Silakan ubah konfigurasi 'upload_max_filesize' dan 'post_max_size' di cPanel (menu Select PHP Version / MultiPHP INI Editor) minimal ke {$assignment->max_file_size_mb}M, atau perkecil ukuran file ZIP Anda.",
                    UPLOAD_ERR_FORM_SIZE => "File melebihi batas ukuran form HTML.",
                    UPLOAD_ERR_PARTIAL => "File hanya terunggah sebagian (koneksi internet sempat terputus saat upload). Silakan coba unggah kembali.",
                    UPLOAD_ERR_NO_TMP_DIR => "Server hosting kehilangan folder temporary (upload_tmp_dir). Silakan hubungi admin hosting.",
                    UPLOAD_ERR_CANT_WRITE => "Server hosting gagal menulis file ke disk (disk hosting penuh atau masalah permission folder /tmp).",
                    UPLOAD_ERR_EXTENSION => "Unggahan file dihentikan oleh ekstensi PHP di server hosting.",
                    default => "File gagal diunggah ke server hosting (PHP Upload Error code: {$error}).",
                };

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => $message,
                        'errors'  => ['project_file' => [$message]],
                    ], 422);
                }

                return back()->withInput()->withErrors(['project_file' => $message]);
            }
        }

        $request->validate([
            'slot_number'  => 'required|integer|min:1|max:' . $assignment->max_slots,
            'title'        => 'required|string|max:191',
            'project_file' => 'required|file|max:' . ($assignment->max_file_size_mb * 1024),
        ], [
            'project_file.uploaded' => "File gagal diunggah ke server hosting. Ukuran file kemungkinan melebihi batas 'upload_max_filesize' (" . ini_get('upload_max_filesize') . ") di PHP hosting. Silakan sesuaikan batas upload di cPanel PHP Selector.",
            'project_file.max'      => "Ukuran file tidak boleh lebih dari {$assignment->max_file_size_mb} MB.",
            'project_file.required' => "File archive project (.zip) wajib dipilih.",
            'title.required'        => "Judul project wajib diisi.",
        ]);

        // Clean previous tmp upload in session if any
        if ($oldPreview = session('upload_preview')) {
            if (!empty($oldPreview['tmp_path']) && File::isDirectory($oldPreview['tmp_path'])) {
                File::deleteDirectory($oldPreview['tmp_path']);
            }
            session()->forget('upload_preview');
        }

        $result = $galleryService->validateAndExtract($request->file('project_file'), $assignment);

        session(['upload_preview' => [
            'tmp_path'      => $result['tmp_path'],
            'meta'          => $result['meta'],
            'title'         => $request->title,
            'slot'          => (int) $request->slot_number,
            'filename'      => $request->file('project_file')->getClientOriginalName(),
            'size'          => $request->file('project_file')->getSize(),
            'assignment_id' => $assignment->id,
        ]]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('student.projects.preview', $assignment),
            ]);
        }

        return redirect()->route('student.projects.preview', $assignment);
    }

    public function previewConfirm(ProjectAssignment $assignment)
    {
        $preview = session('upload_preview');

        if (!$preview || ($preview['assignment_id'] ?? null) !== $assignment->id) {
            return redirect()->route('student.projects.show', $assignment);
        }

        $student = auth()->user();
        $existingSubmission = $assignment->submissions()
            ->where('student_id', $student->id)
            ->where('slot_number', $preview['slot'])
            ->first();

        return view('student.projects.preview', compact('assignment', 'preview', 'existingSubmission'));
    }

    public function confirmUpload(Request $request, ProjectAssignment $assignment, ProjectGalleryService $galleryService)
    {
        $preview = session('upload_preview');

        if (!$preview || ($preview['assignment_id'] ?? null) !== $assignment->id) {
            return redirect()->route('student.projects.show', $assignment);
        }

        $student = auth()->user();

        $galleryService->storeSubmission(
            $preview['tmp_path'],
            $assignment,
            $student,
            $preview['slot'],
            $preview['title'],
            $preview['filename'],
            $preview['size'],
            $preview['meta']
        );

        session()->forget('upload_preview');

        return redirect()->route('student.projects.show', $assignment)
            ->with('success', 'Project berhasil disimpan dan dipublikasikan di Galeri!');
    }

    public function cancelUpload(ProjectAssignment $assignment)
    {
        $preview = session('upload_preview');

        if ($preview && !empty($preview['tmp_path']) && File::isDirectory($preview['tmp_path'])) {
            File::deleteDirectory($preview['tmp_path']);
        }

        session()->forget('upload_preview');

        return redirect()->route('student.projects.show', $assignment)
            ->with('info', 'Unggahan project dibatalkan.');
    }

    public function destroy(
        ProjectAssignment $assignment,
        ProjectSubmission $submission,
        ProjectGalleryService $galleryService
    ) {
        $student = auth()->user();

        if ($submission->student_id !== $student->id || $submission->assignment_id !== $assignment->id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus tugas ini.');
        }

        $slotNumber = $submission->slot_number;
        $galleryService->deleteSubmission($submission);

        return redirect()->route('student.projects.show', $assignment)
            ->with('success', "Tugas project pada Slot #{$slotNumber} berhasil dihapus.");
    }
}

