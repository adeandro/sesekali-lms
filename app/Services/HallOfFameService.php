<?php

namespace App\Services;

use App\Models\Season;
use App\Models\User;
use App\Models\HallOfFame;
use Illuminate\Support\Facades\DB;
use App\Services\PointService;

class HallOfFameService
{
    /**
     * Create a snapshot of the top 3 players for the given season.
     */
    public function snapshot(Season $season): void
    {
        // Get Top 3 users based on Academic Performance Points (APP)
        // We use the rank_global which should have been updated before closing
        $topUsers = User::where('role', '=', 'student')
            ->where('status', '=', 'Aktif')
            ->whereNotNull('rank_global')
            ->whereBetween('rank_global', [1, 3])
            ->orderBy('rank_global')
            ->get();

        if ($topUsers->isEmpty()) {
            // Fallback: Calculate if rank_global is missing or everyone is null
            $query = User::where('role', '=', 'student')->where('status', '=', 'Aktif');
            $topUsers = PointService::applyFairLeaderboard($query)->limit(3)->get();
            
            // Assign sequential ranks (1, 2, 3) to fallback results
            foreach ($topUsers as $index => $user) {
                if (!$user->rank_global) {
                    $user->rank_global = $index + 1;
                }
            }
        }

        foreach ($topUsers as $user) {
            try {
                // achievements_snapshot: array of achievement names
                $achievements = $user->achievements()->pluck('name')->toArray();

                // season_history_snapshot: previous Hall of Fame entries
                $history = HallOfFame::where('user_id', $user->id)
                    ->with('season')
                    ->get()
                    ->map(function ($hof) {
                        return [
                            'season_id'         => $hof->season_id,
                            'season_name'       => $hof->season->name ?? 'Unknown',
                            'rank'              => $hof->rank,
                            'app_points_final'  => $hof->app_points_final,
                        ];
                    })->toArray();

                $appPoints = (float)($user->performance_points ?? PointService::getFairScore($user));

                HallOfFame::create([
                    'season_id'               => $season->id,
                    'user_id'                 => $user->id,
                    'rank'                    => $user->rank_global ?? 0, // Fallback if necessary
                    'app_points_final'        => $appPoints,
                    'level_final'             => $user->current_level ?? 1,
                    'achievements_snapshot'   => $achievements,
                    'season_history_snapshot' => $history,
                    'display_name'            => $user->name,
                    'avatar_key'              => $user->custom_avatar,
                    'class_name'              => $user->classroom->name ?? 'N/A',
                    'recorded_at'             => now(),
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("HallOfFame snapshot error for user {$user->id}: " . $e->getMessage());
                throw new \Exception('Gagal menyimpan Hall of Fame: ' . $e->getMessage());
            }
        }
    }
}
