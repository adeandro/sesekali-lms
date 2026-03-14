<?php

namespace App\Exports;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports ONLY students with class_group = NULL (unmapped after migration).
 * Includes a second sheet listing all valid classes for reference.
 */
class RemappingTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Data Siswa'   => new RemappingStudentsSheet(),
            'Daftar Kelas' => new RemappingClassesSheet(),
        ];
    }
}

// ── Sheet 1: Unmapped Students ───────────────────────────────────────────────

class RemappingStudentsSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection(): Collection
    {
        return User::where('role', 'student')
            ->where('status', 'Aktif')
            ->whereNull('class_group')
            ->orderBy('grade_level')
            ->orderBy('nis')
            ->get()
            ->map(fn(User $s) => [
                'student_id'    => $s->id,
                'nis'           => $s->nis,
                'name'          => $s->name,
                'current_grade' => $s->grade_level,
                'class_group'   => '',      // ← Admin mengisi kolom ini (contoh: A, B, IPA-1)
                'class_id'      => '',      // Akan diisi otomatis oleh sistem saat import
                'class_name'    => '',      // Info saja, tidak digunakan saat import
                'status'        => 'Aktif',
            ]);
    }

    public function headings(): array
    {
        return [
            'student_id',
            'NIS',
            'Nama',
            'Grade Saat Ini',
            'Class Group (ISI INI)',  // ← Admin hanya perlu mengisi kolom ini
            'class_id',
            'Nama Kelas',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4F46E5']],
            ],
        ];
        // Highlight the Class Group column (E) in yellow to guide admin
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle("E2:E{$lastRow}")
                  ->getFill()
                  ->setFillType('solid')
                  ->getStartColor()->setARGB('FFFFF176');
        }
        return $styles;
    }
}

// ── Sheet 2: Valid Classes Reference ────────────────────────────────────────

class RemappingClassesSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection(): Collection
    {
        return ClassRoom::active()
            ->orderBy('grade')
            ->orderBy('name')
            ->get()
            ->map(fn(ClassRoom $c) => [
                'class_id'    => $c->id,
                'class_name'  => $c->name,
                'grade'       => $c->grade,
                'section'     => $c->section ?? '-',
                'class_group' => $c->section ?? $c->name,  // What to enter in Class Group column
                'capacity'    => $c->capacity ?? 'Tidak Dibatasi',
            ]);
    }

    public function headings(): array
    {
        return ['class_id', 'Nama Kelas', 'Grade', 'Seksi/Jurusan', 'Nilai class_group', 'Kapasitas'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFFBBF24']],
            ],
        ];
    }
}
