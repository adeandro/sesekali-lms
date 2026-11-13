<?php

namespace App\Http\Controllers\Admin\Election;

use App\Http\Controllers\Controller;
use App\Models\ElectionPeriod;
use App\Models\ElectionVoter;
use App\Services\ElectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ElectionVoterController extends Controller
{
    public function __construct(private ElectionService $service) {}

    public function index(Request $request, ElectionPeriod $period)
    {
        $query = ElectionVoter::where('period_id', $period->id);

        // Filter: Kelas / Kelompok
        $selectedClass = $request->input('class');
        if ($request->filled('class')) {
            if ($selectedClass === 'Tanpa Kelas') {
                $query->where(function ($q) {
                    $q->whereNull('class_name')->orWhere('class_name', '');
                });
            } else {
                $query->where('class_name', $selectedClass);
            }
        }

        // Filter: Status Hak Suara (voted)
        $selectedVoted = $request->input('voted');
        if ($request->filled('voted')) {
            if ($selectedVoted === 'belum' || $selectedVoted === '0') {
                $query->where('has_voted', false);
            } elseif ($selectedVoted === 'sudah' || $selectedVoted === '1') {
                $query->where('has_voted', true);
            }
        }

        // Filter: Status Kartu (printed)
        $selectedPrinted = $request->input('printed');
        if ($request->filled('printed')) {
            if ($selectedPrinted === 'sudah' || $selectedPrinted === '1') {
                $query->whereNotNull('token_hash');
            } elseif ($selectedPrinted === 'belum' || $selectedPrinted === '0') {
                $query->whereNull('token_hash');
            }
        }

        // Filter: Pencarian (Nama / NIS / NIP)
        $search = trim($request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Sort / Urutan
        $sort = $request->input('sort', 'class_asc');
        switch ($sort) {
            case 'unvoted_first':
                $query->orderBy('has_voted', 'asc')
                      ->orderBy('class_name', 'asc')
                      ->orderBy('student_name', 'asc');
                break;
            case 'voted_first':
                $query->orderBy('has_voted', 'desc')
                      ->orderBy('class_name', 'asc')
                      ->orderBy('student_name', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('student_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('student_name', 'desc');
                break;
            case 'nis_asc':
                $query->orderBy('nis', 'asc');
                break;
            case 'voted_at_desc':
                $query->orderByRaw('voted_at IS NULL, voted_at DESC')
                      ->orderBy('student_name', 'asc');
                break;
            case 'class_desc':
                $query->orderBy('class_name', 'desc')
                      ->orderBy('student_name', 'asc');
                break;
            case 'class_asc':
            default:
                $query->orderBy('class_name', 'asc')
                      ->orderBy('student_name', 'asc');
                break;
        }

        // Hitung statistik untuk filter aktif (tanpa klausa order agar hemat query)
        $baseFilterQuery = (clone $query)->reorder();
        $filteredTotal   = (clone $baseFilterQuery)->count();
        $filteredVoted   = (clone $baseFilterQuery)->where('has_voted', true)->count();
        $filteredUnvoted = (clone $baseFilterQuery)->where('has_voted', false)->count();

        $voters = $query->paginate(50)->withQueryString();

        // Ringkasan per kelas untuk kartu kelas di bagian atas
        $summary = DB::table('election_voters')
            ->where('period_id', $period->id)
            ->select('class_name', 'class_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(has_voted) as voted'),
                DB::raw('SUM(token_hash IS NOT NULL) as printed')
            )
            ->groupBy('class_name', 'class_id')
            ->orderBy('class_name')
            ->get();

        // Daftar unik kelas untuk dropdown filter
        $classList = $summary->pluck('class_name')->filter()->unique()->values();

        // Total periode keseluruhan (tanpa filter)
        $periodTotal   = DB::table('election_voters')->where('period_id', $period->id)->count();
        $periodVoted   = DB::table('election_voters')->where('period_id', $period->id)->where('has_voted', true)->count();
        $periodUnvoted = $periodTotal - $periodVoted;

        return view('admin.pilketos.voters.index', compact(
            'period',
            'voters',
            'summary',
            'classList',
            'filteredTotal',
            'filteredVoted',
            'filteredUnvoted',
            'periodTotal',
            'periodVoted',
            'periodUnvoted',
            'selectedClass',
            'selectedVoted',
            'selectedPrinted',
            'search',
            'sort'
        ));
    }

    /**
     * Cetak kartu untuk satu kelas.
     * POST /admin/pilketos/periods/{period}/cards/class/{classId}
     */
    public function printClass(Request $request, ElectionPeriod $period, $classId)
    {
        if ($period->status === 'closed') {
            return back()->with('error', 'Tidak bisa cetak kartu setelah pemilihan ditutup.');
        }

        $query = ElectionVoter::where('period_id', $period->id)->where('has_voted', false);

        if ((string)$classId === '0') {
            $query->where(function ($q) {
                $q->where('class_id', 0)->orWhere('class_name', 'GURU & STAF');
            });
        } else {
            $query->where('class_id', $classId);
        }

        $voters = $query->orderBy('student_name')->get();

        if ($voters->isEmpty()) {
            return back()->with('error', 'Tidak ada pemilih yang bisa dicetak di kelompok/kelas ini (semua sudah memilih atau kosong).');
        }

        try {
            $pdf = $this->service->printVoterCards($period, $voters);
        } catch (\App\Exceptions\ElectionException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal render PDF: ' . $e->getMessage());
        }

        $className = $voters->first()->class_name ?? ((string)$classId === '0' ? 'GURU & STAF' : "kelas-{$classId}");
        $filename  = 'kartu-' . str($period->title)->slug() . '-' . str($className)->slug() . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Cetak ulang kartu satu voter.
     * POST /admin/pilketos/periods/{period}/cards/voter/{voter}
     */
    public function printOne(Request $request, ElectionPeriod $period, ElectionVoter $voter)
    {
        if ($period->status === 'closed') {
            return back()->with('error', 'Tidak bisa cetak kartu setelah pemilihan ditutup.');
        }

        if ($voter->period_id !== $period->id) {
            abort(404);
        }

        if ($voter->has_voted) {
            return back()->with('error', 'Pemilih ini sudah memilih, kartu tidak bisa dicetak ulang.');
        }

        try {
            $pdf = $this->service->printVoterCards($period, collect([$voter]));
        } catch (\App\Exceptions\ElectionException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal render PDF: ' . $e->getMessage());
        }

        $filename = 'kartu-' . str($voter->student_name)->slug() . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export daftar pemilih ke Excel (tanpa token/hash).
     * Mendukung filter kelas, status hak suara, dan pencarian jika sedang aktif.
     */
    public function exportExcel(Request $request, ElectionPeriod $period)
    {
        $query = ElectionVoter::where('period_id', $period->id);

        if ($request->filled('class')) {
            $classFilter = $request->input('class');
            if ($classFilter === 'Tanpa Kelas') {
                $query->where(function ($q) {
                    $q->whereNull('class_name')->orWhere('class_name', '');
                });
            } else {
                $query->where('class_name', $classFilter);
            }
        }

        if ($request->filled('voted')) {
            $votedFilter = $request->input('voted');
            if ($votedFilter === 'belum' || $votedFilter === '0') {
                $query->where('has_voted', false);
            } elseif ($votedFilter === 'sudah' || $votedFilter === '1') {
                $query->where('has_voted', true);
            }
        }

        if ($request->filled('printed')) {
            $printedFilter = $request->input('printed');
            if ($printedFilter === 'sudah' || $printedFilter === '1') {
                $query->whereNotNull('token_hash');
            } elseif ($printedFilter === 'belum' || $printedFilter === '0') {
                $query->whereNull('token_hash');
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $voters = $query->orderBy('class_name')->orderBy('student_name')->get();

        $rows = $voters->map(fn ($v) => [
            'Nama'          => $v->student_name,
            'NIS'           => $v->nis,
            'Kelas'         => $v->class_name ?? '-',
            'Sudah Memilih' => $v->has_voted ? 'Ya' : 'Belum',
            'Kartu Dicetak' => $v->token_hash ? 'Ya' : 'Belum',
        ]);

        $suffix = '';
        if ($request->filled('class')) {
            $suffix .= '-' . str($request->class)->slug();
        }
        if ($request->filled('voted')) {
            $suffix .= '-' . ($request->voted === 'belum' || $request->voted === '0' ? 'belum-memilih' : 'sudah-memilih');
        }

        $filename = 'daftar-pemilih-' . str($period->title)->slug() . $suffix . '.xlsx';

        return Excel::download(
            new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function __construct(private \Illuminate\Support\Collection $rows) {}
                public function collection() { return $this->rows->map(fn ($r) => array_values($r)); }
                public function headings(): array { return ['Nama', 'NIS', 'Kelas', 'Sudah Memilih', 'Kartu Dicetak']; }
            },
            $filename
        );
    }
}
