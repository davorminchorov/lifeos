<?php

namespace Database\Seeders;

use App\Enums\MealType;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoCycleMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a user for the demo menu
        $user = User::query()->first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Demo User',
                'email' => 'demo@lifeos.test',
            ]);
        }

        // Create or find a demo active cycle menu starting today
        $menu = CycleMenu::query()->firstOrCreate(
            ['name' => 'Demo 7-Day Cycle Menu'],
            [
                'user_id' => $user->id,
                'starts_on' => Carbon::now()->toDateString(),
                'cycle_length_days' => 7,
                'is_active' => true,
                'notes' => 'Sample menu with multiple items per day.',
            ]
        );

        // Ensure the menu has a user_id if it was created before the migration
        if (!$menu->user_id) {
            $menu->update(['user_id' => $user->id]);
        }

        // Ensure days 0..6 exist
        for ($i = 0; $i < $menu->cycle_length_days; $i++) {
            CycleMenuDay::firstOrCreate([
                'cycle_menu_id' => $menu->id,
                'day_index' => $i,
            ]);
        }

        $mealTypes = MealType::cases();
        $titles = [
            'Oatmeal with berries', 'Greek yogurt parfait', 'Chicken salad wrap', 'Grilled salmon',
            'Quinoa bowl', 'Veggie omelette', 'Protein smoothie', 'Avocado toast', 'Turkey sandwich',
            'Stir-fry veggies', 'Pasta with pesto', 'Beef and rice bowl', 'Tofu scramble', 'Chili with beans',
        ];

        // Populate each day with 3–5 items
        foreach ($menu->days as $day) {
            if ($day->items()->count() >= 3) {
                continue; // don't duplicate if already seeded
            }

            $count = rand(3, 5);
            for ($p = 0; $p < $count; $p++) {
                $type = $mealTypes[$p % count($mealTypes)];
                $title = $titles[array_rand($titles)];
                $hour = match ($type) {
                    MealType::Breakfast => 8,
                    MealType::Lunch => 12,
                    MealType::Dinner => 19,
                    MealType::Snack => 15,
                    default => 10,
                };

                CycleMenuItem::create([
                    'cycle_menu_day_id' => $day->id,
                    'title' => $title,
                    'meal_type' => $type->value,
                    'time_of_day' => sprintf('%02d:%02d:00', $hour, rand(0, 1) ? 0 : 30),
                    'quantity' => rand(0, 1) ? '1 serving' : '2 servings',
                    'recipe_id' => null,
                    'position' => $p,
                ]);
            }
        }
    }
}
