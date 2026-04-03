<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BattleParticipant extends Model
{
    protected $fillable = [
        'battle_room_id', 'user_id',
        'group_label', 'total_score',
        'correct_count', 'wrong_count',
        'rank', 'joined_at', 'finished_at',
    ];

    protected $casts = [
        'joined_at'   => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(BattleRoom::class,
            'battle_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(BattleAnswer::class);
    }
}
