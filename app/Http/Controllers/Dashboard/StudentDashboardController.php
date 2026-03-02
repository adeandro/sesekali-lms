<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 1. Statistics
        $totalAttempts = $user->examAttempts()->count();
        $completedExams = $user->examAttempts()->whereNotNull('submitted_at')->count();
        $avgScore = $user->examAttempts()->whereNotNull('final_score')->avg('final_score') ?? 0;
        
        // 2. Available Exams
        // Published exams that the student hasn't submitted yet
        $submittedExamIds = $user->examAttempts()
            ->whereNotNull('submitted_at')
            ->pluck('exam_id')
            ->toArray();

        $availableExams = Exam::where('status', 'published')
            ->whereNotIn('id', $submittedExamIds)
            // Optional: Filter by grade if applicable
            ->where(function($q) use ($user) {
                $q->whereNull('jenjang')
                  ->orWhere('jenjang', $user->grade);
            })
            ->with(['subject'])
            ->orderBy('start_time', 'asc')
            ->take(6)
            ->get();

        $availableExamsCount = $availableExams->count();

        // 3. Recent Results
        $recentResults = $user->examAttempts()
            ->with('exam.subject')
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'DESC')
            ->take(5)
            ->get();

        $stats = [
            'total_attempts' => $totalAttempts,
            'completed_exams' => $completedExams,
            'available_exams' => $availableExamsCount,
            'avg_score' => round($avgScore, 1)
        ];

        return view('dashboard.student', compact('stats', 'availableExams', 'recentResults'));
    }
}
