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
        
        // Efficient Lookup: Load existing NIS and Emails
        $existingStudents = User::where('role', 'student')->select('id', 'nis', 'email')->get();
        $existingNisMap = $existingStudents->pluck('id', 'nis')->filter()->toArray();
        $existingEmailMap = $existingStudents->pluck('id', 'email')->filter()->toArray();

        // Class Lookup: Load all classes for mapping
        $classes = \App\Models\ClassRoom::all();
        $classLookup = [];
        foreach ($classes as $class) {
            $key = trim($class->grade) . '|' . trim($class->section);
            $classLookup[$key] = $class->id;
        }
        
        // Track NISSes and Emails within THIS import to handle duplicates in the file
        $fileNisses = [];
        $fileEmails = [];

        foreach ($collection as $row) {
            /** @var array $row */
            $row = $row instanceof Collection ? $row->toArray() : (array) $row;
            
            try {
                $nisValue = $row['nis'] ?? '';
                $nisString = trim((string) $nisValue);

                $data = [
                    'nis'            => $nisString,
                    'name'           => trim($row['nama'] ?? $row['full_name'] ?? $row['name'] ?? ''),
                    'grade'          => $row['grade_saat_ini'] ?? $row['grade'] ?? null,
                    'class_group'    => $row['class_group'] ?? $row['class group'] ?? null,
                    'photo'          => $row['foto'] ?? $row['photo'] ?? null,
                    'nisn'           => isset($row['nisn']) ? trim((string)$row['nisn']) : null,
                    'gender'         => $row['jenis_kelamin'] ?? $row['gender'] ?? null,
                    'place_of_birth' => $row['tempat_lahir'] ?? $row['place_of_birth'] ?? null,
                    'date_of_birth'  => $row['tanggal_lahir'] ?? $row['date_of_birth'] ?? null,
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

                // Check for existing records to decide Create vs Update
                $existingId = $existingNisMap[$data['nis']] ?? $existingEmailMap[$email] ?? null;

                // Mark as seen in this file
                $fileNisses[$data['nis']] = true;
                $fileEmails[$email] = true;

                // Class Matching: Find class_id from grade and section
                $classKey = trim($data['grade']) . '|' . trim($data['class_group']);
                $classId = $classLookup[$classKey] ?? null;

                if ($existingId) {
                    // UPDATE path
                    $user = User::find($existingId);
                    $user->update([
                        'name'           => $data['name'],
                        'grade'          => $data['grade'],
                        'class_group'    => $data['class_group'],
                        'class_id'       => $classId,
                        'photo'          => $data['photo'] ?? $user->photo,
                        'nisn'           => $data['nisn'] ?? $user->nisn,
                        'gender'         => $data['gender'] ?? $user->gender,
                        'place_of_birth' => $data['place_of_birth'] ?? $user->place_of_birth,
                        'date_of_birth'  => !empty($data['date_of_birth']) 
                                            ? $data['date_of_birth'] : $user->date_of_birth,
                    ]);

                    // Tambahkan ke daftar untuk ditampilkan di UI
                    $this->students[] = [
                        'student' => $user,
                        'is_update' => true
                    ];

                    $this->successCount++;
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
                    'name'           => $data['name'],
                    'email'          => $email,
                    'password'       => $hashedPassword,
                    'password_display' => $defaultPassword,
                    'nis'            => $data['nis'],
                    'nisn'           => $data['nisn'] ?? null,
                    'gender'         => $data['gender'] ?? null,
                    'place_of_birth' => $data['place_of_birth'] ?? null,
                    'date_of_birth'  => !empty($data['date_of_birth']) 
                                        ? $data['date_of_birth'] : null,
                    'grade'          => $data['grade'],
                    'class_group'    => $data['class_group'],
                    'class_id'       => $classId,
                    'photo'          => $data['photo'],
                    'role'           => 'student',
                    'status'         => 'Aktif',
                    'created_at'     => now(),
                    'updated_at'     => now(),
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
