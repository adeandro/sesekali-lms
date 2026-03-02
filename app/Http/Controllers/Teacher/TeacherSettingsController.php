<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class TeacherSettingsController extends Controller
{
    /**
     * Display the teacher settings page.
     */
    public function index()
    {
        return view('teacher.settings', [
            'user' => auth()->user()
        ]);
    }

    /**
     * Update the teacher's profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'username.unique' => 'NIP/Username ini sudah digunakan oleh pengguna lain.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'photo.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];

        if ($request->hasFile('photo')) {
            // Delete old photo if exists and not default
            if ($user->photo && !str_contains($user->photo, 'ui-avatars.com')) {
                Storage::disk('public')->delete('profiles/' . $user->photo);
            }

            $file = $request->file('photo');
            $filename = 'teacher_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profiles', $filename, 'public');
            $user->photo = $filename;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Update the teacher's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
        ]);

        $user = auth()->user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }
}
