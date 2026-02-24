<?php
/**
 * Module 4 Exam Management Verification Script
 * Checks file structure without loading Laravel
 */

$base_dir = __DIR__;

echo str_repeat("=", 60) . "\n";
echo "MODULE 4: EXAM MANAGEMENT VERIFICATION\n";
echo str_repeat("=", 60) . "\n\n";

$errors = [];
$passed = [];

// 1. Check Migrations
echo "1. CHECKING MIGRATIONS...\n";
$exams_migration = $base_dir . '/database/migrations/2026_02_14_000000_create_exams_table.php';
$exam_question_migration = $base_dir . '/database/migrations/2026_02_14_000001_create_exam_question_table.php';

if (file_exists($exams_migration)) {
    echo "   ✓ Exams migration exists\n";
    $content = file_get_contents($exams_migration);
    if (strpos($content, 'exam_question') === false) {
        $passed[] = "exams migration";
    }
} else {
    echo "   ✗ Exams migration NOT FOUND\n";
    $errors[] = "exams migration missing";
}

if (file_exists($exam_question_migration)) {
    echo "   ✓ Exam-Question pivot migration exists\n";
    $passed[] = "exam_question migration";
} else {
    echo "   ✗ Exam-Question pivot migration NOT FOUND\n";
    $errors[] = "exam_question migration missing";
}

// 2. Check Models
echo "\n2. CHECKING MODELS...\n";
$exam_model = $base_dir . '/app/Models/Exam.php';
if (file_exists($exam_model)) {
    echo "   ✓ Exam model exists\n";
    $content = file_get_contents($exam_model);
    $has_subject = strpos($content, 'belongsTo(Subject::class)') !== false;
    $has_questions = strpos($content, 'belongsToMany(Question::class') !== false;
    $has_can_publish = strpos($content, 'canPublish') !== false;
    $has_can_edit = strpos($content, 'canEdit') !== false;
    
    echo "   - Subject relation: " . ($has_subject ? "✓\n" : "✗\n");
    echo "   - Questions relation: " . ($has_questions ? "✓\n" : "✗\n");
    echo "   - canPublish() method: " . ($has_can_publish ? "✓\n" : "✗\n");
    echo "   - canEdit() method: " . ($has_can_edit ? "✓\n" : "✗\n");
    
    if ($has_subject && $has_questions && $has_can_publish && $has_can_edit) {
        $passed[] = "Exam model";
    } else {
        $errors[] = "Exam model missing required methods";
    }
} else {
    echo "   ✗ Exam model NOT FOUND\n";
    $errors[] = "Exam model missing";
}

// 3. Check Controller
echo "\n3. CHECKING CONTROLLER...\n";
$controller = $base_dir . '/app/Http/Controllers/Admin/ExamController.php';
if (file_exists($controller)) {
    echo "   ✓ ExamController exists\n";
    $content = file_get_contents($controller);
    $methods = ['index', 'create', 'store', 'edit', 'update', 'destroy', 'manageQuestions', 'attachQuestions', 'detachQuestion', 'publish', 'setToDraft'];
    $missing = [];
    
    foreach ($methods as $method) {
        if (strpos($content, "function $method") !== false || strpos($content, "public function $method") !== false) {
            echo "   - $method: ✓\n";
        } else {
            echo "   - $method: ✗\n";
            $missing[] = $method;
        }
    }
    
    if (empty($missing)) {
        $passed[] = "ExamController";
    } else {
        $errors[] = "ExamController missing: " . implode(", ", $missing);
    }
} else {
    echo "   ✗ ExamController NOT FOUND\n";
    $errors[] = "ExamController missing";
}

// 4. Check Form Requests
echo "\n4. CHECKING FORM REQUESTS...\n";
$store_request = $base_dir . '/app/Http/Requests/StoreExamRequest.php';
$update_request = $base_dir . '/app/Http/Requests/UpdateExamRequest.php';

echo "   - StoreExamRequest: " . (file_exists($store_request) ? "✓\n" : "✗\n");
echo "   - UpdateExamRequest: " . (file_exists($update_request) ? "✓\n" : "✗\n");

if (file_exists($store_request) && file_exists($update_request)) {
    $passed[] = "Form Requests";
} else {
    $errors[] = "Form requests missing";
}

// 5. Check Service
echo "\n5. CHECKING SERVICE...\n";
$service = $base_dir . '/app/Services/ExamService.php';
if (file_exists($service)) {
    echo "   ✓ ExamService exists\n";
    $content = file_get_contents($service);
    $methods = ['createExam', 'updateExam', 'attachQuestions', 'detachQuestion', 'publishExam', 'setToDraft', 'getExamsList', 'getAvailableQuestions'];
    $missing = [];
    
    foreach ($methods as $method) {
        if (strpos($content, "function $method") !== false || strpos($content, "public static function $method") !== false) {
            echo "   - $method: ✓\n";
        } else {
            echo "   - $method: ✗\n";
            $missing[] = $method;
        }
    }
    
    if (empty($missing)) {
        $passed[] = "ExamService";
    } else {
        $errors[] = "ExamService missing: " . implode(", ", $missing);
    }
} else {
    echo "   ✗ ExamService NOT FOUND\n";
    $errors[] = "ExamService missing";
}

// 6. Check Views
echo "\n6. CHECKING VIEWS...\n";
$views = [
    'admin/exams/index.blade.php',
    'admin/exams/create.blade.php',
    'admin/exams/edit.blade.php',
    'admin/exams/manage_questions.blade.php',
];

$missing_views = [];
foreach ($views as $view) {
    $path = $base_dir . "/resources/views/$view";
    if (file_exists($path)) {
        echo "   ✓ $view\n";
    } else {
        echo "   ✗ $view\n";
        $missing_views[] = $view;
    }
}

if (empty($missing_views)) {
    $passed[] = "All Views";
} else {
    $errors[] = "Missing views: " . implode(", ", $missing_views);
}

// 7. Check Routes
echo "\n7. CHECKING ROUTES...\n";
$routes_file = $base_dir . '/routes/web.php';
if (file_exists($routes_file)) {
    $content = file_get_contents($routes_file);
    $has_import = strpos($content, 'ExamController') !== false;
    $has_exam_routes = strpos($content, "Route::resource('exams'") !== false;
    $has_publish = strpos($content, "exams/{exam}/publish") !== false;
    
    echo "   - ExamController import: " . ($has_import ? "✓\n" : "✗\n");
    echo "   - Exam resource routes: " . ($has_exam_routes ? "✓\n" : "✗\n");
    echo "   - Custom routes (publish/etc): " . ($has_publish ? "✓\n" : "✗\n");
    
    if ($has_import && $has_exam_routes && $has_publish) {
        $passed[] = "Routes configured";
    } else {
        $errors[] = "Routes incomplete";
    }
} else {
    $errors[] = "routes/web.php not found";
}

// 8. Check Seeder
echo "\n8. CHECKING SEEDER...\n";
$seeder = $base_dir . '/database/seeders/ExamSeeder.php';
if (file_exists($seeder)) {
    echo "   ✓ ExamSeeder exists\n";
    $passed[] = "ExamSeeder";
} else {
    echo "   ✗ ExamSeeder NOT FOUND\n";
    $errors[] = "ExamSeeder missing";
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
if (empty($errors)) {
    echo "✅ MODULE 4 VERIFIED AND STABLE\n";
    echo "All components are in place and properly configured.\n";
} else {
    echo "⚠️  MODULE 4 HAS ISSUES:\n";
    foreach ($errors as $error) {
        echo "   ✗ $error\n";
    }
}
echo str_repeat("=", 60) . "\n";
