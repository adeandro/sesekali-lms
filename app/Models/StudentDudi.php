<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDudi extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'teacher_id',
        'semester',
        'academic_year',
        'activity_name',
        'institution_name',
        'institution_address',
        'period',
        'grade',
        'sort_order',
    ];

    protected $casts = [
        'semester' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Relasi ke siswa.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke guru (penilai/pembimbing).
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relasi ke kelas.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}
