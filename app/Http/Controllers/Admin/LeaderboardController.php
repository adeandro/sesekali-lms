<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoricalWinner;
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

        $cacheKey  = "leaderboard.{$tab}.{$gradeLevel}";

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($gradeLevel, $tab) {
            return $this->buildLeaderboardData($gradeLevel, $tab);
        });

        $seasons     = Season::orderByDesc('start_date')->take(5)->get();
        $hallOfFame  = HistoricalWinner::with(['user', 'season'])
            ->whereIn('rank', [1, 2, 3])
            ->orderByDesc('archived_at')
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
        $winners = HistoricalWinner::with(['user', 'season', 'room'])
            ->orderBy('rank')
            ->orderByDesc('archived_at')
            ->paginate(30);

        return view('admin.gamification.leaderboard.hall-of-fame', compact('winners'));
    }

    // ── Internal Builder ──────────────────────────────────────────────────

    private function buildLeaderboardData(string|int $gradeLevel, string $tab): array
    {
        $query = User::where('role', 'student')->where('status', 'Aktif');

        if ($gradeLevel !== 'all') {
            $query->where('grade_level', (int) $gradeLevel);
        }

        return match($tab) {
            'fleet'  => $this->buildFleetRanking($gradeLevel),
            'career' => $this->buildCareerRanking($query),
            default  => $this->buildLigaRanking($query),           // 'liga' = seasonal
        };
    }

    private function buildLigaRanking($query): array
    {
        return $query->orderByDesc('seasonal_exp')
            ->take(50)
            ->get(['id', 'name', 'grade_level', 'class_group', 'seasonal_exp', 'career_exp',
                   'active_theme_id', 'current_level', 'custom_avatar', 'avatar_upload', 'photo'])
            ->values()
            ->toArray();
    }

    private function buildCareerRanking($query): array
    {
        return $query->orderByDesc('career_exp')
            ->take(50)
            ->get(['id', 'name', 'grade_level', 'class_group', 'seasonal_exp', 'career_exp',
                   'active_theme_id', 'current_level', 'custom_avatar', 'avatar_upload', 'photo'])
            ->values()
            ->toArray();
    }

    private function buildFleetRanking(string|int $gradeLevel): array
    {
        $query = DB::table('users')
            ->where('role', 'student')
            ->where('status', 'Aktif')
            ->select('class_group',
                DB::raw('CONCAT(grade_level, "-", class_group) as fleet_id'),
                DB::raw('grade_level'),
                DB::raw('COUNT(*) as member_count'),
                DB::raw('SUM(seasonal_exp) as total_seasonal_exp'),
                DB::raw('AVG(seasonal_exp) as avg_seasonal_exp'),
                DB::raw('SUM(career_exp) as total_career_exp'),
                DB::raw('AVG(career_exp) as avg_career_exp'))
            ->groupBy('grade_level', 'class_group');

        if ($gradeLevel !== 'all') {
            $query->where('grade_level', (int) $gradeLevel);
        }

        return $query->orderByDesc('avg_seasonal_exp')
            ->get()
            ->values()
            ->toArray();
    }
}
