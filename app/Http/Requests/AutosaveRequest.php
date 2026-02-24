<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutosaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'student';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question_id' => 'required|exists:questions,id',
            'selected_answer' => 'nullable|string',
            'essay_answer' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'question_id.required' => 'Question ID is required.',
            'question_id.exists' => 'The selected question does not exist.',
        ];
    }
}
