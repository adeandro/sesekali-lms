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
        $user = $attempt->student;

        // Fetch all active achievements
        $activeAchievements = Achievement::where('is_active', true)->get();

        foreach ($activeAchievements as $achievement) {
            $type  = $achievement->criteria_type;
            $value = (float) $achievement->criteria_value;

            $shouldAward = match ($type) {
                'exam_count'          => $this->checkExamCount($user, $value),
                'final_score'         => $this->checkFinalScore($attempt, $value),
                'consecutive_pass'    => $this->checkConsecutivePass($user, $value),
                'first_submit'        => $this->checkFirstSubmit($attempt),
                'completion_time_pct' => $this->checkCompletionTimePct($attempt, $value),
                'score_increase'      => $this->checkScoreIncrease($attempt, $value),
                'submission_hour'     => $this->checkSubmissionHour($attempt, $value),
                'avg_score'           => $this->checkAvgScore($user, $value),
                default               => false,
            };

            if ($shouldAward) {
                $this->awardBadge($user, $achievement);
            }
        }

        // Award XP for completion
        $xpReward = 50 + ($attempt->correct_answers ?? 0);
        $this->awardXp($user, $xpReward);
    }

    /**
     * General status-based checks for the dashboard or profile.
     */
    public function checkAchievements(User $user)
    {
        $activeAchievements = Achievement::where('is_active', true)
                                         ->whereIn('criteria_type', ['exam_count', 'avg_score', 'custom_avatar'])
                                         ->get();

        foreach ($activeAchievements as $achievement) {
            $type  = $achievement->criteria_type;
            $value = (float) $achievement->criteria_value;

            $shouldAward = match ($type) {
                'exam_count'    => $this->checkExamCount($user, $value),
                'avg_score'     => $this->checkAvgScore($user, $value),
                'custom_avatar' => $this->checkAvatarAchievement($user),
                default         => false,
            };

            if ($shouldAward) {
                $this->awardBadge($user, $achievement);
            }
        }
    }

    // ─────────────────────────────────────────
    // DYNAMIC CRITERIA CHECKERS
    // ─────────────────────────────────────────

    protected function checkExamCount(User $user, float $targetCount): bool
    {
        $count = $user->examAttempts()->where('status', 'submitted')->count();
        return $count >= $targetCount;
    }

    protected function checkFinalScore(ExamAttempt $attempt, float $targetScore): bool
    {
        return $attempt->final_score >= $targetScore;
    }

    protected function checkConsecutivePass(User $user, float $targetConsecutive): bool
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

    protected function checkFirstSubmit(ExamAttempt $attempt): bool
    {
        $firstAttempt = ExamAttempt::where('exam_session_id', $attempt->exam_session_id)
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'ASC')
            ->first();

        return $firstAttempt && $firstAttempt->id === $attempt->id;
    }

    protected function checkCompletionTimePct(ExamAttempt $attempt, float $targetPct): bool
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

    protected function checkScoreIncrease(ExamAttempt $attempt, float $targetIncrease): bool
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

    protected function checkSubmissionHour(ExamAttempt $attempt, float $targetHour): bool
    {
        if ($attempt->submitted_at) {
            $submittedWIB = $attempt->submitted_at->copy()->setTimezone('Asia/Jakarta');
            return $submittedWIB->hour >= $targetHour;
        }
        return false;
    }

    protected function checkAvgScore(User $user, float $targetAvg): bool
    {
        $avg = $user->examAttempts()->where('status', 'submitted')->avg('final_score');
        return $avg && $avg >= $targetAvg;
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
        $oldLevel = $user->current_level;
        $user->increment('total_exp', $amount);
        
        $newLevel = floor($user->total_exp / 100) + 1;
        
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
            $user->notify(new \App\Notifications\GamificationUnlocked([
                'type' => 'level_up',
                'title' => 'Level Up!',
                'subtitle' => 'Selamat, kamu berhasil mencapai Level ' . $newLevel,
                'icon' => 'fas fa-arrow-up text-indigo-500',
            ]));
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

            // Database Notification
            $user->notify(new \App\Notifications\GamificationUnlocked([
                'type' => 'achievement',
                'title' => 'Piala Diperoleh!',
                'subtitle' => 'Kamu mendapatkan piala: ' . $achievement->title,
                'icon' => 'fas fa-medal text-amber-500',
                'reward' => '+' . $xpReward . ' EXP',
            ]));

            // Process Special Item Unlocks
            if ($achievement->slug === 'social_media_king') {
                $user->notify(new \App\Notifications\GamificationUnlocked([
                    'type' => 'item_unlock',
                    'title' => 'Avatar Terbuka!',
                    'subtitle' => 'Avatar Eksklusif CyberPro telah tersedia di profilmu.',
                    'icon' => 'fas fa-user-astronaut text-purple-500',
                ]));
            }

            if ($achievement->slug === 'night_owl') {
                $user->notify(new \App\Notifications\GamificationUnlocked([
                    'type' => 'item_unlock',
                    'title' => 'Tema Terbuka!',
                    'subtitle' => 'Tema Dark Mode (Midnight) & Volcano kini dapat kamu gunakan.',
                    'icon' => 'fas fa-moon text-blue-500',
                ]));
            }
            
            return true;
        }

        return false;
    }
}
