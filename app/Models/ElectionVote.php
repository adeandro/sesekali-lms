<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionVote extends Model
{
    /**
     * UUID v4 PK, no timestamps, no voter linkage — per security spec.
     */
    public $timestamps  = false;
    public $incrementing = false;
    protected $keyType  = 'string';

    protected $fillable = [
        'id', 'period_id', 'candidate_id',
        // candidate_id nullable = suara kosong
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function period(): BelongsTo
    {
        return $this->belongsTo(ElectionPeriod::class, 'period_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ElectionCandidate::class, 'candidate_id');
    }
}
