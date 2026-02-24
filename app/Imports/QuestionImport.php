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
    public $failureCount = 0;
    public $errors = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        $rowNumber = 2;

        foreach ($collection as $row) {
            try {
                // Get or create subject
                $subject = Subject::firstOrCreate(
                    ['name' => $row['subject'] ?? null],
                    ['name' => $row['subject'] ?? null]
                );

                $data = [
                    'subject_id' => $subject->id,
                    'jenjang' => $row['jenjang'] ?? null,
                    'topic' => $row['topic'] ?? null,
                    'difficulty_level' => $row['difficulty'] ?? null,
                    'question_type' => $row['question_type'] ?? null,
                    'question_text' => $row['question_text'] ?? null,
                    'option_a' => $row['option_a'] ?? null,
                    'option_b' => $row['option_b'] ?? null,
                    'option_c' => $row['option_c'] ?? null,
                    'option_d' => $row['option_d'] ?? null,
                    'option_e' => $row['option_e'] ?? null,
                    'correct_answer' => $row['correct_answer'] ?? null,
                    'explanation' => $row['explanation'] ?? null,
                ];

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

                // Create question
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
}
