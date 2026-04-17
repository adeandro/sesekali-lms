<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\BattleRoom;
use App\Models\User;
use Illuminate\Http\Client\Pool;

class SimulateBattleStress extends Command
{
    protected $signature = 'simulate:stress {token} {--students=50} {--route=join}';
    protected $description = 'Simulasi HTTP Concurrency nyata menggunakan Guzzle Async.';

    public function handle(): int
    {
        $token = $this->argument('token');
        $studentsCount = (int) $this->option('students');
        $routeMode = $this->option('route');
        
        $room = BattleRoom::where('token', strtoupper($token))->first();
        if (!$room) {
            $this->error("Room $token tidak ditemukan.");
            return 1;
        }

        $students = User::where('role', 'student')->limit($studentsCount)->get();
        if ($students->isEmpty()) {
            $this->error("Tidak ada data siswa.");
            return 1;
        }

        $url = url('/load-test/arena-join');
        $this->info("🚀 Menembakkan HTTP POST serentak ke: {$url}");
        $this->line("   Membawa beban {$studentsCount} concurrent requests untuk token: {$token}...");

        $timeStart = microtime(true);

        $responses = Http::pool(function (Pool $pool) use ($students, $token, $url) {
            $requests = [];
            foreach ($students as $s) {
                $requests[] = $pool->as("req_{$s->id}")->post($url, [
                    'secret'  => 'simulasi-stress',
                    'user_id' => $s->id,
                    'token'   => $token,
                ]);
            }
            return $requests;
        });

        $timeEnd = microtime(true);
        $elapsed = round(($timeEnd - $timeStart) * 1000, 2);

        $successCount = 0;
        $failCount = 0;

        foreach ($responses as $res) {
            if ($res instanceof \Exception || !$res->successful()) {
                $failCount++;
            } else {
                $successCount++;
            }
        }

        $this->line('');
        $this->info("✅ Simulasi Selesai dalam {$elapsed} ms!");
        $this->line("   Berhasil: {$successCount} requests");
        if ($failCount > 0) {
            $this->error("   Gagal / 508 / Timeout: {$failCount} requests");
        } else {
            $this->info("   Gagal: {$failCount} requests (Server Tahan Banting!)");
        }

        return 0;
    }
}
