<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Models\AgentRun;
use App\Models\AgentRunEvent;
use App\Models\AgentToken;
use App\Models\PendingAction;
use Illuminate\Support\Facades\DB;
use Throwable;

class AgentRunRecorder
{
    public function start(AgentDefinition $definition, AgentToken $token, ?string $sessionId = null): AgentRun
    {
        return AgentRun::create([
            'tenant_id' => $token->tenant_id,
            'user_id' => $token->user_id,
            'agent_token_id' => $token->id,
            'agent_slug' => $definition->slug,
            'session_id' => $sessionId,
            'model' => $definition->model,
            'status' => AgentRun::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    public function setSession(AgentRun $run, string $sessionId): void
    {
        $run->forceFill(['session_id' => $sessionId])->save();
    }

    /**
     * Apply a single streamed event to an in-progress run.
     *
     * - Persists the event for audit (in `agent_run_events`).
     * - Increments tools_called counts and pending_actions_created where the
     *   event payload makes that obvious.
     * - Aggregates token usage if reported.
     *
     * @param  array<string, mixed>  $event
     */
    public function recordEvent(AgentRun $run, array $event, int $sequence): AgentRunEvent
    {
        $type = (string) ($event['type'] ?? AgentRunEvent::TYPE_SYSTEM);

        return DB::transaction(function () use ($run, $event, $sequence, $type): AgentRunEvent {
            $row = AgentRunEvent::create([
                'agent_run_id' => $run->id,
                'sequence' => $sequence,
                'type' => $type,
                'payload' => $event,
                'occurred_at' => now(),
            ]);

            $tools = (array) ($run->tools_called ?? []);

            switch ($type) {
                case AgentRunEvent::TYPE_TOOL_CALL:
                    $name = (string) ($event['name'] ?? $event['tool'] ?? '');
                    if ($name !== '') {
                        $tools[$name] = ($tools[$name] ?? 0) + 1;
                    }
                    break;

                case AgentRunEvent::TYPE_TOOL_RESULT:
                    $structured = $event['structured_content'] ?? $event['structuredContent'] ?? null;
                    if (is_array($structured) && isset($structured['pending_action_id'])) {
                        $run->pending_actions_created = (int) $run->pending_actions_created + 1;
                    }
                    break;
            }

            $usage = $event['usage'] ?? null;
            if (is_array($usage)) {
                $run->tokens_in = (int) $run->tokens_in + (int) ($usage['input_tokens'] ?? 0);
                $run->tokens_out = (int) $run->tokens_out + (int) ($usage['output_tokens'] ?? 0);
            }

            $run->tools_called = $tools;
            $run->save();

            return $row;
        });
    }

    public function complete(AgentRun $run): void
    {
        if ($run->status === AgentRun::STATUS_RUNNING) {
            $run->forceFill([
                'status' => AgentRun::STATUS_COMPLETED,
                'ended_at' => now(),
                'pending_actions_created' => $this->countPendingActionsForSession($run),
            ])->save();
        }
    }

    public function fail(AgentRun $run, Throwable $error): void
    {
        $run->forceFill([
            'status' => AgentRun::STATUS_FAILED,
            'ended_at' => now(),
            'error' => $error->getMessage(),
            'pending_actions_created' => $this->countPendingActionsForSession($run),
        ])->save();
    }

    public function cancel(AgentRun $run): void
    {
        $run->forceFill([
            'status' => AgentRun::STATUS_CANCELLED,
            'ended_at' => now(),
        ])->save();
    }

    /**
     * Authoritative count from pending_actions, not from streamed events. The
     * applier writes pending_actions atomically; counting them at completion
     * keeps the run summary accurate even if a stream event was dropped.
     */
    private function countPendingActionsForSession(AgentRun $run): int
    {
        if ($run->session_id === null) {
            return $run->pending_actions_created;
        }

        return PendingAction::query()
            ->where('tenant_id', $run->tenant_id)
            ->where('session_id', $run->session_id)
            ->count();
    }
}
