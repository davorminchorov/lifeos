<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryWarranties extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter warranties by product, brand, status, or expiration.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_name' => $schema->string()->description('Filter by product name'),
            'brand' => $schema->string()->description('Filter by brand'),
            'current_status' => $schema->string()->description('Filter by status: active, expired, claimed, transferred'),
            'expiring_within_days' => $schema->integer()->description('Show warranties expiring within N days'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Warranty::class);

        $product = $request['product_name'] ?? null;
        if ($product !== null) {
            $query->where('product_name', 'LIKE', '%'.$product.'%');
        }

        $brand = $request['brand'] ?? null;
        if ($brand !== null) {
            $query->where('brand', 'LIKE', '%'.$brand.'%');
        }

        $status = $request['current_status'] ?? null;
        if ($status !== null) {
            $query->where('current_status', $status);
        }

        $expiringDays = $request['expiring_within_days'] ?? null;
        if ($expiringDays !== null) {
            $now = CarbonImmutable::now();
            $query->whereBetween('warranty_expiration_date', [
                $now->toDateString(),
                $now->addDays((int) $expiringDays)->toDateString(),
            ]);
        }

        $totalCount = $query->count();
        $warranties = $query->orderBy('warranty_expiration_date')->limit(20)->get();

        if ($warranties->isEmpty()) {
            return 'No warranties found matching your criteria.';
        }

        $lines = $warranties->map(
            fn (Warranty $w): string => sprintf(
                '- %s (%s %s): purchased %s for %s, expires %s (%d days left) [%s]',
                $w->product_name,
                $w->brand ?? 'N/A',
                $w->model ?? '',
                $w->purchase_date->format('Y-m-d'),
                $w->purchase_price ? number_format((float) $w->purchase_price, 2) : 'N/A',
                $w->warranty_expiration_date->format('Y-m-d'),
                max(0, $w->days_until_expiration),
                $w->current_status,
            ),
        );

        $showing = $warranties->count();

        return "Found {$totalCount} warranties".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
