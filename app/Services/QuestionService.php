<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class QuestionService
{
    /**
     * Create a new question.
     */
    public static function createQuestion(array $data): Question
    {
        // Handle image uploads
        $data = self::handleImageUploads($data);

        // Prepare data based on question type
        if ($data['question_type'] === 'essay') {
            $data['option_a'] = null;
            $data['option_b'] = null;
            $data['option_c'] = null;
            $data['option_d'] = null;
            $data['option_e'] = null;
            $data['option_a_image'] = null;
            $data['option_b_image'] = null;
            $data['option_c_image'] = null;
            $data['option_d_image'] = null;
            $data['option_e_image'] = null;
            $data['correct_answer'] = null;
        }

        return Question::create($data);
    }

    /**
     * Update a question with image handling.
     */
    public static function updateQuestion(Question $question, array $data): Question
    {
        // Handle image updates - delete old images before uploading new
        $data = self::handleImageUpdates($question, $data);

        // Prepare data based on question type
        if ($data['question_type'] === 'essay') {
            // Delete option images for essay type
            self::deleteImageIfExists($question->option_a_image);
            self::deleteImageIfExists($question->option_b_image);
            self::deleteImageIfExists($question->option_c_image);
            self::deleteImageIfExists($question->option_d_image);
            self::deleteImageIfExists($question->option_e_image);

            $data['option_a'] = null;
            $data['option_b'] = null;
            $data['option_c'] = null;
            $data['option_d'] = null;
            $data['option_e'] = null;
            $data['option_a_image'] = null;
            $data['option_b_image'] = null;
            $data['option_c_image'] = null;
            $data['option_d_image'] = null;
            $data['option_e_image'] = null;
            $data['correct_answer'] = null;
        }

        $question->update($data);
        return $question;
    }

    /**
     * Delete a question and all its images.
     */
    public static function deleteQuestion(Question $question): void
    {
        // Delete all images
        self::deleteImageIfExists($question->question_image);
        self::deleteImageIfExists($question->option_a_image);
        self::deleteImageIfExists($question->option_b_image);
        self::deleteImageIfExists($question->option_c_image);
        self::deleteImageIfExists($question->option_d_image);
        self::deleteImageIfExists($question->option_e_image);

        // Delete the question
        $question->delete();
    }

    /**
     * Handle image uploads for new questions.
     */
    private static function handleImageUploads(array $data): array
    {
        // Handle question image
        if (isset($data['question_image']) && $data['question_image']) {
            $file = $data['question_image'];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['question_image'] = 'images/' . $filename;
        } else {
            unset($data['question_image']);
        }

        // Handle option images
        $optionFields = ['option_a_image', 'option_b_image', 'option_c_image', 'option_d_image', 'option_e_image'];
        foreach ($optionFields as $field) {
            if (isset($data[$field]) && $data[$field]) {
                $file = $data[$field];
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $data[$field] = 'images/' . $filename;
            } else {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Handle image updates - delete old and upload new.
     */
    private static function handleImageUpdates(Question $question, array $data): array
    {
        // Handle question image update
        if (isset($data['question_image']) && $data['question_image']) {
            // Delete old image if exists
            self::deleteImageIfExists($question->question_image);

            // Upload new image
            $file = $data['question_image'];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['question_image'] = 'images/' . $filename;
        } elseif (!isset($data['question_image'])) {
            // If no new image uploaded, keep the old one
            unset($data['question_image']);
        }

        // Handle option images update
        $optionFields = ['option_a_image', 'option_b_image', 'option_c_image', 'option_d_image', 'option_e_image'];
        foreach ($optionFields as $field) {
            if (isset($data[$field]) && $data[$field]) {
                // Delete old image if exists
                $oldPath = $question->$field;
                self::deleteImageIfExists($oldPath);

                // Upload new image
                $file = $data[$field];
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $data[$field] = 'images/' . $filename;
            } elseif (!isset($data[$field])) {
                // If no new image uploaded, keep the old one
                unset($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Delete image file if it exists.
     */
    public static function deleteImageIfExists(?string $imagePath): bool
    {
        if (!$imagePath) {
            return false;
        }

        $fullPath = public_path($imagePath);
        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }

        return false;
    }

    /**
     * Validate question data.
     */
    public static function validateQuestionData(array $data): array
    {
        $errors = [];

        // Check subject exists
        if (!Subject::where('id', $data['subject_id'] ?? null)->exists()) {
            $errors['subject'] = 'Subject not found';
        }

        // Check jenjang
        if (!in_array($data['jenjang'] ?? null, ['10', '11', '12'])) {
            $errors['jenjang'] = 'Invalid grade level';
        }

        // Check difficulty level
        if (!in_array($data['difficulty_level'] ?? null, ['easy', 'medium', 'hard'])) {
            $errors['difficulty'] = 'Invalid difficulty level';
        }

        // Check question type
        if (!in_array($data['question_type'] ?? null, ['multiple_choice', 'essay'])) {
            $errors['type'] = 'Invalid question type';
        }

        // Type-specific validation
        if ($data['question_type'] === 'multiple_choice') {
            if (empty($data['option_a'] ?? null)) {
                $errors['option_a'] = 'Option A is required for multiple choice';
            }
            if (empty($data['option_b'] ?? null)) {
                $errors['option_b'] = 'Option B is required for multiple choice';
            }
            if (empty($data['option_c'] ?? null)) {
                $errors['option_c'] = 'Option C is required for multiple choice';
            }
            if (empty($data['option_d'] ?? null)) {
                $errors['option_d'] = 'Option D is required for multiple choice';
            }
            if (!in_array($data['correct_answer'] ?? null, ['A', 'B', 'C', 'D', 'E'])) {
                $errors['correct_answer'] = 'Valid correct answer is required for multiple choice';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
