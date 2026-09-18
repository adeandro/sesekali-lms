<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Announcement;
use App\Models\Message;
use App\Models\Setting;
use App\Models\TypingTest;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\MessagePolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        \Carbon\Carbon::setLocale('id'); // Sprint 3: tgl tanda tangan raport Bahasa Indonesia
        Gate::policy(User::class, StudentPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);

        // Global Configurations with View Composer
        View::composer('*', function ($view) {
            static $configs = null;
            if ($configs === null) {
                $configs = [
                    'school_name' => 'ExamFlow',
                    'max_violations' => 3,
                    'anti_cheat_active' => 1,
                    'logo' => null,
                    'academic_year' => '2023/2024',
                    'enable_gamification' => '1',
                    'enable_leaderboard' => '1',
                    'enable_theme_customization' => '1'
                ];

                try {
                    if (Schema::hasTable('settings')) {
                        $dbSettings = Setting::all()->pluck('value', 'key')->toArray();
                        $configs = array_merge($configs, $dbSettings);
                    }
                } catch (\Exception $e) {
                    // Ignore during migrations
                }
            }
            $view->with('configs', $configs);

            // Dynamic Theme Injection logic
            if (auth()->check()) {
                static $activeTheme = null;
                if ($activeTheme === null) {
                    $user = auth()->user();
                    $themeSlug = $user->ui_theme ?? 'indigo';
                    
                    // Try to find the theme in database
                    $activeTheme = \App\Models\Theme::where('slug', $themeSlug)
                        ->where('is_active', true)
                        ->first();
                }
                $view->with('activeTheme', $activeTheme);
            }
        });

        // View Composer: inject $typingTests ke halaman exam index siswa
        View::composer('student.exams.index', function ($view) {
            if (auth()->check() && auth()->user()->role === 'student') {
                $student = auth()->user();
                $typingTests = TypingTest::available()
                    ->with(['attempts' => function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    }])
                    ->get()
                    ->filter(fn($t) => $t->isAvailableFor($student));
                $view->with('typingTests', $typingTests);
            } else {
                $view->with('typingTests', collect());
            }
        });
    }
}
