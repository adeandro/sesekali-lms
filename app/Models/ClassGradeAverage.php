<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassGradeAverage extends Model
{
    use HasFactory;

    protected $table = 'class_grade_averages';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'semester',
        'academic_year',
        'jenjang',
        'class_average',
    ];

    protected $casts = [
        'semester' => 'integer',
        'jenjang' => 'integer',
        'class_average' => 'float',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
