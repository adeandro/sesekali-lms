<?php

namespace App\Imports;

use App\Models\User;
use App\Services\StudentService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $failureCount = 0;
    public $errors = [];
    public $students = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        $rowNumber = 2; // Start from 2 because of header

        foreach ($collection as $row) {
            try {
                // Prepare data
                $classData = $row['class'] ?? $row['kelas'] ?? null;

                // Handle class format: if "10A" format, split it
                $grade = null;
                $classGroup = null;
                if ($classData) {
                    if (strlen($classData) > 1) {
                        // Format like "10A" or "11B"
                        $grade = substr($classData, 0, -1);
                        $classGroup = substr($classData, -1);
                    } else {
                        // Just the letter, grade might be in separate column
                        $classGroup = $classData;
                        $grade = $row['grade'] ?? null;
                    }
                }

                $data = [
                    'nis' => $row['nis'] ?? null,
                    'name' => $row['full_name'] ?? $row['name'] ?? null,
                    'grade' => $grade,
                    'class_group' => $classGroup,
                    'email' => $row['email'] ?? null,
                ];

                // Validate
                $validation = StudentService::validateStudentData($data);

                if (!$validation['valid']) {
                    $this->failureCount++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validation['errors'],
                    ];
                    $rowNumber++;
                    continue;
                }

                // Check for duplicates
                if (User::where('nis', $data['nis'])->exists()) {
                    $this->failureCount++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['nis' => 'NIS already exists in database'],
                    ];
                    $rowNumber++;
                    continue;
                }

                // Create student
                $result = StudentService::createStudent($data);
                $this->successCount++;
                $this->students[] = [
                    'student' => $result['student'],
                    'password' => $result['password'],
                ];
            } catch (\Exception $e) {
                $this->failureCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => ['general' => $e->getMessage()],
                ];
            }

            $rowNumber++;
        }
    }
}
