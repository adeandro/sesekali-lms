<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoom extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'grade',
        'section',
        'academic_year',
        'is_active',
        'capacity',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGrade($query, string $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeByYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    // ── Relationships ───────────────────────────────────────────

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id');
    }

    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Returns the display name, e.g. "XI-IPA-1" or just "10" if no section.
     */
    public function fullName(): string
    {
        return $this->name;
    }

    /**
     * Number of currently enrolled students.
     */
    public function enrolledCount(): int
    {
        return $this->students()->where('status', 'Aktif')->count();
    }

    /**
     * Check if a given grade string matches this class's grade.
     * Supports "10", "XI", "12" etc.
     */
    public static function gradeMatches(string $studentGrade, string $classGrade): bool
    {
        // Normalize roman numerals → arabic
        $map = ['X' => '10', 'XI' => '11', 'XII' => '12'];
        $normalized = $map[strtoupper(trim($studentGrade))] ?? trim($studentGrade);
        $classNorm  = $map[strtoupper(trim($classGrade))]  ?? trim($classGrade);
        return $normalized === $classNorm;
    }
}
