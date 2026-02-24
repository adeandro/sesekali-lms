<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExamEngineService
{
    /**
     * Get available exams for a student.
     */
    public static function getAvailableExams(User $student)
    {
        $now = now();

        return Exam::where('status', 'published')
            ->where('end_time', '>=', $now)  // Show exams that haven't ended yet (includes upcoming exams)
            ->where('jenjang', $student->grade)  // Filter by student's grade level
            ->whereNotIn('id', function ($query) use ($student) {
                $query->select('exam_id')
                    ->from('exam_attempts')
                    ->where('student_id', $student->id)
                    ->where('status', 'submitted');
            })
            ->with('subject')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Start an exam for a student.
     */
    public static function startExam(Exam $exam, User $student)
    {
        // Check if already has active attempt
        $activeAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if ($activeAttempt) {
            return $activeAttempt;
        }

        // Check if already submitted
        $submitted = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->first();

        if ($submitted) {
            throw new \Exception('You have already submitted this exam.');
        }

        // Create new attempt
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
        ]);

        // Initialize exam answers
        $questions = $exam->questions()->get();
        foreach ($questions as $question) {
            ExamAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ]);
        }

        return $attempt;
    }

    /**
     * Get attempt with questions.
     */
    public static function getAttemptWithQuestions(ExamAttempt $attempt)
    {
        return $attempt->load([
            'exam.subject',
            'answers.question',
        ]);
    }

    /**
     * Get exam questions organized by type (MC first, then Essay).
     * Returns collections with proper sequential numbering for navigator.
     */
    public static function getExamQuestions(ExamAttempt $attempt)
    {
        $exam = $attempt->exam;
        $allQuestions = $exam->questions()->get();

        // Separate questions by type
        $mcQuestions = $allQuestions->where('question_type', 'multiple_choice')->values();
        $essayQuestions = $allQuestions->where('question_type', 'essay')->values();

        // Apply randomization if enabled
        if ($exam->randomize_questions) {
            // Get or create randomization order for MC questions
            $mcOrder = session()->get("exam_{$attempt->id}_mc_order");
            if (!$mcOrder) {
                $mcOrder = $mcQuestions->pluck('id')->shuffle()->toArray();
                session()->put("exam_{$attempt->id}_mc_order", $mcOrder);
            }
            // Reorder MC questions
            $mcQuestions = $mcQuestions->sortBy(function ($q) use ($mcOrder) {
                return array_search($q->id, $mcOrder);
            })->values();

            // Get or create randomization order for Essay questions
            $essayOrder = session()->get("exam_{$attempt->id}_essay_order");
            if (!$essayOrder) {
                $essayOrder = $essayQuestions->pluck('id')->shuffle()->toArray();
                session()->put("exam_{$attempt->id}_essay_order", $essayOrder);
            }
            // Reorder Essay questions
            $essayQuestions = $essayQuestions->sortBy(function ($q) use ($essayOrder) {
                return array_search($q->id, $essayOrder);
            })->values();
        }

        // Combine: MC first, then Essay
        $questions = $mcQuestions->concat($essayQuestions)->values();

        // Add position metadata to each question for navigator
        $mcCount = $mcQuestions->count();
        foreach ($questions as $index => $question) {
            if ($index < $mcCount) {
                // MC question
                $question->nav_position = $index + 1;
                $question->nav_type = 'mc';
            } else {
                // Essay question
                $question->nav_position = $index - $mcCount + 1;
                $question->nav_type = 'essay';
            }
            $question->display_index = $index;
        }

        return $questions;
    }

    /**
     * Autosave answer.
     */
    public static function autosaveAnswer(ExamAttempt $attempt, int $questionId, ?string $selectedAnswer = null, ?string $essayAnswer = null)
    {
        $answer = ExamAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $questionId)
            ->first();

        if (!$answer) {
            throw new \Exception('Answer record not found.');
        }

        // Update answer
        if ($selectedAnswer !== null) {
            $answer->selected_answer = $selectedAnswer;
        }

        if ($essayAnswer !== null) {
            $answer->essay_answer = $essayAnswer;
        }

        $answer->save();

        return $answer;
    }

    /**
     * Submit exam and calculate score using dynamic weighting.
     * 
     * Process:
     * 1. Score MC questions automatically
     * 2. Prepare essay questions for teacher grading (if any)
     * 3. Calculate final score with dynamic weights
     */
    public static function submitExam(ExamAttempt $attempt)
    {
        if (!$attempt->isInProgress()) {
            throw new \Exception('This exam has already been submitted.');
        }

        return DB::transaction(function () use ($attempt) {
            $attempt = $attempt->refresh();

            // Get all answers
            $answers = ExamAnswer::where('attempt_id', $attempt->id)
                ->with('question')
                ->get();

            // Score MC questions and prepare essays
            foreach ($answers as $answer) {
                if ($answer->question->question_type === 'multiple_choice') {
                    // Auto-score MC questions with case-insensitive comparison
                    if (strtolower($answer->question->correct_answer) === strtolower($answer->selected_answer)) {
                        $answer->is_correct = true;
                    } else {
                        $answer->is_correct = false;
                    }
                } else {
                    // Essay questions: marked as null initially (pending manual grading)
                    $answer->is_correct = null;
                }
                $answer->save();
            }

            // Calculate scores using ScoringService
            $score_mc = ScoringService::calculateMCScore($attempt);

            // Get weights to understand the exam structure
            $weights = ScoringService::getExamWeights($attempt->exam);

            // If exams has essays, final score is null until teacher grades
            // If only MC, final score can be calculated immediately
            $final_score = null;
            if ($weights['has_essay']) {
                // Has essays: final score needs teacher input
                $final_score = null;
            } else {
                // Only MC: can calculate final score now
                $final_score = $score_mc;
            }

            // Update attempt with scores
            $attempt->update([
                'score_mc' => $score_mc,
                'score_essay' => 0,
                'final_score' => $final_score,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);

            return $attempt;
        });
    }

    /**
     * Check if student can access attempt.
     */
    public static function canAccessAttempt(ExamAttempt $attempt, User $student): bool
    {
        return $attempt->student_id === $student->id;
    }

    /**
     * Get student's exam result.
     */
    public static function getExamResult(ExamAttempt $attempt)
    {
        if (!$attempt->isSubmitted()) {
            throw new \Exception('Exam has not been submitted yet.');
        }

        return $attempt->load([
            'exam.subject',
            'answers.question',
        ]);
    }

    /**
     * Auto-submit if time expired.
     */
    public static function autoSubmitIfExpired(ExamAttempt $attempt)
    {
        if ($attempt->hasTimeExpired() && $attempt->isInProgress()) {
            return self::submitExam($attempt);
        }

        return $attempt;
    }
}
