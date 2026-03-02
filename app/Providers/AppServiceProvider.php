<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use App\Models\User;
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
        Gate::policy(User::class, StudentPolicy::class);

        $configs = [
            'school_name' => 'ExamFlow',
            'max_violations' => 3,
            'anti_cheat_active' => 1,
            'logo' => null,
            'academic_year' => '2023/2024'
        ];

        try {
            if (Schema::hasTable('settings')) {
                $dbSettings = Setting::all()->pluck('value', 'key')->toArray();
                $configs = array_merge($configs, $dbSettings);
            }
        } catch (\Exception $e) {
            // Error during migration or DB connection
        }

        View::share('configs', $configs);
    }
}
