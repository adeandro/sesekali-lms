<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Services\LeaderboardService;
use Illuminate\Support\Facades\Schedule;

// Snapshot mingguan tiap Senin 00:01
Schedule::call(function () {
    app(LeaderboardService::class)->snapshotPeriodic(
        'weekly', 
        now()->subWeek()->startOfWeek(), 
        now()->subWeek()->endOfWeek()
    );
})->weekly()->mondays()->at('00:01');

// Snapshot bulanan tiap tanggal 1 00:01
Schedule::call(function () {
    app(LeaderboardService::class)->snapshotPeriodic(
        'monthly', 
        now()->subMonth()->startOfMonth(), 
        now()->subMonth()->endOfMonth()
    );
})->monthlyOn(1, '00:01');
