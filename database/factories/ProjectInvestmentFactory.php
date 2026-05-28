<?php

namespace Database\Factories;

use App\Models\ProjectInvestment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectInvestment>
 */
class ProjectInvestmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company(),
            'project_type' => $this->faker->randomElement(['SaaS', 'Mobile App', 'Marketplace']),
            'stage' => $this->faker->randomElement(['idea', 'prototype', 'mvp', 'growth', 'mature']),
            'business_model' => $this->faker->randomElement(['subscription', 'ads', 'one-time', 'freemium']),
            'equity_percentage' => $this->faker->randomFloat(2, 0, 100),
            'current_value' => null,
            'start_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
