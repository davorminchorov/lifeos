<?php

namespace Tests\Unit;

use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleMenuRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function testDefinesRelationshipsBetweenMenuDaysAndItemsWithOrderedItems(): void
    {
        $menu = CycleMenu::factory()->create();
        $day = CycleMenuDay::factory()->create([
            'cycle_menu_id' => $menu->id,
            'day_index' => 0,
        ]);

        $first = CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day->id,
            'position' => 0,
            'title' => 'First',
        ]);
        $second = CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day->id,
            'position' => 2,
            'title' => 'Second',
        ]);
        $middle = CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day->id,
            'position' => 1,
            'title' => 'Middle',
        ]);

        $this->assertFalse($menu->relationLoaded('days')); // lazy

        $menu->load('days.items');
        $this->assertCount(1, $menu->days);

        $items = $menu->days->first()->items;
        $this->assertEquals(
            ['First', 'Middle', 'Second'],
            $items->pluck('title')->all()
        );
    }
}
