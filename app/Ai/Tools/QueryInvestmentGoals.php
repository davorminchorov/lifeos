<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\InvestmentGoal;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryInvestmentGoals extends TenantScopedTool
{
    public function description(): string
    {
        return 'Query investment goals by title or status.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Filter by goal title'),
            'status' => $schema->string()->description('Filter by status: active, achieved, paused, cancelled'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(InvestmentGoal::class);

        $title = $request['title'] ?? null;
        if ($title !== null) {
            $query->where('title', 'LIKE', '%'.$title.'%');
        }

        $status = $request['status'] ?? null;
        if ($status !== null) {
            $query->where('status', $status);
        }

        $totalCount = $query->count();
        $goals = $query->orderBy('target_date')->limit(20)->get();

        if ($goals->isEmpty()) {
            return 'No investment goals found matching your criteria.';
        }

        $lines = $goals->map(
            fn (InvestmentGoal $g): string => sprintf(
                '- %s: %s/%s (%s%%) target by %s [%s]',
                $g->title,
                number_format((float) $g->current_progress, 2),
                number_format((float) $g->target_amount, 2),
                $g->progress_percentage,
                $g->target_date?->format('Y-m-d') ?? 'no date',
                $g->status,
            ),
        );

        $showing = $goals->count();

        return "Found {$totalCount} investment goals".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
