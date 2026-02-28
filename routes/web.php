<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\SuperAdminDashboardController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\StudentDashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\ExamCardController;
use App\Http\Controllers\Student\StudentExamController;
use App\Http\Controllers\Student\StudentResultController;
use App\Http\Controllers\Student\HeartbeatController;
use App\Http\Controllers\Api\ExamProgressController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\MonitoringController;

// Public routes
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard routes - protected by auth middleware
Route::middleware('auth')->group(function () {
    // Redirect dashboard to appropriate role dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match ($user->role) {
            'superadmin' => redirect()->route('dashboard.superadmin'),
            'admin' => redirect()->route('dashboard.admin'),
            'student' => redirect()->route('dashboard.student'),
            default => redirect()->route('login'),
        };
    })->name('dashboard');

    // Superadmin routes
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/dashboard/superadmin', [SuperAdminDashboardController::class, 'index'])->name('dashboard.superadmin');
    });

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])->name('dashboard.admin');
    });

    // Student routes
    Route::middleware('role:student')->group(function () {
        Route::get('/dashboard/student', [StudentDashboardController::class, 'index'])->name('dashboard.student');

        // Student Exam routes
        Route::prefix('student/exams')->name('student.exams.')->group(function () {
            Route::get('/', [StudentExamController::class, 'index'])->name('index');
            Route::get('{exam}/start', [StudentExamController::class, 'start'])->name('start');
            Route::post('{exam}/start', function ($exam) {
                return redirect()->route('student.exams.start', $exam)->with('error', 'Gunakan tombol submit pada form validasi token.');
            });
            Route::post('{exam}/validate-and-start', [StudentExamController::class, 'validateAndStart'])->name('validate-and-start');

            // Protected routes - require exam session validation
            Route::middleware('verify.exam.session')->group(function () {
                Route::get('{attempt}', [StudentExamController::class, 'take'])->name('take');
                Route::post('{attempt}/autosave', [StudentExamController::class, 'autosave'])->name('autosave');
                Route::post('{attempt}/submit', [StudentExamController::class, 'submit'])->name('submit');
                Route::get('{attempt}/result', [StudentExamController::class, 'result'])->name('result');
                Route::get('{attempt}/remaining-time', [StudentExamController::class, 'getRemainingTime'])->name('remaining-time');
                Route::post('{attempt}/save-violation', [StudentExamController::class, 'saveViolation'])->name('save-violation');
                Route::post('{attempt}/force-submit', [StudentExamController::class, 'forceSubmit'])->name('force-submit');

                // Heartbeat & Session Management Routes (New)
                Route::post('{attempt}/heartbeat', [HeartbeatController::class, 'recordHeartbeat'])->name('heartbeat');
                Route::get('{attempt}/session-status', [HeartbeatController::class, 'getSessionStatus'])->name('session-status');
                Route::post('{attempt}/sync-offline', [HeartbeatController::class, 'syncOfflineAnswers'])->name('sync-offline');
                Route::post('{attempt}/disconnect', [HeartbeatController::class, 'disconnectSession'])->name('disconnect');

                // Real-Time Progress Tracking Routes (New)
                Route::post('{attempt}/record-answer', [ExamProgressController::class, 'recordAnswer'])->name('record-answer');
                Route::post('{attempt}/report-violation', [ExamProgressController::class, 'reportViolation'])->name('report-violation');
                Route::get('{attempt}/progress', [ExamProgressController::class, 'getSessionProgress'])->name('progress');
            });
        });

        // Student Results routes
        Route::get('student/results', [StudentResultController::class, 'index'])->name('student.results');
    });

    // Subject & Question Management routes (Admin & Superadmin only)
    Route::middleware('role:admin,superadmin')->prefix('admin')->name('admin.')->group(function () {
        // Subject routes
        Route::delete('subjects/delete-all', [SubjectController::class, 'deleteAllSubjects'])->name('subjects.deleteAll');
        Route::resource('subjects', SubjectController::class);

        // Question routes with import/export
        Route::get('questions/import/form', [QuestionController::class, 'importForm'])->name('questions.importForm');
        Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
        Route::get('questions/import/result', [QuestionController::class, 'importResult'])->name('questions.importResult');
        Route::get('questions/export', [QuestionController::class, 'export'])->name('questions.export');
        Route::delete('questions/bulk-delete', [QuestionController::class, 'bulkDelete'])->name('questions.bulkDelete');
        Route::delete('questions/delete-all', [QuestionController::class, 'deleteAllQuestions'])->name('questions.deleteAll');
        Route::resource('questions', QuestionController::class);

        // Exam Management routes
        Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
        Route::post('exams/{exam}/set-to-draft', [ExamController::class, 'setToDraft'])->name('exams.set-to-draft');
        Route::post('exams/{exam}/generate-token', [ExamController::class, 'generateToken'])->name('exams.generate-token');
        Route::post('exams/{exam}/refresh-token', [ExamController::class, 'refreshToken'])->name('exams.refresh-token');
        Route::post('exams/{exam}/update-token', [ExamController::class, 'updateToken'])->name('exams.update-token');
        Route::get('exams/{exam}/questions', [ExamController::class, 'manageQuestions'])->name('exams.manage-questions');
        Route::post('exams/{exam}/questions/attach', [ExamController::class, 'attachQuestions'])->name('exams.attach-questions');
        Route::post('exams/{exam}/questions/auto-add', [ExamController::class, 'autoAddQuestions'])->name('exams.auto-add-questions');
        Route::post('exams/{exam}/questions/detach', [ExamController::class, 'detachQuestion'])->name('exams.detach-question');
        Route::post('exams/{exam}/questions/detach-all', [ExamController::class, 'detachAllQuestions'])->name('exams.detach-all-questions');

        // Exam Card / Certificate Routes
        Route::get('exams/{exam}/print-card', [ExamCardController::class, 'printCard'])->name('exams.print-card');
        Route::get('exams/{exam}/print-credentials', [ExamCardController::class, 'printStudentCredentials'])->name('exams.print-credentials');
        Route::get('exams/print-all-cards', [ExamCardController::class, 'printAllCards'])->name('exams.print-all-cards');
        Route::get('exams/{exam}/card/{studentId}', [ExamCardController::class, 'generateStudentCard'])->name('exams.card-single');

        Route::resource('exams', ExamController::class);

        // Exam Results and Reporting routes Module 6
        Route::prefix('results')->name('results.')->group(function () {
            Route::get('/', [ResultController::class, 'index'])->name('index');
            Route::get('{examId}', [ResultController::class, 'show'])->name('show')->where('examId', '[0-9]+');
            Route::get('{examId}/review/{attemptId}', [ResultController::class, 'review'])->name('review')->where(['examId' => '[0-9]+', 'attemptId' => '[0-9]+']);
            Route::post('{examId}/review/{attemptId}/update-grades', [ResultController::class, 'updateGrades'])->name('update-grades')->where(['examId' => '[0-9]+', 'attemptId' => '[0-9]+']);
            Route::get('{examId}/export', [ResultController::class, 'export'])->name('export')->where('examId', '[0-9]+');
        });

        // Token Management Routes (New Module - Monitoring & Security)
        Route::prefix('tokens')->name('tokens.')->group(function () {
            Route::get('/', [TokenController::class, 'index'])->name('index');
            Route::post('exams/{exam}/generate', [TokenController::class, 'generateTokens'])->name('generate');
            Route::get('exams/{exam}/list', [TokenController::class, 'listTokens'])->name('list');
            Route::delete('{token}/revoke', [TokenController::class, 'revokeToken'])->name('revoke');
        });

        // Monitoring Exams List Route
        Route::get('monitor-exams', [MonitoringController::class, 'listExams'])->name('monitor-exams.index');

        // Monitoring & Real-Time Dashboard Routes (New Module)
        Route::prefix('monitor')->name('monitor.')->group(function () {
            Route::get('exams/{exam}', [MonitoringController::class, 'index'])->name('exams.index');
            Route::post('attempts/{attempt}/reopen', [MonitoringController::class, 'reopenSession'])->name('attempts.reopen');
        });

        // Student Management routes
        // Explicit routes must come before resource()
        Route::get('students/import/form', [StudentController::class, 'importForm'])->name('students.importForm');
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('students/import/result', [StudentController::class, 'importResult'])->name('students.importResult');
        Route::get('students/export', [StudentController::class, 'export'])->name('students.export');
        Route::post('students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.resetPassword');
        Route::post('students/reset-all-passwords', [StudentController::class, 'resetAllPasswords'])->name('students.resetAllPasswords');
        Route::delete('students/delete-all', [StudentController::class, 'deleteAllStudents'])->name('students.deleteAll');
        Route::post('students/{student}/toggle-active', [StudentController::class, 'toggleActive'])->name('students.toggleActive');

        // Resource routes last
        Route::resource('students', StudentController::class);
    });
});
