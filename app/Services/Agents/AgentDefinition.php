<?php

declare(strict_types=1);

namespace App\Services\Agents;

use InvalidArgumentException;

/**
 * Immutable value object loaded from agents/{slug}/agent.json. The schema is
 * intentionally narrow — the registry validates required keys at load time so
 * downstream consumers can rely on every accessor returning a non-null value.
 */
final class AgentDefinition
{
    /**
     * @param  array<int, string>  $mcpServers
     * @param  array<int, string>  $allowedTools
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $model,
        public readonly ?string $fallbackModel,
        public readonly string $systemPrompt,
        public readonly array $mcpServers,
        public readonly array $allowedTools,
        public readonly int $maxSessionDurationSeconds,
        public readonly int $maxToolCalls,
        public readonly string $featureFlag,
        public readonly ?string $schedule,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config, string $systemPrompt): self
    {
        foreach (['slug', 'model', 'mcp_servers', 'allowed_tools', 'feature_flag'] as $required) {
            if (! array_key_exists($required, $config)) {
                throw new InvalidArgumentException("agent.json is missing required key [{$required}].");
            }
        }

        return new self(
            slug: (string) $config['slug'],
            model: (string) $config['model'],
            fallbackModel: isset($config['fallback_model']) ? (string) $config['fallback_model'] : null,
            systemPrompt: $systemPrompt,
            mcpServers: array_map('strval', (array) $config['mcp_servers']),
            allowedTools: array_map('strval', (array) $config['allowed_tools']),
            maxSessionDurationSeconds: (int) ($config['max_session_duration_seconds'] ?? 600),
            maxToolCalls: (int) ($config['max_tool_calls'] ?? 200),
            featureFlag: (string) $config['feature_flag'],
            schedule: isset($config['schedule']) ? (string) $config['schedule'] : null,
        );
    }
}
