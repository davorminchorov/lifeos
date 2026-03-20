<?php

namespace App\Ai\Tools\System;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\CycleMenu;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class TodaysMenuTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return "Get today's meal plan from the active cycle menu. Use when the user asks what's for lunch/dinner, today's menu, or about meal plans.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): string
    {
        $menu = CycleMenu::where('tenant_id', $this->tenantId())
            ->active()
            ->first();

        if (! $menu) {
            return 'No active cycle menu found.';
        }

        $daysSinceStart = $menu->starts_on->diffInDays(now());
        $dayIndex = $daysSinceStart % $menu->cycle_length_days;

        $day = $menu->days()->where('day_index', $dayIndex)->with('items')->first();

        if (! $day || $day->items->isEmpty()) {
            return 'No menu items planned for today (Day '.($dayIndex + 1)." of {$menu->name}).";
        }

        $lines = ["Today's Menu (Day ".($dayIndex + 1)." — {$menu->name}):"];

        $byMealType = $day->items->groupBy(fn ($item) => $item->meal_type->value);

        $mealOrder = ['breakfast', 'lunch', 'dinner', 'snack', 'other'];

        foreach ($mealOrder as $mealType) {
            if (! isset($byMealType[$mealType])) {
                continue;
            }

            $label = ucfirst($mealType);
            $items = $byMealType[$mealType];
            $itemNames = $items->pluck('title')->join(', ');
            $lines[] = "- {$label}: {$itemNames}";
        }

        if ($day->notes) {
            $lines[] = "Notes: {$day->notes}";
        }

        return implode("\n", $lines);
    }
}
