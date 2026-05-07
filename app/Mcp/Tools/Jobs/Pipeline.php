<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Jobs;

use App\Mcp\Tools\AbstractTool;
use App\Models\JobApplication;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class Pipeline extends AbstractTool
{
    protected string $name = 'jobs.pipeline';

    protected string $description = 'Return the job-applications pipeline for the authenticated tenant: applications grouped by status with counts and a flat list.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'include_archived' => $schema->boolean()->description('Include archived applications (default false).'),
            'remote_only' => $schema->boolean()->description('Only return remote roles (default false).'),
            'limit' => $schema->integer()->description('Max rows (default 200, max 500).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $limit = (int) min(max((int) $request->get('limit', 200), 1), 500);

        $query = JobApplication::query()->orderByDesc('applied_at');

        if (! $request->boolean('include_archived')) {
            $query->whereNull('archived_at');
        }

        if ($request->boolean('remote_only')) {
            $query->where('remote', true);
        }

        $apps = $query->limit($limit)->get([
            'id',
            'company_name',
            'job_title',
            'location',
            'remote',
            'salary_min',
            'salary_max',
            'currency',
            'status',
            'source',
            'priority',
            'applied_at',
            'next_action_at',
            'archived_at',
        ]);

        $items = $apps->map(fn (JobApplication $a): array => [
            'id' => $a->id,
            'company_name' => $a->company_name,
            'job_title' => $a->job_title,
            'location' => $a->location,
            'remote' => (bool) $a->remote,
            'salary_min' => $a->salary_min !== null ? (float) $a->salary_min : null,
            'salary_max' => $a->salary_max !== null ? (float) $a->salary_max : null,
            'currency' => $a->currency,
            'status' => is_object($a->status) ? $a->status->value : $a->status,
            'source' => is_object($a->source) ? $a->source?->value : $a->source,
            'priority' => $a->priority,
            'applied_at' => $a->applied_at?->toDateString(),
            'next_action_at' => $a->next_action_at?->toIso8601String(),
            'archived' => $a->archived_at !== null,
        ])->all();

        $countsByStatus = collect($items)
            ->groupBy('status')
            ->map(fn ($g) => count($g))
            ->all();

        return Response::structured([
            'count' => count($items),
            'counts_by_status' => $countsByStatus,
            'items' => $items,
        ]);
    }
}
