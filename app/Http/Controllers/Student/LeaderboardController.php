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
            $myRank = User::where('role', 'student')
                ->where('status', 'Aktif')
                ->where('grade_level', $gradeLevel)
                ->where('seasonal_exp', '>', $user->seasonal_exp)
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

        return match ($tab) {
            'fleet'  => $this->buildFleet($gradeLevel),
            'career' => $query->orderByDesc('career_exp')->take(50)
                ->get(['id','name','grade_level','class_group','career_exp','active_theme_id','current_level','custom_avatar','photo'])
                ->values()->toArray(),
            default  => $query->orderByDesc('seasonal_exp')->take(50) // 'liga'
                ->get(['id','name','grade_level','class_group','seasonal_exp','active_theme_id','current_level','custom_avatar','photo'])
                ->values()->toArray(),
        };
    }

    private function buildFleet(?int $gradeLevel): array
    {
        $query = DB::table('users')
            ->where('role', 'student')
            ->where('status', 'Aktif')
            ->select(
                'class_group',
                'grade_level',
                DB::raw('CONCAT(grade_level, "-", COALESCE(class_group,"?")) as fleet_id'),
                DB::raw('COUNT(*) as member_count'),
                DB::raw('AVG(seasonal_exp) as avg_seasonal_exp'),
                DB::raw('SUM(seasonal_exp) as total_seasonal_exp'),
                DB::raw('AVG(career_exp) as avg_career_exp')
            )
            ->groupBy('grade_level', 'class_group');

        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }

        return $query->orderByDesc('avg_seasonal_exp')->get()->values()->toArray();
    }
}
