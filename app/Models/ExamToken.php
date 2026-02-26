<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Exception;

class ExamToken extends Model
{
    protected $fillable = [
        'exam_id',
        'token',
        'expires_at',
        'used_at',
        'used_by',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public $timestamps = false;

    /**
     * Get the exam this token belongs to.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the user who used this token.
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Generate a unique 9-character alphanumeric token.
     * Format: XXXX-XXXX (with single dash in middle for readability)
     */
    public static function generateToken(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            // Generate 4 characters for first part
            $part1 = '';
            for ($i = 0; $i < 4; $i++) {
                $part1 .= $characters[rand(0, strlen($characters) - 1)];
            }

            // Generate 4 characters for second part
            $part2 = '';
            for ($i = 0; $i < 4; $i++) {
                $part2 .= $characters[rand(0, strlen($characters) - 1)];
            }

            // Combine with single dash
            $token = $part1 . '-' . $part2;
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Check if token is valid (not expired and active).
     */
    public function isValid(): bool
    {
        return $this->is_active && $this->expires_at > now();
    }

    /**
     * Check if token has been used.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Mark token as used by a student.
     */
    public function markAsUsed(User $student): void
    {
        $this->update([
            'used_at' => now(),
            'used_by' => $student->id,
            'is_active' => false,
        ]);
    }
}
