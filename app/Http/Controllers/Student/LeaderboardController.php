<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\HistoricalWinner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    private const CACHE_TTL = 3600; // 1 hour

    public function index()
    {
        $user  = Auth::user();
        $tab   = request('tab', 'liga'); // liga | fleet | career | hall

        // Auto-filter by student's own grade_level
        $gradeLevel = $user->grade_level ?? null;

        $cacheKey = "leaderboard.student.{$tab}.grade_{$gradeLevel}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($gradeLevel, $tab) {
            return $this->buildData($gradeLevel, $tab);
        });

        // Hall of Fame: top 10 per season
        $hallOfFame = Cache::remember('leaderboard.hall_of_fame.student', self::CACHE_TTL, function () {
            return HistoricalWinner::with(['user', 'season'])
                ->whereIn('rank', [1, 2, 3])
                ->orderByDesc('archived_at')
                ->take(18)
                ->get();
        });

        // My current rank in liga (seasonal)
        $myRank = null;
        if ($gradeLevel && $tab === 'liga') {
            $myPoints = \App\Services\PointService::getFairScore($user);
            
            $myRank = User::where('role', '=', 'student', 'and')
                ->where('status', '=', 'Aktif', 'and')
                ->where('grade_level', '=', $gradeLevel)
                ->whereRaw('(COALESCE((SELECT AVG(final_score) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL), 0) + 
                    (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 +
                    ((SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
                     (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5)) > ?', [$myPoints])
                ->count() + 1;
        }

        return view('student.leaderboard.index', compact('data', 'tab', 'gradeLevel', 'hallOfFame', 'myRank', 'user'));
    }

    private function buildData(?int $gradeLevel, string $tab): array
    {
        $query = User::where('role', 'student')->where('status', 'Aktif');

        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }

        if ($tab === 'hall') return [];

        if ($tab === 'fleet') {
            return $this->buildFleet($gradeLevel);
        }

        // Student ranking (Liga / Career)
        return $query
            ->select('users.*')
            ->withAvg(['examAttempts as avg_score' => function($q) {
                $q->whereNotNull('submitted_at');
            }], 'final_score')
            ->withCount(['examAttempts as total_sessions' => function($q) {
                $q->whereNotNull('submitted_at');
            }])
            ->selectRaw('(COALESCE((SELECT AVG(final_score) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL), 0) + 
                (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 +
                ((SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
                 (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5)) as performance_points')
            ->orderByDesc('performance_points')
            ->take(50)
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'grade_level' => $student->grade_level,
                    'class_group' => $student->class_group,
                    'active_theme_id' => $student->active_theme_id,
                    'current_level' => $student->current_level,
                    'custom_avatar' => $student->custom_avatar,
                    'photo' => $student->photo,
                    'performance_points' => $student->performance_points,
                    'avg_score' => $student->avg_score,
                    'total_sessions' => $student->total_sessions,
                ];
            })
            ->values()->toArray();
    }

    private function buildFleet(?int $gradeLevel): array
    {
        // For class ranking, we average the performance points of all students in that class
        $fleets = User::where('role', '=', 'student', 'and')
            ->where('status', '=', 'Aktif', 'and')
            ->when($gradeLevel, fn($q) => $q->where('grade_level', $gradeLevel))
            ->select(
                'grade_level',
                'class_group',
                DB::raw('CONCAT(grade_level, "-", COALESCE(class_group,"?")) as fleet_id'),
                DB::raw('COUNT(*) as member_count'),
                // Average APP of the class members
                DB::raw('AVG(
                    (SELECT COALESCE(AVG(final_score), 0) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) + 
                    (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 +
                    ((SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
                     (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5)
                ) as performance_points')
            )
            ->groupBy('grade_level', 'class_group')
            ->orderByDesc('performance_points')
            ->get();

        return $fleets->map(function($f) {
            return [
                'name' => "Kelas " . $f->grade_level . " " . $f->class_group,
                'member_count' => $f->member_count,
                'performance_points' => $f->performance_points,
                'is_fleet' => true,
                'grade_level' => $f->grade_level,
                'class_group' => $f->class_group,
            ];
        })->toArray();
    }
}
