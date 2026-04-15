<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimulateLoginLoad extends Command
{
    protected $signature = 'simulate:login {--students=200 : Jumlah siswa yang mau dicoba login} {--hash=mixed : Jenis password (plain, bcrypt, mixed)}';
    protected $description = 'Simulasi mass-login serentak untuk mengukur footprint CPU dan kecepatan otentikasi.';

    public function handle(): int
    {
        $studentCount = (int) $this->option('students');
        $hashMode = strtolower($this->option('hash'));

        $this->newLine();
        $this->line("╔══════════════════════════════════════════════════════╗");
        $this->line("║         SIMULASI MASS-LOGIN — {$studentCount} SISWA           ║");
        $this->line("╚══════════════════════════════════════════════════════╝");
        $this->newLine();

        // Cari 200 siswa aktif secara random
        $students = User::where('role', 'student')
            ->where('status', 'Aktif')
            ->inRandomOrder()
            ->take($studentCount)
            ->get();

        if ($students->isEmpty()) {
            $this->error('Tidak ada siswa aktif ditemukan!');
            return 1;
        }

        $this->info("⚙️  Menyiapkan data simulasi {$studentCount} siswa...");
        
        // Memodifikasi password di memory (tidak di save ke DB) agar kita tahu apa aslinya
        $testCredentials = [];
        foreach ($students as $index => $user) {
            $passwordAsli = 'pass123';
            
            // Set fake password based on mode (Only in memory!)
            if ($hashMode === 'plain') {
                $user->password = 'PLAIN_' . $passwordAsli;
            } elseif ($hashMode === 'bcrypt') {
                $user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // Hash untuk 'password' standar laravel
                $passwordAsli = 'password';
            } else {
                // Mixed: 50% PLAIN, 50% bcrypt
                if ($index % 2 == 0) {
                    $user->password = 'PLAIN_' . $passwordAsli;
                } else {
                    $user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                    $passwordAsli = 'password';
                }
            }

            $testCredentials[] = [
                'user' => $user,
                'nis' => $user->nis,
                'password_input' => $passwordAsli
            ];
        }

        $this->info("🚀 Memulai hantaman login serentak...\n");

        $results  = [];
        $totalMs  = 0;
        $plainCount = 0;
        $bcryptCount = 0;

        $this->line(str_pad('No', 4) . str_pad('Student', 30) . str_pad('Time(ms)', 15) . str_pad('Type', 15) . 'Status');
        $this->line(str_repeat('─', 80));

        foreach ($testCredentials as $i => $cred) {
            $user = $cred['user'];
            $inputPassword = $cred['password_input'];
            
            $start = microtime(true);

            // SIMULASI LOGIN LOGIC DARI LoginController.php
            $isValid = false;
            $shouldUpgradeHash = false;
            $type = '';

            if ($user->password && str_starts_with($user->password, 'PLAIN_')) {
                $plainFromDb = substr($user->password, 6);
                if ($inputPassword === $plainFromDb) {
                    $isValid = true;
                    // $shouldUpgradeHash = true; // Kita skip upgrade seperti di CBT update
                }
                $type = '⚡ PLAIN';
                $plainCount++;
            } elseif (Hash::check($inputPassword, $user->password)) {
                $isValid = true;
                $type = '🐢 BCRYPT';
                $bcryptCount++;
            }

            $elapsed = round((microtime(true) - $start) * 1000, 2);
            $totalMs += $elapsed;
            $results[] = $elapsed;

            $statusText = $isValid ? '<fg=green>✓ SUCCESS</>' : '<fg=red>✗ FAILED</>';

            $name = Str::limit($user->name, 26);
            $this->line(
                str_pad($i + 1, 4) .
                str_pad($name, 30) .
                str_pad($elapsed . 'ms', 15) .
                str_pad($type, 15) .
                $statusText
            );
        }

        $this->line(str_repeat('─', 80));
        $this->newLine();

        // Summary
        $avg = count($results) > 0 ? round(array_sum($results) / count($results), 2) : 0;
        $max = max($results);
        $min = min($results);

        $this->line("📊 <fg=cyan>RINGKASAN BEBAN CPU (WAKTU HITUNG)</>");
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Siswa Disimulasikan',  count($testCredentials) . " siswa"],
                ['Total CPU Time',       round($totalMs) . ' ms'],
                ['Rata-rata / Login',    $avg . ' ms'],
                ['Login Tercepat',       $min . ' ms'],
                ['Login Terlambat',      $max . ' ms'],
                ['Skema Terdeteksi',     "{$plainCount} Plain-text | {$bcryptCount} Bcrypt"],
            ]
        );

        $this->newLine();

        // Verdict
        if ($avg < 5) {
            $this->info("✅ EXCELLENT — Overhead Login mendekati 0%. Bebas dari penyakit 508 Resource Limit!");
        } elseif ($avg < 50) {
            $this->warn("⚠️  ACCEPTABLE — Ada campur aduk password Bcrypt. Masih aman namun bisa lebih baik.");
        } else {
            $this->error("🔴 DANGER — Rata-rata komputasi tinggi (>50ms per siswa). 200 Siswa serentak = Server 508 Mati!");
        }

        $this->newLine();
        $this->line("💡 Jalankan komparasi manual:");
        $this->line("   <fg=yellow>php artisan simulate:login --hash=plain</>  (Mode CBT yang baru)");
        $this->line("   <fg=yellow>php artisan simulate:login --hash=bcrypt</> (Mode Normal Laravel - BERBAHAYA)");
        $this->newLine();

        return 0;
    }
}
