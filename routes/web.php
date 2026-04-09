<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\GamificationController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\Communication\AnnouncementController;
use App\Http\Controllers\Communication\MessageController;
use App\Http\Controllers\Admin\ArenaController as AdminArenaController;
use App\Http\Controllers\Student\ArenaController as StudentArenaController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Student\LeaderboardController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\LeaderboardController as AdminLeaderboardController;
use App\Http\Controllers\Admin\GradeWeightController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ManualGradeController;
use App\Http\Controllers\Admin\ReportDataController;
use App\Http\Controllers\Admin\ExtracurricularController;
use App\Http\Controllers\Admin\ExtracurricularSessionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentDudiController;
use App\Http\Controllers\Admin\LetterTemplateController;
use App\Http\Controllers\Admin\LetterController;
use App\Http\Controllers\Tu\TuDashboardController;
use App\Http\Controllers\Admin\GradeLockController;
use App\Http\Controllers\InformationController;

// Public routes
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Public Info Page (announcements — no auth)
Route::get('/informasi', [InformationController::class, 'index'])->name('information.index');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard routes - protected by auth middleware
Route::middleware('auth')->group(function () {
    // Universal Theme Route — works for all roles
    Route::post('/profile/update-theme', [UserPreferenceController::class, 'update'])->name('profile.update-theme');
    // Redirect dashboard to appropriate role dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match ($user->role) {
            'superadmin' => redirect()->route('dashboard.superadmin'),
            'teacher'    => redirect()->route('dashboard.teacher'),
            'principal'  => redirect()->route('dashboard.principal'),
            'student'    => redirect()->route('dashboard.student'),
            'tu'         => redirect()->route('dashboard.tu'),
            default      => redirect()->route('login'),
        };
    })->name('dashboard');

    // Superadmin routes
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/dashboard/superadmin', [SuperAdminDashboardController::class, 'index'])->name('dashboard.superadmin');
        
        // Teacher Management (LMS)
        Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.'], function () {
            Route::resource('teachers', \App\Http\Controllers\Dashboard\SuperAdminTeacherController::class);
        });
    });

    // Letter Management (Templates & Generator)
    Route::group(['prefix' => 'admin/letters', 'as' => 'admin.letters.'], function () {
        
        // 1. Template Management
        // Template listing: visible to superadmin & TU (TU can view/use, not manage)
        Route::middleware('role:superadmin,tu')->get('templates', [LetterTemplateController::class, 'index'])->name('templates.index');

        // Template CRUD: superadmin & TU
        Route::middleware('role:superadmin,tu')->group(function() {
            Route::get('templates/create', [LetterTemplateController::class, 'create'])->name('templates.create');
            Route::post('templates', [LetterTemplateController::class, 'store'])->name('templates.store');
            Route::get('templates/{template}/edit', [LetterTemplateController::class, 'edit'])->name('templates.edit');
            Route::put('templates/{template}', [LetterTemplateController::class, 'update'])->name('templates.update');
            Route::post('templates/{template}/toggle', [LetterTemplateController::class, 'toggleActive'])->name('templates.toggle');
            Route::delete('templates/{template}', [LetterTemplateController::class, 'destroy'])->name('templates.destroy');
        });

        // 2. Generator (Superadmin & TU)
        Route::middleware('role:superadmin,tu')->group(function() {
            Route::get('/',                          [LetterController::class, 'index'])->name('index');
            Route::get('history',                    [LetterController::class, 'history'])->name('history');
            Route::delete('history/delete-all',      [LetterController::class, 'deleteAllHistory'])->name('history.deleteAll');
            Route::get('{letter}/redownload',        [LetterController::class, 'redownload'])->name('redownload');
            Route::delete('{letter}',                 [LetterController::class, 'deleteLetter'])->name('delete');
            Route::get('{template}/form',            [LetterController::class, 'form'])->name('form');
            Route::post('/{template}/preview',       [LetterController::class, 'preview'])->name('preview');
            Route::post('/{template}/generate',      [LetterController::class, 'generate'])->name('generate');
            Route::get('/{template}/bulk',           [LetterController::class, 'bulkForm'])->name('bulk.form');
            Route::post('/{template}/bulk',          [LetterController::class, 'bulkGenerate'])->name('bulk.generate');

            // Bulk progress page & AJAX
            Route::get('/bulk-download',                  [LetterController::class, 'bulkDownload'])->name('bulk.download');
            Route::post('/{template}/bulk-progress-page', [LetterController::class, 'bulkProgressPage'])->name('bulk.progress-page');
            Route::post('/{template}/bulk-progress',      [LetterController::class, 'bulkProgress'])->name('bulk.progress');
        });
    });

    // Shared Settings (Superadmin & TU)
    Route::middleware('role:superadmin,tu')->post('admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');

    // TU (Tata Usaha) routes
    Route::middleware('role:tu')->group(function () {
        Route::get('/dashboard/tu', [\App\Http\Controllers\Tu\TuDashboardController::class, 'index'])->name('dashboard.tu');
    });


    // Teacher & Principal routes
    Route::middleware('role:teacher,principal')->group(function () {
        // Teacher dashboard
        Route::get('/dashboard/teacher', [\App\Http\Controllers\Dashboard\TeacherDashboardController::class, 'index'])
            ->name('dashboard.teacher')
            ->middleware('role:teacher');

        // Principal dashboard (terpisah)
        Route::get('/dashboard/principal', [\App\Http\Controllers\Dashboard\PrincipalDashboardController::class, 'index'])
            ->name('dashboard.principal')
            ->middleware('role:principal');
        
        // Teacher Settings
        Route::group(['prefix' => 'teacher/settings', 'as' => 'teacher.settings.'], function () {
            Route::get('/', [\App\Http\Controllers\Teacher\TeacherSettingsController::class, 'index'])->name('index');
            Route::post('profile', [\App\Http\Controllers\Teacher\TeacherSettingsController::class, 'updateProfile'])->name('profile');
            Route::post('password', [\App\Http\Controllers\Teacher\TeacherSettingsController::class, 'updatePassword'])->name('password');
            Route::delete('signature', [\App\Http\Controllers\Teacher\TeacherSettingsController::class, 'deleteSignature'])->name('delete-signature');
        });
    });

    // Student routes
    Route::middleware('role:student')->group(function () {
        Route::get('/dashboard/student', [StudentDashboardController::class, 'index'])->name('dashboard.student');

        // Student Dashboard
        Route::get('/student', [StudentDashboardController::class, 'index'])->name('student.dashboard');
        Route::post('/student/notifications/read', [StudentDashboardController::class, 'markNotificationsRead'])->name('student.notifications.read');

        // Profile & Avatar Management
        Route::get('/student/profile', [\App\Http\Controllers\Student\AvatarController::class, 'index'])->name('student.profile');
        Route::post('/student/profile/avatar/gallery', [\App\Http\Controllers\Student\AvatarController::class, 'updateGallery'])->name('student.profile.avatar.gallery');
        Route::post('/student/profile/avatar/multiavatar', [\App\Http\Controllers\Student\AvatarController::class, 'saveMultiavatar'])->name('student.profile.avatar.multiavatar');
        Route::post('/student/profile/avatar/upload', [\App\Http\Controllers\Student\AvatarController::class, 'updateUpload'])->name('student.profile.avatar.upload');
        Route::post('/student/profile/avatar/delete-upload', [\App\Http\Controllers\Student\AvatarController::class, 'deleteUpload'])->name('student.profile.avatar.delete-upload');
        Route::post('/student/profile/avatar/reset', [\App\Http\Controllers\Student\AvatarController::class, 'resetToFormal'])->name('student.profile.avatar.reset');
        Route::post('/student/profile/theme', [\App\Http\Controllers\Student\AvatarController::class, 'updateTheme'])->name('student.profile.theme');
        Route::post('/student/profile/password', [\App\Http\Controllers\Student\AvatarController::class, 'updatePassword'])->name('student.profile.password');

        // Student Exam routes
        Route::group(['prefix' => 'student/exams', 'as' => 'student.exams.'], function () {
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
                Route::get('{attempt}/print', [StudentExamController::class, 'printReport'])->name('print');
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

        // ── Battle Arena Siswa (V2) ──────────────────────────────────────
        Route::prefix('student/arena')
            ->name('student.arena.')
            ->group(function () {

                Route::get('/', [StudentArenaController::class, 'index'])
                    ->name('index');

                Route::post('join', [StudentArenaController::class, 'join'])
                    ->name('join');

                Route::get('{room}/pick-group', [StudentArenaController::class, 'pickGroup'])
                    ->name('pick-group');

                Route::post('{room}/update-group', [StudentArenaController::class, 'updateGroup'])
                    ->name('update-group');

                Route::get('{room}/lobby', [StudentArenaController::class, 'lobby'])
                    ->name('lobby');

                Route::get('{room}/lobby/status', [StudentArenaController::class, 'lobbyStatus'])
                    ->name('lobby.status');

                Route::get('{room}/battle', [StudentArenaController::class, 'battle'])
                    ->name('battle');

                Route::get('{room}/battle/data', [StudentArenaController::class, 'battleData'])
                    ->name('battle.data');

                Route::post('{room}/answer', [StudentArenaController::class, 'submitAnswer'])
                    ->name('answer');
            });

        // Digital Coupon Wallet
        Route::get('student/coupons', [\App\Http\Controllers\Student\CouponController::class, 'index'])->name('student.coupons.index');

        // Leaderboard
        Route::get('student/leaderboard', [LeaderboardController::class, 'index'])->name('student.leaderboard');

        // Prestige
        Route::post('student/profile/prestige', [\App\Http\Controllers\Student\PrestigeController::class, 'prestige'])->name('student.prestige');

    });

    // Subject & Question Management routes
    Route::group(['middleware' => 'role:teacher,principal,superadmin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
        // [MODUL SUPERADMIN ONLY] Subject routes
        Route::middleware('role:superadmin')->group(function () {
            Route::post('subjects/reorder', [SubjectController::class, 'reorder'])->name('subjects.reorder');
            Route::delete('subjects/delete-all', [SubjectController::class, 'deleteAllSubjects'])->name('subjects.deleteAll');
            Route::resource('subjects', SubjectController::class);
        });

        // Question routes with import/export
        Route::get('questions/import/form', [QuestionController::class, 'importForm'])->name('questions.importForm');
        Route::get('questions/import/template', [QuestionController::class, 'downloadTemplate'])->name('questions.download-template');
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
        Route::get('exams/{exam}/print-questions', [ExamController::class, 'printQuestions'])->name('exams.print-questions');
        Route::get('exams/{exam}/print-card', [ExamCardController::class, 'printCard'])->name('exams.print-card');
        Route::get('exams/{exam}/print-credentials', [ExamCardController::class, 'printStudentCredentials'])->name('exams.print-credentials');
        Route::get('exams/print-all-cards', [ExamCardController::class, 'printAllCards'])->name('exams.print-all-cards');
        Route::get('exams/{exam}/card/{studentId}', [ExamCardController::class, 'generateStudentCard'])->name('exams.card-single');

        Route::resource('exams', ExamController::class);

        // E-Learning Management
        Route::group(['prefix' => 'learning', 'as' => 'learning.'], function () {
            Route::resource('materials', \App\Http\Controllers\Admin\LearningMaterialController::class);
            Route::post('materials/{material}/sections/reorder', [\App\Http\Controllers\Admin\LearningSectionController::class, 'reorder'])->name('sections.reorder');
            Route::delete('sections/{section}/file', [\App\Http\Controllers\Admin\LearningSectionController::class, 'deleteFile'])->name('sections.delete-file');
            Route::resource('materials.sections', \App\Http\Controllers\Admin\LearningSectionController::class)->shallow();
        });

        // Report Data Management (Sprint 1.5)
        Route::group(['prefix' => 'report-data', 'as' => 'report-data.'], function () {
            Route::get('/', [ReportDataController::class, 'index'])->name('index');
            Route::get('/students', [ReportDataController::class, 'studentDataForm'])->name('student-data');
            Route::post('/students', [ReportDataController::class, 'saveStudentData'])->name('save-student-data');
            Route::get('/import', [ReportDataController::class, 'importForm'])->name('import');
            Route::post('/import', [ReportDataController::class, 'import'])->name('import.post');
            Route::get('/template', [ReportDataController::class, 'downloadTemplate'])->name('download-template');
            Route::get('/class-average', [ReportDataController::class, 'classAverageForm'])->name('class-average');
            Route::post('/class-average', [ReportDataController::class, 'saveClassAverage'])->name('save-class-average');
        });

        // Extracurricular Management (Module Dedicated)
        Route::group(['prefix' => 'extracurriculars', 'as' => 'extracurriculars.'], function () {
            Route::get('/',                              [ExtracurricularController::class, 'index'])->name('index');
            Route::post('/',                             [ExtracurricularController::class, 'store'])->name('store');
            
            // Extracurricular Sessions (Journal & Presence) — NEW
            Route::group(['prefix' => '{extracurricular}/sessions', 'as' => 'sessions.'], function () {
                Route::get('/',             [ExtracurricularSessionController::class, 'index'])->name('index');
                Route::get('/create',       [ExtracurricularSessionController::class, 'create'])->name('create');
                Route::post('/',            [ExtracurricularSessionController::class, 'store'])->name('store');
                Route::get('/recap',        [ExtracurricularSessionController::class, 'recap'])->name('recap');
                Route::get('/export/excel', [ExtracurricularSessionController::class, 'exportExcel'])->name('export.excel');
                Route::get('/export/pdf',   [ExtracurricularSessionController::class, 'exportPdf'])->name('export.pdf');
                Route::get('/{session}',    [ExtracurricularSessionController::class, 'show'])->name('show');
                Route::delete('/{session}', [ExtracurricularSessionController::class, 'destroy'])->name('destroy');
            });

            // Ekstrakurikuler (Sprint 2 — Input Nilai)
            Route::get('/my-assignments', [ExtracurricularController::class, 'myAssignments'])->name('my-assignments');
            
            Route::get('/{extracurricular}',             [ExtracurricularController::class, 'show'])->name('show');
            Route::get('/{extracurricular}/edit',        [ExtracurricularController::class, 'edit'])->name('edit');
            Route::put('/{extracurricular}/detail',      [ExtracurricularController::class, 'updateDetail'])->name('update-detail');
            Route::get('/{extracurricular}/grades', [ExtracurricularController::class, 'gradesForm'])->name('grades');
            Route::post('/{extracurricular}/grades', [ExtracurricularController::class, 'gradesSave'])->name('grades.save');

            Route::patch('/{extracurricular}',           [ExtracurricularController::class, 'update'])->name('update');
            Route::delete('/{extracurricular}',          [ExtracurricularController::class, 'destroy'])->name('destroy');
            Route::post('/reorder',                      [ExtracurricularController::class, 'reorder'])->name('reorder');

            // Coach management
            Route::post('/{extracurricular}/coaches',             [ExtracurricularController::class, 'addCoach'])->name('coaches.add');
            Route::delete('/{extracurricular}/coaches/{coach}',   [ExtracurricularController::class, 'removeCoach'])->name('coaches.remove');

            // Member management
            Route::post('/{extracurricular}/members',             [ExtracurricularController::class, 'addMembers'])->name('members.add');
            Route::delete('/{extracurricular}/members/{member}',  [ExtracurricularController::class, 'removeMember'])->name('members.remove');
        });

        // Exam Results and Reporting routes Module 6
        Route::group(['prefix' => 'results', 'as' => 'results.'], function () {
            Route::get('/', [ResultController::class, 'index'])->name('index');
            Route::get('{examId}', [ResultController::class, 'show'])->name('show')->where('examId', '[0-9]+');
            Route::get('{examId}/review/{attemptId}', [ResultController::class, 'review'])->name('review')->where(['examId' => '[0-9]+', 'attemptId' => '[0-9]+']);
            Route::post('{examId}/review/{attemptId}/update-grades', [ResultController::class, 'updateGrades'])->name('update-grades')->where(['examId' => '[0-9]+', 'attemptId' => '[0-9]+']);
            Route::get('{examId}/export', [ResultController::class, 'export'])->name('export')->where('examId', '[0-9]+');
            Route::post('{examId}/apply-adjustment', [ResultController::class, 'applyAdjustment'])->name('apply-adjustment')->where('examId', '[0-9]+');
            Route::post('{examId}/reset-adjustment', [ResultController::class, 'resetAdjustment'])->name('reset-adjustment')->where('examId', '[0-9]+');
        });

        // Token Management Routes (New Module - Monitoring & Security)
        Route::group(['prefix' => 'tokens', 'as' => 'tokens.'], function () {
            Route::get('/', [TokenController::class, 'index'])->name('index');
            Route::post('exams/{exam}/generate', [TokenController::class, 'generateTokens'])->name('generate');
            Route::get('exams/{exam}/list', [TokenController::class, 'listTokens'])->name('list');
            Route::delete('{token}/revoke', [TokenController::class, 'revokeToken'])->name('revoke');
        });

        // Monitoring Exams List Route
        Route::get('monitor-exams', [MonitoringController::class, 'listExams'])->name('monitor-exams.index');

        // Monitoring & Real-Time Dashboard Routes (New Module)
        Route::group(['prefix' => 'monitor', 'as' => 'monitor.'], function () {
            Route::get('exams/{exam}', [MonitoringController::class, 'index'])->name('exams.index');
            Route::post('attempts/{attempt}/reopen', [MonitoringController::class, 'reopenSession'])->name('attempts.reopen');
            Route::post('attempts/{attempt}/reset', [MonitoringController::class, 'resetAnswers'])->name('attempts.reset');
        });

        // ── Sprint 1: Bobot Nilai (teacher & superadmin) ──────────────────────
        Route::resource('grade-weights', GradeWeightController::class)->except(['show', 'destroy']);

        // ── Sprint 2: Input Nilai Manual (teacher & superadmin) ───────────────
        Route::group(['prefix' => 'manual-grades', 'as' => 'manual-grades.'], function () {
            Route::get('/',               [ManualGradeController::class, 'index'])->name('index');
            Route::get('/input',          [ManualGradeController::class, 'inputForm'])->name('input');
            Route::post('/input',         [ManualGradeController::class, 'store'])->name('store');
            Route::get('/import',         [ManualGradeController::class, 'importForm'])->name('import-form');
            Route::post('/import',        [ManualGradeController::class, 'import'])->name('import');
            Route::get('/template',       [ManualGradeController::class, 'downloadTemplate'])->name('download-template');
        });


        Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
            Route::get('/',                          [ReportController::class, 'index'])->name('index');
            Route::get('/preview/{student}',         [ReportController::class, 'preview'])->name('preview');
            Route::get('/print/{student}',           [ReportController::class, 'printSingle'])->name('printSingle');
            Route::get('/print-class/{class}',       [ReportController::class, 'printClass'])->name('printClass');
            Route::post('/notes',                    [ReportController::class, 'saveNote'])->name('notes');
        });

        // Grade Locks
        Route::post('grade-locks/toggle', [GradeLockController::class, 'toggle'])->name('grade-locks.toggle');
        Route::get('grade-locks/status', [GradeLockController::class, 'status'])->name('grade-locks.status');

        // ── Kegiatan DU/DI (Dunia Usaha / Dunia Industri) ─────────────────────
        Route::group(['prefix' => 'dudi', 'as' => 'dudi.'], function () {
            Route::get('/',                        [StudentDudiController::class, 'index'])->name('index');
            Route::get('/student/{student}/edit',  [StudentDudiController::class, 'edit'])->name('edit');
            Route::put('/student/{student}',       [StudentDudiController::class, 'update'])->name('update');
            Route::post('/import',                 [StudentDudiController::class, 'import'])->name('import');
            Route::get('/template',                [StudentDudiController::class, 'downloadTemplate'])->name('template');
        });

        // [SHARED ADMIN SETTINGS]
        // Accessible by superadmin and tu
        Route::middleware('role:superadmin,tu')->post('admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

        // [MODUL SUPERADMIN ONLY] Student Management routes
        Route::middleware('role:superadmin')->group(function () {
            // ── Sprint 1: Class Management (superadmin only) ──────────────────
            Route::resource('classes', ClassController::class);
            // Explicit routes must come before resource()
            Route::get('students/import/form', [StudentController::class, 'importForm'])->name('students.importForm');
            Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
            Route::get('students/import/result', [StudentController::class, 'importResult'])->name('students.importResult');
            Route::get('students/export', [StudentController::class, 'export'])->name('students.export');
            Route::post('students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.resetPassword');
            Route::post('students/reset-all-passwords', [StudentController::class, 'resetAllPasswords'])->name('students.resetAllPasswords');
            Route::delete('students/delete-all', [StudentController::class, 'deleteAllStudents'])->name('students.deleteAll');
            Route::post('students/{student}/toggle-active', [StudentController::class, 'toggleActive'])->name('students.toggleActive');
            Route::get('students/upload-photos', [StudentController::class, 'uploadPhotosForm'])->name('students.upload-photos');
            Route::post('students/upload-photos', [StudentController::class, 'uploadPhotos'])->name('students.upload-photos.post');

            // ── Annual Migration & Re-Mapping ────────────────────────────────
            Route::get('students/migration', [StudentController::class, 'migration'])->name('students.migration');
            Route::post('students/migration/execute', [StudentController::class, 'executeAnnualMigration'])->name('students.migration.execute');

            // Resource routes last
            Route::resource('students', StudentController::class);

            // Alumni management
            Route::get('alumni', [AlumniController::class, 'index'])->name('alumni.index');

            // Settings Management
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('settings/profile', [SettingController::class, 'updateProfile'])->name('settings.update-profile');
            Route::delete('settings/signature', [SettingController::class, 'deleteSignature'])->name('settings.delete-signature');

            // Gamification Center
            Route::group(['prefix' => 'gamification', 'as' => 'gamification.'], function () {
                Route::get('settings', [GamificationController::class, 'globalSettings'])->name('settings');
                Route::post('settings', [GamificationController::class, 'updateGlobalSettings'])->name('settings.update');
                Route::get('achievements', [GamificationController::class, 'achievements'])->name('achievements');
                Route::get('achievements/create', [GamificationController::class, 'createAchievement'])->name('achievements.create');
                Route::post('achievements', [GamificationController::class, 'storeAchievement'])->name('achievements.store');
                Route::get('achievements/{achievement}/edit', [GamificationController::class, 'editAchievement'])->name('achievements.edit');
                Route::post('achievements/{achievement}', [GamificationController::class, 'updateAchievement'])->name('achievements.update');
                Route::delete('achievements/{achievement}', [GamificationController::class, 'destroyAchievement'])->name('achievements.destroy');

                // Themes Management
                Route::get('themes', [GamificationController::class, 'themes'])->name('themes');
                Route::get('themes/create', [GamificationController::class, 'createTheme'])->name('themes.create');
                Route::post('themes', [GamificationController::class, 'storeTheme'])->name('themes.store');
                Route::get('themes/{theme}/edit', [GamificationController::class, 'editTheme'])->name('themes.edit');
                Route::post('themes/{theme}', [GamificationController::class, 'updateTheme'])->name('themes.update');
                Route::delete('themes/{theme}', [GamificationController::class, 'destroyTheme'])->name('themes.destroy');
                
                // ── Leaderboard & Hall of Fame ──────────────────────────────
                Route::group(['prefix' => 'leaderboard', 'as' => 'leaderboard.'], function () {
                    Route::get('/', [AdminLeaderboardController::class, 'index'])->name('index');
                    Route::get('hall-of-fame', [AdminLeaderboardController::class, 'hallOfFame'])->name('hall-of-fame');
                    Route::post('refresh', [AdminLeaderboardController::class, 'refreshCache'])->name('refresh');
                });
                
                // ── Battle Arena Admin (V2) ───────────────────────────────────
                Route::prefix('arena')
                    ->name('arena.')
                    ->middleware(['auth', 'role:superadmin,teacher'])
                    ->group(function () {

                        Route::get('/', [AdminArenaController::class, 'index'])
                            ->name('index');

                        Route::get('create', [AdminArenaController::class, 'create'])
                            ->name('create');

                        Route::post('/', [AdminArenaController::class, 'store'])
                            ->name('store');

                        Route::delete('{room}', [AdminArenaController::class, 'destroy'])
                            ->name('destroy');

                        // Control panel guru
                        Route::get('{room}/control', [AdminArenaController::class, 'control'])
                            ->name('control');

                        Route::post('{room}/control/state', [AdminArenaController::class, 'setState'])
                            ->name('control.setState');

                        Route::get('{room}/control/data', [AdminArenaController::class, 'controlData'])
                            ->name('control.data');

                        Route::post('{room}/toggle-show-question', [AdminArenaController::class, 'toggleShowQuestion'])
                            ->name('toggle-show-question');

                        Route::post('{room}/toggle-lock', [AdminArenaController::class, 'toggleLock'])
                            ->name('toggle-lock');

                        // Proyektor display
                        Route::get('{room}/display', [AdminArenaController::class, 'display'])
                            ->name('display');

                        Route::get('{room}/display/data', [AdminArenaController::class, 'displayData'])
                            ->name('display.data');

                        // Podium & debriefing
                        Route::get('{room}/podium', [AdminArenaController::class, 'podium'])
                            ->name('podium');

                        Route::get('{room}/debriefing', [AdminArenaController::class, 'debriefing'])
                            ->name('debriefing');

                        // AJAX — exam preview (Sprint 2)
                        Route::get('exam-preview', [AdminArenaController::class, 'examPreview'])
                            ->name('exam.preview');
                    });

                // ── Season Management ─────────────────────────────────────────
                Route::group(['prefix' => 'seasons', 'as' => 'seasons.'], function () {
                    Route::get('/', [SeasonController::class, 'index'])->name('index');
                    Route::get('create', [SeasonController::class, 'create'])->name('create');
                    Route::post('/', [SeasonController::class, 'store'])->name('store');
                    Route::get('{season}/edit', [SeasonController::class, 'edit'])->name('edit');
                    Route::put('{season}', [SeasonController::class, 'update'])->name('update');
                    
                    // Lifecycle actions
                    Route::post('{season}/close', [SeasonController::class, 'close'])->name('close');
                    Route::post('{season}/activate', [SeasonController::class, 'activate'])->name('activate');
                    Route::post('start', [SeasonController::class, 'startNew'])->name('start');
                    Route::delete('{season}', [SeasonController::class, 'destroy'])->name('destroy');
                });

                // ── Physical Reward Coupons ──────────────────────────────────
                Route::group(['prefix' => 'coupons', 'as' => 'coupons.'], function () {
                    Route::get('/', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('index');
                    Route::post('{coupon}/claim', [\App\Http\Controllers\Admin\CouponController::class, 'claim'])->name('claim');
                });
            });
        });
    });

    // ── Communication Hub ────────────────────────────────────────────
    // Accessible by ALL authenticated roles: student, teacher, superadmin
    Route::group(['prefix' => 'communication', 'as' => 'communication.'], function () {

        // Announcements (all roles read; staff create/delete)
        Route::post('notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name('notifications.read.all');

        // Polling endpoint for Alpine.js Toast
        Route::get('notifications/latest-unread', function () {
            if (!auth()->check()) return response()->json(['notification' => null, 'unread_count' => 0]);
            
            $user = auth()->user();
            $notification = $user->unreadNotifications()->latest()->first();
            $unreadCount = $user->unreadNotifications->count();
            
            return response()->json([
                'notification' => $notification,
                'unread_count' => $unreadCount
            ]);
        })->name('notifications.latest');

        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::middleware('role:superadmin,teacher,principal,tu')->group(function () {
            Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
            Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
            Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
            Route::post('announcements/{announcement}/toggle', [AnnouncementController::class, 'toggleActive'])->name('announcements.toggle');
        });
        Route::delete('announcements/{announcement}/force', [AnnouncementController::class, 'permanentDelete'])
             ->name('announcements.force-delete')
             ->middleware('role:superadmin');

        // Direct Messaging (all roles inbox; students reply-only)
        Route::get('messages', [MessageController::class, 'inbox'])->name('messages.inbox');
        Route::get('messages/poll', [MessageController::class, 'pollInbox'])->name('messages.poll-inbox');
        Route::get('messages/{id}', [MessageController::class, 'thread'])->name('messages.thread')->where('id', '[0-9]+');
        Route::get('messages/{id}/poll', [MessageController::class, 'pollThread'])->name('messages.poll-thread')->where('id', '[0-9]+');
        Route::post('messages', [MessageController::class, 'send'])->name('messages.send');
        Route::post('messages/{id}/read', [MessageController::class, 'markRead'])->name('messages.read')->where('id', '[0-9]+');
        Route::delete('messages/{message}', [MessageController::class, 'deleteMessage'])->name('messages.delete');
        Route::delete('messages/{message}/thread', [MessageController::class, 'deleteThread'])->name('messages.delete-thread');
    });

    // ── Self-Service Letters ─────────────────────────
    // SPPD Guru (teacher & principal)
    Route::middleware('role:superadmin,tu,teacher,principal')
        ->group(function () {
            Route::get('/self-service/sppd',
                [\App\Http\Controllers\SelfServiceLetterController::class,
                 'sppdForm'])
                ->name('self-service.sppd.form');
            Route::post('/self-service/sppd',
                [\App\Http\Controllers\SelfServiceLetterController::class,
                 'sppdGenerate'])
                ->name('self-service.sppd.generate');
        });

    // Surat Keterangan Aktif Siswa
    Route::middleware('role:student')
        ->group(function () {
            Route::get('/self-service/sk-aktif',
                [\App\Http\Controllers\SelfServiceLetterController::class,
                 'skForm'])
                ->name('self-service.sk.form');
            Route::post('/self-service/sk-aktif',
                [\App\Http\Controllers\SelfServiceLetterController::class,
                 'skGenerate'])
                ->name('self-service.sk.generate');
        });

    // ── E-Learning Viewer ───────────────────────────────────────────
    // Accessible by all academic roles (students learn, teachers present)
    Route::group(['middleware' => 'role:student,teacher,superadmin,principal,tu', 'prefix' => 'learning', 'as' => 'learning.'], function () {
        Route::get('/', [\App\Http\Controllers\Student\LearningController::class, 'index'])->name('index');
        Route::get('{material}', [\App\Http\Controllers\Student\LearningController::class, 'show'])->name('show');
        Route::post('{material}/complete', [\App\Http\Controllers\Student\LearningController::class, 'complete'])->name('complete');
    });

}); // end auth middleware group
