<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\HallOfFame;
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

        // Auto-filter by student's own grade
        $gradeLevel = $user->grade_level ?: $user->grade;

        $cacheKey = "leaderboard.student.{$tab}.grade_{$gradeLevel}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($gradeLevel, $tab) {
            return $this->buildData($gradeLevel, $tab);
        });

        // Hall of Fame: top 3 per season
        $hallOfFame = Cache::remember('leaderboard.hall_of_fame.student', self::CACHE_TTL, function () {
            return HallOfFame::with(['user', 'season'])
                ->whereIn('rank', [1, 2, 3])
                ->orderByDesc('recorded_at')
                ->take(18)
                ->get();
        });

        // My current rank in liga (seasonal)
        $myRank = null;
        if ($gradeLevel && $tab === 'liga') {
            $myPoints = \App\Services\PointService::getFairScore($user);
            
            $myRank = User::where('role', '=', 'student')
                ->where('status', '=', 'Aktif')
                ->where(function($q) use ($gradeLevel) {
                    $q->where('grade_level', $gradeLevel)
                      ->orWhere('grade', (string) $gradeLevel);
                })
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
        $query = User::where('role', '=', 'student')->where('status', '=', 'Aktif');

        if ($gradeLevel) {
            $query->where(function($q) use ($gradeLevel) {
                $q->where('grade_level', $gradeLevel)
                  ->orWhere('grade', (string) $gradeLevel);
            });
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
                    'grade_level' => $student->grade_level ?: $student->grade,
                    'class_group' => $student->class_group,
                    'active_theme_id' => $student->active_theme_id,
                    'current_level' => $student->current_level,
                    'custom_avatar' => $student->custom_avatar,
                    'photo' => $student->photo,
                    'performance_points' => $student->performance_points,
                    'avg_score' => $student->avg_score,
                    'total_sessions' => $student->total_sessions,
                    'seasonal_exp' => $student->seasonal_exp,
                    'career_exp' => $student->career_exp,
                ];
            })
            ->values()->toArray();
    }

    private function buildFleet(?int $gradeLevel): array
    {
        $query = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->select('*')
            ->selectRaw('(COALESCE((SELECT AVG(final_score) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL), 0) + 
                (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 +
                ((SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
                 (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5)) as performance_points');

        if ($gradeLevel) {
            $query->where(function($q) use ($gradeLevel) {
                $q->where('grade_level', $gradeLevel)
                  ->orWhere('grade', (string) $gradeLevel);
            });
        }

        return $query->get()
            ->groupBy(fn($u) => ($u->grade_level ?: $u->grade) . '-' . ($u->class_group ?: 'X'))
            ->map(function($members) {
                $first = $members->first();
                $gl    = $first->grade_level ?: $first->grade;
                $cg    = $first->class_group ?: 'X';

                return [
                    'name'               => "Kelas " . $gl . " " . $cg,
                    'member_count'       => $members->count(),
                    'performance_points' => $members->avg('performance_points'),
                    'is_fleet'           => true,
                    'grade_level'        => $gl,
                    'class_group'        => $cg,
                ];
            })
            ->sortByDesc('performance_points')
            ->values()
            ->toArray();
    }
}
