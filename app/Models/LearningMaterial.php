<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningMaterial extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
        'slug',
        'cover_image',
        'is_published',
        'created_by',
        'order',
        'exam_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(5);
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LearningSection::class)->orderBy('order', 'asc');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LearningProgress::class);
    }
}
