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

        // Cek token jika diperlukan
        if ($test->use_token) {
            $inputToken = session('typing_token_' . $test->id);
            if (!$inputToken || $inputToken !== $test->token) {
                return view('student.typing-tests.token', compact('test'));
            }
        }

        // Cek attempt siswa
        $attempts = TypingAttempt::where('typing_test_id', $test->id)
            ->where('student_id', $student->id)
            ->orderBy('attempt_number')
            ->get();

        $inProgressAttempt = $attempts->firstWhere('status', 'in_progress');
        $completedAttempts = $attempts->where('status', 'completed');
        $maxAttempts       = $test->max_attempts ?? 2;

        if ($inProgressAttempt) {
            $attempt = $inProgressAttempt;
        } elseif ($completedAttempts->count() < $maxAttempts) {
            $nextAttemptNumber = $completedAttempts->count() + 1;
            $words = TypingTestService::generateWords($test->word_count);
            $wordsGenerated = implode(' ', $words);

            $attempt = TypingAttempt::create([
                'typing_test_id'  => $test->id,
                'student_id'      => $student->id,
                'attempt_number'  => $nextAttemptNumber,
                'words_generated' => $wordsGenerated,
                'status'          => 'in_progress',
                'started_at'      => now(),
            ]);
        } else {
            return redirect()->route('student.typing-tests.result', $test)
                ->with('info', 'Anda sudah menggunakan semua kesempatan tes ini.');
        }

        return view('student.typing-tests.show', compact('test', 'attempt'));
    }

    public function validateToken(Request $request, TypingTest $test)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        if (strtoupper($request->token) !== $test->token) {
            return back()->withErrors([
                'token' => 'Token tidak valid.'
            ])->withInput();
        }

        session(['typing_token_' . $test->id => $test->token]);
        return redirect()->route('student.typing-tests.show', $test);
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

        return response()->json([
            'success'           => true,
            'show_wpm_accuracy' => $test->show_wpm_accuracy,
            'show_score'        => $test->show_score,
            'attempt_number'    => $attempt->attempt_number,
            'max_attempts'      => $test->max_attempts ?? 2,
            'result'            => [
                'wpm'         => $result['wpm'],
                'accuracy'    => $result['accuracy'],
                'final_score' => $result['final_score'],
            ],
            'redirect' => route('student.typing-tests.result', $test),
        ]);
    }

    public function result(TypingTest $test)
    {
        $student = auth()->user();

        $attempts = TypingAttempt::where('typing_test_id', $test->id)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('attempt_number')
            ->get();

        if ($attempts->isEmpty()) {
            return redirect()->route('student.typing-tests.show', $test);
        }

        $bestAttempt = $attempts->sortByDesc(function ($att) {
            return ((float)$att->final_score * 1000) + (float)($att->wpm ?? 0);
        })->first();

        $latestAttempt = $attempts->last();
        $maxAttempts   = $test->max_attempts ?? 2;
        $canRetry      = $attempts->count() < $maxAttempts;

        return view('student.typing-tests.result', [
            'test'              => $test,
            'attempts'          => $attempts,
            'attempt'           => $bestAttempt,
            'bestAttempt'       => $bestAttempt,
            'latestAttempt'     => $latestAttempt,
            'canRetry'          => $canRetry,
            'maxAttempts'       => $maxAttempts,
            'show_wpm_accuracy' => $test->show_wpm_accuracy,
            'show_score'        => $test->show_score,
        ]);
    }
}
