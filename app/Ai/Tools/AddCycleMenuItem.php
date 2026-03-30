<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Enums\MealType;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class AddCycleMenuItem extends TenantScopedTool
{
    public function description(): string
    {
        return 'Add a menu item to a specific day of a cycle menu.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'menu_name' => $schema->string()->required()->description('Name of the cycle menu to add to'),
            'day_index' => $schema->integer()->required()->description('Day number (0-based) to add the item to'),
            'title' => $schema->string()->required()->description('Name of the menu item (e.g. Grilled Chicken Salad)'),
            'meal_type' => $schema->string()->required()->description('One of: breakfast, lunch, dinner, snack, other'),
            'time_of_day' => $schema->string()->description('Time of day (e.g. 08:00)'),
            'quantity' => $schema->string()->description('Quantity or serving size'),
        ];
    }

    public function handle(Request $request): string
    {
        $menuName = $request['menu_name'] ?? null;

        $matches = $this->scopedQuery(CycleMenu::class)
            ->where('name', 'LIKE', '%'.$menuName.'%')
            ->limit(5)
            ->get();

        if ($matches->isEmpty()) {
            $available = $this->scopedQuery(CycleMenu::class)
                ->pluck('name')
                ->implode(', ');

            return "No cycle menu found matching '{$menuName}'. Available menus: {$available}";
        }

        if ($matches->count() > 1) {
            $names = $matches->pluck('name')->implode(', ');

            return "Multiple menus match '{$menuName}'. Please be more specific: {$names}";
        }

        $menu = $matches->first();
        $dayIndex = (int) ($request['day_index'] ?? 0);

        if ($dayIndex < 0 || $dayIndex >= $menu->cycle_length_days) {
            return "Day index {$dayIndex} is out of range. This menu has days 0 to ".($menu->cycle_length_days - 1).'.';
        }

        $day = CycleMenuDay::query()
            ->where('cycle_menu_id', $menu->id)
            ->where('day_index', $dayIndex)
            ->first();

        if ($day === null) {
            $day = CycleMenuDay::create([
                'tenant_id' => $this->tenantId,
                'cycle_menu_id' => $menu->id,
                'day_index' => $dayIndex,
            ]);
        }

        $mealTypeInput = strtolower($request['meal_type'] ?? 'other');
        $mealType = MealType::tryFrom($mealTypeInput);

        if ($mealType === null) {
            $validTypes = implode(', ', array_map(fn (MealType $t) => $t->value, MealType::cases()));

            return "Invalid meal type '{$mealTypeInput}'. Valid types: {$validTypes}";
        }

        $maxPosition = CycleMenuItem::query()
            ->where('cycle_menu_day_id', $day->id)
            ->max('position') ?? -1;

        $data = [
            'title' => $request['title'] ?? null,
            'meal_type' => $mealType->value,
            'time_of_day' => $request['time_of_day'] ?? null,
            'quantity' => $request['quantity'] ?? null,
        ];

        $validated = $this->validate($data, [
            'title' => 'required|string|max:255',
            'meal_type' => 'required|string',
            'time_of_day' => 'nullable|string|max:10',
            'quantity' => 'nullable|string|max:255',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        CycleMenuItem::create([
            'tenant_id' => $this->tenantId,
            'cycle_menu_day_id' => $day->id,
            'title' => $validated['title'],
            'meal_type' => $mealType,
            'time_of_day' => $validated['time_of_day'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'position' => $maxPosition + 1,
        ]);

        return "Added {$mealType->value} item '{$validated['title']}' to day {$dayIndex} of '{$menu->name}'.";
    }
}
