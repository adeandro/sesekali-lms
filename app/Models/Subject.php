<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'kkm', 'category', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    const CATEGORY_UMUM          = 'umum';
    const CATEGORY_KEJURUAN      = 'kejuruan';
    const CATEGORY_MUATAN_SEKOLAH = 'muatan_sekolah';
    const CATEGORY_PILIHAN = 'pilihan';

    public static function categories(): array
    {
        return [
            self::CATEGORY_UMUM          => 'A. Mata Pelajaran Umum',
            self::CATEGORY_KEJURUAN      => 'B. Mata Pelajaran Kejuruan',
            self::CATEGORY_MUATAN_SEKOLAH => 'C. Muatan Sekolah',
            self::CATEGORY_PILIHAN        => 'D. Mata Pelajaran Pilihan',
        ];
    }

    public function scopeForReport($query, $semester = null, $academicYear = null, $jenjang = null, $studentId = null)
    {
        if ($semester && $academicYear && $jenjang) {
            $query->where(function($q) use ($semester, $academicYear, $jenjang, $studentId) {
                // Subjects with exams in this period
                $q->whereHas('exams', function($e) use ($semester, $academicYear, $jenjang) {
                    $e->where('semester', $semester)
                      ->where('academic_year', $academicYear)
                      ->where('jenjang', $jenjang)
                      ->where('include_in_report', true);
                });

                // OR subjects with manual grades for this student
                if ($studentId) {
                    $q->orWhereHas('manualGrades', function($m) use ($studentId, $semester, $academicYear) {
                        $m->where('student_id', $studentId)
                          ->where('semester', $semester)
                          ->where('academic_year', $academicYear);
                    });
                }
            });
        }

        return $query->orderByRaw("FIELD(category, 'umum', 'kejuruan', 'muatan_sekolah', 'pilihan')")
                     ->orderBy('sort_order', 'asc')
                     ->orderBy('name', 'asc');
    }

    /**
     * Get all questions for this subject.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the teachers for this subject.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get all exams for this subject.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Get all manual grades for this subject.
     */
    public function manualGrades(): HasMany
    {
        return $this->hasMany(ManualGrade::class);
    }
}
