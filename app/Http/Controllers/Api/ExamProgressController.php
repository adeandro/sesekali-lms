<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamProgressController extends Controller
{
    /**
     * Save answer and update progress in real-time
     */
    public function recordAnswer(Request $request, $attemptId)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'nullable|string',
        ]);

        try {
            // Get the exam attempt and verify ownership
            $attempt = ExamAttempt::find($attemptId);
            if (!$attempt || $attempt->student_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Get exam session
            $session = ExamSession::where('exam_attempt_id', $attemptId)
                ->where('student_id', auth()->id())
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            // Save or update the answer
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attemptId,
                    'question_id' => $request->question_id,
                ],
                [
                    'selected_answer' => $request->answer,
                    'answered_at' => now(),
                ]
            );

            // Count total unique answered questions
            $totalAnswered = ExamAnswer::where('exam_attempt_id', $attemptId)
                ->where('selected_answer', '!=', null)
                ->distinct('question_id')
                ->count('question_id');

            // Update session with total answered
            $session->update([
                'total_answered' => $totalAnswered,
                'last_heartbeat' => now(),
            ]);

            // Log to console for debugging
            error_log("✓ Answer recorded: Attempt {$attemptId}, Question {$request->question_id}, Total Answered: {$totalAnswered}");

            return response()->json([
                'success' => true,
                'message' => 'Answer saved',
                'total_answered' => $totalAnswered,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            error_log("✗ Error recording answer: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving answer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report a violation (tab switch, etc)
     */
    public function reportViolation(Request $request, $attemptId)
    {
        $request->validate([
            'violation_type' => 'required|string|in:tab_switch,window_blur,fullscreen_exit',
            'details' => 'nullable|string',
        ]);

        try {
            // Get the exam attempt
            $attempt = ExamAttempt::find($attemptId);
            if (!$attempt || $attempt->student_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Get exam session
            $session = ExamSession::where('exam_attempt_id', $attemptId)
                ->where('student_id', auth()->id())
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            // Increment violation count
            $newViolationCount = $session->violation_count + 1;
            $session->update([
                'violation_count' => $newViolationCount,
                'last_heartbeat' => now(),
            ]);

            // Log the violation for records
            \App\Models\ActionLog::logAction(
                auth()->id(),
                'violation_detected',
                "Pelanggaran: {$request->violation_type} - {$request->details}",
                $attempt->exam_id,
                auth()->id(),
                [
                    'violation_type' => $request->violation_type,
                    'details' => $request->details,
                    'session_id' => $session->session_id,
                ]
            );

            error_log("⚠️ Violation recorded: {$request->violation_type} - Attempt {$attemptId}, Total Violations: {$newViolationCount}");

            return response()->json([
                'success' => true,
                'message' => 'Violation recorded',
                'violation_count' => $newViolationCount,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            error_log("✗ Error reporting violation: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reporting violation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current session progress for monitoring
     */
    public function getSessionProgress(Request $request, $attemptId)
    {
        try {
            // Get the exam session
            $session = ExamSession::where('exam_attempt_id', $attemptId)
                ->with('student', 'exam')
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            $secondsSinceHeartbeat = $session->last_heartbeat->diffInSeconds(now());
            $isOnline = $secondsSinceHeartbeat < 80;

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $session->id,
                    'attempt_id' => $session->exam_attempt_id,
                    'student_name' => $session->student->name,
                    'total_questions' => $session->exam->total_questions,
                    'total_answered' => $session->total_answered,
                    'progress_percent' => ($session->total_answered / $session->exam->total_questions) * 100,
                    'violation_count' => $session->violation_count,
                    'seconds_since_heartbeat' => $secondsSinceHeartbeat,
                    'is_online' => $isOnline,
                    'status' => $isOnline ? 'active' : 'disconnected',
                    'last_heartbeat' => $session->last_heartbeat->toIso8601String(),
                ],
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            error_log("✗ Error getting session progress: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
