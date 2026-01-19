<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
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

    public function test_index_shows_only_user_customers()
    {
        Customer::factory()->count(3)->create(['user_id' => $this->user->id]);
        Customer::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get('/invoicing/customers');

        $response->assertStatus(200);
    }

    public function test_store_creates_customer_with_valid_data()
    {
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'address_line1' => '123 Main St',
            'city' => 'Test City',
            'country' => 'US',
        ];

        $response = $this->actingAs($this->user)
            ->post('/invoicing/customers', $customerData);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'user_id' => $this->user->id,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);
    }

    public function test_show_prevents_access_to_other_users_customer()
    {
        $otherCustomer = Customer::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get("/invoicing/customers/{$otherCustomer->id}");

        $response->assertStatus(403);
    }

    public function test_update_prevents_idor_attack()
    {
        $otherCustomer = Customer::factory()->create(['user_id' => $this->otherUser->id]);

        $updateData = [
            'name' => 'Hacked Name',
            'email' => 'hacked@example.com',
        ];

        $response = $this->actingAs($this->user)
            ->put("/invoicing/customers/{$otherCustomer->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_destroy_prevents_deletion_of_other_users_customer()
    {
        $otherCustomer = Customer::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->delete("/invoicing/customers/{$otherCustomer->id}");

        $response->assertStatus(403);
    }

    public function test_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post('/invoicing/customers', []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_validates_email_format()
    {
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'invalid-email',
        ];

        $response = $this->actingAs($this->user)
            ->post('/invoicing/customers', $customerData);

        $response->assertSessionHasErrors(['email']);
    }
}
