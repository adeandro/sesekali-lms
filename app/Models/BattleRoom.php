<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BattleRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'mode', 'source_type', 'source_id', 'created_by',
        'winner_count', 'duration_minutes', 'penalty_hp', 'lock_on_start',
        'status', 'started_at', 'ended_at', 'total_questions', 'question_ids',
    ];

    protected $casts = [
        'lock_on_start'  => 'boolean',
        'question_ids'   => 'array',
        'started_at'     => 'datetime',
        'ended_at'       => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $room) {
            if (empty($room->code)) {
                $room->code = strtoupper(Str::random(6));
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BattleParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(BattleParticipant::class)->where('status', 'active');
    }

    // ── Computed helpers ─────────────────────────────────────────

    /**
     * Fleet progress calculation: AVG(correct_count) / total_questions × 100
     * Returns an array keyed by class_id => [ 'progress' => %, 'active' => n, 'fallen' => n, 'total' => n ]
     */
    public function fleetProgress(): array
    {
        if (!$this->total_questions) return [];

        $participants = $this->participants()->with('user')->get();

        $groups = [];
        foreach ($participants as $p) {
            $key = $p->class_id ?? 'Fleet';
            $groups[$key][] = $p;
        }

        $result = [];
        foreach ($groups as $classId => $members) {
            $active = collect($members)->where('status', 'active');
            $fallen = collect($members)->where('status', 'disqualified');
            $avgCorrect = $active->count()
                ? $active->avg('correct_count')
                : collect($members)->avg('correct_count');

            $result[$classId] = [
                'class_id' => $classId,
                'progress' => round($avgCorrect / $this->total_questions * 100, 1),
                'active'   => $active->count(),
                'fallen'   => $fallen->count(),
                'total'    => count($members),
                'members'  => $members,
            ];
        }

        // Sort by progress desc
        uasort($result, fn ($a, $b) => $b['progress'] <=> $a['progress']);
        return $result;
    }

    /** Remaining seconds from started_at + duration_minutes */
    public function remainingSeconds(): int
    {
        if (!$this->started_at) return $this->duration_minutes * 60;
        $elapsed = now()->diffInSeconds($this->started_at, false);
        return max(0, $this->duration_minutes * 60 - $elapsed);
    }

    /** True if in last 2 minutes (Sudden Death) */
    public function isSuddenDeath(): bool
    {
        return $this->status === 'ongoing' && $this->remainingSeconds() <= 120;
    }
}
