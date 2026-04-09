<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Get the fair score for a user (Academic Performance Points).
     * Formula: avg_nilai + (sesi*2) + (menang_arena*5) + (partisipasi*1) + consistency_bonus + improvement_bonus
     */
    public static function getFairScore(User $user): float
    {
        $stats = $user->examAttempts()
            ->whereNotNull('submitted_at')
            ->select(DB::raw('AVG(final_score) as avg_score, COUNT(*) as session_count'))
            ->first();

        $arenaStats = \App\Models\BattleParticipant::where('user_id', $user->id)
            ->select(DB::raw('COUNT(*) as participation_count, SUM(CASE WHEN `rank` = 1 THEN 1 ELSE 0 END) as win_count'))
            ->first();

        $sessionCount = (int)data_get($stats, 'session_count', 0);
        $avgScore = (float)data_get($stats, 'avg_score', 0);
        $participationCount = (int)data_get($arenaStats, 'participation_count', 0);
        $winCount = (int)data_get($arenaStats, 'win_count', 0);

        if ($sessionCount === 0 && $participationCount === 0) {
            return 0;
        }

        $consistencyBonus = $user->consecutive_exam_weeks >= 3 ? 3 : 0;
        $improvementBonus = self::calculateImprovementBonus($user);
        $prestigeBonus = $user->prestige_count * 10;

        return round($avgScore + ($sessionCount * 2) + ($winCount * 5) + ($participationCount * 1) + $consistencyBonus + $improvementBonus + $prestigeBonus, 2);
    }

    /**
     * Calculate improvement bonus: +2 if last score > previous score in the same subject.
     */
    protected static function calculateImprovementBonus(User $user): int
    {
        $bonus = 0;
        $subjects = DB::table('exams')
            ->join('exam_attempts', 'exams.id', '=', 'exam_attempts.exam_id')
            ->where('exam_attempts.student_id', $user->id)
            ->whereNotNull('exam_attempts.submitted_at')
            ->select('exams.subject_id')
            ->distinct()
            ->get();

        foreach ($subjects as $subject) {
            $lastTwo = DB::table('exam_attempts')
                ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
                ->where('exams.subject_id', $subject->subject_id)
                ->where('exam_attempts.student_id', $user->id)
                ->whereNotNull('exam_attempts.submitted_at')
                ->orderByDesc('exam_attempts.submitted_at')
                ->limit(2)
                ->get();

            if ($lastTwo->count() === 2) {
                if ($lastTwo[0]->final_score > $lastTwo[1]->final_score) {
                    $bonus += 2;
                }
            }
        }
        return $bonus;
    }

    /**
     * Recalculate APP and update rank for a specific user.
     */
    public function recalculateForUser(User $user): void
    {
        $oldRank = $user->rank_global;
        
        // Final APP calculation is usually done in bulk for ranking, 
        // but for a single user we can update their individual stats.
        // To get the new global rank, we must query everyone.
        
        $season = $user->current_season_id 
            ? \App\Models\Season::find($user->current_season_id) 
            : \App\Models\Season::where('status', 'active')->first();

        if (!$season) return;

        // Using LeaderboardService to update all ranks ensures accuracy
        // instead of guessing a single user's rank relative to others.
        app(LeaderboardService::class)->updateAllRanks($season);
        
        $user->refresh();
        if ($oldRank !== null) {
            $user->rank_delta = $oldRank - $user->rank_global;
            $user->save();
        }
    }

    /**
     * Scope a query to include fair scores (Academic Performance Points).
     */
    public static function applyFairLeaderboard($query)
    {
        $arenaPointsSql = "(
            (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id) * 1 +
            (SELECT COUNT(*) FROM battle_participants WHERE battle_participants.user_id = users.id AND `rank` = 1) * 5
        )";

        $consistencyBonusSql = "IF(users.consecutive_exam_weeks >= 3, 3, 0)";
        
        // Improvement bonus in SQL is extremely heavy; 
        // in a real production app we'd cache this in a 'performance_points' column.
        // For now, we'll keep the core formula and note the improvement bonus
        // is best handled via the recalculate/snapshot services.

        return $query
            ->withAvg(['examAttempts as avg_score' => function($q) {
                $q->whereNotNull('submitted_at');
            }], 'final_score')
            ->withCount(['examAttempts as total_sessions' => function($q) {
                $q->whereNotNull('submitted_at');
            }])
            ->select('users.*')
            ->selectRaw("(COALESCE((SELECT AVG(final_score) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL), 0) + 
                (SELECT COUNT(*) FROM exam_attempts WHERE exam_attempts.student_id = users.id AND submitted_at IS NOT NULL) * 2 + 
                " . $arenaPointsSql . " + " . $consistencyBonusSql . " + (users.prestige_count * 10)) as performance_points")
            ->orderByDesc('performance_points');
    }
}
