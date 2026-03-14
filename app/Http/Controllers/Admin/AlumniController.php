<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'student')
            ->where('status', 'Alumni');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter by graduation year
        if ($request->filled('year')) {
            $query->where('alumni_year', $request->input('year'));
        }

        // Available graduation years for filter dropdown
        $years = User::where('role', 'student')
            ->where('status', 'Alumni')
            ->whereNotNull('alumni_year')
            ->distinct()
            ->orderByDesc('alumni_year')
            ->pluck('alumni_year');

        $totalAlumni = User::where('role', 'student')->where('status', 'Alumni')->count();

        $alumni = $query->orderByDesc('alumni_year')->orderBy('nis')->paginate(20)->withQueryString();

        return view('admin.alumni.index', compact('alumni', 'years', 'totalAlumni'));
    }
}
