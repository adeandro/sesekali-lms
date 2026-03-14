<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalWinner extends Model
{
    protected $fillable = [
        'season_id',
        'battle_room_id',
        'user_id',
        'rank',
        'battle_room_name',
        'battle_mode',
        'career_exp_snapshot',
        'seasonal_exp_snapshot',
        'theme_awarded',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────────

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(BattleRoom::class, 'battle_room_id');
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function rankLabel(): string
    {
        return match ((int) $this->rank) {
            1  => '🥇 Juara 1',
            2  => '🥈 Juara 2',
            3  => '🥉 Juara 3',
            default => "Peringkat #{$this->rank}",
        };
    }

    public function themeLabel(): string
    {
        return match ($this->theme_awarded) {
            'legendary-golden' => 'Legendary',
            'elite-silver'     => 'Elite',
            'master-bronze'    => 'Master',
            'survivor-common'  => 'Survivor',
            default            => '-',
        };
    }
}
