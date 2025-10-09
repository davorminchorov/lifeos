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

// Schedule warranty expiration notifications to run daily at 9:30 AM
Schedule::command('warranties:check-expiration --dispatch-job')
    ->dailyAt('09:30')
    ->name('warranty-expiration')
    ->description('Check and send warranty expiration notifications');

// Schedule contract expiration notifications to run daily at 10:00 AM
Schedule::command('contracts:check-expiration --dispatch-job')
    ->dailyAt('10:00')
    ->name('contract-expiration')
    ->description('Check and send contract expiration and notice period notifications');

// Schedule utility bill due notifications to run daily at 10:30 AM
Schedule::command('utility-bills:check-due --dispatch-job')
    ->dailyAt('10:30')
    ->name('utility-bill-due')
    ->description('Check and send utility bill payment due notifications');

// Create expenses for auto-renewed subscriptions before advancing billing dates
Schedule::command('subscriptions:create-expenses --dispatch-job')
    ->dailyAt('00:05')
    ->name('subscription-create-expenses')
    ->description('Create expenses for subscriptions with auto-renewal due today');

// Schedule updating of subscription next billing dates shortly after midnight
Schedule::command('subscriptions:update-next-billing --dispatch-job')
    ->dailyAt('00:10')
    ->name('subscription-update-next-billing')
    ->description('Advance subscription next_billing_date for due or overdue subscriptions');

// Alternative direct execution in debug
Schedule::command('subscriptions:update-next-billing')
    ->dailyAt('00:15')
    ->name('subscription-update-next-billing-direct')
    ->description('Advance subscription next_billing_date (direct execution)')
    ->when(config('app.debug', false)); // Only in debug mode
