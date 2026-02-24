<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $superadminCount = User::where('role', 'superadmin')->count();
        $adminCount = User::where('role', 'admin')->count();
        $studentCount = User::where('role', 'student')->count();
        $activeUsersCount = User::where('is_active', true)->count();

        return view('dashboard.superadmin', compact(
            'totalUsers',
            'superadminCount',
            'adminCount',
            'studentCount',
            'activeUsersCount'
        ));
    }
}
