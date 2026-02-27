<?php

namespace App\Http\Middleware;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyExamSession
{
    /**
     * Handle an incoming request.
     * Verify that:
     * 1. Exam is still published
     * 2. Student has already validated token for this exam (session exists)
     * 3. Or student owns the exam attempt with 'in_progress' status
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get exam ID and attempt from route parameters
        $attemptParam = $request->route('attempt');
        $attempt = null;
        $examId = null;

        // CRITICAL: Model binding hasn't happened yet in middleware
        // If $attemptParam is an ID (string/int), resolve the model manually
        if ($attemptParam && !($attemptParam instanceof ExamAttempt)) {
            // $attemptParam is raw ID, resolve the model
            try {
                $attempt = ExamAttempt::findOrFail($attemptParam);
            } catch (\Exception $e) {
                \Log::warning('Exam attempt not found: ' . $attemptParam . ' for student ' . auth()->id());
                return redirect()->route('student.exams.index')
                    ->with('error', 'Ujian tidak ditemukan.');
            }
        } elseif ($attemptParam instanceof ExamAttempt) {
            // Already resolved (shouldn't happen, but handle it)
            $attempt = $attemptParam;
        }

        // If we have an attempt, get exam_id from it
        if ($attempt) {
            $examId = $attempt->exam_id;

            // First check: verify student owns this attempt
            if ((int)$attempt->student_id !== (int)auth()->id()) {
                \Log::warning('Access denied: Student ' . auth()->id() . ' attempting to access attempt ' . $attempt->id . ' (attempt owns student ' . $attempt->student_id . ')', [
                    'request_student_id' => auth()->id(),
                    'attempt_student_id' => $attempt->student_id,
                    'attempt_id' => $attempt->id,
                    'exam_id' => $examId,
                ]);
                return redirect()->route('student.exams.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini.');
            }
        }

        // Fallback to exam route parameter if available
        if (!$examId && $request->route('exam')) {
            $examId = $request->route('exam')->id;
        }

        if (!$examId) {
            \Log::warning('Access denied: Exam ID not found for student ' . auth()->id());
            return redirect()->route('student.exams.index')
                ->with('error', 'Ujian tidak ditemukan.');
        }

        // Verify exam still exists and is published
        $exam = Exam::find($examId);
        if (!$exam || $exam->status !== 'published') {
            \Log::warning('Access denied: Exam ' . $examId . ' not published or not found for student ' . auth()->id());
            return redirect()->route('student.exams.index')
                ->with('error', 'Ujian tidak tersedia atau telah ditutup.');
        }

        // Check MULTIPLE LAYERS of authorization for robustness:

        // Layer 1: Check if student has authorization session for this exam
        $hasSessionAuth = session()->has('authorized_exam_' . $examId);

        // Layer 2: Check if attempt exists and is in_progress/submitted
        $hasValidAttempt = false;
        if ($attempt) {
            $hasValidAttempt = in_array($attempt->status, ['in_progress', 'submitted']);

            if (!$hasValidAttempt) {
                \Log::warning('Access denied: Attempt ' . $attempt->id . ' has invalid status "' . ($attempt->status ?? 'NULL') . '"', [
                    'student_id' => auth()->id(),
                    'attempt_id' => $attempt->id,
                    'status' => $attempt->status,
                    'attempt_student_id' => $attempt->student_id,
                    'auth_student_id' => auth()->id(),
                ]);
            }
        }

        // Layer 3: Fallback check - verify attempt exists in DB
        $dbAttempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', (int)auth()->id())  // Explicit cast
            ->whereIn('status', ['in_progress', 'submitted'])
            ->latest('id')
            ->first();

        $hasDbFallback = $dbAttempt !== null;

        // APPROVED if ANY layer succeeds
        if ($hasSessionAuth || $hasValidAttempt || $hasDbFallback) {
            return $next($request);
        }

        // All layers failed - redirect to token validation
        \Log::warning('Access denied: No valid authorization found for student ' . auth()->id() . ' exam ' . $examId . ' (session: ' . ($hasSessionAuth ? 'yes' : 'no') . ', attempt: ' . ($hasValidAttempt ? 'yes' : 'no') . ', db: ' . ($hasDbFallback ? 'yes' : 'no') . ')');

        return redirect()->route('student.exams.start', ['exam' => $examId])
            ->with('error', 'Sesi ujian tidak valid. Silakan validasi token terlebih dahulu.');
    }
}
