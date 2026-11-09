<?php

namespace App\Http\Controllers\Election;

use App\Exceptions\ElectionException;
use App\Http\Controllers\Controller;
use App\Models\ElectionCandidate;
use App\Models\ElectionPeriod;
use App\Models\ElectionVoter;
use App\Models\Setting;
use App\Services\ElectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class BoothController extends Controller
{
    public function __construct(private ElectionService $service) {}

    // ── Helper: ambil periode aktif dari request attributes ──────────────────

    private function period(Request $request): ElectionPeriod
    {
        return $request->attributes->get('election_period');
    }

    // ── GET /pilketos — halaman utama bilik ──────────────────────────────────
    // Tampilkan: form PIN bilik, form NIS+token, atau pesan "tidak ada pemilihan"

    public function index(Request $request)
    {
        // Middleware ElectionBoothAccess sudah inject election_period jika ada periode aktif.
        // Jika tidak ada, middleware redirect ke /pilketos/closed.
        // Jadi di sini election_period pasti ada.
        $period = $this->period($request);

        // Jika sesi bilik belum valid, tampilkan form PIN bilik
        if (! $this->hasValidBoothSession($request, $period)) {
            $this->clearVoterSession($request);
            return view('election.booth.index', [
                'period'    => $period,
                'step'      => 'pin',
                'settings'  => Setting::pluck('value', 'key')->toArray(),
            ]);
        }

        // Sesi bilik valid — tampilkan form token
        return view('election.booth.index', [
            'period'   => $period,
            'step'     => 'verify',
            'settings' => Setting::pluck('value', 'key')->toArray(),
        ]);
    }

    // ── POST /pilketos/pin — verifikasi PIN bilik ────────────────────────────

    public function pinPost(Request $request)
    {
        $period = $this->period($request);

        // Rate limit: 10x / 5 menit per IP
        $key = 'booth-pin-' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak percobaan PIN. Coba lagi dalam {$seconds} detik.");
        }

        $request->validate(['booth_pin' => 'required|string']);

        if (! $this->service->verifyBoothPin($period, $request->booth_pin)) {
            RateLimiter::hit($key, 300);
            return back()->with('error', 'PIN bilik salah.');
        }

        RateLimiter::clear($key);

        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        // Simpan versi PIN bilik untuk deteksi cabut sesi
        $request->session()->put('booth_period_id', $period->id);
        $request->session()->put('booth_session_version', $period->booth_session_version);

        return redirect()->route('election.index');
    }

    // ── POST /pilketos/verify — verifikasi token ─────────────────────────────

    public function verify(Request $request)
    {
        $period = $this->period($request);

        if (! $this->hasValidBoothSession($request, $period)) {
            return redirect()->route('election.index')
                ->with('error', 'Sesi bilik tidak valid. Masukkan PIN bilik terlebih dahulu.');
        }

        $request->validate([
            'token' => 'required|string|max:20',
        ]);

        $boothKey = hash('sha256', $request->session()->getId());

        try {
            $voter = $this->service->verifyToken(
                $period,
                $request->token,
                $boothKey,
                $request->ip()
            );
        } catch (ElectionException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Simpan sesi voter: [voter_id, period_id, confirmed=false, expires_at=now+3 menit]
        $request->session()->put('booth_voter_id', $voter->id);
        $request->session()->put('booth_voter_period_id', $period->id);
        $request->session()->put('booth_voter_confirmed', false);
        $request->session()->put('booth_voter_expires', now()->addMinutes(3)->timestamp);

        return redirect()->route('election.confirm');
    }

    // ── GET/POST /pilketos/konfirmasi — layar konfirmasi pemilih ─────────────

    public function confirm(Request $request)
    {
        $period = $this->period($request);

        if (! $this->hasValidBoothSession($request, $period)) {
            $this->clearVoterSession($request);
            return redirect()->route('election.index');
        }

        $voterId  = $request->session()->get('booth_voter_id');
        $periodId = $request->session()->get('booth_voter_period_id');
        $expires  = $request->session()->get('booth_voter_expires', 0);

        if (! $voterId || $periodId !== $period->id || now()->timestamp > $expires) {
            $this->clearVoterSession($request);
            return redirect()->route('election.index')
                ->with('error', 'Sesi sudah kadaluwarsa. Silakan masukkan kembali token.');
        }

        $voter = ElectionVoter::find($voterId);
        if (! $voter || $voter->has_voted || $voter->period_id !== $period->id) {
            $msg = ($voter && $voter->has_voted) ? 'Kartu ini sudah digunakan untuk memilih.' : 'Sesi tidak valid.';
            $this->clearVoterSession($request);
            return redirect()->route('election.index')->with('error', $msg);
        }

        // POST /pilketos/konfirmasi: set confirmed=true dan perbarui expires_at = now+3 menit
        if ($request->isMethod('post')) {
            $request->session()->put('booth_voter_confirmed', true);
            $request->session()->put('booth_voter_expires', now()->addMinutes(3)->timestamp);
            return redirect()->route('election.lembar');
        }

        // GET /pilketos/konfirmasi: tampilkan NAMA (besar), KELAS, NIS
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('election.booth.konfirmasi', compact('period', 'voter', 'settings'));
    }

    // ── POST /pilketos/batal — batalkan konfirmasi pemilih ────────────────────

    public function cancel(Request $request)
    {
        $this->clearVoterSession($request);
        return redirect()->route('election.index');
    }

    // ── GET /pilketos/lembar — kartu suara ───────────────────────────────────

    public function lembar(Request $request)
    {
        $period = $this->period($request);

        if (! $this->hasValidBoothSession($request, $period)) {
            return redirect()->route('election.index');
        }

        // Cek sesi voter: WAJIB confirmed=true dan belum kadaluwarsa
        $voterId   = $request->session()->get('booth_voter_id');
        $confirmed = $request->session()->get('booth_voter_confirmed');
        $periodId  = $request->session()->get('booth_voter_period_id');
        $expires   = $request->session()->get('booth_voter_expires', 0);

        if (! $voterId || ! $confirmed || $periodId !== $period->id || now()->timestamp > $expires) {
            $this->clearVoterSession($request);
            return redirect()->route('election.index');
        }

        $voter = ElectionVoter::find($voterId);
        if (! $voter || $voter->has_voted || $voter->period_id !== $period->id) {
            $msg = ($voter && $voter->has_voted) ? 'Kartu ini sudah digunakan untuk memilih.' : 'Sesi tidak valid.';
            $this->clearVoterSession($request);
            return redirect()->route('election.index')->with('error', $msg);
        }

        $candidates = ElectionCandidate::with(['chairman', 'vice'])
            ->where('period_id', $period->id)
            ->orderBy('number')
            ->get();
        $settings   = Setting::pluck('value', 'key')->toArray();

        return view('election.booth.lembar', compact('period', 'voter', 'candidates', 'settings'));
    }

    // ── POST /pilketos/pilih — kirim suara ───────────────────────────────────

    public function pilih(Request $request)
    {
        $period = $this->period($request);

        if (! $this->hasValidBoothSession($request, $period)) {
            return redirect()->route('election.index');
        }

        $voterId   = $request->session()->get('booth_voter_id');
        $confirmed = $request->session()->get('booth_voter_confirmed');
        $periodId  = $request->session()->get('booth_voter_period_id');
        $expires   = $request->session()->get('booth_voter_expires', 0);

        if (! $voterId || ! $confirmed || $periodId !== $period->id || now()->timestamp > $expires) {
            $this->clearVoterSession($request);
            return redirect()->route('election.index');
        }

        $request->validate([
            'candidate_id' => 'required|integer',
        ], [
            'candidate_id.required' => 'Silakan pilih salah satu pasangan calon.',
        ]);

        $candidateId = (int) $request->input('candidate_id');

        try {
            $this->service->castVote($period, (int) $voterId, $candidateId);
        } catch (ElectionException $e) {
            $this->clearVoterSession($request);
            return redirect()->route('election.index')->with('error', $e->getMessage());
        }

        // Hapus sesi voter (sesi bilik tetap aktif untuk pemilih berikutnya)
        $this->clearVoterSession($request);

        return redirect()->route('election.selesai');
    }

    // ── GET /pilketos/selesai ─────────────────────────────────────────────────

    public function selesai(Request $request)
    {
        $period   = $this->period($request);
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('election.booth.selesai', compact('period', 'settings'));
    }

    // ── GET /pilketos/closed ─────────────────────────────────────────────────

    public function closed()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('election.booth.closed', compact('settings'));
    }

    // ── Helper: validasi sesi bilik ───────────────────────────────────────────

    private function hasValidBoothSession(Request $request, ElectionPeriod $period): bool
    {
        return $request->session()->get('booth_period_id') === $period->id
            && $request->session()->get('booth_session_version') === $period->booth_session_version;
    }

    // ── Helper: hapus sesi voter ──────────────────────────────────────────────

    private function clearVoterSession(Request $request): void
    {
        $request->session()->forget([
            'booth_voter_id',
            'booth_voter_period_id',
            'booth_voter_confirmed',
            'booth_voter_expires',
        ]);
    }
}
