<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'exam_attempt_id',
        'student_id',
        'session_id',
        'device_fingerprint',
        'ip_address',
        'user_agent',
        'started_at',
        'last_heartbeat',
        'current_question',
        'violation_count',
        'is_active',
        'status',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_heartbeat' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the exam this session belongs to.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the exam attempt.
     */
    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Check if session is still active (heartbeat within last 80 seconds and not ended).
     */
    public function isConnected(): bool
    {
        // If session has been explicitly ended, it's not connected
        if ($this->ended_at !== null || !$this->is_active) {
            return false;
        }

        $secondsSinceHeartbeat = $this->last_heartbeat->diffInSeconds(now());
        return $secondsSinceHeartbeat < 80;
    }

    /**
     * Update heartbeat timestamp.
     */
    public function recordHeartbeat(?int $currentQuestion = null, ?int $violationCount = null): void
    {
        $this->update([
            'last_heartbeat' => now(),
            'current_question' => $currentQuestion ?? $this->current_question,
            'violation_count' => $violationCount ?? $this->violation_count,
            'status' => 'active',
        ]);
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentage(): float
    {
        if (!$this->exam) {
            return 0;
        }
        $totalQuestions = $this->exam->total_questions;
        if ($totalQuestions === 0) {
            return 0;
        }
        return ($this->current_question / $totalQuestions) * 100;
    }

    /**
     * End the session.
     */
    public function end(): void
    {
        $this->update([
            'is_active' => false,
            'status' => 'inactive',
            'ended_at' => now(),
        ]);
    }
}
