<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contractTypes = ['lease', 'employment', 'service', 'insurance', 'phone', 'internet', 'maintenance'];
        $titles = [
            'Apartment Lease Agreement',
            'Software Development Contract',
            'Car Insurance Policy',
            'Home Cleaning Service',
            'IT Support Contract',
            'Gym Membership Agreement',
            'Phone Service Contract',
        ];

        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = $this->faker->optional(0.8)->dateTimeBetween($startDate, '+2 years');

        return [
            'user_id' => 1, // Will be overridden when creating with relationships
            'contract_type' => $this->faker->randomElement($contractTypes),
            'title' => $this->faker->randomElement($titles),
            'counterparty' => $this->faker->company(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'notice_period_days' => $this->faker->optional()->randomElement([30, 60, 90]),
            'auto_renewal' => $this->faker->boolean(30),
            'contract_value' => $this->faker->optional()->randomFloat(2, 100, 50000),
            'payment_terms' => $this->faker->optional()->randomElement(['Monthly', 'Quarterly', 'Annually', 'One-time']),
            'key_obligations' => $this->faker->optional()->paragraph(),
            'penalties' => $this->faker->optional()->sentence(),
            'termination_clauses' => $this->faker->optional()->paragraph(),
            'document_attachments' => $this->faker->optional()->randomElements([
                'contract_signed.pdf',
                'terms_conditions.pdf',
            ]),
            'performance_rating' => $this->faker->optional()->numberBetween(1, 5),
            'renewal_history' => $this->faker->optional()->randomElements([
                ['date' => '2023-01-01', 'action' => 'renewed'],
                ['date' => '2022-01-01', 'action' => 'initial'],
            ]),
            'amendments' => $this->faker->optional()->randomElements([
                ['date' => '2023-06-01', 'change' => 'Price increase'],
            ]),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['active', 'expired', 'terminated', 'pending']),
        ];
    }
}
