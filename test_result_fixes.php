<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Exam, App\Models\User, App\Models\ExamAttempt, App\Models\ExamAnswer;
use App\Services\ExamEngineService, App\Services\ScoringService;

echo "\n=== TEST: Result Page Fixes ===\n\n";

echo "TEST 1: Question Organization\n";
$exam = Exam::first();
$student = User::where('role', 'student')->first();
$attempt = ExamEngineService::startExam($exam, $student);
$attempt = ExamEngineService::getAttemptWithQuestions($attempt);
$questions = ExamEngineService::getExamQuestions($attempt);

$mc = $questions->filter(function ($q) {
    return $q->nav_type === 'mc';
});
$essay = $questions->filter(function ($q) {
    return $q->nav_type === 'essay';
});

echo "  ✓ Total questions: " . $questions->count() . "\n";
echo "  ✓ MC questions: " . $mc->count() . " (first " . ($mc->first()->display_index + 1) . " to " . ($mc->last()->display_index + 1) . ")\n";
echo "  ✓ Essay questions: " . $essay->count() . " (after MC)\n";

echo "\nTEST 2: Simulate Student Answers\n";
// Simulate answering some MC questions correctly, some wrong, no essays
$answers = $attempt->answers;
$answered_mc = 0;
$correct_mc = 0;

foreach ($answers->take($mc->count()) as $index => $answer) {
    if ($index < 4) { // Answer first 4 correctly
        $answer->selected_answer = 'a'; // Assume 'a' is correct
        $answer->is_correct = true;
        $correct_mc++;
    } elseif ($index < 8) { // Answer next 4 incorrectly
        $answer->selected_answer = 'b';
        $answer->is_correct = false;
    }
    // Rest unanswered
    $answer->save();
    if ($answer->selected_answer) {
        $answered_mc++;
    }
}

echo "  ✓ Saved " . $answered_mc . " MC answers (" . $correct_mc . " correct)\n";

echo "\nTEST 3: Score Calculation\n";
$score_mc = ScoringService::calculateMCScore($attempt);
echo "  ✓ MC Score: " . $score_mc . "/100 (expected: " . (($correct_mc / $mc->count()) * 100) . ")\n";

$weights = ScoringService::getExamWeights($exam);
$expected_final = ($score_mc * $weights['mc_weight'] / 100) + (0 * $weights['essay_weight'] / 100);
echo "  ✓ Final Score (with weights): " . $expected_final . "/100\n";

echo "\nTEST 4: Result Page Data Retrieval\n";
// Simulate what result controller does
$attempt = ExamEngineService::getAttemptWithQuestions($attempt);
$questions_result = ExamEngineService::getExamQuestions($attempt);
$answers_result = $attempt->answers()->with('question')->get();

$correct_count = $answers_result->filter(function ($a) {
    return $a->is_correct === true;
})->count();
$incorrect_count = $answers_result->filter(function ($a) {
    return $a->is_correct === false;
})->count();
$unanswered_count = $answers_result->filter(function ($a) {
    return $a->is_correct === null;
})->count();

echo "  ✓ Correct: " . $correct_count . " (expected: " . $correct_mc . ")\n";
echo "  ✓ Incorrect: " . $incorrect_count . "\n";
echo "  ✓ Unanswered: " . $unanswered_count . "\n";

echo "\nTEST 5: Question Number Display\n";
foreach ($questions_result->take(3) as $q) {
    echo "  ✓ Q" . $q->nav_position . " (" . $q->nav_type . "): " . substr($q->question_text, 0, 40) . "...\n";
}

echo "\n=== ✓ ALL TESTS COMPLETED ===\n\n";
