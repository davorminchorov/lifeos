<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Digest;

use App\Mcp\Tools\AbstractTool;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class Send extends AbstractTool
{
    protected string $name = 'digest.send';

    protected string $description = 'Queue a weekly digest email for the authenticated tenant. The agent composes the body; the server queues it as a pending action; on approval (or auto-apply for tenants that have opted in), the email is sent and a digest_logs row is recorded. Idempotent on (tenant, week_starts_on).';

    public function schema(JsonSchema $schema): array
    {
        return [
            'week_starts_on' => $schema->string()->description('YYYY-MM-DD of the Monday that starts the digest week. Required. Idempotency anchor.'),
            'subject' => $schema->string()->description('Email subject line. Required.'),
            'body_text' => $schema->string()->description('Plaintext body (Markdown is fine; renders as preformatted text in the email). Required.'),
            'body_html' => $schema->string()->description('Optional rendered HTML body. When supplied, used in place of body_text in the email view.'),
            'recipient_email' => $schema->string()->description('Optional override of the recipient. Defaults to the bound user\'s email.'),
            'structured_summary' => $schema->object()->description('Optional machine-readable highlights (counts, totals, etc.) for archive / future UI use.'),
        ];
    }

    public function handle(Request $request, PendingActionApplier $applier): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $payload = array_filter([
            'week_starts_on' => $request->get('week_starts_on'),
            'subject' => $request->get('subject'),
            'body_text' => $request->get('body_text'),
            'body_html' => $request->get('body_html'),
            'recipient_email' => $request->get('recipient_email'),
            'structured_summary' => $request->get('structured_summary'),
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
