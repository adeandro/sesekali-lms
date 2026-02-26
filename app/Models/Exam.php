<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'subject_id',
        'duration_minutes',
        'total_questions',
        'start_time',
        'end_time',
        'randomize_questions',
        'randomize_options',
        'show_score_after_submit',
        'allow_review_results',
        'status',
        'jenjang',
        'token',
        'token_last_updated',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'token_last_updated' => 'datetime',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_score_after_submit' => 'boolean',
        'allow_review_results' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the subject this exam belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the questions for this exam.
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_question');
    }

    /**
     * Get the exam attempts for this exam.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Get the violations for this exam.
     */
    public function violations(): HasMany
    {
        return $this->hasMany(ExamViolation::class);
    }

    /**
     * Get the tokens for this exam.
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(ExamToken::class);
    }

    /**
     * Get the active sessions for this exam.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    /**
     * Get the count of questions attached to this exam.
     */
    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }

    /**
     * Check if exam can be published.
     */
    public function canPublish(): bool
    {
        $questionCount = $this->questions()->count();
        return $questionCount >= $this->total_questions;
    }

    /**
     * Check if exam can be edited.
     */
    public function canEdit(): bool
    {
        return $this->status !== 'finished';
    }

    /**
     * Check if token needs to be refreshed (20 minutes old).
     */
    public function tokenNeedsRefresh(): bool
    {
        if (!$this->token_last_updated || $this->status !== 'published') {
            return false;
        }

        return $this->token_last_updated->diffInMinutes(now()) >= 20;
    }

    /**
     * Get minutes until next token refresh.
     */
    public function minutesUntilTokenRefresh(): int
    {
        if (!$this->token_last_updated || $this->status !== 'published') {
            return 0;
        }

        $minutesPassed = (int)$this->token_last_updated->diffInMinutes(now());
        $minutesUntilRefresh = 20 - $minutesPassed;

        return max(0, $minutesUntilRefresh);
    }

    /**
     * Get the time when token will be refreshed.
     */
    public function tokenRefreshTime()
    {
        if (!$this->token_last_updated || $this->status !== 'published') {
            return null;
        }

        return $this->token_last_updated->addMinutes(20);
    }
}
