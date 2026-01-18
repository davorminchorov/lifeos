<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 500000); // $100 to $5000
        $taxTotal = (int) ($subtotal * 0.2); // 20% tax
        $total = $subtotal + $taxTotal;

        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'status' => InvoiceStatus::DRAFT,
            'number' => null,
            'currency' => 'USD',
            'tax_behavior' => 'exclusive',
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'discount_total' => 0,
            'total' => $total,
            'amount_paid' => 0,
            'amount_due' => $total,
            'net_terms_days' => 30,
            'issued_at' => null,
            'due_at' => null,
            'paid_at' => null,
            'voided_at' => null,
            'last_sent_at' => null,
            'pdf_path' => null,
            'subscription_id' => null,
            'metadata' => null,
            'notes' => fake()->optional()->sentence(),
            'internal_notes' => fake()->optional()->sentence(),
        ];
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::ISSUED,
            'number' => 'INV-' . fake()->unique()->numerify('######'),
            'issued_at' => now(),
            'due_at' => now()->addDays($attributes['net_terms_days'] ?? 30),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::PAID,
            'number' => 'INV-' . fake()->unique()->numerify('######'),
            'issued_at' => now()->subDays(30),
            'due_at' => now(),
            'paid_at' => now(),
            'amount_paid' => $attributes['total'],
            'amount_due' => 0,
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(function (array $attributes) {
            $amountPaid = (int) ($attributes['total'] * 0.5);

            return [
                'status' => InvoiceStatus::PARTIALLY_PAID,
                'number' => 'INV-' . fake()->unique()->numerify('######'),
                'issued_at' => now()->subDays(15),
                'due_at' => now()->addDays(15),
                'amount_paid' => $amountPaid,
                'amount_due' => $attributes['total'] - $amountPaid,
            ];
        });
    }
}
