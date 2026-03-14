<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $studentCount = User::where('role', 'student')->count();
        $activeUsersCount = User::where('status', 'Aktif')->count();

        return view('dashboard.admin', compact(
            'totalUsers',
            'studentCount',
            'activeUsersCount'
        ));
    }
}
