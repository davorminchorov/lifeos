<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warranty>
 */
class WarrantyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            'MacBook Pro', 'iPhone 15', 'Samsung Galaxy S24', 'iPad Air', 'Dell XPS 13',
            'Sony WH-1000XM5', 'Nintendo Switch', 'Tesla Model 3', 'Dyson V15',
            'KitchenAid Mixer', 'LG OLED TV', 'Canon EOS R5', 'Apple Watch Series 9',
        ];

        $brands = ['Apple', 'Samsung', 'Sony', 'Dell', 'HP', 'LG', 'Canon', 'Dyson', 'KitchenAid', 'Tesla'];
        $retailers = ['Best Buy', 'Amazon', 'Apple Store', 'Target', 'Costco', 'B&H', 'Newegg'];
        $warrantyTypes = ['manufacturer', 'extended', 'both'];
        $statuses = ['active', 'expired', 'claimed', 'transferred'];

        $purchaseDate = $this->faker->dateTimeBetween('-3 years', 'now');
        $warrantyMonths = $this->faker->randomElement([12, 24, 36, 48, 60]);
        $warrantyExpiration = (clone $purchaseDate)->modify("+{$warrantyMonths} months");

        return [
            'user_id' => \App\Models\User::factory(),
            'product_name' => $this->faker->randomElement($products),
            'brand' => $this->faker->randomElement($brands),
            'model' => $this->faker->optional()->bothify('##??-###'),
            'serial_number' => $this->faker->optional()->bothify('??########'),
            'purchase_date' => $purchaseDate,
            'purchase_price' => $this->faker->randomFloat(2, 50, 3000),
            'retailer' => $this->faker->randomElement($retailers),
            'warranty_duration_months' => $warrantyMonths,
            'warranty_type' => $this->faker->randomElement($warrantyTypes),
            'warranty_terms' => $this->faker->optional()->paragraph(),
            'warranty_expiration_date' => $warrantyExpiration,
            'claim_history' => $this->faker->optional(0.3)->randomElements([
                [
                    'date' => $this->faker->dateTimeBetween($purchaseDate, 'now')->format('Y-m-d'),
                    'issue' => 'Screen defect',
                    'resolution' => 'Replacement unit provided',
                ],
            ]),
            'receipt_attachments' => $this->faker->optional()->randomElements([
                'receipt_001.pdf',
                'purchase_confirmation.jpg',
            ]),
            'proof_of_purchase_attachments' => $this->faker->optional()->randomElements([
                'invoice.pdf',
                'credit_card_statement.pdf',
            ]),
            'current_status' => $this->faker->randomElement($statuses),
            'transfer_history' => $this->faker->optional(0.1)->randomElements([
                [
                    'date' => $this->faker->dateTimeBetween($purchaseDate, 'now')->format('Y-m-d'),
                    'to' => $this->faker->name(),
                    'reason' => 'Gift',
                ],
            ]),
            'maintenance_reminders' => $this->faker->optional()->randomElements([
                [
                    'type' => 'Software Update',
                    'frequency' => 'Monthly',
                    'last_done' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                ],
            ]),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
