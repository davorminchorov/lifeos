<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use App\Services\Agents\ManagedAgentsClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ManagedAgentsClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('agents.anthropic', [
            'api_key' => 'test-key',
            'base_url' => 'https://api.anthropic.test',
            'beta' => 'managed-agents-2026-04-01',
            'version' => '2023-06-01',
            'connect_timeout' => 5,
            'request_timeout' => 30,
        ]);
    }

    public function test_create_session_posts_with_beta_header(): void
    {
        Http::fake([
            'https://api.anthropic.test/v1/agents/sessions' => Http::response([
                'id' => 'sess_123',
                'status' => 'running',
            ], 201),
        ]);

        $session = (new ManagedAgentsClient)->createSession([
            'model' => 'claude-opus-4-7',
            'system_prompt' => 'p',
            'mcp_servers' => [],
            'allowed_tools' => [],
        ]);

        $this->assertSame('sess_123', $session['id']);

        Http::assertSent(function (Request $request): bool {
            return $request->hasHeader('anthropic-beta', 'managed-agents-2026-04-01')
                && $request->hasHeader('x-api-key', 'test-key')
                && $request->method() === 'POST';
        });
    }

    public function test_create_session_throws_on_failure(): void
    {
        Http::fake([
            'https://api.anthropic.test/v1/agents/sessions' => Http::response('boom', 500),
        ]);

        $this->expectException(RuntimeException::class);
        (new ManagedAgentsClient)->createSession(['model' => 'x']);
    }

    public function test_stream_events_yields_each_json_line(): void
    {
        $body = implode("\n", [
            '{"type":"tool_call","name":"expenses.create"}',
            'data: {"type":"tool_result","structured_content":{"pending_action_id":1}}',
            '',
            ': comment line — ignored',
            '{"type":"text","usage":{"input_tokens":10,"output_tokens":5}}',
        ]);

        Http::fake([
            'https://api.anthropic.test/v1/agents/sessions/sess_123/events' => Http::response($body, 200),
        ]);

        $events = iterator_to_array((new ManagedAgentsClient)->streamEvents('sess_123'));

        $this->assertCount(3, $events);
        $this->assertSame('tool_call', $events[0]['type']);
        $this->assertSame('tool_result', $events[1]['type']);
        $this->assertSame(1, $events[1]['structured_content']['pending_action_id']);
        $this->assertSame(10, $events[2]['usage']['input_tokens']);
    }

    public function test_no_api_key_raises(): void
    {
        Config::set('agents.anthropic.api_key', '');

        $this->expectException(RuntimeException::class);
        (new ManagedAgentsClient)->createSession([]);
    }
}
