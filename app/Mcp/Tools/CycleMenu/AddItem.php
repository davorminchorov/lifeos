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

class AddItem extends AbstractTool
{
    protected string $name = 'cycleMenu.addItem';

    protected string $description = 'Add a single item to a specific day of an existing cycle menu. Idempotent on (menu, day_index, title, meal_type).';

    public function schema(JsonSchema $schema): array
    {
        return [
            'cycle_menu_id' => $schema->integer()->description('Cycle menu id (must belong to the authenticated tenant). Required.'),
            'day_index' => $schema->integer()->description('0-based day in the rotation. Required.'),
            'title' => $schema->string()->description('Dish name. Required.'),
            'meal_type' => $schema->string()->description('"breakfast", "lunch", "dinner", "snack", or "other". Required.'),
            'time_of_day' => $schema->string()->description('Optional HH:MM.'),
            'quantity' => $schema->string()->description('Optional free-text serving (e.g. "1 bowl", "250 g").'),
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

        $payload = array_filter([
            'cycle_menu_id' => $menu->id,
            'day_index' => $request->get('day_index'),
            'title' => $request->get('title'),
            'meal_type' => $request->get('meal_type'),
            'time_of_day' => $request->get('time_of_day'),
            'quantity' => $request->get('quantity'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_CREATE,
                payload: $payload,
            );
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }

        return Response::structured([
            'pending_action_id' => $action->id,
            'status' => $action->status,
            'idempotency_key' => $action->idempotency_key,
            'auto_applied' => $action->status === PendingAction::STATUS_APPLIED,
        ]);
    }
}
