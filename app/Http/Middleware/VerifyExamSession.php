<?php

namespace App\Http\Middleware;

use App\Models\Exam;
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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get exam ID from route parameter
        $examId = $request->route('attempt') ?
            $request->route('attempt')->exam_id :
            $request->route('exam')?->id;

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
        if (session('authorized_exam_' . $examId)) {
            return $next($request);
        }

        // Session not found or invalid - redirect to token validation
        return redirect()->route('student.exams.start', ['exam' => $examId])
            ->with('error', 'Sesi ujian tidak valid. Silakan validasi token terlebih dahulu.');
    }
}
