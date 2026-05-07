<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Jobs;

use App\Mcp\Tools\AbstractTool;
use App\Models\JobApplication;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class UpdateJobStatus extends AbstractTool
{
    protected string $name = 'jobs.updateStatus';

    protected string $description = 'Move a job application to a new pipeline status (and optionally schedule the next action). Queued as a pending action.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'job_application_id' => $schema->integer()->description('Job application id (must belong to the authenticated tenant). Required.'),
            'status' => $schema->string()->description('New status (one of the pipeline statuses LifeOS uses). Required.'),
            'next_action_at' => $schema->string()->description('ISO 8601 datetime to follow up.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id for idempotency.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $jobId = (int) $request->get('job_application_id', 0);

        if ($jobId <= 0) {
            return Response::error('job_application_id is required.');
        }

        $application = JobApplication::query()->find($jobId);

        if ($application === null) {
            return Response::error("Job application [{$jobId}] not found in this tenant.");
        }

        $payload = array_filter([
            'job_application_id' => $application->id,
            'status' => $request->get('status'),
            'next_action_at' => $request->get('next_action_at'),
            'source_email_id' => $request->get('source_email_id'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_UPDATE,
                payload: $payload,
            );
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }

        return Response::structured([
            'pending_action_id' => $action->id,
            'status' => $action->status,
            'idempotency_key' => $action->idempotency_key,
            'auto_applied' => $action->status === PendingAction::STATUS_APPLIED,
        ]);
    }
}
