<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\BattleRoom;
use App\Models\User;

class SimulateBattleStress extends Command
{
    protected $signature = 'simulate:stress {token} {--students=50} {--route=join} {--pacing=0 : Jeda antar batch dalam milidetik} {--batch=50 : Berapa request per batch}';
    protected $description = 'Simulasi HTTP Concurrency dengan LVE Limit awareness.';

    public function handle(): int
    {
        $token = $this->argument('token');
        $studentsCount = (int) $this->option('students');
        $pacingMs = (int) $this->option('pacing');
        $batchSize = (int) $this->option('batch');
        
        $room = BattleRoom::where('token', strtoupper($token))->first();
        if (!$room) { $this->error("Room tidak ditemukan."); return 1; }

        $students = User::where('role', 'student')->limit($studentsCount)->get();
        if ($students->isEmpty()) { $this->error("Tidak ada siswa."); return 1; }

        $url = url('/load-test/arena-join');
        $this->info("🚀 Stress Test LVE LIMIT: {$studentsCount} Request");
        $this->info("   Batch Size: {$batchSize} concurrent | Jeda: {$pacingMs}ms");

        $timeStart = microtime(true);
        $successCount = 0; $failCount = 0; $firstError = false;

        $chunks = $students->chunk($batchSize);

        foreach ($chunks as $chunk) {
            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $token, $url) {
                $reqs = [];
                foreach ($chunk as $s) {
                    $reqs[] = $pool->as("req_{$s->id}")->post($url, ['secret' => 'simulasi-stress', 'user_id' => $s->id, 'token' => $token]);
                }
                return $reqs;
            });

            foreach ($responses as $res) {
                if ($res instanceof \Exception || !$res->successful()) {
                    $failCount++;
                    if (!$firstError) {
                        $this->error("Contoh 508 Error: " . ($res instanceof \Exception ? $res->getMessage() : $res->status()));
                        $firstError = true;
                    }
                } else {
                    $successCount++;
                }
            }

            if ($pacingMs > 0) {
                usleep($pacingMs * 1000);
            }
        }

        $elapsed = round((microtime(true) - $timeStart) * 1000, 2);
        $this->line('');
        $this->info("✅ Simulasi Selesai dalam {$elapsed} ms");
        $this->line("   Berhasil: {$successCount} | Gagal: {$failCount}");

        return 0;
    }
}
