<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtracurricularSession extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'academic_year',
        'semester',
        'date',
        'topic',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'semester' => 'integer'
    ];

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(ExtracurricularSessionAttendance::class, 'session_id');
    }

    public function coachAttendances(): HasMany
    {
        return $this->hasMany(ExtracurricularCoachAttendance::class, 'session_id');
    }
}
