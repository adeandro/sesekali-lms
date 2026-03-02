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
    public $skipped = [];
    public $students = []; // Untuk menampilkan hasil di UI
    public $duration = 0;
    
    private $startTime;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        $this->startTime = microtime(true);
        
        // Safety Nest: Increase execution time and memory for large imports
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $rowNumber = 2; // Start from 2 because of header
        $dataToInsert = [];
        
        // Pre-Hashing: Calculate hash once outside the loop
        $defaultPassword = 'password_default';
        $hashedPassword = \Illuminate\Support\Facades\Hash::make($defaultPassword);
        
        // Efficient Duplicate Check: Load existing NIS and Email into lookup tables
        $existingStudents = User::select('nis', 'email')->get();
        $existingNis = $existingStudents->pluck('nis')->filter()->flip()->toArray();
        $existingEmails = $existingStudents->pluck('email')->filter()->flip()->toArray();
        
        // Track NISSes and Emails within THIS import to handle duplicates in the file
        $fileNisses = [];
        $fileEmails = [];

        foreach ($collection as $row) {
            try {
                $nisValue = $row['nis'] ?? '';
                $nisString = trim((string) $nisValue);

                $data = [
                    'nis' => $nisString,
                    'name' => trim($row['full_name'] ?? $row['name'] ?? ''),
                    'grade' => $row['grade'] ?? null,
                    'class_group' => $row['class_group'] ?? $row['class group'] ?? null,
                    'photo' => $row['foto'] ?? $row['photo'] ?? null,
                ];

                // Skip empty rows
                if (empty($data['nis']) || empty($data['name'])) {
                    $rowNumber++;
                    continue;
                }

                $email = 'student_' . $data['nis'] . '@sesekalicbt.local';

                // Check for duplicates in the file
                if (isset($fileNisses[$data['nis']]) || isset($fileEmails[$email])) {
                    $this->skippedCount++;
                    $this->skipped[] = array_merge($data, [
                        'row' => $rowNumber,
                        'reason' => 'Duplikat dalam file',
                    ]);
                    $rowNumber++;
                    continue;
                }

                // Check for duplicates in the database (Efficient lookup)
                if (isset($existingNis[$data['nis']]) || isset($existingEmails[$email])) {
                    $this->skippedCount++;
                    $this->skipped[] = array_merge($data, [
                        'row' => $rowNumber,
                        'reason' => 'Sudah ada di database',
                    ]);
                    $rowNumber++;
                    continue;
                }

                // Basic validation
                if (empty($data['grade']) || empty($data['class_group'])) {
                    $this->failureCount++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['general' => 'Jenjang dan Kelompok Kelas wajib diisi'],
                    ];
                    $rowNumber++;
                    continue;
                }

                // Mark as seen in this file
                $fileNisses[$data['nis']] = true;
                $fileEmails[$email] = true;

                // Prepare for bulk insert
                $insertData = [
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => $hashedPassword,
                    'password_display' => $defaultPassword,
                    'nis' => $data['nis'],
                    'grade' => $data['grade'],
                    'class_group' => $data['class_group'],
                    'photo' => $data['photo'],
                    'role' => 'student',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $dataToInsert[] = $insertData;

                // Untuk tampilan di UI (menggunakan array biasa, bukan Model untuk speed)
                $this->students[] = [
                    'student' => (object)$insertData, 
                    'password' => $defaultPassword
                ];

                $this->successCount++;

            } catch (\Exception $e) {
                $this->failureCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => ['general' => $e->getMessage()],
                ];
            }

            $rowNumber++;
        }

        // Bulk Insert in chunks of 500
        if (!empty($dataToInsert)) {
            $chunks = array_chunk($dataToInsert, 500);
            foreach ($chunks as $chunk) {
                User::insert($chunk);
            }
        }
        
        $this->duration = round(microtime(true) - $this->startTime, 2);
    }
}
