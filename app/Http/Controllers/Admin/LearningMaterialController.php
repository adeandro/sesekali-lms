<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LearningMaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LearningMaterial::query()->with(['subject', 'creator']);

        if (auth()->user()->role === 'teacher') {
            $mySubjectIds = auth()->user()->subjects->pluck('id')->toArray();
            $query->whereIn('subject_id', $mySubjectIds);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $materials = $query->orderBy('order', 'asc')->paginate(10);

        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
        } else {
            $subjects = Subject::orderBy('name')->get();
        }

        return view('admin.learning.materials.index', compact('materials', 'subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
            $exams = \App\Models\Exam::whereIn('subject_id', $subjects->pluck('id'))
                ->orderBy('title')
                ->get();
        } else {
            $subjects = Subject::orderBy('name')->get();
            $exams = \App\Models\Exam::orderBy('title')->get();
        }

        return view('admin.learning.materials.create', compact('subjects', 'exams'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'cover_image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'order' => 'integer',
            'exam_id' => 'nullable|exists:exams,id',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('learning/covers', 'public');
        }

        $validated['created_by'] = auth()->id();
        $validated['is_published'] = $request->has('is_published');
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);

        LearningMaterial::create($validated);

        return redirect()->route('admin.learning.materials.index')
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LearningMaterial $material)
    {
        $material->load(['sections', 'subject']);
        return view('admin.learning.materials.show', compact('material'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LearningMaterial $material)
    {
        if (auth()->user()->role === 'teacher') {
            $subjects = auth()->user()->subjects;
            if (!in_array($material->subject_id, $subjects->pluck('id')->toArray())) {
                abort(403);
            }
            $exams = \App\Models\Exam::whereIn('subject_id', $subjects->pluck('id'))
                ->orderBy('title')
                ->get();
        } else {
            $subjects = Subject::orderBy('name')->get();
            $exams = \App\Models\Exam::orderBy('title')->get();
        }

        return view('admin.learning.materials.edit', compact('material', 'subjects', 'exams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LearningMaterial $material)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'cover_image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
            'order' => 'integer',
            'exam_id' => 'nullable|exists:exams,id',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($material->cover_image) {
                Storage::disk('public')->delete($material->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('learning/covers', 'public');
        }

        $validated['is_published'] = $request->has('is_published');
        
        if ($material->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        $material->update($validated);

        return redirect()->route('admin.learning.materials.index')
            ->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningMaterial $material)
    {
        if ($material->cover_image) {
            Storage::disk('public')->delete($material->cover_image);
        }

        $material->delete();

        return redirect()->route('admin.learning.materials.index')
            ->with('success', 'Materi berhasil dihapus.');
    }
}
