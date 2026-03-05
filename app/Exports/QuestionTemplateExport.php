<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class QuestionTemplateExport implements FromCollection, WithHeadings
{
    /**
     * @return Collection
     */
    public function collection()
    {
        return collect([
            [
                'subject' => 'Matematika',
                'jenjang' => '10',
                'topic' => 'Logaritma',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
                'question_text' => 'Contoh pertanyaan pilihan ganda...',
                'option_a' => 'Pilihan A',
                'option_b' => 'Pilihan B',
                'option_c' => 'Pilihan C',
                'option_d' => 'Pilihan D',
                'option_e' => 'Pilihan E',
                'correct_answer' => 'A',
                'explanation' => 'Penjelasan jawaban...',
            ],
            [
                'subject' => 'Matematika',
                'jenjang' => '10',
                'topic' => 'Persamaan Kuadrat',
                'difficulty' => 'easy',
                'question_type' => 'essay',
                'question_text' => 'Contoh pertanyaan essay...',
                'option_a' => '',
                'option_b' => '',
                'option_c' => '',
                'option_d' => '',
                'option_e' => '',
                'correct_answer' => '',
                'explanation' => 'Kunci jawaban essay atau pedoman penskoran...',
            ]
        ]);
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
