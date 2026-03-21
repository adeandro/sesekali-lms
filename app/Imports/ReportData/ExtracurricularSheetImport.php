<?php

namespace App\Imports\ReportData;

use App\Models\User;
use App\Models\StudentExtracurricular;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExtracurricularSheetImport implements ToCollection, WithHeadingRow
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

            // Delete old extracurriculars
            StudentExtracurricular::where('student_id', $student->id)
                ->where('academic_year', $this->academicYear)
                ->where('semester', $this->semester)
                ->delete();

            // Check ekskul 1
            if (!empty($row['ekskul_1']) && !empty($row['nilai_1'])) {
                StudentExtracurricular::create([
                    'student_id' => $student->id,
                    'academic_year' => $this->academicYear,
                    'semester' => $this->semester,
                    'name' => $row['ekskul_1'],
                    'grade' => $row['nilai_1'],
                    'note' => $row['keterangan_1'] ?? null,
                ]);
            }

            // Check ekskul 2
            if (!empty($row['ekskul_2']) && !empty($row['nilai_2'])) {
                StudentExtracurricular::create([
                    'student_id' => $student->id,
                    'academic_year' => $this->academicYear,
                    'semester' => $this->semester,
                    'name' => $row['ekskul_2'],
                    'grade' => $row['nilai_2'],
                    'note' => $row['keterangan_2'] ?? null,
                ]);
            }
            
            // Check ekskul 3 (just in case they need up to 3)
            if (!empty($row['ekskul_3']) && !empty($row['nilai_3'])) {
                StudentExtracurricular::create([
                    'student_id' => $student->id,
                    'academic_year' => $this->academicYear,
                    'semester' => $this->semester,
                    'name' => $row['ekskul_3'],
                    'grade' => $row['nilai_3'],
                    'note' => $row['keterangan_3'] ?? null,
                ]);
            }
        }
    }
}
