<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class RecalculateExamScores extends Command
{
    protected $signature = 'exam:recalculate-scores {--attempt-id= : Specific attempt ID}';
    protected $description = 'Recalculate exam scores using robust normalization logic';

    public function handle()
    {
        $query = ExamAttempt::where('status', 'submitted');

        if ($this->option('attempt-id')) {
            $query->where('id', $this->option('attempt-id'));
        }

        $attempts = $query->get();

        if ($attempts->isEmpty()) {
            $this->info('No submitted exams found to recalculate.');
            return 0;
        }

        $this->info("Recalculating {$attempts->count()} exam attempt(s)...");

        foreach ($attempts as $attempt) {
            try {
                // Get all answers
                $answers = ExamAnswer::where('attempt_id', $attempt->id)
                    ->with('question')
                    ->get();

                $totalCorrect = 0;
                $totalIncorrect = 0;
                $totalUnanswered = 0;

                // Recalculate is_correct for MC questions
                foreach ($answers as $answer) {
                    if ($answer->question->question_type === 'multiple_choice') {
                        // Get selected answer text
                        $selectedText = $answer->selected_answer_text;
                        if (!$selectedText && $answer->selected_answer) {
                            $selectedText = $answer->question->{"option_" . $answer->selected_answer} ?? null;
                        }

                        // Get correct answer text
                        $correctText = $answer->correct_answer_text;
                        if (!$correctText) {
                            // correct_answer is stored as uppercase (A, B, C, D, E) - convert to lowercase
                            $correctPosition = strtolower($answer->question->correct_answer);
                            $correctText = $answer->question->{"option_" . $correctPosition} ?? null;
                            $answer->correct_answer_text = $correctText;
                        }

                        // Score using robust normalization
                        if ($selectedText && $correctText) {
                            $selectedClean = ScoringService::normalizeAnswerText($selectedText);
                            $correctClean = ScoringService::normalizeAnswerText($correctText);
                            $answer->is_correct = ($selectedClean === $correctClean);
                        } else {
                            $answer->is_correct = false;
                        }

                        if ($answer->is_correct === true) {
                            $totalCorrect++;
                        } elseif ($answer->is_correct === false) {
                            $totalIncorrect++;
                        } else {
                            $totalUnanswered++;
                        }

                        $answer->save();
                    } elseif ($answer->question->question_type === 'essay') {
                        // Essays are teacher-graded, skip
                        if ($answer->is_correct === null) {
                            $totalUnanswered++;
                        }
                    }
                }

                // Recalculate score
                $score_mc = ScoringService::calculateMCScore($attempt);
                $weights = ScoringService::getExamWeights($attempt->exam);

                $final_score = null;
                if ($weights['has_essay']) {
                    $final_score = null; // Needs teacher input
                } else {
                    $final_score = $score_mc;
                }

                // Update attempt
                $attempt->update([
                    'score_mc' => $score_mc,
                    'final_score' => $final_score,
                ]);

                $this->info(
                    "Attempt {$attempt->id} (Student {$attempt->student_id}): "
                    . "Correct={$totalCorrect}, Wrong={$totalIncorrect}, Skipped={$totalUnanswered}, Score={$final_score}"
                );
            } catch (\Exception $e) {
                $this->error("Error recalculating attempt {$attempt->id}: " . $e->getMessage());
            }
        }

        $this->info('Scoring recalculation complete!');
        return 0;
    }
}
