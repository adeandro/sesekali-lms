<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\ReportNote;
use App\Models\User;
use App\Services\GradeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // ── RBAC Helpers ───────────────────────────────────────────────────

    private function getAccessibleClasses()
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') {
            return ClassRoom::active()->orderBy('name', 'asc')->get();
        }
        return ClassRoom::where('homeroom_teacher_id', '=', $user->id, 'and')
            ->active()
            ->orderBy('name', 'asc')
            ->get();
    }

    private function authorizeClass(int $classId): void
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') return;

        $allowed = ClassRoom::active()
            ->where('id', '=', $classId)
            ->where('homeroom_teacher_id', '=', $user->id)
            ->exists();

        if (!$allowed) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
    }

    // ── LANGKAH 1: index() ───────────────────────────────────────────────

    /**
     * Halaman utama raport — daftar kelas + filter semester/tahun.
     */
    public function index(Request $request)
    {
        $classId      = $request->query('class_id');
        $semester     = (int) $request->query('semester', 1);
        $academicYear = $request->query('academic_year', \App\Models\Setting::get('academic_year', '2023/2024'));
        $jenjang      = (int) $request->query('jenjang', 10);
        $reportType   = $request->query('report_type', 'semester');

        $classes = $this->getAccessibleClasses();

        // Auto-select for teacher with 1 class
        $user = auth()->user();
        if ($user->role === 'teacher' && !$classId) {
            if ($classes->count() === 1) {
                $classId = $classes->first()->id;
            }
        }

        $class   = $classId ? ClassRoom::find($classId, ['*']) : null;
        if ($class) {
            $this->authorizeClass($class->id);
            $jenjang = $class->getGradeLevel(); // Always follow class
        }

        $reportSummary = [];
        $students      = collect();

        if ($class) {
            $students = User::where('class_id', '=', $class->id, 'and')
                ->where('role', '=', 'student', 'and')
                ->orderBy('name')
                ->get();

            foreach ($students as $student) {
                $data = GradeService::getStudentReportData(
                    $student, $semester, $academicYear, $jenjang
                );
                $finals = array_filter(array_column($data, 'final'), fn($v) => $v !== null);
                $reportSummary[] = [
                    'student'     => $student,
                    'has_any'     => count($finals) > 0,
                    'is_complete' => count($data) > 0 && collect($data)->every(fn($r) => $r['is_complete']),
                ];
            }
        }

        return view('admin.reports.index', compact(
            'classes', 'class', 'students', 'reportSummary',
            'semester', 'academicYear', 'jenjang', 'reportType'
        ));
    }

    // ── LANGKAH 2: preview() ─────────────────────────────────────────────

    /**
     * Preview raport 1 siswa di browser (dengan navbar/wrapper).
     */
    public function preview(User $student, Request $request)
    {
        $semester     = (int) $request->query('semester', 1);
        $academicYear = $request->query('academic_year', \App\Models\Setting::get('academic_year', '2023/2024'));
        $reportType   = $request->query('report_type', 'semester');

        $this->authorizeClass($student->class_id);

        $class   = ClassRoom::find($student->class_id, ['*']);
        $jenjang = $class ? $class->getGradeLevel() : 10;
        $data    = GradeService::getStudentReportData($student, $semester, $academicYear, $jenjang);
        $note    = ReportNote::where('student_id', $student->id)
            ->where('semester', '=', $semester, 'and')
            ->where('academic_year', '=', $academicYear, 'and')
            ->first();
        $ranking = self::calculateRanking($student, $class, $semester, $academicYear, $jenjang);
        $configs = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('admin.reports.preview', compact(
            'student', 'class', 'data', 'note',
            'ranking', 'semester', 'academicYear', 'reportType', 'configs'
        ));
    }

    // ── LANGKAH 3: printSingle() ─────────────────────────────────────────

    /**
     * Render view raport tanpa layout (bare HTML) untuk window.print().
     */
    public function printSingle(User $student, Request $request)
    {
        $semester     = (int) $request->query('semester', 1);
        $academicYear = $request->query('academic_year', \App\Models\Setting::get('academic_year', '2023/2024'));
        $reportType   = $request->query('report_type', 'semester');

        $this->authorizeClass($student->class_id);

        $class   = ClassRoom::find($student->class_id, ['*']);
        $jenjang = $class ? $class->getGradeLevel() : 10;
        $data    = GradeService::getStudentReportData($student, $semester, $academicYear, $jenjang);
        $note    = ReportNote::where('student_id', $student->id)
            ->where('semester', '=', $semester, 'and')
            ->where('academic_year', '=', $academicYear, 'and')
            ->first();
        $ranking = self::calculateRanking($student, $class, $semester, $academicYear, $jenjang);
        $configs = \App\Models\Setting::pluck('value', 'key')->toArray();

        return view('admin.reports.print', compact(
            'student', 'class', 'data', 'note',
            'ranking', 'semester', 'academicYear', 'reportType', 'configs'
        ));
    }

    // ── LANGKAH 4: printClass() ──────────────────────────────────────────

    /**
     * Render semua siswa dalam 1 kelas dalam satu halaman HTML.
     */
    public function printClass(ClassRoom $class, Request $request)
    {
        $semester     = (int) $request->query('semester', 1);
        $academicYear = $request->query('academic_year', \App\Models\Setting::get('academic_year', '2023/2024'));
        $reportType   = $request->query('report_type', 'semester');

        $this->authorizeClass($class->id);

        // Hanya kirim metadata — HTML di-fetch per siswa via AJAX
        $students = User::where('class_id', '=', $class->id, 'and')
            ->where('role', '=', 'student', 'and')
            ->orderBy('name')
            ->get(['id', 'name', 'nis']);

        return view('admin.reports.print-class', compact(
            'students', 'class', 'semester', 'academicYear', 'reportType'
        ));
    }

    // ── LANGKAH 5: saveNote() ────────────────────────────────────────────

    /**
     * Simpan catatan wali kelas untuk siswa.
     */
    public function saveNote(Request $request)
    {
        $request->validate([
            'student_id'    => 'required|exists:users,id',
            'semester'      => 'required|integer|in:1,2',
            'academic_year' => 'required|string',
            'note'          => 'nullable|string|max:1000',
            'class_id'      => 'required|exists:classes,id',
        ]);

        $this->authorizeClass((int)$request->class_id);

        ReportNote::updateOrCreate(
            [
                'student_id'    => $request->student_id,
                'semester'      => $request->semester,
                'academic_year' => $request->academic_year,
            ],
            [
                'note'       => $request->note,
                'teacher_id' => auth()->id(),
                'class_id'   => $request->class_id,
            ]
        );

        return redirect()->back()->with('success', 'Catatan wali kelas berhasil disimpan.');
    }

    // ── Private Helper ───────────────────────────────────────────────────

    /**
     * Hitung ranking semua siswa sekaligus — O(n) bukan O(n²)
     */
    private static function calculateAllRankings(
        \Illuminate\Support\Collection $students,
        array $allReportData
    ): array {
        $scores = [];
        foreach ($students as $s) {
            $reportData = $allReportData[$s->id] ?? [];
            $finals = array_filter(
                array_column($reportData, 'final'),
                fn($v) => $v !== null
            );
            $scores[$s->id] = count($finals) > 0
                ? round(array_sum($finals) / count($finals), 2)
                : 0;
        }

        // Sort descending untuk ranking
        arsort($scores);
        $total = count($students);
        $rankings = [];
        $rank = 1;
        foreach ($scores as $studentId => $avg) {
            $rankings[$studentId] = [
                'rank'  => $rank++,
                'total' => $total,
                'avg'   => $avg,
            ];
        }

        return $rankings;
    }

    /**
     * Hitung ranking siswa di kelasnya berdasarkan rata-rata nilai akhir.
     */
    private static function calculateRanking(
        User      $student,
        ?ClassRoom $class,
        int       $semester,
        string    $academicYear,
        int       $jenjang
    ): array {
        if (! $class) {
            return ['rank' => '-', 'total' => 0, 'avg' => 0];
        }

        $classStudents = User::where('class_id', '=', $class->id, 'and')
            ->where('role', '=', 'student', 'and')
            ->get();

        $scores = [];
        foreach ($classStudents as $s) {
            $reportData = GradeService::getStudentReportData($s, $semester, $academicYear, $jenjang);
            $finals     = array_filter(
                array_column($reportData, 'final'),
                fn($v) => $v !== null
            );
            $scores[$s->id] = count($finals) > 0
                ? round(array_sum($finals) / count($finals), 2)
                : 0;
        }

        arsort($scores); // descending
        $rank = array_search($student->id, array_keys($scores));
        $rank = $rank !== false ? $rank + 1 : '-';

        return [
            'rank'  => $rank,
            'total' => count($classStudents),
            'avg'   => $scores[$student->id] ?? 0,
        ];
    }
}
