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

        if (!$stats) {
            return 0;
        }

        $sessionCount = (int)data_get($stats, 'session_count', 0);
        $avgScore = (float)data_get($stats, 'avg_score', 0);

        if ($sessionCount === 0) {
            return 0;
        }

        return round($avgScore + ($sessionCount * 2), 2);
    }

    /**
     * Scope a query to include fair scores.
     * Use this for leaderboards to ensure consistency.
     */
    public static function applyFairLeaderboard($query)
    {
        return $query
            ->withAvg(['examAttempts as avg_score' => function($q) {
                $q->whereNotNull('submitted_at');
            }], 'final_score')
            ->withCount(['examAttempts as total_sessions' => function($q) {
                $q->whereNotNull('submitted_at');
            }])
            ->select('users.*')
            ->selectRaw('(COALESCE((SELECT AVG(final_score) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL), 0) + 
                (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2) as performance_points')
            ->orderByDesc('performance_points');
    }
}
