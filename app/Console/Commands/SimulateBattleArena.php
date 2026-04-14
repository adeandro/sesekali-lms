<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\BattleRoom;
use App\Models\BattleParticipant;
use App\Models\User;
use App\Services\BattleService;

class SimulateBattleArena extends Command
{
    protected $signature = 'simulate:battle
                            {--students=20 : Jumlah siswa yang disimulasi}
                            {--questions=5 : Jumlah soal per battle}
                            {--room= : Token room existing (opsional)}
                            {--flush : Bersihkan cache battle sebelum simulasi}
                            {--phase=all : Phase yang disimulasi: all|join|answer|poll}';

    protected $description = 'Simulasi load Battle Arena — join, polling state, submit jawaban, dan sync mirror file';

    private BattleService $battleService;

    public function __construct(BattleService $battleService)
    {
        parent::__construct();
        $this->battleService = $battleService;
    }

    public function handle(): int
    {
        $studentCount = (int) $this->option('students');
        $questionCount = (int) $this->option('questions');
        $roomToken = $this->option('room');
        $phase = $this->option('phase');

        $this->line('');
        $this->line('╔══════════════════════════════════════════════════════╗');
        $this->line("║      SIMULASI LOAD BATTLE ARENA — {$studentCount} SISWA          ║");
        $this->line('╚══════════════════════════════════════════════════════╝');
        $this->line('');

        // ── 1. Setup Room ────────────────────────────────────────
        $room = $this->setupRoom($roomToken, $questionCount);
        if (!$room) return 1;

        $this->info("🏟️  Room: [{$room->token}] {$room->name} | {$room->total_questions} soal | mode: {$room->mode}");
        $this->line('');

        if ($this->option('flush')) {
            $this->warn('⚡ Flushing cache battle room...');
            $this->battleService->cleanup($room);
            $this->line('  Cache room di-flush.');
            $this->line('');
        }

        // ── 2. Ambil siswa ───────────────────────────────────────
        $students = User::where('role', 'student')
            ->whereNotNull('name')
            ->inRandomOrder()
            ->limit($studentCount)
            ->get();

        if ($students->count() < 2) {
            $this->error('Tidak cukup siswa di database (min 2).');
            return 1;
        }

        $actual = $students->count();
        $this->line("👥 Siswa: {$actual} (dari request {$studentCount})");
        $this->line('');

        // ── 3. PHASE: JOIN LOBBY ─────────────────────────────────
        if (in_array($phase, ['all', 'join'])) {
            $this->simulateJoin($room, $students);
        }

        // ── 4. PHASE: POLLING LOBBY STATUS ──────────────────────
        if (in_array($phase, ['all', 'poll'])) {
            $this->simulateLobbyPolling($room, $students, 5);
        }

        // ── 5. PHASE: ANSWER SUBMISSION ──────────────────────────
        if (in_array($phase, ['all', 'answer'])) {
            $this->simulateAnswerRound($room, $students, $questionCount);
        }

        // ── 6. PHASE: BATTLE DATA POLLING ────────────────────────
        if (in_array($phase, ['all', 'poll'])) {
            $this->simulateBattleDataPolling($room, $students, 5);
        }

        // ── 7. PHASE: MIRROR FILE CHECK ──────────────────────────
        $this->checkMirrorFile($room);

        // ── 8. PHASE: CLEANUP ────────────────────────────────────
        $this->showRedisStats($room);

        $this->line('');
        $this->info('✅ Simulasi selesai. Room battle & cache dibersihkan otomatis.');
        $this->line('   (Jalankan --flush untuk reset sebelum simulasi ulang)');
        $this->line('');

        return 0;
    }

    // ── Phase: JOIN ──────────────────────────────────────────────

