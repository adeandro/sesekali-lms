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
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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
}
