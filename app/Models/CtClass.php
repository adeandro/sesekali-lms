<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CtClass extends Model
{
    protected $table = 'ct_classes';

    protected $fillable = [
        'name',
        'opens_at',
        'closes_at',
        'is_active',
    ];

    protected $casts = [
        'opens_at'  => 'datetime',
        'closes_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function teams(): HasMany
    {
        return $this->hasMany(CtTeam::class, 'ct_class_id')->orderBy('number');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /**
     * Is the class currently open (active, within time window)?
     */
    public function isOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    /**
     * Next opening time, or null if currently open / no schedule.
     */
    public function nextOpenAt(): ?\Illuminate\Support\Carbon
    {
        if (! $this->opens_at || now()->gte($this->opens_at)) {
            return null;
        }

        return $this->opens_at;
    }
}
