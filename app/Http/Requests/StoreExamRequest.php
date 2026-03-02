<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['superadmin', 'teacher']);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert datetime-local format (YYYY-MM-DDTHH:mm) to Y-m-d H:i
        if ($this->start_time) {
            $this->merge([
                'start_time' => str_replace('T', ' ', $this->start_time),
            ]);
        }

        if ($this->end_time) {
            $this->merge([
                'end_time' => str_replace('T', ' ', $this->end_time),
            ]);
        }

        // Handle unchecked checkboxes - HTML doesn't send them, so we need to explicitly set to false
        $booleanFields = ['randomize_questions', 'randomize_options', 'show_score_after_submit', 'allow_review_results'];
        foreach ($booleanFields as $field) {
            if (!$this->has($field)) {
                $this->merge([$field => false]);
            }
        }

        // For teachers, we no longer force a single subject_id as they can have many.
        // The value from the form will be validated against their assigned subjects in rules().
        if (auth()->check() && auth()->user()->role === 'teacher') {
            // No-op, just keep the input
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subject_id' => [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $value)) {
                        $fail('Anda hanya diperbolehkan mengolah data sesuai dengan Mata Pelajaran yang Anda ampu.');
                    }
                },
            ],
            'jenjang' => 'required|in:10,11,12',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'total_questions' => 'required|integer|min:1|max:500',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i|after:start_time',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_score_after_submit' => 'boolean',
            'allow_review_results' => 'boolean',
            'weight_pg' => 'required|integer|min:0|max:100',
            'weight_essay' => 'required|integer|min:0|max:100',
            'status' => 'required|in:draft,published',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'end_time.after' => 'End time must be after start time.',
            'total_questions.min' => 'Total questions must be at least 1.',
            'duration_minutes.max' => 'Duration cannot exceed 8 hours (480 minutes).',
        ];
    }
}
