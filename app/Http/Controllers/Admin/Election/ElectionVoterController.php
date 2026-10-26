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

    public function index(ElectionPeriod $period)
    {
        $voters = ElectionVoter::where('period_id', $period->id)
            ->orderBy('class_name')
            ->orderBy('student_name')
            ->paginate(50);

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

        return view('admin.pilketos.voters.index', compact('period', 'voters', 'summary'));
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
     */
    public function exportExcel(ElectionPeriod $period)
    {
        $voters = ElectionVoter::where('period_id', $period->id)
            ->orderBy('class_name')->orderBy('student_name')
            ->get();

        $rows = $voters->map(fn ($v) => [
            'Nama'         => $v->student_name,
            'NIS'          => $v->nis,
            'Kelas'        => $v->class_name ?? '-',
            'Sudah Memilih' => $v->has_voted ? 'Ya' : 'Belum',
            'Kartu Dicetak' => $v->token_hash ? 'Ya' : 'Belum',
        ]);

        $filename = 'daftar-pemilih-' . str($period->title)->slug() . '.xlsx';

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
