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
        $routeMode = $this->option('route');
        
        $room = BattleRoom::where('token', strtoupper($token))->first();
        if (!$room && $routeMode !== 'login') { $this->error("Room tidak ditemukan."); return 1; }

        $students = User::where('role', 'student')->limit($studentsCount)->get();
        if ($students->isEmpty()) { $this->error("Tidak ada siswa."); return 1; }

        $url = $routeMode === 'login' ? url('/load-test/login') : url('/load-test/arena-join');
        $this->info("🚀 Stress Test LVE LIMIT: {$studentsCount} Request ke {$routeMode}");
        $this->info("   Batch Size: {$batchSize} concurrent | Jeda: {$pacingMs}ms");

        $timeStart = microtime(true);
        $successCount = 0; $failCount = 0; $firstError = false;

        $chunks = $students->chunk($batchSize);

        foreach ($chunks as $chunk) {
            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($chunk, $token, $url, $routeMode) {
                $reqs = [];
                foreach ($chunk as $s) {
                    $payload = ['secret' => 'simulasi-stress', 'user_id' => $s->id, 'token' => $token];
                    if ($routeMode === 'login') {
                        $payload['username'] = $s->email ?? $s->nis ?? $s->nip;
                        $payload['password'] = 'pass123'; // Asumsi default pass123 dari update sebelumnya
                    }
                    $reqs[] = $pool->as("req_{$s->id}")
                        ->withHeaders(['Connection' => 'close'])
                        ->post($url, $payload);
                }
                return $reqs;
            });

            foreach ($responses as $res) {
                if ($res instanceof \Exception || !$res->successful()) {
                    $failCount++;
                    if (!$firstError) {
                        if ($res instanceof \Exception) {
                            try {
                                $body = method_exists($res, 'getResponse') && $res->getResponse() ? substr($res->getResponse()->getBody()->getContents(), 0, 100) : '';
                                $this->error("Contoh Error: " . $res->getMessage() . " | Body: " . preg_replace('/\s+/', ' ', strip_tags($body)));
                            } catch (\Exception $e) {
                                $this->error("Contoh Error: " . $res->getMessage());
                            }
                        } else {
                            $this->error("Contoh Status: " . $res->status() . " | Body: " . preg_replace('/\s+/', ' ', strip_tags(substr($res->body(), 0, 100))));
                        }
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
