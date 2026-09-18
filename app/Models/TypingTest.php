<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TypingTest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'use_token', 'token', 'duration_seconds',
        'target_wpm', 'weight_accuracy', 'weight_speed',
        'show_wpm_accuracy', 'show_score',
        'word_category', 'word_count', 'status',
        'class_restriction', 'starts_at', 'ends_at', 'created_by',
    ];

    protected $casts = [
        'class_restriction'  => 'array',
        'show_wpm_accuracy'  => 'boolean',
        'show_score'         => 'boolean',
        'use_token'          => 'boolean',
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
    ];

    // Relations
    public function attempts(): HasMany
    {
        return $this->hasMany(TypingAttempt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeAvailable($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    // Helper
    public function isAvailableFor(User $student): bool
    {
        if (empty($this->class_restriction)) {
            return true;
        }
        return in_array($student->class_id, $this->class_restriction);
    }

    // Generate token unik 6 karakter
    public function generateToken(): string
    {
        return strtoupper(Str::random(6));
    }
}
