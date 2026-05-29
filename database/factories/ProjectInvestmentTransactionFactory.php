<?php

namespace Database\Factories;

use App\Models\ProjectInvestment;
use App\Models\ProjectInvestmentTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectInvestmentTransaction>
 */
class ProjectInvestmentTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_investment_id' => ProjectInvestment::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 100000),
            'currency' => 'USD',
            'transaction_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
