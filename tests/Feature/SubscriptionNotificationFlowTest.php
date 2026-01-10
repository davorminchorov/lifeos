<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionRenewalAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionNotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function full_notification_flow_works_end_to_end()
    {
        Notification::fake();

        // Create user with subscription
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run the command (without dispatching to queue)
        $exitCode = Artisan::call('subscriptions:check-renewals');

        // Assert command succeeded
        $this->assertEquals(0, $exitCode);

        // Assert notification was sent
        Notification::assertSentTo(
            $user,
            SubscriptionRenewalAlert::class,
            function ($notification) use ($subscription) {
                return $notification->subscription->id === $subscription->id;
            }
        );
    }

    /** @test */
    public function notifications_respect_multiple_users_preferences()
    {
        Notification::fake();

        // User A: Wants notifications at 14, 7 days
        $userA = User::factory()->create();
        $userA->createDefaultNotificationPreferences();
        $prefA = $userA->getNotificationPreference('subscription_renewal');
        $prefA->setNotificationDays([14, 7]);
        $prefA->save();

        // User B: Only wants notifications at 1 day
        $userB = User::factory()->create();
        $userB->createDefaultNotificationPreferences();
        $prefB = $userB->getNotificationPreference('subscription_renewal');
        $prefB->setNotificationDays([1]);
        $prefB->save();

        // Subscriptions for both users, due in 7 days
        Subscription::factory()->create([
            'user_id' => $userA->id,
            'service_name' => 'User A Subscription',
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        Subscription::factory()->create([
            'user_id' => $userB->id,
            'service_name' => 'User B Subscription',
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run command
        Artisan::call('subscriptions:check-renewals');

        // User A should get notification (7 is in their list)
        Notification::assertSentTo($userA, SubscriptionRenewalAlert::class);

        // User B should NOT get notification (7 is not in their list)
        Notification::assertNotSentTo($userB, SubscriptionRenewalAlert::class);
    }

    /** @test */
    public function users_with_disabled_channels_dont_receive_notifications()
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Disable all channels
        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->email_enabled = false;
        $preference->database_enabled = false;
        $preference->push_enabled = false;
        $preference->save();

        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run command
        Artisan::call('subscriptions:check-renewals');

        // Assert no notification sent
        Notification::assertNothingSent();
    }

    /** @test */
    public function command_with_specific_days_uses_legacy_mode()
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Set user preference to 14 days only
        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([14]);
        $preference->save();

        // Create subscription due in 7 days
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run command with --days=7 (legacy mode)
        Artisan::call('subscriptions:check-renewals', ['--days' => [7]]);

        // Assert notification WAS sent (legacy mode ignores user preferences)
        Notification::assertSentTo($user, SubscriptionRenewalAlert::class);
    }

    /** @test */
    public function multiple_subscriptions_for_same_user_all_get_notifications()
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Create 3 subscriptions all due in 7 days
        $sub1 = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Netflix',
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        $sub2 = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Spotify',
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        $sub3 = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Disney+',
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run command
        Artisan::call('subscriptions:check-renewals');

        // Assert 3 notifications sent to user
        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, 3);
    }

    /** @test */
    public function notifications_sent_for_different_notification_days()
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Set preference for multiple days
        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([7, 3, 1, 0]);
        $preference->save();

        // Create subscriptions for each notification day
        $sub7days = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Due in 7 days',
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        $sub3days = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Due in 3 days',
            'next_billing_date' => now()->addDays(3),
            'status' => 'active',
        ]);

        $sub1day = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Due tomorrow',
            'next_billing_date' => now()->addDays(1),
            'status' => 'active',
        ]);

        $subToday = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Due today',
            'next_billing_date' => now(),
            'status' => 'active',
        ]);

        // Run command
        Artisan::call('subscriptions:check-renewals');

        // Assert all 4 notifications sent
        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, 4);

        // Verify correct days in notifications
        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, function ($notification, $channels, $notifiable) use ($sub7days) {
            return $notification->subscription->id === $sub7days->id && $notification->days === 7;
        });

        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, function ($notification, $channels, $notifiable) use ($sub3days) {
            return $notification->subscription->id === $sub3days->id && $notification->days === 3;
        });

        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, function ($notification, $channels, $notifiable) use ($sub1day) {
            return $notification->subscription->id === $sub1day->id && $notification->days === 1;
        });

        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, function ($notification, $channels, $notifiable) use ($subToday) {
            return $notification->subscription->id === $subToday->id && $notification->days === 0;
        });
    }

    /** @test */
    public function cancelled_and_paused_subscriptions_are_ignored()
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Active subscription - should notify
        Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Active',
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Cancelled subscription - should NOT notify
        Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Cancelled',
            'next_billing_date' => now()->addDays(7),
            'status' => 'cancelled',
        ]);

        // Paused subscription - should NOT notify
        Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Paused',
            'next_billing_date' => now()->addDays(7),
            'status' => 'paused',
        ]);

        // Run command
        Artisan::call('subscriptions:check-renewals');

        // Assert only 1 notification sent (for active subscription)
        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, 1);
    }

    /** @test */
    public function overdue_subscriptions_are_notified_on_day_zero()
    {
        Notification::fake();

        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([0]);
        $preference->save();

        // Create subscription that was due 5 days ago
        $overdueSubscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'service_name' => 'Overdue Subscription',
            'next_billing_date' => now()->subDays(5),
            'status' => 'active',
        ]);

        // Run command
        Artisan::call('subscriptions:check-renewals');

        // Assert notification sent for overdue subscription
        Notification::assertSentTo($user, SubscriptionRenewalAlert::class, function ($notification) use ($overdueSubscription) {
            return $notification->subscription->id === $overdueSubscription->id && $notification->days === 0;
        });
    }

    /** @test */
    public function new_users_get_default_notification_preferences()
    {
        Notification::fake();

        // Create new user (UserObserver should auto-create preferences)
        $user = User::factory()->create();

        // Verify preferences were created
        $this->assertNotNull($user->getNotificationPreference('subscription_renewal'));

        // Create subscription due in 7 days (default includes 7)
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run command
        Artisan::call('subscriptions:check-renewals');

        // Assert notification sent using defaults
        Notification::assertSentTo($user, SubscriptionRenewalAlert::class);
    }
}
