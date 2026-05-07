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

class AddInterview extends AbstractTool
{
    protected string $name = 'jobs.addInterview';

    protected string $description = 'Schedule an interview against an existing job application. Queued as a pending action.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'job_application_id' => $schema->integer()->description('Job application id (must belong to the authenticated tenant). Required.'),
            'scheduled_at' => $schema->string()->description('ISO 8601 datetime. Required.'),
            'interview_type' => $schema->string()->description('"phone", "onsite", "video", "technical", etc.'),
            'interviewer_name' => $schema->string()->description('Name of the interviewer (or recruiter contact).'),
            'location' => $schema->string()->description('Address or video link.'),
            'notes' => $schema->string()->description('Free-text notes from the email.'),
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
            'scheduled_at' => $request->get('scheduled_at'),
            'interview_type' => $request->get('interview_type'),
            'interviewer_name' => $request->get('interviewer_name'),
            'location' => $request->get('location'),
            'notes' => $request->get('notes'),
            'source_email_id' => $request->get('source_email_id'),
        ], static fn ($v) => $v !== null);

        try {
            $action = $applier->record(
                token: $this->agentToken(),
                tool: $this->name(),
                action: PendingAction::ACTION_CREATE,
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
