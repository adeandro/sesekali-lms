<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExtracurricular extends Model
{
    use HasFactory;

    protected $table = 'student_extracurriculars';

    protected $fillable = [
        'student_id',
        'class_id',
        'teacher_id',
        'semester',
        'academic_year',
        'name',
        'grade',
        'note',
    ];

    protected $casts = [
        'semester' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
