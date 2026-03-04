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
        Setting::set('show_login_header', $request->show_login_header);
        Setting::set('max_violations', $request->max_violations);
        Setting::set('anti_cheat_active', $request->anti_cheat_active);
        Setting::set('academic_year', $request->academic_year);

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui.');
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

        $user->save();

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }
}
