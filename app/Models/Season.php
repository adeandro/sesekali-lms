<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'name',
        'status',
        'semester_type',
        'academic_year',
        'started_at',
        'closed_at',
        'closed_by',
        'migration_done',
        'migration_executed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at'  => 'datetime',
        'migration_executed_at' => 'datetime',
    ];

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ── Relationships ───────────────────────────────────────────

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function hallOfFameEntries(): HasMany
    {
        return $this->hasMany(HallOfFame::class);
    }
}
