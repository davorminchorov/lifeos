<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Holiday;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryHolidays extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search holidays by country, date range, or name.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'country' => $schema->string()->description('Filter by country code (e.g. US, MK, DE)'),
            'name' => $schema->string()->description('Filter by holiday name'),
            'date_from' => $schema->string()->description('Start date YYYY-MM-DD'),
            'date_to' => $schema->string()->description('End date YYYY-MM-DD'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(Holiday::class);

        $country = $request['country'] ?? null;
        if ($country !== null) {
            $query->where('country', $country);
        }

        $name = $request['name'] ?? null;
        if ($name !== null) {
            $query->where('name', 'LIKE', '%'.$name.'%');
        }

        $dateFrom = $request['date_from'] ?? null;
        if ($dateFrom !== null) {
            $query->where('date', '>=', $dateFrom);
        }

        $dateTo = $request['date_to'] ?? null;
        if ($dateTo !== null) {
            $query->where('date', '<=', $dateTo);
        }

        $totalCount = $query->count();
        $holidays = $query->orderBy('date')->limit(20)->get();

        if ($holidays->isEmpty()) {
            return 'No holidays found matching your criteria.';
        }

        $lines = $holidays->map(
            fn (Holiday $h): string => sprintf(
                '- %s: %s (%s)%s',
                $h->date->format('Y-m-d'),
                $h->name,
                $h->country,
                $h->description ? " — {$h->description}" : '',
            ),
        );

        $showing = $holidays->count();

        return "Found {$totalCount} holidays".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
