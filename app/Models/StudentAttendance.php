<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $table = 'student_attendances';

    protected $fillable = [
        'student_id',
        'class_id',
        'semester',
        'academic_year',
        'sick_days',
        'permit_days',
        'alpha_days',
    ];

    protected $casts = [
        'semester' => 'integer',
        'sick_days' => 'integer',
        'permit_days' => 'integer',
        'alpha_days' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}
