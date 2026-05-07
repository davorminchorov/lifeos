<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Models\AgentToken;
use Illuminate\Support\Facades\Config;
use RuntimeException;

/**
 * Builds the JSON body sent to the Managed Agents API when creating a session
 * for a given (AgentDefinition, AgentToken) pair. Keeping the builder pure (no
 * HTTP calls) makes Phase 3's tests trivial.
 */
final class AgentSessionConfig
{
    public function __construct(
        public readonly AgentDefinition $definition,
        public readonly AgentToken $token,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'model' => $this->definition->model,
            'fallback_model' => $this->definition->fallbackModel,
            'system_prompt' => $this->definition->systemPrompt,
            'mcp_servers' => $this->resolveMcpServers(),
            'allowed_tools' => $this->definition->allowedTools,
            'max_session_duration_seconds' => $this->definition->maxSessionDurationSeconds,
            'max_tool_calls' => $this->definition->maxToolCalls,
            'metadata' => [
                'agent_slug' => $this->definition->slug,
                'tenant_id' => $this->token->tenant_id,
                'agent_token_id' => $this->token->id,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveMcpServers(): array
    {
        $servers = (array) Config::get('agents.mcp_servers', []);
        $resolved = [];

        foreach ($this->definition->mcpServers as $name) {
            if (! isset($servers[$name])) {
                throw new RuntimeException("MCP server [{$name}] referenced by agent.json is not configured in config/agents.php.");
            }

            $config = $servers[$name];

            if (empty($config['url'])) {
                throw new RuntimeException("MCP server [{$name}] has no url configured.");
            }

            $entry = [
                'name' => $name,
                'url' => (string) $config['url'],
            ];

            if (($config['auth'] ?? null) === 'agent_token') {
                // The plaintext is intentionally not stored anywhere on disk.
                // It is generated fresh each run by AgentTokenIssuer and passed
                // here in-memory; in tests we accept the seed via the request
                // attribute path instead.
                $entry['headers'] = [
                    'Authorization' => 'Bearer '.($config['_plaintext'] ?? ''),
                ];
            }

            $resolved[] = $entry;
        }

        return $resolved;
    }
}
