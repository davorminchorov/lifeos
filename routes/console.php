<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule job application reminders to run daily at 8:00 AM
Schedule::command('job-applications:check-reminders')
    ->dailyAt('08:00')
    ->name('job-application-reminders')
    ->description('Check and send job application reminders (interviews, offers, actions, stale)');

// Schedule subscription renewal notifications to run daily at 9 AM
Schedule::command('subscriptions:check-renewals --dispatch-job')
    ->dailyAt('09:00')
    ->name('subscription-renewals')
    ->description('Check and send subscription renewal notifications');

// Schedule Cycle Menu daily notification at 9:00 AM
Schedule::command('cycle-menus:notify-today --dispatch-job')
    ->dailyAt('09:00')
    ->name('cycle-menu-daily-notify')
    ->description("Send today's Cycle Menu notification");

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

// Schedule Gmail receipt sync to run every hour
Schedule::command('gmail:sync-receipts --all --queue')
    ->hourly()
    ->name('gmail-sync-receipts')
    ->description('Sync receipts from Gmail for all active connections')
    ->when(config('gmail_receipts.sync.auto_sync_enabled', true)); // Only when auto-sync is enabled

// Email-ingestion agent: every 30 minutes, behind a feature flag.
Schedule::command('agents:run email-ingestion')
    ->cron('*/30 * * * *')
    ->name('agent-email-ingestion')
    ->description('Run the email-ingestion agent across all eligible tenants')
    ->withoutOverlapping(15)
    ->when(fn (): bool => (bool) config('agents.flags.agents.email_ingestion.enabled', false));

// Investments-sync agent: daily at 07:00.
Schedule::command('agents:run investments-sync')
    ->cron('0 7 * * *')
    ->name('agent-investments-sync')
    ->description('Sync investment positions, dividends, and prices from broker confirms + Drive statements')
    ->withoutOverlapping(30)
    ->when(fn (): bool => (bool) config('agents.flags.agents.investments_sync.enabled', false));

// Bank-statements agent: daily at 08:00.
Schedule::command('agents:run bank-statements')
    ->cron('0 8 * * *')
    ->name('agent-bank-statements')
    ->description('Ingest new bank/card statements from Drive and reconcile against existing expenses')
    ->withoutOverlapping(30)
    ->when(fn (): bool => (bool) config('agents.flags.agents.bank_statements.enabled', false));

// Receipts-OCR agent: every 6 hours.
Schedule::command('agents:run receipts-ocr')
    ->cron('0 */6 * * *')
    ->name('agent-receipts-ocr')
    ->description('Watch the connected Drive folder for new receipt scans and propose expenses / warranties / utility bills')
    ->withoutOverlapping(20)
    ->when(fn (): bool => (bool) config('agents.flags.agents.receipts_ocr.enabled', false));

// Job-search hunter agent: Mon/Wed/Fri at 09:00. Pre-checks gate further on
// the per-skill active-search window, so the cron expression is just the
// upper bound of when this agent CAN run.
Schedule::command('agents:run job-search')
    ->cron('0 9 * * 1,3,5')
    ->name('agent-job-search')
    ->description('Discover job postings matching the user\'s CV and criteria during active search windows')
    ->withoutOverlapping(30)
    ->when(fn (): bool => (bool) config('agents.flags.agents.job_search.enabled', false));

// Cycle-menu planner: Sunday at 09:00.
Schedule::command('agents:run cycle-menu-planner')
    ->cron('0 9 * * 0')
    ->name('agent-cycle-menu-planner')
    ->description('Fill empty days of the active cycle menu and produce a shopping list for the upcoming week')
    ->withoutOverlapping(15)
    ->when(fn (): bool => (bool) config('agents.flags.agents.cycle_menu_planner.enabled', false));

// Weekly digest: Sunday at 21:00.
Schedule::command('agents:run weekly-digest')
    ->cron('0 21 * * 0')
    ->name('agent-weekly-digest')
    ->description('Compose and email a one-page summary of the user\'s LifeOS state for the week')
    ->withoutOverlapping(15)
    ->when(fn (): bool => (bool) config('agents.flags.agents.weekly_digest.enabled', false));
