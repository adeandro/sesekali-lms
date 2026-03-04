<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Pagination\Paginator;

class ExamService
{
    /**
     * Create a new exam.
     */
    public static function createExam(array $data): Exam
    {
        return Exam::create($data);
    }

    /**
     * Update an exam.
     */
    public static function updateExam(Exam $exam, array $data): Exam
    {
        // Check if total_questions is being reduced
        $oldTotal = $exam->total_questions;
        $newTotal = $data['total_questions'] ?? $oldTotal;

        // If total_questions is reduced, remove excess questions
        if ($newTotal < $oldTotal) {
            $currentAttachedCount = $exam->questions()->count();

            if ($currentAttachedCount > $newTotal) {
                // Get the questions to detach (keep the first N questions, remove the rest)
                $questionsToKeep = $exam->questions()
                    ->limit($newTotal)
                    ->pluck('questions.id')
                    ->toArray();

                $allAttachedIds = $exam->questions()
                    ->pluck('questions.id')
                    ->toArray();

                // Find which questions to remove (those not in the keep list)
                $questionsToRemove = array_diff($allAttachedIds, $questionsToKeep);

                // Detach the excess questions
                if (!empty($questionsToRemove)) {
                    $exam->questions()->detach($questionsToRemove);
                }
            }
        }

        $exam->update($data);
        return $exam;
    }

    /**
     * Attach questions to exam.
     */
    public static function attachQuestions(Exam $exam, array $questionIds): void
    {
        // Get current count
        $currentCount = $exam->questions()->count();
        $newCount = count($questionIds);

        // Check if total exceeds limit
        if ($currentCount + $newCount > $exam->total_questions) {
            throw new \Exception(
                "Cannot attach " . $newCount . " questions. " .
                    "Already have " . $currentCount . " questions. " .
                    "Limit is " . $exam->total_questions . " questions."
            );
        }

        // Attach without duplicates
        $exam->questions()->attach($questionIds);
    }

    /**
     * Detach a question from exam.
     */
    public static function detachQuestion(Exam $exam, int $questionId): void
    {
        $exam->questions()->detach($questionId);
    }

    /**
     * Publish an exam.
     */
    public static function publishExam(Exam $exam): bool
    {
        if (!$exam->canPublish()) {
            throw new \Exception(
                "Cannot publish exam. Need " . $exam->total_questions . " questions, " .
                    "but only have " . $exam->questions()->count() . "."
            );
        }

        $exam->update(['status' => 'published']);
        return true;
    }

    /**
     * Set exam to draft.
     */
    public static function setToDraft(Exam $exam): void
    {
        if ($exam->status === 'finished') {
            throw new \Exception('Cannot modify finished exam.');
        }

        $exam->update(['status' => 'draft']);
    }

    /**
     * Get exam list with filters.
     */
    public static function getExamsList(array $filters = [], int $perPage = 15)
    {
        $query = Exam::with('subject')->withCount('questions');

        // Search by title
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter by subject
        if (!empty($filters['subject'])) {
            $subjectIds = is_array($filters['subject']) ? $filters['subject'] : [$filters['subject']];
            $query->whereIn('subject_id', $subjectIds);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get available questions for an exam.
     */
    public static function getAvailableQuestions(Exam $exam, int $perPage = 15)
    {
        // Get questions that are:
        // 1. From the same subject
        // 2. Match the exam's jenjang (grade level)
        // 3. Not already attached to this exam
        return Question::where('subject_id', $exam->subject_id)
            ->where('jenjang', $exam->jenjang)
            ->whereNotIn('id', $exam->questions()->pluck('questions.id')->toArray())
            ->paginate($perPage);
    }
}
