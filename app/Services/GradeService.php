<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\GradeWeight;
use App\Models\ManualGrade;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\User;

/**
 * GradeService — pure static service untuk kalkulasi nilai raport.
 *
 * Aturan:
 *  - Nilai CBT SELALU prioritas atas nilai manual (guru).
 *  - Null = "belum ada data", bukan 0.
 *  - Semua method static, tidak ada state.
 */
class GradeService
{
    /** @var array<string, float|null> Static cache for class averages */
    protected static $classAverages = [];

    // ── Method 0 ─────────────────────────────────────────────────────────

    /**
     * Get class average for a subject in a specific period.
     * Uses in-memory static caching.
     */
    public static function getClassAverage(
        int    $classId,
        int    $subjectId,
        int    $semester,
        string $academicYear
    ): ?float {
        $cacheKey = "{$classId}-{$subjectId}-{$semester}-{$academicYear}";

        if (array_key_exists($cacheKey, self::$classAverages)) {
            return self::$classAverages[$cacheKey];
        }

        // Get all students in the class
        $students = User::where('role', '=', 'student', 'and')
            ->where('class_id', '=', $classId, 'and')
            ->get();

        if ($students->isEmpty()) {
            return self::$classAverages[$cacheKey] = null;
        }

        // Get subject to calculate jenjang (grade)
        $subject = Subject::find($subjectId, ['*']);
        $classRoom = ClassRoom::find($classId, ['*']);
        if (!$subject || !$classRoom) {
            return self::$classAverages[$cacheKey] = null;
        }

        $jenjang = $classRoom->getGradeLevel();

        $totalGrades = [];
        foreach ($students as $student) {
            $grade = self::calculateFinalGrade($student, $subject, $semester, $academicYear, $jenjang);
            if ($grade !== null) {
                $totalGrades[] = $grade;
            }
        }

        $average = count($totalGrades) > 0
            ? round(array_sum($totalGrades) / count($totalGrades), 2)
            : null;

        return self::$classAverages[$cacheKey] = $average;
    }

    // ── Method 1 ─────────────────────────────────────────────────────────

    /**
     * Tentukan nilai efektif dari satu attempt.
     * Pakai adjusted_score jika is_adjusted = true, fallback ke final_score.
     */
    public static function getEffectiveScore(ExamAttempt $attempt): ?float
    {
        if ($attempt->is_adjusted && $attempt->adjusted_score !== null) {
            return (float) $attempt->adjusted_score;
        }
        return $attempt->final_score !== null
            ? (float) $attempt->final_score
            : null;
    }

    // ── Method 2 ─────────────────────────────────────────────────────────

    /**
     * Ambil nilai CBT siswa untuk satu mapel per semester.
     *
     * @return array{harian: float|null, uts: float|null, uas: float|null}
     */
    public static function getCbtGrades(
        User    $student,
        Subject $subject,
        int     $semester,
        string  $academicYear
    ): array {
        $attempts = ExamAttempt::where('student_id', '=', $student->id, 'and')
            ->where('status', '=', 'submitted', 'and')
            ->whereHas('exam', function ($q) use ($subject, $semester, $academicYear) {
                $q->where('subject_id', '=', $subject->id, 'and')
                  ->where('semester', '=', $semester, 'and')
                  ->where('academic_year', '=', $academicYear, 'and')
                  ->where('include_in_report', '=', true, 'and')
                  ->whereNotIn('exam_type', ['latihan']);
            })
            ->with('exam')
            ->get();

        $harianScores = [];
        $utsScore     = null;
        $uasScore     = null;

        foreach ($attempts as $attempt) {
            $score = self::getEffectiveScore($attempt);
            if ($score === null) {
                continue;
            }

            $type = $attempt->exam->exam_type;

            if ($type === 'harian') {
                $harianScores[] = $score;
            } elseif (in_array($type, ['uts', 'pts'])) {
                $utsScore = $score; // hanya 1 per semester
            } elseif (in_array($type, ['uas', 'pas'])) {
                $uasScore = $score; // hanya 1 per semester
            }
        }

        $harianAvg = count($harianScores) > 0
            ? round(array_sum($harianScores) / count($harianScores), 2)
            : null;

        return [
            'harian' => $harianAvg,
            'uts'    => $utsScore,
            'uas'    => $uasScore,
        ];
    }

    // ── Method 3 ─────────────────────────────────────────────────────────

    /**
     * Ambil nilai manual dari tabel manual_grades.
     *
     * @return array{harian: float|null, uts: float|null, uas: float|null}
     */
    public static function getManualGrades(
        User    $student,
        Subject $subject,
        int     $semester,
        string  $academicYear
    ): array {
        $grades = ManualGrade::where('student_id', '=', $student->id)
            ->where('subject_id', '=', $subject->id)
            ->where('semester', '=', $semester)
            ->where('academic_year', '=', $academicYear)
            ->get(['*'])
            ->keyBy('grade_type');

        // Rata-rata semua entry harian jika ada lebih dari satu
        $harianEntries = ManualGrade::where('student_id', '=', $student->id)
            ->where('subject_id', '=', $subject->id)
            ->where('semester', '=', $semester)
            ->where('academic_year', '=', $academicYear)
            ->where('grade_type', '=', 'harian')
            ->pluck('score')
            ->toArray();

        $harianAvg = count($harianEntries) > 0
            ? round(array_sum($harianEntries) / count($harianEntries), 2)
            : null;

        return [
            'harian' => $harianAvg,
            'uts'    => isset($grades['uts'])
                ? (float) $grades['uts']->score
                : (isset($grades['pts'])
                    ? (float) $grades['pts']->score
                    : null),
            'uas'    => isset($grades['uas'])
                ? (float) $grades['uas']->score
                : (isset($grades['pas'])
                    ? (float) $grades['pas']->score
                    : null),
        ];
    }

