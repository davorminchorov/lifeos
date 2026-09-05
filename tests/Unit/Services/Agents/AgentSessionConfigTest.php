<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Models\AgentToken;
use App\Services\Agents\AgentDefinition;
use App\Services\Agents\AgentSessionConfig;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class AgentSessionConfigTest extends TestCase
{
    public function test_agent_token_is_attached_to_a_valid_https_server(): void
    {
        Config::set('agents.mcp_servers', [
            'lifeos' => [
                'url' => 'https://lifeos.test/mcp/lifeos',
                'https_only' => true,
                'auth' => 'agent_token',
                '_plaintext' => 'secret',
            ],
        ]);

        $servers = $this->sessionConfig()->toArray()['mcp_servers'];

        $this->assertSame('https://lifeos.test/mcp/lifeos', $servers[0]['url']);
        $this->assertSame('Bearer secret', $servers[0]['headers']['Authorization']);
    }

    #[DataProvider('invalidAgentTokenUrlProvider')]
    public function test_agent_token_server_rejects_non_https_urls(string $url): void
    {
        Config::set('agents.mcp_servers', [
            'lifeos' => [
                'url' => $url,
                'https_only' => true,
                'auth' => 'agent_token',
                '_plaintext' => 'secret',
            ],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MCP server [lifeos] must use a valid HTTPS URL.');

        $this->sessionConfig()->toArray();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidAgentTokenUrlProvider(): array
    {
        return [
            'http' => ['http://lifeos.test/mcp/lifeos'],
            'malformed' => ['not-a-url'],
        ];
    }

    private function sessionConfig(): AgentSessionConfig
    {
        $definition = new AgentDefinition(
            slug: 'test-agent',
            model: 'claude-opus-4-7',
            fallbackModel: null,
            systemPrompt: 'Test prompt',
            mcpServers: ['lifeos'],
            allowedTools: [],
            maxSessionDurationSeconds: 600,
            maxToolCalls: 10,
            featureFlag: 'agents.test.enabled',
            schedule: null,
        );
        $token = new AgentToken;
        $token->forceFill(['id' => 123, 'tenant_id' => 456]);

        return new AgentSessionConfig($definition, $token);
    }
}
