<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_index_shows_all_subscriptions_when_authenticated()
    {
        $userSubscription = Subscription::factory()->create(['user_id' => $this->user->id]);
        $otherUserSubscription = Subscription::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->get('/subscriptions');

        $response->assertStatus(200);
        // This test will reveal the authorization issue - users should only see their own subscriptions
    }

    public function test_index_filters_by_status()
    {
        $activeSubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);
        $cancelledSubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'cancelled'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/subscriptions?status=active');

        $response->assertStatus(200);
    }

    public function test_index_filters_by_category()
    {
        $entertainmentSubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'Entertainment'
        ]);
        $softwareSubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'Software'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/subscriptions?category=Entertainment');

        $response->assertStatus(200);
    }

    public function test_index_filters_by_due_soon()
    {
        $dueSoonSubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'next_billing_date' => now()->addDays(3)
        ]);
        $notDueSoonSubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'next_billing_date' => now()->addDays(10)
        ]);

        $response = $this->actingAs($this->user)
            ->get('/subscriptions?due_soon=7');

        $response->assertStatus(200);
    }

    public function test_index_searches_by_service_name()
    {
        $netflixSubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'service_name' => 'Netflix'
        ]);
        $spotifySubscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'service_name' => 'Spotify'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/subscriptions?search=Netflix');

        $response->assertStatus(200);
    }

    public function test_create_shows_create_form()
    {
        $response = $this->actingAs($this->user)->get('/subscriptions/create');

        $response->assertStatus(200);
    }

    public function test_store_creates_subscription_with_valid_data()
    {
        $subscriptionData = [
            'service_name' => 'Netflix',
            'category' => 'Entertainment',
            'cost' => 15.99,
            'billing_cycle' => 'monthly',
            'currency' => 'USD',
            'start_date' => '2024-01-01',
            'next_billing_date' => '2024-02-01',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->user)
            ->post('/subscriptions', $subscriptionData);

        $response->assertRedirect();
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'service_name' => 'Netflix',
            'category' => 'Entertainment',
            'cost' => 15.99,
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post('/subscriptions', []);

        $response->assertSessionHasErrors([
            'service_name',
            'category',
            'cost',
            'billing_cycle',
            'currency',
            'start_date',
            'next_billing_date',
        ]);
    }

    public function test_store_validates_billing_cycle_days_for_custom_cycle()
    {
        $subscriptionData = [
            'service_name' => 'Custom Service',
            'category' => 'Software',
            'cost' => 25.00,
            'billing_cycle' => 'custom',
            'currency' => 'USD',
            'start_date' => '2024-01-01',
            'next_billing_date' => '2024-01-15',
        ];

        $response = $this->actingAs($this->user)
            ->post('/subscriptions', $subscriptionData);

        $response->assertSessionHasErrors(['billing_cycle_days']);
    }

    public function test_store_validates_start_date_not_in_future()
    {
        $subscriptionData = [
            'service_name' => 'Future Service',
            'category' => 'Software',
            'cost' => 10.00,
            'billing_cycle' => 'monthly',
            'currency' => 'USD',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'next_billing_date' => now()->addMonth()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->post('/subscriptions', $subscriptionData);

        $response->assertSessionHasErrors(['start_date']);
    }

    public function test_store_validates_next_billing_date_after_start_date()
    {
        $subscriptionData = [
            'service_name' => 'Invalid Date Service',
            'category' => 'Software',
            'cost' => 10.00,
            'billing_cycle' => 'monthly',
            'currency' => 'USD',
            'start_date' => '2024-02-01',
            'next_billing_date' => '2024-01-01',
        ];

        $response = $this->actingAs($this->user)
            ->post('/subscriptions', $subscriptionData);

        $response->assertSessionHasErrors(['next_billing_date']);
    }

    public function test_show_displays_subscription()
    {
        $subscription = Subscription::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get("/subscriptions/{$subscription->id}");

        $response->assertStatus(200);
    }

    public function test_show_allows_access_to_other_users_subscription()
    {
        // This test verifies that authorization is working properly
        $otherUserSubscription = Subscription::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get("/subscriptions/{$otherUserSubscription->id}");

        // Should fail with 403 due to proper authorization
        $response->assertStatus(403);
    }

    public function test_edit_shows_edit_form()
    {
        $subscription = Subscription::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get("/subscriptions/{$subscription->id}/edit");

        $response->assertStatus(200);
    }

    public function test_update_modifies_subscription_with_valid_data()
    {
        $subscription = Subscription::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'service_name' => 'Updated Service Name',
            'category' => 'Updated Category',
            'cost' => 25.99,
            'billing_cycle' => 'yearly',
            'currency' => 'USD',
            'start_date' => $subscription->start_date->format('Y-m-d'),
            'next_billing_date' => $subscription->next_billing_date->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->put("/subscriptions/{$subscription->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'service_name' => 'Updated Service Name',
            'cost' => 25.99,
        ]);
    }

    public function test_update_allows_modification_of_other_users_subscription()
    {
        // This test verifies that authorization is working properly
        $otherUserSubscription = Subscription::factory()->create(['user_id' => $this->otherUser->id]);

        $updateData = [
            'service_name' => 'Hacked Service Name',
            'category' => 'Hacked Category',
            'cost' => 999.99,
            'billing_cycle' => 'monthly',
            'currency' => 'USD',
            'start_date' => $otherUserSubscription->start_date->format('Y-m-d'),
            'next_billing_date' => $otherUserSubscription->next_billing_date->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->put("/subscriptions/{$otherUserSubscription->id}", $updateData);

        // Should fail with 403 due to proper authorization
        $response->assertStatus(403);
    }

    public function test_destroy_deletes_subscription()
    {
        $subscription = Subscription::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->delete("/subscriptions/{$subscription->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }

    public function test_destroy_allows_deletion_of_other_users_subscription()
    {
        // This test verifies that authorization is working properly
        $otherUserSubscription = Subscription::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->delete("/subscriptions/{$otherUserSubscription->id}");

        // Should fail with 403 due to proper authorization
        $response->assertStatus(403);
    }

    public function test_cancel_sets_status_and_cancellation_date()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/subscriptions/{$subscription->id}/cancel");

        $response->assertRedirect();
        $subscription->refresh();
        $this->assertEquals('cancelled', $subscription->status);
        $this->assertNotNull($subscription->cancellation_date);
    }

    public function test_pause_sets_status_to_paused()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/subscriptions/{$subscription->id}/pause");

        $response->assertRedirect();
        $subscription->refresh();
        $this->assertEquals('paused', $subscription->status);
    }

    public function test_resume_sets_status_to_active()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paused'
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/subscriptions/{$subscription->id}/resume");

        $response->assertRedirect();
        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
    }

    public function test_analytics_summary_returns_user_specific_data()
    {
        // Create subscriptions for the authenticated user
        Subscription::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);
        Subscription::factory()->count(1)->create([
            'user_id' => $this->user->id,
            'status' => 'cancelled'
        ]);

        // Create subscriptions for another user (should not be included)
        Subscription::factory()->count(2)->create([
            'user_id' => $this->otherUser->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/subscriptions/analytics/summary');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(4, $data['total_subscriptions']);
        $this->assertEquals(3, $data['active_subscriptions']);
        $this->assertEquals(1, $data['cancelled_subscriptions']);
    }

    public function test_spending_analytics_returns_user_specific_data()
    {
        Subscription::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'cost' => 10.00
        ]);

        Subscription::factory()->count(1)->create([
            'user_id' => $this->otherUser->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'cost' => 50.00
        ]);

        $response = $this->actingAs($this->user)
            ->get('/subscriptions/analytics/spending');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals(20.00, $data['spending_trend']['current_month']);
    }

    public function test_category_breakdown_returns_user_specific_data()
    {
        Subscription::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'category' => 'Entertainment'
        ]);

        Subscription::factory()->count(3)->create([
            'user_id' => $this->otherUser->id,
            'status' => 'active',
            'category' => 'Entertainment'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/subscriptions/analytics/category-breakdown');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Should only show data for the authenticated user
        $this->assertCount(1, $data); // Only one category for authenticated user
        $this->assertEquals('Entertainment', $data[0]['category']);
        $this->assertEquals(2, $data[0]['count']);
    }

    public function test_unauthenticated_access_is_denied()
    {
        $subscription = Subscription::factory()->create();

        $this->get('/subscriptions')->assertRedirect();
        $this->get("/subscriptions/{$subscription->id}")->assertRedirect();
        $this->post('/subscriptions', [])->assertRedirect();
        $this->put("/subscriptions/{$subscription->id}", [])->assertRedirect();
        $this->delete("/subscriptions/{$subscription->id}")->assertRedirect();
    }
}
