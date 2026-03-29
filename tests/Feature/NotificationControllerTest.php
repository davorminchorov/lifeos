<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\SubscriptionRenewalAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        Queue::fake();
    }

    /**
     * Send a notification synchronously (bypassing ShouldQueue) so that
     * the database record is created immediately for test assertions.
     */
    private function sendNotificationSync(User $user, SubscriptionRenewalAlert $notification): void
    {
        $user->notifyNow($notification);
    }

    public function test_can_view_notifications_index()
    {
        $this->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Index')
                ->has('notifications')
                ->has('unreadCount')
            );
    }

    public function test_can_get_notifications_data_via_ajax()
    {
        // Create a test notification
        $this->sendNotificationSync($this->user, new SubscriptionRenewalAlert(
            Subscription::factory()->create([
                'user_id' => $this->user->id,
                'tenant_id' => $this->tenant->id,
            ]),
            7
        ));

        $response = $this->getJson(route('notifications.data'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'notifications' => [
                    '*' => [
                        'id',
                        'title',
                        'message',
                        'type',
                        'action_url',
                        'read_at',
                        'created_at',
                    ],
                ],
                'unread_count',
            ]);
    }

    public function test_can_mark_notification_as_read()
    {
        // Create a test notification
        $this->sendNotificationSync($this->user, new SubscriptionRenewalAlert(
            Subscription::factory()->create([
                'user_id' => $this->user->id,
                'tenant_id' => $this->tenant->id,
            ]),
            7
        ));

        $notification = $this->user->unreadNotifications->first();

        $this->postJson(route('notifications.mark-as-read', $notification->id))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_can_mark_all_notifications_as_read()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        // Create multiple test notifications
        $this->sendNotificationSync($this->user, new SubscriptionRenewalAlert($subscription, 7));
        $this->sendNotificationSync($this->user, new SubscriptionRenewalAlert($subscription, 3));

        $this->assertEquals(2, $this->user->unreadNotifications->count());

        $this->postJson(route('notifications.mark-all-as-read'))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertEquals(0, $this->user->unreadNotifications->count());
    }

    public function test_can_delete_notification()
    {
        // Create a test notification
        $this->sendNotificationSync($this->user, new SubscriptionRenewalAlert(
            Subscription::factory()->create([
                'user_id' => $this->user->id,
                'tenant_id' => $this->tenant->id,
            ]),
            7
        ));

        $notification = $this->user->notifications->first();

        $this->deleteJson(route('notifications.destroy', $notification->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $this->user->notifications->count());
    }

    public function test_can_view_notification_preferences()
    {
        $this->get(route('notifications.preferences'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Preferences')
                ->has('preferences')
            );
    }

    public function test_can_update_notification_preferences()
    {
        $preferences = [
            'subscription_renewal' => [
                'email_enabled' => true,
                'database_enabled' => false,
                'push_enabled' => true,
                'days_before' => [7, 3, 1],
            ],
            'contract_expiration' => [
                'email_enabled' => false,
                'database_enabled' => true,
                'push_enabled' => false,
                'days_before' => [30, 7],
            ],
        ];

        $this->postJson(route('notifications.preferences.update'), ['preferences' => $preferences])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification preferences updated successfully.',
            ]);

        // Check that preferences were saved
        $subscriptionPref = $this->user->notificationPreferences()
            ->where('notification_type', 'subscription_renewal')
            ->first();

        $this->assertTrue($subscriptionPref->email_enabled);
        $this->assertFalse($subscriptionPref->database_enabled);
        $this->assertTrue($subscriptionPref->push_enabled);
        $this->assertEquals([7, 3, 1], $subscriptionPref->getNotificationDays());

        $contractPref = $this->user->notificationPreferences()
            ->where('notification_type', 'contract_expiration')
            ->first();

        $this->assertFalse($contractPref->email_enabled);
        $this->assertTrue($contractPref->database_enabled);
        $this->assertFalse($contractPref->push_enabled);
        $this->assertEquals([30, 7], $contractPref->getNotificationDays());
    }

    public function test_can_get_notification_stats()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        // Create some notifications
        $this->sendNotificationSync($this->user, new SubscriptionRenewalAlert($subscription, 7));
        $this->sendNotificationSync($this->user, new SubscriptionRenewalAlert($subscription, 3));

        // Mark one as read
        $this->user->notifications->first()->markAsRead();

        $response = $this->getJson(route('notifications.stats'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'total',
                'unread',
                'read',
                'by_type',
            ]);

        $stats = $response->json();
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['unread']);
        $this->assertEquals(1, $stats['read']);
    }

    public function test_cannot_access_other_users_notifications()
    {
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['current_tenant_id' => $otherTenant->id]);
        $otherTenant->update(['owner_id' => $otherUser->id]);
        $otherUser->notifyNow(new SubscriptionRenewalAlert(
            Subscription::factory()->create([
                'user_id' => $otherUser->id,
                'tenant_id' => $otherTenant->id,
            ]),
            7
        ));

        $otherNotification = $otherUser->notifications->first();

        // Try to mark other user's notification as read
        $this->postJson(route('notifications.mark-as-read', $otherNotification->id))
            ->assertStatus(404);

        // Try to delete other user's notification
        $this->deleteJson(route('notifications.destroy', $otherNotification->id))
            ->assertStatus(404);
    }

    public function test_guest_cannot_access_notifications()
    {
        $this->app['auth']->forgetGuards();

        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));

        $this->get(route('notifications.preferences'))
            ->assertRedirect(route('login'));

        $this->getJson(route('notifications.data'))
            ->assertStatus(401);
    }
}
