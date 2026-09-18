<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TypingAttempt;
use App\Models\TypingTest;
use App\Services\TypingTestService;
use Illuminate\Http\Request;

class TypingTestController extends Controller
{
    public function show(TypingTest $test)
    {
        $student = auth()->user();

        // Cek apakah tes tersedia
        if (!TypingTest::available()->where('id', $test->id)->exists()) {
            abort(403, 'Tes mengetik tidak tersedia.');
        }

        // Cek apakah tes terbuka untuk kelas ini
        if (!$test->isAvailableFor($student)) {
            abort(403, 'Tes ini tidak tersedia untuk kelas Anda.');
        }

        // Cek apakah sudah ada attempt completed
        $existingAttempt = TypingAttempt::where('typing_test_id', $test->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttempt && $existingAttempt->status === 'completed') {
            return redirect()->route('student.typing-tests.result', $test)
                ->with('info', 'Anda sudah menyelesaikan tes ini.');
        }

        // Buat atau ambil attempt in_progress
        if (!$existingAttempt) {
            // Generate kata
            $words = TypingTestService::generateWords($test->word_count);
            $wordsGenerated = implode(' ', $words);

            $existingAttempt = TypingAttempt::create([
                'typing_test_id'  => $test->id,
                'student_id'      => $student->id,
                'words_generated' => $wordsGenerated,
                'status'          => 'in_progress',
                'started_at'      => now(),
            ]);
        }
        // Jika in_progress, gunakan words_generated yang sudah tersimpan (konsistensi)

        $attempt = $existingAttempt;

        return view('student.typing-tests.show', compact('test', 'attempt'));
    }

    public function submit(Request $request, TypingTest $test)
    {
        $student = auth()->user();

        $request->validate([
            'words_typed' => 'required|string|max:10000',
        ]);

        // Ambil attempt in_progress milik siswa ini
        $attempt = TypingAttempt::where('typing_test_id', $test->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            return response()->json(['success' => false, 'message' => 'Attempt tidak ditemukan atau sudah selesai.'], 403);
        }

        // Hitung hasil
        $result = TypingTestService::calculateResult([
            'words_generated'  => $attempt->words_generated,
            'words_typed'      => $request->words_typed,
            'duration_seconds' => $test->duration_seconds,
            'target_wpm'       => $test->target_wpm,
            'weight_accuracy'  => $test->weight_accuracy,
            'weight_speed'     => $test->weight_speed,
        ]);

        // Update attempt
        $attempt->update(array_merge($result, [
            'words_typed'  => $request->words_typed,
            'status'       => 'completed',
            'completed_at' => now(),
        ]));

        $response = ['success' => true, 'show_result' => $test->show_result];
        if ($test->show_result) {
            $response['result'] = [
                'wpm'         => $result['wpm'],
                'accuracy'    => $result['accuracy'],
                'final_score' => $result['final_score'],
            ];
        }

        return response()->json($response);
    }

    public function result(TypingTest $test)
    {
        $student = auth()->user();

        $attempt = TypingAttempt::where('typing_test_id', $test->id)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->first();

        if (!$attempt) {
            return redirect()->route('student.typing-tests.show', $test);
        }

        return view('student.typing-tests.result', compact('test', 'attempt'));
    }
}
