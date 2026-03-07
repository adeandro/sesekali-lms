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
        
        $this->checkFirstBlood($user);
        $this->checkPerfectScore($attempt);
        $this->checkUnstoppable($user);
        $this->checkEarlyBird($attempt);
        $this->checkTheFlash($attempt);
        $this->checkComebackKing($attempt);
        $this->checkNightOwl($attempt);
        $this->checkHardWorker($user);
        $this->checkScholarWarrior($user);

        // Award XP for completion
        $xpReward = 50 + ($attempt->correct_answers ?? 0);
        $this->awardXp($user, $xpReward);
    }

    /**
     * General status-based checks for the dashboard or profile.
     */
    public function checkAchievements(User $user)
    {
        $this->checkFirstBlood($user);
        $this->checkAvatarAchievement($user);
        $this->checkHardWorker($user);
        $this->checkScholarWarrior($user);
    }

    /**
     * Check social media king achievement (updated avatar).
     */
    public function checkAvatarAchievement(User $user)
    {
        if ($user->custom_avatar || $user->avatar_upload) {
            if ($this->awardBadge($user, 'social_media_king')) {
                $this->awardXp($user, 20); // Bonus XP for styling
            }
        }
    }

    protected function checkFirstBlood(User $user)
    {
        $completedCount = $user->examAttempts()
            ->where('status', 'submitted')
            ->count();

        if ($completedCount >= 1) {
            $this->awardBadge($user, 'first_blood');
        }
    }

    protected function checkPerfectScore(ExamAttempt $attempt)
    {
        // Nilai 100 murni (before any adjustment if possible, but here we use final_score)
        if ($attempt->final_score >= 100) {
            $this->awardBadge($attempt->student, 'perfect_score');
        }
    }

    protected function checkUnstoppable(User $user)
    {
        // Last 3 attempts passing KKM
        $lastAttempts = $user->examAttempts()
            ->with('exam.subject')
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'DESC')
            ->take(3)
            ->get();

        if ($lastAttempts->count() < 3) return;

        $allPassed = true;
        foreach ($lastAttempts as $attempt) {
            $kkm = $attempt->exam->subject->kkm ?? 75;
            if ($attempt->final_score < $kkm) {
                $allPassed = false;
                break;
            }
        }

        if ($allPassed) {
            $this->awardBadge($user, 'unstoppable');
        }
    }

    protected function checkEarlyBird(ExamAttempt $attempt)
    {
        // First student to submit in their class group for this exam
        $classGroup = $attempt->student->class_group;
        
        $isFirst = !ExamAttempt::where('exam_id', $attempt->exam_id)
            ->where('id', '!=', $attempt->id)
            ->where('status', 'submitted')
            ->whereHas('student', function($q) use ($classGroup) {
                $q->where('class_group', $classGroup);
            })
            ->exists();

        if ($isFirst) {
            $this->awardBadge($attempt->student, 'early_bird');
        }
    }

    protected function checkTheFlash(ExamAttempt $attempt)
    {
        // Lulus KKM with work time < 50% of duration
        $kkm = $attempt->exam->subject->kkm ?? 75;
        if ($attempt->final_score < $kkm) return;

        $durationMinutes = $attempt->exam->duration_minutes;
        if (!$durationMinutes) return;

        $startedAt = $attempt->started_at;
        $submittedAt = $attempt->submitted_at;
        
        if ($startedAt && $submittedAt) {
            $minutesUsed = $startedAt->diffInMinutes($submittedAt);
            if ($minutesUsed < ($durationMinutes / 2)) {
                $this->awardBadge($attempt->student, 'the_flash');
            }
        }
    }

    protected function checkComebackKing(ExamAttempt $attempt)
    {
        // Score increase > 30 points from previous exam
        $previousAttempt = $attempt->student->examAttempts()
            ->where('status', 'submitted')
            ->where('id', '!=', $attempt->id)
            ->where('submitted_at', '<', $attempt->submitted_at)
            ->orderBy('submitted_at', 'DESC')
            ->first();

        if ($previousAttempt) {
            if (($attempt->final_score - $previousAttempt->final_score) > 30) {
                $this->awardBadge($attempt->student, 'comeback_king');
            }
        }
    }

    protected function checkNightOwl(ExamAttempt $attempt)
    {
        // Submit after 21:00
        if ($attempt->submitted_at && $attempt->submitted_at->hour >= 21) {
            $this->awardBadge($attempt->student, 'night_owl');
        }
    }

    protected function checkHardWorker(User $user)
    {
        // 10 completed exams
        $count = $user->examAttempts()->where('status', 'submitted')->count();
        if ($count >= 10) {
            $this->awardBadge($user, 'hard_worker');
        }
    }

    protected function checkScholarWarrior(User $user)
    {
        // Average score > 90
        $avg = $user->examAttempts()->where('status', 'submitted')->avg('final_score');
        if ($avg && $avg > 90) {
            $this->awardBadge($user, 'scholar_warrior');
        }
    }

    public function awardXp(User $user, int $amount)
    {
        $oldLevel = $user->current_level;
        $user->increment('total_exp', $amount);
        
        // Level calculation: 100 XP per level
        $newLevel = floor($user->total_exp / 100) + 1;
        
        if ($newLevel > $oldLevel) {
            $user->update(['current_level' => $newLevel]);
            
            // Celebration logic for theme/avatar unlocks
            $celebrations = [];
            if ($newLevel == 5) $celebrations[] = "🎉 Baru! Tema 'Emerald' kini dapat kamu gunakan!";
            if ($newLevel == 15) $celebrations[] = "🎉 Baru! Tema 'Rose' kini dapat kamu gunakan!";
            if ($newLevel == 20) $celebrations[] = "🎉 Baru! Avatar Spesial 'Cyber Master' kini dapat kamu gunakan!";
            if ($newLevel == 30) $celebrations[] = "🎉 Baru! Tema 'Amber (Gold)' kini dapat kamu gunakan!";

            if (!empty($celebrations)) {
                foreach ($celebrations as $msg) {
                    session()->push('celebrations', $msg);
                }
            }

            // Flash level up to session for SweetAlert2
            session()->push('level_ups', [
                'old' => $oldLevel,
                'new' => $newLevel,
                'title' => $user->level_title
            ]);
        }
    }

    protected function awardBadge(User $user, string $slug): bool
    {
        $achievement = Achievement::where('slug', $slug)->first();
        if (!$achievement) return false;

        // Check if already has it
        if (!$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
            $user->achievements()->attach($achievement->id, [
                'achieved_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            // Store a flag in session to show the unlock modal on next page load
            session()->push('new_achievements', $achievement->toArray());

            // Award XP for achievement
            $this->awardXp($user, 100);
            
            return true;
        }

        return false;
    }
}
