<?php

namespace App\Imports\ReportData;

use App\Models\User;
use App\Models\StudentPersonality;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PersonalitySheetImport implements ToCollection, WithHeadingRow
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

            StudentPersonality::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year' => $this->academicYear,
                    'semester' => $this->semester,
                ],
                [
                    'discipline' => isset($row['kedisiplinan']) ? strtolower($row['kedisiplinan']) : 'baik',
                    'behavior' => isset($row['kelakuan']) ? strtolower($row['kelakuan']) : 'baik',
                    'neatness' => isset($row['kerapian']) ? strtolower($row['kerapian']) : 'baik',
                ]
            );
        }
    }
}
