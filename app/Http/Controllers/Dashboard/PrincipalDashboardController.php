<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Subject;
use App\Models\ManualGrade;
use App\Models\GradeLock;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PrincipalDashboardController extends Controller
{
    public function index()
    {
        // ── 0. Configs ──────────────────────────────────────────────────
        // Jika app('configs') tidak tersedia, ambil dari Setting::get()
        $semester    = Setting::get('current_semester', 1);
        $academicYear= Setting::get('academic_year', '2024/2025');

        // ── 1. Ringkasan Hari Ini ────────────────────────────────────────
        $activeExams = Exam::where('status', 'published')->count();

        $studentsInExam = ExamAttempt::where('status', 'in_progress')->count();

        $recentlyLocked = GradeLock::where('is_locked', true)
            ->where('locked_at', '>=', now()->startOfDay())
            ->count();

        // ── 2. Semua kelas ───────────────────────────────────────────────
        $classes = ClassRoom::with('homeroomTeacher')
            ->withCount('students')
            ->get();

        // ── 3. Progress kelengkapan nilai per kelas ──────────────────────
        $classProgress = [];
        foreach ($classes as $class) {
            $gradeLevel = $class->getGradeLevel();
            $gradeLabel = match($gradeLevel) {
                10 => 'X', 11 => 'XI', 12 => 'XII',
                default => null,
            };

            // Mapel aktif untuk jenjang ini
            $subjectQuery = Subject::whereNotNull('category');
            if ($gradeLabel) {
                $subjectQuery->forGrade($gradeLabel);
            }
            $subjects    = $subjectQuery->get();
            $totalSubjects = $subjects->count();

            if ($totalSubjects === 0) {
                $classProgress[] = [
                    'class'          => $class,
                    'total_subjects' => 0,
                    'locked_subjects'=> 0,
                    'pct'            => 0,
                    'homeroom_name'  => $class->homeroomTeacher?->name ?? '-',
                ];
                continue;
            }

            // Hitung mapel yang sudah dikunci
            $lockedCount = GradeLock::whereIn('subject_id',
                    $subjects->pluck('id'))
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->where('is_locked', true)
                ->count();

            $pct = round($lockedCount / $totalSubjects * 100);

            $classProgress[] = [
                'class'          => $class,
                'total_subjects' => $totalSubjects,
                'locked_subjects'=> $lockedCount,
                'pct'            => $pct,
                'homeroom_name'  => $class->homeroomTeacher?->name ?? '-',
            ];
        }

        // Urutkan: yang belum lengkap di atas
        usort($classProgress, fn($a, $b) => $a['pct'] <=> $b['pct']);

        // ── 4. Rekap nilai per kelas (rata-rata) ─────────────────────────
        // Ambil dari manual_grades + exam_attempts — estimasi cepat
        $classAverages = [];
        foreach ($classes as $class) {
            $studentIds = $class->students()
                ->pluck('id')->toArray();

            if (empty($studentIds)) {
                $classAverages[$class->id] = null;
                continue;
            }

            // Rata-rata dari manual grades
            $avg = ManualGrade::whereIn('student_id', $studentIds)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->avg('score');

            $classAverages[$class->id] = $avg
                ? round($avg, 1) : null;
        }

        // ── 5. Siswa di bawah KKM banyak ────────────────────────────────
        // Siswa dengan lebih dari 2 mapel di bawah KKM
        $atRiskStudents = [];

        $allStudents = User::where('role', 'student')
            ->aktif()
            ->with('classRoom')
            ->get(['id', 'name', 'nis', 'class_id']);

        foreach ($allStudents as $student) {
            if (!$student->classRoom) continue;

            $gradeLevel = $student->classRoom->getGradeLevel();
            $gradeLabel = match($gradeLevel) {
                10 => 'X', 11 => 'XI', 12 => 'XII',
                default => null,
            };

            $subjects = Subject::whereNotNull('category')
                ->when($gradeLabel,
                    fn($q) => $q->forGrade($gradeLabel)
                )
                ->get();

            $belowKkm = 0;
            foreach ($subjects as $subject) {
                // Cek nilai manual saja untuk performa
                $score = ManualGrade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('semester', $semester)
                    ->where('academic_year', $academicYear)
                    ->whereIn('grade_type', ['uas', 'pas'])
                    ->value('score');

                $kkm = $subject->kkm ?? 75;
                if ($score !== null && $score < $kkm) {
                    $belowKkm++;
                }
            }

            if ($belowKkm >= 2) {
                $atRiskStudents[] = [
                    'student'   => $student,
                    'class'     => $student->classRoom,
                    'below_kkm' => $belowKkm,
                    'total'     => $subjects->count(),
                ];
            }
        }

        // Urutkan: terbanyak di bawah KKM di atas, ambil 10
        usort($atRiskStudents,
            fn($a, $b) => $b['below_kkm'] <=> $a['below_kkm']
        );
        $atRiskStudents = array_slice($atRiskStudents, 0, 10);

        // ── 6. Mapel dengan rata-rata terendah ───────────────────────────
        $subjectAverages = ManualGrade::where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->whereIn('grade_type', ['uas', 'pas'])
            ->select('subject_id',
                DB::raw('AVG(score) as avg_score'),
                DB::raw('COUNT(DISTINCT student_id) as student_count')
            )
            ->groupBy('subject_id')
            ->with('subject:id,name,category,kkm')
            ->get()
            ->sortBy('avg_score')
            ->take(5);

        // ── 7. Total statistik sekolah ───────────────────────────────────
        $schoolStats = [
            'total_classes'  => $classes->count(),
            'total_students' => User::where('role', 'student')
                ->aktif()->count(),
            'total_teachers' => User::where('role', 'teacher')
                ->count(),
            'total_exams'    => Exam::where('status', 'published')
                ->count(),
        ];

        return view('dashboard.principal', compact(
            'activeExams',
            'studentsInExam',
            'recentlyLocked',
            'classProgress',
            'classAverages',
            'atRiskStudents',
            'subjectAverages',
            'schoolStats',
            'semester',
            'academicYear',
        ));
    }
}
