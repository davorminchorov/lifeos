<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Warranties;

use App\Mcp\Tools\AbstractTool;
use App\Models\Warranty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ListWarranties extends AbstractTool
{
    protected string $name = 'warranties.list';

    protected string $description = 'List warranties for the authenticated tenant. Optionally filter to those expiring within N days.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'current_status' => $schema->string()->description('Filter by current_status (e.g. "active", "expired", "claimed").'),
            'expiring_within_days' => $schema->integer()->description('Only return warranties whose coverage ends within this many days.'),
            'brand' => $schema->string()->description('Match brand (substring, case-insensitive).'),
            'limit' => $schema->integer()->description('Max rows (default 100, max 500).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $limit = (int) min(max((int) $request->get('limit', 100), 1), 500);

        $query = Warranty::query()->orderBy('warranty_expiration_date');

        if ($status = $request->get('current_status')) {
            $query->where('current_status', $status);
        }

        if ($brand = $request->get('brand')) {
            $query->where('brand', 'LIKE', '%'.$brand.'%');
        }

        if (($within = $request->get('expiring_within_days')) !== null) {
            $query->expiringSoon((int) $within);
        }

        $items = $query->limit($limit)->get([
            'id',
            'product_name',
            'brand',
            'model',
            'serial_number',
            'purchase_date',
            'purchase_price',
            'retailer',
            'warranty_expiration_date',
            'warranty_type',
            'current_status',
        ])->map(fn (Warranty $w): array => [
            'id' => $w->id,
            'product_name' => $w->product_name,
            'brand' => $w->brand,
            'model' => $w->model,
            'serial_number' => $w->serial_number,
            'purchase_date' => $w->purchase_date?->toDateString(),
            'purchase_price' => $w->purchase_price !== null ? (float) $w->purchase_price : null,
            'retailer' => $w->retailer,
            'warranty_expiration_date' => $w->warranty_expiration_date?->toDateString(),
            'warranty_type' => $w->warranty_type,
            'current_status' => $w->current_status,
            'days_until_expiration' => $w->warranty_expiration_date
                ? (int) round(now()->startOfDay()->diffInDays($w->warranty_expiration_date->startOfDay(), false))
                : null,
        ])->all();

        return Response::structured([
            'count' => count($items),
            'limit' => $limit,
            'items' => $items,
        ]);
    }
}
