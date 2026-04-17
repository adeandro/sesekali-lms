<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use App\Models\ExamAttempt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    /**
     * Check and award achievements after an exam submission.
     */
    public function checkSubmissionAchievements(ExamAttempt $attempt)
    {
        try {
            // Refresh student from DB
            $user = $attempt->student()->first();
            if (!$user) return;

            // Cache active achievements for 1 hour to avoid DB hit every submission
            $activeAchievements = \Illuminate\Support\Facades\Cache::remember('active_achievements_list', 3600, function() {
                return Achievement::where('is_active', true)->get();
            });

            // Pre-calculate user summary stats once per submission session
            $stats = $user->examAttempts()
                ->where('status', 'submitted')
                ->selectRaw('COUNT(*) as total_exams, AVG(final_score) as average_score')
                ->first();

            foreach ($activeAchievements as $achievement) {
                // Avoid awarding if already have it
                if ($user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                    continue;
                }

                $type  = $achievement->criteria_type;
                $value = (float) $achievement->criteria_value;

                $shouldAward = match ($type) {
                    'exam_count'          => ($stats->total_exams ?? 0) >= $value,
                    'final_score'         => $attempt->final_score >= $value,
                    'consecutive_pass'    => $this->checkConsecutivePass($user, $value),
                    'first_submit'        => $this->checkFirstSubmit($attempt),
                    'completion_time_pct' => $this->checkCompletionTimePct($attempt, $value),
                    'score_increase'      => $this->checkScoreIncrease($attempt, $value),
                    'submission_hour'     => $this->checkSubmissionHour($attempt, $value),
                    'avg_score'           => ($stats->average_score ?? 0) >= $value,
                    'arena_win_count'     => $this->checkArenaWinCount($user, $value),
                    default               => false,
                };

                if ($shouldAward) {
                    $this->awardBadge($user, $achievement);
                }
            }

            // Award XP for completion
            $correctCount = $attempt->answers()
                ->where('is_correct', true)
                ->count();
            
            $xpReward = 50 + $correctCount;
            $this->awardXp($user, $xpReward);

        } catch (\Throwable $e) {
            \Log::error('AchievementService::checkSubmissionAchievements error', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't re-throw - don't let gamification errors break exam submission
        }
    }

    /**
     * General status-based checks for the dashboard or profile.
     */
    public function checkAchievements(User $user)
    {
        $activeAchievements = Achievement::where('is_active', true)
                                         ->whereIn('criteria_type', ['exam_count', 'avg_score', 'custom_avatar', 'arena_win_count'])
                                         ->get();

        foreach ($activeAchievements as $achievement) {
            $type  = $achievement->criteria_type;
            $value = (float) $achievement->criteria_value;

            $shouldAward = match ($type) {
                'exam_count'      => $this->checkExamCount($user, $value),
                'avg_score'       => $this->checkAvgScore($user, $value),
                'custom_avatar'   => $this->checkAvatarAchievement($user),
                'arena_win_count' => $this->checkArenaWinCount($user, $value),
                'prestige_count'  => $this->checkPrestigeCount($user, $value),
                default           => false,
            };

            if ($shouldAward) {
                $this->awardBadge($user, $achievement);
            }
        }
    }

    // ─────────────────────────────────────────
    // DYNAMIC CRITERIA CHECKERS
    // ─────────────────────────────────────────

    public function checkExamCount(User $user, float $targetCount): bool
    {
        $count = $user->examAttempts()->where('status', 'submitted')->count();
        return $count >= $targetCount;
    }

    public function checkFinalScore(ExamAttempt $attempt, float $targetScore): bool
    {
        return $attempt->final_score >= $targetScore;
    }

    public function checkConsecutivePass(User $user, float $targetConsecutive): bool
    {
        $attempts = $user->examAttempts()
            ->with(['exam.subject'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'DESC')
            ->take((int)$targetConsecutive)
            ->get();

        if ($attempts->count() < $targetConsecutive) {
            return false;
        }

        foreach ($attempts as $attempt) {
            $kkm = $attempt->exam->subject->kkm ?? 75;
            if ($attempt->final_score < $kkm) {
                return false;
            }
        }

        return true;
    }

    public function checkFirstSubmit(ExamAttempt $attempt): bool
    {
        $firstAttempt = ExamAttempt::where('exam_session_id', $attempt->exam_session_id)
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'ASC')
            ->first();

        return $firstAttempt && $firstAttempt->id === $attempt->id;
    }

    public function checkCompletionTimePct(ExamAttempt $attempt, float $targetPct): bool
    {
        $kkm = $attempt->exam->subject->kkm ?? 75;
        if ($attempt->final_score < $kkm) {
            return false;
        }

        $durationMinutes = $attempt->exam->duration_minutes;
        if (!$durationMinutes) {
            return false;
        }

        $startedAt   = $attempt->started_at;
        $submittedAt = $attempt->submitted_at;
        
        if ($startedAt && $submittedAt) {
            $minutesUsed = $startedAt->diffInMinutes($submittedAt);
            $pctUsed     = ($minutesUsed / $durationMinutes) * 100;
            return $pctUsed <= $targetPct;
        }

        return false;
    }

    public function checkScoreIncrease(ExamAttempt $attempt, float $targetIncrease): bool
    {
        $previousAttempt = $attempt->student->examAttempts()
            ->where('status', 'submitted')
            ->where('id', '!=', $attempt->id)
            ->where('submitted_at', '<', $attempt->submitted_at)
            ->orderBy('submitted_at', 'DESC')
            ->first();

        if ($previousAttempt) {
            return ($attempt->final_score - $previousAttempt->final_score) >= $targetIncrease;
        }

        return false;
    }

    public function checkSubmissionHour(ExamAttempt $attempt, float $targetHour): bool
    {
        if ($attempt->submitted_at) {
            $submittedWIB = $attempt->submitted_at->copy()->setTimezone('Asia/Jakarta');
            return $submittedWIB->hour >= $targetHour;
        }
        return false;
    }

    public function checkAvgScore(User $user, float $targetAvg): bool
    {
        $avg = $user->examAttempts()->where('status', 'submitted')->avg('final_score');
        return $avg && $avg >= $targetAvg;
    }

    public function checkArenaWinCount(User $user, float $targetCount): bool
    {
        // One way to count is checking BattleParticipant where rank = 1
        $count = \App\Models\BattleParticipant::where('user_id', $user->id)
            ->where('rank', 1)
            ->count();
        return $count >= $targetCount;
    }

    public function checkPrestigeCount(User $user, float $targetCount): bool
    {
        return $user->prestige_count >= $targetCount;
    }

    public function checkAvatarAchievement(User $user)
    {
        $hasCustomAvatar = false;
        
        if ($user->avatar_upload) {
            $hasCustomAvatar = true;
        } elseif ($user->custom_avatar && !$user->is_avatar_seed && !str_starts_with($user->custom_avatar, 'avatars/multiavatar/')) {
            $hasCustomAvatar = true;
        }

        if ($hasCustomAvatar) {
            $achievement = Achievement::where('slug', 'social_media_king')->first();
            if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                $this->awardBadge($user, $achievement);
            }
        }
    }

    // ─────────────────────────────────────────
    // AWARDS & XP
    // ─────────────────────────────────────────

    public function awardXp(User $user, int $amount)
    {
        // Refresh user from DB to avoid stale data
        $user = $user->fresh();
        if (!$user) return;

        $oldLevel = $user->current_level ?? 1;

        // Increment EXP
        $user->increment('total_exp', $amount);
        $user->increment('exp_total_alltime', $amount);
        
        // Refresh again after increment to get updated values
        $user = $user->fresh();
        
        $newLevel = (int) floor($user->total_exp / 100) + 1;
        
        if ($newLevel > $oldLevel) {
            $user->update(['current_level' => $newLevel]);
            
            $celebrations = [];
            if ($newLevel == 5)  $celebrations[] = "🎉 Baru! Tema 'Emerald' kini dapat kamu gunakan!";
            if ($newLevel == 15) $celebrations[] = "🎉 Baru! Tema 'Volcano' kini dapat kamu gunakan!";
            if ($newLevel == 20) $celebrations[] = "🎉 Baru! Avatar Spesial 'Cyber Master' kini dapat kamu gunakan!";
            if ($newLevel == 25) $celebrations[] = "🎉 Baru! Tema 'Rose' kini dapat kamu gunakan!";
            if ($newLevel == 35) $celebrations[] = "🎉 Baru! Tema 'Amber (Gold)' kini dapat kamu gunakan!";
            if ($newLevel == 45) $celebrations[] = "🎉 Baru! Tema 'Midnight' kini dapat kamu gunakan!";

            if (!empty($celebrations)) {
                foreach ($celebrations as $msg) {
                    session()->push('celebrations', $msg);
                }
            }

            // Fire Celebration Popup payload
            session()->flash('celebration', [
                'type' => 'level_up',
                'title' => 'LEVEL UP!',
                'subtitle' => 'Kamu Naik ke Level ' . $newLevel,
                'icon' => 'fas fa-angle-double-up',
                'reward' => 'Peringkat: ' . $user->level_title,
            ]);

            // Database Notification
            $user->notify(new \App\Notifications\GamificationNotification(
                'level_up',
                'Level Up! 🎉',
                'Kamu berhasil mencapai Level ' . $newLevel . ($user->level_title ? ' — ' . $user->level_title : ''),
                'fas fa-arrow-up',
                $user->level_title ? 'Peringkat: ' . $user->level_title : null
            ));
        }
    }

    protected function awardBadge(User $user, Achievement $achievement): bool
    {
        if (!$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
            $user->achievements()->attach($achievement->id, [
                'achieved_at' => Carbon::now(),
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now()
            ]);
            
            $xpReward = $achievement->xp_reward ?? 100;
            $this->awardXp($user, $xpReward);
            
            // Generate Celebration Payload
            session()->flash('celebration', [
                'type' => 'achievement',
                'title' => 'ACHIEVEMENT UNLOCKED!',
                'subtitle' => $achievement->title,
                'icon' => 'fas fa-medal',
                'reward' => '+' . $xpReward . ' EXP',
            ]);

            // Database Notification — Achievement
            $user->notify(new \App\Notifications\GamificationNotification(
                'achievement',
                'Achievement Unlocked! 🏅',
                $achievement->title,
                $achievement->icon ?? 'fas fa-medal',
                '+' . $xpReward . ' EXP'
            ));

            // Process Special Item Unlocks
            if ($achievement->slug === 'social_media_king') {
                $user->notify(new \App\Notifications\GamificationNotification(
                    'item',
                    'Avatar Terbuka!',
                    'Avatar Eksklusif CyberPro telah tersedia di profilmu.',
                    'fas fa-user-astronaut',
                    null
                ));
            }

            if ($achievement->slug === 'night_owl') {
                $user->notify(new \App\Notifications\GamificationNotification(
                    'theme',
                    'Tema Terbuka!',
                    'Tema Dark Mode (Midnight) & Volcano kini dapat kamu gunakan.',
                    'fas fa-moon',
                    null
                ));
            }
            
            return true;
        }

        return false;
    }
}
