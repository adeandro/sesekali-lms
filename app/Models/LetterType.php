<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'sort_order'];

    public function templates(): HasMany
    {
        return $this->hasMany(LetterTemplate::class);
    }
}
