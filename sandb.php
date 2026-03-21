<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$studentId    = 672;
$semester     = 1;
$academicYear = '2023/2024';

\DB::enableQueryLog();
$start = microtime(true);

$student   = \App\Models\User::find($studentId);
$class     = \App\Models\ClassRoom::where('id', $student->class_id ?? 0)->first();
$principal = \App\Models\User::where('role', 'principal')->first();
\App\Models\StudentAttendance::where('student_id', $studentId)->where('semester', $semester)->where('academic_year', $academicYear)->first();
\App\Models\StudentPersonality::where('student_id', $studentId)->where('semester', $semester)->where('academic_year', $academicYear)->first();
\App\Models\StudentExtracurricular::where('student_id', $studentId)->where('semester', $semester)->where('academic_year', $academicYear)->get();
\App\Models\StudentDudi::where('student_id', $studentId)->where('semester', $semester)->where('academic_year', $academicYear)->get();
\App\Models\StudentPromotion::where('student_id', $studentId)->where('academic_year', $academicYear)->first();

$elapsed = round((microtime(true) - $start) * 1000, 2);
$queries = \DB::getQueryLog();

echo "Total: " . count($queries) . " queries | {$elapsed}ms total\n\n";
foreach ($queries as $q) {
    echo round($q['time'], 2) . "ms — " . $q['query'] . "\n";
}