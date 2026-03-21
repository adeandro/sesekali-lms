<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display settings form.
     */
    public function index()
    {
        $allSettings = Setting::all()->pluck('value', 'key');
        $extracurriculars = Extracurricular::orderBy('sort_order', 'asc')->get();
        
        return view('admin.settings.index', compact('allSettings', 'extracurriculars'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('school', 'public');
            Setting::set('logo', $path);
        }

        // Handle Default Student Avatar Upload
        if ($request->hasFile('default_student_avatar')) {
            // Delete old avatar if exists
            $oldAvatar = Setting::get('default_student_avatar');
            if ($oldAvatar) {
                Storage::disk('public')->delete($oldAvatar);
            }

            $path = $request->file('default_student_avatar')->store('school', 'public');
            Setting::set('default_student_avatar', $path);
        }

        // Save other settings if present in request
        $settingsToUpdate = [
            'school_name', 'school_address', 'school_phone',
            'school_province', 'school_city', 'school_village',
            'school_rt', 'school_rw', 'school_postal_code',
            'report_header_subtitle', 'bidang_studi', 'program_studi',
            'kompetensi_keahlian', 'show_report_header', 'show_login_header',
            'watermark_enabled', 'max_violations', 'anti_cheat_active',
            'academic_year', 'enable_gamification', 'enable_leaderboard', 
            'report_decimal', 'letter_code',
            'letterhead_foundation', 'letterhead_program', 'letterhead_email',
            'letterhead_website', 'letterhead_border_style',
        ];

        foreach ($settingsToUpdate as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.')->with('active_tab', 'identity');
    }

    /**
     * Update superadmin profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_signature_active' => 'nullable|in:0,1',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Handle Password Update
        if ($request->filled('new_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Password saat ini tidak cocok.'])->withInput();
            }
            $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        }

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete('profiles/' . $user->photo);
            }

            $filename = time() . '_' . $user->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->storeAs('profiles', $filename, 'public');
            $user->photo = $filename;
        }

        // Handle Signature Upload
        if ($request->hasFile('signature')) {
            if ($user->signature) {
                Storage::disk('public')->delete('signatures/' . $user->signature);
            }

            $filename = 'sig_admin_' . $user->id . '_' . time() . '.' . $request->file('signature')->getClientOriginalExtension();
            $request->file('signature')->storeAs('signatures', $filename, 'public');
            $user->signature = $filename;
            $user->is_signature_active = true;
        }

        if ($request->has('is_signature_active')) {
            $user->is_signature_active = $request->is_signature_active == '1';
        }

        $user->save();

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.')->with('active_tab', 'profile');
    }

    /**
     * Delete admin signature.
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

        return redirect()->back()->with('success', 'Tanda tangan berhasil dihapus.')->with('active_tab', 'profile');
    }
}
