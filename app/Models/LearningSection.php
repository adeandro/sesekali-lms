<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningSection extends Model
{
    protected $fillable = [
        'learning_material_id',
        'title',
        'type',
        'content',
        'video_url',
        'file_path',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'learning_material_id');
    }
}
