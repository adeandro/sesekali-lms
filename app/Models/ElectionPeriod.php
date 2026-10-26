<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElectionPeriod extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'status'               => 'draft',
        'allow_blank_vote'     => false,
        'candidates_published' => false,
        'results_published'    => false,
    ];

    protected $fillable = [
        'title', 'description', 'event_date', 'status',
        'candidates_published', 'results_published', 'results_published_at',
        'eligible_class_ids', 'allow_blank_vote',
        'booth_pin_hash', 'booth_session_version',
        'opened_at', 'closed_at', 'collected_cards_count', 'created_by',
    ];

    protected $casts = [
        'event_date'           => 'date',
        'eligible_class_ids'   => 'array',
        'allow_blank_vote'     => 'boolean',
        'candidates_published' => 'boolean',
        'results_published'    => 'boolean',
        'results_published_at' => 'datetime',
        'opened_at'            => 'datetime',
        'closed_at'            => 'datetime',
    ];

    protected $hidden = ['booth_pin_hash'];

    // ── Relations ─────────────────────────────────────────────────────────

    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class, 'period_id')->orderBy('number');
    }

    public function voters(): HasMany
    {
        return $this->hasMany(ElectionVoter::class, 'period_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class, 'period_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ElectionAuditLog::class, 'period_id')->latest();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── State Helpers ─────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isReadyOrOpen(): bool
    {
        return in_array($this->status, ['ready', 'open']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'  => 'Draft',
            'ready'  => 'Siap',
            'open'   => 'Berlangsung',
            'closed' => 'Ditutup',
            default  => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'  => 'gray',
            'ready'  => 'blue',
            'open'   => 'green',
            'closed' => 'red',
            default  => 'gray',
        };
    }

    public function hasPinSet(): bool
    {
        return ! empty($this->booth_pin_hash);
    }
}
