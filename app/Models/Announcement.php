<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'type',
        'target_role',
        'target_class_id',
        'expires_at',
        'is_active',
        'show_on_login',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'    => 'datetime',
            'is_active'     => 'boolean',
            'show_on_login' => 'boolean',
        ];
    }

    // ── Relationships ────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    /**
     * Only active, non-expired announcements.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', Carbon::now());
                     });
    }

    /**
     * Filter announcements visible to the given user.
     * Matches by role and optionally by class_group.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('target_role', 'all')
              ->orWhere('target_role', $user->role)
              ->orWhere(function ($inner) use ($user) {
                  // Teacher / superadmin can always see announcements targeting 'teacher'
                  if ($user->role === 'student') {
                      $inner->where('target_role', 'student')
                            ->where(function ($classQ) use ($user) {
                                $classQ->whereNull('target_class_id')
                                       ->orWhere('target_class_id', $user->class_group);
                            });
                  }
              });
        });
    }

    /**
     * Announcements that should appear on the login page.
     * Returns ALL active, non-expired announcements with show_on_login = true,
     * ordered urgent first, then by newest.
     */
    public function scopeForLogin(Builder $query): Builder
    {
        return $query->where('show_on_login', true)
                     ->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                     })
                     ->orderByRaw("FIELD(type, 'urgent', 'warning', 'info') ASC")
                     ->orderBy('created_at', 'desc');
    }

    // ── Helpers ──────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'warning' => 'Peringatan',
            'urgent'  => 'URGENT',
            default   => 'Informasi',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'warning' => 'amber',
            'urgent'  => 'red',
            default   => 'blue',
        };
    }
}
