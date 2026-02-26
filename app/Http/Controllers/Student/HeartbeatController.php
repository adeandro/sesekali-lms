<?php

namespace App\Http\Controllers\Student;

use App\Models\ExamSession;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HeartbeatController extends Controller
{
    /**
     * Record heartbeat from student during exam.
     * Sent every 20 seconds with current progress.
     */
    public function recordHeartbeat(Request $request, $attemptId)
    {
        $request->validate([
            'current_question' => 'required|integer|min:0',
            'violation_count' => 'required|integer|min:0',
            'session_id' => 'required|string',
        ]);

        // Get the exam attempt
        $attempt = ExamAttempt::find($attemptId);
        if (!$attempt || $attempt->student_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Get or create exam session
        $session = ExamSession::where('exam_attempt_id', $attemptId)
            ->where('student_id', auth()->id())
            ->first();

        if (!$session) {
            // Create session if it doesn't exist
            $session = ExamSession::create([
                'exam_id' => $attempt->exam_id,
                'exam_attempt_id' => $attemptId,
                'student_id' => auth()->id(),
                'session_id' => $request->session_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'started_at' => $attempt->started_at,
                'last_heartbeat' => now(),
                'current_question' => $request->current_question,
                'violation_count' => $request->violation_count,
                'status' => 'active',
            ]);
        } else {
            // Update heartbeat
            $session->recordHeartbeat(
                $request->current_question,
                $request->violation_count
            );
        }

        // Update exam attempt heartbeat tracking
        $attempt->update([
            'heartbeat_last_seen' => now(),
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $session->session_id,
            'progress' => $session->getProgressPercentage(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get session details for monitoring.
     */
    public function getSessionStatus(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::find($attemptId);
        if (!$attempt || $attempt->student_id !== auth()->id()) {
            return response()->json(['success' => false], 403);
        }

        $session = ExamSession::where('exam_attempt_id', $attemptId)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'session_id' => $session->session_id,
                'status' => $session->status,
                'is_connected' => $session->isConnected(),
                'current_question' => $session->current_question,
                'progress' => $session->getProgressPercentage(),
                'violation_count' => $session->violation_count,
                'last_heartbeat' => $session->last_heartbeat->toIso8601String(),
            ]
        ]);
    }

    /**
     * Offline cache sync - submit cached answers.
     * Called when connection is restored.
     */
    public function syncOfflineAnswers(Request $request, $attemptId)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $attempt = ExamAttempt::find($attemptId);
        if (!$attempt || $attempt->student_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Process offline answers
        foreach ($request->answers as $questionId => $answer) {
            // Update or create answer record
            $attempt->answers()->updateOrCreate(
                ['question_id' => $questionId],
                [
                    'selected_answer' => $answer['selected_answer'] ?? null,
                    'essay_answer' => $answer['essay_answer'] ?? null,
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Jawaban offline berhasil disinkronkan',
            'synced_count' => count($request->answers),
        ]);
    }

    /**
     * Disconnect session (student closes exam page).
     */
    public function disconnectSession($attemptId)
    {
        $attempt = ExamAttempt::find($attemptId);
        if (!$attempt || $attempt->student_id !== auth()->id()) {
            return response()->json(['success' => false], 403);
        }

        $session = ExamSession::where('exam_attempt_id', $attemptId)->first();
        if ($session) {
            $session->end();
        }

        return response()->json(['success' => true]);
    }
}
