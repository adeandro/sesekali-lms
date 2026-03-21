<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Extracurricular extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    public function coaches()
    {
        return $this->hasMany(ExtracurricularCoach::class);
    }

    public function members()
    {
        return $this->hasMany(ExtracurricularMember::class);
    }

    public function sessions()
    {
        return $this->hasMany(ExtracurricularSession::class);
    }

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function studentExtracurriculars()
    {
        return $this->hasMany(StudentExtracurricular::class);
    }
}