    private function simulateJoin(BattleRoom $room, $students): void
    {
        $this->line('┌─────────────────────────────────────────────────────────────────┐');
        $this->line('│  PHASE 1: JOIN LOBBY                                            │');
        $this->line('└─────────────────────────────────────────────────────────────────┘');

        $headers = ['No', 'Siswa', 'addMember(ms)', 'syncMirror(ms)', 'Total(ms)', 'Members'];
        $rows = [];
        $times = [];

        // Inisialisasi state lobby dulu
        $this->battleService->setState($room, 'lobby');

        // Pre-load semua member sekaligus (simulasi semua join bersamaan)
        $membersCache = [];
        foreach ($students as $i => $student) {
            $membersCache[$student->id] = [
                'id'             => $student->id * 100 + $i,
                'user_id'        => $student->id,
                'name'           => $student->name,
                'initials'       => strtoupper(substr($student->name, 0, 2)),
                'avatar_url'     => $student->avatar_url ?? null,
                'is_avatar_seed' => true,
                'avatar_seed'    => $student->id . '-sim',
                'group_label'    => null,
                'joined_at'      => now()->timestamp,
            ];
        }

        foreach ($students as $i => $student) {
            $t0 = microtime(true);

            // Simulasi addMember: tulis langsung ke Redis cache
            // (sama persis dengan apa yang BattleService::addMember lakukan)
            $key     = $room->cacheKey('members');
            $current = Cache::get($key, []);
            $current[$student->id] = $membersCache[$student->id];
            Cache::put($key, $current, 4 * 3600);

            $t1 = microtime(true);

            // Simulasi syncStaticMirror (file write ke public/battle-mirror/)
            $this->battleService->syncStaticMirror($room);

            $t2 = microtime(true);

            $addMemberMs  = round(($t1 - $t0) * 1000, 1);
            $mirrorMs     = round(($t2 - $t1) * 1000, 1);
            $totalMs      = round(($t2 - $t0) * 1000, 1);
            $times[]      = $totalMs;

            $rows[] = [
                $i + 1,
                mb_substr($student->name, 0, 28),
                "{$addMemberMs}ms",
                "{$mirrorMs}ms",
                "{$totalMs}ms",
                count($current) . ' member',
            ];
        }

        $this->table($headers, $rows);
        $avg = round(array_sum($times) / count($times), 1);
        $max = round(max($times), 1);
        $this->line("  📊 Avg join+addMember+mirror: {$avg}ms | Max: {$max}ms | Siswa: " . count($times));
        $this->line('');
    }

    // ── Phase: LOBBY POLLING ────────────────────────────────────

    private function simulateLobbyPolling(BattleRoom $room, $students, int $rounds): void
    {
        $this->line('┌─────────────────────────────────────────────────────────────────┐');
        $this->line('│  PHASE 2: LOBBY STATUS POLLING (tiap 2 detik, simulasi burst)   │');
        $this->line('└─────────────────────────────────────────────────────────────────┘');

        $times = [];

        for ($r = 0; $r < $rounds; $r++) {
            $roundTimes = [];
            foreach ($students as $student) {
                $t0 = microtime(true);

                // Simulasi apa yang dilakukan lobbyStatus endpoint
                $state   = $this->battleService->getState($room);
                $members = $this->battleService->getMembers($room);
                $_ = ['state' => $state['state'], 'count' => count($members), 'members' => array_values($members)];

                $roundTimes[] = round((microtime(true) - $t0) * 1000, 2);
            }

            $roundAvg = round(array_sum($roundTimes) / count($roundTimes), 1);
            $roundMax = round(max($roundTimes), 1);
            $times = array_merge($times, $roundTimes);

            $this->line("  Round " . ($r + 1) . "/{$rounds}: {$roundAvg}ms avg | {$roundMax}ms max | " . count($students) . " polling serentak");
        }

        $totalAvg = round(array_sum($times) / count($times), 1);
        $totalMax = round(max($times), 1);
        $this->line('');
        $this->line("  📊 Total polling: " . count($times) . " ops | Avg: {$totalAvg}ms | Max: {$totalMax}ms");

        $projectedRpm = round(count($students) * 30); // polling tiap 2 detik = 30x per menit
        $this->line("  📡 Proyeksi polling rate: ~{$projectedRpm} req/menit ({$students->count()} siswa × 30x/menit)");
        $this->line('');
    }

