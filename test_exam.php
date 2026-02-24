<?php
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== CHECKING EXAMS ===\n\n";
$now = now();

$exams10 = \App\Models\Exam::where('jenjang', '10')
    ->where('status', 'published')
    ->latest()
    ->limit(3)
    ->get();

foreach ($exams10 as $e) {
    echo "Exam: {$e->title}\n";
    echo "  Jenjang: {$e->jenjang}\n";
    echo "  Status: {$e->status}\n";
    echo "  Questions: " . $e->questions()->count() . "\n";
    echo "  Start: {$e->start_time}\n";
    echo "  End: {$e->end_time}\n";
    echo "  Available: " . ($e->end_time >= $now ? 'YES' : 'NO') . "\n";
    echo "\n";
}

echo "\n=== CHECKING STUDENT VIEWS ===\n\n";
$student = \App\Models\User::where('role', 'student')->where('grade', 10)->first();
if ($student) {
    echo "Student: {$student->name} (Grade: {$student->grade})\n";
    $available = \App\Services\ExamEngineService::getAvailableExams($student);
    echo "Available exams: " . $available->count() . "\n";
    foreach ($available as $e) {
        echo "  - {$e->title}\n";
    }
}
