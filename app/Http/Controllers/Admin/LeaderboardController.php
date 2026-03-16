<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HallOfFame;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    // Cache TTL: 1 jam
    private const CACHE_TTL = 3600;

    // ── Admin: Global Leaderboard Index ───────────────────────────────────

    public function index()
    {
        $gradeLevel = request('grade', 'all'); // filter
        $tab        = request('tab', 'liga');   // liga | fleet | career

        $cacheKey  = "leaderboard.v3.{$tab}.{$gradeLevel}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($gradeLevel, $tab) {
            return $this->buildLeaderboardData($gradeLevel, $tab);
        });

        $seasons     = Season::orderByDesc('started_at')->take(5)->get();
        $hallOfFame  = HallOfFame::with(['user', 'season'])
            ->whereIn('rank', [1, 2, 3])
            ->orderByDesc('recorded_at')
            ->take(20)
            ->get();

        return view('admin.gamification.leaderboard.index', compact(
            'data', 'gradeLevel', 'tab', 'seasons', 'hallOfFame'
        ));
    }

    // ── Refresh Cache (POST) ──────────────────────────────────────────────

    public function refreshCache()
    {
        $patterns = ['liga', 'fleet', 'career'];
        $grades   = ['all', 10, 11, 12];

        foreach ($patterns as $tab) {
            foreach ($grades as $grade) {
                Cache::forget("leaderboard.{$tab}.{$grade}");
            }
        }
        // Also clear student-side cache
        Cache::forget('leaderboard.student.*');

        return back()->with('success', 'Cache leaderboard berhasil di-refresh! ⚡');
    }

    // ── Hall of Fame Full Page ─────────────────────────────────────────────

    public function hallOfFame()
    {
        $winners = HallOfFame::with(['user', 'season'])
            ->orderBy('rank')
            ->orderByDesc('recorded_at')
            ->paginate(30);

        return view('admin.gamification.leaderboard.hall-of-fame', compact('winners'));
    }

    private function buildLeaderboardData(string|int $gradeLevel, string $tab): array
    {
        $query = User::where('role', '=', 'student')->where('status', '=', 'Aktif');

        if ($gradeLevel !== 'all') {
            $query->where(function($q) use ($gradeLevel) {
                $q->where('grade_level', (int) $gradeLevel)
                  ->orWhere('grade', (string) $gradeLevel);
            });
        }

        return match($tab) {
            'fleet'  => $this->buildFleetRanking($gradeLevel),
            'career' => $this->buildCareerRanking($query),
            default  => $this->buildLigaRanking($query),           // 'liga' = seasonal
        };
    }

    private function buildLigaRanking($query): array
    {
        // For Liga, we use Seasonal Metrics (APP + Seasonal EXP)
        return $query->select('*')
            ->selectRaw('((SELECT COALESCE(AVG(final_score), 0) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) + 
                (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 +
                ((SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
                 (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5)) as performance_points')
            ->orderByDesc('performance_points')
            ->take(50)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'grade_level' => $u->grade_level ?: $u->grade,
                'class_group' => $u->class_group,
                'seasonal_exp' => $u->seasonal_exp,
                'career_exp' => $u->career_exp,
                'performance_points' => $u->performance_points,
                'active_theme_id' => $u->active_theme_id,
                'current_level' => $u->current_level,
            ])
            ->toArray();
    }

    private function buildCareerRanking($query): array
    {
        $currentYear = 2026; // Based on migration year context
        
        $students = $query->orderByDesc('career_exp')
            ->get()
            ->map(function($u) use ($currentYear) {
                // Calculate "Angkatan"
                // If Grade 12 in 2026 -> 2026
                // If Grade 11 in 2026 -> 2027
                // If Grade 10 in 2026 -> 2028
                $angkatan = $u->alumni_year;
                if (!$angkatan && $u->grade_level) {
                    $angkatan = $currentYear + (12 - $u->grade_level);
                }
                $angkatanLabel = $angkatan ? "Angkatan " . $angkatan : "Angkatan Tidak Diketahui";

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'grade_level' => $u->grade_level ?: $u->grade,
                    'class_group' => $u->class_group,
                    'seasonal_exp' => $u->seasonal_exp,
                    'career_exp' => $u->career_exp,
                    'performance_points' => $u->career_exp / 10,
                    'active_theme_id' => $u->active_theme_id,
                    'current_level' => $u->current_level,
                    'group_label' => "Kelas " . ($u->grade_level ?: $u->grade),
                ];
            });

        // Group by grade and ensure sort order 12, 11, 10
        return $students->groupBy('group_label')
            ->map(fn($group) => $group->values())
            ->sortByDesc(fn($group, $key) => (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT))
            ->toArray();
    }

    private function buildFleetRanking(string|int $gradeLevel): array
    {
        $query = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->select('*')
            ->selectRaw('((SELECT COALESCE(AVG(final_score), 0) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) + 
                (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 +
                ((SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
                 (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5)) as performance_points');

        if ($gradeLevel !== 'all') {
            $query->where(function($q) use ($gradeLevel) {
                $q->where('grade_level', (int) $gradeLevel)
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
                    'grade_level'        => $gl,
                    'class_group'        => $cg,
                    'fleet_id'           => $gl . '-' . $cg,
                    'member_count'       => $members->count(),
                    'performance_points' => $members->avg('performance_points'),
                    'total_seasonal_exp' => $members->sum('seasonal_exp'),
                    'avg_seasonal_exp'   => $members->avg('seasonal_exp'),
                ];
            })
            ->sortByDesc('performance_points')
            ->values()
            ->toArray();
    }
}
