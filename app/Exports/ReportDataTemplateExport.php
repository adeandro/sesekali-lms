<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;

class ReportDataTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new class implements FromCollection, WithHeadings, WithTitle {
                public function collection() { return collect([['12345', 'Budi Santoso', '0', '0', '0']]); }
                public function headings(): array { return ['NIS', 'Nama Siswa', 'Sakit', 'Izin', 'Alpha']; }
                public function title(): string { return 'Kehadiran'; }
            },
            new class implements FromCollection, WithHeadings, WithTitle {
                public function collection() { return collect([['12345', 'Budi Santoso', 'Baik', 'Baik', 'Baik']]); }
                public function headings(): array { return ['NIS', 'Nama Siswa', 'Kedisiplinan', 'Kelakuan', 'Kerapian']; }
                public function title(): string { return 'Kepribadian'; }
            },
            new class implements FromCollection, WithHeadings, WithTitle {
                public function collection() { return collect([['12345', 'Budi Santoso', 'Pramuka', 'A', 'Sangat Aktif', 'PMR', 'B', 'Aktif', '', '', '']]); }
                public function headings(): array { return ['NIS', 'Nama Siswa', 'Ekskul 1', 'Nilai 1', 'Keterangan 1', 'Ekskul 2', 'Nilai 2', 'Keterangan 2', 'Ekskul 3', 'Nilai 3', 'Keterangan 3']; }
                public function title(): string { return 'Ekstrakurikuler'; }
            }
        ];
    }
}
