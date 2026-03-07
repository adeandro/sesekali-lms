<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index(\App\Services\AchievementService $achievementService)
    {
        $user = Auth::user();
        
        // Check and award achievements if enabled
        if (\App\Models\Setting::get('enable_gamification', '1') == '1') {
            $achievementService->checkAchievements($user);
        }
        
        // 1. Statistics
        $totalAttempts = $user->examAttempts()->count();
        $completedExams = $user->examAttempts()->whereNotNull('submitted_at')->count();
        $avgScore = $user->examAttempts()->whereNotNull('final_score')->avg('final_score') ?? 0;
        
        // 2. Global Leaderboard (Top 10)
        $leaderboard = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->take(10)
            ->get();

        // 3. Local Leaderboard (Same Grade)
        $localLeaderboard = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $user->grade)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->take(10)
            ->get();

        // 4. Current Student Rank (Global & Local)
        $allRankedStudents = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->pluck('id')
            ->toArray();
        
        $currentRank = array_search($user->id, $allRankedStudents) !== false 
            ? array_search($user->id, $allRankedStudents) + 1 
            : '-';

        $localRankedStudents = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $user->grade)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->pluck('id')
            ->toArray();
        
        $currentLocalRank = array_search($user->id, $localRankedStudents) !== false 
            ? array_search($user->id, $localRankedStudents) + 1 
            : '-';

        // 5. Badges
        $allAchievements = \App\Models\Achievement::all();
        $earnedAchievements = $user->achievements->keyBy('slug');

        // 6. Greeting
        $hour = now()->hour;
        $minute = now()->minute;
        $greeting = 'Selamat Malam 🌙';
        $motivationalText = 'Siap menaklukkan materi malam ini?';

        if ($hour >= 5 && $hour < 11) {
            $greeting = 'Selamat Pagi 🌅';
            $motivationalText = 'Mulai harimu dengan semangat belajar baru!';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat Siang ☀️';
            $motivationalText = 'Tetap fokus, perjalanan menuju sukses masih panjang.';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Selamat Sore 🌇';
            $motivationalText = 'Sore yang cerah untuk mereview pelajaran hari ini.';
        } elseif ($hour >= 18 || $hour < 5) {
            $firstName = explode(' ', $user->name)[0];
            $greeting = 'Selamat Malam, ' . $firstName . '. 🌙';
            
            if ($hour >= 20 || $hour < 5) {
                 $motivationalText = 'Malam yang tenang untuk konsentrasi maksimal. 🌙';
            } else {
                 $motivationalText = 'Konsentrasi ekstra untuk meraih bintang ujianmu!';
            }
        }

        // 7. Available Exams
        $submittedExamIds = $user->examAttempts()
            ->where('status', 'submitted')
            ->pluck('exam_id')
            ->toArray();

        $availableExams = Exam::where('status', 'published')
            ->whereNotIn('id', $submittedExamIds)
            ->where(function($q) use ($user) {
                $q->whereNull('jenjang')
                  ->orWhere('jenjang', $user->grade);
            })
            ->with(['subject'])
            ->orderBy('start_time', 'asc')
            ->take(6)
            ->get();

        // 8. Recent Results
        $recentResults = $user->examAttempts()
            ->with('exam.subject')
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'DESC')
            ->take(5)
            ->get();

        $stats = [
            'total_attempts' => $totalAttempts,
            'completed_exams' => $completedExams,
            'available_exams' => $availableExams->count(),
            'avg_score' => round($avgScore, 1),
            'current_rank' => $currentRank,
            'current_local_rank' => $currentLocalRank,
            'greeting' => $greeting,
            'motivational_text' => $motivationalText
        ];

        return view('dashboard.student', compact(
            'stats', 
            'availableExams', 
            'recentResults', 
            'leaderboard', 
            'localLeaderboard',
            'earnedAchievements', 
            'allAchievements'
        ));
    }
}
