<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'battle_room_id',
        'description',
        'code',
        'status',
        'redeemed_at',
        'redeemed_by',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(BattleRoom::class, 'battle_room_id');
    }

    public function redeemer()
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
}
