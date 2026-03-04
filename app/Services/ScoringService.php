<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;

/**
 * ScoringService - Handles dynamic weighted scoring for exams
 * Automatically calculates weights based on question distribution
 * 
 * Weighting Logic:
 * - If both MC and Essay exist: MC gets 70%, Essay gets 30% (configurable)
 * - If only MC: MC gets 100%
 * - If only Essay: Essay gets 100%
 * 
 * Scoring Logic:
 * - MC: Automatically scored on submission → Score = (Correct/Total) × Weight_MC
 * - Essay: Teacher inputs points (0-10 per question) → Score = (Sum_Points/Sum_Max_Points) × Weight_Essay
 * - Final: Score = Score_MC + Score_Essay (max 100)
 */
class ScoringService
{
    /**
     * Get dynamic weights for exam based on question distribution.
     * Returns array with 'mc_weight' and 'essay_weight' (0-100, sum = 100)
     */
    public static function getExamWeights(Exam $exam)
    {
        $weight_pg = $exam->weight_pg ?? 70;
        $weight_essay = $exam->weight_essay ?? 30;

        $questions = $exam->questions()->get();
        $mc_count = $questions->where('question_type', 'multiple_choice')->count();
        $essay_count = $questions->where('question_type', 'essay')->count();

        // If total weights not 100, normalize (though migration ensures consistency)
        if ($weight_pg + $weight_essay !== 100) {
            $total = $weight_pg + $weight_essay;
            $weight_pg = ($weight_pg / $total) * 100;
            $weight_essay = ($weight_essay / $total) * 100;
        }

        // Configuration based on active question types
        if ($mc_count > 0 && $essay_count > 0) {
            return [
                'mc_weight' => $weight_pg,
                'essay_weight' => $weight_essay,
                'has_mc' => true,
                'has_essay' => true,
            ];
        } elseif ($mc_count > 0) {
            return [
                'mc_weight' => 100,
                'essay_weight' => 0,
                'has_mc' => true,
                'has_essay' => false,
            ];
        } elseif ($essay_count > 0) {
            return [
                'mc_weight' => 0,
                'essay_weight' => 100,
                'has_mc' => false,
                'has_essay' => true,
            ];
        }

        return [
            'mc_weight' => 0,
            'essay_weight' => 0,
            'has_mc' => false,
            'has_essay' => false,
        ];
    }

    /**
     * Normalize answer text for robust comparison.
     * Handles whitespace, case, HTML entities, and special characters.
     * 
     * CRITICAL: This ensures answers are compared consistently regardless of formatting
     */
    public static function normalizeAnswerText($text)
    {
        if (!$text) {
            return '';
        }

        // Convert to string
        $text = (string)$text;

        // 1. Decode HTML entities (&nbsp; → space, etc.)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 2. Convert to lowercase for case-insensitive comparison
        $text = strtolower($text);

        // 3. Remove extra whitespace: trim ends and collapse internal spaces
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);  // Collapse multiple spaces to single space

        // 4. Remove common variations in punctuation/special chars that don't affect meaning
        // (optional - only if needed)
        $text = str_replace(['−', '–', '—'], '-', $text);  // Normalize dashes

