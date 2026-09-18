<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\TypingTest;
use App\Models\TypingAttempt;
use Illuminate\Http\Request;
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
            'token'               => 'nullable|string|max:50',
            'duration_seconds'    => 'required|integer|min:10|max:600',
            'target_wpm'          => 'required|integer|min:1|max:300',
            'weight_accuracy'     => 'required|integer|min:0|max:100',
            'weight_speed'        => 'required|integer|min:0|max:100',
            'show_result'         => 'boolean',
            'status'              => 'required|in:draft,published',
            'starts_at'           => 'nullable|date',
            'ends_at'             => 'nullable|date|after_or_equal:starts_at',
            'class_restriction'   => 'nullable|array',
            'class_restriction.*' => 'exists:classes,id',
        ]);

        if (($data['weight_accuracy'] + $data['weight_speed']) !== 100) {
            return back()->withErrors(['weight_accuracy' => 'Total bobot akurasi dan kecepatan harus 100%.'])->withInput();
        }

        $data['created_by']  = auth()->id();
        $data['show_result'] = $request->boolean('show_result');
        $data['word_count']  = 200;

        TypingTest::create($data);

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
            'token'               => 'nullable|string|max:50',
            'duration_seconds'    => 'required|integer|min:10|max:600',
            'target_wpm'          => 'required|integer|min:1|max:300',
            'weight_accuracy'     => 'required|integer|min:0|max:100',
            'weight_speed'        => 'required|integer|min:0|max:100',
            'show_result'         => 'boolean',
            'status'              => 'required|in:draft,published',
            'starts_at'           => 'nullable|date',
            'ends_at'             => 'nullable|date|after_or_equal:starts_at',
            'class_restriction'   => 'nullable|array',
            'class_restriction.*' => 'exists:classes,id',
        ]);

        if (($data['weight_accuracy'] + $data['weight_speed']) !== 100) {
            return back()->withErrors(['weight_accuracy' => 'Total bobot akurasi dan kecepatan harus 100%.'])->withInput();
        }

        $data['show_result'] = $request->boolean('show_result');

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

    public function results(TypingTest $test)
    {
        $attempts = TypingAttempt::with('student.classroom')
            ->where('typing_test_id', $test->id)
            ->where('status', 'completed')
            ->orderByDesc('final_score')
            ->get();

        return view('admin.typing-tests.results', compact('test', 'attempts'));
    }

    public function export(TypingTest $test)
    {
        $attempts = TypingAttempt::with('student.classroom')
            ->where('typing_test_id', $test->id)
            ->where('status', 'completed')
            ->orderByDesc('final_score')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hasil Tes Mengetik');

        // Header row
        $headers = ['No', 'Nama Siswa', 'NIS', 'Kelas', 'WPM', 'Akurasi (%)', 'Nilai Akhir', 'Waktu Selesai'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
        }

        // Data rows
        foreach ($attempts as $i => $attempt) {
            $row = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $row, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $row, $attempt->student->name ?? '-');
            $sheet->setCellValueByColumnAndRow(3, $row, $attempt->student->nis ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $row, $attempt->student->classroom->name ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $row, number_format($attempt->wpm, 2));
            $sheet->setCellValueByColumnAndRow(6, $row, number_format($attempt->accuracy, 2));
            $sheet->setCellValueByColumnAndRow(7, $row, number_format($attempt->final_score, 2));
            $sheet->setCellValueByColumnAndRow(8, $row, $attempt->completed_at?->format('d/m/Y H:i') ?? '-');
        }

        // Auto-size columns
        for ($col = 1; $col <= 8; $col++) {
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
