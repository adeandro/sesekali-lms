<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'name',
        'semester_type',
        'academic_year',
        'start_date',
        'end_date',
        'is_active',
        'reset_done',
        'migration_done',
        'reset_executed_at',
        'migration_executed_at',
    ];

    protected $casts = [
        'start_date'             => 'date',
        'end_date'               => 'date',
        'is_active'              => 'boolean',
        'reset_done'             => 'boolean',
        'migration_done'         => 'boolean',
        'reset_executed_at'      => 'datetime',
        'migration_executed_at'  => 'datetime',
    ];

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now()->toDateString())
                     ->where('end_date', '>=', now()->toDateString());
    }

    // ── Relationships ───────────────────────────────────────────

    public function historicalWinners(): HasMany
    {
        return $this->hasMany(HistoricalWinner::class);
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function isEnded(): bool
    {
        return now()->toDateString() > $this->end_date->toDateString();
    }

    public function daysRemaining(): int
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function semesterLabel(): string
    {
        return ucfirst($this->semester_type) . ' ' . $this->academic_year;
    }
}
