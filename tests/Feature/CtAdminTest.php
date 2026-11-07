<?php

namespace Tests\Feature;

use App\Models\CtClass;
use App\Models\CtTeam;
use App\Models\User;
use App\Services\CaseGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Feature tests for the CT Admin panel.
 *
 * Uses DatabaseTransactions so each test rolls back after completion.
 * Requires MySQL (same as CtVaultFlowTest) because of MySQL-specific migrations.
 */
class CtAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'mysql');
    }

    private User $superadmin;
    private User $teacher;
    private User $student;
    private CtClass $ctClass;
    private CtTeam $ctTeam;
    private CaseGenerator $gen;

    protected function setUp(): void
    {
        parent::setUp();

        config(['ct.enabled' => true]);

        $this->gen = new CaseGenerator();

        // Create users with different roles
        $this->superadmin = User::factory()->create([
            'role'   => 'superadmin',
            'status' => 'Aktif',
        ]);

        $this->teacher = User::factory()->create([
            'role'   => 'teacher',
            'status' => 'Aktif',
        ]);

        $this->student = User::factory()->create([
            'role'   => 'student',
            'status' => 'Aktif',
        ]);

        // Create a test class and team
        $this->ctClass = CtClass::create([
            'name'      => 'TST-' . uniqid(),
            'is_active' => true,
        ]);

        $params = $this->gen->generate();
        $code   = $this->gen->deriveCode($params);

        $this->ctTeam = CtTeam::create([
            'ct_class_id' => $this->ctClass->id,
            'number'      => 1,
            'params'      => $params,
            'code_hash'   => hash_hmac('sha256', $code, config('app.key')),
        ]);
    }

    // ── 1. Auth guard ────────────────────────────────────────────────────────

    /** @test */
    public function guest_cannot_access_admin_index(): void
    {
        $this->get(route('admin.ct.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function student_gets_403_on_admin_index(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.ct.index'))
            ->assertForbidden();
    }

    /** @test */
    public function superadmin_can_access_admin_index(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.ct.index'))
            ->assertOk()
            ->assertSee($this->ctClass->name);
    }

    /** @test */
    public function teacher_can_access_admin_index(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.ct.index'))
            ->assertOk();
    }

    // ── 2. Class CRUD ────────────────────────────────────────────────────────

    /** @test */
    public function superadmin_can_create_class(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.ct.classes.store'), [
                'name'      => '11-TKJ',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.ct.index'));
        $this->assertDatabaseHas('ct_classes', ['name' => '11-TKJ']);
    }

    /** @test */
    public function duplicate_class_name_fails_validation(): void
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.ct.classes.store'), [
                'name' => $this->ctClass->name, // duplicate
            ])
            ->assertSessionHasErrors('name');
    }

    /** @test */
    public function superadmin_can_update_class(): void
    {
        $newName = 'EDITED-' . uniqid();

        $this->actingAs($this->superadmin)
            ->put(route('admin.ct.classes.update', $this->ctClass), [
                'name'      => $newName,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.ct.index'));

        $this->assertDatabaseHas('ct_classes', ['id' => $this->ctClass->id, 'name' => $newName]);
    }

    /** @test */
    public function superadmin_can_delete_class(): void
    {
        $this->actingAs($this->superadmin)
            ->delete(route('admin.ct.classes.destroy', $this->ctClass))
            ->assertRedirect(route('admin.ct.index'));

        $this->assertDatabaseMissing('ct_classes', ['id' => $this->ctClass->id]);
        // Teams should cascade-delete
        $this->assertDatabaseMissing('ct_teams', ['ct_class_id' => $this->ctClass->id]);
    }

    // ── 3. Team generation ───────────────────────────────────────────────────

    /** @test */
    public function superadmin_can_generate_teams(): void
    {
        // Create a fresh class with no teams
        $freshClass = CtClass::create(['name' => 'GEN-' . uniqid(), 'is_active' => true]);

        $this->actingAs($this->superadmin)
            ->post(route('admin.ct.classes.generate', $freshClass), [
                'count' => 5,
            ])
            ->assertRedirect(route('admin.ct.teams', $freshClass));

        $this->assertSame(5, CtTeam::where('ct_class_id', $freshClass->id)->count());
    }

    /** @test */
    public function generate_refuses_to_overwrite_without_force(): void
    {
        // ctClass already has 1 team from setUp
        $this->actingAs($this->superadmin)
            ->post(route('admin.ct.classes.generate', $this->ctClass), [
                'count' => 3,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /** @test */
    public function generate_overwrites_with_force_flag(): void
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.ct.classes.generate', $this->ctClass), [
                'count' => 4,
                'force' => '1',
            ])
            ->assertRedirect();

        $this->assertSame(4, CtTeam::where('ct_class_id', $this->ctClass->id)->count());
    }

    // ── 4. Team list ─────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_team_list(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.ct.teams', $this->ctClass))
            ->assertOk()
            ->assertSee((string) $this->ctTeam->number);
    }

    // ── 5. Answer key ────────────────────────────────────────────────────────

    /** @test */
    public function answer_key_is_not_publicly_accessible(): void
    {
        // No auth
        $this->get(route('admin.ct.answer-key', $this->ctClass))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_view_answer_key(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.ct.answer-key', $this->ctClass))
            ->assertOk();

        // The answer key page must NOT show actual 4-digit code in source
        // (it's shown as monospace in a table, so we just check the page loads)
        $code = $this->gen->deriveCode($this->ctTeam->params);
        $this->assertStringContainsString($code, $response->getContent()); // OK to show to admin
    }

    // ── 6. PDF downloads — no public access ──────────────────────────────────

    /** @test */
    public function guest_cannot_download_worksheet_pdf(): void
    {
        $this->get(route('admin.ct.teams.worksheet', $this->ctTeam))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function student_cannot_download_worksheet_pdf(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.ct.teams.worksheet', $this->ctTeam))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_download_worksheet_pdf(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.ct.teams.worksheet', $this->ctTeam));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function admin_can_download_answer_key_pdf(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.ct.answer-key.pdf', $this->ctClass));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    // ── 7. Results page ──────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_results(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.ct.results', $this->ctClass))
            ->assertOk()
            ->assertSee($this->ctClass->name);
    }

    // ── 8. CSV export ────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_export_csv(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.ct.export-csv', $this->ctClass));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        // Streamed response — capture via streamedContent()
        $content = $response->streamedContent();
        $this->assertStringContainsString($this->ctClass->name, $content);
        $this->assertStringContainsString((string) $this->ctTeam->number, $content);
    }

    /** @test */
    public function csv_contains_expected_columns(): void
    {
        $content = $this->actingAs($this->superadmin)
            ->get(route('admin.ct.export-csv', $this->ctClass))
            ->streamedContent();

        // Header row must include all expected columns
        $this->assertStringContainsString('Kelas', $content);
        $this->assertStringContainsString('Tim', $content);
        $this->assertStringContainsString('Status', $content);
        $this->assertStringContainsString('Percobaan', $content);
        $this->assertStringContainsString('No. Sertifikat', $content);
    }

    /** @test */
    public function guest_cannot_export_csv(): void
    {
        $this->get(route('admin.ct.export-csv', $this->ctClass))
            ->assertRedirect(route('login'));
    }

    // ── 9. Team reset ────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_reset_opened_team(): void
    {
        // Mark team as opened
        $this->ctTeam->update([
            'opened_at'      => now(),
            'member_names'   => ['A', 'B', 'C', 'D'],
            'certificate_no' => 'CT-2611-TESTTEST',
            'wrong_count'    => 3,
        ]);

        $this->actingAs($this->superadmin)
            ->post(route('admin.ct.teams.reset', $this->ctTeam))
            ->assertRedirect();

        $this->ctTeam->refresh();
        $this->assertNull($this->ctTeam->opened_at);
        $this->assertNull($this->ctTeam->member_names);
        $this->assertNull($this->ctTeam->certificate_no);
        $this->assertSame(0, (int) $this->ctTeam->wrong_count);
    }

    // ── 10. Fix names ────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_fix_member_names(): void
    {
        $this->ctTeam->update(['opened_at' => now(), 'member_names' => ['Lama1', 'Lama2', 'Lama3', 'Lama4']]);

        $newNames = ['Baru Satu', 'Baru Dua', 'Baru Tiga', 'Baru Empat'];

        $this->actingAs($this->superadmin)
            ->post(route('admin.ct.teams.fix-names', $this->ctTeam), [
                'names' => $newNames,
            ])
            ->assertRedirect();

        $this->ctTeam->refresh();
        $this->assertSame($newNames, $this->ctTeam->member_names);
    }

    /** @test */
    public function fix_names_requires_exactly_four_names(): void
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.ct.teams.fix-names', $this->ctTeam), [
                'names' => ['Hanya', 'Tiga', 'Orang'],
            ])
            ->assertSessionHasErrors('names');
    }

    // ── 11. Artisan ct:reshuffle ─────────────────────────────────────────────

    /** @test */
    public function artisan_reshuffle_updates_unopened_teams_and_skips_opened(): void
    {
        // ctTeam is unopened
        $codeBefore = $this->gen->deriveCode($this->ctTeam->params);

        // Create an opened team in the same class
        $openedParams = $this->gen->generate();
        $openedTeam = CtTeam::create([
            'ct_class_id'    => $this->ctClass->id,
            'number'         => 2,
            'params'         => $openedParams,
            'code_hash'      => $openedParams['code_hash'],
            'opened_at'      => now(),
            'member_names'   => ['Satu', 'Dua', 'Tiga', 'Empat'],
            'certificate_no' => 'CT-2611-OPENED01',
        ]);

        $this->artisan('ct:reshuffle', ['kelas' => $this->ctClass->name])
            ->expectsOutputToContain('Berhasil me-reshuffle 1 kelompok')
            ->expectsOutputToContain('Dilewati')
            ->assertSuccessful();

        $this->ctTeam->refresh();
        $this->assertArrayHasKey('log_lines', $this->ctTeam->params);
        $this->assertArrayHasKey('folder_files', $this->ctTeam->params);

        // Code and hash unchanged
        $codeAfter = $this->gen->deriveCode($this->ctTeam->params);
        $this->assertSame($codeBefore, $codeAfter);
        $this->assertTrue($this->ctTeam->verifyCode($codeBefore));

        // Opened team params untouched
        $openedTeam->refresh();
        $this->assertEquals($openedParams, $openedTeam->params);
    }
}