    // ── Method 4 ─────────────────────────────────────────────────────────

    /**
     * Merge nilai CBT dan manual — CBT selalu prioritas.
     *
     * @return array{
     *   harian: float|null, uts: float|null, uas: float|null,
     *   harian_source: string|null, uts_source: string|null, uas_source: string|null
     * }
     */
    public static function getMergedGrades(
        User    $student,
        Subject $subject,
        int     $semester,
        string  $academicYear
    ): array {
        $cbt    = self::getCbtGrades($student, $subject, $semester, $academicYear);
        $manual = self::getManualGrades($student, $subject, $semester, $academicYear);

        return [
            'harian' => $cbt['harian'] ?? $manual['harian'],
            'uts'    => $cbt['uts']    ?? $manual['uts'],
            'uas'    => $cbt['uas']    ?? $manual['uas'],
            // source: untuk info di UI apakah nilai dari CBT atau manual
            'harian_source' => $cbt['harian'] !== null ? 'cbt'
                                : ($manual['harian'] !== null ? 'manual' : null),
            'uts_source'    => $cbt['uts'] !== null    ? 'cbt'
                                : ($manual['uts'] !== null    ? 'manual' : null),
            'uas_source'    => $cbt['uas'] !== null    ? 'cbt'
                                : ($manual['uas'] !== null    ? 'manual' : null),
        ];
    }

    // ── Method 5 ─────────────────────────────────────────────────────────

    /**
     * Hitung nilai akhir berdasarkan bobot dari grade_weights.
     * Null jika salah satu komponen belum ada.
     */
    public static function calculateFinalGrade(
        User    $student,
        Subject $subject,
        int     $semester,
        string  $academicYear,
        int     $jenjang
    ): ?float {
        $grades = self::getMergedGrades($student, $subject, $semester, $academicYear);

        // Ambil bobot untuk mapel/jenjang/periode ini
        $weight = GradeWeight::where('subject_id', '=', $subject->id)
            ->where('semester', '=', $semester)
            ->where('academic_year', '=', $academicYear)
            ->where('jenjang', '=', $jenjang)
            ->first(['*']);

        // Fallback ke default jika bobot belum dikonfigurasi
        $wHarian = $weight ? $weight->weight_harian : 40;
        $wUts    = $weight ? $weight->weight_uts    : 30;
        $wUas    = $weight ? $weight->weight_uas    : 30;

        // Jika salah satu komponen null, tidak bisa hitung nilai akhir
        if ($grades['harian'] === null
            || $grades['uts'] === null
            || $grades['uas'] === null) {
            return null;
        }

        $final = ($grades['harian'] * $wHarian / 100)
               + ($grades['uts']    * $wUts    / 100)
               + ($grades['uas']    * $wUas    / 100);

        return round($final, 2);
    }

    // ── Method 6 ─────────────────────────────────────────────────────────

    /**
     * Kumpulkan semua data nilai siswa untuk semua mapel dalam satu periode.
     * Digunakan oleh halaman input nilai dan cetak raport (Sprint 3).
     *
     * @return array<int, array{
     *   subject: Subject,
     *   grades: array,
     *   final: float|null,
     *   weight: GradeWeight|null,
     *   kkm: int|float,
     *   is_pass: bool,
     *   is_complete: bool
     * }>
     */
    public static function getStudentReportData(
        User   $student,
        int    $semester,
        string $academicYear,
        ?int   $jenjang = null
    ): array {
        // If jenjang is not provided, try to get it from student's class
        if ($jenjang === null && $student->classRoom) {
            $jenjang = $student->classRoom->getGradeLevel();
        }

        // Fallback or explicit jenjang
        $jenjang = $jenjang ?? 10;

        // Ambil semua mapel yang terdaftar (mempunyai kategori) untuk urutan raport
        $subjects = Subject::whereNotNull('category', 'and')
            ->forReport()
            ->get();

        $result = [];
        foreach ($subjects as $subject) {
            $grades = self::getMergedGrades($student, $subject, $semester, $academicYear);
            $final  = self::calculateFinalGrade($student, $subject, $semester, $academicYear, $jenjang);
            $weight = GradeWeight::where('subject_id', $subject->id)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->where('jenjang', $jenjang)
                ->first();

            $kkm = $subject->kkm ?? 75;

            $result[] = [
                'subject'       => $subject,
                'grades'        => $grades,
                'final'         => $final,
                'weight'        => $weight,
                'kkm'           => $kkm,
                'class_average' => $student->class_id 
                                    ? self::getClassAverage((int)$student->class_id, $subject->id, $semester, $academicYear) 
                                    : null,
                'is_pass'       => $final !== null && $final >= $kkm,
                'is_complete'   => $grades['harian'] !== null
                                   && $grades['uts'] !== null
                                   && $grades['uas'] !== null,
            ];
        }

        return $result;
    }
}
