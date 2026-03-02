<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    /**
     * Display teacher dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $subjectIds = $user->subjects->pluck('id');

        $stats = [
            'total_questions' => Question::whereIn('subject_id', $subjectIds)->count(),
            'total_exams' => Exam::whereIn('subject_id', $subjectIds)->count(),
            'total_students' => ExamAttempt::whereHas('exam', function ($query) use ($subjectIds) {
                $query->whereIn('subject_id', $subjectIds);
            })->distinct('student_id')->count('student_id'),
            'total_remedial' => ExamAttempt::whereHas('exam', function ($query) use ($subjectIds) {
                $query->whereIn('subject_id', $subjectIds);
            })->where('status', 'submitted')
              ->get()
              ->filter(fn($attempt) => $attempt->final_score < ($attempt->exam->subject->kkm ?? 75))
              ->count(),
            'subject_names' => $user->subjects->pluck('name')->implode(', '),
        ];

        return view('dashboard.teacher', compact('stats'));
    }
}
