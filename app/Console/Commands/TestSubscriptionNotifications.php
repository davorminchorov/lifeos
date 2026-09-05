<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionRenewalAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestSubscriptionNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:test-notifications {user_id? : The ID of the user to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test subscription renewal notifications for debugging';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $user = $userId ? User::find($userId) : User::first();

        if (! $user) {
            $this->error('❌ No user found');

            return Command::FAILURE;
        }

        $this->info("🧪 Testing notifications for: {$user->email} (ID: {$user->id})");
        $this->newLine();

        // Check notification preferences
        $this->info('📋 Notification Preferences:');
        $preference = $user->getNotificationPreference('subscription_renewal');

        if (! $preference) {
            $this->warn('⚠️  No notification preference found. Using defaults.');
            $this->info('💡 Creating default preferences...');
            $user->createDefaultNotificationPreferences();
            $preference = $user->getNotificationPreference('subscription_renewal');
        }

        $channels = $user->getEnabledNotificationChannels('subscription_renewal');
        $days = $user->getNotificationDays('subscription_renewal');

        $this->table(
            ['Setting', 'Value'],
            [
                ['Enabled Channels', empty($channels) ? 'None (all disabled!)' : implode(', ', $channels)],
                ['Notification Days', implode(', ', $days).' days before'],
                ['Email Enabled', $preference->email_enabled ? '✓' : '✗'],
                ['Database Enabled', $preference->database_enabled ? '✓' : '✗'],
                ['Push Enabled', $preference->push_enabled ? '✓' : '✗'],
            ]
        );
        $this->newLine();

        // Check subscriptions
        $this->info('📊 Active Subscriptions:');
        $subscriptions = $user->subscriptions()->where('status', 'active')->get();

        if ($subscriptions->isEmpty()) {
            $this->warn('⚠️  No active subscriptions found');
            $this->newLine();

            if ($this->confirm('Would you like to create a test subscription?')) {
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'service_name' => 'Test Subscription',
                    'description' => 'Created for notification testing',
                    'cost' => 9.99,
                    'currency' => 'USD',
                    'billing_cycle' => 'monthly',
                    'next_billing_date' => now()->addDays(7),
                    'start_date' => now(),
                    'status' => 'active',
                    'auto_renewal' => true,
                ]);
                $this->info("✓ Created test subscription (ID: {$subscription->id})");
                $subscriptions = collect([$subscription]);
            } else {
                return Command::SUCCESS;
            }
        }

        $subscriptionData = $subscriptions->map(function ($subscription) {
            $daysUntil = now()->diffInDays($subscription->next_billing_date, false);
            $status = match (true) {
                $daysUntil < 0 => "⚠️ OVERDUE ({$daysUntil} days)",
                $daysUntil === 0 => '🔔 DUE TODAY',
                $daysUntil <= 3 => "🟡 {$daysUntil} days",
                $daysUntil <= 7 => "🟢 {$daysUntil} days",
                default => "🔵 {$daysUntil} days"
            };

            return [
                $subscription->id,
                $subscription->service_name,
                "\${$subscription->cost} {$subscription->currency}",
                $subscription->next_billing_date->format('Y-m-d'),
                $status,
            ];
        });

        $this->table(
            ['ID', 'Service', 'Cost', 'Next Billing', 'Status'],
            $subscriptionData
        );
        $this->newLine();

        // Check which subscriptions match notification days
        $this->info('🎯 Subscriptions Matching Notification Days:');
        $matchingFound = false;

        foreach ($days as $day) {
            $targetDate = now()->addDays($day);
            $matching = $subscriptions->filter(function ($sub) use ($day, $targetDate) {
                if ($day === 0) {
                    return $sub->next_billing_date->lte(now());
                }

                return $sub->next_billing_date->isSameDay($targetDate);
            });

            if ($matching->count() > 0) {
                $matchingFound = true;
                $this->line("  ✓ {$day} days: {$matching->count()} subscription(s)");
                foreach ($matching as $sub) {
                    $this->line("    - {$sub->service_name}");
                }
            }
        }

        if (! $matchingFound) {
            $this->warn('  ⚠️  No subscriptions match the notification days');
        }

        $this->newLine();

        // Send test notification
        if (empty($channels)) {
            $this->error('❌ Cannot send test notification: All channels are disabled');
            $this->info('💡 Enable at least one channel in notification preferences');

            return Command::SUCCESS;
        }

        if ($this->confirm('Would you like to send a test notification?')) {
            $testSub = $subscriptions->first();
            if ($testSub) {
                $this->info('📤 Sending test notification...');

                try {
                    $user->notify(new SubscriptionRenewalAlert($testSub, 7));
                    $this->info('✅ Test notification sent successfully!');
                    $this->newLine();
                    $this->info('📧 Check:');
                    $this->line('  - Mailpit: http://localhost:45302');
                    $this->line('  - Database: SELECT * FROM notifications WHERE notifiable_id = '.$user->id.' ORDER BY created_at DESC LIMIT 5;');
                    $this->line('  - Logs: tail -f storage/logs/laravel.log');
                } catch (\Exception $e) {
                    $this->error("❌ Failed to send notification: {$e->getMessage()}");
                    Log::error('Test notification failed: '.$e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }
}
