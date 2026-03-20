<?php

namespace App\Ai\Tools\JobApplications;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\JobApplication;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class ListJobApplicationsTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'List job applications. Use when the user asks about their applications, job search status, or pipeline.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status: wishlist, applied, screening, interview, assessment, offer, accepted, rejected, withdrawn'),
            'active_only' => $schema->boolean()->description('Only show non-archived applications (default: true)'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = JobApplication::where('tenant_id', $this->tenantId())
            ->orderBy('updated_at', 'desc');

        if ($request['active_only'] ?? true) {
            $query->active();
        }

        if ($request['status'] ?? null) {
            $query->where('status', $request['status']);
        }

        $applications = $query->limit(20)->get();

        if ($applications->isEmpty()) {
            return 'No job applications found.';
        }

        $byStatus = $applications->groupBy(fn ($app) => $app->status->label());
        $lines = ["Job Applications ({$applications->count()} total):"];

        foreach ($byStatus as $status => $apps) {
            $lines[] = "\n{$status} ({$apps->count()}):";
            foreach ($apps as $app) {
                $days = $app->days_since_applied ? " ({$app->days_since_applied}d ago)" : '';
                $salary = $app->formatted_salary_range ? " | {$app->formatted_salary_range}" : '';
                $lines[] = "- {$app->company_name} — {$app->job_title}{$days}{$salary}";
            }
        }

        return implode("\n", $lines);
    }
}
