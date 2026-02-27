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
     * PRODUCTION HARDENED: Handles type mismatches and session sync issues
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = auth()->id();
        $attemptParam = $request->route('attempt');
        $attempt = null;
        $examId = null;

        // Log dengan type information untuk debugging type mismatch
        \Log::info('VerifyExamSession: Starting check', [
            'student_id' => $studentId,
            'student_id_type' => gettype($studentId),
            'attempt_param' => $attemptParam,
            'attempt_param_type' => gettype($attemptParam),
            'route_name' => $request->route()->getName(),
        ]);

        // CRITICAL: Model binding hasn't happened yet in middleware
        // Resolve model manually if we have raw ID
        if ($attemptParam && !($attemptParam instanceof ExamAttempt)) {
            try {
                $attempt = ExamAttempt::findOrFail($attemptParam);
                \Log::info('VerifyExamSession: Attempt model loaded', [
                    'attempt_id' => $attempt->id,
                    'attempt_student_id' => $attempt->student_id,
                    'attempt_student_id_type' => gettype($attempt->student_id),
                    'attempt_status' => $attempt->status,
                    'exam_id' => $attempt->exam_id,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Exam attempt not found', [
                    'attempt_param' => $attemptParam,
                    'student_id' => $studentId,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('student.exams.index')
                    ->with('error', 'Ujian tidak ditemukan.');
            }
        } elseif ($attemptParam instanceof ExamAttempt) {
            $attempt = $attemptParam;
            \Log::info('VerifyExamSession: Attempt already resolved', [
                'attempt_id' => $attempt->id,
                'attempt_student_id' => $attempt->student_id,
            ]);
        }

        // Get exam_id from attempt
        if ($attempt) {
            $examId = $attempt->exam_id;

            // OWNERSHIP CHECK: Use LOOSE comparison (!=) instead of strict (!==)
            // Cast both to string to handle Integer vs String type differences in PDO
            // This handles the case where DB returns integer but auth()->id() returns string (or vice versa)
            $attemptStudentIdStr = (string)$attempt->student_id;
            $requestStudentIdStr = (string)$studentId;

            \Log::info('VerifyExamSession: Ownership check details', [
                'attempt_student_id_raw' => $attempt->student_id,
                'attempt_student_id_type' => gettype($attempt->student_id),
                'request_student_id_raw' => $studentId,
                'request_student_id_type' => gettype($studentId),
                'attempt_student_id_str' => $attemptStudentIdStr,
                'request_student_id_str' => $requestStudentIdStr,
                'strings_equal' => ($attemptStudentIdStr === $requestStudentIdStr),
            ]);

            // Use loose comparison (!= instead of !==) with string casting
            // This handles type juggling in production environments
            if ((string)$attempt->student_id != (string)$studentId) {
                \Log::warning('Access denied: Student ownership mismatch', [
                    'request_student_id' => $studentId,
                    'request_student_id_type' => gettype($studentId),
                    'attempt_student_id' => $attempt->student_id,
                    'attempt_student_id_type' => gettype($attempt->student_id),
                    'attempt_id' => $attempt->id,
                    'exam_id' => $examId,
                ]);
                return redirect()->route('student.exams.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini.');
            }

            \Log::info('VerifyExamSession: Ownership verified');
        }

        // Fallback to exam route parameter if available
        if (!$examId && $request->route('exam')) {
            $examId = $request->route('exam')->id;
        }

        if (!$examId) {
            \Log::warning('Access denied: Exam ID not found', ['student_id' => $studentId]);
            return redirect()->route('student.exams.index')
                ->with('error', 'Ujian tidak ditemukan.');
        }

        // Verify exam still exists and is published
        $exam = Exam::find($examId);
        if (!$exam || $exam->status !== 'published') {
            \Log::warning('Access denied: Exam not available', [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'exam_exists' => $exam !== null,
                'exam_status' => $exam?->status,
            ]);
            return redirect()->route('student.exams.index')
                ->with('error', 'Ujian tidak tersedia atau telah ditutup.');
        }

        // Check MULTIPLE LAYERS of authorization

        // Layer 1: Check if student has authorization session for this exam
        $sessionKey = 'authorized_exam_' . $examId;
        $hasSessionAuth = session()->has($sessionKey);

        \Log::info('VerifyExamSession: Layer 1 - Session check', [
            'exam_id' => $examId,
            'session_key' => $sessionKey,
            'has_session_auth' => $hasSessionAuth,
            'session_keys_present' => array_keys(session()->all()),
        ]);

        // Layer 2: Check if attempt exists and is in_progress/submitted
        $hasValidAttempt = false;
        if ($attempt) {
            $hasValidAttempt = in_array($attempt->status, ['in_progress', 'submitted']);

            \Log::info('VerifyExamSession: Layer 2 - Attempt status check', [
                'attempt_id' => $attempt->id,
                'attempt_status' => $attempt->status,
                'has_valid_status' => $hasValidAttempt,
                'valid_statuses' => ['in_progress', 'submitted'],
            ]);
        }

        // Layer 3: Fallback check - verify attempt exists in DB
        // IMPORTANT: Don't force (int) casting on student_id parameter
        // Let Eloquent handle type binding flexibly via PDO parameter binding
        $dbAttempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $studentId)  // Let Eloquent handle type binding naturally
            ->whereIn('status', ['in_progress', 'submitted'])
            ->latest('id')
            ->first();

        $hasDbFallback = $dbAttempt !== null;

        \Log::info('VerifyExamSession: Layer 3 - Database fallback check', [
            'exam_id' => $examId,
            'student_id' => $studentId,
            'student_id_type' => gettype($studentId),
            'has_db_fallback' => $hasDbFallback,
            'db_attempt_id' => $dbAttempt?->id,
            'db_attempt_status' => $dbAttempt?->status,
            'db_attempt_student_id' => $dbAttempt?->student_id,
            'db_attempt_student_id_type' => $dbAttempt ? gettype($dbAttempt->student_id) : 'null',
        ]);

        // Final decision log
        \Log::info('VerifyExamSession: Authorization decision', [
            'exam_id' => $examId,
            'student_id' => $studentId,
            'session_auth' => $hasSessionAuth,
            'attempt_valid' => $hasValidAttempt,
            'db_fallback' => $hasDbFallback,
            'approved' => ($hasSessionAuth || $hasValidAttempt || $hasDbFallback),
        ]);

        // APPROVED if ANY layer succeeds
        if ($hasSessionAuth || $hasValidAttempt || $hasDbFallback) {
            \Log::info('VerifyExamSession: APPROVED - Proceeding to exam', [
                'exam_id' => $examId,
                'student_id' => $studentId,
            ]);
            return $next($request);
        }

        // All layers failed - redirect to token validation
        \Log::warning('Access denied: No valid authorization found', [
            'student_id' => $studentId,
            'exam_id' => $examId,
            'session_auth' => $hasSessionAuth,
            'attempt_valid' => $hasValidAttempt,
            'db_fallback' => $hasDbFallback,
            'recommendation' => 'Student must re-validate token via start route',
        ]);

        return redirect()->route('student.exams.start', ['exam' => $examId])
            ->with('error', 'Sesi ujian tidak valid. Silakan validasi token terlebih dahulu.');
    }
}
