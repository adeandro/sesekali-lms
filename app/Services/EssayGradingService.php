<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use Illuminate\Support\Facades\DB;

class EssayGradingService
{
    /**
     * Get essay answers for an attempt
     */
    public static function getEssayAnswers(ExamAttempt $attempt)
    {
        return ExamAnswer::where('attempt_id', $attempt->id)
            ->with(['question' => function ($q) {
                $q->where('question_type', 'essay');
            }])
            ->whereHas('question', fn($q) => $q->where('question_type', 'essay'))
            ->get();
    }

    /**
     * Save essay scores for an attempt
     */
    public static function saveEssayScores(ExamAttempt $attempt, array $essayScores)
    {
        return DB::transaction(function () use ($attempt, $essayScores) {
            $totalEssayScore = 0;
            $essayCount = 0;

            // Update each essay answer with its score
            foreach ($essayScores as $questionId => $score) {
                $score = (float) $score;

                // Validate score range (0-100)
                if ($score < 0 || $score > 100) {
                    throw new \Exception("Score must be between 0 and 100. Got $score for question $questionId");
                }

                $answer = ExamAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $questionId)
                    ->firstOrFail();

                $answer->update(['essay_score' => $score]);
                $totalEssayScore += $score;
                $essayCount++;
            }

            // Calculate average essay score
            $averageEssayScore = $essayCount > 0 ? $totalEssayScore / $essayCount : 0;

            // Update exam attempt with new scores using ScoringService
            $attempt->score_essay = $averageEssayScore;
            $attempt->save(); // Save essay score first
            
            \App\Services\ScoringService::updateAttemptScores($attempt);

            return $attempt->refresh();
        });
    }

    /**
     * Get exam statistics
     */
    public static function getExamStatistics($examId)
    {
        $attempts = ExamAttempt::where('exam_id', $examId)
            ->where('status', 'submitted')
            ->get();

        if ($attempts->isEmpty()) {
            return [
                'total_participants' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'pass_rate' => 0,
            ];
        }

        $scores = $attempts->pluck('final_score');
        $exam = \App\Models\Exam::with('subject')->find($examId);
        $kkm = $exam->subject->kkm ?? 75;

        return [
            'total_participants' => $attempts->count(),
            'average_score' => $scores->average(),
            'highest_score' => $scores->max(),
            'lowest_score' => $scores->min(),
            'pass_rate' => $attempts->where('final_score', '>=', $kkm)->count() / $attempts->count() * 100,
        ];
    }

    /**
     * Get exam rankings
     */
    public static function getExamRanking($examId)
    {
        return ExamAttempt::where('exam_id', $examId)
            ->where('status', 'submitted')
            ->with('student')
            ->orderByDesc('final_score')
            ->get()
            ->map(function ($attempt, $index) {
                $attempt->ranking = $index + 1;
                return $attempt;
            });
    }

    /**
     * Get student exam history
     */
    public static function getStudentExamHistory($studentId)
    {
        return ExamAttempt::where('student_id', $studentId)
            ->where('status', 'submitted')
            ->with(['exam' => function ($q) {
                $q->with('subject');
            }])
            ->orderByDesc('submitted_at')
            ->get();
    }

    /**
     * Check if student can view score
     */
    public static function canViewScore(ExamAttempt $attempt)
    {
        // Check if exam exists (it might have been deleted)
        if (!$attempt->exam) {
            return false;
        }

        // If exam settings allow showing score after submit, return true
        if ($attempt->exam->show_score_after_submit) {
            return true;
        }

        // Otherwise, score is hidden until exam is finished (status = 'finished')
        return $attempt->exam->status === 'finished';
    }
}
