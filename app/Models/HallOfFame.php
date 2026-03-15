<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallOfFame extends Model
{
    protected $table = 'hall_of_fame';

    protected $fillable = [
        'season_id',
        'user_id',
        'rank',
        'app_points_final',
        'level_final',
        'achievements_snapshot',
        'season_history_snapshot',
        'display_name',
        'avatar_key',
        'class_name',
        'recorded_at',
    ];

    protected $casts = [
        'achievements_snapshot'   => 'array',
        'season_history_snapshot' => 'array',
        'app_points_final'        => 'decimal:2',
        'recorded_at'             => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
