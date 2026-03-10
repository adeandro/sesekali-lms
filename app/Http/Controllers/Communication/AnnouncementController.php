<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AnnouncementController extends Controller
{
    public function __construct(private CommunicationService $commService) {}

    /**
     * Management view for staff (superadmin/teacher) — all announcements.
     * Read-only list view for students.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            return $this->studentIndex();
        }

        $announcements = $this->commService->getAnnouncementsForManagement($user);

        return view('communication.announcements.index', compact('announcements'));
    }

    /**
     * Student-facing: returns only active announcements visible to this student.
     * Used as internal redirect from index().
     */
    private function studentIndex()
    {
        $user          = Auth::user();
        $announcements = $this->commService->getActiveAnnouncementsForUser($user);

        return view('communication.announcements.student_index', compact('announcements'));
    }

    /**
     * Show creation form (superadmin / teacher only).
     */
    public function create()
    {
        Gate::authorize('create', Announcement::class);

        $user = Auth::user();

        // Teachers can only target their own assigned classes
        $classes = [];
        if ($user->role === 'teacher') {
            $classes = User::where('role', 'student')
                           ->where('is_active', true)
                           ->select('class_group')
                           ->distinct()
                           ->orderBy('class_group')
                           ->pluck('class_group')
                           ->toArray();
        } else {
            // Superadmin sees all classes
            $classes = User::where('role', 'student')
                           ->select('class_group')
                           ->distinct()
                           ->orderBy('class_group')
                           ->pluck('class_group')
                           ->toArray();
        }

        return view('communication.announcements.create', compact('classes'));
    }

    /**
     * Store a new announcement.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Announcement::class);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'content'         => 'required|string|max:5000',
            'type'            => 'required|in:info,warning,urgent',
            'target_role'     => 'required|in:all,teacher,student',
            'target_class_id' => 'nullable|string|max:100',
            'expires_at'      => 'nullable|date|after:now',
        ]);

        $this->commService->createAnnouncement(Auth::user(), $validated);

        return redirect()->route('communication.announcements.index')
                         ->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Soft-deactivate / delete an announcement.
     */
    public function destroy(Announcement $announcement)
    {
        Gate::authorize('delete', $announcement);

        $announcement->update(['is_active' => false]);

        return back()->with('success', 'Pengumuman telah dinonaktifkan.');
    }

    /**
     * Toggle is_active (superadmin only).
     */
    public function toggleActive(Announcement $announcement)
    {
        Gate::authorize('toggleActive', Announcement::class);

        $announcement->update(['is_active' => !$announcement->is_active]);

        return back()->with('success', 'Status pengumuman diperbarui.');
    }

    /**
     * Permanently delete an announcement from the database (superadmin only).
     */
    public function permanentDelete(Announcement $announcement)
    {
        Gate::authorize('permanentDelete', $announcement);

        $announcement->delete();

        return redirect()->route('communication.announcements.index')
                         ->with('success', 'Pengumuman berhasil dihapus permanen.');
    }
}
