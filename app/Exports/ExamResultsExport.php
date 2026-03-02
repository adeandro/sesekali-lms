<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class ExamResultsExport implements FromCollection
{
    use RegistersEventListeners;

    protected $filters;

    public function __construct(Exam $exam, array $filters = [])
    {
        $this->exam = $exam->load('subject');
        $this->filters = $filters;
    }

    /**
     * Get the results data with title header
     */
    public function collection()
    {
        $query = $this->exam->attempts()
            ->where('status', 'submitted')
            ->with('student')
            ->orderByDesc('final_score');

        // Apply filters
        if (!empty($this->filters['class'])) {
            $classData = explode('|', $this->filters['class']);
            if (count($classData) === 2) {
                $query->whereHas('student', fn($q) => 
                    $q->where('grade', $classData[0])
                      ->where('class_group', $classData[1])
                );
            }
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('student', fn($q) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
            );
        }

        $attempts = $query->get();

        // Start collection with title and info rows (Indonesian)
        $data = collect([
            ['HASIL UJIAN ' . strtoupper($this->exam->title)],
            ['Mata Pelajaran: ' . ($this->exam->subject->name ?? 'N/A')],
            ['Tanggal Ekspor: ' . now()->format('d/m/Y H:i:s')],
            ['KKM Mata Pelajaran: ' . ($this->exam->subject->kkm ?? 75)],
            [], // Empty row for spacing
            [
                'Ranking',
                'NIS',
                'Nama Lengkap',
                'Kelas / Rombel',
                'Skor PG',
                'Skor Esai',
                'Skor Akhir',
                'Status',
                'Waktu Selesai',
            ],
        ]);

        // Add student results
        $attempts->each(function ($attempt, $index) use ($data) {
            $gradeDisplay = ($attempt->student->grade ?? '-') . ' - ' . ($attempt->student->class_group ?? '-');
            $data->push([
                $index + 1,  // ranking
                $attempt->student->nis ?? '-',  // nis
                $attempt->student->name,  // name
                $gradeDisplay,  // class
                round($attempt->score_mc ?? 0, 2),  // score_mc
                round($attempt->score_essay ?? 0, 2),  // score_essay
                round($attempt->final_score ?? 0, 2),  // final_score
                $attempt->status_kelulusan,  // status (accessor from model)
                $attempt->submitted_at->format('d/m/Y H:i:s'),  // submitted_at
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
