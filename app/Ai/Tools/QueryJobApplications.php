<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\JobApplication;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class QueryJobApplications extends TenantScopedTool
{
    public function description(): string
    {
        return 'Search and filter job applications by company, status, or date range.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'company' => $schema->string()->description('Filter by company name'),
            'status' => $schema->string()->description('Filter by status: wishlist, applied, screening, interview, assessment, offer, accepted, rejected, withdrawn, archived'),
            'date_from' => $schema->string()->description('Start date in YYYY-MM-DD format'),
            'date_to' => $schema->string()->description('End date in YYYY-MM-DD format'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = $this->scopedQuery(JobApplication::class);

        $company = $request->get('company');
        if ($company !== null) {
            $query->where('company_name', 'LIKE', '%'.$company.'%');
        }

        $status = $request->get('status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $dateFrom = $request->get('date_from');
        if ($dateFrom !== null) {
            $query->where('applied_at', '>=', $dateFrom);
        }

        $dateTo = $request->get('date_to');
        if ($dateTo !== null) {
            $query->where('applied_at', '<=', $dateTo);
        }

        $totalCount = $query->count();
        $applications = $query->orderByDesc('applied_at')->limit(20)->get();

        if ($applications->isEmpty()) {
            return 'No job applications found matching your criteria.';
        }

        $lines = $applications->map(
            fn (JobApplication $a): string => sprintf(
                '- %s at %s [%s] — applied %s%s',
                $a->job_title,
                $a->company_name,
                $a->status instanceof \BackedEnum ? $a->status->value : $a->status,
                $a->applied_at?->format('Y-m-d') ?? 'N/A',
                $a->location ? " ({$a->location})" : '',
            ),
        );

        $showing = $applications->count();

        return "Found {$totalCount} job applications".($totalCount > $showing ? " (showing {$showing})" : '').":\n"
            .$lines->implode("\n");
    }
}
