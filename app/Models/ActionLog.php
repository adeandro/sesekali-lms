<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionLog extends Model
{
    protected $fillable = [
        'admin_id',
        'exam_id',
        'student_id',
        'action_type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the admin who performed the action.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the exam related to the action.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student affected by the action.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Log an action to the database.
     */
    public static function logAction(
        int $adminId,
        string $actionType,
        ?string $description = null,
        ?int $examId = null,
        ?int $studentId = null,
        ?array $metadata = null
    ): ActionLog {
        return self::create([
            'admin_id' => $adminId,
            'exam_id' => $examId,
            'student_id' => $studentId,
            'action_type' => $actionType,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    /**
     * Action Types
     */
    public const FORCE_SUBMIT = 'force_submit';
    public const FORCE_LOGOUT = 'force_logout';
    public const SESSION_LOCKED = 'session_locked';
    public const SESSION_REOPENED = 'session_reopened';
    public const VIOLATION_DETECTED = 'violation_detected';
    public const TOKEN_GENERATED = 'token_generated';
    public const TOKEN_REVOKED = 'token_revoked';
}
