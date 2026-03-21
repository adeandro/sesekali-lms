<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualGrade extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'grade_type',
        'score',
        'semester',
        'academic_year',
        'jenjang',
        'note',
    ];

    protected $casts = [
        'score'    => 'float',
        'semester' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
