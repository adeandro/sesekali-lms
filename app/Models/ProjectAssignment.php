<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'subject_id',
        'max_file_size_mb',
        'max_slots',
        'class_restriction',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'class_restriction' => 'array',
        'is_active'         => 'boolean',
        'max_file_size_mb'  => 'integer',
        'max_slots'         => 'integer',
        'starts_at'         => 'datetime',
        'ends_at'           => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
            $original = $model->slug;
            $count = 1;
            while (static::where('slug', $model->slug)->exists()) {
                $model->slug = $original . '-' . $count++;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relations
    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectSubmission::class, 'assignment_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    // Helpers
    public function isAvailableFor(User $student): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }

        if (!empty($this->class_restriction)) {
            return in_array($student->class_id, $this->class_restriction);
        }

        return true;
    }
}
