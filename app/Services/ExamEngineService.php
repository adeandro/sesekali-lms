<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExamEngineService
{
    /**
     * Get available exams for a student.
     * Shows:
     * - All published exams within time window
     * - Excludes only exams where student has active/in-progress attempt
     * - Includes exams with submitted attempts (for admin reopen case)
     */
    public static function getAvailableExams(User $student)
    {
        $now = now();

        return Exam::where('status', 'published')
            ->where('jenjang', $student->grade)  // Filter by student's grade level
            ->where(function ($query) use ($student, $now) {
                // Show exams that haven't ended yet
                $query->where('end_time', '>=', $now)
                    // OR show exams with in_progress attempts (reopened exams)
                    ->orWhereIn('id', function ($subquery) use ($student) {
                        $subquery->select('exam_id')
                            ->from('exam_attempts')
                            ->where('student_id', $student->id)
                            ->where('status', 'in_progress');
                    });
            })
            ->whereNotIn('id', function ($query) use ($student) {
                // Exclude exams with submitted attempts (fully completed)
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
    public static function startExam(Exam $exam, User $student, $token = null)
    {
        // Check if already has an attempt
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttempt) {
            // If already submitted and NOT locked, stay submitted
            if ($existingAttempt->isSubmitted() && !$existingAttempt->is_session_locked) {
                throw new \Exception('You have already submitted this exam.');
            }

            // If it's locked (reset case) or already in progress, just reactivate/return it
            $existingAttempt->update([
                'status' => 'in_progress',
                'started_at' => $existingAttempt->started_at ?? now(),
                'is_session_locked' => false,
                'token' => $token ?? $existingAttempt->token,
            ]);

            // Ensure answers exist (in case they were deleted during reset)
            self::initializeAnswers($existingAttempt, $exam);

            return $existingAttempt;
        }

        // Create new attempt
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'token' => $token,
        ]);

        // Initialize exam answers
        self::initializeAnswers($attempt, $exam);

        return $attempt;
    }

    /**
     * Initialize or restore answers for an attempt.
     */
    private static function initializeAnswers(ExamAttempt $attempt, Exam $exam)
    {
        $questions = $exam->questions()->get();
        foreach ($questions as $question) {
            // Only create if doesn't exist
            $exists = ExamAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $question->id)
                ->exists();
            
            if (!$exists) {
                $creationData = [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ];
                
                if ($question->question_type === 'multiple_choice') {
                    $correctAnswerPosition = strtolower($question->correct_answer);
                    $correctAnswerText = $question->{"option_" . $correctAnswerPosition} ?? null;
                    $creationData['correct_answer_text'] = $correctAnswerText;
                }
                
                ExamAnswer::create($creationData);
            }
        }
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
     * 
     * Handles both randomize_questions and randomize_options.
     */
    public static function getExamQuestions(ExamAttempt $attempt)
    {
        $exam = $attempt->exam;
        $allQuestions = $exam->questions()->get();

        // Separate questions by type
        $mcQuestions = $allQuestions->where('question_type', 'multiple_choice')->values();
        $essayQuestions = $allQuestions->where('question_type', 'essay')->values();

        // Apply question randomization if enabled
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

        // Apply option randomization if enabled (only for MC questions)
        if ($exam->randomize_options) {
            foreach ($questions as $question) {
                if ($question->question_type === 'multiple_choice') {
                    // Get or create option randomization mapping for this question
                    $optionMapKey = "exam_{$attempt->id}_question_{$question->id}_option_map";
                    $optionMap = session()->get($optionMapKey);

                    if (!$optionMap) {
                        // First time viewing this question: create and shuffle option mapping
                        // Collect available options (a, b, c, d, e that are not null)
                        $optionLetters = [];
                        foreach (['a', 'b', 'c', 'd', 'e'] as $letter) {
                            if ($question->{"option_$letter"}) {
                                $optionLetters[] = $letter;
                            }
                        }

                        // Shuffle the available options
                        $shuffledLetters = collect($optionLetters)->shuffle()->toArray();

                        // Create mapping: display_position -> original_letter
                        // E.g., if original is [a, b, c, d] and shuffled is [c, a, d, b]
                        // then map should be: ['a' => 'c', 'b' => 'a', 'c' => 'd', 'd' => 'b']
                        // Meaning: "at display position A, show the option that was originally at position C"
                        $optionMap = [];
                        for ($i = 0; $i < count($optionLetters); $i++) {
                            $displayPosition = $optionLetters[$i];
                            $originalPosition = $shuffledLetters[$i];
                            $optionMap[$displayPosition] = $originalPosition;
                        }

                        // IMPORTANT: Also store reverse mapping for scoring later
                        // reverse_map: original_position -> display_position
                        $reverseMap = [];
                        foreach ($optionMap as $displayPos => $originalPos) {
                            $reverseMap[$originalPos] = $displayPos;
                        }

                        session()->put($optionMapKey, $optionMap);
                        session()->put("exam_{$attempt->id}_question_{$question->id}_reverse_map", $reverseMap);
                    }

                    // Apply the mapping: rearrange options
                    $originalOptions = [
                        'a' => $question->option_a,
                        'b' => $question->option_b,
                        'c' => $question->option_c,
                        'd' => $question->option_d,
                        'e' => $question->option_e,
                    ];

                    $originalImages = [
                        'a' => $question->option_a_image ?? null,
                        'b' => $question->option_b_image ?? null,
                        'c' => $question->option_c_image ?? null,
                        'd' => $question->option_d_image ?? null,
                        'e' => $question->option_e_image ?? null,
                    ];

                    // Rearrange options using the mapping
                    foreach (['a', 'b', 'c', 'd', 'e'] as $displayLetter) {
                        if (isset($optionMap[$displayLetter])) {
                            $originalLetter = $optionMap[$displayLetter];
                            $question->{"option_$displayLetter"} = $originalOptions[$originalLetter];
                            $question->{"option_{$displayLetter}_image"} = $originalImages[$originalLetter];
                        }
                    }

                    // Update the correct_answer to reflect the new position
                    $originalCorrectAnswer = $question->correct_answer;
                    // Find which display position the correct answer is now at
                    foreach ($optionMap as $displayLetter => $originalLetter) {
                        if ($originalLetter === $originalCorrectAnswer) {
                            $question->correct_answer = $displayLetter;
                            $question->original_correct_answer = $originalCorrectAnswer; // Store original for reference
                            break;
                        }
                    }

                    // Store the mapping on the question object for later reference
                    $question->option_map = $optionMap;
                }
            }
        }

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
     * 
     * CRITICAL: Must apply same shuffle transformation to question as was applied in getExamQuestions()
     * Otherwise shuffled answer positions won't match unshuffled DB question structure!
     * 
     * Stores both the selected answer position AND the text that was displayed.
     * Critical for handling shuffled options correctly.
     */
    public static function autosaveAnswer(ExamAttempt $attempt, int $questionId, ?string $selectedAnswer = null, ?string $essayAnswer = null, $question = null)
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
            
            // Store the actual TEXT that's currently displayed (handles shuffled options)
            if ($question === null) {
                $question = \App\Models\Question::find($questionId);
            }
            
            if ($question && $question->question_type === 'multiple_choice') {
                // CRITICAL: Always store the selected answer TEXT
                // This is what the student actually saw and clicked on
                $attempt = $attempt->refresh(); // Ensure fresh instance
                $exam = $attempt->exam;
                
                $selectedText = null;
                
                if ($exam && $exam->randomize_options) {
                    // Get the shuffle mapping from session
                    $optionMapKey = "exam_{$attempt->id}_question_{$question->id}_option_map";
                    $optionMap = session()->get($optionMapKey);
                    
                    if ($optionMap) {
                        // optionMap[display_pos] = original_pos
                        // Student selected a display position, convert to original position
                        $originalPosition = $optionMap[$selectedAnswer] ?? null;
                        if ($originalPosition) {
                            // Get text of the ORIGINAL position
                            $selectedText = $question->{"option_" . $originalPosition} ?? null;
                        }
                    } else {
                        // Fallback if map not found (shouldn't happen)
                        $selectedText = $question->{"option_" . $selectedAnswer} ?? null;
                    }
                } else {
                    // No shuffling: direct text lookup
                    $selectedText = $question->{"option_" . $selectedAnswer} ?? null;
                }
                
                // Store it - this is the TEXT the student saw
                $answer->selected_answer_text = $selectedText;
                
                \Log::debug('Autosaving MC answer', [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_display_position' => $selectedAnswer,
                    'selected_original_position' => $optionMap[$selectedAnswer] ?? null,
                    'selected_text_raw' => $selectedText,
                    'selected_text_normalized' => ScoringService::normalizeAnswerText($selectedText),
                    'has_shuffle' => $exam?->randomize_options,
                ]);
            }
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
                    // Auto-score MC questions by comparing ACTUAL TEXT
                    // Multiple fallbacks to ensure we always get text for comparison
                    
                    // Fallback 1: Use stored text (captured at time of answer)
                    $studentSelectedAnswerText = $answer->selected_answer_text;
                    
                    // Fallback 2: If not stored, look up from current question
                    if (!$studentSelectedAnswerText && $answer->selected_answer) {
                        // selected_answer is lowercase (a, b, c, d, e)
                        $studentSelectedAnswerText = $answer->question->{"option_" . strtolower($answer->selected_answer)} ?? null;
                    }
                    
                    // Get the correct answer text - prefer stored, fallback to current
                    $correctAnswerText = $answer->correct_answer_text;
                    if (!$correctAnswerText) {
                        // correct_answer might be uppercase (A, B, C, D, E) - convert to lowercase
                        $correctAnswerPosition = strtolower($answer->question->correct_answer);
                        $correctAnswerText = $answer->question->{"option_" . $correctAnswerPosition} ?? null;
                        // Store it for history/display
                        $answer->correct_answer_text = $correctAnswerText;
                    }
                    
                    // Log for debugging
                    \Log::debug('Exam scoring - MC answer comparison', [
                        'attempt_id' => $attempt->id,
                        'question_id' => $answer->question_id,
                        'selected_answer_position' => $answer->selected_answer,
                        'selected_text_stored' => $answer->selected_answer_text,
                        'selected_text_fallback' => $studentSelectedAnswerText,
                        'correct_text' => $correctAnswerText,
                    ]);
                    
                    // Score: Compare TEXT values (case-insensitive, trimmed)
                    // Both must be non-empty and text must match
                    if ($studentSelectedAnswerText && $correctAnswerText) {
                        // Use robust normalization for comparison
                        $studentClean = ScoringService::normalizeAnswerText($studentSelectedAnswerText);
                        $correctClean = ScoringService::normalizeAnswerText($correctAnswerText);
                        
                        $answer->is_correct = ($studentClean === $correctClean);
                        
                        // Log for debugging
                        \Log::debug('Exam scoring - is_correct set', [
                            'attempt_id' => $attempt->id,
                            'question_id' => $answer->question_id,
                            'selected_raw' => $studentSelectedAnswerText,
                            'selected_normalized' => $studentClean,
                            'correct_raw' => $correctAnswerText,
                            'correct_normalized' => $correctClean,
                            'is_correct' => $answer->is_correct,
                        ]);
                    } else {
                        // Missing text data - mark as incorrect
                        $answer->is_correct = false;
                        
                        \Log::warning('Exam scoring - missing text', [
                            'attempt_id' => $attempt->id,
                            'question_id' => $answer->question_id,
                            'has_selected_text' => !!$studentSelectedAnswerText,
                            'has_correct_text' => !!$correctAnswerText,
                        ]);
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

            // End the exam session when exam is submitted
            $session = ExamSession::where('exam_attempt_id', $attempt->id)->first();
            if ($session) {
                $session->end();
            }

            return $attempt;
        });
    }

    /**
     * Check if student can access attempt.
     */
    public static function canAccessAttempt(ExamAttempt $attempt, User $student): bool
    {
        // Use loose comparison (==) with string casting to handle type mismatches
        // between DB (string "4") and model (integer 4) in production environments
        return (string)$attempt->student_id == (string)$student->id;
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
