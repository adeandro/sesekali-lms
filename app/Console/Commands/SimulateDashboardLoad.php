<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AchievementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SimulateDashboardLoad extends Command
{
    protected $signature = 'simulate:dashboard {--students=10 : Jumlah siswa yang disimulasikan} {--flush : Flush cache sebelum simulasi (cold start)}';
    protected $description = 'Simulasi load dashboard student untuk mengukur performa caching';

    public function handle(AchievementService $achievementService): int
    {
        $studentCount = (int) $this->option('students');
        $flush        = $this->option('flush');

        $this->newLine();
        $this->line("╔══════════════════════════════════════════════════════╗");
        $this->line("║        SIMULASI LOAD DASHBOARD — {$studentCount} SISWA           ║");
        $this->line("╚══════════════════════════════════════════════════════╝");
        $this->newLine();

        // Ambil sample siswa aktif
        $students = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->inRandomOrder()
            ->take($studentCount)
            ->get();

        if ($students->isEmpty()) {
            $this->error('Tidak ada siswa aktif ditemukan!');
            return 1;
        }

        if ($flush) {
            $this->warn("⚡ Flushing cache — simulasi COLD START (worst case)...");
            Cache::flush();
            $this->info("  Cache flushed.\n");
        } else {
            $this->info("ℹ️  Cache tidak di-flush — simulasi setelah warm-up (normal use).\n");
        }

        $results  = [];
        $totalMs  = 0;
        $cacheHit = 0;
        $cacheMiss = 0;

        $this->line(str_pad('No', 4) . str_pad('Student', 30) . str_pad('Time(ms)', 10) . str_pad('Stats', 10) . str_pad('Exams', 10) . 'Status');
        $this->line(str_repeat('─', 80));

        foreach ($students as $i => $user) {
            $start = microtime(true);

            // Cek cache sebelum request
            $statsKey = "student_stats_{$user->id}";
            $wasHit   = Cache::has($statsKey);

            // Simulasi hit dashboard controller logic
            Auth::setUser($user);
            $this->simulateDashboard($user, $achievementService);

            $elapsed = round((microtime(true) - $start) * 1000, 1);
            $totalMs += $elapsed;

            $status = $wasHit ? '✅ HIT' : '⚠️  MISS';
            if ($wasHit) $cacheHit++; else $cacheMiss++;

            $statsNow  = Cache::has("student_stats_{$user->id}") ? '✓' : '✗';
            $examsNow  = Cache::has("available_exams_{$user->id}_{$user->grade}") ? '✓' : '✗';

            $name = substr($user->name, 0, 28);
            $this->line(
                str_pad($i + 1, 4) .
                str_pad($name, 30) .
                str_pad($elapsed . 'ms', 10) .
                str_pad($statsNow, 10) .
                str_pad($examsNow, 10) .
                $status
            );

            $results[] = $elapsed;
        }

        $this->line(str_repeat('─', 80));
        $this->newLine();

        // Summary
        $avg = count($results) > 0 ? round(array_sum($results) / count($results), 1) : 0;
        $max = max($results);
        $min = min($results);

        $this->line("📊 <fg=cyan>RINGKASAN HASIL SIMULASI</>");
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Siswa Disimulasi',  count($students)],
                ['Total Waktu',       round($totalMs) . ' ms'],
                ['Rata-rata/Request', $avg . ' ms'],
                ['Request Tercepat',  $min . ' ms'],
                ['Request Terlambat', $max . ' ms'],
                ['Cache HIT',         $cacheHit . ' request'],
                ['Cache MISS',        $cacheMiss . ' request (hanya request pertama)'],
            ]
        );

        $this->newLine();

        // Verdict
        if ($max < 200) {
            $this->info("✅ EXCELLENT — Semua request < 200ms. Siap untuk 58 siswa.");
        } elseif ($max < 500) {
            $this->warn("⚠️  ACCEPTABLE — Ada request lambat. Pastikan Redis berjalan optimal.");
        } else {
            $this->error("🔴 SLOW — Ada request > 500ms. Cek koneksi Redis dan query DB.");
        }

        // Cek Redis session count
        $this->newLine();
        try {
            $redis    = app('redis');
            $sesKeys  = $redis->keys('*_session*');
            $cacheKeys = $redis->keys('laravel_cache:*');
            $this->line("🔴 Redis Session Keys : " . count($sesKeys));
            $this->line("🟢 Redis Cache Keys   : " . count($cacheKeys));
            $memInfo  = $redis->info('memory');
            $memUsed  = $memInfo['used_memory_human'] ?? ($memInfo['memory']['used_memory_human'] ?? 'N/A');
            $this->line("💾 Redis Memory Used  : {$memUsed}");
        } catch (\Throwable $e) {
            $this->warn("Tidak bisa cek Redis stats: " . $e->getMessage());
        }

        $this->newLine();
        $this->line("💡 Jalankan dengan --flush untuk simulasi COLD START (worst case saat server restart):");
        $this->line("   php artisan simulate:dashboard --students=20 --flush");
        $this->newLine();

        return 0;
    }

    private function simulateDashboard(User $user, AchievementService $achievementService): void
    {
        // Achievement throttle
        $achKey = "achievement_checked_{$user->id}";
        if (!\Illuminate\Support\Facades\Cache::has($achKey)) {
            // Skip actual check in simulation to avoid side effects
            \Illuminate\Support\Facades\Cache::put($achKey, true, 300);
        }

        // Stats cache
        \Illuminate\Support\Facades\Cache::remember("student_stats_{$user->id}", 300, function () use ($user) {
            return [
                'total'     => $user->examAttempts()->count(),
                'completed' => $user->examAttempts()->whereNotNull('submitted_at')->count(),
                'avg_score' => $user->examAttempts()->whereNotNull('final_score')->avg('final_score') ?? 0,
            ];
        });

        // Leaderboard caches
        \Illuminate\Support\Facades\Cache::remember("leaderboard_angkatan_{$user->grade}", 600, function () use ($user) {
            return \App\Models\User::where('role', 'student')->where('status', 'Aktif')
                ->where('grade', $user->grade)->take(10)->get(['id', 'name']);
        });

        // Submitted exam IDs
        $submittedIds = \Illuminate\Support\Facades\Cache::remember("submitted_exam_ids_{$user->id}", 180, function () use ($user) {
            return $user->examAttempts()->where('status', 'submitted')->pluck('exam_id')->toArray();
        });

        // Available exams
        \Illuminate\Support\Facades\Cache::remember("available_exams_{$user->id}_{$user->grade}", 180, function () use ($user, $submittedIds) {
            return \App\Models\Exam::where('status', 'published')
                ->whereNotIn('id', $submittedIds)
                ->take(6)->get(['id', 'title']);
        });

        // Recent results
        \Illuminate\Support\Facades\Cache::remember("recent_results_{$user->id}", 180, function () use ($user) {
            return $user->examAttempts()->whereNotNull('submitted_at')->orderByDesc('submitted_at')->take(5)->get();
        });
    }
}
