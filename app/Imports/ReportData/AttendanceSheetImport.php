<?php

namespace App\Imports\ReportData;

use App\Models\User;
use App\Models\StudentAttendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceSheetImport implements ToCollection, WithHeadingRow
{
    public $academicYear;
    public $semester;

    public function __construct($academicYear, $semester)
    {
        $this->academicYear = $academicYear;
        $this->semester = $semester;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!isset($row['nis'])) continue;

            $student = User::where('nis', $row['nis'])->where('role', 'student')->first();
            if (!$student) continue;

            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year' => $this->academicYear,
                    'semester' => $this->semester,
                ],
                [
                    'sick_days' => $row['sakit'] ?? 0,
                    'permit_days' => $row['izin'] ?? 0,
                    'alpha_days' => $row['alpha'] ?? 0,
                ]
            );
        }
    }
}
