<?php

namespace App\Services;

use App\Models\Season;
use App\Models\User;
use App\Models\LeaderboardSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    /**
     * Periodic snapshot of the top 100 users.
     */
    public function snapshotPeriodic(string $type, Carbon $start, Carbon $end): void
    {
        $activeSeason = Season::where('status', 'active')->first();
        if (!$activeSeason) return;

        // Get Top 100 users by Academic Performance Points (APP)
        $query = User::where('role', '=', 'student')
            ->where('status', '=', 'Aktif');
            
        $topUsers = PointService::applyFairLeaderboard($query)
            ->limit(100)
            ->get();

        foreach ($topUsers as $user) {
            LeaderboardSnapshot::updateOrCreate(
                [
                    'season_id'    => $activeSeason->id,
                    'user_id'      => $user->id,
                    'period_type'  => $type,
                    'period_start' => $start->format('Y-m-d'),
                    'period_end'   => $end->format('Y-m-d'),
                ],
                [
                    'app_points' => $user->performance_points,
                    'rank'       => $user->rank_global ?? PHP_INT_MAX,
                    'snapped_at' => now(),
                ]
            );
        }
    }

    /**
     * Recalculate global ranks for all students in the active season.
     */
    public function updateAllRanks(Season $season): void
    {
        $students = User::where('role', '=', 'student')
            ->where('status', '=', 'Aktif')
            ->get();

        $scores = [];
        foreach ($students as $student) {
            $scores[$student->id] = PointService::getFairScore($student);
        }

        // Sort by score DESC
        arsort($scores);

        $rank = 1;
        foreach ($scores as $userId => $score) {
            $user = User::find($userId);
            if (!$user) continue;

            $oldRank = $user->rank_global;
            $user->rank_global = $rank;
            
            if ($oldRank !== null) {
                $user->rank_delta = $oldRank - $rank;
            } else {
                $user->rank_delta = 0;
            }

            $user->save();
            $rank++;
        }
    }
}