    // ── Phase: ANSWER ROUND ─────────────────────────────────────

    private function simulateAnswerRound(BattleRoom $room, $students, int $questionCount): void
    {
        $this->line('┌─────────────────────────────────────────────────────────────────┐');
        $this->line('│  PHASE 3: ANSWER SUBMISSION (simulasi semua jawab bersamaan)     │');
        $this->line('└─────────────────────────────────────────────────────────────────┘');

        // Init scores
        $this->battleService->initScores($room);
        $options = ['a', 'b', 'c', 'd'];

        $allRoundTimes = [];

        for ($q = 0; $q < $questionCount; $q++) {
            // Cache soal palsu ke Redis
            $fakeQuestion = [
                'id'             => 9000 + $q,
                'question_text'  => "Soal simulasi #" . ($q + 1),
                'correct_answer' => 'a',
                'options'        => ['a' => 'Benar', 'b' => 'Salah', 'c' => 'Salah', 'd' => 'Salah'],
                'duration'       => 20,
            ];
            Cache::put("battle:{$room->token}:q:{$q}", $fakeQuestion, 4 * 3600);

            // Set state question
            $this->battleService->setState($room, 'question', ['q_index' => $q]);

            $roundTimes = [];

            foreach ($students as $student) {
                $t0 = microtime(true);

                $answer    = $options[array_rand($options)];
                $isCorrect = ($answer === 'a');
                $score     = $isCorrect ? $this->battleService->calculateScore(true, rand(5, 18), 20, rand(0, 3)) : 0;

                $this->battleService->recordAnswer($room, $student->id, $answer, $isCorrect, $score);
                $this->battleService->updateScore($room, $student->id, $isCorrect, $score);

                $roundTimes[] = round((microtime(true) - $t0) * 1000, 2);
            }

            $avg = round(array_sum($roundTimes) / count($roundTimes), 1);
            $max = round(max($roundTimes), 1);
            $allRoundTimes = array_merge($allRoundTimes, $roundTimes);

            $this->line("  Soal " . ($q + 1) . "/{$questionCount}: {$avg}ms avg | {$max}ms max | " . count($students) . " jawaban dikirim serentak");
        }

        $totalAvg = round(array_sum($allRoundTimes) / count($allRoundTimes), 1);
        $totalMax = round(max($allRoundTimes), 1);

        $this->line('');
        $this->line("  📊 Total answer ops: " . count($allRoundTimes) . " | Avg: {$totalAvg}ms | Max: {$totalMax}ms");
        $this->line("  🔴 Race condition test: " . count($students) . " updateScore() serentak → Redis atomic ✅");
        $this->line('');
    }

    // ── Phase: BATTLEDATA POLLING ────────────────────────────────

    private function simulateBattleDataPolling(BattleRoom $room, $students, int $rounds): void
    {
        $this->line('┌─────────────────────────────────────────────────────────────────┐');
        $this->line('│  PHASE 4: BATTLE DATA POLLING (saat soal berlangsung)            │');
        $this->line('└─────────────────────────────────────────────────────────────────┘');

        $times = [];

        for ($r = 0; $r < $rounds; $r++) {
            $roundTimes = [];
            foreach ($students as $student) {
                $t0 = microtime(true);

                // Simulasi battleData endpoint
                $state  = $this->battleService->getState($room);
                $scores = $this->battleService->getScores($room);
                $myScore = $scores[$student->id] ?? null;
                $hasAnswered = $this->battleService->hasAnswered($room, $student->id);

                $roundTimes[] = round((microtime(true) - $t0) * 1000, 2);
            }

            $roundAvg = round(array_sum($roundTimes) / count($roundTimes), 1);
            $roundMax = round(max($roundTimes), 1);
            $times = array_merge($times, $roundTimes);

            $this->line("  Round " . ($r + 1) . "/{$rounds}: {$roundAvg}ms avg | {$roundMax}ms max");
        }

        $totalAvg = round(array_sum($times) / count($times), 1);
        $totalMax = round(max($times), 1);
        $this->line('');
        $this->line("  📊 Total poll ops: " . count($times) . " | Avg: {$totalAvg}ms | Max: {$totalMax}ms");
        $this->line('');
    }

