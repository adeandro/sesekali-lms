<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PowerupCard extends Model
{
    protected $fillable = [
        'user_id',
        'battle_room_id',
        'season_id',
        'type',
        'status',
        'acquired_at',
        'used_at',
    ];

    protected $casts = [
        'acquired_at' => 'datetime',
        'used_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(BattleRoom::class, 'battle_room_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
