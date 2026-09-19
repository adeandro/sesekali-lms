<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\TypingTest;
use App\Models\TypingAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TypingTestController extends Controller
{
    public function index()
    {
        $tests = TypingTest::withCount('attempts')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.typing-tests.index', compact('tests'));
    }

    public function create()
    {
        $classes = ClassRoom::orderBy('name')->get();
        return view('admin.typing-tests.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:191',
            'description'         => 'nullable|string',
            'duration_seconds'    => 'required|integer|min:10|max:600',
            'target_wpm'          => 'required|integer|min:1|max:300',
            'weight_accuracy'     => 'required|integer|min:0|max:100',
            'weight_speed'        => 'required|integer|min:0|max:100',
            'max_attempts'        => 'required|integer|min:1|max:10',
            'show_wpm_accuracy'   => 'boolean',
            'show_score'          => 'boolean',
            'use_token'           => 'boolean',
            'status'              => 'required|in:draft,published',
            'starts_at'           => 'nullable|date',
            'ends_at'             => 'nullable|date|after_or_equal:starts_at',
            'class_restriction'   => 'nullable|array',
            'class_restriction.*' => 'exists:classes,id',
        ]);

        if (($data['weight_accuracy'] + $data['weight_speed']) !== 100) {
            return back()->withErrors(['weight_accuracy' => 'Total bobot akurasi dan kecepatan harus 100%.'])->withInput();
        }

        $data['show_wpm_accuracy'] = $request->boolean('show_wpm_accuracy');
        $data['show_score']        = $request->boolean('show_score');
        $data['use_token']         = $request->boolean('use_token');
        $data['word_count']        = 200;

        // Auto-generate token jika use_token aktif
        $token = null;
        if ($request->boolean('use_token')) {
            do {
                $token = strtoupper(Str::random(6));
            } while (TypingTest::where('token', $token)->exists());
        }

        TypingTest::create(array_merge($data, [
            'token'      => $token,
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('admin.typing-tests.index')
            ->with('success', 'Tes mengetik berhasil dibuat.');
    }

    public function edit(TypingTest $test)
    {
        $classes = ClassRoom::orderBy('name')->get();
        return view('admin.typing-tests.edit', compact('test', 'classes'));
    }

    public function update(Request $request, TypingTest $test)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:191',
            'description'         => 'nullable|string',
            'duration_seconds'    => 'required|integer|min:10|max:600',
            'target_wpm'          => 'required|integer|min:1|max:300',
            'weight_accuracy'     => 'required|integer|min:0|max:100',
            'weight_speed'        => 'required|integer|min:0|max:100',
            'max_attempts'        => 'required|integer|min:1|max:10',
            'show_wpm_accuracy'   => 'boolean',
            'show_score'          => 'boolean',
            'use_token'           => 'boolean',
            'status'              => 'required|in:draft,published',
            'starts_at'           => 'nullable|date',
            'ends_at'             => 'nullable|date|after_or_equal:starts_at',
            'class_restriction'   => 'nullable|array',
            'class_restriction.*' => 'exists:classes,id',
        ]);

        if (($data['weight_accuracy'] + $data['weight_speed']) !== 100) {
            return back()->withErrors(['weight_accuracy' => 'Total bobot akurasi dan kecepatan harus 100%.'])->withInput();
        }

        $data['show_wpm_accuracy'] = $request->boolean('show_wpm_accuracy');
        $data['show_score']        = $request->boolean('show_score');
        $data['use_token']         = $request->boolean('use_token');

        // Token logic
        if ($request->boolean('use_token') && !$test->token) {
            do {
                $data['token'] = strtoupper(Str::random(6));
            } while (TypingTest::where('token', $data['token'])->where('id', '!=', $test->id)->exists());
        } elseif (!$request->boolean('use_token')) {
            $data['token'] = null;
        }
        // Jika use_token aktif dan token sudah ada, biarkan token lama

        $test->update($data);

        return redirect()->route('admin.typing-tests.index')
            ->with('success', 'Tes mengetik berhasil diperbarui.');
    }

    public function destroy(TypingTest $test)
    {
        $test->delete();

        return redirect()->route('admin.typing-tests.index')
            ->with('success', 'Tes mengetik berhasil dihapus.');
    }

    public function toggleStatus(TypingTest $test)
    {
        $newStatus = $test->status === 'published' ? 'draft' : 'published';
        $test->update(['status' => $newStatus]);

        return back()->with('success', 'Status diubah ke ' . ucfirst($newStatus));
    }

    public function results(TypingTest $test)
    {
        $allAttempts = TypingAttempt::with('student.classroom')
            ->where('typing_test_id', $test->id)
            ->where('status', 'completed')
            ->orderBy('student_id')
            ->orderBy('attempt_number')
            ->get();

        $attempts = $allAttempts->groupBy('student_id')->map(function ($studentAttempts) {
            $best = $studentAttempts->sortByDesc(function ($att) {
                return ((float)$att->final_score * 1000) + (float)($att->wpm ?? 0);
            })->first();

            $best->all_attempts = $studentAttempts;
            $best->attempts_count = $studentAttempts->count();
            return $best;
        })->sortByDesc(function ($att) {
            return ((float)$att->final_score * 1000) + (float)($att->wpm ?? 0);
        })->values();

        return view('admin.typing-tests.results', compact('test', 'attempts'));
    }

    public function resetAttempt(TypingTest $test, TypingAttempt $attempt)
    {
        abort_if($attempt->typing_test_id !== $test->id, 403);

        $studentName = $attempt->student->name ?? 'Siswa';
        TypingAttempt::where('typing_test_id', $test->id)
            ->where('student_id', $attempt->student_id)
            ->delete();

        return back()->with('success',
            'Semua attempt tes siswa ' . $studentName . ' berhasil direset. Siswa dapat mengerjakan ulang dari awal.');
    }

    public function export(TypingTest $test)
    {
        $allAttempts = TypingAttempt::with('student.classroom')
            ->where('typing_test_id', $test->id)
            ->where('status', 'completed')
            ->orderBy('student_id')
            ->orderBy('attempt_number')
            ->get();

        $attempts = $allAttempts->groupBy('student_id')->map(function ($studentAttempts) {
            $best = $studentAttempts->sortByDesc(function ($att) {
                return ((float)$att->final_score * 1000) + (float)($att->wpm ?? 0);
            })->first();

            $best->attempts_count = $studentAttempts->count();
            return $best;
        })->sortByDesc(function ($att) {
            return ((float)$att->final_score * 1000) + (float)($att->wpm ?? 0);
        })->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Tes Mengetik');

        $headers = ['No', 'Nama Siswa', 'NIS', 'Kelas', 'Percobaan Terbaik', 'WPM', 'Akurasi (%)', 'Nilai Akhir', 'Waktu Selesai'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
        }

        foreach ($attempts as $i => $attempt) {
            $row = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $row, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, $attempt->student->name ?? '-');
            $sheet->setCellValueByColumnAndRow(3, $row, $attempt->student->nis ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $row, $attempt->student->classroom->name ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $row, 'Ke-' . $attempt->attempt_number . ' (dari ' . $attempt->attempts_count . ')');
            $sheet->setCellValueByColumnAndRow(6, $row, number_format($attempt->wpm, 2));
            $sheet->setCellValueByColumnAndRow(7, $row, number_format($attempt->accuracy, 2));
            $sheet->setCellValueByColumnAndRow(8, $row, number_format($attempt->final_score, 2));
            $sheet->setCellValueByColumnAndRow(9, $row, $attempt->completed_at?->format('d/m/Y H:i') ?? '-');
        }

        for ($col = 1; $col <= 9; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $slug     = \Illuminate\Support\Str::slug($test->title);
        $date     = now()->format('d-m-Y');
        $filename = "Hasil-Tes-Mengetik-{$slug}-{$date}.xlsx";

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'typing_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
