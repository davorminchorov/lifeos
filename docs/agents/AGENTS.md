# LifeOS Managed Agents — Agent reference

This file lists the Managed Agents that ship with LifeOS, what they're allowed to touch, how to enable them, and how they connect to the rest of the system. Each agent is a directory under `agents/`; runtime config (URLs, secrets, feature flags) lives in `config/agents.php`.

## Architecture in one paragraph

`php artisan agents:run {slug} [--tenant=...]` resolves an agent's `agent.json`, issues a short-lived agent token bound to (user, tenant) with the allowed-tool list, asks the Anthropic Managed Agents API to create a session pointed at the LifeOS MCP server (and any other MCP servers declared in the agent definition), then streams events into `agent_runs` + `agent_run_events`. Every write tool the session calls produces a row in `pending_actions`; nothing mutates live data without human approval.

Visual:

```
                         agents:run
                              │
                              ▼
                    ┌──────────────────┐    REST + SSE
                    │ ManagedAgents    │ ◀───────────────▶  api.anthropic.com (Managed Agents beta)
                    │ Client           │
                    └──────────────────┘
                              │
                              ▼ (events)
                    ┌──────────────────┐
                    │ AgentRunRecorder │  → agent_runs, agent_run_events
                    └──────────────────┘
                              │
                              ▼ (tool_call/tool_result)
                  ┌─────────────────────────┐
                  │ Anthropic-hosted agent  │
                  │ runtime calls our MCP   │
                  └─────────────────────────┘
                              │
                              ▼
                ┌────────────────────────────┐
                │ /mcp/lifeos (this app)     │
                │ auth.agent middleware →    │
                │ Mcp\Tools\* → services →   │
                │ pending_actions queue      │
                └────────────────────────────┘
```

## Available agents

### `email-ingestion`

Reads receipt-shaped messages from the connected Gmail mailbox and proposes one `expenses.create` per receipt. Never mutates live data — every proposal lands in `/dashboard/pending-actions` for human approval.

| Field | Value |
|---|---|
| Definition | `agents/email-ingestion/agent.json` + `system.md` |
| Skill | `.claude/skills/expense-categorization/SKILL.md` |
| Model | `claude-opus-4-7` (fallback `claude-sonnet-4-6`) |
| MCP servers | `lifeos`, `gmail` |
| Allowed tools | `expenses.list`, `expenses.create`, `expenses.bulkImport` |
| Limits | 10 min session, 200 tool calls |
| Feature flag | `AGENT_EMAIL_INGESTION_ENABLED` (env) → `agents.flags.agents.email_ingestion.enabled` (config) |
| Schedule | Every 30 min when the flag is on (`routes/console.php`) |

**Hard rules** (enforced by the system prompt):

- Never auto-applies. Every proposed write goes through the approval queue.
- Never edits or deletes existing expenses (categorization fixes for existing rows arrive in Phase 4).
- Never invents a category. The `expense-categorization` skill is the only source of truth for `category` / `subcategory`. Anything that doesn't match a rule lands as `uncategorized` for human review.
- Skips messages where amount, currency, or date can't be confidently extracted, with a one-line note explaining the skip.

### Future agents (later phases)

- `email-ingestion` will gain Subscriptions, Utility Bills, IOUs, Contracts, Warranties, and Job-status updates in Phase 4.
- Phase 5: `investments-sync`.
- Phase 6: `bank-statements`.
- Phase 7: `receipts-ocr` (Drive folder).
- Phase 8: `job-search` (gated, only during active windows).
- Phase 9: `cycle-menu-planner`.
- Phase 10: `weekly-digest` (read-only).

## Enabling an agent

1. **Confirm config.** `ANTHROPIC_API_KEY` must be set; the env-driven flag for the agent must be true (e.g. `AGENT_EMAIL_INGESTION_ENABLED=true`).
2. **Confirm MCP server URLs.** `LIFEOS_MCP_PUBLIC_URL` must point at a publicly reachable URL of `/mcp/lifeos`. Phase 3 deliberately doesn't ship a tunnel playbook — Managed Agents reaches the deployed/staging server, not localhost.
3. **Confirm the skill.** For `email-ingestion`, `.claude/skills/expense-categorization/SKILL.md` must contain the user's category map and vendor aliases. The agent's system prompt requires it.
4. **Run once manually.** `php artisan agents:run email-ingestion --tenant=<slug>` (or `--dry-run` first to confirm config without calling the API). Inspect the resulting `AgentRun` at `/dashboard/agents/{id}`.
5. **Schedule kicks in automatically.** The cron entry in `routes/console.php` runs every 30 minutes for every tenant that hasn't set `agents_writes_disabled = true`.

## Disabling an agent

- **Single tenant:** set `tenants.agents_writes_disabled = true` for that tenant. The command will skip them.
- **Globally:** flip the env feature flag to false. The schedule is gated on the flag, and so is the explicit command.

## Operational notes

- Each run issues a fresh `AgentToken` with abilities limited to the agent's tool allowlist. The token is revoked at the end of the run (success or failure).
- The plaintext token never lands on disk: it's generated in memory, passed into `AgentSessionConfig` as a config value scoped to the run, and discarded.
- `agent_run_events` is append-only. A retention sweep (default 90 days, see `agents.run_event_retention_days`) is intentionally not implemented in Phase 3 — add it once we have data on event volume in production.
- The Managed Agents wire format is still in beta (`managed-agents-2026-04-01`). `ManagedAgentsClient` isolates the protocol so individual methods can swap to `anthropic-ai/sdk` calls as the SDK gains coverage. No callers will need to change.

## Failure modes

- **Session creation fails** → run is marked `failed`, the error stored on `agent_runs.error`, and the agent token is revoked. Re-run manually after fixing config.
- **Tool call fails** → individual `tool_result` events with `is_error=true` show up in the timeline; the run continues.
- **Agent times out** → the session ends; the run is marked completed with whatever pending actions exist.
- **Tenant has writes disabled mid-run** → already-proposed pending actions remain visible but cannot be applied (the applier guards `agents_writes_disabled`).
