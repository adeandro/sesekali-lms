<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionVoter extends Model
{
    /**
     * Snapshot tabel — tidak ada created_at/updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'period_id', 'student_id', 'nis', 'student_name',
        'class_id', 'class_name',
        'token_hash', 'token_version', 'token_printed_at',
        'has_voted', 'voted_at',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'has_voted'        => 'boolean',
        'token_version'    => 'integer',
        'token_printed_at' => 'datetime',
        'voted_at'         => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function period(): BelongsTo
    {
        return $this->belongsTo(ElectionPeriod::class, 'period_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
