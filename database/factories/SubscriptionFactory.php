<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = ['Netflix', 'Spotify', 'Amazon Prime', 'Disney+', 'Hulu', 'Adobe Creative Suite', 'Microsoft 365', 'Dropbox', 'GitHub Pro', 'Gym Membership'];
        $categories = ['Entertainment', 'Software', 'Storage', 'Fitness', 'Productivity', 'Development'];
        $billingCycles = ['monthly', 'yearly', 'weekly'];
        $paymentMethods = ['Credit Card', 'PayPal', 'Bank Transfer', 'Apple Pay'];

        $startDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $billingCycle = $this->faker->randomElement($billingCycles);

        // Calculate next billing date based on cycle
        $nextBillingDate = match($billingCycle) {
            'monthly' => (clone $startDate)->modify('+1 month'),
            'yearly' => (clone $startDate)->modify('+1 year'),
            'weekly' => (clone $startDate)->modify('+1 week'),
        };

        return [
            'user_id' => \App\Models\User::factory(),
            'service_name' => $this->faker->randomElement($services),
            'description' => $this->faker->optional()->sentence(),
            'category' => $this->faker->randomElement($categories),
            'cost' => $this->faker->randomFloat(2, 2.99, 99.99),
            'billing_cycle' => $billingCycle,
            'billing_cycle_days' => null,
            'currency' => 'USD',
            'start_date' => $startDate,
            'next_billing_date' => $nextBillingDate,
            'cancellation_date' => $this->faker->optional(0.2)->dateTimeBetween($startDate, 'now'),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'merchant_info' => $this->faker->optional()->company(),
            'auto_renewal' => $this->faker->boolean(80),
            'cancellation_difficulty' => $this->faker->optional()->numberBetween(1, 5),
            'price_history' => $this->faker->optional()->randomElements([
                ['date' => '2023-01-01', 'price' => 9.99],
                ['date' => '2023-06-01', 'price' => 12.99],
            ]),
            'notes' => $this->faker->optional()->sentence(),
            'tags' => $this->faker->optional()->randomElements(['essential', 'entertainment', 'work', 'family'], 2),
            'status' => $this->faker->randomElement(['active', 'cancelled', 'paused']),
        ];
    }

    /**
     * Create a subscription with a custom billing cycle.
     */
    public function customBillingCycle(): static
    {
        return $this->state(function (array $attributes) {
            $billingCycleDays = $this->faker->numberBetween(15, 90);
            $startDate = $attributes['start_date'] ?? $this->faker->dateTimeBetween('-2 years', 'now');

            return [
                'billing_cycle' => 'custom',
                'billing_cycle_days' => $billingCycleDays,
                'next_billing_date' => (clone $startDate)->modify("+{$billingCycleDays} days"),
            ];
        });
    }
}
