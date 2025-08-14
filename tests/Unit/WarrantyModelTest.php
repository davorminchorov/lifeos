<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_warranty_has_fillable_attributes(): void
    {
        $fillable = [
            'user_id', 'product_name', 'brand', 'model', 'serial_number', 'purchase_date',
            'purchase_price', 'retailer', 'warranty_duration_months', 'warranty_type',
            'warranty_terms', 'warranty_expiration_date', 'claim_history', 'receipt_attachments',
            'proof_of_purchase_attachments', 'current_status', 'transfer_history',
            'maintenance_reminders', 'notes'
        ];
        $warranty = new Warranty();

        $this->assertEquals($fillable, $warranty->getFillable());
    }

    public function test_warranty_casts_attributes_correctly(): void
    {
        $warranty = new Warranty();
        $casts = $warranty->getCasts();

        $this->assertArrayHasKey('purchase_date', $casts);
        $this->assertArrayHasKey('purchase_price', $casts);
        $this->assertArrayHasKey('warranty_duration_months', $casts);
        $this->assertArrayHasKey('warranty_expiration_date', $casts);
        $this->assertArrayHasKey('claim_history', $casts);
        $this->assertArrayHasKey('receipt_attachments', $casts);
        $this->assertArrayHasKey('proof_of_purchase_attachments', $casts);
        $this->assertArrayHasKey('transfer_history', $casts);
        $this->assertArrayHasKey('maintenance_reminders', $casts);

        $this->assertEquals('date', $casts['purchase_date']);
        $this->assertEquals('decimal:2', $casts['purchase_price']);
        $this->assertEquals('integer', $casts['warranty_duration_months']);
        $this->assertEquals('date', $casts['warranty_expiration_date']);
        $this->assertEquals('array', $casts['claim_history']);
        $this->assertEquals('array', $casts['receipt_attachments']);
        $this->assertEquals('array', $casts['proof_of_purchase_attachments']);
        $this->assertEquals('array', $casts['transfer_history']);
        $this->assertEquals('array', $casts['maintenance_reminders']);
    }

    public function test_warranty_belongs_to_user(): void
    {
        $warranty = new Warranty();
        $relationship = $warranty->user();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
    }

    public function test_scope_active(): void
    {
        $user = User::factory()->create();
        Warranty::factory()->create(['user_id' => $user->id, 'current_status' => 'active']);
        Warranty::factory()->create(['user_id' => $user->id, 'current_status' => 'expired']);
        Warranty::factory()->create(['user_id' => $user->id, 'current_status' => 'active']);

        $activeWarranties = Warranty::active()->get();

        $this->assertCount(2, $activeWarranties);
        $activeWarranties->each(function ($warranty) {
            $this->assertEquals('active', $warranty->current_status);
        });
    }

    public function test_scope_expiring_soon(): void
    {
        $user = User::factory()->create();
        Warranty::factory()->create([
            'user_id' => $user->id,
            'current_status' => 'active',
            'warranty_expiration_date' => now()->addDays(15)
        ]);
        Warranty::factory()->create([
            'user_id' => $user->id,
            'current_status' => 'active',
            'warranty_expiration_date' => now()->addDays(45)
        ]);
        Warranty::factory()->create([
            'user_id' => $user->id,
            'current_status' => 'expired',
            'warranty_expiration_date' => now()->addDays(15)
        ]);

        $expiringSoonWarranties = Warranty::expiringSoon()->get();

        $this->assertCount(1, $expiringSoonWarranties);
        $this->assertTrue($expiringSoonWarranties->first()->warranty_expiration_date->lte(now()->addDays(30)));
        $this->assertEquals('active', $expiringSoonWarranties->first()->current_status);
    }

    public function test_scope_expired(): void
    {
        $user = User::factory()->create();
        Warranty::factory()->create([
            'user_id' => $user->id,
            'warranty_expiration_date' => now()->subDays(10)
        ]);
        Warranty::factory()->create([
            'user_id' => $user->id,
            'warranty_expiration_date' => now()->addDays(10)
        ]);

        $expiredWarranties = Warranty::expired()->get();

        $this->assertCount(1, $expiredWarranties);
        $this->assertTrue($expiredWarranties->first()->warranty_expiration_date->isPast());
    }

    public function test_is_expired_attribute(): void
    {
        $user = User::factory()->create();
        $expiredWarranty = Warranty::factory()->create([
            'user_id' => $user->id,
            'warranty_expiration_date' => now()->subDays(1)
        ]);
        $activeWarranty = Warranty::factory()->create([
            'user_id' => $user->id,
            'warranty_expiration_date' => now()->addDays(1)
        ]);

        $this->assertTrue($expiredWarranty->is_expired);
        $this->assertFalse($activeWarranty->is_expired);
    }

    public function test_days_until_expiration_attribute(): void
    {
        $user = User::factory()->create();
        $warranty = Warranty::factory()->create([
            'user_id' => $user->id,
            'warranty_expiration_date' => now()->addDays(30)
        ]);

        $this->assertEquals(30, $warranty->days_until_expiration);
    }

    public function test_warranty_remaining_percentage_attribute(): void
    {
        $user = User::factory()->create();

        // Test warranty that's 50% through its life
        $warranty = Warranty::factory()->create([
            'user_id' => $user->id,
            'purchase_date' => now()->subDays(60),
            'warranty_expiration_date' => now()->addDays(60)
        ]);

        $this->assertEquals(50, $warranty->warranty_remaining_percentage);

        // Test expired warranty (should be 0)
        $expiredWarranty = Warranty::factory()->create([
            'user_id' => $user->id,
            'purchase_date' => now()->subDays(120),
            'warranty_expiration_date' => now()->subDays(60)
        ]);

        $this->assertEquals(0, $expiredWarranty->warranty_remaining_percentage);
    }

    public function test_has_claims_attribute(): void
    {
        $user = User::factory()->create();
        $warrantyWithClaims = Warranty::factory()->create([
            'user_id' => $user->id,
            'claim_history' => [
                ['date' => '2023-01-15', 'description' => 'Battery replacement'],
                ['date' => '2023-03-20', 'description' => 'Screen repair']
            ]
        ]);
        $warrantyWithoutClaims = Warranty::factory()->create([
            'user_id' => $user->id,
            'claim_history' => []
        ]);

        $this->assertTrue($warrantyWithClaims->has_claims);
        $this->assertFalse($warrantyWithoutClaims->has_claims);
    }

    public function test_total_claims_attribute(): void
    {
        $user = User::factory()->create();
        $warrantyWithClaims = Warranty::factory()->create([
            'user_id' => $user->id,
            'claim_history' => [
                ['date' => '2023-01-15', 'description' => 'Battery replacement'],
                ['date' => '2023-03-20', 'description' => 'Screen repair'],
                ['date' => '2023-06-10', 'description' => 'Button repair']
            ]
        ]);
        $warrantyWithoutClaims = Warranty::factory()->create([
            'user_id' => $user->id,
            'claim_history' => []
        ]);
        $warrantyWithNullClaims = Warranty::factory()->create([
            'user_id' => $user->id,
            'claim_history' => null
        ]);

        $this->assertEquals(3, $warrantyWithClaims->total_claims);
        $this->assertEquals(0, $warrantyWithoutClaims->total_claims);
        $this->assertEquals(0, $warrantyWithNullClaims->total_claims);
    }

    public function test_warranty_factory_creates_valid_warranty(): void
    {
        $warranty = Warranty::factory()->create();

        $this->assertInstanceOf(Warranty::class, $warranty);
        $this->assertNotNull($warranty->user_id);
        $this->assertNotNull($warranty->product_name);
        $this->assertNotNull($warranty->purchase_date);
        $this->assertNotNull($warranty->warranty_expiration_date);
        $this->assertNotNull($warranty->created_at);
        $this->assertNotNull($warranty->updated_at);
    }

    public function test_warranty_factory_can_create_with_custom_attributes(): void
    {
        $warrantyData = [
            'product_name' => 'iPhone 15 Pro',
            'brand' => 'Apple',
            'warranty_duration_months' => 24,
            'purchase_price' => 999.99
        ];

        $warranty = Warranty::factory()->create($warrantyData);

        $this->assertEquals('iPhone 15 Pro', $warranty->product_name);
        $this->assertEquals('Apple', $warranty->brand);
        $this->assertEquals(24, $warranty->warranty_duration_months);
        $this->assertEquals(999.99, $warranty->purchase_price);
    }
}
