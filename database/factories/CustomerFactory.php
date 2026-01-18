<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'company_name' => fake()->optional()->company(),
            'billing_address' => [
                'line1' => fake()->streetAddress(),
                'line2' => fake()->optional()->secondaryAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => fake()->countryCode(),
            ],
            'tax_id' => fake()->optional()->numerify('TAX-########'),
            'tax_country' => fake()->optional()->countryCode(),
            'currency' => 'USD',
            'default_payment_method_id' => null,
            'metadata' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
