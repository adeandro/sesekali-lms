<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutosaveRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamViolation;
use App\Services\ExamEngineService;
use Illuminate\Http\Request;

class StudentExamController extends Controller
{
    /**
     * Display list of available exams.
     */
    public function index()
    {
        $exams = ExamEngineService::getAvailableExams(auth()->user());
        $submittedExams = ExamAttempt::where('student_id', auth()->id())
            ->where('status', 'submitted')
            ->pluck('exam_id')
            ->toArray();

        return view('student.exams.index', compact('exams', 'submittedExams'));
    }

    /**
     * Start an exam.
     */
    public function start(Exam $exam)
    {
        try {
            // Check if exam is available
            $now = now();
            if ($exam->status !== 'published') {
                return redirect()->route('student.exams.index')
                    ->with('error', 'This exam is not available.');
            }

            if ($exam->start_time > $now) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'This exam has not started yet.');
            }

            if ($exam->end_time < $now) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'This exam has ended.');
            }

            // Start exam
            $attempt = ExamEngineService::startExam($exam, auth()->user());

            return redirect()->route('student.exams.take', $attempt->id);
        } catch (\Exception $e) {
            return redirect()->route('student.exams.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show exam taking interface.
     */
    public function take(ExamAttempt $attempt)
    {
        // Verify student owns this attempt
        if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
            abort(403, 'Unauthorized');
        }

        // Check if already submitted
        if ($attempt->isSubmitted()) {
            return redirect()->route('student.exams.result', $attempt->id);
        }

        // Check if time expired
        if ($attempt->hasTimeExpired()) {
            ExamEngineService::autoSubmitIfExpired($attempt);
            return redirect()->route('student.exams.result', $attempt->id);
        }

        // Get exam with questions
        $attempt = ExamEngineService::getAttemptWithQuestions($attempt);
        $questions = ExamEngineService::getExamQuestions($attempt);
        $remainingMinutes = $attempt->getRemainingTimeMinutes();

        return view('student.exams.take', compact('attempt', 'questions', 'remainingMinutes'));
    }

    /**
     * Autosave answer via AJAX.
     */
    public function autosave(AutosaveRequest $request, ExamAttempt $attempt)
    {
        try {
            // Verify student owns this attempt
            if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check if already submitted
            if ($attempt->isSubmitted()) {
                return response()->json(['success' => false, 'message' => 'Exam already submitted'], 400);
            }

            // Check if time expired
            if ($attempt->hasTimeExpired()) {
                return response()->json(['success' => false, 'message' => 'Time expired'], 400);
            }

            // Autosave answer
            $selectedAnswer = $request->input('selected_answer');
            $essayAnswer = $request->input('essay_answer');

            ExamEngineService::autosaveAnswer($attempt, $request->question_id, $selectedAnswer, $essayAnswer);

            return response()->json(['success' => true, 'message' => 'Answer saved']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Submit exam.
     */
    public function submit(Request $request, ExamAttempt $attempt)
    {
        try {
            // Verify student owns this attempt
            if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
                abort(403, 'Unauthorized');
            }

            // Check if already submitted
            if ($attempt->isSubmitted()) {
                return redirect()->route('student.exams.result', $attempt->id);
            }

            // Submit exam
            $attempt = ExamEngineService::submitExam($attempt);

            return redirect()->route('student.exams.result', $attempt->id)
                ->with('success', 'Exam submitted successfully. Your score will be calculated.');
        } catch (\Exception $e) {
            return redirect()->route('student.exams.take', $attempt->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show exam result.
     */
    public function result(ExamAttempt $attempt)
    {
        // Verify student owns this attempt
        if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
            abort(403, 'Unauthorized');
        }

        // Get result
        try {
            $attempt = ExamEngineService::getExamResult($attempt);
            // Use getExamQuestions to get questions in the same order student saw them
            $attempt = ExamEngineService::getAttemptWithQuestions($attempt);
            $questions = ExamEngineService::getExamQuestions($attempt);
            $answers = $attempt->answers()->with('question')->get();

            // Calculate statistics
            $correct_count = $answers->filter(fn($a) => $a->is_correct === true)->count();
            $incorrect_count = $answers->filter(fn($a) => $a->is_correct === false)->count();
            $unanswered_count = $answers->filter(fn($a) => $a->is_correct === null)->count();

            return view('student.exams.result', compact('attempt', 'questions', 'answers', 'correct_count', 'incorrect_count', 'unanswered_count'));
        } catch (\Exception $e) {
            return redirect()->route('student.exams.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get remaining time via AJAX.
     */
    public function getRemainingTime(ExamAttempt $attempt)
    {
        // Verify student owns this attempt
        if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
            return response()->json(['success' => false], 403);
        }

        $remainingMinutes = $attempt->getRemainingTimeMinutes();
        $seconds = ($remainingMinutes % 1) * 60;
        $minutes = floor($remainingMinutes);

        return response()->json([
            'success' => true,
            'remaining_minutes' => $minutes,
            'remaining_seconds' => (int)$seconds,
            'total_seconds' => $remainingMinutes * 60,
            'expired' => $attempt->hasTimeExpired(),
        ]);
    }

    /**
     * Save exam violation (cheating attempt).
     */
    public function saveViolation(Request $request, ExamAttempt $attempt)
    {
        try {
            // Verify student owns this attempt
            if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'violation_type' => 'required|in:tab_switch,fullscreen_exit,keyboard_shortcut,right_click,copy_paste,dev_tools,printscreen',
                'description' => 'nullable|string|max:255',
            ]);

            // Log violation
            $violation = ExamViolation::create([
                'exam_id' => $attempt->exam_id,
                'user_id' => auth()->id(),
                'violation_type' => $validated['violation_type'],
                'description' => $validated['description'] ?? null,
                'violation_count' => 1,
                'detected_at' => now(),
            ]);

            // Check total violations for this exam session
            $totalViolations = ExamViolation::where('exam_id', $attempt->exam_id)
                ->where('user_id', auth()->id())
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Violation recorded',
                'violation_count' => $totalViolations,
                'max_violations' => 3,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Force submit exam (auto-submit on max violations).
     */
    public function forceSubmit(Request $request, ExamAttempt $attempt)
    {
        try {
            // Verify student owns this attempt
            if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check if already submitted
            if ($attempt->isSubmitted()) {
                return response()->json(['success' => false, 'message' => 'Exam already submitted'], 400);
            }

            // Force submit exam
            $attempt = ExamEngineService::submitExam($attempt);

            return response()->json([
                'success' => true,
                'message' => 'Exam submitted due to violation limit exceeded',
                'redirect_url' => route('student.exams.result', $attempt->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
