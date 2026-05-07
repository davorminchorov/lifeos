<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use App\Models\AgentRun;
use App\Models\AgentRunEvent;
use App\Models\AgentToken;
use App\Services\Agents\AgentDefinition;
use App\Services\Agents\AgentRunRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentRunRecorderTest extends TestCase
{
    use RefreshDatabase;

    private function definition(): AgentDefinition
    {
        return AgentDefinition::fromArray([
            'slug' => 'email-ingestion',
            'model' => 'claude-opus-4-7',
            'mcp_servers' => ['lifeos'],
            'allowed_tools' => ['expenses.create'],
            'feature_flag' => 'agents.email_ingestion.enabled',
        ], 'prompt');
    }

    private function token(): AgentToken
    {
        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();
        [$token] = AgentToken::issue($user, $tenant, 'phpunit', ['*']);

        return $token;
    }

    public function test_start_creates_running_run_with_started_at(): void
    {
        $token = $this->token();
        $recorder = app(AgentRunRecorder::class);

        $run = $recorder->start($this->definition(), $token);

        $this->assertSame(AgentRun::STATUS_RUNNING, $run->status);
        $this->assertSame('email-ingestion', $run->agent_slug);
        $this->assertSame($token->id, $run->agent_token_id);
        $this->assertNotNull($run->started_at);
    }

    public function test_record_event_persists_row_and_updates_counters(): void
    {
        $token = $this->token();
        $recorder = app(AgentRunRecorder::class);
        $run = $recorder->start($this->definition(), $token);

        $recorder->recordEvent($run, [
            'type' => AgentRunEvent::TYPE_TOOL_CALL,
            'name' => 'expenses.create',
        ], 1);

        $recorder->recordEvent($run, [
            'type' => AgentRunEvent::TYPE_TOOL_RESULT,
            'structured_content' => ['pending_action_id' => 42, 'status' => 'pending'],
        ], 2);

        $recorder->recordEvent($run, [
            'type' => AgentRunEvent::TYPE_TEXT,
            'usage' => ['input_tokens' => 100, 'output_tokens' => 50],
        ], 3);

        $run->refresh();

        $this->assertSame(3, $run->events()->count());
        $this->assertSame(['expenses.create' => 1], $run->tools_called);
        $this->assertSame(1, $run->pending_actions_created);
        $this->assertSame(100, (int) $run->tokens_in);
        $this->assertSame(50, (int) $run->tokens_out);
    }

    public function test_complete_marks_run_completed(): void
    {
        $token = $this->token();
        $recorder = app(AgentRunRecorder::class);
        $run = $recorder->start($this->definition(), $token);

        $recorder->complete($run);

        $this->assertSame(AgentRun::STATUS_COMPLETED, $run->refresh()->status);
        $this->assertNotNull($run->ended_at);
    }

    public function test_fail_records_error_and_ends_run(): void
    {
        $token = $this->token();
        $recorder = app(AgentRunRecorder::class);
        $run = $recorder->start($this->definition(), $token);

        $recorder->fail($run, new \RuntimeException('upstream timeout'));

        $run->refresh();
        $this->assertSame(AgentRun::STATUS_FAILED, $run->status);
        $this->assertSame('upstream timeout', $run->error);
        $this->assertNotNull($run->ended_at);
    }
}
