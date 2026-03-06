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
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_signature_active' => 'nullable|in:0,1',
        ], [
            'username.unique' => 'NIP/Username ini sudah digunakan oleh pengguna lain.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'photo.max' => 'Ukuran gambar maksimal 2MB.',
            'signature.image' => 'File tanda tangan harus berupa gambar.',
            'signature.mimes' => 'Format tanda tangan harus PNG, JPG, atau JPEG.',
            'signature.max' => 'Ukuran tanda tangan maksimal 2MB.',
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

        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($user->signature) {
                Storage::disk('public')->delete('signatures/' . $user->signature);
            }

            $file = $request->file('signature');
            $filename = 'sig_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('signatures', $filename, 'public');
            $user->signature = $filename;
            $user->is_signature_active = true; // Auto activate on upload
        }

        // Handle toggle
        if ($request->has('is_signature_active')) {
            $user->is_signature_active = $request->is_signature_active == '1';
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

    /**
     * Delete the teacher's signature.
     */
    public function deleteSignature()
    {
        $user = auth()->user();
        
        if ($user->signature) {
            Storage::disk('public')->delete('signatures/' . $user->signature);
            $user->signature = null;
            $user->is_signature_active = false;
            $user->save();
        }

        return redirect()->back()->with('success', 'Tanda tangan berhasil dihapus.');
    }
}
