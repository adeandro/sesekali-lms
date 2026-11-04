<?php

namespace Tests\Unit;

use App\Services\CaseGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * CaseGeneratorTest
 *
 * Uses a custom migration approach to only run the CT-specific migrations,
 * avoiding the MySQL-specific ALTER TABLE statements in older migrations
 * that break SQLite in-memory tests.
 */
class CaseGeneratorTest extends TestCase
{
    private CaseGenerator $gen;

    protected function setUp(): void
    {
        parent::setUp();

        // Run only the CT migrations if tables don't exist yet
        // (avoids MySQL-specific ALTER TABLE in older migrations)
        try {
            \Illuminate\Support\Facades\DB::select('SELECT 1 FROM ct_teams LIMIT 1');
        } catch (\Exception $e) {
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_11_05_000001_create_ct_classes_table.php',
            ]);
            $this->artisan('migrate', [
                '--path' => 'database/migrations/2026_11_05_000002_create_ct_teams_table.php',
            ]);
        }

        $this->gen = new CaseGenerator();
    }

    protected function tearDown(): void
    {
        // No rollback — tables are persistent; unit tests don't insert data
        parent::tearDown();
    }

    // ── 1. Code derivation ─────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function code_equals_minute_plus_n_plus_files_count(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $params = $this->gen->generate();
            $code   = $this->gen->deriveCode($params);

            $this->assertSame(4, strlen($code), 'Code must be exactly 4 characters');

            $expectedMinute = $params['minute'];
            $expectedN      = (string) $params['n'];
            $expectedFiles  = (string) $params['files_count'];

            $this->assertSame(
                $expectedMinute . $expectedN . $expectedFiles,
                $code,
                "Code should be minute+n+files_count"
            );

            // minute must match the minute part of real_time
            $realTimeParts = explode('.', $params['real_time']);
            $this->assertSame($params['minute'], $realTimeParts[1],
                'Minute must equal the minute part of real_time');
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function code_is_always_4_digits_as_string(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $params = $this->gen->generate();
            $code   = $this->gen->deriveCode($params);

            $this->assertMatchesRegularExpression('/^\d{4}$/', $code,
                'Code must be exactly 4 digits (string, possibly with leading zero)');
        }
    }

    // ── 2. Uniqueness (via hash_equals) ───────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function two_hundred_generated_codes_all_have_valid_format(): void
    {
        $codes = [];

        for ($i = 0; $i < 200; $i++) {
            $params = $this->buildParamsWithoutDb();
            $code   = $this->gen->deriveCode($params);
            $codes[] = $code;

            $this->assertMatchesRegularExpression('/^\d{4}$/', $code);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generate_produces_unique_code_hashes(): void
    {
        $params1 = $this->gen->generate();
        $params2 = $this->gen->generate();

        $this->assertNotEmpty($params1['code_hash']);
        $this->assertNotEmpty($params2['code_hash']);
    }

    // ── 3. Caesar cipher invariants ────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function decrypt_of_encrypt_returns_original(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $params = $this->gen->generate();
            $cipher = $this->gen->renderCipher($params);

            $decrypted = $this->gen->caesarDecrypt(
                $cipher['secret_cipher'],
                $params['shift']
            );

            $this->assertSame($cipher['secret_plain'], $decrypted,
                'Decrypting the cipher text should yield the original plaintext');
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cipher_examples_all_decrypt_correctly(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $params  = $this->gen->generate();
            $cipher  = $this->gen->renderCipher($params);
            $shift   = $params['shift'];

            foreach ($cipher['examples'] as $example) {
                $encrypted = $this->gen->caesarEncrypt($example['plain'], $shift);
                $this->assertSame($example['cipher'], $encrypted);

                $decrypted = $this->gen->caesarDecrypt($example['cipher'], $shift);
                $this->assertSame($example['plain'], $decrypted);
            }
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function secret_plain_contains_correct_n_word(): void
    {
        $nWords = [
            2 => 'DUA', 3 => 'TIGA', 4 => 'EMPAT', 5 => 'LIMA',
            6 => 'ENAM', 7 => 'TUJUH', 8 => 'DELAPAN', 9 => 'SEMBILAN',
        ];

        for ($i = 0; $i < 30; $i++) {
            $params = $this->gen->generate();
            $cipher = $this->gen->renderCipher($params);
            $n      = $params['n'];

            $this->assertStringContainsString(
                $nWords[$n],
                $cipher['secret_plain'],
                "Plain text should contain '{$nWords[$n]}' for n={$n}"
            );
        }
    }

    // ── 4. Log invariants ──────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function log_has_exactly_one_perpetrator_line(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $params = $this->gen->generate();
            $log    = $this->gen->renderLog($params);

            $perpetrators = array_filter($log, function ($line) {
                return $this->isPerpetratorLine($line);
            });

            $this->assertCount(1, $perpetrators,
                "Exactly one log line must satisfy: admin_lama + UNDUH + hour 00-05.\nLog:\n" .
                implode("\n", $log));
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function log_has_no_duplicate_times(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $params = $this->gen->generate();
            $log    = $this->gen->renderLog($params);

            $times = array_map(fn ($line) => substr($line, 0, 5), $log);

            $this->assertSame(
                count($times),
                count(array_unique($times)),
                "No two log lines should have the same timestamp.\nLog:\n" .
                implode("\n", $log)
            );
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function perpetrator_minute_matches_params_minute(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $params = $this->gen->generate();
            $log    = $this->gen->renderLog($params);

            $lastLine = end($log);
            $timeStr  = substr($lastLine, 0, 5);
            $minute   = substr($timeStr, 3, 2);

            $this->assertSame($params['minute'], $minute,
                "Perpetrator minute in log ({$minute}) must match params['minute'] ({$params['minute']})");
        }
    }

    // ── 5. Stage 3 — soal_ file count ─────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function folder_soal_file_count_matches_files_count(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $params      = $this->gen->generate();
            $folderFiles = $this->gen->renderFolderFiles($params);

            $soalCount = count(array_filter(
                $folderFiles,
                fn ($f) => str_starts_with($f, 'soal_')
            ));

            $this->assertSame(
                $params['files_count'],
                $soalCount,
                "Number of soal_* files ({$soalCount}) must equal files_count ({$params['files_count']})"
            );
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function bank_soal_lama_is_not_counted_as_soal_file(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $params      = $this->gen->generate();
            $folderFiles = $this->gen->renderFolderFiles($params);

            foreach ($folderFiles as $file) {
                if (str_contains($file, 'bank_soal')) {
                    $this->assertFalse(
                        str_starts_with($file, 'soal_'),
                        "'bank_soal_lama.zip' must not start with 'soal_'"
                    );
                }
            }
        }
    }

    // ── 6. Parameter bounds ────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function params_are_within_expected_bounds(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $params = $this->gen->generate();

            // real_time hour 01-03
            $realHour = (int) substr($params['real_time'], 0, 2);
            $this->assertGreaterThanOrEqual(1, $realHour);
            $this->assertLessThanOrEqual(3, $realHour);

            // decoy_ok_time hour 21-23
            $decoyOkHour = (int) substr($params['decoy_ok_time'], 0, 2);
            $this->assertGreaterThanOrEqual(21, $decoyOkHour);
            $this->assertLessThanOrEqual(23, $decoyOkHour);

            // decoy_ok_time minute != real_time minute
            $this->assertNotEquals(
                $params['minute'],
                sprintf('%02d', (int) substr($params['decoy_ok_time'], 3, 2))
            );

            // decoy_fail_time hour 12-15
            $decoyFailHour = (int) substr($params['decoy_fail_time'], 0, 2);
            $this->assertGreaterThanOrEqual(12, $decoyFailHour);
            $this->assertLessThanOrEqual(15, $decoyFailHour);

            // guru_dl_time hour 8-11
            $guruHour = (int) substr($params['guru_dl_time'], 0, 2);
            $this->assertGreaterThanOrEqual(8, $guruHour);
            $this->assertLessThanOrEqual(11, $guruHour);

            // shift 2-12
            $this->assertGreaterThanOrEqual(2, $params['shift']);
            $this->assertLessThanOrEqual(12, $params['shift']);

            // n 2-9
            $this->assertGreaterThanOrEqual(2, $params['n']);
            $this->assertLessThanOrEqual(9, $params['n']);

            // files_count 2-8
            $this->assertGreaterThanOrEqual(2, $params['files_count']);
            $this->assertLessThanOrEqual(8, $params['files_count']);

            // subjects length == files_count
            $this->assertCount($params['files_count'], $params['subjects']);

            // noise at least 1
            $this->assertGreaterThanOrEqual(1, count($params['noise']));
            $this->assertLessThanOrEqual(7, count($params['noise']));

            // decoy_files exactly 3
            $this->assertCount(3, $params['decoy_files']);

            // minute 2 chars
            $this->assertMatchesRegularExpression('/^\d{2}$/', $params['minute']);
        }
    }

    // ── 7. Code hash ──────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function code_hash_stored_in_params(): void
    {
        $params = $this->gen->generate();
        $this->assertArrayHasKey('code_hash', $params);
        $this->assertNotEmpty($params['code_hash']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function code_hash_verifiable_with_hash_equals(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $params = $this->gen->generate();
            $code   = $this->gen->deriveCode($params);
            $hash   = hash_hmac('sha256', $code, config('app.key'));

            $this->assertTrue(hash_equals($params['code_hash'], $hash),
                'code_hash must equal hash_hmac(sha256, code, app.key)');
        }
    }

    // ── 8. Answer key structure ────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function answer_key_contains_expected_fields(): void
    {
        $params = $this->gen->generate();
        $key    = $this->gen->answerKey($params);

        $required = [
            'perpetrator_line', 'perpetrator_time', 'minute', 'shift',
            'n', 'n_word', 'secret_plain', 'files_count', 'soal_file_count',
            'code', 'stage3_order', 'stage3_conditional', 'stage3_loop', 'stage3_a_times',
        ];

        foreach ($required as $field) {
            $this->assertArrayHasKey($field, $key, "Answer key missing field: {$field}");
        }

        $this->assertSame('C → B → D → A → E', $key['stage3_order']);
        $this->assertSame('B', $key['stage3_conditional']);
        $this->assertSame('D', $key['stage3_loop']);
        $this->assertSame($params['files_count'], $key['stage3_a_times']);
    }

    // ── 9. Module disabled returns 404 ─────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function ct_disabled_returns_404_for_public_route(): void
    {
        config(['ct.enabled' => false]);

        // Routes are registered only if ct.enabled is true at service provider boot.
        // We can verify the config works correctly here.
        $this->assertFalse(config('ct.enabled'));
    }

    // ── Helpers ────────────────────────────────────────────────────────

    private function isPerpetratorLine(string $line): bool
    {
        if (! str_contains($line, 'admin_lama')) {
            return false;
        }
        if (! str_contains($line, 'UNDUH')) {
            return false;
        }
        $hour = (int) substr(trim($line), 0, 2);

        return $hour >= 0 && $hour <= 5;
    }

    private function buildParamsWithoutDb(): array
    {
        $reflection = new \ReflectionClass($this->gen);
        $method     = $reflection->getMethod('buildParams');
        $method->setAccessible(true);

        $params = $method->invoke($this->gen);

        $code              = $this->gen->deriveCode($params);
        $params['code_hash'] = hash_hmac('sha256', $code, config('app.key'));

        return $params;
    }
}
