<?php

namespace App\Services;

use App\Exceptions\ElectionException;
use App\Models\ElectionAuditLog;
use App\Models\ElectionCandidate;
use App\Models\ElectionPeriod;
use App\Models\ElectionVoter;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ElectionService
{
    private const TOKEN_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    // ── Token ──────────────────────────────────────────────────────────────

    public function generateToken(): string
    {
        $alpha  = self::TOKEN_ALPHABET;
        $len    = strlen($alpha);
        $token  = '';
        for ($i = 0; $i < 8; $i++) {
            $token .= $alpha[random_int(0, $len - 1)];
        }
        return $token;
    }

    public function hashToken(string $token): string
    {
        return hash_hmac('sha256', strtoupper($token), config('app.key'));
    }

    /** Format token untuk tampil: ABCD-EFGH */
    public function formatToken(string $token): string
    {
        return strtoupper(substr($token, 0, 4)) . '-' . strtoupper(substr($token, 4, 4));
    }

    // ── Generate Voters ────────────────────────────────────────────────────

    /**
     * Insert snapshot siswa aktif ke election_voters.
     * Chunk 500. Siswa tanpa NIS dilewati. Yang sudah ada tidak disentuh.
     * Return: ['inserted' => n, 'skipped_no_nis' => n]
     */
    public function generateVoters(ElectionPeriod $period): array
    {
        $eligibleClassIds = $period->eligible_class_ids; // null = semua kelas

        $query = User::aktif()->where('role', 'student')->with('classroom');

        if (! empty($eligibleClassIds)) {
            $query->whereIn('class_id', $eligibleClassIds);
        }

        // Ambil NIS yang sudah ada agar tidak duplikat
        $existingNis = DB::table('election_voters')
            ->where('period_id', $period->id)
            ->pluck('nis')
            ->flip();

        $inserted     = 0;
        $skippedNoNis = 0;

        $query->chunk(500, function ($students) use ($period, $existingNis, &$inserted, &$skippedNoNis) {
            $rows = [];
            foreach ($students as $student) {
                if (empty($student->nis)) {
                    $skippedNoNis++;
                    continue;
                }
                if ($existingNis->has($student->nis)) {
                    continue;
                }
                $rows[] = [
                    'period_id'    => $period->id,
                    'student_id'   => $student->id,
                    'nis'          => $student->nis,
                    'student_name' => $student->name,
                    'class_id'     => $student->class_id,
                    'class_name'   => $student->classroom?->name,
                    'token_hash'   => null,
                    'token_version' => 0,
                    'token_printed_at' => null,
                    'has_voted'    => false,
                    'voted_at'     => null,
                ];
                $inserted++;
            }
            if (! empty($rows)) {
                DB::table('election_voters')->insert($rows);
            }
        });

        ElectionAuditLog::record('voters.generated', $period->id, auth()->id(), [
            'inserted'      => $inserted,
            'skipped_no_nis' => $skippedNoNis,
        ]);

        return ['inserted' => $inserted, 'skipped_no_nis' => $skippedNoNis];
    }

    // ── Token Issue & Commit (PDF flow) ────────────────────────────────────

    /**
     * Siapkan token plaintext di memori untuk kumpulan voter.
     * Voter yang has_voted=true dilewati (tidak bisa dapat token baru).
     * Return: array of ['voter_id' => n, 'token' => '...', 'token_hash' => '...']
     */
    public function issueTokens(Collection $voters): array
    {
        $issued = [];
        foreach ($voters as $voter) {
            if ($voter->has_voted) {
                continue; // sudah memilih, tidak bisa cetak ulang
            }
            $token = $this->generateToken();
            $issued[] = [
                'voter_id'   => $voter->id,
                'token'      => $token,
                'token_hash' => $this->hashToken($token),
            ];
        }
        return $issued;
    }

    /**
     * Commit token hash ke DB dalam satu transaksi.
     * Dipanggil HANYA SETELAH PDF berhasil dirender.
     * Menghanguskan token lama dengan menaikkan token_version.
     */
    public function commitTokens(array $issued): void
    {
        DB::transaction(function () use ($issued) {
            foreach ($issued as $item) {
                DB::table('election_voters')
                    ->where('id', $item['voter_id'])
                    ->update([
                        'token_hash'       => $item['token_hash'],
                        'token_version'    => DB::raw('token_version + 1'),
                        'token_printed_at' => now(),
                    ]);
            }
        });
    }

    /**
     * Generate & render PDF kartu satu kelas (atau subset voter).
     * Alur: issueTokens → render PDF → commitTokens → return PDF string.
     * Jika render gagal, DB tidak berubah.
     */
    public function printVoterCards(ElectionPeriod $period, Collection $voters): string
    {
        if ($voters->isEmpty()) {
            throw new ElectionException('Tidak ada pemilih untuk dicetak.');
        }

        // Voter yang has_voted tidak bisa dicetak
        $printable = $voters->where('has_voted', false);
        if ($printable->isEmpty()) {
            throw new ElectionException('Semua pemilih di kelompok ini sudah memilih.');
        }

        // 1. Issue tokens (plaintext di memori saja)
        $issued = $this->issueTokens($printable);

        // 2. Bangun data kartu dengan token plaintext
        $tokenMap = collect($issued)->keyBy('voter_id');
        $cards = $printable->map(function ($voter) use ($tokenMap) {
            $t = $tokenMap->get($voter->id);
            return [
                'name'       => $voter->student_name,
                'class_name' => $voter->class_name ?? '-',
                'nis'        => $voter->nis,
                'token'      => $t ? $this->formatToken($t['token']) : '-',
            ];
        })->values();

        // Ambil nama sekolah dari Setting
        $settings   = \App\Models\Setting::pluck('value', 'key')->toArray();
        $schoolName = $settings['school_name'] ?? $settings['app_name'] ?? 'Sekolah';

        // 3. Render PDF — jika gagal, exception akan naik, DB tidak disentuh
        $pdf = Pdf::loadView('election.pdf.voter-cards', [
            'period'     => $period,
            'cards'      => $cards,
            'schoolName' => $schoolName,
        ]);
        $pdfContent = $pdf->output();

        // 4. PDF sukses → commit token hash ke DB
        $this->commitTokens($issued);

        ElectionAuditLog::record('tokens.printed', $period->id, auth()->id(), [
            'count'      => count($issued),
            'class_name' => $voters->first()?->class_name,
        ]);

        return $pdfContent;
    }

    // ── Verify Credentials ─────────────────────────────────────────────────

    /**
     * Verifikasi NIS + token untuk bilik suara.
     * Return ElectionVoter jika valid, null jika tidak.
     * Limiter: 5x/5 menit per NIS + 60x/10 menit per IP.
     */
    public function verifyCredentials(
        ElectionPeriod $period,
        string $nis,
        string $rawToken,
        string $ip
    ): ?ElectionVoter {
        $nisKey = 'booth-nis-' . $nis;
        $ipKey  = 'booth-ip-' . $ip;

        if (RateLimiter::tooManyAttempts($nisKey, 5)) {
            throw new ElectionException('Terlalu banyak percobaan untuk NIS ini. Coba lagi dalam ' . RateLimiter::availableIn($nisKey) . ' detik.');
        }
        if (RateLimiter::tooManyAttempts($ipKey, 60)) {
            throw new ElectionException('Terlalu banyak percobaan dari perangkat ini. Coba lagi dalam ' . RateLimiter::availableIn($ipKey) . ' detik.');
        }

        // Normalisasi token: uppercase, buang non-alphanumeric
        $normalized = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $rawToken));
        $tokenHash  = $this->hashToken($normalized);

        $voter = DB::table('election_voters')
            ->where('period_id', $period->id)
            ->where('nis', $nis)
            ->first();

        // 1. Cek apakah pemilih terdaftar dan token sesuai
        if (! $voter || $voter->token_hash === null || ! hash_equals($voter->token_hash, $tokenHash)) {
            RateLimiter::hit($nisKey, 300);  // 5 menit
            RateLimiter::hit($ipKey, 600);   // 10 menit
            return null;
        }

        // 2. Kredensial valid — bersihkan limiter
        RateLimiter::clear($nisKey);
        RateLimiter::clear($ipKey);

        // 3. Jika sudah memilih, beri pesan spesifik
        if ($voter->has_voted) {
            throw new ElectionException('Anda sudah menggunakan hak pilih.');
        }

        return ElectionVoter::find($voter->id);
    }

    // ── Cast Vote ──────────────────────────────────────────────────────────

    /**
     * Rekam suara. Transaksi + lockForUpdate pada voter DAN period.
     * candidateId NULL hanya jika allow_blank_vote.
     * TANPA increment apapun. TANPA log per suara.
     */
    public function castVote(
        ElectionPeriod $period,
        int $voterId,
        ?int $candidateId
    ): void {
        DB::transaction(function () use ($period, $voterId, $candidateId) {
            // Lock voter
            $voter = DB::table('election_voters')
                ->where('id', $voterId)
                ->where('period_id', $period->id)
                ->lockForUpdate()
                ->first();

            if (! $voter) {
                throw new ElectionException('Data pemilih tidak ditemukan.');
            }
            if ($voter->has_voted) {
                throw new ElectionException('Anda sudah memberikan suara.');
            }

            // Lock period untuk pastikan masih open
            $currentPeriod = DB::table('election_periods')
                ->where('id', $period->id)
                ->lockForUpdate()
                ->first();

            if (! $currentPeriod || $currentPeriod->status !== 'open') {
                throw new ElectionException('Pemilihan sudah tidak berlangsung.');
            }

            // Validasi candidateId
            if ($candidateId === null) {
                if (! $currentPeriod->allow_blank_vote) {
                    throw new ElectionException('Suara kosong tidak diizinkan.');
                }
            } else {
                $candidate = DB::table('election_candidates')
                    ->where('id', $candidateId)
                    ->where('period_id', $period->id)
                    ->first();
                if (! $candidate) {
                    throw new ElectionException('Paslon tidak ditemukan.');
                }
            }

            // Insert vote — UUID v4, no voter linkage, no timestamps
            DB::table('election_votes')->insert([
                'id'           => (string) Str::uuid(),
                'period_id'    => $period->id,
                'candidate_id' => $candidateId,
            ]);

            // Update voter: has_voted=true, voted_at dibulatkan ke jam
            DB::table('election_voters')
                ->where('id', $voterId)
                ->update([
                    'has_voted' => true,
                    'voted_at'  => now()->startOfHour(),
                ]);

            // TANPA log per suara (keamanan privasi)
        });
    }

    // ── Tally ──────────────────────────────────────────────────────────────

    /**
     * Hitung suara dari election_votes, bukan dari kolom denormalized.
     * Hanya boleh saat status closed.
     * Sertakan suara kosong, pemenang, dan penanda seri.
     *
     * @throws ElectionException jika status bukan closed
     */
    public function tally(ElectionPeriod $period): array
    {
        if ($period->status !== 'closed') {
            throw new ElectionException('Hasil hanya tersedia setelah pemilihan ditutup.');
        }

        $candidates = ElectionCandidate::where('period_id', $period->id)
            ->orderBy('number')
            ->get();

        // Hitung suara per kandidat dari DB
        $voteCounts = DB::table('election_votes')
            ->where('period_id', $period->id)
            ->whereNotNull('candidate_id')
            ->select('candidate_id', DB::raw('COUNT(*) as total'))
            ->groupBy('candidate_id')
            ->pluck('total', 'candidate_id');

        $blankCount = DB::table('election_votes')
            ->where('period_id', $period->id)
            ->whereNull('candidate_id')
            ->count();

        $totalVotes = DB::table('election_votes')
            ->where('period_id', $period->id)
            ->count();

        $results = $candidates->map(function ($c) use ($voteCounts) {
            return [
                'candidate' => $c,
                'votes'     => (int) ($voteCounts[$c->id] ?? 0),
            ];
        })->sortByDesc('votes')->values();

        // Penanda pemenang/seri
        $maxVotes = $results->max('votes') ?? 0;
        $topCount = $results->where('votes', $maxVotes)->count();
        $isTie    = $topCount > 1 && $maxVotes > 0;

        return [
            'results'     => $results,
            'blank_count' => $blankCount,
            'total_votes' => $totalVotes,
            'winner'      => (! $isTie && $maxVotes > 0) ? $results->first() : null,
            'is_tie'      => $isTie,
            'max_votes'   => $maxVotes,
        ];
    }

    // ── Participation ──────────────────────────────────────────────────────

    public function participation(ElectionPeriod $period): array
    {
        $total = DB::table('election_voters')->where('period_id', $period->id)->count();
        $voted = DB::table('election_voters')->where('period_id', $period->id)->where('has_voted', true)->count();

        // Per kelas
        $perClass = DB::table('election_voters')
            ->where('period_id', $period->id)
            ->select('class_name', DB::raw('COUNT(*) as total'), DB::raw('SUM(has_voted) as voted'))
            ->groupBy('class_name')
            ->orderBy('class_name')
            ->get()
            ->map(fn ($row) => [
                'class_name'  => $row->class_name ?? '-',
                'total'       => (int) $row->total,
                'voted'       => (int) $row->voted,
                'percentage'  => $row->total > 0 ? round(($row->voted / $row->total) * 100, 1) : 0,
            ]);

        return [
            'total'      => $total,
            'voted'      => $voted,
            'not_voted'  => $total - $voted,
            'percentage' => $total > 0 ? round(($voted / $total) * 100, 1) : 0,
            'per_class'  => $perClass,
        ];
    }

    // ── Consistency ────────────────────────────────────────────────────────

    public function consistency(ElectionPeriod $period): array
    {
        $votes        = DB::table('election_votes')->where('period_id', $period->id)->count();
        $votersVoted  = DB::table('election_voters')->where('period_id', $period->id)->where('has_voted', true)->count();
        $cardsIn      = $period->collected_cards_count;
        $match        = ($votes === $votersVoted && $cardsIn !== null && $votes === $cardsIn);
        $diff         = $cardsIn !== null ? ($cardsIn - $votes) : null;

        return [
            'votes_count'            => $votes,
            'voters_voted_count'     => $votersVoted,
            'collected_cards_count'  => $cardsIn,
            'votes_match_voters'     => $votes === $votersVoted,
            'votes_match_cards'      => $cardsIn !== null && $votes === $cardsIn,
            'match'                  => $match,
            'diff'                   => $diff,
        ];
    }

    // ── Publication ────────────────────────────────────────────────────────

    /**
     * Publikasikan / tarik profil kandidat untuk umum & proyektor.
     * Hanya boleh jika status ready, open, atau closed dan terdapat minimal 1 paslon.
     */
    public function publishCandidates(ElectionPeriod $period, bool $on): void
    {
        if ($on) {
            if ($period->status === 'draft') {
                throw new ElectionException('Kandidat tidak dapat dipublikasikan saat periode berstatus Draft.');
            }
            if ($period->candidates()->count() < 1) {
                throw new ElectionException('Minimal 1 paslon harus didaftarkan sebelum dapat dipublikasikan.');
            }
        }

        $period->update(['candidates_published' => $on]);

        ElectionAuditLog::record(
            $on ? 'candidates.publish' : 'candidates.unpublish',
            $period->id,
            auth()->id()
        );
    }

    /**
     * Publikasikan / tarik hasil suara untuk umum.
     * ON: Wajib status closed dan collected_cards_count tidak null.
     *     Jika consistency match false atau selisih != 0, wajib $force = true.
     * OFF: Boleh kapan saja.
     * Bersihkan cache hasil publik.
     */
    public function publishResults(ElectionPeriod $period, bool $on, bool $force = false): void
    {
        if ($on) {
            if ($period->status !== 'closed') {
                throw new ElectionException('Hasil hanya dapat dipublikasikan setelah pemilihan resmi ditutup.');
            }
            if ($period->collected_cards_count === null) {
                throw new ElectionException('Jumlah kertas fisik terkumpul belum diisi pada form rekonsiliasi.');
            }

            $consistency = $this->consistency($period);
            if ((! $consistency['match'] || $consistency['diff'] !== 0) && ! $force) {
                throw new ElectionException('Terdapat selisih rekonsiliasi antara kertas fisik dan suara digital.|confirm_force');
            }

            $period->update([
                'results_published'    => true,
                'results_published_at' => now(),
            ]);
        } else {
            $period->update([
                'results_published' => false,
            ]);
        }

        // Bersihkan cache publik
        Cache::forget("pilketos_public_results_{$period->id}");
        Cache::forget("pilketos_public_results");

        ElectionAuditLog::record(
            $on ? 'results.publish' : 'results.unpublish',
            $period->id,
            auth()->id(),
            ['force' => $force]
        );
    }

    // ── Transition ─────────────────────────────────────────────────────────

    /**
     * Transisi status periode.
     * draft→ready, ready→draft, ready→open, open→closed, closed→open
     *
     * @param bool $force  Untuk ready→open saat ada voter tanpa token
     * @throws ElectionException
     */
    public function transition(ElectionPeriod $period, string $newStatus, bool $force = false): void
    {
        $current = $period->status;

        $allowed = [
            'draft'  => ['ready'],
            'ready'  => ['draft', 'open'],
            'open'   => ['closed'],
            'closed' => ['open'],
        ];

        if (! isset($allowed[$current]) || ! in_array($newStatus, $allowed[$current])) {
            throw new ElectionException("Transisi dari '{$current}' ke '{$newStatus}' tidak diizinkan.");
        }

        if ($newStatus === 'ready') {
            if ($period->candidates()->count() < 1) {
                throw new ElectionException('Minimal 1 paslon harus didaftarkan sebelum bisa Siap.');
            }
            if (! $period->hasPinSet()) {
                throw new ElectionException('PIN bilik belum diatur. Atur PIN bilik terlebih dahulu.');
            }
            if (DB::table('election_voters')->where('period_id', $period->id)->count() < 1) {
                throw new ElectionException('Daftar pemilih belum di-generate.');
            }
            // Pastikan tidak ada periode lain ready/open
            $conflict = ElectionPeriod::whereIn('status', ['ready', 'open'])
                ->where('id', '!=', $period->id)->exists();
            if ($conflict) {
                throw new ElectionException('Sudah ada periode lain berstatus Siap atau Berlangsung.');
            }
        }

        if ($newStatus === 'open') {
            // Cek tidak ada periode lain open/ready
            $conflict = ElectionPeriod::whereIn('status', ['ready', 'open'])
                ->where('id', '!=', $period->id)->exists();
            if ($conflict) {
                throw new ElectionException('Sudah ada periode lain berstatus Siap atau Berlangsung.');
            }
            // Cek voter tanpa token (belum dicetak)
            if (! $force) {
                $withoutToken = DB::table('election_voters')
                    ->where('period_id', $period->id)
                    ->whereNull('token_hash')
                    ->count();
                if ($withoutToken > 0) {
                    throw new ElectionException(
                        "Ada {$withoutToken} pemilih yang belum dicetak kartunya.|confirm_force"
                    );
                }
            }
        }

        $updates = ['status' => $newStatus];
        if ($newStatus === 'draft') {
            $updates['candidates_published'] = false;
        }
        if ($newStatus === 'open') {
            $updates['opened_at'] = now();
            if ($current === 'closed') {
                $updates['results_published']    = false;
                $updates['results_published_at'] = null;
                Cache::forget("pilketos_public_results_{$period->id}");
                Cache::forget("pilketos_public_results");
            }
        }
        if ($newStatus === 'closed') {
            $updates['closed_at'] = now();
        }

        $period->update($updates);

        ElectionAuditLog::record("period.{$newStatus}", $period->id, auth()->id(), [
            'from' => $current,
            'to'   => $newStatus,
        ]);
    }

    // ── Booth PIN ──────────────────────────────────────────────────────────

    public function setBoothPin(ElectionPeriod $period, string $plainPin): void
    {
        $period->update([
            'booth_pin_hash'        => Hash::make($plainPin),
            'booth_session_version' => DB::raw('booth_session_version + 1'),
        ]);
        ElectionAuditLog::record('booth.pin_changed', $period->id, auth()->id());
    }

    public function verifyBoothPin(ElectionPeriod $period, string $plainPin): bool
    {
        return $period->booth_pin_hash && Hash::check($plainPin, $period->booth_pin_hash);
    }

    public function revokeBoothSessions(ElectionPeriod $period): void
    {
        $period->increment('booth_session_version');
        ElectionAuditLog::record('booth.sessions_revoked', $period->id, auth()->id());
    }

    // ── Active Period ──────────────────────────────────────────────────────

    /**
     * Ambil periode yang sedang aktif (ready atau open).
     * Hanya boleh ada satu pada satu waktu.
     */
    public function getActivePeriod(): ?ElectionPeriod
    {
        return ElectionPeriod::whereIn('status', ['ready', 'open'])->first();
    }
}
