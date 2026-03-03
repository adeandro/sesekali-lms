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
}
