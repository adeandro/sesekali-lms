<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name'];

    /**
     * Get all questions for this subject.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
