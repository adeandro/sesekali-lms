<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtracurricularMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'student_id',
        'academic_year',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
