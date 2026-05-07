<?php

declare(strict_types=1);

namespace App\Mcp\Tools\CycleMenu;

use App\Mcp\Tools\AbstractTool;
use App\Models\CycleMenu;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class SetWeek extends AbstractTool
{
    protected string $name = 'cycleMenu.setWeek';

    protected string $description = 'Replace the items on multiple consecutive day_indexes of a cycle menu in one transaction. Existing items on those days are deleted; revert restores them. Idempotent on (menu, sorted day list, sorted item set per day).';

    public function schema(JsonSchema $schema): array
    {
        return [
            'cycle_menu_id' => $schema->integer()->description('Cycle menu id. Required.'),
            'items_by_day_index' => $schema->object()->description(
                'Map of day_index → list of items. Each item: { title, meal_type, time_of_day?, quantity? }. Required.'
            ),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $menuId = (int) $request->get('cycle_menu_id', 0);

        if ($menuId <= 0) {
            return Response::error('cycle_menu_id is required.');
        }

        $menu = CycleMenu::query()->find($menuId);

        if ($menu === null) {
            return Response::error("Cycle menu [{$menuId}] not found in this tenant.");
        }

        $itemsByDay = (array) $request->get('items_by_day_index', []);

        if ($itemsByDay === []) {
            return Response::error('items_by_day_index must contain at least one day.');
        }

        $cycleLength = (int) $menu->cycle_length_days;
        foreach (array_keys($itemsByDay) as $dayIndex) {
            $idx = (int) $dayIndex;
            if ($idx < 0 || $idx >= $cycleLength) {
                return Response::error("day_index {$idx} is outside menu cycle (0..".($cycleLength - 1).').');
            }
        }

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_BULK_CREATE,
                payload: [
                    'cycle_menu_id' => $menu->id,
                    'items_by_day_index' => $itemsByDay,
                ],
            );
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }

        $itemTotal = collect($itemsByDay)->sum(fn ($items) => count((array) $items));

        return Response::structured([
            'pending_action_id' => $action->id,
            'status' => $action->status,
            'idempotency_key' => $action->idempotency_key,
            'day_count' => count($itemsByDay),
            'item_count' => $itemTotal,
            'auto_applied' => $action->status === PendingAction::STATUS_APPLIED,
        ]);
    }
}
