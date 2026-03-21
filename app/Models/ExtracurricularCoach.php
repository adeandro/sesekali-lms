<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtracurricularCoach extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'teacher_id',
        'academic_year',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
