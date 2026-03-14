<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * @param string $scope 'all' = full export, 'unmapped' = no class_group only (for re-mapping template)
     */
    public function __construct(protected string $scope = 'all')
    {
    }

    public function collection(): Collection
    {
        $query = User::where('role', 'student')
            ->where('status', '!=', 'Alumni')
            ->with('classroom')
            ->orderBy('grade')
            ->orderBy('class_group')
            ->orderBy('nis');

        if ($this->scope === 'unmapped') {
            $query->where('status', 'Aktif')->whereNull('class_group');
        }

        return $query->get()->map(function (User $student) {
            return [
                'student_id'    => $student->id,
                'nis'           => $student->nis,
                'name'          => $student->name,
                'current_grade' => $student->grade,
                'class_group'   => $student->class_group ?? '',
                'class_name'    => $student->classroom?->name ?? (
                    $student->grade && $student->class_group
                        ? $student->grade . '-' . $student->class_group
                        : '— Belum Dipetakan —'
                ),
                'status'        => $student->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'student_id',
            'NIS',
            'Nama',
            'Grade Saat Ini',
            'Class Group',
            'Nama Kelas',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4F46E5']]],
        ];
    }
}
