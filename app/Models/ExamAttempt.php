<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'submitted_at',
        'status',
        'violation_count',
        'score_mc',
        'score_essay',
        'final_score',
        'token',
        'heartbeat_last_seen',
        'is_session_locked',
        'force_submitted',
        'force_submit_reason',
        'force_submitted_at',
        'adjusted_score',
        'is_adjusted',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score_mc' => 'float',
        'score_essay' => 'float',
        'final_score' => 'float',
        'adjusted_score' => 'float',
        'is_adjusted' => 'boolean',
    ];

    /**
     * Get the exam this attempt belongs to.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the subject for this attempt (via exam).
     */
    public function subject()
    {
        return $this->exam->subject();
    }

    /**
     * Get the student who took this exam.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get all answers for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'attempt_id');
    }

    /**
     * Get the exam session for this attempt.
     */
    public function session(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExamSession::class, 'exam_attempt_id');
    }

    /**
     * Get violations for this attempt.
     */
    public function violations(): HasMany
    {
        return $this->hasMany(ExamViolation::class, 'exam_id', 'exam_id')
                    ->where('user_id', $this->student_id);
    }

    /**
     * Check if attempt is still in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if attempt is submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Get remaining time in minutes.
     */
    public function getRemainingTimeMinutes(): float
    {
        $elapsed = $this->started_at->diffInMinutes(now(), absolute: true);
        $remaining = $this->exam->duration_minutes - $elapsed;
        return max(0, $remaining);
    }

    /**
     * Check if time has expired.
     */
    public function hasTimeExpired(): bool
    {
        return $this->getRemainingTimeMinutes() === 0;
    }

    /**
     * Get Nilai Akhir status (TUNTAS/REMIDIAL)
     */
    public function getStatusKelulusanAttribute(): string
    {
        $kkm = $this->exam->subject->kkm ?? 75;
        return $this->final_score >= $kkm ? 'TUNTAS' : 'REMIDIAL';
    }

    /**
     * Check if student passed.
     */
    public function isPassed(): bool
    {
        $kkm = $this->exam->subject->kkm ?? 75;
        return $this->final_score >= $kkm;
    }
}
