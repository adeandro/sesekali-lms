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
     * 3. Or student owns the exam attempt
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
            
            // Additional check: verify student owns this attempt
            if ($attempt->student_id !== auth()->id()) {
                return redirect()->route('student.exams.index')
                    ->with('error', 'Anda tidak memiliki akses ke ujian ini.');
            }
        }
        
        // Fallback to exam route parameter if available
        if (!$examId && $request->route('exam')) {
            $examId = $request->route('exam')->id;
        }

        if (!$examId) {
            return redirect()->route('student.exams.index')
                ->with('error', 'Ujian tidak ditemukan.');
        }

        // Verify exam still exists and is published
        $exam = Exam::find($examId);
        if (!$exam || $exam->status !== 'published') {
            return redirect()->route('student.exams.index')
                ->with('error', 'Ujian tidak tersedia atau telah ditutup.');
        }

        // Check if student has authorization session for this exam
        // OR if they have an active exam attempt
        $hasAuthorization = session('authorized_exam_' . $examId);
        $hasActiveAttempt = $attempt instanceof ExamAttempt && 
                           $attempt->student_id === auth()->id() && 
                           in_array($attempt->status, ['active', 'in_progress', 'submitted']);

        if ($hasAuthorization || $hasActiveAttempt) {
            return $next($request);
        }

        // Session not found or invalid - redirect to token validation
        return redirect()->route('student.exams.start', ['exam' => $examId])
            ->with('error', 'Sesi ujian tidak valid. Silakan validasi token terlebih dahulu.');
    }
}
