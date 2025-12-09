<?php

namespace Database\Factories;

use App\Enums\MealType;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CycleMenuItem>
 */
class CycleMenuItemFactory extends Factory
{
    protected $model = CycleMenuItem::class;

    public function definition(): array
    {
        $mealType = $this->faker->randomElement(array_map(fn ($c) => $c->value, MealType::cases()));

        return [
            'cycle_menu_day_id' => CycleMenuDay::factory(),
            'title' => $this->faker->words(3, true),
            'meal_type' => $mealType,
            'time_of_day' => $this->faker->optional()->time('H:i:s'),
            'quantity' => $this->faker->optional()->randomElement(['1 serving', '2 servings', '250 g', '1 bowl']),
            'recipe_id' => null, // optional link to Recipe if exists
            'position' => $this->faker->numberBetween(0, 5),
        ];
    }
}
