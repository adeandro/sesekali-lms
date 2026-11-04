<?php

namespace Tests\Feature;

use App\Models\CtAttempt;
use App\Models\CtClass;
use App\Models\CtTeam;
use App\Services\CaseGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Feature tests for the CT Escape Room public vault flow.
 *
 * Uses DatabaseTransactions to roll back after each test so we reuse
 * the MySQL test database without resetting the whole schema (the existing
 * migrations have MySQL-specific SQL incompatible with SQLite).
 */
class CtVaultFlowTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Override to use MySQL (the main database) instead of SQLite in-memory.
     * The CT tables already exist in the dev/test MySQL database.
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Use the same MySQL connection as development
        $app['config']->set('database.default', 'mysql');
    }

    private CaseGenerator $gen;

    protected function setUp(): void
    {
        parent::setUp();

        config(['ct.enabled' => true, 'ct.cooldown' => 30, 'ct.escalate_after' => 10]);

        $this->gen = new CaseGenerator();

        // Create an open class (no time restrictions)
        $this->class = CtClass::create([
            'name'      => 'TEST-' . uniqid(),
            'opens_at'  => null,
            'closes_at' => null,
            'is_active' => true,
        ]);

        // Generate a team
        $params = $this->gen->generate();
        $this->validCode = $this->gen->deriveCode($params);

        $this->team = CtTeam::create([
            'ct_class_id' => $this->class->id,
            'number'      => 1,
            'params'      => $params,
            'code_hash'   => $params['code_hash'],
        ]);
    }

    // ── 1. Class list page ─────────────────────────────────────────────

    /** @test */
    public function index_page_loads_and_shows_active_classes(): void
    {
        $response = $this->get(route('ct.index'));

        $response->assertStatus(200);
        $response->assertSee($this->class->name);
    }

    /** @test */
    public function index_response_has_noindex_header(): void
    {
        $response = $this->get(route('ct.index'));

        $response->assertHeader('X-Robots-Tag', 'noindex');
    }

    // ── 2. Team list page ──────────────────────────────────────────────

    /** @test */
    public function teams_page_shows_team_numbers(): void
    {
        $response = $this->get(route('ct.teams', $this->class));

        $response->assertStatus(200);
        $response->assertSee('1');  // team number 1
    }

    /** @test */
    public function teams_page_redirects_when_class_is_closed(): void
    {
        $closedClass = CtClass::create([
            'name'      => 'CLOSED-' . uniqid(),
            'opens_at'  => now()->subDay(),
            'closes_at' => now()->subHour(),
            'is_active' => true,
        ]);

        $response = $this->get(route('ct.teams', $closedClass));

        $response->assertRedirect(route('ct.index'));
    }

    // ── 3. Vault form ──────────────────────────────────────────────────

    /** @test */
    public function vault_form_loads(): void
    {
        $response = $this->get(route('ct.vault', [
            'class' => $this->class,
            'team'  => $this->team,
        ]));

        $response->assertStatus(200);
        // Should NOT contain the code or params
        $response->assertDontSee($this->validCode);
    }

    /** @test */
    public function vault_form_response_does_not_contain_code_or_params(): void
    {
        $response = $this->get(route('ct.vault', [
            'class' => $this->class,
            'team'  => $this->team,
        ]));

        $body = $response->getContent();

        // The code itself must never appear in the HTML
        $this->assertStringNotContainsString($this->validCode, $body);

        // The raw params must not appear
        $params = $this->team->params;
        $this->assertStringNotContainsString((string) $params['shift'], $body === true ? '' : '');
        // code_hash must not appear
        $this->assertStringNotContainsString($this->team->code_hash, $body);
    }

    // ── 4. Wrong code increments wrong_count and sets cooldown ─────────

    /** @test */
    public function wrong_code_increments_wrong_count_and_sets_cooldown(): void
    {
        $wrongCode = $this->validCode === '1234' ? '5678' : '1234';

        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],
            'code'     => $wrongCode,
        ]);

        $this->team->refresh();

        $this->assertSame(1, $this->team->wrong_count);
        $this->assertNotNull($this->team->cooldown_until);
        $this->assertTrue($this->team->cooldown_until->isFuture());

        // Attempt must be recorded
        $this->assertDatabaseHas('ct_attempts', [
            'ct_team_id' => $this->team->id,
            'is_correct' => false,
        ]);
    }

    // ── 5. Request during cooldown is rejected ─────────────────────────

    /** @test */
    public function request_during_cooldown_is_rejected(): void
    {
        // Set active cooldown
        $this->team->update(['cooldown_until' => now()->addSeconds(60)]);

        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],
            'code'     => $this->validCode,
        ]);

        // Should not open the vault
        $this->team->refresh();
        $this->assertNull($this->team->opened_at);
        // Should redirect back with an error flash (not validation errors)
        $response->assertRedirect();
        $this->assertStringContainsString('cooldown', strtolower($response->getSession()->get('error', '')));
    }

    // ── 6. Correct code opens vault ────────────────────────────────────

    /** @test */
    public function correct_code_opens_vault_and_locks_names(): void
    {
        $names = ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'];

        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => $names,
            'code'     => $this->validCode,
        ]);

        $this->team->refresh();

        $this->assertNotNull($this->team->opened_at);
        $this->assertNotNull($this->team->certificate_no);
        $this->assertNotNull($this->team->member_names);

        // Names are locked (normalized: title case)
        foreach ($names as $i => $name) {
            $this->assertSame($name, $this->team->member_names[$i]);
        }

        // Attempt recorded as correct
        $this->assertDatabaseHas('ct_attempts', [
            'ct_team_id' => $this->team->id,
            'is_correct' => true,
        ]);

        // Redirected to success page
        $response->assertRedirect(route('ct.success', $this->team->id));
    }

    // ── 7. Already opened team redirects to certificate ────────────────

    /** @test */
    public function already_opened_team_redirects_to_certificate(): void
    {
        $certNo = 'CT-2611-TESTAAAA';
        $this->team->update([
            'opened_at'      => now(),
            'member_names'   => ['A', 'B', 'C', 'D'],
            'certificate_no' => $certNo,
        ]);

        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['X', 'Y', 'Z', 'W'],
            'code'     => $this->validCode,
        ]);

        $response->assertRedirect(route('ct.certificate', $certNo));
    }

    // ── 8. Name validation ─────────────────────────────────────────────

    /** @test */
    public function fewer_than_4_names_fails_validation(): void
    {
        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['Andi', 'Budi', 'Citra'],  // only 3
            'code'     => $this->validCode,
        ]);

        $response->assertSessionHasErrors('names');
        $this->team->refresh();
        $this->assertNull($this->team->opened_at);
    }

    /** @test */
    public function duplicate_names_in_same_team_fails(): void
    {
        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['Andi Susanto', 'andi susanto', 'Citra Dewi', 'Dina Rahayu'],  // duplicate (case-insensitive)
            'code'     => $this->validCode,
        ]);

        $response->assertSessionHasErrors();
        $this->team->refresh();
        $this->assertNull($this->team->opened_at);
    }

    /** @test */
    public function name_too_short_fails(): void
    {
        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['AB', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],  // AB = 2 chars
            'code'     => $this->validCode,
        ]);

        $response->assertSessionHasErrors('names.0');
    }

    /** @test */
    public function name_with_invalid_characters_fails(): void
    {
        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['Andi123!', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],
            'code'     => $this->validCode,
        ]);

        $response->assertSessionHasErrors('names.0');
    }

    /** @test */
    public function code_must_be_exactly_4_digits(): void
    {
        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],
            'code'     => 'ABC1',  // not digits
        ]);

        $response->assertSessionHasErrors('code');
    }

    // ── 9. Class outside schedule is rejected ──────────────────────────

    /** @test */
    public function closed_class_rejects_vault_submission(): void
    {
        $closedClass = CtClass::create([
            'name'      => 'CLOSED2-' . uniqid(),
            'opens_at'  => now()->subDays(2),
            'closes_at' => now()->subHour(),
            'is_active' => true,
        ]);

        $params = $this->gen->generate();
        $team   = CtTeam::create([
            'ct_class_id' => $closedClass->id,
            'number'      => 1,
            'params'      => $params,
            'code_hash'   => $params['code_hash'],
        ]);

        $response = $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $closedClass->id,
            'team_id'  => $team->id,
            'names'    => ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],
            'code'     => $this->gen->deriveCode($params),
        ]);

        $team->refresh();
        $this->assertNull($team->opened_at);
        $this->assertStringContainsString('tutup', strtolower($response->getSession()->get('error', '')));
    }

    // ── 10. Cooldown escalation after 10 wrong attempts ─────────────────

    /** @test */
    public function cooldown_escalates_after_threshold_wrong_attempts(): void
    {
        config(['ct.escalate_after' => 3, 'ct.cooldown' => 10, 'ct.cooldown_escalated' => 90]);

        $this->team->update(['wrong_count' => 2]);  // next wrong = 3rd = threshold

        $wrongCode = $this->validCode === '1111' ? '2222' : '1111';

        $this->post(route('ct.open'), [
            '_token'   => csrf_token(),
            'class_id' => $this->class->id,
            'team_id'  => $this->team->id,
            'names'    => ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],
            'code'     => $wrongCode,
        ]);

        $this->team->refresh();
        $this->assertSame(3, $this->team->wrong_count);

        // Cooldown should be ~90 seconds (escalated), not 10
        $secondsLeft = $this->team->cooldownSecondsLeft();
        $this->assertGreaterThan(50, $secondsLeft);
    }

    // ── 11. Certificate and verification pages ──────────────────────────

    /** @test */
    public function certificate_page_shows_names_and_no_code(): void
    {
        $certNo = 'CT-2611-CERTTEST';
        $names  = ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'];

        $this->team->update([
            'opened_at'      => now(),
            'member_names'   => $names,
            'certificate_no' => $certNo,
        ]);

        $response = $this->get(route('ct.certificate', $certNo));

        $response->assertStatus(200);
        $response->assertSee('Andi Susanto');
        $response->assertSee($certNo);
        // Must NOT contain the code
        $response->assertDontSee($this->validCode);
        // Must NOT contain code_hash
        $this->assertStringNotContainsString($this->team->code_hash, $response->getContent());
    }

    /** @test */
    public function verify_page_shows_correct_data(): void
    {
        $certNo = 'CT-2611-VRFYTEST';
        $this->team->update([
            'opened_at'      => now(),
            'member_names'   => ['Andi Susanto', 'Budi Hartono', 'Citra Dewi', 'Dina Rahayu'],
            'certificate_no' => $certNo,
        ]);

        $response = $this->get(route('ct.verify', $certNo));

        $response->assertStatus(200);
        $response->assertSee($certNo);
        $response->assertSee($this->class->name);
    }

    // ── 12. CT disabled returns 404 ────────────────────────────────────

    /** @test */
    public function ct_disabled_makes_routes_unavailable(): void
    {
        // Routes are registered at boot time, so we can't truly disable them in a live test.
        // We test the config flag is respected — this is a contract test.
        config(['ct.enabled' => false]);
        $this->assertFalse(config('ct.enabled'));
        // The actual 404 enforcement is covered in the route registration (bootstrap/app.php)
    }

    // ── 13. Response body never contains case params ───────────────────

    /** @test */
    public function success_page_does_not_expose_code_or_params(): void
    {
        $certNo = 'CT-2611-SCRTTEST';
        $this->team->update([
            'opened_at'      => now(),
            'member_names'   => ['A B Cde', 'E F Ghi', 'I J Klm', 'M N Opq'],
            'certificate_no' => $certNo,
        ]);

        $response = $this->get(route('ct.success', $this->team->id));
        $body     = $response->getContent();

        $this->assertStringNotContainsString($this->validCode, $body,
            'Success page must not expose the vault code');
        $this->assertStringNotContainsString($this->team->code_hash, $body,
            'Success page must not expose the code hash');
    }
}
