<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'category', 'body', 
        'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function letters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Letter::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
