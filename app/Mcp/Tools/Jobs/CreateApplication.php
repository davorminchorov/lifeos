<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Jobs;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class CreateApplication extends AbstractTool
{
    protected string $name = 'jobs.createApplication';

    protected string $description = 'Record a job application discovered by the job-search agent. The status defaults to "discovered" — the user explicitly transitions to "applied" when (and if) they actually apply.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'company_name' => $schema->string()->description('Company name. Required.'),
            'job_title' => $schema->string()->description('Job title as posted. Required.'),
            'job_description' => $schema->string()->description('A short summary of the posting (one paragraph max).'),
            'job_url' => $schema->string()->description('Canonical URL to the posting (company career page preferred over board listings).'),
            'location' => $schema->string()->description('City / region as posted.'),
            'remote' => $schema->boolean()->description('Whether the posting is remote.'),
            'salary_min' => $schema->number()->description('Lower bound of stated compensation.'),
            'salary_max' => $schema->number()->description('Upper bound of stated compensation.'),
            'currency' => $schema->string()->description('ISO 4217. Defaults to MKD.'),
            'status' => $schema->string()->description('"discovered" (default) or "shortlisted" when fit is high.'),
            'source' => $schema->string()->description('Where the agent found the posting (e.g. "linkedin", "company_site", "recruiter_email").'),
            'priority' => $schema->integer()->description('Optional 1-5 priority. The agent should set this only when fit is very high.'),
            'contact_name' => $schema->string()->description('Recruiter or hiring contact, if present.'),
            'contact_email' => $schema->string()->description('Contact email, if present.'),
            'notes' => $schema->string()->description('One-line rationale: why this posting passed the filters.'),
            'source_email_id' => $schema->string()->description('Optional Gmail message id when found via email. Used in idempotency.'),
            'source_file_id' => $schema->string()->description('Optional Drive file id when found via a saved listing. Used in idempotency.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $payload = array_filter([
            'company_name' => $request->get('company_name'),
            'job_title' => $request->get('job_title'),
            'job_description' => $request->get('job_description'),
            'job_url' => $request->get('job_url'),
            'location' => $request->get('location'),
            'remote' => $request->get('remote'),
            'salary_min' => $request->get('salary_min'),
            'salary_max' => $request->get('salary_max'),
            'currency' => $request->get('currency', 'MKD'),
            'status' => $request->get('status', 'discovered'),
            'source' => $request->get('source'),
            'priority' => $request->get('priority'),
            'contact_name' => $request->get('contact_name'),
            'contact_email' => $request->get('contact_email'),
            'notes' => $request->get('notes'),
            'source_email_id' => $request->get('source_email_id'),
            'source_file_id' => $request->get('source_file_id'),
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
