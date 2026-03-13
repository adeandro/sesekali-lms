<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BattleAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'battle_participant_id', 'question_id',
        'chosen_option', 'is_correct', 'hp_delta', 'answered_at',
    ];

    protected $casts = [
        'is_correct'  => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(BattleParticipant::class, 'battle_participant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
