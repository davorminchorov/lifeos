<?php

namespace Database\Factories;

use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CycleMenuDay>
 */
class CycleMenuDayFactory extends Factory
{
    protected $model = CycleMenuDay::class;

    public function definition(): array
    {
        return [
            'cycle_menu_id' => CycleMenu::factory(),
            'day_index' => $this->faker->numberBetween(0, 6),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
