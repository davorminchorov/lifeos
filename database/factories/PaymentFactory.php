<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invoice_id' => Invoice::factory(),
            'provider' => 'manual',
            'provider_payment_id' => null,
            'amount' => fake()->numberBetween(1000, 100000),
            'currency' => 'USD',
            'status' => PaymentStatus::SUCCEEDED,
            'attempted_at' => now(),
            'succeeded_at' => now(),
            'failed_at' => null,
            'failure_code' => null,
            'failure_message' => null,
            'payment_method' => fake()->randomElement(['bank_transfer', 'credit_card', 'cash', 'check']),
            'payment_method_details' => null,
            'payment_date' => now()->toDateString(),
            'reference' => fake()->optional()->bothify('REF-####-????'),
            'metadata' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::PENDING,
            'succeeded_at' => null,
            'attempted_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::FAILED,
            'succeeded_at' => null,
            'failed_at' => now(),
            'failure_code' => 'insufficient_funds',
            'failure_message' => 'Payment failed due to insufficient funds',
        ]);
    }
}
