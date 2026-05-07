<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Subscriptions;

use App\Mcp\Tools\AbstractTool;
use App\Models\Subscription;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ListSubscriptions extends AbstractTool
{
    protected string $name = 'subscriptions.list';

    protected string $description = 'List subscriptions for the authenticated tenant. Filter by status or upcoming renewal window.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status (e.g. "active", "cancelled", "paused").'),
            'due_within_days' => $schema->integer()->description('Only return subscriptions whose next billing falls within this many days.'),
            'category' => $schema->string()->description('Match category (substring, case-insensitive).'),
            'limit' => $schema->integer()->description('Max rows (default 100, max 500).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $limit = (int) min(max((int) $request->get('limit', 100), 1), 500);

        $query = Subscription::query()->orderBy('next_billing_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if (($within = $request->get('due_within_days')) !== null) {
            $query->whereNotNull('next_billing_date')
                ->where('next_billing_date', '<=', now()->addDays((int) $within));
        }

        if ($category = $request->get('category')) {
            $query->where('category', 'LIKE', '%'.$category.'%');
        }

        $items = $query->limit($limit)->get([
            'id',
            'service_name',
            'category',
            'cost',
            'currency',
            'billing_cycle',
            'next_billing_date',
            'status',
            'auto_renewal',
        ])->map(fn (Subscription $s): array => [
            'id' => $s->id,
            'service_name' => $s->service_name,
            'category' => $s->category,
            'cost' => (float) $s->cost,
            'currency' => $s->currency,
            'billing_cycle' => $s->billing_cycle,
            'next_billing_date' => $s->next_billing_date?->toDateString(),
            'status' => $s->status,
            'auto_renewal' => (bool) $s->auto_renewal,
        ])->all();

        return Response::structured([
            'count' => count($items),
            'limit' => $limit,
            'items' => $items,
        ]);
    }
}
