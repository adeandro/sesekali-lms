<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
     * Normalize jenjang[] checkbox array → sorted comma-separated string.
     */
    protected function prepareForValidation()
    {
        $jenjangArr = $this->input('jenjang', []);
        if (is_array($jenjangArr)) {
            $valid = array_filter($jenjangArr, fn($v) => in_array($v, ['10', '11', '12']));
            sort($valid);
            $this->merge([
                'jenjang' => !empty($valid) ? implode(',', array_unique($valid)) : null,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'subject_id' => [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role === 'teacher' && !auth()->user()->subjects->contains('id', $value)) {
                        $fail('Anda hanya diperbolehkan mengolah data sesuai dengan Mata Pelajaran yang Anda ampu.');
                    }
                },
            ],
            'jenjang' => 'nullable|string',
            'topic' => 'required|string|max:255',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'question_type' => 'required|in:multiple_choice,essay',
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'explanation' => 'nullable|string',
        ];

        // Image validation for options
        $imageOptions = ['option_a_image', 'option_b_image', 'option_c_image', 'option_d_image', 'option_e_image'];
        foreach ($imageOptions as $imageOption) {
            $rules[$imageOption] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        // Multiple choice specific validation
        if ($this->input('question_type') === 'multiple_choice') {
            $rules['option_a'] = 'required|string';
            $rules['option_b'] = 'required|string';
            $rules['option_c'] = 'required|string';
            $rules['option_d'] = 'required|string';
            $rules['option_e'] = 'nullable|string';
            $rules['correct_answer'] = 'required|in:a,b,c,d,e,A,B,C,D,E';
        } else {
            // Essay type - no options
            $rules['option_a'] = 'nullable|string';
            $rules['option_b'] = 'nullable|string';
            $rules['option_c'] = 'nullable|string';
            $rules['option_d'] = 'nullable|string';
            $rules['option_e'] = 'nullable|string';
            $rules['correct_answer'] = 'nullable';
        }

        return $rules;
    }
}
