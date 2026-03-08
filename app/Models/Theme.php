<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'primary_color',
        'secondary_color',
        'glow_color',
        'bg_color',
        'text_color',
        'dark_color',
        'surface_color',
        'is_unlocked_by_default',
        'is_active',
        'min_level',
        'required_achievement_id',
    ];

    protected $casts = [
        'is_unlocked_by_default' => 'boolean',
        'is_active' => 'boolean',
        'min_level' => 'integer',
    ];

    public function requiredAchievement()
    {
        return $this->belongsTo(\App\Models\Achievement::class, 'required_achievement_id');
    }
}
