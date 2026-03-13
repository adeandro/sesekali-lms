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
        
        // 2. Angkatan Leaderboard (Same Grade)
        $angkatanLeaderboard = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $user->grade)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->take(10)
            ->get();
            
        // 3.1 Class Leaderboard (Same Grade & Class Group)
        $classLeaderboard = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $user->grade)
            ->where('class_group', $user->class_group)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->take(10)
            ->get();

        // 4. Current Student Rank (Angkatan & Class)
        $angkatanRankedStudents = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $user->grade)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->pluck('id')
            ->toArray();
        
        $currentAngkatanRank = array_search($user->id, $angkatanRankedStudents) !== false 
            ? array_search($user->id, $angkatanRankedStudents) + 1 
            : '-';

        $classRankedStudents = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $user->grade)
            ->where('class_group', $user->class_group)
            ->withSum(['examAttempts as total_score' => function($query) {
                $query->whereNotNull('submitted_at');
            }], 'final_score')
            ->orderByDesc('total_score')
            ->pluck('id')
            ->toArray();
        
        $currentClassRank = array_search($user->id, $classRankedStudents) !== false 
            ? array_search($user->id, $classRankedStudents) + 1 
            : '-';

        // 5. Badges (Dynamic active only)
        $allAchievements = \App\Models\Achievement::where('is_active', true)->orderBy('created_at', 'asc')->get();
        $earnedAchievements = $user->achievements->keyBy('slug');

        // 6. Greeting (WIB)
        $now = now()->timezone('Asia/Jakarta');
        $hour = $now->hour;
        $firstName = explode(' ', $user->name)[0];
        
        if ($hour >= 5 && $hour < 11) {
            $greeting = 'Selamat Pagi, ' . $firstName . ' 🌅';
            $motivationalText = 'Mulai harimu dengan semangat belajar baru!';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat Siang, Pejuang! ☀️';
            $motivationalText = 'Tetap semangat di tengah aktivitas hari ini!';
        } elseif ($hour >= 15 && $hour < 19) {
            $greeting = 'Selamat Sore, ' . $firstName . ' 🌇';
            $motivationalText = 'Sore yang cerah untuk mereview pelajaran hari ini.';
        } else {
            $greeting = 'Selamat Malam, ' . $firstName . ' 🌙';
            if ($hour >= 20 || $hour < 5) {
                 $motivationalText = 'Malam yang tenang untuk konsentrasi maksimal.';
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
            ->where('end_time', '>', now())
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
            'current_angkatan_rank' => $currentAngkatanRank,
            'current_class_rank' => $currentClassRank,
            'greeting' => $greeting,
            'motivational_text' => $motivationalText
        ];

        return view('dashboard.student', compact(
            'stats', 
            'availableExams', 
            'recentResults', 
            'angkatanLeaderboard', 
            'classLeaderboard',
            'earnedAchievements', 
            'allAchievements'
        ));
    }

    public function markNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
