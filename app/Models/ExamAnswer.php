<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $table = 'exam_answers';

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_answer',
        'selected_answer_text',
        'correct_answer_text',
        'essay_answer',
        'is_correct',
        'essay_score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'essay_score' => 'decimal:2',
    ];

    /**
     * Get the attempt this answer belongs to.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    /**
     * Get the question being answered.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Check if this is a multiple choice answer.
     */
    public function isMultipleChoice(): bool
    {
        return $this->question->question_type === 'multiple_choice';
    }

    /**
     * Check if this question is essay.
     */
    public function isEssay(): bool
    {
        return $this->question->question_type === 'essay';
    }

    /**
     * Check if answer is correct (for MC only).
     */
    public function isAnswerCorrect(): bool
    {
        if (!$this->isMultipleChoice()) {
            return false;
        }

        return $this->selected_answer === $this->question->correct_answer;
    }
}
