<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExamResultsExport implements FromCollection
{
    use RegistersEventListeners;

    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam->load('subject');
    }

    /**
     * Get the results data with title header
     */
    public function collection()
    {
        $attempts = $this->exam->attempts()
            ->where('status', 'submitted')
            ->with('student')
            ->orderByDesc('final_score')
            ->get();

        // Start collection with title and info rows
        $data = collect([
            ['HASIL UJIAN / EXAM RESULTS'],
            ['Nama Ujian (Exam Name): ' . $this->exam->name],
            ['Mata Pelajaran (Subject): ' . ($this->exam->subject->name ?? 'N/A')],
            ['Tanggal Ekspor (Export Date): ' . now()->format('d/m/Y H:i:s')],
            [], // Empty row for spacing
            [
                'Ranking',
                'NIS',
                'Name',
                'Class',
                'MC Score',
                'Essay Score',
                'Final Score',
                'Subject',
                'Submitted At',
            ],
        ]);

        // Add student results
        $attempts->each(function ($attempt, $index) use ($data) {
            $gradeDisplay = 'Grade ' . ($attempt->student->grade ?? '-') . ' - ' . ($attempt->student->class_group ?? '-');
            $data->push([
                $index + 1,  // ranking
                $attempt->student->nis ?? '-',  // nis
                $attempt->student->name,  // name
                $gradeDisplay,  // grade/class
                round($attempt->score_mc ?? 0, 2),  // score_mc
                round($attempt->score_essay ?? 0, 2),  // score_essay
                round($attempt->final_score ?? 0, 2),  // final_score
                $this->exam->subject->name ?? 'N/A',  // subject
                $attempt->submitted_at->format('Y-m-d H:i:s'),  // submitted_at
            ]);
        });

        return $data;
    }

    /**
     * Add styling to the sheet
     */
    public static function afterSheet(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();

        // Style title row (Row 1)
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType('solid')
            ->getStartColor()->setARGB('FFFF9999');

        // Style info rows (Rows 2-4)
        $sheet->getStyle('A2:I4')->getFont()->setSize(11);

        // Style header row (Row 6)
        $sheet->getStyle('A6:I6')->getFont()->setBold(true);
        $sheet->getStyle('A6:I6')->getFill()
            ->setFillType('solid')
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A6:I6')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A6:I6')->getAlignment()->setHorizontal('center');

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
