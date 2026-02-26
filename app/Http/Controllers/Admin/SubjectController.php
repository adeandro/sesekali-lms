<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subject;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index()
    {
        $subjects = Subject::withCount('questions')->paginate(15);
        return view('admin.subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        return view('admin.subjects.create');
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully');
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $subject->id,
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully');
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        // Prevent deletion if questions exist
        if ($subject->questions()->exists()) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'Cannot delete subject. Questions exist for this subject.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully');
    }

    /**
     * Delete all subjects
     */
    public function deleteAllSubjects()
    {
        try {
            $subjects = Subject::all();
            $count = $subjects->count();

            foreach ($subjects as $subject) {
                $subject->delete();
            }

            return redirect()->route('admin.subjects.index')
                ->with('success', "All {$count} subjects have been permanently deleted.");
        } catch (\Exception $e) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'Error deleting subjects: ' . $e->getMessage());
        }
    }
}
