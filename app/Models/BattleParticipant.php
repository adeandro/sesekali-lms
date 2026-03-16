<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BattleParticipant extends Model
{
    protected $fillable = [
        'battle_room_id', 'user_id', 'class_id', 'group_label',
        'hp', 'correct_count', 'wrong_count', 'current_question_index',
        'status', 'rank', 'disqualified_at', 'finished_at', 'last_seen_at',
        'current_streak', 'max_streak', 'exp_multiplier',
        'comeback_active', 'comeback_questions_left',
        'active_powerup', 'powerup_used_count',
    ];

    protected $casts = [
        'battle_room_id'  => 'integer',
        'user_id'         => 'integer',
        'disqualified_at' => 'datetime',
        'finished_at'     => 'datetime',
        'last_seen_at'    => 'datetime',
        'exp_multiplier'  => 'decimal:1',
        'comeback_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────

    public function room(): BelongsTo
    {
        return $this->belongsTo(BattleRoom::class, 'battle_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(BattleAnswer::class);
    }

    // ── Helpers ───────────────────────────────────

    /** Apply HP damage (negative delta). Handles disqualification. */
    public function applyHpDelta(int $delta): void
    {
        $this->hp = max(0, $this->hp + $delta);
        if ($this->hp === 0) {
            $this->status = 'disqualified';
            $this->disqualified_at = now();
        }
        $this->save();
    }

    /** Progress percentage as an individual for race track display */
    public function progressPercent(int $totalQuestions): float
    {
        if (!$totalQuestions) return 0;
        return round($this->correct_count / $totalQuestions * 100, 1);
    }
}
