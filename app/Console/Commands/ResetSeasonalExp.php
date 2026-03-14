<?php

namespace App\Console\Commands;

use App\Models\BattleParticipant;
use App\Models\HistoricalWinner;
use App\Models\Season;
use App\Models\User;
use App\Notifications\GamificationNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetSeasonalExp extends Command
{
    protected $signature = 'season:reset {season_id? : ID season yang akan direset (default: season aktif)}
                                        {--dry-run : Preview tanpa menyimpan perubahan}';

    protected $description = 'Reset seasonal_exp semua siswa dan buat snapshot Hall of Fame untuk musim yang berakhir.';

    public function handle(): int
    {
        $seasonId = $this->argument('season_id');

        $season = $seasonId
            ? Season::find($seasonId)
            : Season::active()->latest('end_date')->first();

        if (!$season) {
            $this->error('Season tidak ditemukan atau tidak ada season aktif.');
            return self::FAILURE;
        }

        if ($season->reset_done) {
            $this->warn("Season [{$season->name}] sudah di-reset sebelumnya pada {$season->reset_executed_at}.");
            if (!$this->confirm('Jalankan lagi?')) {
                return self::SUCCESS;
            }
        }

        $isDryRun = $this->option('dry-run');
        $this->info($isDryRun ? '🔍 DRY RUN mode aktif...' : "🔄 Reset Seasonal EXP untuk: [{$season->name}]");

        $students = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->select('id', 'name', 'seasonal_exp', 'career_exp', 'active_theme_id')
            ->get();

        $this->info("  Total siswa aktif: {$students->count()}");

        // ── 1. Snapshot top performers into historical_winners ──────────────
        $topStudents = $students->sortByDesc('seasonal_exp')->take(50);

        $bar = $this->output->createProgressBar($topStudents->count());
        $bar->start();

        DB::transaction(function () use ($season, $topStudents, $bar, $isDryRun) {
            $rank = 1;
            foreach ($topStudents as $student) {
                if (!$isDryRun) {
                    HistoricalWinner::create([
                        'season_id'              => $season->id,
                        'battle_room_id'         => null,
                        'user_id'                => $student->id,
                        'rank'                   => $rank,
                        'battle_room_name'       => 'Season Leaderboard — ' . $season->semesterLabel(),
                        'battle_mode'            => 'season',
                        'career_exp_snapshot'    => $student->career_exp,
                        'seasonal_exp_snapshot'  => $student->seasonal_exp,
                        'theme_awarded'          => $student->active_theme_id,
                        'archived_at'            => now(),
                    ]);
                }
                $rank++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // ── 2. Reset seasonal_exp ────────────────────────────────────────────
        if (!$isDryRun) {
            User::where('role', 'student')->update(['seasonal_exp' => 0]);

            $season->update([
                'reset_done'         => true,
                'reset_executed_at'  => now(),
            ]);
        }

        $this->info($isDryRun
            ? '  ✅ [DRY RUN] Selesai — seasonal_exp TIDAK direset.'
            : '  ✅ Snapshot tersimpan. seasonal_exp semua siswa = 0.'
        );

        return self::SUCCESS;
    }
}
