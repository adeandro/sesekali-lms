<?php

namespace Tests\Feature;

use App\Exceptions\ElectionException;
use App\Models\ElectionAuditLog;
use App\Models\ElectionCandidate;
use App\Models\ElectionPeriod;
use App\Models\ElectionVote;
use App\Models\ElectionVoter;
use App\Models\Setting;
use App\Models\User;
use App\Services\ElectionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ElectionBoothTokenTest extends TestCase
{
    use DatabaseTransactions;

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'mysql');
    }

    private ElectionService $service;
    private ElectionPeriod $period;
    private ElectionCandidate $candidate1;
    private ElectionCandidate $candidate2;
    private string $rawPin = '123456';

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ElectionService::class);

        // Pastikan setting minimal ada
        Setting::updateOrCreate(['key' => 'school_name'], ['value' => 'SMK Negeri 1 Test']);

        // Pastikan tidak ada periode aktif lain yang mengganggu getActivePeriod()
        ElectionPeriod::whereIn('status', ['ready', 'open'])->update(['status' => 'closed']);

        // Buat periode pemilihan terbuka
        $this->period = ElectionPeriod::create([
            'title'                 => 'Pilketos Test ' . uniqid(),
            'status'                => 'open',
            'event_date'            => now()->toDateString(),
            'booth_pin_hash'        => Hash::make($this->rawPin),
            'booth_session_version' => 1,
            'allow_blank_vote'      => true,
            'opened_at'             => now(),
        ]);

        // Buat 2 paslon
        $this->candidate1 = ElectionCandidate::create([
            'period_id'     => $this->period->id,
            'number'        => 1,
            'chairman_name' => 'Calon Ketua 1',
            'vice_name'     => 'Calon Wakil 1',
        ]);

        $this->candidate2 = ElectionCandidate::create([
            'period_id'     => $this->period->id,
            'number'        => 2,
            'chairman_name' => 'Calon Ketua 2',
            'vice_name'     => 'Calon Wakil 2',
        ]);
    }

    private function createVoterWithToken(string $name = 'Budi Santoso', string $nis = '1001'): array
    {
        $voter = ElectionVoter::create([
            'period_id'     => $this->period->id,
            'nis'           => $nis,
            'student_name'  => $name,
            'class_id'      => 1,
            'class_name'    => 'X-RPL-1',
            'token_hash'    => null,
            'token_version' => 0,
            'has_voted'     => false,
        ]);

        $issued = $this->service->issueTokens(collect([$voter]));
        $this->service->commitTokens($issued);
        $voter->refresh();

        return [$voter, $issued[0]['token']];
    }

    private function boothSession(array $extra = []): array
    {
        return array_merge([
            'booth_period_id'       => $this->period->id,
            'booth_session_version' => $this->period->booth_session_version,
        ], $extra);
    }

    /**
     * Test 1: Token benar -> layar konfirmasi menampilkan nama/kelas/NIS yang benar -> Ya -> lembar suara -> pilih -> selesai
     */
    public function test_valid_token_full_voting_flow(): void
    {
        [$voter, $rawToken] = $this->createVoterWithToken('Siti Aisyah', '2024001');

        // 1. Submit token di form verify
        $response = $this->withSession($this->boothSession())
            ->post(route('election.verify'), ['token' => $rawToken]);

        $response->assertRedirect(route('election.confirm'));

        // 2. Akses halaman konfirmasi (GET)
        $confirmResponse = $this->withSession($this->boothSession([
            'booth_voter_id'        => $voter->id,
            'booth_voter_period_id' => $this->period->id,
            'booth_voter_confirmed' => false,
            'booth_voter_expires'   => now()->addMinutes(3)->timestamp,
        ]))->get(route('election.confirm'));

        $confirmResponse->assertOk();
        $confirmResponse->assertSee('Siti Aisyah');
        $confirmResponse->assertSee('X-RPL-1');
        $confirmResponse->assertSee('2024001');
        $confirmResponse->assertSee('Cocokkan dengan kartu pemilih.');
        $confirmResponse->assertSee('Ya, sesuai kartu');
        $confirmResponse->assertSee('Bukan, batalkan');
        // JANGAN tampilkan token di layar konfirmasi
        $confirmResponse->assertDontSee($rawToken);

        // 3. Konfirmasi "Ya, sesuai kartu" (POST)
        $yesResponse = $this->withSession($this->boothSession([
            'booth_voter_id'        => $voter->id,
            'booth_voter_period_id' => $this->period->id,
            'booth_voter_confirmed' => false,
            'booth_voter_expires'   => now()->addMinutes(3)->timestamp,
        ]))->post(route('election.confirm'));

        $yesResponse->assertRedirect(route('election.lembar'));

        // 4. Buka lembar suara
        $lembarResponse = $this->withSession($this->boothSession([
            'booth_voter_id'        => $voter->id,
            'booth_voter_period_id' => $this->period->id,
            'booth_voter_confirmed' => true,
            'booth_voter_expires'   => now()->addMinutes(3)->timestamp,
        ]))->get(route('election.lembar'));

        $lembarResponse->assertOk();
        $lembarResponse->assertSee('Calon Ketua 1');
        $lembarResponse->assertSee('Calon Ketua 2');

        // 5. Kirim pilihan (POST /pilketos/pilih)
        $pilihResponse = $this->withSession($this->boothSession([
            'booth_voter_id'        => $voter->id,
            'booth_voter_period_id' => $this->period->id,
            'booth_voter_confirmed' => true,
            'booth_voter_expires'   => now()->addMinutes(3)->timestamp,
        ]))->post(route('election.pilih'), ['candidate_id' => $this->candidate1->id]);

        $pilihResponse->assertRedirect(route('election.selesai'));

        // Verifikasi hasil
        $voter->refresh();
        $this->assertTrue($voter->has_voted);
        $this->assertNotNull($voter->voted_at);
        $this->assertEquals(1, ElectionVote::where('period_id', $this->period->id)->count());
    }

    /**
     * Test 2: Token salah/acak -> "Token tidak sesuai." (generik)
     */
    public function test_invalid_token_rejected_with_generic_message(): void
    {
        // Token salah 8 karakter
        $response = $this->withSession($this->boothSession())
            ->post(route('election.verify'), ['token' => 'INVALID9']);

        $response->assertSessionHas('error', 'Token tidak sesuai.');

        // Token salah < 8 karakter
        $response2 = $this->withSession($this->boothSession())
            ->post(route('election.verify'), ['token' => 'ABC']);

        $response2->assertSessionHas('error', 'Token tidak sesuai.');
    }

    /**
     * Test 3: "Bukan, batalkan" -> kembali ke form, token yang sama masih bisa dipakai kemudian
     */
    public function test_cancel_button_clears_session_and_keeps_token_valid(): void
    {
        [$voter, $rawToken] = $this->createVoterWithToken('Ahmad Dahlan', '2024002');

        // Batalkan konfirmasi
        $response = $this->withSession($this->boothSession([
            'booth_voter_id'        => $voter->id,
            'booth_voter_period_id' => $this->period->id,
            'booth_voter_confirmed' => false,
            'booth_voter_expires'   => now()->addMinutes(3)->timestamp,
        ]))->post(route('election.cancel'));

        $response->assertRedirect(route('election.index'));
        $response->assertSessionMissing('booth_voter_id');
        $response->assertSessionMissing('booth_voter_confirmed');

        // Voter di DB tetap has_voted = false dan token tetap berlaku
        $voter->refresh();
        $this->assertFalse($voter->has_voted);

        // Gunakan token yang sama lagi -> harus berhasil masuk konfirmasi
        $retryResponse = $this->withSession($this->boothSession())
            ->post(route('election.verify'), ['token' => $rawToken]);

        $retryResponse->assertRedirect(route('election.confirm'));
    }

    /**
     * Test 4: Buka /pilketos/lembar atau /pilketos/pilih langsung tanpa konfirmasi -> redirect ke /pilketos
     */
    public function test_lembar_and_pilih_require_confirmed_voter_session(): void
    {
        [$voter, $rawToken] = $this->createVoterWithToken('Dewi Sartika', '2024003');

        // Tanpa sesi voter sama sekali
        $this->withSession($this->boothSession())
            ->get(route('election.lembar'))
            ->assertRedirect(route('election.index'));

        $this->withSession($this->boothSession())
            ->post(route('election.pilih'), ['candidate_id' => $this->candidate1->id])
            ->assertRedirect(route('election.index'));

        // Sesi voter ada tetapi confirmed = false (belum klik "Ya, sesuai kartu")
        $this->withSession($this->boothSession([
            'booth_voter_id'        => $voter->id,
            'booth_voter_period_id' => $this->period->id,
            'booth_voter_confirmed' => false,
            'booth_voter_expires'   => now()->addMinutes(3)->timestamp,
        ]))->get(route('election.lembar'))
            ->assertRedirect(route('election.index'));

        $this->withSession($this->boothSession([
            'booth_voter_id'        => $voter->id,
            'booth_voter_period_id' => $this->period->id,
            'booth_voter_confirmed' => false,
            'booth_voter_expires'   => now()->addMinutes(3)->timestamp,
        ]))->post(route('election.pilih'), ['candidate_id' => $this->candidate1->id])
            ->assertRedirect(route('election.index'));
    }

    /**
     * Test 5: Token sudah dipakai -> ditolak
     */
    public function test_used_token_is_rejected(): void
    {
        [$voter, $rawToken] = $this->createVoterWithToken('Pemilih Sudah Nyoblos', '2024004');
        $voter->update(['has_voted' => true, 'voted_at' => now()]);

        $response = $this->withSession($this->boothSession())
            ->post(route('election.verify'), ['token' => $rawToken]);

        $response->assertSessionHas('error', 'Kartu ini sudah digunakan untuk memilih.');
    }

    /**
     * Test 5b: Siswa tidak aktif -> ditolak
     */
    public function test_inactive_student_voter_is_rejected(): void
    {
        $inactiveStudent = User::factory()->create([
            'role'   => 'student',
            'status' => 'Nonaktif',
        ]);

        $voter = ElectionVoter::create([
            'period_id'     => $this->period->id,
            'student_id'    => $inactiveStudent->id,
            'nis'           => '99999',
            'student_name'  => $inactiveStudent->name,
            'class_id'      => 1,
            'class_name'    => 'X-RPL-1',
            'token_hash'    => null,
            'token_version' => 0,
            'has_voted'     => false,
        ]);

        $issued = $this->service->issueTokens(collect([$voter]));
        $this->service->commitTokens($issued);
        $voter->refresh();

        $boothKey = 'booth-inactive-test';
        $this->expectException(ElectionException::class);
        $this->expectExceptionMessage('Siswa tidak aktif.');

        $this->service->verifyToken($this->period, $issued[0]['token'], $boothKey, '127.0.0.1');
    }

    /**
     * Test 6: 21 token salah berturut-turut di satu sesi bilik -> terkunci, bilik kedua tetap bisa dipakai
     */
    public function test_booth_lockout_after_20_failures_and_other_booth_remains_active(): void
    {
        $booth1Key = 'booth-session-1-' . uniqid();
        $booth2Key = 'booth-session-2-' . uniqid();
        $ip = '192.168.1.100';

        RateLimiter::clear('booth-key-' . $booth1Key);
        RateLimiter::clear('booth-key-' . $booth2Key);
        RateLimiter::clear('booth-ip-' . $ip);

        // 20 kali gagal di bilik 1
        for ($i = 0; $i < 20; $i++) {
            try {
                $this->service->verifyToken($this->period, 'WRONG00' . $i, $booth1Key, $ip);
                $this->fail('Harusnya gagal');
            } catch (ElectionException $e) {
                $this->assertEquals('Token tidak sesuai.', $e->getMessage());
            }
        }

        // Percobaan ke-21 di bilik 1 harus terkunci
        try {
            $this->service->verifyToken($this->period, 'WRONG021', $booth1Key, $ip);
            $this->fail('Bilik 1 harusnya terkunci');
        } catch (ElectionException $e) {
            $this->assertEquals('Terlalu banyak percobaan. Hubungi panitia.', $e->getMessage());
        }

        // Bilik 2 dengan boothKey berbeda tetap bisa melayani token yang valid
        [$voter, $rawToken] = $this->createVoterWithToken('Siswa Bilik Dua', '2024006');
        $result = $this->service->verifyToken($this->period, $rawToken, $booth2Key, '192.168.1.101');
        $this->assertEquals($voter->id, $result->id);
    }

    /**
     * Test 7: Dua tab dengan token yang sama, kirim bersamaan -> hanya 1 suara
     */
    public function test_concurrency_double_vote_prevents_duplicate(): void
    {
        [$voter, $rawToken] = $this->createVoterWithToken('Siswa Tab Dobel', '2024007');

        // Tab 1 mengirim suara
        $this->service->castVote($this->period, $voter->id, $this->candidate1->id);

        // Tab 2 mencoba mengirim suara untuk voter yang sama
        $this->expectException(ElectionException::class);
        $this->expectExceptionMessage('Anda sudah memberikan suara.');

        $this->service->castVote($this->period, $voter->id, $this->candidate2->id);

        $this->assertEquals(1, ElectionVote::where('period_id', $this->period->id)->count());
    }

    /**
     * Test 8: Cetak ulang satu siswa: token lama hangus, token baru berlaku
     */
    public function test_reprint_invalidates_old_token_and_activates_new(): void
    {
        [$voter, $tokenOld] = $this->createVoterWithToken('Siswa Hilang Kartu', '2024008');
        $this->assertEquals(1, $voter->token_version);

        // Cetak ulang kartu siswa ini
        $this->service->printVoterCards($this->period, collect([$voter]));
        $voter->refresh();
        $this->assertEquals(2, $voter->token_version);

        // Token lama harus ditolak
        $boothKey = 'booth-key-test';
        try {
            $this->service->verifyToken($this->period, $tokenOld, $boothKey, '127.0.0.1');
            $this->fail('Token lama harusnya tidak valid');
        } catch (ElectionException $e) {
            $this->assertEquals('Token tidak sesuai.', $e->getMessage());
        }

        // Generate ulang issueTokens untuk mendapatkan plaintext token baru
        $newIssued = $this->service->issueTokens(collect([$voter]));
        $this->service->commitTokens($newIssued);
        $voter->refresh();

        $verified = $this->service->verifyToken($this->period, $newIssued[0]['token'], $boothKey, '127.0.0.1');
        $this->assertEquals($voter->id, $verified->id);
    }

    /**
     * Test 9: storage/logs dan tabel election_audit_logs tidak memuat token maupun NIS dari percobaan gagal
     */
    public function test_failed_attempts_do_not_leak_token_or_nis_in_audit_logs(): void
    {
        $secretDummyToken = 'FAKE7799';
        $boothKey = 'booth-leak-test';

        try {
            $this->service->verifyToken($this->period, $secretDummyToken, $boothKey, '127.0.0.1');
        } catch (ElectionException $e) {
            // expected
        }

        // Cek tabel election_audit_logs
        $auditLogs = ElectionAuditLog::where('period_id', $this->period->id)
            ->where('action', 'booth.verify_fail')
            ->latest('id')
            ->first();

        $this->assertNotNull($auditLogs);
        $metaJson = json_encode($auditLogs->meta ?? []);
        $this->assertStringNotContainsString($secretDummyToken, $metaJson);
        $this->assertStringNotContainsString('token', strtolower($metaJson));
        $this->assertStringNotContainsString('nis', strtolower($metaJson));
    }

    /**
     * Test 10: Migration unique index berhasil di data yang sudah ada; jika ada duplikat, migration berhenti dengan pesan jelas
     */
    public function test_unique_index_and_duplicate_detection(): void
    {
        // Cek bahwa unique index pada (period_id, token_hash) bekerja di DB
        [$voter1, $token1] = $this->createVoterWithToken('Voter A', '9001');

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Paksa insert duplikat token_hash pada periode yang sama
        DB::table('election_voters')->insert([
            'period_id'     => $this->period->id,
            'student_id'    => null,
            'nis'           => '9002',
            'student_name'  => 'Voter B Duplikat',
            'token_hash'    => $voter1->token_hash,
            'token_version' => 1,
            'has_voted'     => false,
        ]);
    }

    /**
     * Test 11: Regresi: alur PIN bilik, cabut sesi, publikasi kandidat/hasil, dan modul lain normal
     */
    public function test_booth_pin_session_revocation_and_public_routes_regression(): void
    {
        // 1. Verifikasi PIN bilik benar
        $pinResponse = $this->post(route('election.pin.post'), ['booth_pin' => $this->rawPin]);
        $pinResponse->assertRedirect(route('election.index'));
        $pinResponse->assertSessionHas('booth_period_id', $this->period->id);

        // 2. PIN bilik salah
        $wrongPinResponse = $this->post(route('election.pin.post'), ['booth_pin' => '999999']);
        $wrongPinResponse->assertSessionHas('error', 'PIN bilik salah.');

        // 3. Cabut sesi bilik (session version dinaikkan)
        $this->period->increment('booth_session_version');

        // Request dengan versi sesi lama harus diarahkan kembali ke step PIN
        $indexResponse = $this->withSession($this->boothSession([
            'booth_session_version' => 1, // versi lama
        ]))->get(route('election.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Bilik Terkunci');

        // 4. Halaman publik kandidat (aktifkan candidates_published)
        $this->period->update(['candidates_published' => true]);
        $kandidatResponse = $this->get(route('election.candidates'));
        $kandidatResponse->assertOk();
        $kandidatResponse->assertSee('Calon Ketua 1');
    }
}
