<?php

namespace App\Services;

use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use App\Models\BattleAnswer;
use Illuminate\Support\Facades\DB;
use App\Services\AchievementService;

class BattleService
{
    /**
     * Handle a single answer submission in a battle arena.
     * Calculates streaks, multipliers, EXP, and manages comeback/sudden death impacts.
     */
    public function handleAnswer(BattleParticipant $participant, bool $isCorrect): array
    {
        $room = $participant->room;
        $baseExp = 10;
        $expEarned = 0;
        $newPowerup = null;

        return DB::transaction(function () use ($participant, $room, $isCorrect, $baseExp, &$expEarned, &$newPowerup) {
            if ($isCorrect) {
                // Correct answer logic
                $participant->correct_count += 1;
                $participant->current_streak += 1;

                if ($participant->current_streak > $participant->max_streak) {
                    $participant->max_streak = $participant->current_streak;
                }

                // Power-up acquisition trigger: every 5 correct answers
                if ($participant->correct_count > 0 && $participant->correct_count % 5 === 0) {
                    $newPowerup = app(PowerupService::class)->tryAcquire($participant->user, $room);
                }

                // Determine multiplier
                $multiplier = 1.0;
                if ($participant->current_streak >= 5) {
                    $multiplier = 2.0;
                } elseif ($participant->current_streak >= 3) {
                    $multiplier = 1.5;
                }

                $participant->exp_multiplier = $multiplier;
                $expEarned = (int) ($baseExp * $multiplier);

                // Award XP
                app(AchievementService::class)->awardXp($participant->user, $expEarned);

                // Log EXP
                DB::table('exp_logs')->insert([
                    'user_id' => $participant->user_id,
                    'season_id' => $participant->user->current_season_id,
                    'source' => 'battle',
                    'exp_amount' => $expEarned,
                    'multiplier' => $multiplier,
                    'reference_id' => $room->id,
                    'reference_type' => BattleRoom::class,
                    'earned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Comeback logic check
                if ($participant->comeback_active) {
                    $participant->comeback_questions_left -= 1;
                    if ($participant->comeback_questions_left <= 0) {
                        $participant->comeback_active = false;
                        $participant->comeback_questions_left = 0;
                    }
                }
            } else {
                // Wrong answer logic
                $participant->current_streak = 0;
                $participant->exp_multiplier = 1.0;

                // Shield Power-up check
                if ($participant->active_powerup === 'shield') {
                    $participant->active_powerup = 'none';
                    // HP tidak berkurang, tapi streak tetap reset
                } else {
                    $damage = 1;
                    if ($room->status === 'sudden_death') {
                        $damage = 2;
                    }

                    $participant->hp -= $damage;
                    if ($participant->hp <= 0) {
                        $participant->hp = 0;
                        $participant->status = 'eliminated';
                    }
                }

                // Comeback trigger check
                $leaderCorrectCount = BattleParticipant::where('battle_room_id', $room->id)
                    ->max('correct_count') ?? 0;

                if ($leaderCorrectCount > 0 && ($participant->correct_count / $leaderCorrectCount) < 0.7) {
                    if (!$participant->comeback_active) {
                        $participant->comeback_active = true;
                        $participant->comeback_questions_left = 5;
                    }
                }
            }

            $participant->save();

            return [
                'hp'            => $participant->hp,
                'correct_count' => $participant->correct_count,
                'streak'        => $participant->current_streak,
                'multiplier'    => $participant->exp_multiplier,
                'exp_earned'    => $expEarned,
                'status'        => $participant->status,
                'new_powerup'   => $newPowerup ? [
                    'id'   => $newPowerup->id,
                    'type' => $newPowerup->type,
                ] : null,
            ];
        });
    }

    /**
     * Check if a room should enter sudden death mode.
     */
    public function checkSuddenDeath(BattleRoom $room): bool
    {
        if ($room->status !== 'ongoing') {
            return false;
        }

        $remainingSeconds = $room->remainingSeconds();
        $triggerSeconds = $room->sudden_death_trigger_seconds ?? 120; // Default 2 minutes

        if ($remainingSeconds <= $triggerSeconds) {
            $room->status = 'sudden_death';
            return $room->save();
        }

        return false;
    }
}
