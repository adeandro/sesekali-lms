<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentDudiTemplateExport implements FromArray, WithHeadings, WithStyles
{
    protected $classId;

    public function __construct($classId = null)
    {
        $this->classId = $classId;
    }

    public function array(): array
    {
        if ($this->classId) {
            $students = \App\Models\User::where('class_id', '=', $this->classId)
                ->where('role', '=', 'student')
                ->orderBy('name', 'asc')
                ->get();

            if ($students->isNotEmpty()) {
                $data = [];
                foreach ($students as $student) {
                    $data[] = [
                        $student->nis ?? '',
                        $student->name,
                        '', // Kegiatan
                        '', // Nama Instansi
                        '', // Alamat Instansi
                        '', // Waktu Pelaksanaan
                        '', // Nilai
                    ];
                }
                return $data;
            }
        }

        // Fallback or Example data if no class/students
        return [
            [
                '2223001', 
                'Andi Pratama', 
                'Prakerin Jaringan Dasar', 
                'PT Telkom Indonesia', 
                'Jl. Gatot Subroto No.10, Jakarta', 
                'Januari - Maret 2025', 
                'A'
            ],
            [
                '2223002', 
                'Budi Santoso', 
                'Maintenance Hardware', 
                'CV Multi Komputer', 
                'Jl. Sudirman No.5, Malang', 
                'Februari 2025', 
                'B'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Siswa',
            'Kegiatan',
            'Nama Instansi',
            'Alamat Instansi',
            'Waktu Pelaksanaan',
            'Nilai',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
