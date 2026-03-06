<?php

namespace App\Http\Controllers\Admin;

use App\Models\Exam;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExamCardController extends Controller
{
    /**
     * Display exam card for printing
     */
    public function printCard(Exam $exam)
    {
        // Get active students matching the exam's grade level (jenjang)
        $allStudents = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $exam->jenjang) // Filter by grade matching exam
            ->orderBy('grade', 'asc')
            ->orderBy('class_group', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Get exam attempts keyed by student_id for quick lookup
        $attemptsMap = $exam->attempts()
            ->with('student')
            ->get()
            ->keyBy('student_id');

        // Determine teacher name for signature
        // Priority: 1. Subject Teacher, 2. Exam Creator, 3. Generic Fallback
        $teacherName = 'Guru Mata Pelajaran';
        
        $teacher = $exam->subject->teachers->first();
        if ($teacher) {
            $teacherName = $teacher->name;
        } elseif ($exam->creator) {
            $teacherName = $exam->creator->name;
        } elseif (auth()->user()->role === 'teacher') {
            $teacherName = auth()->user()->name;
        }

        // Map all students with their attempts (if any)
        $students = $allStudents->map(function ($student) use ($exam, $attemptsMap) {
            $attempt = $attemptsMap->get($student->id);
            return [
                'student' => $student,
                'score' => $attempt?->final_score ?? 0,
                'status' => $attempt
                    ? ($attempt->final_score >= ($exam->subject->kkm ?? 75) ? 'Lulus' : 'Tidak Lulus')
                    : 'Belum Dinilai',
                'is_submitted' => $attempt ? true : false,
            ];
        });

        return view('admin.exams.print-card', compact('exam', 'students', 'teacherName'));
    }

    /**
     * Display exam cards for all students
     */
    public function printAllCards(Request $request)
    {
        // Get all published exams
        $exams = Exam::where('status', 'published')
            ->with('subject')
            ->orderBy('start_time')
            ->get();

        return view('admin.exams.print-all-cards', compact('exams'));
    }

    /**
     * Print student credentials for exam login
     */
    public function printStudentCredentials(Exam $exam)
    {
        // Get active students matching the exam's grade level (jenjang)
        $students = \App\Models\User::where('role', 'student')
            ->where('is_active', true)
            ->where('grade', $exam->jenjang)
            ->orderBy('grade', 'asc')
            ->orderBy('class_group', 'asc')
            ->orderBy('nis', 'asc')
            ->get()
            ->map(function ($student) use ($exam) {
                return [
                    'student' => $student,
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'class' => "Kelas {$student->grade} - {$student->class_group}",
                    'password' => $student->password_display ?? '-',
                    'exam' => $exam,
                ];
            });

        return view('admin.exams.print-credentials', compact('exam', 'students'));
    }
}