        return $text;
    }

    /**
     * Calculate MC score for an exam attempt.
     * Returns score 0-100 based on correct answers
     * 
     * CRITICAL: Uses TEXT-based comparison to match ExamEngineService scoring

     * This handles shuffled options correctly!
     */
    public static function calculateMCScore(ExamAttempt $attempt)
    {
        $answers = ExamAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get()
            ->filter(function ($answer) {
                return $answer->question->question_type === 'multiple_choice';
            });

        if ($answers->count() === 0) {
            return 0;
        }

        $correct = 0;
        foreach ($answers as $answer) {
            // Use TEXT-based comparison (same as ExamEngineService::submitExam)
            // This handles shuffled options correctly
            
            // Get selected answer text - prefer stored, fallback to lookup
            $selectedText = $answer->selected_answer_text;
            if (!$selectedText && $answer->selected_answer) {
                // selected_answer is lowercase (a, b, c, d, e)
                $selectedText = $answer->question->{"option_" . strtolower($answer->selected_answer)} ?? null;
            }
            
            // Get correct answer text
            $correctText = $answer->correct_answer_text;
            if (!$correctText) {
                // correct_answer might be uppercase (A, B, C, D, E) - convert to lowercase for column access
                $correctPosition = strtolower($answer->question->correct_answer);
                $correctText = $answer->question->{"option_" . $correctPosition} ?? null;
            }
            
            // NORMALIZE both texts using robust comparison
            if ($selectedText && $correctText) {
                $selectedClean = self::normalizeAnswerText($selectedText);
                $correctClean = self::normalizeAnswerText($correctText);
                
                // Log for debugging
                \Log::debug('MC answer comparison', [
                    'attempt_id' => $attempt->id,
                    'question_id' => $answer->question_id,
                    'selected_raw' => $selectedText,
                    'selected_normalized' => $selectedClean,
                    'correct_raw' => $correctText,
                    'correct_normalized' => $correctClean,
                    'match' => ($selectedClean === $correctClean),
                ]);
                
                if ($selectedClean === $correctClean) {
                    $correct++;
                }
            }
        }

        // Score: (correct / total) × 100
        return ($correct / $answers->count()) * 100;
    }

    /**
     * Get all essay answers for an attempt.
     * Returns array of essay answers grouped by question
     */
    public static function getEssayAnswers(ExamAttempt $attempt)
    {
        return ExamAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get()
            ->filter(function ($answer) {
                return $answer->question->question_type === 'essay';
            })
            ->map(function ($answer) {
                return [
                    'answer_id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'question_text' => $answer->question->question_text,
                    'essay_answer' => $answer->essay_answer,
                    'score' => $answer->essay_score ?? null,
                ];
            });
    }

    /**
     * Save essay score for a single answer.
     * Score should be 0-10 (teacher's input)
     */
    public static function saveEssayScore(ExamAttempt $attempt, int $answerId, float $score)
    {
        if ($score < 0 || $score > 100) {
            throw new \Exception('Essay score must be between 0 and 100.');
        }

        $answer = ExamAnswer::where('id', $answerId)
            ->where('attempt_id', $attempt->id)
            ->first();

        if (!$answer) {
            throw new \Exception('Answer record not found.');
        }

        $answer->essay_score = $score;
        $answer->save();

        return $answer;
    }

    /**
     * Calculate essay score for an attempt (0-100).
     * Converts teacher points to proportional score
     * 
     * Formula: (Sum of points) / (Count × 10) × 100
     */
    public static function calculateEssayScore(ExamAttempt $attempt)
    {
        $answers = ExamAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get()
            ->filter(function ($answer) {
                return $answer->question->question_type === 'essay';
            });

        if ($answers->count() === 0) {
            return 0;
        }

        $total_points = 0;
        $graded_count = 0;

        foreach ($answers as $answer) {
            if ($answer->essay_score !== null) {
                $total_points += $answer->essay_score;
                $graded_count++;
            }
        }

        if ($graded_count === 0) {
            return null; // Not all scored yet
        }

        // Score: (sum of points) / (count × 100) × 100
        $max_points = $answers->count() * 100;
        return ($total_points / $max_points) * 100;
    }

    /**
     * Calculate final score combining MC and Essay with dynamic weighting.
     * 
     * Returns:
     * - null if essays not all graded yet
     * - 0-100 if calculation possible
     */
    public static function calculateFinalScore(ExamAttempt $attempt)
    {
        $exam = $attempt->exam;
        $weights = self::getExamWeights($exam);

        $score_mc = 0;
        $score_essay = 0;

        // Calculate MC score if questions exist
        if ($weights['has_mc']) {
            $score_mc = self::calculateMCScore($attempt);
        }

        // Calculate Essay score if questions exist
        if ($weights['has_essay']) {
            $essay_score_raw = self::calculateEssayScore($attempt);

            // If not all essays graded, return null
            if ($essay_score_raw === null) {
                return null;
            }

            $score_essay = $essay_score_raw;
        }

        // Apply weights and sum
        $final_score = ($score_mc * $weights['mc_weight'] / 100) +
            ($score_essay * $weights['essay_weight'] / 100);

        // Ensure within 0-100 range
        return min(100, max(0, round($final_score, 2)));
    }

    /**
     * Update exam attempt scores.
     * Calculates and saves score_mc, score_essay, and final_score
     */
    public static function updateAttemptScores(ExamAttempt $attempt)
    {
        $exam = $attempt->exam;
        $weights = self::getExamWeights($exam);

        // Calculate scores
        $score_mc = $weights['has_mc'] ? self::calculateMCScore($attempt) : 0;
        $score_essay = $weights['has_essay'] ? self::calculateEssayScore($attempt) : 0;
        $final_score = self::calculateFinalScore($attempt);

        // Save to attempt
        $attempt->score_mc = $score_mc;
        $attempt->score_essay = $score_essay;
        $attempt->final_score = $final_score;
        $attempt->save();

        return $attempt;
    }

    /**
     * Get grade letter based on score.
     * A: 85-100, B: 75-84, C: 65-74, D: 50-64, F: 0-49
     */
    public static function getGrade(float $score, $kkm = 75)
    {
        if ($score >= 85) return 'A';
        if ($score >= $kkm) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 40) return 'D';
        return 'F';
    }

    /**
     * Get grade color for display.
     */
    public static function getGradeColor(string $grade)
    {
        return match ($grade) {
            'A' => 'bg-green-100 text-green-800 border-green-300',
            'B' => 'bg-blue-100 text-blue-800 border-blue-300',
            'C' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'D' => 'bg-orange-100 text-orange-800 border-orange-300',
            'F' => 'bg-red-100 text-red-800 border-red-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }
}
