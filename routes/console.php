<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription renewal notifications to run daily at 9 AM
Schedule::command('subscriptions:check-renewals --dispatch-job')
    ->dailyAt('09:00')
    ->name('subscription-renewals')
    ->description('Check and send subscription renewal notifications');

// Alternative: run immediately without queue (for debugging)
Schedule::command('subscriptions:check-renewals')
    ->dailyAt('09:15')
    ->name('subscription-renewals-direct')
    ->description('Check and send subscription renewal notifications (direct execution)')
    ->when(config('app.debug', false)); // Only in debug mode
