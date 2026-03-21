<?php

namespace App\Http\Controllers\Tu;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;

class TuDashboardController extends Controller
{
    public function index()
    {
        $totalLetters = Letter::count();
        $totalTemplates = LetterTemplate::active()->count();
        $recentLetters = Letter::with('template', 'recipient', 'creator')
            ->latest()
            ->take(5)
            ->get();

        return view('tu.dashboard', compact(
            'totalLetters', 
            'totalTemplates', 
            'recentLetters'
        ));
    }
}
