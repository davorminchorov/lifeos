<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'default_currency' => fake()->randomElement(['MKD', 'USD', 'EUR', 'GBP']),
            'default_country' => fake()->randomElement(['MK', 'US', 'GB', 'DE', 'FR', 'CA', 'AU', 'RS', 'BG']),
            'owner_id' => User::factory(),
        ];
    }
}
