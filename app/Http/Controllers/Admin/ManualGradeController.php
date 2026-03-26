<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManualGradeRequest;
use App\Http\Requests\UpdateManualGradeRequest;
use App\Imports\ManualGradeImport;
use App\Models\ClassRoom;
use App\Models\GradeWeight;
use App\Models\ManualGrade;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ManualGradeController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────

    /**
     * Ringkasan nilai manual yang sudah diinput, dikelompokkan per mapel+kelas+periode.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = ManualGrade::with(['subject', 'teacher'])
            ->selectRaw(
                'subject_id, semester, academic_year, jenjang, teacher_id,
                 COUNT(DISTINCT student_id) as student_count'
            )
            ->groupBy('subject_id', 'semester', 'academic_year', 'jenjang', 'teacher_id');

        if ($user->role === 'teacher') {
            $query->where('teacher_id', $user->id);
        }

        // Optional filter
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        $summaries = $query->orderByDesc('academic_year')
            ->orderBy('semester')
            ->paginate(20)
            ->withQueryString();

        $subjects = $user->role === 'teacher'
            ? $user->subjects()->orderBy('name')->get()
            : Subject::orderBy('name')->get();

        return view('admin.manual-grades.index', compact('summaries', 'subjects'));
    }

    // ── Input Form ───────────────────────────────────────────────────

    /**
     * Form input nilai manual per mapel per kelas per semester.
     */
    public function inputForm(Request $request)
    {
        $user = auth()->user();

        $subjects = $user->role === 'teacher'
            ? $user->subjects()->orderBy('name')->get()
            : Subject::orderBy('name')->get();

        $classes = $this->getAccessibleClasses();

        // ── Auto-select jika hanya ada 1 pilihan ──────────────────────────

        // Auto-select mapel jika hanya 1
        if (!$request->subject_id && $subjects->count() === 1) {
            return redirect()->route('admin.manual-grades.input', array_merge(
                $request->query(),
                ['subject_id' => $subjects->first()->id]
            ));
        }

        // Auto-select kelas jika hanya 1
        if (!$request->class_id && $classes->count() === 1) {
            return redirect()->route('admin.manual-grades.input', array_merge(
                $request->query(),
                ['class_id' => $classes->first()->id]
            ));
        }

        // Auto-select semester dari settings jika belum dipilih
        if (!$request->semester) {
            // Ambil semester aktif dari settings, default 1
            $activeSemester = Setting::get('active_semester', '1');
            return redirect()->route('admin.manual-grades.input', array_merge(
                $request->query(),
                ['semester' => $activeSemester]
            ));
        }

        // Auto-select academic_year dari settings jika belum dipilih
        if (!$request->academic_year) {
            $activeYear = Setting::get('academic_year', '2024/2025');
            return redirect()->route('admin.manual-grades.input', array_merge(
                $request->query(),
                ['academic_year' => $activeYear]
            ));
        }

        // Try to resolve selected filters
        $selectedSubject = $request->subject_id
            ? Subject::find($request->subject_id)
            : null;
        $selectedClass = $request->class_id
            ? ClassRoom::find($request->class_id)
            : null;
        $semester    = $request->semester    ? (int) $request->semester    : null;
        $academicYear = $request->academic_year ?? null;

        // Auto-select jika teacher hanya punya 1 kelas dan belum ada class_id
        if ($user->role === 'teacher' && !$request->class_id && $classes->count() === 1) {
            $selectedClass = $classes->first();
        }

        // Validasi akses ke kelas yang dipilih
        if ($selectedClass) {
            $this->authorizeClass($selectedClass->id);
        }

        $jenjang     = $selectedClass?->grade  ? (int) $selectedClass->grade : null;

        // Teacher scope guard
        if ($user->role === 'teacher' && $selectedSubject) {
            if (!$user->subjects->contains('id', $selectedSubject->id)) {
                abort(403, 'Anda tidak mengampu mata pelajaran ini.');
            }
        }

        $students  = [];
        $cbtGrades = [];
        $manualGradesMap = [];
        $weight = null;

        if ($selectedSubject && $selectedClass && $semester && $academicYear) {
            // Siswa aktif di kelas ini
            $students = User::where('role', 'student')
                ->where('class_id', $selectedClass->id)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get();

            $jenjang = (int) $selectedClass->grade;

            // Bobot nilai
            $weight = GradeWeight::where('subject_id', $selectedSubject->id)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->where('jenjang', $jenjang)
                ->first();

            // Prefill existing manual grades & CBT info per student
            foreach ($students as $student) {
                // CBT values (read-only reference)
                $cbtGrades[$student->id] = GradeService::getCbtGrades(
                    $student, $selectedSubject, $semester, $academicYear
                );

                // Existing manual grades
                $manual = ManualGrade::where('student_id', $student->id)
                    ->where('subject_id', $selectedSubject->id)
                    ->where('semester', $semester)
                    ->where('academic_year', $academicYear)
                    ->get()
                    ->keyBy('grade_type');

                $manualGradesMap[$student->id] = [
                    'harian' => $manual['harian']?->score ?? '',
                    'uts'    => ($manual['uts'] ?? $manual['pts'] ?? null)?->score ?? '',
                    'uas'    => ($manual['uas'] ?? $manual['pas'] ?? null)?->score ?? '',
                ];
            }
        }

        return view('admin.manual-grades.input', compact(
            'subjects', 'classes', 'selectedSubject', 'selectedClass',
            'semester', 'academicYear', 'students', 'cbtGrades',
            'manualGradesMap', 'weight', 'jenjang'
        ));
    }

    // ── Store ────────────────────────────────────────────────────────

    /**
     * Simpan/update nilai manual per siswa (updateOrCreate).
     */
    public function store(StoreManualGradeRequest $request)
    {

        $user = auth()->user();
        $class = ClassRoom::findOrFail($request->class_id);
        $this->authorizeClass($class->id);
        $jenjang = (int) $class->grade;

        // Teacher scope guard
        if ($user->role === 'teacher') {
            $subject = Subject::findOrFail($request->subject_id);
            if (!$user->subjects->contains('id', $subject->id)) {
                abort(403, 'Anda tidak mengampu mata pelajaran ini.');
            }
        }

        $saved = 0;

        foreach ($request->grades ?? [] as $studentId => $gradeData) {
            foreach (['harian', 'uts', 'uas'] as $type) {
                if (isset($gradeData[$type]) && $gradeData[$type] !== '') {
                    $score = (float) $gradeData[$type];


                    ManualGrade::updateOrCreate(
                        [
                            'student_id'    => $studentId,
                            'subject_id'    => $request->subject_id,
                            'grade_type'    => $type,
                            'semester'      => $request->semester,
                            'academic_year' => $request->academic_year,
                        ],
                        [
                            'score'      => $score,
                            'teacher_id' => auth()->id(),
                            'jenjang'    => $jenjang,
                        ]
                    );

                    $saved++;
                }
            }
        }

        return redirect()->route('admin.manual-grades.input', [
            'subject_id'    => $request->subject_id,
            'class_id'      => $request->class_id,
            'semester'      => $request->semester,
            'academic_year' => $request->academic_year,
        ])->with('success', "Nilai berhasil disimpan ($saved entri).");
    }

    // ── Import Form ──────────────────────────────────────────────────

    /**
     * Form upload Excel untuk import nilai massal.
     */
    public function importForm(Request $request)
    {
        $user = auth()->user();
        $subjects = $user->role === 'teacher'
            ? $user->subjects()->orderBy('name')->get()
            : Subject::orderBy('name')->get();

        $classes = $this->getAccessibleClasses();

        $selectedSubject = $request->subject_id  ? Subject::find($request->subject_id)   : null;
        $selectedClass   = $request->class_id    ? ClassRoom::find($request->class_id)   : null;
        $semester        = $request->semester    ? (int) $request->semester              : null;
        $academicYear    = $request->academic_year ?? null;

        if ($selectedClass) {
            $this->authorizeClass($selectedClass->id);
        }

        return view('admin.manual-grades.import', compact(
            'subjects', 'classes', 'selectedSubject', 'selectedClass',
            'semester', 'academicYear'
        ));
    }

    // ── Import (process) ─────────────────────────────────────────────

    /**
     * Proses import nilai dari Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'          => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'subject_id'    => 'required|exists:subjects,id',
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|in:1,2',
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
        ]);

        $user  = auth()->user();
        $class = ClassRoom::findOrFail($request->class_id);
        $this->authorizeClass($class->id);

        // Teacher scope guard
        if ($user->role === 'teacher') {
            $subject = Subject::findOrFail($request->subject_id);
            if (!$user->subjects->contains('id', $subject->id)) {
                abort(403, 'Anda tidak mengampu mata pelajaran ini.');
            }
        }

        $importer = new ManualGradeImport(
            subjectId:    (int) $request->subject_id,
            semester:     (int) $request->semester,
            academicYear: $request->academic_year,
            teacherId:    auth()->id(),
            jenjang:      (int) $class->grade,
        );

        Excel::import($importer, $request->file('file'));

        $summary = [
            'success' => $importer->successCount,
            'skipped' => $importer->skippedCount,
            'errors'  => $importer->errors,
        ];

        return redirect()->route('admin.manual-grades.import', [
            'subject_id'    => $request->subject_id,
            'class_id'      => $request->class_id,
            'semester'      => $request->semester,
            'academic_year' => $request->academic_year,
        ])->with('import_summary', $summary);
    }

    // ── Download Template ────────────────────────────────────────────

    /**
     * Download template Excel (XLSX sederhana via response streaming).
     */
    public function downloadTemplate(Request $request)
    {
        $class   = $request->class_id   ? ClassRoom::find($request->class_id)  : null;
        if ($class) $this->authorizeClass($class->id);
        $subject = $request->subject_id ? Subject::find($request->subject_id)  : null;
        $semester    = $request->semester     ?? '';
        $academicYear = $request->academic_year ?? '';

        // Get students if class specified
        $students = [];
        if ($class) {
            $students = User::where('role', 'student')
                ->where('class_id', $class->id)
                ->where('status', 'Aktif')
                ->orderBy('name')
                ->get();
        }

        $filename = 'template_nilai'
            . ($subject ? '_' . str_replace(' ', '_', $subject->name) : '')
            . ($class   ? '_' . $class->name : '')
            . ($semester ? '_sem' . $semester : '')
            . '.xlsx';

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        // Build a simple XLSX using PhpSpreadsheet (available via Maatwebsite/Excel)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header info rows
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT NILAI MANUAL');
        $sheet->setCellValue('A2', 'Mapel: ' . ($subject?->name ?? '-'));
        $sheet->setCellValue('A3', 'Kelas: ' . ($class?->name ?? '-') . ' | Semester: ' . ($semester ?: '-') . ' | Tahun: ' . ($academicYear ?: '-'));
        $sheet->setCellValue('A4', 'PETUNJUK: Isi kolom Nilai Harian, UTS, dan UAS dengan angka 0-100. Kosongkan jika belum ada nilai.');

        // Column headers
        $sheet->setCellValue('A6', 'NIS');
        $sheet->setCellValue('B6', 'Nama Siswa');
        $sheet->setCellValue('C6', 'Nilai Harian');
        $sheet->setCellValue('D6', 'Nilai UTS');
        $sheet->setCellValue('E6', 'Nilai UAS');

        // Style header row
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E7FF'],
            ],
        ];
        $sheet->getStyle('A6:E6')->applyFromArray($headerStyle);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);

        // Fetch existing manual grades in bulk to avoid N+1 issues
        $grades = collect();
        if ($class && $subject && $semester && $academicYear) {
            $grades = ManualGrade::whereIn('student_id', $students->pluck('id'))
                ->where('subject_id', $subject->id)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->get()
                ->groupBy('student_id');
        }

        // Fill in eligible students and existing grades
        $row = 7;
        foreach ($students as $student) {
            $sheet->setCellValue("A$row", $student->nis ?? '');
            $sheet->setCellValue("B$row", $student->name);

            $studentGrades = $grades->get($student->id, collect())->keyBy('grade_type');
            $sheet->setCellValue("C$row", $studentGrades['harian']?->score ?? '');
            $sheet->setCellValue("D$row", ($studentGrades['uts'] ?? $studentGrades['pts'] ?? null)?->score ?? '');
            $sheet->setCellValue("E$row", ($studentGrades['uas'] ?? $studentGrades['pas'] ?? null)?->score ?? '');

            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, $headers);
    }

    // ── Helper ────────────────────────────────────────────────────────

    private function getAccessibleClasses()
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') {
            return ClassRoom::active()->orderBy('grade', 'asc')->orderBy('name', 'asc')->get();
        }
        return ClassRoom::where('homeroom_teacher_id', '=', $user->id, 'and')
            ->active()
            ->orderBy('grade', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    private function authorizeClass(int $classId): void
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') return;

        $allowed = ClassRoom::active()
            ->where('id', '=', $classId, 'and')
            ->where('homeroom_teacher_id', '=', $user->id, 'and')
            ->exists();

        if (!$allowed) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }
    }
}
