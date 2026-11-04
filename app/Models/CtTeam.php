<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CtTeam extends Model
{
    protected $table = 'ct_teams';

    protected $fillable = [
        'ct_class_id',
        'number',
        'params',
        'code_hash',
        'wrong_count',
        'hint_count',
        'cooldown_until',
        'opened_at',
        'member_names',
        'certificate_no',
    ];

    protected $casts = [
        'params'         => 'array',
        'member_names'   => 'array',
        'cooldown_until' => 'datetime',
        'opened_at'      => 'datetime',
        'wrong_count'    => 'integer',
        'hint_count'     => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function ctClass(): BelongsTo
    {
        return $this->belongsTo(CtClass::class, 'ct_class_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CtAttempt::class, 'ct_team_id')->latest();
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Has the vault been unlocked?
     */
    public function isOpened(): bool
    {
        return $this->opened_at !== null;
    }

    /**
     * Is the team currently on cooldown?
     */
    public function isOnCooldown(): bool
    {
        return $this->cooldown_until !== null && now()->lt($this->cooldown_until);
    }

    /**
     * Remaining cooldown in seconds (0 if not on cooldown).
     */
    public function cooldownSecondsLeft(): int
    {
        if (! $this->isOnCooldown()) {
            return 0;
        }

        return (int) now()->diffInSeconds($this->cooldown_until, false);
    }

    /**
     * Verify a submitted code against the stored hash.
     */
    public function verifyCode(string $code): bool
    {
        $expected = hash_hmac('sha256', $code, config('app.key'));

        return hash_equals($this->code_hash, $expected);
    }
}
