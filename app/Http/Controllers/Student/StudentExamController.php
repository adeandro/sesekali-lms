<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutosaveRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamViolation;
use App\Models\ExamSession;
use App\Services\ExamEngineService;
use Illuminate\Http\Request;

class StudentExamController extends Controller
{
    /**
     * Display list of available exams.
     */
    public function index()
    {
        $student = auth()->user();
        $exams = ExamEngineService::getAvailableExams($student);

        // Get all attempts keyed by exam_id for quick lookup
        // Includes submitted, active, and in_progress attempts
        $attempts = ExamAttempt::where('student_id', auth()->id())
            ->whereIn('status', ['submitted', 'active', 'in_progress'])
            ->get()  // Get collection first
            ->keyBy('exam_id');  // Then keyBy on the collection

        return view('student.exams.index', compact('exams', 'attempts'));
    }

    /**
     * Start an exam - show token validation form.
     */
    public function start(Exam $exam)
    {
        try {
            // Check if exam is available
            $now = now();
            if ($exam->status !== 'published') {
                return redirect()->route('student.exams.index')
                    ->with('error', 'Ujian ini tidak tersedia.');
            }

            if ($exam->start_time > $now) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'Ujian ini belum dimulai.');
            }

            if ($exam->end_time < $now) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'Ujian ini telah berakhir.');
            }

            // Show token validation form
            return view('student.exams.token-validation', compact('exam'));
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

        // Check if session is locked (force logged out)
        if ($attempt->is_session_locked) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Sesi ujian anda telah dikunci oleh pengawas.');
        }

        // Check if time expired
        if ($attempt->hasTimeExpired()) {
            ExamEngineService::autoSubmitIfExpired($attempt);
            return redirect()->route('student.exams.result', $attempt->id);
        }

        // Verify valid token was used
        if (!$attempt->token) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Token ujian tidak valid.');
        }

        // Create or update session
        $session = ExamSession::firstOrCreate(
            ['exam_attempt_id' => $attempt->id],
            [
                'exam_id' => $attempt->exam_id,
                'student_id' => auth()->id(),
                'session_id' => 'session_' . md5($attempt->id . auth()->id() . time()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'started_at' => $attempt->started_at,
                'last_heartbeat' => now(),
                'status' => 'active',
            ]
        );

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

            // Autosave answer - pass question for text extraction
            $selectedAnswer = $request->input('selected_answer');
            $essayAnswer = $request->input('essay_answer');
            $question = \App\Models\Question::find($request->question_id);

            ExamEngineService::autosaveAnswer($attempt, $request->question_id, $selectedAnswer, $essayAnswer, $question);

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

            // Check Achievements
            if (\App\Models\Setting::get('enable_gamification', '1') == '1') {
                app(\App\Services\AchievementService::class)->checkSubmissionAchievements($attempt);
            }

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
     * Print professional report for student.
     */
    public function printReport(ExamAttempt $attempt)
    {
        // Verify student owns this attempt
        if (!ExamEngineService::canAccessAttempt($attempt, auth()->user())) {
            abort(403, 'Unauthorized');
        }

        try {
            $exam = $attempt->exam;
            
            $finalScore = $attempt->is_adjusted ? $attempt->adjusted_score : ($attempt->final_score ?? 0);

            // Map student with their attempt data (matching ExamCardController format)
            $students = collect([[
                'student' => $attempt->student,
                'score' => $finalScore,
                'is_adjusted' => $attempt->is_adjusted ?? false,
                'status' => ($finalScore >= ($exam->subject->kkm ?? 75) ? 'Lulus' : 'Tidak Lulus'),
                'is_submitted' => true,
            ]]);

            // Determine teacher name for signature
            // Priority: 1. Subject Teacher, 2. Exam Creator, 3. Generic Fallback
            $teacherName = 'Guru Mata Pelajaran';
            $signatureUser = null;
            
            $teacher = $exam->subject->teachers->first();
            if ($teacher) {
                $teacherName = $teacher->full_name;
                $signatureUser = $teacher;
            } elseif ($exam->creator) {
                $teacherName = $exam->creator->full_name;
                $signatureUser = $exam->creator;
            }

            return view('admin.exams.print-card', compact('exam', 'students', 'teacherName', 'signatureUser'));
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
                'violation_type' => 'required|in:tab_switch,fullscreen_exit,keyboard_shortcut,right_click,copy_paste,dev_tools,printscreen,floating_window',
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

    /**
     * Validate token and start exam.
     */
    public function validateAndStart(Request $request, Exam $exam)
    {
        $request->validate([
            'token' => 'required|string|max:20',
        ]);

        try {
            // 1. VERIFY EXAM STATUS & TIMING
            if ($exam->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian ini tidak tersedia untuk diikuti.',
                ], 400);
            }

            $now = now();
            if ($exam->start_time > $now) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian belum dimulai. Waktu mulai: ' . $exam->start_time->format('d M Y H:i'),
                ], 400);
            }

            if ($exam->end_time < $now) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian telah berakhir.',
                ], 400);
            }

            // 2. REFRESH TOKEN IF NEEDED
            if ($exam->tokenNeedsRefresh()) {
                $this->regenerateExamToken($exam);
                // Reload exam from database to get fresh token value
                $exam->refresh();
            }

            // 3. VALIDATE TOKEN
            $inputToken = strtoupper(trim($request->token));
            $examToken = strtoupper($exam->token ?? '');

            if (!$examToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token ujian belum ditetapkan oleh admin. Silakan hubungi pengawas.',
                ], 400);
            }

            if ($inputToken !== $examToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token salah atau sudah kadaluwarsa. Silakan hubungi pengawas.',
                ], 400);
            }

            // 4. CREATE EXAM ATTEMPT (with status = in_progress)
            $attempt = ExamEngineService::startExam($exam, auth()->user(), $inputToken);

            if (!$attempt || !$attempt->id) {
                throw new \Exception('Gagal membuat attempt ujian. Silakan coba lagi.');
            }

            \Log::info('Exam attempt created', [
                'exam_id' => $exam->id,
                'student_id' => auth()->id(),
                'attempt_id' => $attempt->id,
                'attempt_status' => $attempt->status,
                'token' => $inputToken,
            ]);

            // 5. SET SESSION - untuk fallback authorization
            $sessionKey1 = 'authorized_exam_' . $exam->id;
            $sessionKey2 = 'exam_attempt_' . $exam->id;

            session([$sessionKey1 => true]);
            session([$sessionKey2 => $attempt->id]);

            \Log::info('Session set for exam attempt', [
                'exam_id' => $exam->id,
                'student_id' => auth()->id(),
                'attempt_id' => $attempt->id,
                'session_key_1' => $sessionKey1,
                'session_key_2' => $sessionKey2,
                'session_has_key_1' => session()->has($sessionKey1),
                'session_has_key_2' => session()->has($sessionKey2),
                'session_value_1' => session($sessionKey1),
                'session_value_2' => session($sessionKey2),
            ]);

            // 6. RETURN SUCCESS WITH REDIRECT
            return response()->json([
                'success' => true,
                'message' => 'Token valid! Ujian dimulai...',
                'attempt_id' => $attempt->id,
                'redirect_url' => route('student.exams.take', $attempt->id),
            ]);
        } catch (\Exception $e) {
            \Log::error('Token validation error for exam ' . $exam->id . ' student ' . auth()->id() . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Regenerate token for exam (internal method).
     */
    private function regenerateExamToken(Exam $exam): void
    {
        if ($exam->status !== 'published') {
            return;
        }

        // Generate random 6-character alphanumeric token
        $token = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        $exam->update([
            'token' => $token,
            'token_last_updated' => now(),
        ]);
    }
}
