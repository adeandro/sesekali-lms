<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'title',
        'description',
        'lore_text',
        'icon',
        'icon_path',
        'color',
        'criteria_type',
        'criteria_value',
        'xp_reward',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'achievement_user')
            ->withPivot('achieved_at')
            ->withTimestamps();
    }

    /**
     * Get the display title (falls back to name).
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: $this->name;
    }

    /**
     * Get icon URL if uploaded, else null.
     */
    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path ? asset('storage/' . $this->icon_path) : null;
    }
}
