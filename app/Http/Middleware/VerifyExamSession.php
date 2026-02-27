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
        $attempt = $request->route('attempt');
        $examId = null;
        
        // If we have an attempt parameter (ExamAttempt model), get exam_id from it
        if ($attempt instanceof ExamAttempt) {
            $examId = $attempt->exam_id;
            
            // First check: verify student owns this attempt
            if ($attempt->student_id !== auth()->id()) {
                \Log::warning('Access denied: Student ' . auth()->id() . ' attempting to access attempt ' . $attempt->id . ' (owns student ' . $attempt->student_id . ')');
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
        if ($attempt instanceof ExamAttempt) {
            $hasValidAttempt = in_array($attempt->status, ['in_progress', 'submitted']);
            
            if (!$hasValidAttempt) {
                \Log::warning('Access denied: Attempt ' . $attempt->id . ' has invalid status "' . ($attempt->status ?? 'NULL') . '"');
            }
        }
        
        // Layer 3: Fallback check - verify attempt exists in DB
        $dbAttempt = ExamAttempt::where('exam_id', $examId)
            ->where('student_id', auth()->id())
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
