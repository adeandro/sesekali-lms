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
    private $seenNisses = [];   // Track NISSes within this import
    private $seenEmails = [];   // Track emails within this import

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        // Increase execution time for large imports
        set_time_limit(300); // 5 minutes for imports
        
        $rowNumber = 2; // Start from 2 because of header
        $batch = [];
        $batchSize = 10; // Smaller batch size for hosted MySQL to avoid lock waits
        
        foreach ($collection as $row) {
            try {
                // Prepare data - expects separate columns for grade and class_group
                // IMPORTANT: Cast NIS to string to prevent integer overflow from Excel
                // Excel reads large numbers as integers which can overflow, converting them to negative
                $nisValue = $row['nis'] ?? '';
                $nisString = trim((string) $nisValue); // Explicit string conversion
                
                $data = [
                    'nis' => $nisString,
                    'name' => trim($row['full_name'] ?? $row['name'] ?? ''),
                    'grade' => $row['grade'] ?? null,
                    'class_group' => $row['class_group'] ?? $row['class group'] ?? null,
                ];

                // Skip empty rows
                if (empty($data['nis']) || empty($data['name'])) {
                    $rowNumber++;
                    continue;
                }

                // Generate email early to check for duplicates
                $email = 'student_' . $data['nis'] . '@sesekalicbt.local';

                // Check for duplicates within current import
                if (isset($this->seenNisses[$data['nis']])) {
                    $this->skippedCount++;
                    $this->skipped[] = [
                        'row' => $rowNumber,
                        'nis' => $data['nis'],
                        'name' => $data['name'],
                        'grade' => $data['grade'] ?? 'N/A',
                        'class_group' => $data['class_group'] ?? 'N/A',
                        'reason' => 'Duplicate NIS in import (previously seen in row ' . $this->seenNisses[$data['nis']] . ')',
                    ];
                    $rowNumber++;
                    continue;
                }

                if (isset($this->seenEmails[$email])) {
                    $this->skippedCount++;
                    $this->skipped[] = [
                        'row' => $rowNumber,
                        'nis' => $data['nis'],
                        'name' => $data['name'],
                        'grade' => $data['grade'] ?? 'N/A',
                        'class_group' => $data['class_group'] ?? 'N/A',
                        'reason' => 'Duplicate email in import (previously seen in row ' . $this->seenEmails[$email] . ')',
                    ];
                    $rowNumber++;
                    continue;
                }

                // Mark as seen
                $this->seenNisses[$data['nis']] = $rowNumber;
                $this->seenEmails[$email] = $rowNumber;

                // Validate data
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

                // Prepare batch data
                $batch[] = [
                    'rowNumber' => $rowNumber,
                    'data' => $data,
                ];

                // Process batch when it reaches batch size to avoid lock waits
                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch);
                    $batch = [];
                    // Add small delay to reduce database pressure
                    usleep(100000); // 0.1 second delay
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
     * Process a batch of students with individual error handling
     * Each student is processed in its own transaction to avoid locking entire batch
     */
    private function processBatch(array $batch): void
    {
        foreach ($batch as $item) {
            try {
                // Use updateOrCreate for idempotency - handles re-imports gracefully
                $result = StudentService::createOrUpdateStudent($item['data']);
                $this->successCount++;
                $this->students[] = [
                    'student' => $result['student'],
                    'password' => $result['password'],
                ];
            } catch (\Exception $e) {
                $this->failureCount++;
                $error = $e->getMessage();
                
                // Provide more meaningful error messages
                if (str_contains($error, 'Duplicate entry') && str_contains($error, '_email_')) {
                    $error = 'Email already exists in database';
                } elseif (str_contains($error, 'Duplicate entry') && str_contains($error, '_nis_')) {
                    $error = 'NIS already exists in database';
                } elseif (str_contains($error, 'Lock wait timeout')) {
                    $error = 'Database is locked - try again in a moment';
                }
                
                $this->errors[] = [
                    'row' => $item['rowNumber'],
                    'errors' => ['general' => $error],
                ];
            }
        }
    }
}
