<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CtAttempt extends Model
{
    protected $table = 'ct_attempts';

    public $timestamps = false;  // only created_at, managed manually

    protected $fillable = [
        'ct_team_id',
        'member_names',
        'submitted_code',
        'is_correct',
        'ip',
        'created_at',
    ];

    protected $casts = [
        'member_names' => 'array',
        'is_correct'   => 'boolean',
        'created_at'   => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function team(): BelongsTo
    {
        return $this->belongsTo(CtTeam::class, 'ct_team_id');
    }
}
