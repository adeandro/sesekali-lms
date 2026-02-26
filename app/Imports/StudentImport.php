<?php

namespace App\Imports;

use App\Models\User;
use App\Services\StudentService;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB;

class StudentImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $skippedCount = 0;
    public $failureCount = 0;
    public $errors = [];
    public $students = [];
    public $skipped = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        // Increase execution time for large imports
        set_time_limit(300); // 5 minutes for imports
        
        $rowNumber = 2; // Start from 2 because of header
        $batch = [];
        $batchSize = 50; // Process in batches of 50
        
        // Pre-fetch all existing NISSes to avoid N+1 queries
        // Filter out NULL values to prevent array_flip errors
        $existingNisses = User::whereNotNull('nis')->pluck('nis')->flip();

        foreach ($collection as $row) {
            try {
                // Prepare data - expects separate columns for grade and class_group
                $data = [
                    'nis' => $row['nis'] ?? null,
                    'name' => $row['full_name'] ?? $row['name'] ?? null,
                    'grade' => $row['grade'] ?? null,
                    'class_group' => $row['class_group'] ?? $row['class group'] ?? null,
                ];

                // Check for duplicates using cached list instead of query
                if (!empty($data['nis']) && isset($existingNisses[$data['nis']])) {
                    $this->skippedCount++;
                    $this->skipped[] = [
                        'row' => $rowNumber,
                        'nis' => $data['nis'],
                        'name' => $data['name'] ?? 'N/A',
                        'grade' => $data['grade'] ?? 'N/A',
                        'class_group' => $data['class_group'] ?? 'N/A',
                        'reason' => 'Student with this NIS already exists',
                    ];
                    $rowNumber++;
                    continue;
                }

                // Validate only if not a duplicate
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

                // Prepare batch data (without creating yet)
                $batch[] = [
                    'rowNumber' => $rowNumber,
                    'data' => $data,
                ];

                // Process batch when it reaches batch size
                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch);
                    $batch = [];
                }
            } catch (\Exception $e) {
                $this->failureCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => ['general' => $e->getMessage()],
                ];
            }

            $rowNumber++;
        }

        // Process remaining batch
        if (!empty($batch)) {
            $this->processBatch($batch);
        }
    }

    /**
     * Process a batch of students
     */
    private function processBatch(array $batch): void
    {
        DB::beginTransaction();
        try {
            foreach ($batch as $item) {
                $result = StudentService::createStudent($item['data']);
                $this->successCount++;
                $this->students[] = [
                    'student' => $result['student'],
                    'password' => $result['password'],
                ];
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($batch as $item) {
                $this->failureCount++;
                $this->errors[] = [
                    'row' => $item['rowNumber'],
                    'errors' => ['general' => $e->getMessage()],
                ];
            }
        }
    }
}
