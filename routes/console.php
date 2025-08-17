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

// Schedule warranty expiration notifications to run daily at 9:30 AM
Schedule::command('warranties:check-expiration --dispatch-job')
    ->dailyAt('09:30')
    ->name('warranty-expiration')
    ->description('Check and send warranty expiration notifications');

// Alternative: run immediately without queue (for debugging)
Schedule::command('warranties:check-expiration')
    ->dailyAt('09:35')
    ->name('warranty-expiration-direct')
    ->description('Check and send warranty expiration notifications (direct execution)')
    ->when(config('app.debug', false)); // Only in debug mode

// Schedule contract expiration notifications to run daily at 10:00 AM
Schedule::command('contracts:check-expiration --dispatch-job')
    ->dailyAt('10:00')
    ->name('contract-expiration')
    ->description('Check and send contract expiration and notice period notifications');

// Alternative: run immediately without queue (for debugging)
Schedule::command('contracts:check-expiration')
    ->dailyAt('10:05')
    ->name('contract-expiration-direct')
    ->description('Check and send contract expiration and notice period notifications (direct execution)')
    ->when(config('app.debug', false)); // Only in debug mode

// Schedule utility bill due notifications to run daily at 10:30 AM
Schedule::command('utility-bills:check-due --dispatch-job')
    ->dailyAt('10:30')
    ->name('utility-bill-due')
    ->description('Check and send utility bill payment due notifications');

// Alternative: run immediately without queue (for debugging)
Schedule::command('utility-bills:check-due')
    ->dailyAt('10:35')
    ->name('utility-bill-due-direct')
    ->description('Check and send utility bill payment due notifications (direct execution)')
    ->when(config('app.debug', false)); // Only in debug mode
