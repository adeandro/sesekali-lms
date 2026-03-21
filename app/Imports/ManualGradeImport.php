<?php

namespace App\Imports;

use App\Models\ManualGrade;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ManualGradeImport implements ToCollection
{
    public int   $successCount = 0;
    public int   $skippedCount = 0;
    public array $errors       = [];

    public function __construct(
        private readonly int    $subjectId,
        private readonly int    $semester,
        private readonly string $academicYear,
        private readonly int    $teacherId,
        private readonly int    $jenjang,
    ) {}

    /**
     * Process each row from the Excel file.
     *
     * Expected columns (without heading row):
     *  Col 1: NIS
     *  Col 2: Nama Siswa (ignored, NIS is authoritative)
     *  Col 3: Nilai Harian
     *  Col 4: Nilai UTS
     *  Col 5: Nilai UAS
     */
    public function collection(Collection $rows): void
    {
        set_time_limit(300);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;

            // Skip header rows (first 5 rows based on template)
            if ($rowNumber <= 5) {
                continue;
            }

            // Also skip header column labels row (row 6)
            if ($rowNumber === 6) {
                continue;
            }

            // Skip empty rows
            $nis = trim((string) ($row[0] ?? ''));
            if (empty($nis)) {
                continue;
            }

            // Find student by NIS
            $student = User::where('role', 'student')
                ->where('nis', $nis)
                ->first();

            if (!$student) {
                $this->skippedCount++;
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'reason' => "NIS \"$nis\" tidak ditemukan.",
                ];
                continue;
            }

            // Process each grade type
            $typeMap = [
                'harian' => $row[2] ?? null,
                'uts'    => $row[3] ?? null,
                'uas'    => $row[4] ?? null,
            ];

            $rowSaved = false;

            foreach ($typeMap as $type => $rawScore) {
                $scoreStr = trim((string) ($rawScore ?? ''));
                if ($scoreStr === '') {
                    continue; // empty = skip, not 0
                }

                if (!is_numeric($scoreStr)) {
                    $this->errors[] = [
                        'row'    => $rowNumber,
                        'reason' => "NIS $nis — kolom " . strtoupper($type) . " bukan angka: \"$scoreStr\"",
                    ];
                    continue;
                }

                $score = (float) $scoreStr;

                if ($score < 0 || $score > 100) {
                    $this->errors[] = [
                        'row'    => $rowNumber,
                        'reason' => "NIS $nis — " . strtoupper($type) . " harus 0-100, diterima: $score",
                    ];
                    continue;
                }

                ManualGrade::updateOrCreate(
                    [
                        'student_id'    => $student->id,
                        'subject_id'    => $this->subjectId,
                        'grade_type'    => $type,
                        'semester'      => $this->semester,
                        'academic_year' => $this->academicYear,
                    ],
                    [
                        'score'      => $score,
                        'teacher_id' => $this->teacherId,
                        'jenjang'    => $this->jenjang,
                    ]
                );

                $rowSaved = true;
            }

            if ($rowSaved) {
                $this->successCount++;
            } else {
                $this->skippedCount++;
            }
        }
    }
}
