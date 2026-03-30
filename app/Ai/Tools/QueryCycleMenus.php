<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\CycleMenu;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryCycleMenus extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter cycle menus by name, active status, or date range.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Filter by menu name'),
            'is_active' => $schema->boolean()->description('Filter by active status'),
            'date_from' => $schema->string()->description('Start date YYYY-MM-DD for menu start date'),
            'date_to' => $schema->string()->description('End date YYYY-MM-DD for menu start date'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(CycleMenu::class)->with('days.items');

        $name = $request['name'] ?? null;
        if ($name !== null) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        $isActive = $request['is_active'] ?? null;
        if ($isActive !== null) {
            $query->where('is_active', (bool) $isActive);
        }

        $dateFrom = $request['date_from'] ?? null;
        if ($dateFrom !== null) {
            $query->where('starts_on', '>=', $dateFrom);
        }

        $dateTo = $request['date_to'] ?? null;
        if ($dateTo !== null) {
            $query->where('starts_on', '<=', $dateTo);
        }

        $totalCount = $query->count();
        $menus = $query->orderByDesc('starts_on')->limit(20)->get();

        if ($menus->isEmpty()) {
            return 'No cycle menus found matching your criteria.';
        }

        $lines = $menus->map(function (CycleMenu $m): string {
            $dayCount = $m->days->count();
            $itemCount = $m->days->sum(fn ($d) => $d->items->count());
            $status = $m->is_active ? 'active' : 'inactive';

            return sprintf(
                '- %s (%d-day cycle, starts %s, %s) — %d days, %d items',
                $m->name,
                $m->cycle_length_days,
                $m->starts_on->format('Y-m-d'),
                $status,
                $dayCount,
                $itemCount,
            );
        });

        $showing = $menus->count();

        return "Found {$totalCount} cycle menus".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
