<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Subject;
use App\Services\QuestionService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class QuestionImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $updatedCount = 0;
    public $skippedCount = 0;
    public $failureCount = 0;
    public $errors = [];
    public $skipped = [];
    public $updated = [];
    private $seenQuestions = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        // Increase execution time for large imports
        set_time_limit(300);
        
        $rowNumber = 2;

        foreach ($collection as $row) {
            try {
                // Get or create subject
                $subject = Subject::firstOrCreate(
                    ['name' => $row['subject'] ?? null],
                    ['name' => $row['subject'] ?? null]
                );

                $questionText = trim($row['question_text'] ?? '');
                
                // Skip empty rows
                if (empty($questionText)) {
                    $rowNumber++;
                    continue;
                }

                $data = [
                    'subject_id' => $subject->id,
                    'jenjang' => $row['jenjang'] ?? null,
                    'topic' => $row['topic'] ?? null,
                    'difficulty_level' => $row['difficulty'] ?? null,
                    'question_type' => $row['question_type'] ?? null,
                    'question_text' => $questionText,
                    'option_a' => $row['option_a'] ?? null,
                    'option_b' => $row['option_b'] ?? null,
                    'option_c' => $row['option_c'] ?? null,
                    'option_d' => $row['option_d'] ?? null,
                    'option_e' => $row['option_e'] ?? null,
                    'correct_answer' => strtoupper($row['correct_answer'] ?? null),
                    'explanation' => $row['explanation'] ?? null,
                ];

                // Create unique key for duplicate detection: subject + question_text
                $uniqueKey = $subject->id . '::' . md5($questionText);

                // Check if question already seen in this import
                if (isset($this->seenQuestions[$uniqueKey])) {
                    $this->skippedCount++;
                    $this->skipped[] = [
                        'row' => $rowNumber,
                        'subject' => $subject->name,
                        'question' => substr($questionText, 0, 100) . (strlen($questionText) > 100 ? '...' : ''),
                        'reason' => 'Duplicate in import file (first seen in row ' . $this->seenQuestions[$uniqueKey] . ')',
                    ];
                    $rowNumber++;
                    continue;
                }

                // Check if question already exists in database
                $existingQuestion = Question::where('subject_id', $subject->id)
                    ->where('question_text', $questionText)
                    ->first();

                if ($existingQuestion) {
                    // Check if data has changed
                    $hasChanges = $this->hasDataChanged($existingQuestion, $data);

                    if ($hasChanges) {
                        // Update the question with new data
                        $existingQuestion->update($data);
                        
                        $this->updatedCount++;
                        $this->updated[] = [
                            'row' => $rowNumber,
                            'subject' => $subject->name,
                            'question' => substr($questionText, 0, 100) . (strlen($questionText) > 100 ? '...' : ''),
                            'reason' => 'Question updated with new data',
                        ];
                    } else {
                        // No changes detected, skip
                        $this->skippedCount++;
                        $this->skipped[] = [
                            'row' => $rowNumber,
                            'subject' => $subject->name,
                            'question' => substr($questionText, 0, 100) . (strlen($questionText) > 100 ? '...' : ''),
                            'reason' => 'Question already exists (no changes detected)',
                        ];
                    }
                    
                    $rowNumber++;
                    continue;
                }

                // Mark as seen in this import
                $this->seenQuestions[$uniqueKey] = $rowNumber;

                // Validate
                $validation = QuestionService::validateQuestionData($data);

                if (!$validation['valid']) {
                    $this->failureCount++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validation['errors'],
                    ];
                    $rowNumber++;
                    continue;
                }

                // Create question only if it's truly new
                Question::create($data);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->failureCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => ['general' => $e->getMessage()],
                ];
            }

            $rowNumber++;
        }
    }

    /**
     * Check if imported data has changed compared to existing question
     * @param Question $existingQuestion
     * @param array $newData
     * @return bool
     */
    private function hasDataChanged(Question $existingQuestion, array $newData): bool
    {
        // Fields to compare for changes
        $fieldsToCompare = [
            'jenjang',
            'topic',
            'difficulty_level',
            'question_type',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'option_e',
            'correct_answer',
            'explanation',
        ];

        foreach ($fieldsToCompare as $field) {
            $existingValue = (string) ($existingQuestion->$field ?? '');
            $newValue = (string) ($newData[$field] ?? '');

            if ($existingValue !== $newValue) {
                return true;
            }
        }

        return false;
    }
}
