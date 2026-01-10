<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSubscriptionNotificationsSeeder extends Seeder
{
    /**
     * Seed test subscriptions with upcoming billing dates for notification testing.
     */
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command->error('No users found. Please create a user first.');

            return;
        }

        $this->command->info("Creating test subscriptions for user: {$user->email}");

        // Ensure user has notification preferences
        if (! $user->getNotificationPreference('subscription_renewal')) {
            $user->createDefaultNotificationPreferences();
            $this->command->info('Created default notification preferences for user');
        }

        // Create subscriptions due at different intervals to test all notification days
        $intervals = [
            0 => 'Due Today',
            1 => 'Due Tomorrow',
            3 => 'Due in 3 Days',
            7 => 'Due in 1 Week',
            14 => 'Due in 2 Weeks',
            30 => 'Due in 1 Month',
        ];

        foreach ($intervals as $days => $label) {
            Subscription::create([
                'user_id' => $user->id,
                'service_name' => "Test Service - {$label}",
                'description' => "Test subscription for notification testing (due in {$days} days)",
                'cost' => rand(10, 100),
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'next_billing_date' => now()->addDays($days),
                'start_date' => now()->subMonths(6),
                'status' => 'active',
                'auto_renewal' => true,
            ]);

            $this->command->line("  ✓ Created: {$label}");
        }

        $this->command->info('✅ Successfully created 6 test subscriptions with staggered billing dates');
        $this->command->line('');
        $this->command->info('Next steps:');
        $this->command->line('  1. Run: php artisan subscriptions:check-renewals');
        $this->command->line('  2. Check notifications: SELECT * FROM notifications ORDER BY created_at DESC;');
        $this->command->line('  3. Check Mailpit: http://localhost:8025');
    }
}
