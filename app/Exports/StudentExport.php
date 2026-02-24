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
            ->select('nis', 'name', 'email', 'grade', 'class_group', 'is_active')
            ->orderBy('nis')
            ->get()
            ->map(function ($student) {
                $gradeDisplay = "Grade {$student->grade} - {$student->class_group}";
                return [
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'class' => $gradeDisplay,
                    'email' => $student->email ?? 'N/A',
                    'status' => $student->is_active ? 'Active' : 'Inactive',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Name',
            'Class',
            'Email',
            'Status',
        ];
    }
}
