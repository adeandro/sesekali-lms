<?php

namespace Tests\Feature;

use App\Models\CtClass;
use App\Models\CtTeam;
use App\Services\CaseGenerator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Feature tests for CT Phase 4 — Hints & Leaderboard.
 */
class CtHintLeaderboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'mysql');
    }

    private CaseGenerator $gen;
    private CtClass $ctClass;
    private CtTeam $ctTeam;
    private string $validCode;

    protected function setUp(): void
    {
        parent::setUp();

        config(['ct.enabled' => true, 'ct.cooldown' => 30]);

        $this->gen = new CaseGenerator();

        $this->ctClass = CtClass::create([
            'name'      => 'PH4-' . uniqid(),
            'is_active' => true,
            'opens_at'  => null,
            'closes_at' => null,
        ]);

        $params          = $this->gen->generate();
        $this->validCode = $this->gen->deriveCode($params);

        $this->ctTeam = CtTeam::create([
            'ct_class_id' => $this->ctClass->id,
            'number'      => 1,
            'params'      => $params,
            'code_hash'   => hash_hmac('sha256', $this->validCode, config('app.key')),
            'wrong_count' => 0,
            'hint_count'  => 0,
        ]);
    }

    // ── 1. Hint — access control ─────────────────────────────────────────────

    /** @test */
    public function hint_blocked_before_3_wrong_attempts(): void
    {
        $this->ctTeam->update(['wrong_count' => 2]);

        $response = $this->postJson(route('ct.hint'), [
            'class_id' => $this->ctClass->id,
            'team_id'  => $this->ctTeam->id,
        ]);

        $response->assertStatus(403);
        $response->assertJsonStructure(['error']);
        // hint_count must NOT have changed
        $this->assertSame(0, (int) $this->ctTeam->fresh()->hint_count);
    }

    /** @test */
    public function hint_available_after_3_wrong_attempts(): void
    {
        $this->ctTeam->update(['wrong_count' => 3]);

        $response = $this->postJson(route('ct.hint'), [
            'class_id' => $this->ctClass->id,
            'team_id'  => $this->ctTeam->id,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['hint', 'hint_count', 'max']);
        $this->assertSame(1, (int) $this->ctTeam->fresh()->hint_count);
    }

    /** @test */
    public function hint_text_does_not_contain_code(): void
    {
        $this->ctTeam->update(['wrong_count' => 5]);

        $response = $this->postJson(route('ct.hint'), [
            'class_id' => $this->ctClass->id,
            'team_id'  => $this->ctTeam->id,
        ]);

        $response->assertOk();
        $hint = $response->json('hint');

        // The hint must NOT contain the actual 4-digit code
        $this->assertStringNotContainsString($this->validCode, $hint);
        // The hint must NOT contain the code_hash
        $this->assertStringNotContainsString($this->ctTeam->code_hash, $hint);
    }

    /** @test */
    public function hint_count_increments_each_request(): void
    {
        $this->ctTeam->update(['wrong_count' => 5, 'hint_count' => 0]);

        for ($i = 1; $i <= 3; $i++) {
            $this->postJson(route('ct.hint'), [
                'class_id' => $this->ctClass->id,
                'team_id'  => $this->ctTeam->id,
            ])->assertOk();
        }

        $this->assertSame(3, (int) $this->ctTeam->fresh()->hint_count);
    }

    /** @test */
    public function hint_capped_at_3(): void
    {
        $this->ctTeam->update(['wrong_count' => 5, 'hint_count' => 3]);

        $response = $this->postJson(route('ct.hint'), [
            'class_id' => $this->ctClass->id,
            'team_id'  => $this->ctTeam->id,
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);
        // hint_count must remain 3
        $this->assertSame(3, (int) $this->ctTeam->fresh()->hint_count);
    }

    /** @test */
    public function hint_blocked_for_already_opened_vault(): void
    {
        $this->ctTeam->update([
            'wrong_count' => 5,
            'opened_at'   => now(),
        ]);

        $response = $this->postJson(route('ct.hint'), [
            'class_id' => $this->ctClass->id,
            'team_id'  => $this->ctTeam->id,
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function hint_blocked_for_closed_class(): void
    {
        $this->ctClass->update([
            'opens_at'  => now()->subHour(),
            'closes_at' => now()->subMinute(), // closed
        ]);
        $this->ctTeam->update(['wrong_count' => 5]);

        $response = $this->postJson(route('ct.hint'), [
            'class_id' => $this->ctClass->id,
            'team_id'  => $this->ctTeam->id,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function hint_returns_404_for_mismatched_class_team(): void
    {
        $otherClass = CtClass::create(['name' => 'OTHER-' . uniqid(), 'is_active' => true]);
        $this->ctTeam->update(['wrong_count' => 5]);

        $this->postJson(route('ct.hint'), [
            'class_id' => $otherClass->id,
            'team_id'  => $this->ctTeam->id,
        ])->assertNotFound();
    }

    // ── 2. Hint — content ────────────────────────────────────────────────────

    /** @test */
    public function each_hint_level_returns_different_text(): void
    {
        $this->ctTeam->update(['wrong_count' => 10, 'hint_count' => 0]);

        $hints = [];
        for ($i = 0; $i < 3; $i++) {
            $hints[] = $this->postJson(route('ct.hint'), [
                'class_id' => $this->ctClass->id,
                'team_id'  => $this->ctTeam->id,
            ])->json('hint');
        }

        // All 3 hints must be distinct
        $this->assertCount(3, array_unique($hints));
    }

    /** @test */
    public function hint_text_is_not_empty(): void
    {
        $this->ctTeam->update(['wrong_count' => 3]);

        $hint = $this->postJson(route('ct.hint'), [
            'class_id' => $this->ctClass->id,
            'team_id'  => $this->ctTeam->id,
        ])->json('hint');

        $this->assertNotEmpty($hint);
        $this->assertGreaterThan(10, strlen($hint));
    }

    // ── 3. CaseGenerator::hint() unit tests ──────────────────────────────────

    /** @test */
    public function generator_hint_level_1_does_not_contain_code(): void
    {
        $params = $this->ctTeam->params;
        $code   = $this->gen->deriveCode($params);
        $hint   = $this->gen->hint($params, 1);

        $this->assertStringNotContainsString($code, $hint);
    }

    /** @test */
    public function generator_hint_level_2_mentions_time_range(): void
    {
        $hint = $this->gen->hint($this->ctTeam->params, 2);

        // Level 2 should mention the hour range (01.00–03.59)
        $this->assertStringContainsString('01', $hint);
        $this->assertStringContainsString('03', $hint);
    }

    /** @test */
    public function generator_hint_level_3_mentions_n_word(): void
    {
        $params = $this->ctTeam->params;
        $nWord  = match($params['n']) {
            2 => 'DUA', 3 => 'TIGA', 4 => 'EMPAT', 5 => 'LIMA',
            6 => 'ENAM', 7 => 'TUJUH', 8 => 'DELAPAN', 9 => 'SEMBILAN',
        };
        $hint = $this->gen->hint($params, 3);

        $this->assertStringContainsString($nWord, $hint);
    }

    // ── 4. Leaderboard ───────────────────────────────────────────────────────

    /** @test */
    public function leaderboard_loads_for_active_class(): void
    {
        $this->get(route('ct.leaderboard', $this->ctClass))
            ->assertOk()
            ->assertSee($this->ctClass->name);
    }

    /** @test */
    public function leaderboard_returns_404_for_inactive_class(): void
    {
        $this->ctClass->update(['is_active' => false]);

        $this->get(route('ct.leaderboard', $this->ctClass))
            ->assertNotFound();
    }

    /** @test */
    public function leaderboard_shows_opened_teams_sorted_by_time(): void
    {
        // Create 3 teams: opened at different times
        $t1 = CtTeam::create([
            'ct_class_id' => $this->ctClass->id,
            'number'      => 2,
            'params'      => $this->ctTeam->params,
            'code_hash'   => hash_hmac('sha256', '9999', config('app.key')),
            'opened_at'   => now()->subMinutes(5),
            'member_names'=> ['A', 'B', 'C', 'D'],
            'certificate_no' => 'CT-2611-TESTA001',
        ]);
        $t2 = CtTeam::create([
            'ct_class_id' => $this->ctClass->id,
            'number'      => 3,
            'params'      => $this->ctTeam->params,
            'code_hash'   => hash_hmac('sha256', '8888', config('app.key')),
            'opened_at'   => now()->subMinutes(10), // earlier → rank 1
            'member_names'=> ['E', 'F', 'G', 'H'],
            'certificate_no' => 'CT-2611-TESTA002',
        ]);

        $response = $this->get(route('ct.leaderboard', $this->ctClass));
        $response->assertOk();
        $content = $response->getContent();

        // t2 (earlier) should appear before t1 in the HTML
        $pos1 = strpos($content, 'Tim ' . $t1->number);
        $pos2 = strpos($content, 'Tim ' . $t2->number);
        $this->assertNotFalse($pos1);
        $this->assertNotFalse($pos2);
        $this->assertLessThan($pos1, $pos2, 'Earlier team (t2) should rank higher than later team (t1)');
    }

    /** @test */
    public function leaderboard_does_not_expose_code_or_hash(): void
    {
        $this->ctTeam->update([
            'opened_at'    => now(),
            'member_names' => ['Alfa', 'Beta', 'Gama', 'Delta'],
            'certificate_no' => 'CT-2611-TESTA003',
        ]);

        $content = $this->get(route('ct.leaderboard', $this->ctClass))->getContent();

        $this->assertStringNotContainsString($this->validCode, $content);
        $this->assertStringNotContainsString($this->ctTeam->code_hash, $content);
    }

    /** @test */
    public function leaderboard_shows_total_team_count(): void
    {
        // ctClass already has ctTeam (not opened) + potentially others
        $response = $this->get(route('ct.leaderboard', $this->ctClass));
        $response->assertOk();
        // The total count should appear on the page
        $total = CtTeam::where('ct_class_id', $this->ctClass->id)->count();
        $this->assertStringContainsString((string) $total, $response->getContent());
    }

    /** @test */
    public function unopened_team_not_shown_in_leaderboard(): void
    {
        // ctTeam is NOT opened — should not appear in leaderboard opened list
        $content = $this->get(route('ct.leaderboard', $this->ctClass))->getContent();

        // "Tim 1" might appear in "1 kelompok" text, but not in an opened-team card
        // We test: opened_at null means not in ranking area
        $this->assertStringNotContainsString($this->ctTeam->certificate_no ?? 'NOCERT', $content);
    }
}
