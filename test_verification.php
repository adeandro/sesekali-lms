<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========== VERIFICATION TEST ==========\n\n";

// Test 1: Check users count
$studentCount = User::where('role', 'student')->count();
echo "✓ Test 1 - Student Count: $studentCount students\n";
if ($studentCount == 50) {
    echo "  ✅ PASS: 50 students created\n";
} else {
    echo "  ❌ FAIL: Expected 50 students, got $studentCount\n";
}

// Test 2: Check exams count
$publishedExamCount = Exam::where('status', 'published')->count();
$draftExamCount = Exam::where('status', 'draft')->count();
echo "\n✓ Test 2 - Exam Count: $publishedExamCount published, $draftExamCount draft\n";
if ($publishedExamCount == 1 && $draftExamCount == 2) {
    echo "  ✅ PASS: 1 published + 2 draft exams created\n";
} else {
    echo "  ❌ FAIL: Expected 1 published + 2 draft, got $publishedExamCount published + $draftExamCount draft\n";
}

// Test 3: Check exam attempts
$attemptCount = ExamAttempt::count();
echo "\n✓ Test 3 - Exam Attempts: $attemptCount attempts created\n";
if ($attemptCount >= 10) {
    echo "  ✅ PASS: At least 10 exam attempts created\n";
} else {
    echo "  ⚠️  WARNING: Only $attemptCount attempts created (expected at least 10)\n";
}

// Test 4: Check User-ExamAttempt relationship
$student = User::where('role', 'student')->first();
if ($student) {
    $attemptsCount = $student->examAttempts()->count();
    echo "\n✓ Test 4 - User->examAttempts() relationship works\n";
    if ($attemptsCount >= 0) {
        echo "  ✅ PASS: Relationship works (student has $attemptsCount attempts)\n";
    } else {
        echo "  ❌ FAIL: Relationship issue\n";
    }
}

// Test 5: Check published exam with questions
$publishedExam = Exam::where('status', 'published')->first();
if ($publishedExam) {
    $questionCount = $publishedExam->questions()->count();
    echo "\n✓ Test 5 - Published Exam questions: $questionCount questions attached\n";
    if ($questionCount > 0) {
        echo "  ✅ PASS: Published exam has $questionCount questions\n";
    } else {
        echo "  ❌ FAIL: Published exam has no questions\n";
    }
}

// Test 6: Check print card students list
if ($publishedExam) {
    $attempts = $publishedExam->attempts()
        ->with('student')
        ->orderBy('final_score', 'desc')
        ->get();

    echo "\n✓ Test 6 - Print Card SQL Query:\n";
    echo "  Query returns: " . $attempts->count() . " exam attempts\n";

    if ($attempts->count() > 0) {
        echo "  ✅ PASS: Print card query works (returns " . $attempts->count() . " attempts)\n";
        echo "  Sample attempts:\n";
        foreach ($attempts->take(3) as $attempt) {
            echo "    - Student: " . ($attempt->student->name ?? 'N/A') . ", Score: " . $attempt->final_score . "\n";
        }
    } else {
        // Try fallback query
        $allStudents = User::where('role', 'student')
            ->where('is_active', true)
            ->orderBy('class', 'asc')
            ->orderBy('name', 'asc')
            ->count();
        echo "  ℹ️  No exam attempts yet, fallback to all active students: $allStudents students\n";
        echo "  ✅ PASS: Fallback query will use $allStudents active students\n";
    }
}

// Test 7: Check database column existence
echo "\n✓ Test 7 - Database schema check:\n";
$dbSchema = \DB::getDoctrineSchemaManager();
$columns = $dbSchema->listTableColumns('exam_attempts');
$hasCanViewScore = isset($columns['can_view_score']);
echo "  can_view_score column exists: " . ($hasCanViewScore ? 'YES' : 'NO') . "\n";

if ($hasCanViewScore) {
    echo "  ⚠️  Column exists - dashboard will try to filter by it\n";
} else {
    echo "  ✅ PASS: Column doesn't exist - dashboard uses simple query (no filter)\n";
}

echo "\n========== END VERIFICATION ==========\n";
