<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\IdempotencyKey;
use Tests\TestCase;

class IdempotencyKeyPhase9Test extends TestCase
{
    private IdempotencyKey $keys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new IdempotencyKey;
    }

    public function test_add_item_collapses_on_same_natural_fields(): void
    {
        $a = $this->keys->for('cycleMenu.addItem', 1, [
            'cycle_menu_id' => 7,
            'day_index' => 2,
            'title' => 'Pasta Bolognese',
            'meal_type' => 'dinner',
        ]);
        $b = $this->keys->for('cycleMenu.addItem', 1, [
            'cycle_menu_id' => 7,
            'day_index' => 2,
            'title' => '  PASTA  Bolognese  ',
            'meal_type' => 'DINNER',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_add_item_distinguishes_by_meal_type(): void
    {
        $lunch = $this->keys->for('cycleMenu.addItem', 1, [
            'cycle_menu_id' => 7,
            'day_index' => 2,
            'title' => 'Pasta Bolognese',
            'meal_type' => 'lunch',
        ]);
        $dinner = $this->keys->for('cycleMenu.addItem', 1, [
            'cycle_menu_id' => 7,
            'day_index' => 2,
            'title' => 'Pasta Bolognese',
            'meal_type' => 'dinner',
        ]);

        $this->assertNotSame($lunch, $dinner);
    }

    public function test_set_week_is_order_insensitive_within_a_day(): void
    {
        $a = $this->keys->for('cycleMenu.setWeek', 1, [
            'cycle_menu_id' => 7,
            'items_by_day_index' => [
                0 => [
                    ['title' => 'Eggs', 'meal_type' => 'breakfast'],
                    ['title' => 'Salad', 'meal_type' => 'lunch'],
                ],
            ],
        ]);
        $b = $this->keys->for('cycleMenu.setWeek', 1, [
            'cycle_menu_id' => 7,
            'items_by_day_index' => [
                0 => [
                    ['title' => 'Salad', 'meal_type' => 'lunch'],
                    ['title' => 'Eggs', 'meal_type' => 'breakfast'],
                ],
            ],
        ]);

        $this->assertSame($a, $b);
    }

    public function test_set_week_changes_when_an_item_changes(): void
    {
        $base = [
            'cycle_menu_id' => 7,
            'items_by_day_index' => [
                0 => [
                    ['title' => 'Eggs', 'meal_type' => 'breakfast'],
                ],
            ],
        ];
        $different = [
            'cycle_menu_id' => 7,
            'items_by_day_index' => [
                0 => [
                    ['title' => 'Oatmeal', 'meal_type' => 'breakfast'],
                ],
            ],
        ];

        $this->assertNotSame(
            $this->keys->for('cycleMenu.setWeek', 1, $base),
            $this->keys->for('cycleMenu.setWeek', 1, $different),
        );
    }

    public function test_set_week_distinguishes_across_menus(): void
    {
        $payload = [
            'cycle_menu_id' => 7,
            'items_by_day_index' => [
                0 => [
                    ['title' => 'Eggs', 'meal_type' => 'breakfast'],
                ],
            ],
        ];

        $this->assertNotSame(
            $this->keys->for('cycleMenu.setWeek', 1, $payload),
            $this->keys->for('cycleMenu.setWeek', 1, [...$payload, 'cycle_menu_id' => 8]),
        );
    }
}
