<?php

namespace App\Http\Controllers\Admin;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamAttempt;
use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MonitoringController extends Controller
{
    /**
     * Show monitoring dashboard for an exam (Simplified).
     * Only shows completed/submitted exams.
     */
    public function index(Exam $exam)
    {
        // Get total students in this grade/jenjang
        $totalStudentsInGrade = User::where('grade', $exam->jenjang)
            ->where('role', 'student')
            ->count();

        // Get only COMPLETED attempts (status = submitted) with exam sessions
        $completedAttempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'submitted')  // Only submitted/completed
            ->with(['student', 'exam', 'session'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        // Build data for display
        $completedStudents = $completedAttempts->map(function ($attempt) {
            $session = $attempt->session;
            return [
                'attempt_id' => $attempt->id,
                'student_id' => $attempt->student_id,
                'student_name' => $attempt->student->name,
                'nis' => $attempt->student->nis ?? 'N/A',
                'exam_type' => $this->getExamType($attempt),
                'score_mc' => $attempt->score_mc,
                'score_essay' => $attempt->score_essay,
                'final_score' => $attempt->final_score,
                'submitted_at' => $attempt->submitted_at,
            ];
        });

        // Calculate stats
        $totalSelesai = $completedAttempts->count();
        $belumUjian = $totalStudentsInGrade - ExamAttempt::where('exam_id', $exam->id)->distinct('student_id')->count();

        return view('admin.monitoring.index', [
            'exam' => $exam,
            'completedStudents' => $completedStudents,
            'stats' => [
                'total_siswa_jenjang' => $totalStudentsInGrade,
                'total_selesai' => $totalSelesai,
                'belum_ujian' => $belumUjian,
            ]
        ]);
    }

    /**
     * Determine exam type from attempt.
     */
    private function getExamType($attempt)
    {
        $questions = $attempt->exam->questions()->get();
        $hasMC = $questions->where('question_type', 'multiple_choice')->count() > 0;
        $hasEssay = $questions->where('question_type', 'essay')->count() > 0;

        if ($hasMC && $hasEssay) {
            return 'Pilihan Ganda & Essay';
        } elseif ($hasEssay) {
            return 'Essay';
        } else {
            return 'Pilihan Ganda';
        }
    }



    /**
     * Reopen an exam session (allow student to continue).
     */
    public function reopenSession(Request $request, ExamAttempt $attempt)
    {
        try {
            // Validate input
            $request->validate([
                'reason' => 'nullable|string|max:255',
                'time_option' => 'required|in:continue,reset,custom',
                'custom_minutes' => 'nullable|integer|min:1',
            ]);

            // Verify authorization - check if user is admin or superadmin
            if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Only reopen if status is submitted (completed)
            if ($attempt->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya ujian yang sudah diselesaikan yang dapat dibuka kembali',
                ], 422);
            }

            // Determine new started_at based on time_option
            $updateData = [
                'status' => 'in_progress',
                'submitted_at' => null,
            ];

            $timeOption = $request->input('time_option');

            if ($timeOption === 'reset') {
                // Reset: start from now (fresh start with full duration)
                $updateData['started_at'] = now();
            } elseif ($timeOption === 'custom') {
                // Custom: set remaining time in minutes
                // Calculate started_at based on how many minutes student should have left
                $customMinutes = (int)$request->input('custom_minutes');
                $examDuration = $attempt->exam->duration_minutes ?? 60; // Default 60 if not set

                // Validate custom minutes doesn't exceed exam duration
                if ($customMinutes > $examDuration) {
                    return response()->json([
                        'success' => false,
                        'message' => "Durasi waktu tidak boleh melebihi durasi ujian ($examDuration menit)",
                    ], 422);
                }

                // started_at = now - (total_duration - remaining_minutes)
                // This way student will have exactly $customMinutes left to complete
                $minutesAlreadyUsed = $examDuration - $customMinutes;
                $updateData['started_at'] = now()->subMinutes($minutesAlreadyUsed);
            }
            // 'continue' option: don't change started_at, student continues with original time

            // Update exam attempt
            $attempt->update($updateData);

            // Update or create exam session as active
            $session = ExamSession::where('exam_attempt_id', $attempt->id)->first();
            if ($session) {
                $session->update([
                    'status' => 'active',
                    'ended_at' => null,
                    'is_active' => true,
                ]);
            }

            // Log the action
            try {
                $logData = [
                    'reason' => $request->input('reason', 'Dibuka kembali oleh admin'),
                    'time_option' => $timeOption,
                ];

                if ($timeOption === 'custom') {
                    $logData['custom_minutes'] = $request->input('custom_minutes');
                }

                ActionLog::logAction(
                    auth()->id(),
                    'session_reopened',
                    "Ujian dibuka kembali untuk siswa (opsi: $timeOption)",
                    $attempt->exam_id,
                    $attempt->student_id,
                    $logData
                );
            } catch (\Exception $logError) {
                // Logging error won't fail the reopen
                \Log::warning('Failed to log action: ' . $logError->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Akses ujian berhasil dibuka',
            ]);
        } catch (\Exception $e) {
            \Log::error('Reopen session error', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display list of exams available for monitoring.
     */
    public function listExams(Request $request)
    {
        $search = $request->input('search');
        $subjectId = $request->input('subject');

        $exams = Exam::where('status', 'published')
            ->when($search, function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($subjectId, function ($query) use ($subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->with(['subject', 'attempts', 'sessions'])
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        $subjects = \App\Models\Subject::orderBy('name')->get();

        $activeExamsCount = Exam::where('status', 'published')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->count();

        $upcomingExamsCount = Exam::where('status', 'published')
            ->where('start_time', '>', now())
            ->count();

        $finishedExamsCount = Exam::where('status', 'published')
            ->where('end_time', '<', now())
            ->count();

        return view('admin.monitoring.exams', [
            'exams' => $exams,
            'subjects' => $subjects,
            'activeExamsCount' => $activeExamsCount,
            'upcomingExamsCount' => $upcomingExamsCount,
            'finishedExamsCount' => $finishedExamsCount,
        ]);
    }
}
