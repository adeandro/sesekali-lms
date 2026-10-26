<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ElectionAuditLog extends Model
{
    /**
     * Only created_at — no updated_at.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'period_id',
        'action',
        'actor_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    // ── Static helper ─────────────────────────────────────────────────────

    /**
     * Record an audit event.
     * Failure MUST NOT throw or bubble — wrapped in try/catch.
     */
    public static function record(
        string $action,
        ?int $periodId = null,
        ?int $actorId = null,
        array $meta = []
    ): void {
        try {
            static::create([
                'period_id' => $periodId,
                'action'    => $action,
                'actor_id'  => $actorId,
                'meta'      => $meta ?: null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ElectionAuditLog::record failed', [
                'action' => $action,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
