<?php

declare(strict_types=1);

namespace App\Mcp\Tools\CycleMenu;

use App\Mcp\Tools\AbstractTool;
use App\Models\CycleMenu;
use App\Services\CycleMenu\CycleMenuService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ShoppingList extends AbstractTool
{
    protected string $name = 'cycleMenu.shoppingList';

    protected string $description = 'Aggregate the items scheduled across the next N days (default 7) of the active cycle menu into a structured shopping list. Read-only. Free-text quantities are returned as a list rather than summed because the schema doesn\'t carry units.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'cycle_menu_id' => $schema->integer()->description('Cycle menu id. Optional — defaults to the tenant\'s active menu.'),
            'window_days' => $schema->integer()->description('How many days forward to aggregate. Default 7, max 30.'),
        ];
    }

    public function handle(Request $request, CycleMenuService $service): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $window = (int) min(max((int) ($request->get('window_days') ?? 7), 1), 30);

        $menu = null;
        $menuId = (int) $request->get('cycle_menu_id', 0);

        if ($menuId > 0) {
            $menu = CycleMenu::query()->find($menuId);

            if ($menu === null) {
                return Response::error("Cycle menu [{$menuId}] not found in this tenant.");
            }
        } else {
            $menu = CycleMenu::query()->where('is_active', true)->orderByDesc('id')->first();

            if ($menu === null) {
                return Response::structured([
                    'menu' => null,
                    'window_days' => $window,
                    'items' => [],
                ]);
            }
        }

        $items = $service->shoppingList($menu, $window);

        return Response::structured([
            'menu' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'cycle_length_days' => (int) $menu->cycle_length_days,
            ],
            'window_days' => $window,
            'item_count' => count($items),
            'items' => $items,
        ]);
    }
}
