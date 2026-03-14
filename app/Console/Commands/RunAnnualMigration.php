<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunAnnualMigration extends Command
{
    protected $signature = 'season:migrate
                            {--stay-behind=* : User IDs yang TIDAK dinaikkan (tetap di grade saat ini)}
                            {--dry-run : Preview tanpa menyimpan perubahan}
                            {--academic-year= : Tahun ajaran alumni, e.g. 2025/2026}';

    protected $description = 'Jalankan migrasi tahunan: Kelas 12 → Alumni, Kelas 10-11 → naik grade.';

    public function handle(): int
    {
        $isDryRun    = $this->option('dry-run');
        $stayBehind  = (array) $this->option('stay-behind');
        $alumniYear  = $this->option('academic-year') ?? now()->year . '/' . (now()->year + 1);

        $this->info($isDryRun ? '🔍 DRY RUN: Annual Migration' : '🎓 Menjalankan Annual Migration...');

        // ── 1. Graduate: Kelas 12 → Alumni ──────────────────────────────────
        $grade12 = User::where('role', 'student')
            ->where(function ($q) {
                $q->where('grade_level', 12)
                  ->orWhere('grade', '12');
            })
            ->whereNotIn('id', $stayBehind)
            ->get();

        $this->info("\n📋 Siswa Kelas 12 yang akan diwisuda: {$grade12->count()}");

        if (!$isDryRun && $grade12->isNotEmpty()) {
            DB::transaction(function () use ($grade12, $alumniYear) {
                foreach ($grade12 as $student) {
                    $student->update([
                        'status'      => 'Alumni',
                        'alumni_year' => $alumniYear,
                    ]);
                }
            });
            $this->info("  ✅ {$grade12->count()} siswa diarsipkan sebagai Alumni {$alumniYear}.");
        } else {
            $grade12->each(fn($s) => $this->line("  [DRY] Would archive: {$s->name} (Grade {$s->grade_level})"));
        }

        // ── 2. Promote: Kelas 10 & 11 → grade + 1 ───────────────────────────
        $toPromote = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->whereIn('grade_level', [10, 11])
            ->whereNotIn('id', $stayBehind)
            ->get();

        $this->info("\n📈 Siswa yang akan dinaikkan: {$toPromote->count()}");

        if (!$isDryRun && $toPromote->isNotEmpty()) {
            DB::transaction(function () use ($toPromote) {
                foreach ($toPromote as $student) {
                    $newGrade = $student->grade_level + 1;
                    $student->update([
                        'grade_level' => $newGrade,
                        'grade'       => (string) $newGrade,
                    ]);
                }
            });
            $this->info("  ✅ {$toPromote->count()} siswa dinaikkan satu tingkat.");
        } else {
            $toPromote->each(fn($s) => $this->line("  [DRY] Would promote: {$s->name} Grade {$s->grade_level} → " . ($s->grade_level + 1)));
        }

        // ── 3. Stay-Behind Summary ────────────────────────────────────────────
        if (!empty($stayBehind)) {
            $stayCount = User::whereIn('id', $stayBehind)->count();
            $this->info("\n⛔ Ditahan (stay-behind): {$stayCount} siswa — grade tidak berubah.");
        }

        $this->info($isDryRun
            ? "\n✅ [DRY RUN] Selesai — tidak ada data yang berubah."
            : "\n✅ Annual Migration selesai!"
        );

        return self::SUCCESS;
    }
}
