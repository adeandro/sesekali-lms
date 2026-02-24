<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\EssayGradingService;
use App\Exports\ExamResultsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResultController extends Controller
{
    /**
     * Display list of exams with statistics.
     */
    public function index()
    {
        $exams = Exam::with('subject')
            ->where('status', 'published')
            ->get()
            ->map(function ($exam) {
                $stats = EssayGradingService::getExamStatistics($exam->id);
                $exam->stats = $stats;
                return $exam;
            });

        return view('admin.results.index', compact('exams'));
    }

    /**
     * Display exam detail with student results.
     */
    public function show(Exam $exam, Request $request)
    {
        // Validate exam exists
        $exam->load('subject', 'questions');

        // Get attempts with filtering
        $query = $exam->attempts()
            ->where('status', 'submitted')
            ->with('student');

        // Filter by grade if provided
        if ($request->get('class')) {
            $query->whereHas('student', fn($q) => $q->where('grade', $request->get('class')));
        }

        // Search by name if provided
        if ($request->get('search')) {
            $search = $request->get('search');
            $query->whereHas(
                'student',
                fn($q) =>
                $q->where('name', 'like', "%$search%")
                    ->orWhere('nis', 'like', "%$search%")
            );
        }

        // Order by score descending and add ranking
        $attempts = $query->orderByDesc('final_score')->get()
            ->map(function ($attempt, $index) {
                $attempt->ranking = $index + 1;
                return $attempt;
            });

        // Get statistics
        $stats = EssayGradingService::getExamStatistics($exam->id);

        // Get available grades for filter
        $classes = ExamAttempt::whereHas('student')
            ->where('exam_id', $exam->id)
            ->with('student')
            ->get()
            ->pluck('student.grade')
            ->unique()
            ->filter()
            ->sort()
            ->values();

        return view('admin.results.show', compact('exam', 'attempts', 'stats', 'classes'));
    }

    /**
     * Display essay review page for a specific attempt.
     */
    public function review(Exam $exam, ExamAttempt $attempt)
    {
        // Validate attempt belongs to exam
        if ($attempt->exam_id !== $exam->id) {
            abort(404, 'Attempt not found for this exam');
        }

        // Load essay answers
        $essayAnswers = EssayGradingService::getEssayAnswers($attempt);

        // Get student info
        $attempt->load('student');

        return view('admin.results.review', compact('exam', 'attempt', 'essayAnswers'));
    }

    /**
     * Update essay grades for an attempt.
     */
    public function updateGrades(Exam $exam, ExamAttempt $attempt, Request $request)
    {
        // Validate attempt belongs to exam
        if ($attempt->exam_id !== $exam->id) {
            abort(404, 'Attempt not found for this exam');
        }

        // Validate input
        $validated = $request->validate([
            'scores.*' => 'required|numeric|min:0|max:100',
        ], [
            'scores.*.required' => 'All scores are required',
            'scores.*.numeric' => 'Scores must be numeric',
            'scores.*.min' => 'Minimum score is 0',
            'scores.*.max' => 'Maximum score is 100',
        ]);

        try {
            // Update grades
            EssayGradingService::saveEssayScores($attempt, $validated['scores']);

            return redirect()->route('admin.results.show', $exam->id)
                ->with('success', 'Essay grades updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error updating grades: ' . $e->getMessage());
        }
    }

    /**
     * Export exam results to Excel.
     */
    public function export(Exam $exam)
    {
        $filename = 'exam_results_' . $exam->id . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new ExamResultsExport($exam), $filename);
    }
}
