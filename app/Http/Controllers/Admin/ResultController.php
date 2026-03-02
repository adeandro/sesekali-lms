<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\EssayGradingService;
use App\Exports\ExamResultsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ResultController extends Controller
{
    /**
     * Display list of exams with statistics.
     */
    public function index()
    {
        $query = Exam::with('subject')
            ->where('status', 'published');

        // Scoping for Teacher
        if (auth()->user()->role === 'teacher') {
            $mySubjectIds = auth()->user()->subjects->pluck('id');
            $query->whereIn('subject_id', $mySubjectIds);
        }

        $exams = $query->get()
            ->map(function ($exam) {
                $stats = EssayGradingService::getExamStatistics($exam->id);
                $exam->stats = $stats;
                return $exam;
            });

        return view('admin.results.index', compact('exams'));
    }

    /**
     * Display exam detail with student results.
     * REFACTORED: Uses explicit ID parameter to avoid implicit model binding issues
     */
    public function show($examId, Request $request)
    {
        // Log the request for debugging
        Log::info('Admin Results Show - Request Details', [
            'exam_id' => $examId,
            'exam_id_type' => gettype($examId),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);

        // Validate exam exists via explicit find()
        $exam = Exam::find($examId);
        
        if (!$exam || (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id))) {
            Log::warning('Admin Results Show - Exam Not Found', [
                'exam_id' => $examId,
                'user_id' => auth()->id(),
                'available_exams' => Exam::pluck('id')->toArray(),
            ]);
            return redirect()->route('admin.results.index')
                ->with('error', 'Ujian dengan ID ' . $examId . ' tidak ditemukan.');
        }

        // Load relationship data
        $exam->load('subject', 'questions');

        Log::info('Admin Results Show - Exam Found', [
            'exam_id' => $exam->id,
            'exam_name' => $exam->title,
            'exam_status' => $exam->status,
            'soft_deleted' => $exam->trashed(),
        ]);

        // Get attempts with filtering
        $query = $exam->attempts()
            ->where('status', 'submitted')
            ->with('student');

        // Filter by Class/Rombel if provided
        if ($request->get('class')) {
            $classData = explode('|', $request->get('class'));
            if (count($classData) === 2) {
                $query->whereHas('student', fn($q) => 
                    $q->where('grade', $classData[0])
                      ->where('class_group', $classData[1])
                );
            }
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

        // Get available classes/rombels for filter (Grade + Class Group)
        $classes = ExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->whereHas('student')
            ->with('student')
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->student->grade . '|' . $attempt->student->class_group,
                    'name' => 'Kelas ' . $attempt->student->grade . ' - ' . $attempt->student->class_group
                ];
            })
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('admin.results.show', compact('exam', 'attempts', 'stats', 'classes'));
    }

    /**
     * Display essay review page for a specific attempt.
     * REFACTORED: Uses explicit ID parameters with manual validation and comprehensive error handling
     */
    public function review($examId, $attemptId)
    {
        // Log the detailed request
        Log::info('Admin Results Review - Request Initiated', [
            'exam_id' => $examId,
            'exam_id_type' => gettype($examId),
            'attempt_id' => $attemptId,
            'attempt_id_type' => gettype($attemptId),
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role,
            'timestamp' => now(),
        ]);

        // Convert to integers to ensure type consistency
        $examId = (int)$examId;
        $attemptId = (int)$attemptId;

        // Step 1: Validate and fetch Exam
        $exam = Exam::find($examId);
        
        if (!$exam || (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id))) {
            Log::warning('Admin Results Review - Exam Not Found', [
                'exam_id' => $examId,
                'attempt_id' => $attemptId,
                'available_exams' => Exam::pluck('id', 'title')->toArray(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->route('admin.results.index')
                ->with('error', "Ujian dengan ID $examId tidak ditemukan dalam sistem.");
        }

        Log::info('Admin Results Review - Exam Found', [
            'exam_id' => $exam->id,
            'exam_title' => $exam->title,
            'exam_status' => $exam->status,
        ]);

        // Step 2: Validate and fetch ExamAttempt
        $attempt = ExamAttempt::find($attemptId);
        
        if (!$attempt) {
            Log::warning('Admin Results Review - Attempt Not Found', [
                'exam_id' => $examId,
                'attempt_id' => $attemptId,
                'available_attempts_for_exam' => ExamAttempt::where('exam_id', $examId)
                    ->pluck('id')
                    ->toArray(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->route('admin.results.show', $examId)
                ->with('error', "Respons ujian dengan ID $attemptId tidak ditemukan dalam sistem.");
        }

        Log::info('Admin Results Review - Attempt Found', [
            'attempt_id' => $attempt->id,
            'attempt_exam_id' => $attempt->exam_id,
            'attempt_student_id' => $attempt->student_id,
            'attempt_status' => $attempt->status,
            'attempt_score' => $attempt->final_score,
        ]);

        // Step 3: Validate relationship - Attempt must belong to Exam
        if ((string)$attempt->exam_id !== (string)$examId) {
            Log::error('Admin Results Review - Attempt Exam Mismatch', [
                'requested_exam_id' => $examId,
                'attempt_exam_id' => $attempt->exam_id,
                'attempt_id' => $attemptId,
                'user_id' => auth()->id(),
                'note' => 'Attempt does not belong to requested exam',
            ]);
            return redirect()->route('admin.results.show', $examId)
                ->with('error', "Respons ujian tidak sesuai dengan ujian yang diminta. Silakan kembali dan coba lagi.");
        }

        Log::info('Admin Results Review - Relationships Validated', [
            'exam_id' => $examId,
            'attempt_id' => $attemptId,
            'exam_attempt_match' => true,
        ]);

        // Step 4: Load required data with eager loading
        try {
            $attempt->load([
                'student',
                'exam.subject',
                'answers' => function ($query) {
                    $query->with('question')
                        ->orderBy('question_id');
                }
            ]);

            Log::info('Admin Results Review - Data Loaded Successfully', [
                'attempt_id' => $attempt->id,
                'answers_count' => $attempt->answers->count(),
                'student_name' => $attempt->student->name ?? 'Unknown',
            ]);
        } catch (\Exception $e) {
            Log::error('Admin Results Review - Data Loading Error', [
                'exam_id' => $examId,
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('admin.results.show', $examId)
                ->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }

        // Step 5: Load essay and multiple choice answers
        $essayAnswers = EssayGradingService::getEssayAnswers($attempt);
        $mcAnswers = $attempt->answers->filter(fn($a) => $a->question->question_type === 'multiple_choice');

        Log::info('Admin Results Review - Review Page Rendered', [
            'exam_id' => $examId,
            'attempt_id' => $attemptId,
            'essay_answers' => count($essayAnswers),
            'mc_answers' => $mcAnswers->count(),
        ]);

        return view('admin.results.review', compact('exam', 'attempt', 'essayAnswers', 'mcAnswers'));
    }

    /**
     * Update essay grades for an attempt.
     * REFACTORED: Uses explicit ID parameters with validation
     */
    public function updateGrades($examId, $attemptId, Request $request)
    {
        // Convert to integers
        $examId = (int)$examId;
        $attemptId = (int)$attemptId;

        Log::info('Admin Results Update Grades - Request Started', [
            'exam_id' => $examId,
            'attempt_id' => $attemptId,
            'user_id' => auth()->id(),
        ]);

        // Validate exam exists
        $exam = Exam::find($examId);
        if (!$exam || (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id))) {
            Log::warning('Update Grades - Exam Not Found', ['exam_id' => $examId]);
            return redirect()->route('admin.results.index')
                ->with('error', 'Ujian tidak ditemukan.');
        }

        // Validate attempt exists
        $attempt = ExamAttempt::find($attemptId);
        if (!$attempt) {
            Log::warning('Update Grades - Attempt Not Found', ['attempt_id' => $attemptId]);
            return redirect()->route('admin.results.show', $examId)
                ->with('error', 'Respons ujian tidak ditemukan.');
        }

        // Validate relationship
        if ((string)$attempt->exam_id !== (string)$examId) {
            Log::error('Update Grades - Mismatch', [
                'exam_id' => $examId,
                'attempt_exam_id' => $attempt->exam_id,
            ]);
            return redirect()->route('admin.results.show', $examId)
                ->with('error', 'Data tidak sesuai.');
        }

        // Validate input
        $validated = $request->validate([
            'scores.*' => 'required|numeric|min:0|max:100',
        ], [
            'scores.*.required' => 'Semua nilai harus diisi',
            'scores.*.numeric' => 'Nilai harus berupa angka',
            'scores.*.min' => 'Nilai minimum adalah 0',
            'scores.*.max' => 'Nilai maksimum adalah 100',
        ]);

        try {
            // Update grades
            EssayGradingService::saveEssayScores($attempt, $validated['scores']);

            Log::info('Admin Results Update Grades - Success', [
                'exam_id' => $examId,
                'attempt_id' => $attemptId,
                'scores_updated' => count($validated['scores']),
            ]);

            return redirect()->route('admin.results.show', $examId)
                ->with('success', 'Nilai essay berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Admin Results Update Grades - Error', [
                'exam_id' => $examId,
                'attempt_id' => $attemptId,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->with('error', 'Terjadi kesalahan saat memperbarui nilai: ' . $e->getMessage());
        }
    }

    /**
     * Export exam results to Excel.
     * REFACTORED: Uses explicit ID parameter with validation
     */
    public function export($examId)
    {
        // Convert to integer
        $examId = (int)$examId;

        Log::info('Admin Results Export - Started', [
            'exam_id' => $examId,
            'user_id' => auth()->id(),
        ]);

        // Validate exam exists
        $exam = Exam::find($examId);
        if (!$exam || (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $exam->subject_id))) {
            Log::warning('Export - Exam Not Found', ['exam_id' => $examId]);
            return redirect()->route('admin.results.index')
                ->with('error', 'Ujian tidak ditemukan.');
        }

        $filename = 'hasil_ujian_' . $exam->id . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        Log::info('Admin Results Export - File Generated', [
            'exam_id' => $examId,
            'filename' => $filename,
            'filters' => request()->all(),
        ]);

        return Excel::download(new ExamResultsExport($exam, request()->all()), $filename);
    }
}

