<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class StudentDashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.student');
    }
}
