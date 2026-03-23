<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLock;
use App\Models\Subject;
use App\Models\ClassRoom;
use Illuminate\Http\Request;

class GradeLockController extends Controller
{
    /**
     * Toggle lock/unlock nilai satu mapel
     * POST /admin/grade-locks/toggle
     */
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'subject_id'    => 'required|exists:subjects,id',
            'semester'      => 'required|integer|in:1,2',
            'academic_year' => 'required|string',
        ]);

        $result = GradeLock::toggle(
            $data['subject_id'],
            $data['semester'],
            $data['academic_year'],
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'locked'  => $result['locked'],
            'message' => $result['locked']
                ? 'Nilai berhasil dikunci'
                : 'Nilai berhasil dibuka',
        ]);
    }

    /**
     * Cek status lock semua mapel untuk kelas tertentu
     * GET /admin/grade-locks/status
     */
    public function status(Request $request)
    {
        $request->validate([
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|integer',
            'academic_year' => 'required|string',
            'report_type'   => 'nullable|string',
        ]);

        $class = ClassRoom::findOrFail($request->class_id);
        $gradeLevel  = $class->getGradeLevel();
        $gradeLabel  = match($gradeLevel) {
            10 => 'X', 11 => 'XI', 12 => 'XII',
            default => null,
        };

        // Ambil semua mapel aktif untuk jenjang ini
        $subjectQuery = Subject::whereNotNull('category')
            ->where('include_in_report', true);
        if ($gradeLabel) {
            $subjectQuery->forGrade($gradeLabel);
        }
        $subjects = $subjectQuery->orderBy('sort_order')->get();

        $semester     = (int) $request->semester;
        $academicYear = $request->academic_year;
        $reportType   = $request->report_type ?? 'semester';
        $isMid        = $reportType === 'mid';

        // Ambil semua siswa di kelas ini
        $students = $class->students()->get();
        $totalStudents = $students->count();

        $result = [];
        $lockedCount = 0;

        foreach ($subjects as $subject) {
            $isLocked = GradeLock::isLocked(
                $subject->id, $semester, $academicYear
            );

            if ($isLocked) $lockedCount++;

            // Hitung kelengkapan nilai per mapel
            $studentsWithGrade = 0;
            foreach ($students as $student) {
                $hasGrade = $this->studentHasGrade(
                    $student, $subject,
                    $semester, $academicYear,
                    $isMid
                );
                if ($hasGrade) $studentsWithGrade++;
            }

            $lockRecord = GradeLock::where([
                'subject_id'    => $subject->id,
                'semester'      => $semester,
                'academic_year' => $academicYear,
            ])->first();

            $result[] = [
                'subject_id'       => $subject->id,
                'subject_name'     => $subject->name,
                'category'         => $subject->category,
                'is_locked'        => $isLocked,
                'locked_by_name'   => $lockRecord?->lockedBy?->name,
                'locked_at'        => $lockRecord?->locked_at
                    ?->translatedFormat('d M Y H:i'),
                'students_total'   => $totalStudents,
                'students_graded'  => $studentsWithGrade,
                'is_complete'      => $studentsWithGrade >= $totalStudents
                    && $totalStudents > 0,
            ];
        }

        $totalSubjects   = count($result);
        $completeSubjects = count(array_filter(
            $result, fn($r) => $r['is_complete']
        ));

        return response()->json([
            'subjects'         => $result,
            'total_subjects'   => $totalSubjects,
            'complete_subjects'=> $completeSubjects,
            'all_complete'     => $completeSubjects >= $totalSubjects
                && $totalSubjects > 0,
            'locked_count'     => $lockedCount,
        ]);
    }

    /**
     * Cek apakah siswa sudah punya nilai untuk mapel ini
     */
    private function studentHasGrade(
        $student, $subject,
        int $semester, string $academicYear,
        bool $isMid
    ): bool {
        // Cek nilai manual
        $manualTypes = $isMid
            ? ['harian', 'pts']
            : ['harian', 'pts', 'uts', 'uas'];

        $hasManual = \App\Models\ManualGrade
            ::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->whereIn('grade_type', $manualTypes)
            ->exists();

        if ($hasManual) return true;

        // Cek nilai CBT (exam_attempts)
        $hasCbt = \App\Models\ExamAttempt
            ::whereHas('exam', function ($q) use (
                $subject, $semester, $academicYear
            ) {
                $q->where('subject_id', $subject->id)
                  ->where('semester', $semester)
                  ->where('academic_year', $academicYear)
                  ->where('include_in_report', true);
            })
            ->where('student_id', $student->id)
            ->whereNotNull('final_score')
            ->exists();

        return $hasCbt;
    }
}
