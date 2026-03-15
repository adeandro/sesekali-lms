<?php

namespace App\Services;

use App\Models\Season;
use App\Models\User;
use App\Models\PowerupCard;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;
use App\Services\HallOfFameService;
use App\Services\LeaderboardService;

class SeasonService
{
    protected $hofService;
    protected $leaderboardService;

    public function __construct(HallOfFameService $hofService, LeaderboardService $leaderboardService)
    {
        $this->hofService = $hofService;
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Close the current season and perform all reset logic.
     */
    public function closeSeason(Season $season, User $admin): void
    {
        DB::transaction(function () use ($season, $admin) {
            // 1. Update global ranks for all students before snapshot
            $this->leaderboardService->updateAllRanks($season);

            // 2. Update season status
            $season->update([
                'status'    => 'closed',
                'closed_at' => now(),
                'closed_by' => $admin->id,
            ]);

            // 3. Hall of Fame Snapshot
            $this->hofService->snapshot($season);

            // 3. Send announcement to all students
            Announcement::create([
                'title'      => "Season {$season->name} Berakhir!",
                'content'    => "Season {$season->name} telah berakhir. Selamat kepada para juara! Cek Hall of Fame untuk melihat pemenang.",
                'user_id'    => $admin->id,
                'target_role' => 'student',
                'is_active'  => true,
            ]);

            // 4. Reset User Seasonal Stats (preserving achievements & all-time EXP)
            User::where('role', '=', 'student')
                ->where('current_season_id', '=', $season->id)
                ->update([
                    'total_exp'     => 0,
                    'current_level' => 1,
                    'rank_global'   => null,
                    'rank_delta'    => 0,
                ]);

            // 5. Expire available powerup cards
            PowerupCard::where('season_id', $season->id)
                ->where('status', 'available')
                ->update([
                    'status' => 'expired'
                ]);
        });
    }

    /**
     * Start a new active season.
     */
    public function startNewSeason(array $data, User $admin): Season
    {
        return DB::transaction(function () use ($data, $admin) {
            // 1. Close current active season if exists
            $activeSeason = Season::where('status', '=', 'active')->first();
            if ($activeSeason) {
                $this->closeSeason($activeSeason, $admin);
            }

            // 2. Create the new season
            $season = Season::create(array_merge($data, [
                'status'     => 'active',
                'started_at' => now(),
            ]));

            // 3. Update all students to current season
            User::where('role', 'student')->update([
                'current_season_id' => $season->id
            ]);

            return $season;
        });
    }

    /**
     * Activate a specific season (re-activation).
     */
    public function activateSeason(Season $season, User $admin): void
    {
        DB::transaction(function () use ($season, $admin) {
            // 1. Close current active season if exists
            $activeSeason = Season::where('status', '=', 'active')->where('id', '!=', $season->id)->first();
            if ($activeSeason) {
                $this->closeSeason($activeSeason, $admin);
            }

            // 2. Activate this season
            $season->update([
                'status'    => 'active',
                'closed_at' => null,
                'closed_by' => null,
            ]);

            // 3. Update all students to current season
            User::where('role', 'student')->update([
                'current_season_id' => $season->id
            ]);

            // 4. Force rank update for the newly activated season context
            $this->leaderboardService->updateAllRanks($season);
        });
    }
}
