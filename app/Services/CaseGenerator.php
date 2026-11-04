<?php

namespace App\Services;

use App\Models\CtTeam;
use RuntimeException;

/**
 * CaseGenerator — deterministic CT escape room case builder.
 *
 * All randomness happens exactly once during generate(); the resulting
 * $params array is stored in ct_teams.params and can be passed back
 * into render methods to reproduce the worksheet / answer key at any
 * time without touching the database again.
 */
class CaseGenerator
{
    // ── Constants ──────────────────────────────────────────────────────

    private const SUBJECTS = [
        'informatika', 'matematika', 'fisika', 'kimia',
        'biologi', 'sejarah', 'ekonomi', 'geografi',
    ];

    private const N_WORDS = [
        2 => 'DUA', 3 => 'TIGA', 4 => 'EMPAT', 5 => 'LIMA',
        6 => 'ENAM', 7 => 'TUJUH', 8 => 'DELAPAN', 9 => 'SEMBILAN',
    ];

    private const DECOY_FILES = [
        'nilai_10A.xlsx', 'modul_bab3.pdf', 'bank_soal_lama.zip',
        'kisi_kisi.docx', 'rekap_absen.xlsx',
    ];

    /**
     * Server log noise pool.
     * Each entry: [time, account, action, detail]
     */
    private const NOISE_POOL = [
        ['07.12', 'siswa_1042', 'LOGIN', 'berhasil'],
        ['07.15', 'guru_mtk',   'BUKA',  'nilai_10A.xlsx'],
        ['07.48', 'siswa_0331', 'LOGIN', 'berhasil'],
        ['08.40', 'siswa_0877', 'LOGIN', 'gagal'],
        ['09.30', 'perpus_01',  'LOGIN', 'berhasil'],
        ['10.05', 'guru_ipa',   'UNDUH', 'modul_bab3.pdf'],
        ['10.42', 'siswa_0215', 'BUKA',  'jadwal.pdf'],
        ['11.20', 'tu_01',      'BUKA',  'absensi.xlsx'],
        ['12.20', 'siswa_1042', 'BUKA',  'jadwal.pdf'],
        ['12.55', 'guru_bing',  'LOGIN', 'berhasil'],
        ['13.10', 'siswa_0560', 'LOGIN', 'gagal'],
        ['15.50', 'tu_01',      'BUKA',  'rekap_kas.xlsx'],
        ['16.30', 'guru_pjok',  'BUKA',  'nilai_10C.xlsx'],
        ['17.05', 'wali_10B',   'LOGIN', 'berhasil'],
        ['18.20', 'siswa_0331', 'LOGIN', 'berhasil'],
    ];

    /**
     * Example words used for Caesar cipher demonstration.
     * [plaintext, ...] — cipher text computed at render time.
     */
    private const EXAMPLE_WORDS = ['HALO', 'BUKU', 'SOAL'];

    // ── Public API ─────────────────────────────────────────────────────

    /**
     * Generate a fresh set of params for a team.
     * Throws RuntimeException if unable to find a unique code after max retries.
     *
     * @param  int|null  $existingTeamId  Exclude this team when checking uniqueness (for regeneration)
     */
    public function generate(?int $existingTeamId = null): array
    {
        $maxRetries = config('ct.max_gen_retries', 50);

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $params = $this->buildParams();
            $code   = $this->deriveCode($params);
            $hash   = hash_hmac('sha256', $code, config('app.key'));

            // Global uniqueness check
            $conflict = CtTeam::where('code_hash', $hash)
                ->when($existingTeamId, fn ($q) => $q->where('id', '!=', $existingTeamId))
                ->exists();

            if (! $conflict) {
                $params['code_hash'] = $hash;

                return $params;
            }
        }

