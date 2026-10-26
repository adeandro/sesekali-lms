<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionCandidate extends Model
{
    protected $fillable = [
        'period_id', 'number',
        'chairman_id', 'chairman_name', 'chairman_class',
        'vice_id', 'vice_name', 'vice_class',
        'vision', 'mission', 'photo_path',
    ];

    protected $casts = [
        'number' => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function period(): BelongsTo
    {
        return $this->belongsTo(ElectionPeriod::class, 'period_id');
    }

    public function chairman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    public function vice(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vice_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path)) {
            return '/storage/' . $this->photo_path;
        }
        if ($this->chairman) {
            if ($this->chairman->avatar_upload && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->chairman->avatar_upload)) {
                return '/storage/' . str_replace(' ', '%20', $this->chairman->avatar_upload);
            }
            if ($this->chairman->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists('profiles/' . $this->chairman->photo)) {
                return '/storage/profiles/' . str_replace(' ', '%20', $this->chairman->photo);
            }
            if ($this->chairman->photo_url) {
                return str_replace(' ', '%20', $this->chairman->photo_url);
            }
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->chairman_name) . '&background=4f46e5&color=fff&size=400';
    }

    public function getVicePhotoUrlAttribute(): string
    {
        if ($this->vice) {
            if ($this->vice->avatar_upload && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->vice->avatar_upload)) {
                return '/storage/' . str_replace(' ', '%20', $this->vice->avatar_upload);
            }
            if ($this->vice->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists('profiles/' . $this->vice->photo)) {
                return '/storage/profiles/' . str_replace(' ', '%20', $this->vice->photo);
            }
            if ($this->vice->photo_url) {
                return str_replace(' ', '%20', $this->vice->photo_url);
            }
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->vice_name) . '&background=10b981&color=fff&size=400';
    }

    public function hasCustomPhoto(): bool
    {
        return ! empty($this->photo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path);
    }

    /**
     * Display label: "No. X — Nama Ketua & Nama Wakil"
     */
    public function getLabelAttribute(): string
    {
        return "Paslon {$this->number} — {$this->chairman_name} & {$this->vice_name}";
    }
}
