<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateCycleMenu extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create a new cycle menu for meal planning with a specified number of days.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required()->description('Name of the cycle menu'),
            'cycle_length_days' => $schema->integer()->required()->description('Number of days in the cycle (1-365)'),
            'starts_on' => $schema->string()->description('Start date YYYY-MM-DD, defaults to today'),
            'is_active' => $schema->boolean()->description('Whether the menu is active, defaults to true'),
            'notes' => $schema->string()->description('Additional notes'),
        ];
    }

    public function handle(Request $request): string
    {
        $startsOn = $request['starts_on'] ?? date('Y-m-d');
        $isActive = $request['is_active'] ?? true;

        $data = [
            'name' => $request['name'] ?? null,
            'cycle_length_days' => $request['cycle_length_days'] ?? null,
            'starts_on' => $startsOn,
            'is_active' => $isActive,
            'notes' => $request['notes'] ?? null,
        ];

        $validated = $this->validate($data, [
            'name' => 'required|string|max:255',
            'cycle_length_days' => 'required|integer|min:1|max:365',
            'starts_on' => 'required|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:10000',
        ]);

        if (is_string($validated)) {
            return $validated;
        }

        $menu = CycleMenu::create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            ...$validated,
        ]);

        for ($i = 0; $i < $validated['cycle_length_days']; $i++) {
            CycleMenuDay::create([
                'tenant_id' => $this->tenantId,
                'cycle_menu_id' => $menu->id,
                'day_index' => $i,
            ]);
        }

        return "Created cycle menu '{$validated['name']}' with {$validated['cycle_length_days']} days starting {$validated['starts_on']}.";
    }
}