        throw new RuntimeException(
            "CaseGenerator: unable to find a unique code after {$maxRetries} attempts. " .
            "Too many teams already generated for the available code space."
        );
    }

    /**
     * Derive the 4-digit code string from stored params.
     * Code = minute(2 chars) + n(1 char) + files_count(1 char).
     */
    public function deriveCode(array $params): string
    {
        $minute     = $params['minute'];         // 2-char string "00".."59"
        $n          = (string) $params['n'];     // 1 digit 2-9
        $filesCount = (string) $params['files_count']; // 1 digit 2-8

        return $minute . $n . $filesCount;
    }

    /**
     * Render the server log lines for Stage 1 (sorted, perpetrator last).
     * Returns array of formatted strings ready for display.
     */
    public function renderLog(array $params): array
    {
        $lines = [];

        // Add the 7 noise lines
        foreach ($params['noise'] as $entry) {
            $lines[] = $entry;
        }

        // Add the 3 fixed narrative lines (before perpetrator)
        $lines[] = [$params['decoy_fail_time'], 'admin_lama', 'LOGIN', 'gagal'];
        $lines[] = [$params['decoy_ok_time'],   'admin_lama', 'LOGIN', 'berhasil'];
        $lines[] = [$params['guru_dl_time'],     'guru_mtk',  'UNDUH', 'soal_latihan.pdf'];

        // Sort all lines by time ascending
        usort($lines, fn ($a, $b) => strcmp($a[0], $b[0]));

        // Append perpetrator row last (do NOT sort it in)
        $subject = $params['subjects'][0];
        $lines[] = [$params['real_time'], 'admin_lama', 'UNDUH', "soal_{$subject}.pdf"];

        return array_map([$this, 'formatLogLine'], $lines);
    }

    /**
     * Format a single log entry tuple into a padded string.
     */
    public function formatLogLine(array $entry): string
    {
        [$time, $account, $action, $detail] = $entry;

        return sprintf(
            '%s | %-10s | %-5s | %s',
            $time,
            $account,
            $action,
            $detail
        );
    }

    /**
     * Apply Caesar cipher to a string (A-Z only, preserves spaces and case).
     */
    public function caesarEncrypt(string $plain, int $shift): string
    {
        $result = '';
        $plain  = strtoupper($plain);

        foreach (str_split($plain) as $char) {
            if ($char === ' ') {
                $result .= ' ';
            } elseif ($char >= 'A' && $char <= 'Z') {
                $result .= chr((ord($char) - ord('A') + $shift) % 26 + ord('A'));
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Decrypt Caesar cipher.
     */
    public function caesarDecrypt(string $cipher, int $shift): string
    {
        return $this->caesarEncrypt($cipher, 26 - ($shift % 26));
    }

    /**
     * Render Stage 2 cipher data for display.
     * Returns: examples, secret_cipher, secret_plain.
     */
    public function renderCipher(array $params): array
    {
        $shift = $params['shift'];
        $n     = $params['n'];
        $kata  = self::N_WORDS[$n];

        $plain  = "AKUN LAMA AKTIF {$kata}";
        $cipher = $this->caesarEncrypt($plain, $shift);

        $examples = [];
        foreach (self::EXAMPLE_WORDS as $word) {
            $examples[] = [
                'plain'  => $word,
                'cipher' => $this->caesarEncrypt($word, $shift),
            ];
        }

        return [
            'examples'      => $examples,
            'secret_plain'  => $plain,
            'secret_cipher' => $cipher,
        ];
    }

    /**
     * Render Stage 3 folder file list (soal_* files + decoy files, interleaved).
     */
    public function renderFolderFiles(array $params): array
    {
        $subjects     = array_slice($params['subjects'], 0, $params['files_count']);
        $soalFiles    = array_map(fn ($s) => "soal_{$s}.pdf", $subjects);
        $decoyFiles   = $params['decoy_files'];

        // Interleave: 2 soal, 1 decoy, repeat
        $result = [];
        $si     = 0;  // soal index
        $di     = 0;  // decoy index
        $count  = count($soalFiles) + count($decoyFiles);

        for ($i = 0; $i < $count; $i++) {
            if ($di < count($decoyFiles) && ($si >= count($soalFiles) || $i % 3 === 2)) {
                $result[] = $decoyFiles[$di++];
            } else {
                $result[] = $soalFiles[$si++];
            }
        }

        return $result;
    }

    /**
     * Full key data for teacher answer sheet.
     */
    public function answerKey(array $params): array
    {
        $log           = $this->renderLog($params);
        $perpetrator   = end($log);  // last line is always the perpetrator
        $cipher        = $this->renderCipher($params);
        $folderFiles   = $this->renderFolderFiles($params);
        $soalFileCount = count(array_filter($folderFiles, fn ($f) => str_starts_with($f, 'soal_')));
        $code          = $this->deriveCode($params);

        return [
            'perpetrator_line'   => $perpetrator,
            'perpetrator_time'   => $params['real_time'],
            'minute'             => $params['minute'],
            'shift'              => $params['shift'],
            'n'                  => $params['n'],
            'n_word'             => self::N_WORDS[$params['n']],
            'secret_plain'       => $cipher['secret_plain'],
            'files_count'        => $params['files_count'],
            'soal_file_count'    => $soalFileCount,
            'decoy_count'        => count($params['decoy_files']),
            'code'               => $code,
            'examples'           => $cipher['examples'],
            'folder_files'       => $folderFiles,
            'decoy_fail_time'    => $params['decoy_fail_time'],
            'decoy_ok_time'      => $params['decoy_ok_time'],
            'guru_dl_time'       => $params['guru_dl_time'],
            // Stage 3 fixed answers
            'stage3_order'       => 'C → B → D → A → E',
            'stage3_conditional' => 'B',
            'stage3_loop'        => 'D',
            'stage3_a_times'     => $params['files_count'],
        ];
    }

    // ── Private helpers ────────────────────────────────────────────────

    /**
     * Build a fresh random parameter set (no uniqueness check here).
     */
    private function buildParams(): array
    {
        // real_time: hour 1–3
        $realHour   = random_int(1, 3);
        $realMinute = random_int(0, 59);
        $realTime   = sprintf('%02d.%02d', $realHour, $realMinute);

        // decoy_ok_time: hour 21–23, minute != realMinute
        $decoyOkHour   = random_int(21, 23);
        $decoyOkMinute = $this->randomMinuteDifferentFrom($realMinute);
        $decoyOkTime   = sprintf('%02d.%02d', $decoyOkHour, $decoyOkMinute);

        // decoy_fail_time: hour 12–15
        $decoyFailHour   = random_int(12, 15);
        $decoyFailMinute = random_int(0, 59);
        $decoyFailTime   = sprintf('%02d.%02d', $decoyFailHour, $decoyFailMinute);

        // guru_dl_time: hour 8–11
        $guruHour   = random_int(8, 11);
        $guruMinute = random_int(0, 59);
        $guruDlTime = sprintf('%02d.%02d', $guruHour, $guruMinute);

        // shift: 2–12
        $shift = random_int(2, 12);

        // n: 2–9
        $n = random_int(2, 9);

        // files_count: 2–8
        $filesCount = random_int(2, 8);

        // subjects: rotate pool by random offset, take filesCount items
        $offset   = random_int(0, count(self::SUBJECTS) - 1);
        $rotated  = array_merge(
            array_slice(self::SUBJECTS, $offset),
            array_slice(self::SUBJECTS, 0, $offset)
        );
        $subjects = array_slice($rotated, 0, $filesCount);

        // noise: 7 entries from pool, avoiding time conflicts
        $noise = $this->pickNoise(7, [
            $realTime, $decoyOkTime, $decoyFailTime, $guruDlTime,
        ]);

        // decoy_files: 3 from DECOY_FILES
        $decoyFiles = $this->pickDecoyFiles(3);

        // minute string (2-digit, zero-padded)
        $minute = sprintf('%02d', $realMinute);

        return [
            'real_time'       => $realTime,
            'decoy_ok_time'   => $decoyOkTime,
            'decoy_fail_time' => $decoyFailTime,
            'guru_dl_time'    => $guruDlTime,
            'shift'           => $shift,
            'n'               => $n,
            'files_count'     => $filesCount,
            'subjects'        => $subjects,
            'noise'           => $noise,
            'decoy_files'     => $decoyFiles,
            'minute'          => $minute,
        ];
    }

    /**
     * Pick a random minute (0–59) that differs from the given minute.
     */
    private function randomMinuteDifferentFrom(int $exclude): int
    {
        do {
            $m = random_int(0, 59);
        } while ($m === $exclude);

        return $m;
    }

    /**
     * Pick $count noise entries from the pool, excluding entries whose
     * hour matches any of the reserved times.
     */
    private function pickNoise(int $count, array $reservedTimes): array
    {
        // Extract hours from reserved times ("HH.MM" => "HH")
        $reservedHours = array_map(fn ($t) => substr($t, 0, 2), $reservedTimes);

        $eligible = array_filter(
            self::NOISE_POOL,
            fn ($entry) => ! in_array(substr($entry[0], 0, 2), $reservedHours, true)
        );
        $eligible = array_values($eligible);

        // Shuffle and pick
        shuffle($eligible);

        return array_slice($eligible, 0, min($count, count($eligible)));
    }

    /**
     * Pick $count decoy file names randomly.
     */
    private function pickDecoyFiles(int $count): array
    {
        $pool = self::DECOY_FILES;
        shuffle($pool);

        return array_slice($pool, 0, $count);
    }

    // ── Public: Hint generator ──────────────────────────────────────────

    /**
     * Return a staged hint text based on how many hints have been given.
     *
     * Hints reveal the answer progressively without ever exposing the code:
     *   Level 1 — point to the right log entry without naming the minute
     *   Level 2 — confirm the action type (UNDUH) and approximate hour range
     *   Level 3 — reveal the N word from the cipher message; not the code
     *
     * @param  array  $params   Team's stored params (from ct_teams.params)
     * @param  int    $level    Current hint_count (1, 2, or 3)
     */
    public function hint(array $params, int $level): string
    {
        // Safe: params contains no code, only the puzzle parameters
        $subject = $params['subjects'][0] ?? 'soal';
        $hour    = explode('.', $params['real_time'])[0];   // e.g. "02"
        $n       = $params['n'];
        $nWord   = self::N_WORDS[$n];

        return match ($level) {
            1 => "Petunjuk 1: Perhatikan entri log yang paling mencurigakan — "
               . "baris terakhir bukan dalam urutan waktu normal. "
               . "Fokus pada akun yang melakukan UNDUH file soal.",

            2 => "Petunjuk 2: Jam unduhan yang mencurigakan terjadi antara jam 01.00 dan 03.59. "
               . "Dua digit menit dari jam tersebut adalah dua digit pertama kode (MM).",

            3 => "Petunjuk 3: Pesan terenkripsi di Tahap 2 mengandung kata \"$nWord\". "
               . "Kata itulah angka N (digit ke-3 kode). "
               . "Hitung juga file soal_*.pdf di folder untuk mendapat F (digit ke-4).",

            default => "Semua petunjuk sudah diberikan. Coba kembali periksa ketiga tahap dengan cermat.",
        };
    }
}
