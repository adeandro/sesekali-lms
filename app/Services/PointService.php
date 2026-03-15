<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Get the fair score for a user.
     * Formula: Average Score + (Count of Exams * 2)
     */
    public static function getFairScore(User $user): float
    {
        $stats = $user->examAttempts()
            ->whereNotNull('submitted_at')
            ->select(DB::raw('AVG(final_score) as avg_score, COUNT(*) as session_count'))
            ->first();

        $arenaStats = \App\Models\BattleParticipant::where('user_id', '=', $user->id, 'and')
            ->select(DB::raw('COUNT(*) as participation_count, SUM(CASE WHEN rank = 1 THEN 1 ELSE 0 END) as win_count'))
            ->first();

        $sessionCount = (int)data_get($stats, 'session_count', 0);
        $avgScore = (float)data_get($stats, 'avg_score', 0);
        $participationCount = (int)data_get($arenaStats, 'participation_count', 0);
        $winCount = (int)data_get($arenaStats, 'win_count', 0);

        if ($sessionCount === 0 && $participationCount === 0) {
            return 0;
        }

        // Formula: average_score + (exams * 2) + (arena_wins * 5) + (arena_participation * 1)
        return round($avgScore + ($sessionCount * 2) + ($winCount * 5) + ($participationCount * 1), 2);
    }

    /**
     * Scope a query to include fair scores.
     * Use this for leaderboards to ensure consistency.
     */
    public static function applyFairLeaderboard($query)
    {
        $arenaPointsSql = "(
            (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
            (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5
        )";

        return $query
            ->withAvg(['examAttempts as avg_score' => function($q) {
                $q->whereNotNull('submitted_at');
            }], 'final_score')
            ->withCount(['examAttempts as total_sessions' => function($q) {
                $q->whereNotNull('submitted_at');
            }])
            ->select('users.*')
            ->selectRaw('(COALESCE((SELECT AVG(final_score) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL), 0) + 
                (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 + 
                ' . $arenaPointsSql . ') as performance_points')
            ->orderByDesc('performance_points');
    }
}
