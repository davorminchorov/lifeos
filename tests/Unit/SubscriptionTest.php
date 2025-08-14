<?php

namespace Tests\Unit;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_belongs_to_user()
    {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $subscription->user);
        $this->assertEquals($user->id, $subscription->user->id);
    }

    public function test_subscription_has_correct_fillable_fields()
    {
        $subscription = new Subscription();

        $expectedFillable = [
            'user_id',
            'service_name',
            'description',
            'category',
            'cost',
            'billing_cycle',
            'billing_cycle_days',
            'currency',
            'start_date',
            'next_billing_date',
            'cancellation_date',
            'payment_method',
            'merchant_info',
            'auto_renewal',
            'cancellation_difficulty',
            'price_history',
            'notes',
            'tags',
            'status',
        ];

        $this->assertEquals($expectedFillable, $subscription->getFillable());
    }

    public function test_subscription_casts_attributes_correctly()
    {
        $subscription = Subscription::factory()->create([
            'cost' => 19.99,
            'billing_cycle_days' => 30,
            'auto_renewal' => true,
            'cancellation_difficulty' => 3,
            'price_history' => [['date' => '2023-01-01', 'price' => 9.99]],
            'tags' => ['work', 'essential'],
        ]);

        $this->assertIsFloat($subscription->cost);
        $this->assertIsInt($subscription->billing_cycle_days);
        $this->assertIsBool($subscription->auto_renewal);
        $this->assertIsInt($subscription->cancellation_difficulty);
        $this->assertIsArray($subscription->price_history);
        $this->assertIsArray($subscription->tags);
    }

    public function test_active_scope_returns_only_active_subscriptions()
    {
        $activeSubscription = Subscription::factory()->create(['status' => 'active']);
        $cancelledSubscription = Subscription::factory()->create(['status' => 'cancelled']);
        $pausedSubscription = Subscription::factory()->create(['status' => 'paused']);

        $activeSubscriptions = Subscription::active()->get();

        $this->assertCount(1, $activeSubscriptions);
        $this->assertTrue($activeSubscriptions->contains($activeSubscription));
        $this->assertFalse($activeSubscriptions->contains($cancelledSubscription));
        $this->assertFalse($activeSubscriptions->contains($pausedSubscription));
    }

    public function test_due_soon_scope_returns_subscriptions_due_within_specified_days()
    {
        $dueTomorrow = Subscription::factory()->create([
            'status' => 'active',
            'next_billing_date' => now()->addDay(),
        ]);

        $dueNextWeek = Subscription::factory()->create([
            'status' => 'active',
            'next_billing_date' => now()->addDays(8),
        ]);

        $dueSoonSubscriptions = Subscription::dueSoon(7)->get();

        $this->assertCount(1, $dueSoonSubscriptions);
        $this->assertTrue($dueSoonSubscriptions->contains($dueTomorrow));
        $this->assertFalse($dueSoonSubscriptions->contains($dueNextWeek));
    }

    public function test_due_soon_scope_only_returns_active_subscriptions()
    {
        $activeDueSoon = Subscription::factory()->create([
            'status' => 'active',
            'next_billing_date' => now()->addDay(),
        ]);

        $cancelledDueSoon = Subscription::factory()->create([
            'status' => 'cancelled',
            'next_billing_date' => now()->addDay(),
        ]);

        $dueSoonSubscriptions = Subscription::dueSoon(7)->get();

        $this->assertCount(1, $dueSoonSubscriptions);
        $this->assertTrue($dueSoonSubscriptions->contains($activeDueSoon));
        $this->assertFalse($dueSoonSubscriptions->contains($cancelledDueSoon));
    }

    public function test_monthly_cost_attribute_calculates_correctly_for_monthly_billing()
    {
        $subscription = Subscription::factory()->create([
            'billing_cycle' => 'monthly',
            'cost' => 10.00,
        ]);

        $this->assertEquals(10.00, $subscription->monthly_cost);
    }

    public function test_monthly_cost_attribute_calculates_correctly_for_yearly_billing()
    {
        $subscription = Subscription::factory()->create([
            'billing_cycle' => 'yearly',
            'cost' => 120.00,
        ]);

        $this->assertEquals(10.00, $subscription->monthly_cost);
    }

    public function test_monthly_cost_attribute_calculates_correctly_for_weekly_billing()
    {
        $subscription = Subscription::factory()->create([
            'billing_cycle' => 'weekly',
            'cost' => 10.00,
        ]);

        $this->assertEquals(43.3, $subscription->monthly_cost);
    }

    public function test_monthly_cost_attribute_calculates_correctly_for_custom_billing()
    {
        $subscription = Subscription::factory()->create([
            'billing_cycle' => 'custom',
            'billing_cycle_days' => 15,
            'cost' => 10.00,
        ]);

        $expectedMonthlyCost = (10.00 * 30.44) / 15;
        $this->assertEquals($expectedMonthlyCost, $subscription->monthly_cost);
    }

    public function test_monthly_cost_attribute_returns_zero_for_custom_billing_without_days()
    {
        $subscription = Subscription::factory()->create([
            'billing_cycle' => 'custom',
            'billing_cycle_days' => null,
            'cost' => 10.00,
        ]);

        $this->assertEquals(0, $subscription->monthly_cost);
    }

    public function test_monthly_cost_attribute_returns_zero_for_unknown_billing_cycle()
    {
        $subscription = new Subscription([
            'billing_cycle' => 'unknown',
            'cost' => 10.00,
        ]);

        $this->assertEquals(0, $subscription->monthly_cost);
    }

    public function test_yearly_cost_attribute_calculates_correctly()
    {
        $subscription = Subscription::factory()->create([
            'billing_cycle' => 'monthly',
            'cost' => 10.00,
        ]);

        $this->assertEquals(120.00, $subscription->yearly_cost);
    }
}
