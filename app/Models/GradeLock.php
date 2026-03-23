<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLock extends Model
{
    protected $fillable = [
        'subject_id', 'locked_by', 'semester',
        'academic_year', 'is_locked',
        'locked_at', 'unlocked_at',
    ];

    protected $casts = [
        'is_locked'   => 'boolean',
        'locked_at'   => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Cek apakah mapel terkunci untuk semester tertentu
     */
    public static function isLocked(
        int $subjectId,
        int $semester,
        string $academicYear
    ): bool {
        return static::where('subject_id', $subjectId)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->where('is_locked', true)
            ->exists();
    }

    /**
     * Toggle lock/unlock
     */
    public static function toggle(
        int $subjectId,
        int $semester,
        string $academicYear,
        int $userId
    ): array {
        $lock = static::firstOrNew([
            'subject_id'    => $subjectId,
            'semester'      => $semester,
            'academic_year' => $academicYear,
        ]);

        if ($lock->is_locked) {
            // Buka kunci
            $lock->is_locked    = false;
            $lock->unlocked_at  = now();
            $lock->save();
            return ['locked' => false];
        } else {
            // Kunci
            $lock->is_locked  = true;
            $lock->locked_by  = $userId;
            $lock->locked_at  = now();
            $lock->save();
            return ['locked' => true];
        }
    }
}
