<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::where('role', 'student')
            ->select('nis', 'name', 'grade', 'class_group', 'is_active')
            ->orderBy('nis')
            ->get()
            ->map(function ($student) {
                return [
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'grade' => $student->grade,
                    'class_group' => $student->class_group,
                    'status' => $student->is_active ? 'Active' : 'Inactive',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Name',
            'Grade',
            'Class Group',
            'Status',
        ];
    }
}
