<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Bills;

use App\Mcp\Tools\AbstractTool;
use App\Models\UtilityBill;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class UpcomingBills extends AbstractTool
{
    protected string $name = 'bills.upcoming';

    protected string $description = 'List utility bills due soon for the authenticated tenant.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'within_days' => $schema->integer()->description('Show bills due within this many days (default 30).'),
            'utility_type' => $schema->string()->description('Filter by utility type (e.g. "electricity", "water").'),
            'include_overdue' => $schema->boolean()->description('Include overdue bills regardless of due date (default true).'),
            'limit' => $schema->integer()->description('Max rows (default 100, max 500).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $within = (int) ($request->get('within_days') ?? 30);
        $includeOverdue = $request->boolean('include_overdue', true);
        $limit = (int) min(max((int) $request->get('limit', 100), 1), 500);

        $query = UtilityBill::query()->orderBy('due_date');

        if ($type = $request->get('utility_type')) {
            $query->where('utility_type', $type);
        }

        $query->where(function ($q) use ($within, $includeOverdue): void {
            $q->where(function ($qq) use ($within): void {
                $qq->where('payment_status', 'pending')
                    ->where('due_date', '<=', now()->addDays($within));
            });

            if ($includeOverdue) {
                $q->orWhere('payment_status', 'overdue')
                    ->orWhere(function ($qq): void {
                        $qq->where('payment_status', 'pending')
                            ->where('due_date', '<', now());
                    });
            }
        });

        $items = $query->limit($limit)->get([
            'id',
            'utility_type',
            'service_provider',
            'bill_amount',
            'currency',
            'due_date',
            'payment_status',
            'account_number',
        ])->map(fn (UtilityBill $b): array => [
            'id' => $b->id,
            'utility_type' => $b->utility_type,
            'service_provider' => $b->service_provider,
            'bill_amount' => (float) $b->bill_amount,
            'currency' => $b->currency,
            'due_date' => $b->due_date?->toDateString(),
            'payment_status' => $b->payment_status,
            'account_number' => $b->account_number,
            'days_until_due' => $b->due_date
                ? (int) round(now()->startOfDay()->diffInDays($b->due_date->startOfDay(), false))
                : null,
        ])->all();

        return Response::structured([
            'count' => count($items),
            'within_days' => $within,
            'items' => $items,
        ]);
    }
}
