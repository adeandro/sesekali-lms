<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularSessionAttendance extends Model
{
    protected $fillable = [
        'session_id',
        'student_id',
        'status',
        'note'
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExtracurricularSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
