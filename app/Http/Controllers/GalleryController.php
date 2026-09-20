<?php

namespace App\Http\Controllers;

use App\Models\ProjectAssignment;
use App\Models\ProjectSubmission;
use App\Models\Setting;
use App\Services\ProjectGalleryService;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $assignments = ProjectAssignment::active()
            ->with(['subject', 'creator'])
            ->withCount('submissions')
            ->latest()
            ->paginate(12);

        $configs = Setting::pluck('value', 'key')->toArray();

        return view('gallery.index', compact('assignments', 'configs'));
    }

    public function show(ProjectAssignment $assignment)
    {
        if (!$assignment->is_active) {
            abort(404, 'Galeri project ini tidak tersedia atau dinonaktifkan.');
        }

        $assignment->load(['subject', 'creator']);

        $submissions = $assignment->submissions()
            ->with('student.classroom')
            ->orderBy('slot_number')
            ->orderByDesc('uploaded_at')
            ->get();

        $configs = Setting::pluck('value', 'key')->toArray();

        return view('gallery.show', compact('assignment', 'submissions', 'configs'));
    }

    public function serveFile(
        ProjectAssignment $assignment,
        int $studentId,
        int $slotNumber,
        string $filePath,
        ProjectGalleryService $galleryService
    ) {
        $submission = ProjectSubmission::where([
            'assignment_id' => $assignment->id,
            'student_id'    => $studentId,
            'slot_number'   => $slotNumber,
        ])->firstOrFail();

        return $galleryService->serveFile($submission, $filePath);
    }
}
