<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject_id',
        'jenjang',
        'topic',
        'difficulty_level',
        'question_type',
        'question_text',
        'question_image',
        'option_a',
        'option_a_image',
        'option_b',
        'option_b_image',
        'option_c',
        'option_c_image',
        'option_d',
        'option_d_image',
        'option_e',
        'option_e_image',
        'correct_answer',
        'explanation',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the subject for this question.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
