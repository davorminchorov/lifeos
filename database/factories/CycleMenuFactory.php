<?php

namespace Database\Factories;

use App\Models\CycleMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CycleMenu>
 */
class CycleMenuFactory extends Factory
{
    protected $model = CycleMenu::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->words(3, true),
            'starts_on' => $this->faker->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d'),
            'cycle_length_days' => 7,
            'is_active' => false,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
        ]);
    }
}
