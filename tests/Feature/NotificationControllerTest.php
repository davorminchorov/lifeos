<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SubscriptionRenewalAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_view_notifications_index()
    {
        $this->actingAs($this->user)
            ->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertViewIs('notifications.index')
            ->assertViewHas(['notifications', 'unreadCount']);
    }

    public function test_can_get_notifications_data_via_ajax()
    {
        // Create a test notification
        $this->user->notify(new SubscriptionRenewalAlert(
            \App\Models\Subscription::factory()->create(['user_id' => $this->user->id]),
            7
        ));

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.data'))
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
        $this->user->notify(new SubscriptionRenewalAlert(
            \App\Models\Subscription::factory()->create(['user_id' => $this->user->id]),
            7
        ));

        $notification = $this->user->unreadNotifications->first();

        $this->actingAs($this->user)
            ->postJson(route('notifications.mark-as-read', $notification->id))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_can_mark_all_notifications_as_read()
    {
        $subscription = \App\Models\Subscription::factory()->create(['user_id' => $this->user->id]);

        // Create multiple test notifications
        $this->user->notify(new SubscriptionRenewalAlert($subscription, 7));
        $this->user->notify(new SubscriptionRenewalAlert($subscription, 3));

        $this->assertEquals(2, $this->user->unreadNotifications->count());

        $this->actingAs($this->user)
            ->postJson(route('notifications.mark-all-as-read'))
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
        $this->user->notify(new SubscriptionRenewalAlert(
            \App\Models\Subscription::factory()->create(['user_id' => $this->user->id]),
            7
        ));

        $notification = $this->user->notifications->first();

        $this->actingAs($this->user)
            ->deleteJson(route('notifications.destroy', $notification->id))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $this->user->notifications->count());
    }

    public function test_can_view_notification_preferences()
    {
        $this->actingAs($this->user)
            ->get(route('notifications.preferences'))
            ->assertStatus(200)
            ->assertViewIs('notifications.preferences')
            ->assertViewHas('preferences');
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

        $this->actingAs($this->user)
            ->postJson(route('notifications.preferences.update'), ['preferences' => $preferences])
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
        $subscription = \App\Models\Subscription::factory()->create(['user_id' => $this->user->id]);

        // Create some notifications
        $this->user->notify(new SubscriptionRenewalAlert($subscription, 7));
        $this->user->notify(new SubscriptionRenewalAlert($subscription, 3));

        // Mark one as read
        $this->user->notifications->first()->markAsRead();

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.stats'))
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
        $otherUser = User::factory()->create();
        $otherUser->notify(new SubscriptionRenewalAlert(
            \App\Models\Subscription::factory()->create(['user_id' => $otherUser->id]),
            7
        ));

        $otherNotification = $otherUser->notifications->first();

        // Try to mark other user's notification as read
        $this->actingAs($this->user)
            ->postJson(route('notifications.mark-as-read', $otherNotification->id))
            ->assertStatus(404);

        // Try to delete other user's notification
        $this->actingAs($this->user)
            ->deleteJson(route('notifications.destroy', $otherNotification->id))
            ->assertStatus(404);
    }

    public function test_guest_cannot_access_notifications()
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));

        $this->get(route('notifications.preferences'))
            ->assertRedirect(route('login'));

        $this->getJson(route('notifications.data'))
            ->assertStatus(401);
    }
}
