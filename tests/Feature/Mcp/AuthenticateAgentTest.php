<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Models\AgentToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticateAgentTest extends TestCase
{
    use RefreshDatabase;

    private function ping(array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/mcp/lifeos', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'ping',
            'params' => (object) [],
        ], $headers);
    }

    public function test_request_without_bearer_token_is_rejected(): void
    {
        $this->ping()->assertStatus(401)->assertJsonPath('error', 'Missing bearer token.');
    }

    public function test_request_with_unknown_token_is_rejected(): void
    {
        $this->ping(['Authorization' => 'Bearer lifeos_agent_unknown'])
            ->assertStatus(401)
            ->assertJsonPath('error', 'Invalid or expired token.');
    }

    public function test_request_with_revoked_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        [$token, $plain] = AgentToken::issue($user, $tenant, 'test', ['*']);
        $token->forceFill(['revoked_at' => now()])->save();

        $this->ping(['Authorization' => "Bearer {$plain}"])
            ->assertStatus(401)
            ->assertJsonPath('error', 'Invalid or expired token.');
    }

    public function test_request_with_active_token_is_authenticated_and_pings_back(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['owner_id' => $user->id]);
        [, $plain] = AgentToken::issue($user, $tenant, 'test', ['*']);

        $response = $this->ping(['Authorization' => "Bearer {$plain}"]);

        // Ping is a built-in MCP method; a 200 with a JSON-RPC result confirms the
        // route + middleware accepted the token and dispatched to the server.
        $response->assertStatus(200);
        $response->assertJsonPath('jsonrpc', '2.0');
        $response->assertJsonPath('id', 1);
    }
}
