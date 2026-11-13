<?php

namespace Tests\Feature;

use App\Models\ElectionPeriod;
use App\Models\ElectionVoter;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ElectionVoterFilterTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private ElectionPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(['key' => 'school_name'], ['value' => 'SMK Negeri 1 Test']);

        $this->admin = User::factory()->create([
            'name'     => 'Super Admin Test ' . uniqid(),
            'email'    => 'superadmin_' . uniqid() . '@example.com',
            'role'     => 'superadmin',
            'status'   => 'Aktif',
        ]);

        $this->period = ElectionPeriod::create([
            'title'                 => 'Pilketos Filter Test ' . uniqid(),
            'status'                => 'open',
            'event_date'            => now()->toDateString(),
            'booth_session_version' => 1,
            'allow_blank_vote'      => true,
            'opened_at'             => now(),
        ]);
    }

    private function createSampleVoters(): array
    {
        // Kelas 10 A: 2 orang (1 sudah memilih, 1 belum)
        $v1 = ElectionVoter::create([
            'period_id'     => $this->period->id,
            'student_id'    => null,
            'nis'           => '1001',
            'student_name'  => 'Andi Kelas Sepuluh A',
            'class_id'      => 101,
            'class_name'    => '10 A',
            'token_hash'    => 'hash_1001',
            'token_version' => 1,
            'has_voted'     => true,
            'voted_at'      => now(),
        ]);

        $v2 = ElectionVoter::create([
            'period_id'     => $this->period->id,
            'student_id'    => null,
            'nis'           => '1002',
            'student_name'  => 'Budi Kelas Sepuluh A Belum',
            'class_id'      => 101,
            'class_name'    => '10 A',
            'token_hash'    => null,
            'token_version' => 0,
            'has_voted'     => false,
            'voted_at'      => null,
        ]);

        // Kelas 10 B: 2 orang (keduanya belum memilih)
        $v3 = ElectionVoter::create([
            'period_id'     => $this->period->id,
            'student_id'    => null,
            'nis'           => '2001',
            'student_name'  => 'Citra Kelas Sepuluh B',
            'class_id'      => 102,
            'class_name'    => '10 B',
            'token_hash'    => 'hash_2001',
            'token_version' => 1,
            'has_voted'     => false,
            'voted_at'      => null,
        ]);

        $v4 = ElectionVoter::create([
            'period_id'     => $this->period->id,
            'student_id'    => null,
            'nis'           => '2002',
            'student_name'  => 'Dedi Kelas Sepuluh B',
            'class_id'      => 102,
            'class_name'    => '10 B',
            'token_hash'    => null,
            'token_version' => 0,
            'has_voted'     => false,
            'voted_at'      => null,
        ]);

        return [$v1, $v2, $v3, $v4];
    }

    /**
     * Test 1: Halaman index memuat pemilih beserta ringkasan kelas dan tab filter
     */
    public function test_index_displays_voters_and_summary_cards(): void
    {
        $this->createSampleVoters();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', $this->period));

        $response->assertOk();
        $response->assertSee('Daftar Pemilih');
        $response->assertSee('10 A');
        $response->assertSee('10 B');
        $response->assertSee('Andi Kelas Sepuluh A');
        $response->assertSee('Budi Kelas Sepuluh A Belum');
        $response->assertSee('Belum Memilih');
        $response->assertSee('Sudah Memilih');
    }

    /**
     * Test 2: Filter Hak Suara belum memilih (voted=belum)
     */
    public function test_filter_by_voted_status_belum(): void
    {
        $this->createSampleVoters();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', [
                'period' => $this->period,
                'voted'  => 'belum',
            ]));

        $response->assertOk();
        // Pemilih yang belum memilih harus muncul
        $response->assertSee('Budi Kelas Sepuluh A Belum');
        $response->assertSee('Citra Kelas Sepuluh B');
        $response->assertSee('Dedi Kelas Sepuluh B');
        // Pemilih yang sudah memilih TIDAK boleh muncul di tabel
        $response->assertDontSee('Andi Kelas Sepuluh A');
    }

    /**
     * Test 3: Filter Hak Suara sudah memilih (voted=sudah)
     */
    public function test_filter_by_voted_status_sudah(): void
    {
        $this->createSampleVoters();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', [
                'period' => $this->period,
                'voted'  => 'sudah',
            ]));

        $response->assertOk();
        $response->assertSee('Andi Kelas Sepuluh A');
        $response->assertDontSee('Budi Kelas Sepuluh A Belum');
        $response->assertDontSee('Citra Kelas Sepuluh B');
    }

    /**
     * Test 4: Filter kelas spesifik (misal '10 A')
     */
    public function test_filter_by_class_name(): void
    {
        $this->createSampleVoters();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', [
                'period' => $this->period,
                'class'  => '10 A',
            ]));

        $response->assertOk();
        $response->assertSee('Andi Kelas Sepuluh A');
        $response->assertSee('Budi Kelas Sepuluh A Belum');
        $response->assertDontSee('Citra Kelas Sepuluh B');
        $response->assertDontSee('Dedi Kelas Sepuluh B');
    }

    /**
     * Test 5: Kombinasi filter kelas '10 A' + belum memilih -> melihat siapa saja di 10 A yang belum voting
     */
    public function test_filter_by_class_and_unvoted_combined(): void
    {
        $this->createSampleVoters();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', [
                'period' => $this->period,
                'class'  => '10 A',
                'voted'  => 'belum',
            ]));

        $response->assertOk();
        // Hanya Budi yang ada di kelas 10 A dan belum memilih
        $response->assertSee('Budi Kelas Sepuluh A Belum');
        $response->assertDontSee('Andi Kelas Sepuluh A'); // di 10 A tapi sudah memilih
        $response->assertDontSee('Citra Kelas Sepuluh B'); // belum memilih tapi di 10 B
        $response->assertDontSee('Dedi Kelas Sepuluh B');
    }

    /**
     * Test 6: Pencarian berdasarkan Nama atau NIS
     */
    public function test_search_by_name_and_nis(): void
    {
        $this->createSampleVoters();

        // Cari berdasarkan nama
        $resName = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', [
                'period' => $this->period,
                'search' => 'Citra',
            ]));

        $resName->assertOk();
        $resName->assertSee('Citra Kelas Sepuluh B');
        $resName->assertDontSee('Andi Kelas Sepuluh A');

        // Cari berdasarkan NIS
        $resNis = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', [
                'period' => $this->period,
                'search' => '1001',
            ]));

        $resNis->assertOk();
        $resNis->assertSee('Andi Kelas Sepuluh A');
        $resNis->assertDontSee('Citra Kelas Sepuluh B');
    }

    /**
     * Test 7: Pengurutan sort=unvoted_first menempatkan yang belum memilih di baris atas
     */
    public function test_sorting_unvoted_first(): void
    {
        $this->createSampleVoters();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.index', [
                'period' => $this->period,
                'sort'   => 'unvoted_first',
            ]));

        $response->assertOk();
        $content = $response->getContent();

        // Budi (belum memilih) harus muncul sebelum Andi (sudah memilih)
        $posBudi = strpos($content, 'Budi Kelas Sepuluh A Belum');
        $posAndi = strpos($content, 'Andi Kelas Sepuluh A');

        $this->assertNotFalse($posBudi);
        $this->assertNotFalse($posAndi);
        $this->assertLessThan($posAndi, $posBudi);
    }

    /**
     * Test 8: Export Excel menghormati filter yang dipilih
     */
    public function test_export_excel_with_filters(): void
    {
        $this->createSampleVoters();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pilketos.periods.voters.export', [
                'period' => $this->period,
                'class'  => '10 A',
                'voted'  => 'belum',
            ]));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('10-a', $disposition);
        $this->assertStringContainsString('belum-memilih', $disposition);
    }
}
