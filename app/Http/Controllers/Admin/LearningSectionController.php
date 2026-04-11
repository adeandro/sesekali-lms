<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\LearningSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LearningSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LearningMaterial $material)
    {
        return redirect()->route('admin.learning.materials.show', $material);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(LearningMaterial $material)
    {
        return view('admin.learning.sections.create', compact('material'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, LearningMaterial $material)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,video,file',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'file_path' => 'nullable|file|max:10240', // Max 10MB
            'order' => 'integer',
        ]);

        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')->store('learning/files', 'public');
        }

        $validated['learning_material_id'] = $material->id;
        
        if (!isset($validated['order'])) {
            $validated['order'] = $material->sections()->count() + 1;
        }

        LearningSection::create($validated);

        return redirect()->route('admin.learning.materials.show', $material)
            ->with('success', 'Bab materi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LearningSection $section)
    {
        $material = $section->material;
        return view('admin.learning.sections.edit', compact('section', 'material'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LearningSection $section)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,video,file',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'file_path' => 'nullable|file|max:51200', // Max 50MB
            'order' => 'integer',
        ]);

        if ($request->hasFile('file_path')) {
            if ($section->file_path) {
                Storage::disk('public')->delete($section->file_path);
            }
            $validated['file_path'] = $request->file('file_path')->store('learning/files', 'public');
        }

        $section->update($validated);

        return redirect()->route('admin.learning.materials.show', $section->learning_material_id)
            ->with('success', 'Bab materi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningSection $section)
    {
        $materialId = $section->learning_material_id;

        if ($section->file_path) {
            Storage::disk('public')->delete($section->file_path);
        }

        $section->delete();

        return redirect()->route('admin.learning.materials.show', $materialId)
            ->with('success', 'Bab materi berhasil dihapus.');
    }

    /**
     * Remove only the file from the section.
     */
    public function deleteFile(LearningSection $section)
    {
        if ($section->file_path) {
            Storage::disk('public')->delete($section->file_path);
            $section->update(['file_path' => null]);
            
            return redirect()->back()->with('success', 'Berkas materi berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Tidak ada berkas untuk dihapus.');
    }

    /**
     * Reorder sections via AJAX.
     */
    public function reorder(Request $request, LearningMaterial $material)
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*' => 'exists:learning_sections,id',
        ]);

        foreach ($request->sections as $index => $id) {
            LearningSection::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
