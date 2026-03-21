<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeWeight extends Model
{
    protected $fillable = [
        'subject_id',
        'teacher_id',
        'semester',
        'academic_year',
        'jenjang',
        'weight_harian',
        'weight_uts',
        'weight_uas',
    ];

    protected $casts = [
        'weight_harian' => 'float',
        'weight_uts'    => 'float',
        'weight_uas'    => 'float',
        'semester'      => 'integer',
        'jenjang'       => 'integer',
    ];

    // ── Accessors ────────────────────────────────────────────────────

    /**
     * Total gabungan ketiga bobot. Harus = 100 (divalidasi di controller).
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->weight_harian + $this->weight_uts + $this->weight_uas;
    }

    // ── Relationships ────────────────────────────────────────────────

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
