<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('admin.settings.index', compact('allSettings'));
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

        // Save other settings
        Setting::set('school_name', $request->school_name);
        Setting::set('school_address', $request->school_address);
        Setting::set('school_phone', $request->school_phone);
        Setting::set('report_header_subtitle', $request->report_header_subtitle);
        Setting::set('show_report_header', $request->show_report_header);
        Setting::set('show_login_header', $request->show_login_header);
        Setting::set('max_violations', $request->max_violations);
        Setting::set('anti_cheat_active', $request->anti_cheat_active);
        Setting::set('academic_year', $request->academic_year);

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
