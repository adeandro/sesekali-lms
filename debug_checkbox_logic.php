<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Subject;

$teacher = User::find(2);
$subjects = Subject::orderBy('name')->get();
$categories = Subject::categories();
$groupedSubjects = $subjects->groupBy('category');
$assignedSubjects = $teacher->subjects->pluck('id')->toArray();

echo "Assigned Subjects: " . json_encode($assignedSubjects) . "\n";

foreach ($categories as $key => $label) {
    if (isset($groupedSubjects[$key])) {
        $categoryIds = $groupedSubjects[$key]->pluck('id')->toArray();
        $intersect = array_intersect($categoryIds, $assignedSubjects);
        $mapped = array_map('strval', $intersect);
        $valued = array_values($mapped);
        $json = json_encode($valued);
        
        echo "Category [$key] ($label):\n";
        echo "  Category IDs: " . json_encode($categoryIds) . "\n";
        echo "  Intersect: " . json_encode($intersect) . "\n";
        echo "  Mapped: " . json_encode($mapped) . "\n";
        echo "  Valued: " . json_encode($valued) . "\n";
        echo "  JSON: $json\n";
    }
}
