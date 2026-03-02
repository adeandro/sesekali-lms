<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'kkm'];

    /**
     * Get all questions for this subject.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the teachers for this subject.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get all exams for this subject.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
