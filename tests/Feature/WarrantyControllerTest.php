<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarrantyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_display_warranties_index(): void
    {
        Warranty::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('warranties.index'));

        $response->assertStatus(200);
        $response->assertViewIs('warranties.index');
    }

    public function test_can_search_warranties_by_product_name(): void
    {
        Warranty::factory()->create([
            'user_id' => $this->user->id,
            'product_name' => 'iPhone 14'
        ]);

        Warranty::factory()->create([
            'user_id' => $this->user->id,
            'product_name' => 'MacBook Pro'
        ]);

        $response = $this->actingAs($this->user)->get(route('warranties.index', ['search' => 'iPhone']));

        $response->assertStatus(200);
        $response->assertSee('iPhone 14');
        $response->assertDontSee('MacBook Pro');
    }

    public function test_can_filter_warranties_by_status(): void
    {
        Warranty::factory()->create([
            'user_id' => $this->user->id,
            'current_status' => 'active'
        ]);

        Warranty::factory()->create([
            'user_id' => $this->user->id,
            'current_status' => 'expired'
        ]);

        $response = $this->actingAs($this->user)->get(route('warranties.index', ['status' => 'active']));

        $response->assertStatus(200);
    }

    public function test_can_display_create_warranty_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('warranties.create'));

        $response->assertStatus(200);
        $response->assertViewIs('warranties.create');
    }

    public function test_can_store_new_warranty(): void
    {
        $warrantyData = [
            'product_name' => $this->faker->words(2, true),
            'brand' => $this->faker->company,
            'model' => $this->faker->bothify('??-####'),
            'serial_number' => $this->faker->bothify('??########'),
            'purchase_date' => now()->subMonths(6)->format('Y-m-d'),
            'warranty_expiration_date' => now()->addMonths(6)->format('Y-m-d'),
            'warranty_duration_months' => 12,
            'purchase_price' => $this->faker->randomFloat(2, 100, 2000),
            'retailer' => $this->faker->company,
            'warranty_type' => 'manufacturer',
            'warranty_terms' => $this->faker->paragraph,
            'current_status' => 'active'
        ];

        $response = $this->actingAs($this->user)->post(route('warranties.store'), $warrantyData);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('warranties', [
            'user_id' => $this->user->id,
            'product_name' => $warrantyData['product_name'],
            'serial_number' => $warrantyData['serial_number']
        ]);
    }

    public function test_store_warranty_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('warranties.store'), []);

        $response->assertSessionHasErrors([
            'product_name',
            'purchase_date',
            'warranty_expiration_date'
        ]);
    }

    public function test_can_display_warranty_details(): void
    {
        $warranty = Warranty::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('warranties.show', $warranty));

        $response->assertStatus(200);
        $response->assertViewIs('warranties.show');
        $response->assertSee($warranty->product_name);
    }

    public function test_can_display_edit_warranty_form(): void
    {
        $warranty = Warranty::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('warranties.edit', $warranty));

        $response->assertStatus(200);
        $response->assertViewIs('warranties.edit');
        $response->assertSee($warranty->product_name);
    }

    public function test_can_update_warranty(): void
    {
        $warranty = Warranty::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'product_name' => 'Updated Product Name',
            'brand' => $warranty->brand,
            'purchase_date' => $warranty->purchase_date->format('Y-m-d'),
            'warranty_expiration_date' => $warranty->warranty_expiration_date->format('Y-m-d'),
            'warranty_duration_months' => $warranty->warranty_duration_months,
            'purchase_price' => $warranty->purchase_price,
            'retailer' => $warranty->retailer,
            'warranty_type' => $warranty->warranty_type,
            'current_status' => 'active'
        ];

        $response = $this->actingAs($this->user)->put(route('warranties.update', $warranty), $updateData);

        $response->assertRedirect(route('warranties.show', $warranty));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('warranties', [
            'id' => $warranty->id,
            'product_name' => 'Updated Product Name'
        ]);
    }

    public function test_can_delete_warranty(): void
    {
        $warranty = Warranty::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('warranties.destroy', $warranty));

        $response->assertRedirect(route('warranties.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('warranties', ['id' => $warranty->id]);
    }



    public function test_unauthenticated_users_cannot_access_warranties(): void
    {
        $response = $this->get(route('warranties.index'));

        $response->assertRedirect(route('login'));
    }
}