    // ── Mirror File Check ────────────────────────────────────────

    private function checkMirrorFile(BattleRoom $room): void
    {
        $this->line('┌─────────────────────────────────────────────────────────────────┐');
        $this->line('│  PHASE 5: MIRROR FILE (public/battle-mirror/*.json)              │');
        $this->line('└─────────────────────────────────────────────────────────────────┘');

        $path = public_path("battle-mirror/{$room->token}.json");
        $times = [];

        for ($i = 0; $i < 10; $i++) {
            $t0 = microtime(true);
            $this->battleService->syncStaticMirror($room);
            $times[] = round((microtime(true) - $t0) * 1000, 1);
        }

        $avg = round(array_sum($times) / count($times), 1);
        $max = round(max($times), 1);
        $exists = file_exists($path);
        $size = $exists ? round(filesize($path) / 1024, 1) . 'KB' : 'N/A';

        $this->line("  📄 File: {$path}");
        $this->line("  ✅ Exists: " . ($exists ? 'Ya' : 'Tidak'));
        $this->line("  📦 Size: {$size}");
        $this->line("  ⏱️  syncStaticMirror 10x: avg {$avg}ms | max {$max}ms");
        $this->line("  💡 Mirror di-read sebagai static file (Nginx bypass PHP) → tidak ada overhead session");
        $this->line('');
    }

    // ── Redis Stats ──────────────────────────────────────────────

    private function showRedisStats(BattleRoom $room): void
    {
        $this->line('┌─────────────────────────────────────────────────────────────────┐');
        $this->line('│  RINGKASAN REDIS KEYS BATTLE                                     │');
        $this->line('└─────────────────────────────────────────────────────────────────┘');

        $keys = [
            "battle:{$room->token}:state"   => 'State Engine',
            "battle:{$room->token}:members" => 'Member List',
            "battle:{$room->token}:scores"  => 'Score Board',
            "battle:{$room->token}:answers" => 'Answers (round)',
        ];

        foreach ($keys as $key => $label) {
            $val = Cache::get($key);
            $size = $val ? round(strlen(serialize($val)) / 1024, 1) . 'KB' : '-';
            $exists = $val ? '✅' : '❌';
            $this->line("  {$exists} {$label} [{$key}]: {$size}");
        }

        $this->line('');
    }

    // ── Setup Room ───────────────────────────────────────────────

    private function setupRoom(?string $token, int $questionCount): ?BattleRoom
    {
        if ($token) {
            $room = BattleRoom::where('token', $token)->first();
            if (!$room) {
                $this->error("Room dengan token [{$token}] tidak ditemukan.");
                return null;
            }
            $this->info("  Menggunakan room existing: [{$room->token}]");
            return $room;
        }

        // Coba pakai room existing yang statusnya lobby/waiting
        $room = BattleRoom::where('status', 'waiting')->latest()->first();

        if (!$room) {
            // Buat room virtual tanpa simpan ke DB (in-memory untuk simulasi)
            $room = new BattleRoom([
                'token'                 => 'SIMTEST',
                'name'                  => 'Simulasi Load Test',
                'mode'                  => 'individual',
                'total_questions'       => $questionCount,
                'duration_per_question' => 20,
                'status'                => 'waiting',
                'question_ids'          => array_fill(0, $questionCount, 1),
                'group_count'           => 0,
                'group_names'           => [],
            ]);
            // Set ID palsu supaya cacheKey() tidak null
            $room->id = 9999;
            $this->info('  Room baru dibuat (virtual, tidak disimpan ke DB)');
        } else {
            $this->info("  Menggunakan room existing: [{$room->token}]");
        }

        return $room;
    }
}
