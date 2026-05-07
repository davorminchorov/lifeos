<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Contracts;

use App\Mcp\Tools\AbstractTool;
use App\Models\Contract;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ListContracts extends AbstractTool
{
    protected string $name = 'contracts.list';

    protected string $description = 'List contracts for the authenticated tenant. Optionally filter to those expiring within N days.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status (e.g. "active", "terminated", "expired").'),
            'expiring_within_days' => $schema->integer()->description('Only return contracts expiring within this many days.'),
            'contract_type' => $schema->string()->description('Filter by contract_type.'),
            'limit' => $schema->integer()->description('Max rows (default 100, max 500).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $limit = (int) min(max((int) $request->get('limit', 100), 1), 500);

        $query = Contract::query()->orderBy('end_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('contract_type')) {
            $query->where('contract_type', $type);
        }

        if (($within = $request->get('expiring_within_days')) !== null) {
            $query->expiringSoon((int) $within);
        }

        $items = $query->limit($limit)->get([
            'id',
            'title',
            'counterparty',
            'contract_type',
            'start_date',
            'end_date',
            'notice_period_days',
            'auto_renewal',
            'contract_value',
            'status',
        ])->map(fn (Contract $c): array => [
            'id' => $c->id,
            'title' => $c->title,
            'counterparty' => $c->counterparty,
            'contract_type' => $c->contract_type,
            'start_date' => $c->start_date?->toDateString(),
            'end_date' => $c->end_date?->toDateString(),
            'notice_period_days' => $c->notice_period_days,
            'auto_renewal' => (bool) $c->auto_renewal,
            'contract_value' => $c->contract_value !== null ? (float) $c->contract_value : null,
            'status' => $c->status,
            'days_until_expiration' => $c->end_date
                ? (int) round(now()->startOfDay()->diffInDays($c->end_date->startOfDay(), false))
                : null,
        ])->all();

        return Response::structured([
            'count' => count($items),
            'limit' => $limit,
            'items' => $items,
        ]);
    }
}
