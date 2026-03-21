<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperAdminTeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['teacher', 'principal', 'superadmin', 'tu'])->with('subjects');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $teachers = $query->latest()->paginate(10);
        return view('superadmin.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        return view('superadmin.teachers.create', compact('subjects'));
    }

    /**
     * Store a newly created teacher.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title_ahead' => 'nullable|string|max:50',
            'title_behind' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users,email',
            'nis' => 'required|string|unique:users,nis',
            'password' => 'required|string|min:8|confirmed',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'role' => 'required|string|in:teacher,principal,tu',
            'nip' => 'nullable|string|max:50',
            'niy' => 'nullable|string|max:50',
        ]);

        if ($request->role === 'principal' && User::where('role', '=', 'principal', 'and')->exists()) {
            return back()->withErrors(['role' => 'Gagal menambahkan. Hanya diperbolehkan ada satu Kepala Sekolah dalam sistem.'])->withInput();
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($request->has('subject_ids')) {
            $user->subjects()->sync($request->subject_ids);
        }

        return redirect()->route('superadmin.teachers.index')
            ->with('success', 'Guru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the teacher.
     */
    public function edit(User $teacher)
    {
        if (!in_array($teacher->role, ['teacher', 'principal', 'superadmin', 'tu'])) {
            abort(404);
        }

        $subjects = Subject::orderBy('name')->get();
        return view('superadmin.teachers.edit', compact('teacher', 'subjects'));
    }

    /**
     * Update the teacher.
     */
    public function update(Request $request, User $teacher)
    {
        if (!in_array($teacher->role, ['teacher', 'principal', 'superadmin', 'tu'])) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title_ahead' => 'nullable|string|max:50',
            'title_behind' => 'nullable|string|max:50',
            'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->id)],
            'nis' => ['required', 'string', Rule::unique('users')->ignore($teacher->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
            'status' => 'required|string|in:Aktif,Nonaktif',
            'role' => 'required|string|in:teacher,principal,superadmin,tu',
            'nip' => 'nullable|string|max:50',
            'niy' => 'nullable|string|max:50',
        ]);

        if ($request->role === 'principal' && User::where('role', '=', 'principal', 'and')->where('id', '!=', $teacher->id, 'and')->exists()) {
            return back()->withErrors(['role' => 'Gagal mengubah. Sudah ada Kepala Sekolah lain yang terdaftar.'])->withInput();
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $teacher->update($validated);
        
        $teacher->subjects()->sync($request->subject_ids ?? []);

        return redirect()->route('superadmin.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Remove the teacher.
     */
    public function destroy(User $teacher)
    {
        if (!in_array($teacher->role, ['teacher', 'principal', 'superadmin', 'tu'])) {
            abort(404);
        }

        if ($teacher->id === auth()->id()) {
            return redirect()->route('superadmin.teachers.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $teacher->delete();

        return redirect()->route('superadmin.teachers.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
