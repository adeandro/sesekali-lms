<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectAssignment;
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

        $request->validate([
            'slot_number'  => 'required|integer|min:1|max:' . $assignment->max_slots,
            'title'        => 'required|string|max:191',
            'project_file' => 'required|file|max:' . ($assignment->max_file_size_mb * 1024),
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
}
