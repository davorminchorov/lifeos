<?php

declare(strict_types=1);

namespace App\Mcp\Tools\CycleMenu;

use App\Mcp\Tools\AbstractTool;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CurrentWeekCycleMenu extends AbstractTool
{
    protected string $name = 'cycleMenu.currentWeek';

    protected string $description = 'Return the active cycle menu mapped to the current week. Days are aligned by index: today maps to (now - starts_on) modulo cycle_length_days.';

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $menu = CycleMenu::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->with(['days.items'])
            ->first();

        if ($menu === null) {
            return Response::structured([
                'menu' => null,
                'week' => [],
            ]);
        }

        $cycleLength = max(1, (int) $menu->cycle_length_days);
        $startsOn = $menu->starts_on?->startOfDay() ?? now()->startOfDay();
        $today = now()->startOfDay();
        $daysSinceStart = (int) $today->diffInDays($startsOn, false);
        $todayIndex = (($daysSinceStart % $cycleLength) + $cycleLength) % $cycleLength;

        $daysByIndex = $menu->days->keyBy('day_index');

        $week = [];
        for ($offset = 0; $offset < 7; $offset++) {
            $date = $today->copy()->addDays($offset);
            $dayIndex = ($todayIndex + $offset) % $cycleLength;
            $day = $daysByIndex->get($dayIndex);

            $week[] = [
                'date' => $date->toDateString(),
                'day_index' => $dayIndex,
                'notes' => $day?->notes,
                'items' => $day instanceof CycleMenuDay
                    ? $day->items->map(fn (CycleMenuItem $item): array => [
                        'id' => $item->id,
                        'title' => $item->title,
                        'meal_type' => is_object($item->meal_type) ? $item->meal_type?->value : $item->meal_type,
                        'time_of_day' => $item->time_of_day,
                        'quantity' => $item->quantity,
                        'recipe_id' => $item->recipe_id,
                        'position' => $item->position,
                    ])->values()->all()
                    : [],
            ];
        }

        return Response::structured([
            'menu' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'starts_on' => $menu->starts_on?->toDateString(),
                'cycle_length_days' => $cycleLength,
            ],
            'today_day_index' => $todayIndex,
            'week' => $week,
        ]);
    }
}
