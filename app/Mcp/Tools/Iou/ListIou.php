<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Iou;

use App\Mcp\Tools\AbstractTool;
use App\Models\Iou;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ListIou extends AbstractTool
{
    protected string $name = 'iou.list';

    protected string $description = 'List IOU/debt records for the authenticated tenant. Filter by direction (owe vs owed), status, or person.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'direction' => $schema->string()->description('"owe" or "owed".'),
            'status' => $schema->string()->description('Filter by status (e.g. "pending", "partially_paid", "paid").'),
            'person_name' => $schema->string()->description('Match person_name (substring, case-insensitive).'),
            'overdue_only' => $schema->boolean()->description('Only return overdue records (default false).'),
            'limit' => $schema->integer()->description('Max rows (default 100, max 500).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $limit = (int) min(max((int) $request->get('limit', 100), 1), 500);

        $query = Iou::query()->orderBy('due_date');

        $direction = $request->get('direction');
        if ($direction === 'owe') {
            $query->owe();
        } elseif ($direction === 'owed') {
            $query->owed();
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($person = $request->get('person_name')) {
            $query->where('person_name', 'LIKE', '%'.$person.'%');
        }

        if ($request->boolean('overdue_only')) {
            $query->overdue();
        }

        $items = $query->limit($limit)->get([
            'id',
            'type',
            'person_name',
            'amount',
            'amount_paid',
            'currency',
            'transaction_date',
            'due_date',
            'description',
            'status',
            'category',
        ])->map(fn (Iou $i): array => [
            'id' => $i->id,
            'direction' => $i->type,
            'person_name' => $i->person_name,
            'amount' => (float) $i->amount,
            'amount_paid' => (float) $i->amount_paid,
            'remaining' => round((float) $i->amount - (float) $i->amount_paid, 2),
            'currency' => $i->currency,
            'transaction_date' => $i->transaction_date?->toDateString(),
            'due_date' => $i->due_date?->toDateString(),
            'description' => $i->description,
            'status' => $i->status,
            'category' => $i->category,
        ])->all();

        return Response::structured([
            'count' => count($items),
            'limit' => $limit,
            'items' => $items,
        ]);
    }
}
