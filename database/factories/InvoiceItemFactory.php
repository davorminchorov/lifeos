<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitAmount = fake()->numberBetween(1000, 50000); // $10 to $500
        $amount = (int) round($quantity * $unitAmount);
        $taxAmount = 0;
        $discountAmount = 0;
        $totalAmount = $amount + $taxAmount - $discountAmount;

        return [
            'invoice_id' => Invoice::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'quantity' => $quantity,
            'unit_amount' => $unitAmount,
            'currency' => 'USD',
            'tax_rate_id' => null,
            'tax_amount' => $taxAmount,
            'discount_id' => null,
            'discount_amount' => $discountAmount,
            'amount' => $amount,
            'total_amount' => $totalAmount,
            'metadata' => null,
            'sort_order' => 1,
        ];
    }
}
