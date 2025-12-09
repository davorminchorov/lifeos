<?php

use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;

it('defines relationships between menu, days, and items with ordered items', function () {
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

    expect($menu->days)->toHaveCount(0); // lazy

    $menu->load('days.items');
    expect($menu->days)->toHaveCount(1);

    $items = $menu->days->first()->items;
    expect($items->pluck('title')->all())
        ->toEqual(['First', 'Middle', 'Second']);
});
