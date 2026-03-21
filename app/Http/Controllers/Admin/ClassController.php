<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of all class rooms.
     */
    public function index()
    {
        $classes = ClassRoom::with('homeroomTeacher')
            ->withCount('students')
            ->orderBy('grade')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.classes.create', compact('teachers'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'grade'                => 'required|in:10,11,12',
            'section'              => 'nullable|string|max:100',
            'academic_year'        => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'capacity'             => 'nullable|integer|min:1|max:100',
            'is_active'            => 'boolean',
            'homeroom_teacher_id'  => 'nullable|exists:users,id',
        ], [
            'academic_year.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2024/2025)',
        ]);

        if (!$request->has('is_active')) {
            $data['is_active'] = false;
        }

        ClassRoom::create($data);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassRoom $class)
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, ClassRoom $class)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'grade'                => 'required|in:10,11,12',
            'section'              => 'nullable|string|max:100',
            'academic_year'        => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'capacity'             => 'nullable|integer|min:1|max:100',
            'is_active'            => 'boolean',
            'homeroom_teacher_id'  => 'nullable|exists:users,id',
        ], [
            'academic_year.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2024/2025)',
        ]);

        if (!$request->has('is_active')) {
            $data['is_active'] = false;
        }

        $class->update($data);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassRoom $class)
    {
        if ($class->students()->exists()) {
            return back()->withErrors([
                'class' => 'Kelas tidak dapat dihapus karena masih memiliki siswa.',
            ]);
        }

        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
