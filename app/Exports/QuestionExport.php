<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuestionExport implements FromCollection, WithHeadings
{
    protected $questions;

    public function __construct(Collection $questions)
    {
        $this->questions = $questions;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->questions->map(function ($question) {
            return [
                'subject' => $question->subject->name,
                'jenjang' => $question->jenjang,
                'topic' => $question->topic,
                'difficulty' => $question->difficulty_level,
                'question_type' => $question->question_type,
                'question_text' => $question->question_text,
                'option_a' => $question->option_a,
                'option_b' => $question->option_b,
                'option_c' => $question->option_c,
                'option_d' => $question->option_d,
                'option_e' => $question->option_e,
                'correct_answer' => $question->correct_answer,
                'explanation' => $question->explanation,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'subject',
            'jenjang',
            'topic',
            'difficulty',
            'question_type',
            'question_text',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'option_e',
            'correct_answer',
            'explanation',
        ];
    }
}
