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
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\GamificationController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\Communication\AnnouncementController;
use App\Http\Controllers\Communication\MessageController;
use App\Http\Controllers\Admin\ArenaController;
use App\Http\Controllers\Admin\AlumniController;

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
    // Universal Theme Route — works for all roles
    Route::post('/profile/update-theme', [UserPreferenceController::class, 'update'])->name('profile.update-theme');
    // Redirect dashboard to appropriate role dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match ($user->role) {
            'superadmin' => redirect()->route('dashboard.superadmin'),
            'teacher' => redirect()->route('dashboard.teacher'),
            'student' => redirect()->route('dashboard.student'),
            default => redirect()->route('login'),
        };
    })->name('dashboard');

    // Superadmin routes
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/dashboard/superadmin', [SuperAdminDashboardController::class, 'index'])->name('dashboard.superadmin');
        
        // Teacher Management (LMS)
        Route::prefix('superadmin')->name('superadmin.')->group(function () {
            Route::resource('teachers', \App\Http\Controllers\Dashboard\SuperAdminTeacherController::class);
        });
    });

    // Teacher routes
    Route::middleware('role:teacher')->group(function () {
        Route::get('/dashboard/teacher', [\App\Http\Controllers\Dashboard\TeacherDashboardController::class, 'index'])->name('dashboard.teacher');
        
        // Teacher Settings
        Route::prefix('teacher/settings')->name('teacher.settings.')->group(function () {
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

        // ── Student Battle Arena ──────────────────────────────────────────
        Route::post('student/arena/join', [ArenaController::class, 'studentJoin'])->name('student.arena.join');
        Route::get('student/arena/{room}/lobby', [ArenaController::class, 'studentLobby'])->name('student.arena.lobby');
        Route::get('student/arena/{room}/lobby/status', [ArenaController::class, 'studentLobbyStatus'])->name('student.arena.lobby.status');
        Route::get('student/arena/{room}/battle/{participant}', [ArenaController::class, 'battle'])->name('student.arena.battle');
        Route::post('student/arena/{room}/battle/{participant}/submit', [ArenaController::class, 'submitAnswer'])->name('student.arena.submit');
        Route::post('student/arena/{room}/battle/{participant}/heartbeat', [ArenaController::class, 'heartbeat'])->name('student.arena.heartbeat');
        Route::post('student/arena/{room}/battle/{participant}/tab-penalty', [ArenaController::class, 'tabPenalty'])->name('student.arena.tab-penalty');

        // Digital Coupon Wallet
        Route::get('student/coupons', [\App\Http\Controllers\Student\CouponController::class, 'index'])->name('student.coupons.index');
    });

    // Subject & Question Management routes
    Route::middleware('role:teacher,superadmin')->prefix('admin')->name('admin.')->group(function () {
        // [MODUL SUPERADMIN ONLY] Subject routes
        Route::middleware('role:superadmin')->group(function () {
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
            Route::post('{examId}/apply-adjustment', [ResultController::class, 'applyAdjustment'])->name('apply-adjustment')->where('examId', '[0-9]+');
            Route::post('{examId}/reset-adjustment', [ResultController::class, 'resetAdjustment'])->name('reset-adjustment')->where('examId', '[0-9]+');
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
            Route::post('attempts/{attempt}/reset', [MonitoringController::class, 'resetAnswers'])->name('attempts.reset');
        });

        // [MODUL SUPERADMIN ONLY] Student Management routes
        Route::middleware('role:superadmin')->group(function () {
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
            Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
            Route::post('settings/profile', [SettingController::class, 'updateProfile'])->name('settings.update-profile');
            Route::delete('settings/signature', [SettingController::class, 'deleteSignature'])->name('settings.delete-signature');

            // Gamification Center
            Route::prefix('gamification')->name('gamification.')->group(function () {
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

                // ── Battle Arena ──────────────────────────────────────────────
                Route::prefix('arena')->name('arena.')->group(function () {
                    Route::get('/', [ArenaController::class, 'index'])->name('index');
                    Route::get('create', [ArenaController::class, 'create'])->name('create');
                    Route::post('/', [ArenaController::class, 'store'])->name('store');
                    Route::get('{room}/lobby', [ArenaController::class, 'lobby'])->name('lobby');
                    Route::post('{room}/ignite', [ArenaController::class, 'ignite'])->name('ignite');
                    Route::get('{room}/spectator', [ArenaController::class, 'spectator'])->name('spectator');
                    Route::get('{room}/spectator/data', [ArenaController::class, 'spectatorData'])->name('spectator.data');
                    Route::post('{room}/finish', [ArenaController::class, 'finish'])->name('finish');
                    Route::get('{room}/podium', [ArenaController::class, 'podium'])->name('podium');
                    Route::get('{room}/debriefing', [ArenaController::class, 'debriefing'])->name('debriefing');
                    Route::delete('{room}', [ArenaController::class, 'destroy'])->name('destroy');
                });

                // ── Physical Reward Coupons ──────────────────────────────────
                Route::prefix('coupons')->name('coupons.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('index');
                    Route::post('{coupon}/claim', [\App\Http\Controllers\Admin\CouponController::class, 'claim'])->name('claim');
                });
            });
        });
    });

    // ── Communication Hub ────────────────────────────────────────────
    // Accessible by ALL authenticated roles: student, teacher, superadmin
    Route::prefix('communication')->name('communication.')->group(function () {

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
        Route::middleware('role:superadmin,teacher')->group(function () {
            Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
            Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
            Route::post('announcements/{announcement}/toggle', [AnnouncementController::class, 'toggleActive'])->name('announcements.toggle');
        });
        Route::delete('announcements/{announcement}/force', [AnnouncementController::class, 'permanentDelete'])
             ->name('announcements.force-delete')
             ->middleware('role:superadmin');

        // Direct Messaging (all roles inbox; students reply-only)
        Route::get('messages', [MessageController::class, 'inbox'])->name('messages.inbox');
        Route::get('messages/{rootId}', [MessageController::class, 'thread'])->name('messages.thread')->where('rootId', '[0-9]+');
        Route::post('messages', [MessageController::class, 'send'])->name('messages.send');
        Route::post('messages/{rootId}/read', [MessageController::class, 'markRead'])->name('messages.read')->where('rootId', '[0-9]+');
        Route::delete('messages/{message}', [MessageController::class, 'deleteMessage'])->name('messages.delete');
        Route::delete('messages/{message}/thread', [MessageController::class, 'deleteThread'])->name('messages.delete-thread');
    });

}); // end auth middleware group
