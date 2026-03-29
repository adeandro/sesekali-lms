<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Setting;

class InformationController extends Controller
{
    /**
     * Public info page — no auth required.
     * Shows all active login-announcements in a clean vertical list.
     */
    public function index()
    {
        $schoolName = '';
        try {
            // Try app('configs') first (shared view composer approach)
            $configs    = app('configs') ?? [];
            $schoolName = $configs['school_name'] ?? Setting::get('school_name', 'SesekaliCBT');
        } catch (\Throwable $e) {
            $schoolName = Setting::get('school_name', 'SesekaliCBT');
        }

        $announcements = collect([]);
        try {
            $announcements = Announcement::forLogin()->get();
        } catch (\Throwable $e) {
            // Non-critical — return empty list
        }

        return view('information', compact('announcements', 'schoolName'));
    }
}
