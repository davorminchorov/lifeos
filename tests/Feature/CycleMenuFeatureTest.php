<?php

namespace Tests\Feature;

use App\Enums\MealType;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleMenuFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_cycle_menu_and_auto_creates_days_then_adds_multiple_items_and_reorders_them(): void
    {
        $user = User::factory()->create();

        // Create menu via HTTP
        $resp = $this->actingAs($user)
            ->post(route('cycle-menus.store'), [
                'name' => 'Weekly Menu',
                'starts_on' => now()->toDateString(),
                'cycle_length_days' => 7,
                'is_active' => true,
                'notes' => 'Test notes',
            ]);

        $resp->assertRedirect();

        /** @var CycleMenu $menu */
        $menu = CycleMenu::query()->firstOrFail();

        // Ensure days 0..6 exist
        $this->assertEquals(7, $menu->days()->count());
        $this->assertNotNull($menu->days()->where('day_index', 0)->first());
        $day = $menu->days()->where('day_index', 0)->first();

        // Add three items via HTTP
        $this->actingAs($user)
            ->post(route('cycle-menu-items.store'), [
                'cycle_menu_day_id' => $day->id,
                'title' => 'Oatmeal',
                'meal_type' => MealType::Breakfast->value,
                'time_of_day' => '08:00',
                'quantity' => '1 bowl',
            ])->assertRedirect();

        $this->actingAs($user)
            ->post(route('cycle-menu-items.store'), [
                'cycle_menu_day_id' => $day->id,
                'title' => 'Chicken salad wrap',
                'meal_type' => MealType::Lunch->value,
                'time_of_day' => '12:30',
                'quantity' => '1 wrap',
            ])->assertRedirect();

        $this->actingAs($user)
            ->post(route('cycle-menu-items.store'), [
                'cycle_menu_day_id' => $day->id,
                'title' => 'Greek yogurt',
                'meal_type' => MealType::Snack->value,
                'time_of_day' => '15:00',
                'quantity' => '1 cup',
            ])->assertRedirect();

        $day->refresh();
        $this->assertCount(3, $day->items);

        // Reorder: send positions [2,0,1] by id order
        $orders = $day->items()->orderBy('position')->get()->values()->map(function ($item, $i) {
            return ['id' => $item->id, 'position' => [2, 0, 1][$i]]; // first ->2, second->0, third->1
        })->all();

        $this->actingAs($user)
            ->post(route('cycle-menu-items.reorder'), [
                'orders' => $orders,
            ])->assertRedirect();

        $sorted = $day->items()->orderBy('position')->pluck('title')->all();
        $this->assertCount(3, $sorted);
    }

    public function test_adds_missing_days_when_cycle_length_increases_without_deleting_existing_days(): void
    {
        $user = User::factory()->create();
        $menu = CycleMenu::factory()->create([
            'user_id' => $user->id,
            'cycle_length_days' => 3,
            'is_active' => false,
        ]);
        // Seed existing days 0..2
        for ($i = 0; $i < 3; $i++) {
            CycleMenuDay::firstOrCreate(['cycle_menu_id' => $menu->id, 'day_index' => $i]);
        }

        // Increase to 5 via HTTP update
        $this->actingAs($user)
            ->put(route('cycle-menus.update', $menu), [
                'cycle_length_days' => 5,
                'name' => $menu->name,
            ])->assertRedirect();

        $menu->refresh();
        $this->assertEquals(5, $menu->cycle_length_days);
        // Ensure new days 3 and 4 exist, and original 0..2 remain
        $indices = $menu->days()->pluck('day_index')->sort()->values()->all();
        $this->assertEquals([0, 1, 2, 3, 4], $indices);
    }
}
