<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularCoachAttendance extends Model
{
    protected $fillable = [
        'session_id',
        'coach_id',
        'status',
        'recorded_by'
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExtracurricularSession::class, 'session_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
