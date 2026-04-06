<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BattleRoom extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'show_question_on_device' => true,
    ];

    protected $fillable = [
        'token', 'name', 'mode',
        'source_type', 'source_id',
        'question_ids', 'total_questions',
        'duration_per_question', 'show_question_on_device',
        'group_count', 'group_names',
        'max_per_group', 'status', 'is_locked',
        'current_q_index',
        'reward_rank1_exp',
        'reward_rank1_theme_id',
        'reward_rank1_physical',
        'reward_rank2_exp',
        'reward_rank2_theme_id',
        'reward_rank2_physical',
        'reward_rank3_exp',
        'reward_rank3_theme_id',
        'reward_rank3_physical',
        'reward_participant_theme_id',
        'created_by',
        'started_at', 'ended_at',
    ];


    protected $casts = [
        'question_ids' => 'array',
        'group_names'  => 'array',
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $room) {
            if (empty($room->token)) {
                do {
                    $token = strtoupper(Str::random(6));
                } while (self::where('token', $token)->exists());
                $room->token = $token;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BattleParticipant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFull(): bool
    {
        return $this->participants()->count() >= 40;
    }

    public function isGroupFull(string $groupLabel): bool
    {
        if (!$this->max_per_group) return false;
        return $this->participants()
            ->where('group_label', $groupLabel)
            ->count() >= $this->max_per_group;
    }

    // Redis cache key prefix
    public function cacheKey(string $suffix = ''): string
    {
        return 'battle:' . $this->token
            . ($suffix ? ':' . $suffix : '');
    }
}
