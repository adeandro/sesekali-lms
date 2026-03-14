<?php

namespace App\Imports;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Imports a re-mapping spreadsheet.
 *
 * Admin ONLY needs to fill the "Class Group" column.
 * The system derives class_id automatically from grade_level + class_group.
 *
 * ALL rows are validated first; if ANY row fails → full rollback.
 */
class RemappingImport implements ToCollection, WithHeadingRow
{
    public int   $successCount = 0;
    public array $errors       = [];
    public float $duration     = 0;

    private float $startTime;

    public function collection(Collection $collection): void
    {
        $this->startTime = microtime(true);

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        // ── 1. Pre-load lookup maps ─────────────────────────────────────────
        /** @var \Illuminate\Database\Eloquent\Collection<int, User> keyed by user.id */
        $students = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->select('id', 'grade_level', 'name', 'class_group', 'class_id')
            ->get()
            ->keyBy('id');

        /**
         * Build classes map: "{grade}-{section}" => ClassRoom
         * e.g. "11-A" => ClassRoom(id=5, name="XI-A", grade="11", section="A")
         */
        $classMap = ClassRoom::active()
            ->select('id', 'name', 'grade', 'section')
            ->get()
            ->keyBy(fn(ClassRoom $c) => $c->grade . '-' . $c->section);

        $updates   = [];
        $rowNumber = 2;

        // ── 2. Validate ALL rows first ──────────────────────────────────────
        foreach ($collection as $row) {
            $studentId  = (int) ($row['student_id'] ?? 0);
            // Support both heading variants: "class_group_isi_ini" or "class_group"
            $classGroup = trim((string) ($row['class_group_isi_ini'] ?? $row['class_group'] ?? ''));

            // Skip completely blank rows
            if (!$studentId && !$classGroup) {
                $rowNumber++;
                continue;
            }

            // Validation 1: student_id must exist and be active
            if (!isset($students[$studentId])) {
                $this->errors[] = [
                    'row'   => $rowNumber,
                    'error' => "Baris {$rowNumber}: student_id [{$studentId}] tidak ditemukan atau tidak aktif.",
                ];
                $rowNumber++;
                continue;
            }

            $student = $students[$studentId];

            // Validation 2: class_group must not be empty
            if (empty($classGroup)) {
                $this->errors[] = [
                    'row'   => $rowNumber,
                    'error' => "Baris {$rowNumber} [{$student->name}]: Kolom Class Group tidak boleh kosong.",
                ];
                $rowNumber++;
                continue;
            }

            // Validation 3: lookup class_id from grade_level + class_group
            $lookupKey = $student->grade_level . '-' . $classGroup;
            if (!isset($classMap[$lookupKey])) {
                $this->errors[] = [
                    'row'   => $rowNumber,
                    'error' => "Baris {$rowNumber} [{$student->name}]: Kombinasi Grade {$student->grade_level} + Class Group \"{$classGroup}\" tidak cocok dengan kelas manapun. Lookup key: [{$lookupKey}]. Pastikan tabel 'classes' memiliki entri ini.",
                ];
                $rowNumber++;
                continue;
            }

            $classroom = $classMap[$lookupKey];

            $updates[$studentId] = [
                'class_group' => $classGroup,
                'class_id'    => $classroom->id,
            ];
            $rowNumber++;
        }

        // ── 3. If ANY error → abort entirely (no DB changes) ───────────────
        if (!empty($this->errors)) {
            $this->duration = round(microtime(true) - $this->startTime, 2);
            return;
        }

        // ── 4. Apply updates in a single transaction ────────────────────────
        DB::transaction(function () use ($updates) {
            foreach (array_chunk($updates, 200, true) as $chunk) {
                foreach ($chunk as $studentId => $data) {
                    User::where('id', $studentId)->update([
                        'class_group' => $data['class_group'],
                        'class_id'    => $data['class_id'],
                    ]);
                    $this->successCount++;
                }
            }
        });

        $this->duration = round(microtime(true) - $this->startTime, 2);
    }
}
