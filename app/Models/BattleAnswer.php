<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BattleAnswer extends Model
{
    protected $fillable = [
        'battle_room_id', 'battle_participant_id',
        'question_id', 'q_index',
        'chosen_option', 'is_correct',
        'score_earned', 'answered_at',
    ];

    protected $casts = [
        'is_correct'  => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(BattleParticipant::class,
            'battle_participant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
