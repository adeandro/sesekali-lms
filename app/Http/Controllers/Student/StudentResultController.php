<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\EssayGradingService;

class StudentResultController extends Controller
{
    /**
     * Display student's exam history.
     */
    public function index()
    {
        $student = auth()->user();
        $results = EssayGradingService::getStudentExamHistory($student->id);

        // Filter out attempts for deleted exams and apply score visibility rules
        $results = $results->filter(fn($attempt) => $attempt->exam !== null)
            ->map(function ($attempt) {
                $canViewScore = EssayGradingService::canViewScore($attempt);
                if (!$canViewScore) {
                    $attempt->final_score = null;
                    $attempt->score_mc = null;
                    $attempt->score_essay = null;
                }
                $attempt->can_view_score = $canViewScore;
                return $attempt;
            })
            ->values(); // Reset collection keys

        return view('student.results.index', compact('results'));
    }
}
