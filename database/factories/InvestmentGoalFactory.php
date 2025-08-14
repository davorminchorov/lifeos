<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvestmentGoal>
 */
class InvestmentGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $targetAmount = fake()->randomFloat(2, 1000, 100000);
        $currentProgress = fake()->randomFloat(2, 0, $targetAmount * 0.8);

        return [
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'target_amount' => $targetAmount,
            'current_progress' => $currentProgress,
            'target_date' => fake()->dateTimeBetween('now', '+5 years'),
            'status' => fake()->randomElement(['active', 'achieved', 'paused', 'cancelled']),
        ];
    }
}
