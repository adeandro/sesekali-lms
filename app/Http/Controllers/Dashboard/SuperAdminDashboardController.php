<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $superadminCount = User::where('role', 'superadmin')->count();
        $teacherCount = User::where('role', 'teacher')->count();
        $studentCount = User::where('role', 'student')->count();
        $activeUsersCount = User::where('status', 'Aktif')->count();

        // Time-based WIB greeting (timezone is already Asia/Jakarta)
        $hour = Carbon::now()->hour;
        if ($hour >= 5 && $hour < 12) {
            $greetingWord = 'Pagi';
            $greetingEmoji = '🌤️';
            $greetingMotif = 'Semangat memulai hari yang produktif!';
        } elseif ($hour >= 12 && $hour < 15) {
            $greetingWord = 'Siang';
            $greetingEmoji = '☀️';
            $greetingMotif = 'Mari kendalikan ekosistem belajar hari ini.';
        } elseif ($hour >= 15 && $hour < 18) {
            $greetingWord = 'Sore';
            $greetingEmoji = '🌇';
            $greetingMotif = 'Tetap semangat menjelang petang!';
        } else {
            $greetingWord = 'Malam';
            $greetingEmoji = '🌙';
            $greetingMotif = 'Pantau sistem dan nikmati malam yang tenang.';
        }

        return view('dashboard.superadmin', compact(
            'totalUsers',
            'superadminCount',
            'teacherCount',
            'studentCount',
            'activeUsersCount',
            'greetingWord',
            'greetingEmoji',
            'greetingMotif'
        ));
    }
}

