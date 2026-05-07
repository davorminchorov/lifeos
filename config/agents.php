<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Definitions root
    |--------------------------------------------------------------------------
    |
    | Filesystem path under which agent definitions live. Each subdirectory is
    | one agent and contains agent.json + system.md (+ optional skills/).
    |
    */
    'definitions_path' => env('AGENT_DEFINITIONS_PATH', base_path('agents')),

    /*
    |--------------------------------------------------------------------------
    | Per-agent feature flags
    |--------------------------------------------------------------------------
    |
    | Agents are gated by feature flags so a malfunctioning agent can be
    | disabled with a config change. agent.json names the flag; this file
    | resolves it from the environment.
    |
    */
    'flags' => [
        'agents.email_ingestion.enabled' => env('AGENT_EMAIL_INGESTION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Managed Agents API
    |--------------------------------------------------------------------------
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'base_url' => env('ANTHROPIC_API_BASE_URL', 'https://api.anthropic.com'),
        'beta' => env('ANTHROPIC_MANAGED_AGENTS_BETA', 'managed-agents-2026-04-01'),
        'version' => env('ANTHROPIC_API_VERSION', '2023-06-01'),
        'connect_timeout' => 10,
        'request_timeout' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP servers attached to agent sessions
    |--------------------------------------------------------------------------
    |
    | Each MCP server an agent.json references must be declared here. The
    | "lifeos" entry points at our own MCP server (Phase 1) and uses an
    | agent-bound token; "gmail" expects an externally-managed MCP endpoint
    | that the user connected through the Anthropic Console.
    |
    */
    'mcp_servers' => [
        'lifeos' => [
            'url' => env('LIFEOS_MCP_PUBLIC_URL', 'http://localhost/mcp/lifeos'),
            'auth' => 'agent_token',
        ],
        'gmail' => [
            'url' => env('GMAIL_MCP_URL'),
            'auth' => 'managed_console',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Run hygiene
    |--------------------------------------------------------------------------
    */
    'run_event_retention_days' => env('AGENT_RUN_EVENT_RETENTION_DAYS', 90),
    'revert_window_minutes' => env('AGENT_REVERT_WINDOW_MINUTES', 10),
];
