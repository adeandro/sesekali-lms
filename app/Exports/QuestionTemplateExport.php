<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class QuestionTemplateExport implements
    FromCollection,
    WithHeadings,
    WithColumnFormatting
{
    public function columnFormats(): array
    {
        // Kolom B = jenjang, force sebagai text agar "10,11,12" tidak berubah
        return [
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }

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
                'jenjang' => '10,11,12',
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
            ],
            [
                'subject'        => 'Matematika',
                'jenjang'        => '10,11',
                'topic'          => 'Statistika',
                'difficulty'     => 'medium',
                'question_type'  => 'multiple_choice',
                'question_text'  => 'Contoh soal untuk kelas 10 dan 11...',
                'option_a'       => 'Pilihan A',
                'option_b'       => 'Pilihan B',
                'option_c'       => 'Pilihan C',
                'option_d'       => 'Pilihan D',
                'option_e'       => 'Pilihan E',
                'correct_answer' => 'B',
                'explanation'    => 'Penjelasan jawaban...',
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
