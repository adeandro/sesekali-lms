<?php

namespace App\Imports;

use App\Models\User;
use App\Models\StudentDudi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentDudiImport implements ToCollection, WithHeadingRow
{
    private $classId;
    private $semester;
    private $academicYear;
    private $teacherId;

    public function __construct(int $classId, int $semester, string $academicYear, int $teacherId)
    {
        $this->classId = $classId;
        $this->semester = $semester;
        $this->academicYear = $academicYear;
        $this->teacherId = $teacherId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Heading: nis | nama_siswa | kegiatan | nama_instansi | alamat_instansi | waktu_pelaksanaan | nilai
            $activity = $row['kegiatan'] ?? null;
            if (empty($activity)) continue;

            $nis = $row['nis'] ?? null;
            $name = $row['nama_siswa'] ?? null;

            $student = null;
            if ($nis) {
                $student = User::where('role', '=', 'student')->where('nis', '=', $nis)->first();
            }

            if (!$student && $name) {
                $student = User::where('role', '=', 'student')->where('name', '=', $name)->first();
            }

            if (!$student) continue;

            StudentDudi::create([
                'student_id' => $student->id,
                'class_id' => $this->classId,
                'teacher_id' => $this->teacherId,
                'semester' => $this->semester,
                'academic_year' => $this->academicYear,
                'activity_name' => $activity,
                'institution_name' => $row['nama_instansi'] ?? '-',
                'institution_address' => $row['alamat_instansi'] ?? null,
                'period' => $row['waktu_pelaksanaan'] ?? '-',
                'grade' => $row['nilai'] ?? null,
                'sort_order' => 0, // Default for import
            ]);
        }
    }
}
