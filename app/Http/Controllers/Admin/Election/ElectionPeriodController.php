<?php

namespace App\Http\Controllers\Admin\Election;

use App\Exceptions\ElectionException;
use App\Http\Controllers\Controller;
use App\Models\ElectionAuditLog;
use App\Models\ElectionPeriod;
use App\Services\ElectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ElectionPeriodController extends Controller
{
    public function __construct(private ElectionService $service) {}

    public function index()
    {
        $periods = ElectionPeriod::latest()->paginate(15);
        return view('admin.pilketos.periods.index', compact('periods'));
    }

    public function create()
    {
        $classes = \App\Models\ClassRoom::orderBy('name')->get();
        return view('admin.pilketos.periods.form', ['period' => null, 'classes' => $classes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:191',
            'description'        => 'nullable|string|max:2000',
            'event_date'         => 'nullable|date',
            'eligible_class_ids' => 'nullable|array',
            'eligible_class_ids.*' => 'integer',
            'allow_blank_vote'   => 'nullable|boolean',
            'booth_pin'          => 'nullable|string|digits_between:4,8',
        ]);

        $pinHash = null;
        if (! empty($validated['booth_pin'])) {
            $pinHash = Hash::make($validated['booth_pin']);
        }

        $period = ElectionPeriod::create([
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'event_date'         => $validated['event_date'] ?? null,
            'eligible_class_ids' => ! empty($validated['eligible_class_ids']) ? $validated['eligible_class_ids'] : null,
            'allow_blank_vote'   => false,
            'booth_pin_hash'     => $pinHash,
            'status'             => 'draft',
            'created_by'         => auth()->id(),
        ]);

        ElectionAuditLog::record('period.created', $period->id, auth()->id(), ['title' => $period->title]);

        return redirect()->route('admin.pilketos.periods.show', $period)
            ->with('success', "Periode '{$period->title}' berhasil dibuat.");
    }

    public function show(ElectionPeriod $period)
    {
        $period->load(['candidates.chairman', 'candidates.vice', 'createdBy']);
        $participation = $this->service->participation($period);
        $logs = ElectionAuditLog::where('period_id', $period->id)->latest()->take(20)->get();

        // Ringkasan voter per kelas
        $voterSummary = DB::table('election_voters')
            ->where('period_id', $period->id)
            ->select('class_name', 'class_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(has_voted) as voted'),
                DB::raw('SUM(token_hash IS NOT NULL) as printed')
            )
            ->groupBy('class_name', 'class_id')
            ->orderBy('class_name')
            ->get();

        $boothUrl = url('/pilketos');
        $consistency = $this->service->consistency($period);

        return view('admin.pilketos.periods.show', compact(
            'period', 'participation', 'logs', 'voterSummary', 'boothUrl', 'consistency'
        ));
    }

    public function edit(ElectionPeriod $period)
    {
        if ($period->status === 'closed') {
            return redirect()->route('admin.pilketos.periods.show', $period)
                ->with('error', 'Periode yang sudah ditutup tidak dapat diedit.');
        }
        $classes = \App\Models\ClassRoom::orderBy('name')->get();
        return view('admin.pilketos.periods.form', compact('period', 'classes'));
    }

    public function update(Request $request, ElectionPeriod $period)
    {
        if ($period->status === 'closed') {
            return back()->with('error', 'Periode yang sudah ditutup tidak dapat diedit.');
        }

        $validated = $request->validate([
            'title'              => 'required|string|max:191',
            'description'        => 'nullable|string|max:2000',
            'event_date'         => 'nullable|date',
            'eligible_class_ids' => 'nullable|array',
            'eligible_class_ids.*' => 'integer',
            'allow_blank_vote'   => 'nullable|boolean',
        ]);

        $period->update([
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'event_date'         => $validated['event_date'] ?? null,
            'eligible_class_ids' => ! empty($validated['eligible_class_ids']) ? $validated['eligible_class_ids'] : null,
            'allow_blank_vote'   => false,
        ]);

        ElectionAuditLog::record('period.updated', $period->id, auth()->id());

        return redirect()->route('admin.pilketos.periods.show', $period)
            ->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(ElectionPeriod $period)
    {
        if (! in_array($period->status, ['draft', 'closed'])) {
            return back()->with('error', 'Hanya periode berstatus Draft atau Ditutup yang dapat dihapus.');
        }
        $title = $period->title;
        $period->delete();
        ElectionAuditLog::record('period.deleted', null, auth()->id(), ['title' => $title]);
        return redirect()->route('admin.pilketos.periods.index')
            ->with('success', "Periode '{$title}' berhasil dihapus.");
    }

    // ── Transition ──────────────────────────────────────────────────────────

    public function transition(Request $request, ElectionPeriod $period)
    {
        $request->validate([
            'to_status' => 'required|string|in:draft,ready,open,closed',
            'force'     => 'nullable|boolean',
        ]);

        try {
            $this->service->transition($period, $request->to_status, $request->boolean('force'));
        } catch (ElectionException $e) {
            $msg = $e->getMessage();
            // Jika pesan mengandung |confirm_force, kirim flag ke view
            if (str_contains($msg, '|confirm_force')) {
                return back()->with('error', Str::before($msg, '|confirm_force'))
                             ->with('confirm_force', true)
                             ->with('confirm_to_status', $request->to_status);
            }
            return back()->with('error', $msg);
        }

        $labels = ['draft' => 'Draft', 'ready' => 'Siap', 'open' => 'Berlangsung', 'closed' => 'Ditutup'];
        return back()->with('success', "Status diubah ke '{$labels[$request->to_status]}'.");
    }

    // ── Booth PIN ───────────────────────────────────────────────────────────

    public function setPin(Request $request, ElectionPeriod $period)
    {
        $request->validate([
            'booth_pin' => 'required|string|digits_between:4,8',
        ]);

        $this->service->setBoothPin($period, $request->booth_pin);

        return back()->with('success', 'PIN bilik berhasil diperbarui. Semua sesi bilik lama dicabut.');
    }

    public function revokeBoothSessions(ElectionPeriod $period)
    {
        $this->service->revokeBoothSessions($period);
        return back()->with('success', 'Semua sesi bilik suara telah dicabut.');
    }

    // ── Voters ──────────────────────────────────────────────────────────────

    public function generateVoters(ElectionPeriod $period)
    {
        if (! in_array($period->status, ['draft', 'ready'])) {
            return back()->with('error', 'Generate pemilih hanya saat Draft atau Siap.');
        }

        try {
            $result = $this->service->generateVoters($period);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }

        $msg = "{$result['inserted']} pemilih berhasil ditambahkan.";
        if ($result['skipped_no_nis'] > 0) {
            $msg .= " {$result['skipped_no_nis']} siswa dilewati (tanpa NIS).";
        }
        return back()->with('success', $msg);
    }

    // ── Results (hanya saat closed) ─────────────────────────────────────────

    public function results(ElectionPeriod $period)
    {
        if ($period->status !== 'closed') {
            abort(403, 'Hasil hanya tersedia setelah pemilihan ditutup.');
        }

        try {
            $tally = $this->service->tally($period);
        } catch (ElectionException $e) {
            return back()->with('error', $e->getMessage());
        }

        $participation  = $this->service->participation($period);
        $consistency    = $this->service->consistency($period);

        return view('admin.pilketos.periods.results', compact(
            'period', 'tally', 'participation', 'consistency'
        ));
    }

    public function updateCollectedCards(Request $request, ElectionPeriod $period)
    {
        $request->validate(['collected_cards_count' => 'required|integer|min:0']);
        $period->update(['collected_cards_count' => $request->collected_cards_count]);
        return back()->with('success', 'Jumlah kertas terkumpul diperbarui.');
    }

    public function publishCandidates(Request $request, ElectionPeriod $period)
    {
        $on = $request->boolean('on');

        try {
            $this->service->publishCandidates($period, $on);
        } catch (ElectionException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = $on ? 'Profil kandidat berhasil dipublikasikan untuk umum.' : 'Publikasi kandidat ditarik.';
        return back()->with('success', $msg);
    }

    public function publishResults(Request $request, ElectionPeriod $period)
    {
        $on    = $request->boolean('on');
        $force = $request->boolean('force');

        try {
            $this->service->publishResults($period, $on, $force);
        } catch (ElectionException $e) {
            $msg = $e->getMessage();
            if (Str::contains($msg, '|confirm_force')) {
                return back()->with('error', Str::before($msg, '|confirm_force'))
                    ->with('confirm_publish_force', true);
            }
            return back()->with('error', $msg);
        }

        $msg = $on ? 'Hasil pemilihan berhasil dipublikasikan untuk umum.' : 'Publikasi hasil ditarik.';
        return back()->with('success', $msg);
    }
}
