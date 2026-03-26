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
        string $academicYear,
        string $reportType = 'semester'
    ): ?float {
        $cacheKey = "{$classId}-{$subjectId}-{$semester}-{$academicYear}-{$reportType}";

        if (array_key_exists($cacheKey, self::$classAverages)) {
            return self::$classAverages[$cacheKey];
        }

        // Get all students in the class
        $students = User::where('role', '=', 'student', 'and')
            ->where('class_id', '=', $classId, 'and')
            ->aktif()
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
            $grade = self::calculateFinalGrade($student, $subject, $semester, $academicYear, $jenjang, $reportType);
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
        int     $jenjang,
        string  $reportType = 'semester'
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

        if ($reportType === 'mid') {
            // Mode tengah semester: hanya harian + UTS
            if ($grades['harian'] === null || $grades['uts'] === null) {
                return null;
            }

            $totalWeight = $wHarian + $wUts;
            if ($totalWeight == 0) return null;

            // Normalisasi bobot ke 100%
            $wHarianNorm = $wHarian / $totalWeight * 100;
            $wUtsNorm    = $wUts    / $totalWeight * 100;

            $final = ($grades['harian'] * $wHarianNorm / 100)
                   + ($grades['uts']    * $wUtsNorm    / 100);

            return round($final, 2);
        }

        // Mode semester (default): butuh semua komponen
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
        ?int   $jenjang = null,
        string $reportType = 'semester'
    ): array {
        // If jenjang is not provided, try to get it from student's class
        if ($jenjang === null && $student->classRoom) {
            $jenjang = $student->classRoom->getGradeLevel();
        }

        // Fallback or explicit jenjang
        $jenjang = $jenjang ?? 10;

        // Deteksi jenjang dari nama kelas
        $gradeLabel = match($jenjang) {
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            default => null,
        };

        // Ambil semua mapel yang terdaftar (mempunyai kategori) untuk urutan raport
        $subjectQuery = Subject::whereNotNull('category')
            ->forReport();

        if ($gradeLabel !== null) {
            $subjectQuery->forGrade($gradeLabel);
        }

        $subjects = $subjectQuery->get();

        $result = [];
        foreach ($subjects as $subject) {
            $grades = self::getMergedGrades($student, $subject, $semester, $academicYear);
            $final  = self::calculateFinalGrade($student, $subject, $semester, $academicYear, $jenjang, $reportType);
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
                                    ? self::getClassAverage((int)$student->class_id, $subject->id, $semester, $academicYear, $reportType) 
                                    : null,
                'is_pass'       => $final !== null && $final >= $kkm,
                'is_complete'   => $reportType === 'mid'
                                   ? ($grades['harian'] !== null && $grades['uts'] !== null)
                                   : ($grades['harian'] !== null && $grades['uts'] !== null && $grades['uas'] !== null),
            ];
        }

        return $result;
    }

    /**
     * Ambil data mentah (raw) sekelas untuk kalkulasi ranking secepat kilat.
     */
    public static function getBulkClassData(int $classId, int $semester, string $academicYear, int $jenjang): array
    {
        $students = User::where('class_id', '=', $classId, 'and')
            ->where('role', '=', 'student', 'and')
            ->aktif()
            ->get(['id', 'name', 'class_id']);
        $studentIds = $students->pluck('id')->toArray();

        // 1. Ambil semua nilai manual (harian, uts, uas) sekaligus
        $manualGrades = ManualGrade::whereIn('student_id', $studentIds)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->get();

        // 2. Ambil bobot (grade weights) sejenjang sekaligus
        $weights = GradeWeight::where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->where('jenjang', $jenjang)
            ->get();

        // 3. Ambil semua mata pelajaran relevan
        $subjects = Subject::whereNotNull('category')->forReport()->get();

        // 4. Grouping untuk lookup O(1)
        return [
            'students'     => $students,
            'manualGrades' => $manualGrades->groupBy('student_id'),
            'weights'      => $weights->keyBy('subject_id'),
            'subjects'     => $subjects,
        ];
    }

    /**
     * Hitung rata-rata siswa menggunakan data bulk (tanpa query DB tambahan).
     */
    public static function calculateAverageFromBulk(User $student, array $bulk, string $reportType = 'semester'): float
    {
        $grades = [];
        $manuals = $bulk['manualGrades'][$student->id] ?? collect();

        foreach ($bulk['subjects'] as $subject) {
            $m = $manuals->firstWhere('subject_id', $subject->id);
            if (!$m) continue;

            $weight = $bulk['weights'][$subject->id] ?? null;
            $final = self::calculateFinalGradeFromData($m, $weight, $reportType);
            if ($final !== null) $grades[] = $final;
        }

        return count($grades) > 0 ? round(array_sum($grades) / count($grades), 2) : 0;
    }

    private static function calculateFinalGradeFromData($manual, $weight, string $reportType = 'semester'): ?float
    {
        $harian = $manual->harian ?? null;
        $uts    = $manual->uts ?? null;
        $uas    = $manual->uas ?? null;

        if ($harian === null && $uts === null && $uas === null) return null;

        $wHarian = $weight->weight_harian ?? 40;
        $wUts    = $weight->weight_uts ?? 30;
        $wUas    = $weight->weight_uas ?? 30;

        if ($reportType === 'mid') {
            if ($harian === null || $uts === null) return null;
            $totalWeight = $wHarian + $wUts;
            if ($totalWeight == 0) return 0;

            $wHarianNorm = $wHarian / $totalWeight * 100;
            $wUtsNorm    = $wUts    / $totalWeight * 100;

            return ($harian * $wHarianNorm / 100) + ($uts * $wUtsNorm / 100);
        }

        if ($harian === null || $uts === null || $uas === null) return null;

        $totalWeight = $wHarian + $wUts + $wUas;
        if ($totalWeight === 0) return 0;

        return (($harian ?? 0) * $wHarian + ($uts ?? 0) * $wUts + ($uas ?? 0) * $wUas) / $totalWeight;
    }

    /**
     * Pre-load semua data nilai untuk seluruh kelas dalam satu batch query.
     * Digunakan oleh ReportController::index untuk optimasi N+1.
     */
    public static function preloadClassData(
        int    $classId,
        int    $semester,
        string $academicYear,
        int    $jenjang,
        string $reportType = 'semester'
    ): array {
        $gradeLabel = match($jenjang) {
            10 => 'X', 11 => 'XI', 12 => 'XII',
            default => null,
        };

        // 1. Semua mapel relevan — 1 query
        $subjectQuery = Subject::whereNotNull('category')
            ->forReport();
        if ($gradeLabel !== null) {
            $subjectQuery->forGrade($gradeLabel);
        }
        $subjects   = $subjectQuery->orderBy('sort_order', 'asc')->get();
        $subjectIds = $subjects->pluck('id')->toArray();

        // 2. Semua siswa di kelas — 1 query
        $students   = User::where('class_id', '=', $classId)
            ->where('role', '=', 'student')
            ->aktif()
            ->get(['id', 'name', 'nis', 'class_id', 'photo']);
        $studentIds = $students->pluck('id')->toArray();

        // 3. Semua nilai manual sekaligus — 1 query
        $allManual = ManualGrade::whereIn('student_id', $studentIds)
            ->whereIn('subject_id', $subjectIds)
            ->where('semester', '=', $semester)
            ->where('academic_year', '=', $academicYear)
            ->get();

        // Indexing manual: [student_id][subject_id][grade_type][]
        $manualIndex = [];
        foreach ($allManual as $mg) {
            $manualIndex[$mg->student_id][$mg->subject_id][$mg->grade_type][] = $mg->score;
        }

        // 4. Semua nilai CBT sekaligus — 1 query
        $allAttempts = ExamAttempt::whereIn('student_id', $studentIds)
            ->where('status', '=', 'submitted')
            ->whereHas('exam', function ($q) use ($subjectIds, $semester, $academicYear) {
                $q->whereIn('subject_id', $subjectIds)
                  ->where('semester', '=', $semester)
                  ->where('academic_year', '=', $academicYear)
                  ->where('include_in_report', '=', true)
                  ->whereNotIn('exam_type', ['latihan']);
            })
            ->with('exam:id,subject_id,exam_type,semester,academic_year')
            ->get(['id', 'student_id', 'exam_id', 'final_score', 'adjusted_score', 'is_adjusted', 'status']);

        // Indexing CBT: [student_id][subject_id][exam_type][]
        $cbtIndex = [];
        foreach ($allAttempts as $attempt) {
            $score = $attempt->is_adjusted && $attempt->adjusted_score !== null
                ? (float) $attempt->adjusted_score
                : ($attempt->final_score !== null ? (float) $attempt->final_score : null);

            if ($score === null) continue;

            $subjectId = $attempt->exam->subject_id;
            $type      = $attempt->exam->exam_type;
            $cbtIndex[$attempt->student_id][$subjectId][$type][] = $score;
        }

        // 5. Semua bobot sekaligus — 1 query
        $allWeights = GradeWeight::whereIn('subject_id', $subjectIds)
            ->where('semester', '=', $semester)
            ->where('academic_year', '=', $academicYear)
            ->where('jenjang', '=', $jenjang)
            ->get()
            ->keyBy('subject_id');

        // 6. Hitung merged grades + final per siswa
        $result = [];
        foreach ($students as $student) {
            $studentData = [];
            $allFinals   = [];

            foreach ($subjects as $subject) {
                $cbt    = $cbtIndex[$student->id][$subject->id] ?? [];
                $manual = $manualIndex[$student->id][$subject->id] ?? [];

                // Rata-rata harian
                $hCbt = isset($cbt['harian']) ? round(array_sum($cbt['harian']) / count($cbt['harian']), 2) : null;
                $hMan = isset($manual['harian']) ? round(array_sum($manual['harian']) / count($manual['harian']), 2) : null;
                $harian = $hCbt ?? $hMan;

                // UTS
                $uCbt = $cbt['uts'][0] ?? ($cbt['pts'][0] ?? null);
                $uMan = isset($manual['uts'][0]) ? (float)$manual['uts'][0] : (isset($manual['pts'][0]) ? (float)$manual['pts'][0] : null);
                $uts = $uCbt ?? $uMan;

                // UAS
                $aCbt = $cbt['uas'][0] ?? ($cbt['pas'][0] ?? null);
                $aMan = isset($manual['uas'][0]) ? (float)$manual['uas'][0] : (isset($manual['pas'][0]) ? (float)$manual['pas'][0] : null);
                $uas = $aCbt ?? $aMan;

                $grades = [
                    'harian'        => $harian,
                    'uts'           => $uts,
                    'uas'           => $uas,
                    'harian_source' => $hCbt !== null ? 'cbt' : ($hMan !== null ? 'manual' : null),
                    'uts_source'    => $uCbt !== null ? 'cbt' : ($uMan !== null ? 'manual' : null),
                    'uas_source'    => $aCbt !== null ? 'cbt' : ($aMan !== null ? 'manual' : null),
                ];

                $weight  = $allWeights[$subject->id] ?? null;
                $wHarian = $weight->weight_harian ?? 40;
                $wUts    = $weight->weight_uts    ?? 30;
                $wUas    = $weight->weight_uas    ?? 30;

                if ($reportType === 'mid') {
                    $totalW = $wHarian + $wUts;
                    $final = ($harian !== null && $uts !== null && $totalW > 0)
                        ? round(($harian * $wHarian / $totalW) + ($uts * $wUts / $totalW), 2)
                        : null;
                    $isComplete = $harian !== null && $uts !== null;
                } else {
                    $final = ($harian !== null && $uts !== null && $uas !== null)
                        ? round(($harian * $wHarian / 100) + ($uts * $wUts / 100) + ($uas * $wUas / 100), 2)
                        : null;
                    $isComplete = $harian !== null && $uts !== null && $uas !== null;
                }

                if ($final !== null) $allFinals[] = $final;

                $studentData[] = [
                    'subject'       => $subject,
                    'grades'        => $grades,
                    'final'         => $final,
                    'weight'        => $weight,
                    'kkm'           => $subject->kkm ?? 75,
                    'is_pass'       => $final !== null && $final >= ($subject->kkm ?? 75),
                    'is_complete'   => $isComplete,
                    'class_average' => null, // diisi nanti
                ];
            }

            $result[$student->id] = [
                'student'     => $student,
                'data'        => $studentData,
                'avg'         => count($allFinals) > 0 ? round(array_sum($allFinals) / count($allFinals), 2) : 0,
                'has_any'     => count($allFinals) > 0,
                'is_complete' => collect($studentData)->every(fn($d) => $d['is_complete']),
            ];
        }

        // 7. Hitung class_average per mapel
        foreach ($subjects as $subject) {
            $subjFinals = [];
            foreach ($result as $sId => $sData) {
                $row = collect($sData['data'])->firstWhere('subject.id', $subject->id);
                if ($row && $row['final'] !== null) $subjFinals[] = $row['final'];
            }

            $avg = count($subjFinals) > 0 ? round(array_sum($subjFinals) / count($subjFinals), 2) : null;

            foreach ($result as $sId => &$sData) {
                foreach ($sData['data'] as &$row) {
                    if ($row['subject']->id === $subject->id) $row['class_average'] = $avg;
                }
            }
        }

        return [
            'students'  => $students,
            'subjects'  => $subjects,
            'byStudent' => $result,
        ];
    }
}
