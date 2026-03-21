<?php

namespace App\Console\Commands;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Sync siswa lama (pakai grade + class_group) ke tabel classes.
 *
 * Langkah:
 *  1. Buat ClassRoom baru untuk setiap kombinasi (grade, class_group) yang belum ada.
 *  2. Assign class_id ke setiap siswa berdasarkan (grade, class_group).
 */
class SyncStudentsToClasses extends Command
{
    protected $signature   = 'raport:sync-classes
                              {--academic-year= : Tahun ajaran untuk ClassRoom baru (default: dari config)}
                              {--dry-run        : Simulasi tanpa menyimpan}';

    protected $description = 'Sinkronisasi siswa lama (grade+class_group) → tabel classes dan set class_id';

    public function handle(): int
    {
        $dryRun      = $this->option('dry-run');
        $academicYear = $this->option('academic-year')
            ?? \App\Models\Setting::where('key','academic_year')->value('value')
            ?? '2023/2024';

        $this->info('🔄  Sync StudentS → Classes');
        $this->info('    Tahun ajaran : ' . $academicYear);
        $this->info('    Mode         : ' . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->newLine();

        // Ambil semua kombinasi grade+class_group dari siswa Aktif
        $combos = User::where('role', 'student')
            ->whereNotNull('grade')
            ->whereNotNull('class_group')
            ->selectRaw('grade, class_group, COUNT(*) as total')
            ->groupBy('grade', 'class_group')
            ->orderBy('grade')
            ->orderBy('class_group')
            ->get();

        if ($combos->isEmpty()) {
            $this->warn('Tidak ada siswa dengan grade+class_group yang terisi.');
            return self::SUCCESS;
        }

        $headers = ['Grade', 'Group', 'Jml Siswa', 'Nama Kelas', 'Status'];
        $rows    = [];
        $created = 0;
        $synced  = 0;

        foreach ($combos as $combo) {
            $grade      = (int) $combo->grade;
            $group      = $combo->class_group;
            $className  = 'X' . str_repeat('I', $grade - 10) . ' ' . $group; // X A, XI B, XII C

            // Mapping jenjang SMA/SMK: 10=X, 11=XI, 12=XII
            $nameMap = [10 => 'X', 11 => 'XI', 12 => 'XII'];
            $className = ($nameMap[$grade] ?? $grade) . ' ' . $group;

            // Cari atau buat ClassRoom
            $classroom = ClassRoom::where('grade', $grade)
                ->where('section', $group)
                ->first();

            if (!$classroom) {
                $status = 'BUAT BARU';
                if (!$dryRun) {
                    $classroom = ClassRoom::create([
                        'name'          => $className,
                        'grade'         => $grade,
                        'section'       => $group,
                        'academic_year' => $academicYear,
                        'is_active'     => true,
                    ]);
                    $created++;
                }
            } else {
                $status    = 'SUDAH ADA (ID:' . $classroom->id . ')';
                $className = $classroom->name;
            }

            // Assign class_id ke siswa
            if (!$dryRun && $classroom) {
                $affected = User::where('role', 'student')
                    ->where('grade', $combo->grade)
                    ->where('class_group', $group)
                    ->update(['class_id' => $classroom->id]);
                $synced += $affected;
            }

            $rows[] = [$grade, $group, $combo->total, $className, $status];
        }

        $this->table($headers, $rows);
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN — tidak ada yang disimpan. Jalankan tanpa --dry-run untuk apply.');
        } else {
            $this->info("✅  ClassRoom baru dibuat : $created");
            $this->info("✅  Siswa di-assign class_id : $synced");
        }

        return self::SUCCESS;
    }
}
