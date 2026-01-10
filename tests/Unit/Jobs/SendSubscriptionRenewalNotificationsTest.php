<?php

namespace Tests\Unit\Jobs;

use App\Events\SubscriptionRenewalDue;
use App\Jobs\SendSubscriptionRenewalNotifications;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SendSubscriptionRenewalNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_respects_user_notification_preferences_for_days()
    {
        // Create user with custom notification days
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([14, 7, 1]);
        $preference->save();

        // Create subscription due in 14 days
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(14),
            'status' => 'active',
        ]);

        // Run job in user-centric mode (no days passed)
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert event was dispatched for the 14-day subscription
        Event::assertDispatched(SubscriptionRenewalDue::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id && $event->days === 14;
        });
    }

    /** @test */
    public function it_skips_users_with_disabled_channels()
    {
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Disable all channels
        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->email_enabled = false;
        $preference->database_enabled = false;
        $preference->push_enabled = false;
        $preference->save();

        // Create subscription
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert no events dispatched
        Event::assertNotDispatched(SubscriptionRenewalDue::class);
    }

    /** @test */
    public function it_finds_subscriptions_due_in_specific_days_for_user()
    {
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([7]);
        $preference->save();

        // Create subscriptions due at different times
        $subDueIn7 = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        $subDueIn14 = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(14),
            'status' => 'active',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Should only dispatch for the 7-day subscription
        Event::assertDispatched(SubscriptionRenewalDue::class, function ($event) use ($subDueIn7) {
            return $event->subscription->id === $subDueIn7->id;
        });

        // Should NOT dispatch for the 14-day subscription (not in user's preferences)
        Event::assertNotDispatched(SubscriptionRenewalDue::class, function ($event) use ($subDueIn14) {
            return $event->subscription->id === $subDueIn14->id;
        });
    }

    /** @test */
    public function it_processes_multiple_users_with_different_preferences()
    {
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

        // Create subscriptions for both users, due in 7 days
        $subA = Subscription::factory()->create([
            'user_id' => $userA->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        $subB = Subscription::factory()->create([
            'user_id' => $userB->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // User A should get notification (7 is in their list)
        Event::assertDispatched(SubscriptionRenewalDue::class, function ($event) use ($subA) {
            return $event->subscription->id === $subA->id;
        });

        // User B should NOT get notification (7 is not in their list)
        Event::assertNotDispatched(SubscriptionRenewalDue::class, function ($event) use ($subB) {
            return $event->subscription->id === $subB->id;
        });
    }

    /** @test */
    public function it_finds_subscriptions_due_today()
    {
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([0]);
        $preference->save();

        // Create subscription due today
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now(),
            'status' => 'active',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert event dispatched with days = 0
        Event::assertDispatched(SubscriptionRenewalDue::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id && $event->days === 0;
        });
    }

    /** @test */
    public function it_finds_overdue_subscriptions()
    {
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([0]);
        $preference->save();

        // Create subscription that was due 3 days ago
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->subDays(3),
            'status' => 'active',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert event dispatched for overdue subscription
        Event::assertDispatched(SubscriptionRenewalDue::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id;
        });
    }

    /** @test */
    public function it_ignores_cancelled_subscriptions()
    {
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Create cancelled subscription
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'cancelled',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert no events dispatched
        Event::assertNotDispatched(SubscriptionRenewalDue::class);
    }

    /** @test */
    public function it_ignores_paused_subscriptions()
    {
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Create paused subscription
        Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'paused',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert no events dispatched
        Event::assertNotDispatched(SubscriptionRenewalDue::class);
    }

    /** @test */
    public function it_supports_legacy_mode_with_specific_days()
    {
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Set user preference to different days
        $preference = $user->getNotificationPreference('subscription_renewal');
        $preference->setNotificationDays([30, 14]);
        $preference->save();

        // Create subscription due in 7 days
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run job in LEGACY mode with specific days (ignores user preferences)
        $job = new SendSubscriptionRenewalNotifications([7, 3, 1, 0]);
        $job->handle();

        // Assert event dispatched even though 7 is not in user's preferences
        Event::assertDispatched(SubscriptionRenewalDue::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id && $event->days === 7;
        });
    }

    /** @test */
    public function it_uses_default_preferences_if_user_has_none()
    {
        $user = User::factory()->create();
        // Don't create preferences - should use defaults

        // Create subscription due in 7 days
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'next_billing_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        // Run job
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert event dispatched (should use default days: [7, 3, 1, 0])
        Event::assertDispatched(SubscriptionRenewalDue::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id && $event->days === 7;
        });
    }

    /** @test */
    public function it_handles_users_with_no_subscriptions()
    {
        // Create user with no subscriptions
        $user = User::factory()->create();
        $user->createDefaultNotificationPreferences();

        // Run job - should not crash
        $job = new SendSubscriptionRenewalNotifications();
        $job->handle();

        // Assert no events dispatched
        Event::assertNotDispatched(SubscriptionRenewalDue::class);

        // Test passes if no exception thrown
        $this->assertTrue(true);
    }
}
