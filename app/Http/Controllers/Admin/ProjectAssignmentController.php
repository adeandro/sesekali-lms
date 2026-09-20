<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ProjectAssignment;
use App\Models\ProjectSubmission;
use App\Models\Subject;
use App\Services\ProjectGalleryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectAssignmentController extends Controller
{
    public function index()
    {
        $assignments = ProjectAssignment::with(['subject', 'creator'])
            ->withCount('submissions')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.project-assignments.index', compact('assignments'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        $classes  = ClassRoom::orderBy('name')->get();

        return view('admin.project-assignments.create', compact('subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:191',
            'description'         => 'nullable|string',
            'subject_id'          => 'nullable|exists:subjects,id',
            'max_file_size_mb'    => 'required|integer|min:1|max:100',
            'max_slots'           => 'required|integer|min:1|max:10',
            'class_restriction'   => 'nullable|array',
            'class_restriction.*' => 'exists:classes,id',
            'is_active'           => 'boolean',
            'starts_at'           => 'nullable|date',
            'ends_at'             => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();

        ProjectAssignment::create($data);

        return redirect()->route('admin.project-assignments.index')
            ->with('success', 'Assignment project berhasil dibuat.');
    }

    public function edit(ProjectAssignment $assignment)
    {
        $subjects = Subject::orderBy('name')->get();
        $classes  = ClassRoom::orderBy('name')->get();

        return view('admin.project-assignments.edit', compact('assignment', 'subjects', 'classes'));
    }

    public function update(Request $request, ProjectAssignment $assignment)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:191',
            'description'         => 'nullable|string',
            'subject_id'          => 'nullable|exists:subjects,id',
            'max_file_size_mb'    => 'required|integer|min:1|max:100',
            'max_slots'           => 'required|integer|min:1|max:10',
            'class_restriction'   => 'nullable|array',
            'class_restriction.*' => 'exists:classes,id',
            'is_active'           => 'boolean',
            'starts_at'           => 'nullable|date',
            'ends_at'             => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $assignment->update($data);

        return redirect()->route('admin.project-assignments.index')
            ->with('success', 'Assignment project berhasil diperbarui.');
    }

    public function destroy(ProjectAssignment $assignment, ProjectGalleryService $galleryService)
    {
        $galleryService->clearAllSubmissions($assignment);
        $assignment->delete();

        return redirect()->route('admin.project-assignments.index')
            ->with('success', 'Assignment project dan seluruh filenya berhasil dihapus.');
    }

    public function submissions(ProjectAssignment $assignment)
    {
        $assignment->load(['subject', 'creator']);
        $submissions = $assignment->submissions()
            ->with('student.classroom')
            ->orderBy('slot_number')
            ->orderByDesc('uploaded_at')
            ->get();

        return view('admin.project-assignments.submissions', compact('assignment', 'submissions'));
    }

    public function toggleActive(ProjectAssignment $assignment)
    {
        $assignment->update(['is_active' => !$assignment->is_active]);

        $statusText = $assignment->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Assignment berhasil {$statusText}.");
    }

    public function destroySubmission(
        ProjectAssignment $assignment,
        ProjectSubmission $submission,
        ProjectGalleryService $galleryService
    ) {
        if ($submission->assignment_id !== $assignment->id) {
            abort(404, 'Tugas project tidak ditemukan pada assignment ini.');
        }

        $studentName = $submission->student->name ?? 'Siswa';
        $slotNumber = $submission->slot_number;

        $galleryService->deleteSubmission($submission);

        return back()->with('success', "Karya tugas milik {$studentName} (Slot #{$slotNumber}) berhasil dihapus dari server.");
    }

    public function archive(
        ProjectAssignment $assignment,
        ProjectGalleryService $galleryService
    ) {
        $zipPath = $galleryService->createArchiveZip($assignment);

        if (!$zipPath || !file_exists($zipPath)) {
            return back()->with('error', 'Belum ada karya tugas siswa yang dapat diarsipkan.');
        }

        $cleanTitle = Str::slug($assignment->title) ?: 'projek';
        $downloadName = "arsip_{$cleanTitle}_" . date('Ymd_His') . '.zip';

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }

    public function clearAllSubmissions(
        ProjectAssignment $assignment,
        ProjectGalleryService $galleryService
    ) {
        $count = $galleryService->clearAllSubmissions($assignment);

        return back()->with('success', "Seluruh ({$count}) karya tugas siswa pada assignment ini berhasil dibersihkan dari server.");
    }
}


