<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotion extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'teacher_id',
        'academic_year',
        'decision',
    ];

    /**
     * Relasi ke Siswa
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke Kelas
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    /**
     * Relasi ke Guru yang menginput
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
