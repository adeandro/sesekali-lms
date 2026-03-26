<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['superadmin', 'teacher']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'subject_id'    => 'required|exists:subjects,id',
            'class_id'      => 'required|exists:classes,id',
            'semester'      => 'required|in:1,2',
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'grades'        => 'nullable|array',
            'grades.*.*'    => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'grades.*.*.min' => 'Nilai harus antara 0 sampai 100.',
            'grades.*.*.max' => 'Nilai harus antara 0 sampai 100.',
            'grades.*.*.numeric' => 'Nilai harus berupa angka.',
        ];
    }
}
