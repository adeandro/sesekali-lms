<?php

namespace App\Http\Controllers\Admin\Election;

use App\Exceptions\ElectionException;
use App\Http\Controllers\Controller;
use App\Models\ElectionPeriod;
use App\Services\ElectionService;
use Maatwebsite\Excel\Facades\Excel;

class ElectionResultController extends Controller
{
    public function __construct(private ElectionService $service) {}

    public function exportExcel(ElectionPeriod $period)
    {
        if ($period->status !== 'closed') {
            abort(403, 'Export hasil hanya tersedia setelah pemilihan ditutup.');
        }

        try {
            $tally = $this->service->tally($period);
        } catch (ElectionException $e) {
            return back()->with('error', $e->getMessage());
        }

        $participation = $this->service->participation($period);

        $rows = collect($tally['results'])->map(fn ($r) => [
            'Nomor' => $r['candidate']->number,
            'Ketua' => $r['candidate']->chairman_name,
            'Wakil' => $r['candidate']->vice_name,
            'Perolehan Suara' => $r['votes'],
            'Persentase' => $tally['total_votes'] > 0
                ? round(($r['votes'] / $tally['total_votes']) * 100, 1) . '%'
                : '0%',
        ]);

        // Tambah baris suara kosong hanya jika ada suaranya
        if ($tally['blank_count'] > 0) {
            $rows->push([
                'Nomor' => '-',
                'Ketua' => 'Suara Kosong',
                'Wakil' => '-',
                'Perolehan Suara' => $tally['blank_count'],
                'Persentase' => $tally['total_votes'] > 0
                    ? round(($tally['blank_count'] / $tally['total_votes']) * 100, 1) . '%'
                    : '0%',
            ]);
        }

        $filename = 'hasil-pilketos-' . str($period->title)->slug() . '.xlsx';

        return Excel::download(
            new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function __construct(private \Illuminate\Support\Collection $rows) {}
                public function collection() { return $this->rows->map(fn ($r) => array_values($r)); }
                public function headings(): array { return ['No. Paslon', 'Ketua', 'Wakil', 'Perolehan Suara', 'Persentase']; }
            },
            $filename
        );
    }
}
