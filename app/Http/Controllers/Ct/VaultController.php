<?php

namespace App\Http\Controllers\Ct;

use App\Http\Controllers\Controller;
use App\Models\CtAttempt;
use App\Models\CtClass;
use App\Models\CtTeam;
use App\Services\CaseGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VaultController extends Controller
{
    // ── 1. Class list ──────────────────────────────────────────────────

    public function index()
    {
        $classes = CtClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('ct.index', compact('classes'));
    }

    // ── 2. Team list for a class ───────────────────────────────────────

    public function teams(CtClass $class)
    {
        if (! $class->isOpen()) {
            return redirect()->route('ct.index')
                ->with('error', 'Kelas ' . $class->name . ' tidak sedang buka. Cek jadwal dengan gurumu.');
        }

        $teams = $class->teams()->get();

        return view('ct.teams', compact('class', 'teams'));
    }

    // ── 3. Vault form ──────────────────────────────────────────────────

    public function vault(Request $request, CtClass $class, CtTeam $team)
    {
        if (! $class->isOpen()) {
            return redirect()->route('ct.index')
                ->with('error', 'Sesi kelas ' . $class->name . ' sudah tutup.');
        }

        if ($team->ct_class_id !== $class->id) {
            abort(404);
        }

        if ($team->isOpened()) {
            return redirect()->route('ct.certificate', $team->certificate_no);
        }

        $cooldownLeft = $team->cooldownSecondsLeft();

        // Re-render previously received hints so they persist on page reload
        $hints = [];
        if ($team->hint_count > 0) {
            $generator = app(CaseGenerator::class);
            for ($i = 1; $i <= $team->hint_count; $i++) {
                $hints[] = $generator->hint($team->params, $i);
            }
        }

        return view('ct.vault', compact('class', 'team', 'cooldownLeft', 'hints'));
    }

    // ── 4. Open vault POST ─────────────────────────────────────────────

    public function open(Request $request)
    {
        // 4a. Load class & team (generic 404 on failure — no info leak)
        $class = CtClass::find($request->input('class_id'));
        $team  = CtTeam::find($request->input('team_id'));

        if (! $class || ! $team || $team->ct_class_id !== $class->id) {
            abort(404);
        }

        // 4b. Class must be open
        if (! $class->isOpen()) {
            return back()->with('error', 'Sesi kelas sudah tutup.');
        }

        // Lock the row for this whole request so concurrent submits can't corrupt counts.
        DB::beginTransaction();
        try {
            $team = CtTeam::lockForUpdate()->find($team->id);

            // 4c. Already opened
            if ($team->isOpened()) {
                DB::rollBack();

                return redirect()->route('ct.certificate', $team->certificate_no);
            }

            // 4d. Cooldown check
            if ($team->isOnCooldown()) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->with('error', 'Masih dalam cooldown. Tunggu ' . $team->cooldownSecondsLeft() . ' detik lagi.');
            }

            // 4e. Validate names
            $validated = $this->validateNames($request);

            // 4f. Validate code format
            $code = $request->input('code', '');
            if (! preg_match('/^\d{4}$/', $code)) {
                DB::rollBack();
                throw ValidationException::withMessages(['code' => 'Kode harus tepat 4 digit angka.']);
            }

            // 4g. Record the attempt
            CtAttempt::create([
                'ct_team_id'     => $team->id,
                'member_names'   => $validated['names'],
                'submitted_code' => $code,
                'is_correct'     => $team->verifyCode($code),
                'ip'             => $request->ip(),
                'created_at'     => now(),
            ]);

            if (! $team->verifyCode($code)) {
                // Wrong code — increment count, set cooldown
                $newWrong   = $team->wrong_count + 1;
                $threshold  = config('ct.escalate_after', 10);
                $cooldownSec = $newWrong >= $threshold
                    ? config('ct.cooldown_escalated', 120)
                    : config('ct.cooldown', 30);

                $team->update([
                    'wrong_count'    => $newWrong,
                    'cooldown_until' => now()->addSeconds($cooldownSec),
                ]);

                DB::commit();

                return back()
                    ->withInput()
                    ->with('error', 'Kode salah. Coba lagi dalam ' . $cooldownSec . ' detik.');
            }

            // Correct! Open the vault.
            $certNo = $this->generateCertNo();

            $team->update([
                'opened_at'      => now(),
                'member_names'   => $validated['names'],
                'certificate_no' => $certNo,
            ]);

            DB::commit();

            return redirect()->route('ct.success', ['team' => $team->id]);

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->with('error', 'Terjadi kesalahan. Coba lagi.');
        }
    }

    // ── 5. Success page ────────────────────────────────────────────────

    public function success(CtTeam $team)
    {
        if (! $team->isOpened()) {
            return redirect()->route('ct.index');
        }

        $wrongCount = CtAttempt::where('ct_team_id', $team->id)
            ->where('is_correct', false)
            ->count();

        return view('ct.success', compact('team', 'wrongCount'));
    }

    // ── 6. Certificate HTML ────────────────────────────────────────────

    public function certificate(string $certificateNo)
    {
        $team = CtTeam::with('ctClass')
            ->where('certificate_no', $certificateNo)
            ->firstOrFail();

        $wrongCount = CtAttempt::where('ct_team_id', $team->id)
            ->where('is_correct', false)
            ->count();

        return view('ct.certificate', compact('team', 'wrongCount'));
    }

    // ── 7. Certificate PDF ─────────────────────────────────────────────

    public function certificatePdf(string $certificateNo)
    {
        $team = CtTeam::with('ctClass')
            ->where('certificate_no', $certificateNo)
            ->firstOrFail();

        $wrongCount = CtAttempt::where('ct_team_id', $team->id)
            ->where('is_correct', false)
            ->count();

        $pdf = Pdf::loadView('ct.certificate_pdf', compact('team', 'wrongCount'))
            ->setPaper('a4', 'landscape');

        $filename = 'sertifikat-' . strtolower($team->certificate_no) . '.pdf';

        return $pdf->download($filename);
    }

    // ── 8. Verification page ───────────────────────────────────────────

    public function verify(string $certificateNo)
    {
        $team = CtTeam::with('ctClass')
            ->where('certificate_no', $certificateNo)
            ->firstOrFail();

        return view('ct.verify', compact('team'));
    }

    // ── 9. Request a hint ──────────────────────────────────────────────

    /**
     * Grant a staged hint to a team that has made enough wrong attempts.
     * Hints are purely server-side text — no params/code/hash in response.
     *
     * POST /ct/petunjuk
     * Requires: class_id, team_id, _token
     * Returns: JSON { hint: string, hint_count: int, max: int }
     */
    public function hint(Request $request)
    {
        $class = CtClass::find($request->input('class_id'));
        $team  = CtTeam::find($request->input('team_id'));

        if (! $class || ! $team || $team->ct_class_id !== $class->id) {
            abort(404);
        }

        if (! $class->isOpen()) {
            return response()->json(['error' => 'Sesi kelas sudah tutup.'], 403);
        }

        if ($team->isOpened()) {
            return response()->json(['error' => 'Brankas sudah terbuka.'], 400);
        }

        // Minimum wrong attempts before hints unlock
        $minWrong = 3;
        if ($team->wrong_count < $minWrong) {
            return response()->json([
                'error' => "Petunjuk tersedia setelah {$minWrong} percobaan salah.",
            ], 403);
        }

        $maxHints = 3;
        if ($team->hint_count >= $maxHints) {
            return response()->json([
                'error'      => 'Semua petunjuk sudah diberikan.',
                'hint_count' => $team->hint_count,
                'max'        => $maxHints,
            ], 400);
        }

        // Increment hint count atomically
        $team->increment('hint_count');
        $team->refresh();

        $generator = app(CaseGenerator::class);
        $hint      = $generator->hint($team->params, $team->hint_count);

        return response()->json([
            'hint'       => $hint,
            'hint_count' => $team->hint_count,
            'max'        => $maxHints,
        ]);
    }

    // ── 10. Leaderboard ────────────────────────────────────────────────

    /**
     * Public leaderboard for a class — shows opened teams sorted by time.
     * Does NOT expose member names or codes.
     *
     * GET /ct/kelas/{class}/papan-skor
     */
    public function leaderboard(CtClass $class)
    {
        if (! $class->is_active) {
            abort(404);
        }

        $opened = CtTeam::where('ct_class_id', $class->id)
            ->whereNotNull('opened_at')
            ->orderBy('opened_at')
            ->get(['id', 'number', 'opened_at', 'wrong_count', 'hint_count', 'certificate_no', 'member_names']);

        $total = CtTeam::where('ct_class_id', $class->id)->count();

        return view('ct.leaderboard', compact('class', 'opened', 'total'));
    }

    // ── Helpers ────────────────────────────────────────────────────────

    /**
     * Validate member names array.
     * Rules: exactly 4, each 3-60 chars, letters/spaces/dots/apostrophes/hyphens,
     * no duplicates (case-insensitive), trim and title-case.
     */
    private function validateNames(Request $request): array
    {
        $request->validate([
            'names'    => ['required', 'array', 'size:4'],
            'names.*'  => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[\pL\s.\'\-]+$/u',
            ],
        ], [
            'names.required'   => 'Semua 4 nama anggota wajib diisi.',
            'names.size'       => 'Harus ada tepat 4 nama anggota.',
            'names.*.required' => 'Nama tidak boleh kosong.',
            'names.*.min'      => 'Nama minimal 3 karakter.',
            'names.*.max'      => 'Nama maksimal 60 karakter.',
            'names.*.regex'    => 'Nama hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.',
        ]);

        // Normalize: trim & title-case
        $names = array_map(
            fn ($n) => mb_convert_case(trim(preg_replace('/\s+/', ' ', $n)), MB_CASE_TITLE, 'UTF-8'),
            $request->input('names')
        );

        // Duplicate check (case-insensitive)
        $lowerNames = array_map('mb_strtolower', $names);
        if (count($lowerNames) !== count(array_unique($lowerNames))) {
            throw ValidationException::withMessages([
                'names.0' => 'Nama anggota dalam satu kelompok tidak boleh sama.',
            ]);
        }

        return ['names' => $names];
    }

    /**
     * Generate a unique, unpredictable certificate number.
     * Format: CT-YYMM-XXXXXXXX (8 uppercase alphanumeric, no ambiguous chars).
     */
    private function generateCertNo(): string
    {
        $prefix = 'CT-' . now()->format('ym') . '-';
        // Characters without ambiguous ones: 0/O, 1/I/L
        $chars  = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $maxTries = 20;

        for ($i = 0; $i < $maxTries; $i++) {
            $suffix = '';
            for ($j = 0; $j < 8; $j++) {
                $suffix .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $no = $prefix . $suffix;

            if (! CtTeam::where('certificate_no', $no)->exists()) {
                return $no;
            }
        }

        // Fallback: use UUID suffix
        return $prefix . strtoupper(substr(str_replace('-', '', Str::uuid()), 0, 8));
    }
}
