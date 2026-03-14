<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MigrationLog extends Model
{
    protected $fillable = [
        'action_type',
        'executed_by',
        'affected_count',
        'academic_year',
        'notes',
        'executed_at',
    ];

    protected $casts = [
        'notes'       => 'array',
        'executed_at' => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────────

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function actionLabel(): string
    {
        return match($this->action_type) {
            'promote'  => '📈 Kenaikan Kelas',
            'graduate' => '🎓 Wisuda Alumni',
            'remap'    => '🗂️ Re-mapping Rombel',
            default    => ucfirst($this->action_type),
        };
    }
}
