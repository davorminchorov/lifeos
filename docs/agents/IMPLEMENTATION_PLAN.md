# LifeOS Managed Agents — Implementation Plan

> **Status:** Approved 2026-05-06. Canonical location for the plan; further phases update this file in place. Branch: `claude/implement-managed-agents-L1MeH` with one child branch + PR per phase.
>
> **Progress:**
> - Phase 0 — plan committed (this file).
> - Phase 1 — read-only MCP server: PR #148, **merged**.
> - Phase 2 — Pending Actions queue + Expenses writes: PR #150, **merged**.
> - Phase 3 — email-ingestion agent + ManagedAgentsClient + `agents:run`: PR #154 (in review). See `docs/agents/AGENTS.md`.
> - Phase 4 — email-ingestion expansion (Subscriptions, Contracts, Warranties, IOU, Utility Bills, Job status updates + interviews): PR #155.
> - Phase 5 — investments-sync agent (record transactions, dividends, mark-to-market, bulk-import statements): PR #156. Broker-agnostic; reads from Gmail (broker confirms) + Drive (statements).
> - Phase 6 — bank/card statement processor with reconciliation against existing expenses: PR #157. Introduces a `bank_lines` table, a deterministic matcher (amount + date + merchant fuzzy), and `bank.recordLines` / `bank.linkExpense` / `bank.unmatched` MCP tools.
> - Phase 7 — receipts/documents OCR agent: PR #158. Adds `source_file_id` (with backward-compatible idempotency keys) to `expenses.create`, `warranties.create`, `utilityBills.create`; new `receipts.processed` read tool to skip already-OCR'd Drive files. Vision via the user's Drive MCP — no new LifeOS infrastructure.
> - Phase 8 — job-search hunter agent: in PR. Gated by `agents.job_search.enabled` AND a per-skill active-search window. New `jobs.createApplication` MCP tool + starter `cv` and `job-criteria` skills (placeholder content the user replaces).
> - Phase 9 — cycle-menu planner agent: in PR. New `cycleMenu.addItem`, `cycleMenu.setWeek`, `cycleMenu.shoppingList` tools. The agent fills empty days only (never overwrites the user's plan) and produces a structured shopping list aggregation for the next 7 days.

## Context

LifeOS is a Laravel 12 + React 19/Inertia v3 personal-life management app with 11 active modules (Subscriptions, Contracts, Warranties, Investments, Expenses, Utility Bills, IOU/Debt, Budgets, Job Applications, Cycle Menu, Notifications) plus the recently delivered Invoicing module. Multi-tenancy is row-level via a `tenant_id` column and `BelongsToTenant`/`TenantScope`; conversion is complete for the 34 covered models.

Today the app already has substantial AI scaffolding:

- `laravel/ai` v0.4 with Anthropic as default provider (`config/ai.php`).
- `app/Ai/Agents/LifeOsAssistant.php` — an in-process conversational agent.
- `app/Ai/Tools/` — 35 tool classes extending `TenantScopedTool` (Create*, Query*, Update*, Add*, Cancel*, Log*, File*, Summarize*, Generate*).
- An `AssistantController` exposing `/api/assistant/{message,stream,suggestions,history}` over session auth.
- `laravel/mcp` v0.6 is already in `composer.lock` (pulled in transitively by `laravel/boost`).
- `laravel/boost`'s MCP server is wired in `.mcp.json` for Claude Code dev usage.

What's missing, and why this initiative exists:

- LifeOS data is still populated and maintained by hand. The user wants autonomous agents (Anthropic Managed Agents, beta header `managed-agents-2026-04-01`) to ingest from Gmail, Drive, broker portals, statements, and job boards and to write back to LifeOS modules through a tenant-safe, auditable, reversible interface.
- The current AI tools are coupled to `laravel/ai`'s in-process `Agent` interface; they can't be invoked by an external Managed Agents runtime.
- There is no Pending Actions queue, no agent run log, no per-tenant API token model, and no pattern for idempotent agent writes that respect FormRequest validation and domain events.
- Most modules outside Invoicing skip the service layer; controllers write directly through Eloquent. MCP write tools must call services to keep validation, policy, and event semantics consistent — so a small extraction effort precedes write tooling.

The plan below builds two layers — a tenant-aware MCP server that exposes domain operations, and a set of Managed Agent definitions plus orchestration — phased so each gate is demonstrable before the next begins.

---

## Architecture decisions

### 1. MCP server: official `laravel/mcp` (already installed)

Use the official package. Rationale:

- Already a transitive dependency; promote it to a top-level `composer require laravel/mcp` so the version is pinned independently of `laravel/boost`.
- Native Laravel routing/middleware integration: tools register via `Mcp::web()` in a dedicated `routes/ai.php`, and Sanctum middleware can guard the endpoint without a custom transport layer.
- Tool classes follow a contract similar to the existing `TenantScopedTool`, so the conceptual leap for contributors is small.
- Avoids reinventing JSON-RPC framing, schema generation, and streaming.

**Reject** custom thin server: maintenance burden with no offsetting benefit.

### 2. Tools: thin MCP wrappers over a real service layer

- New namespace `App\Mcp\Tools\{Module}\{Action}` (e.g. `App\Mcp\Tools\Expenses\CreateExpense`). Each MCP tool defines its JSON Schema input, resolves the tenant from the bound token, calls a service method, and returns a stable JSON response.
- Where a service does not yet exist, extract one. Order: `ExpenseService` (Phase 2), then `SubscriptionService`, `ContractService`, `WarrantyService`, `BudgetService`, `IouService`, `JobApplicationService`, `CycleMenuService`, `UtilityBillService` (one per agent phase). Models retain `BelongsToTenant`. Services accept a `User|Tenant` context and emit domain events (extending coverage where missing — see Phase 3+).
- Existing `app/Ai/Tools/*` are kept and refactored over time to delegate to the same services (so `LifeOsAssistant` and the MCP server share one implementation per operation). No duplicate write logic.

### 3. Auth & tenant resolution: dedicated `agent_tokens` table

- New `agent_tokens` table: `id, user_id, tenant_id, name, agent_slug (nullable), token_hash, abilities (json — tool allowlist), last_used_at, expires_at, revoked_at, created_at`.
- New middleware `auth.agent` resolves the token, sets `auth()` to the bound user, sets `current_tenant_id` on the user, and pushes the token's tool allowlist into the request so MCP tools can self-gate.
- A token belongs to exactly one (user, tenant) pair. Multi-tenant users get one token per tenant. This makes tenant isolation a property of the token, not of request-routing logic.
- Reject Sanctum's default `personal_access_tokens` for this use case — it has no tenant column and no per-tool scope. Keep Sanctum for the existing browser-bound API.

### 4. Pending Actions queue

Every write tool defaults to creating a `pending_action` rather than mutating data. The user reviews and approves in the dashboard. Auto-apply is gated and per-tenant configurable (Phase 2 details below).

### 5. Orchestration: Laravel scheduler dispatches `agents:run` artisan commands

- Reuse the existing scheduler. Each agent has a `Schedule::command('agents:run {slug}')->cron(...)` entry behind a feature flag.
- The `agents:run` command (Phase 3) creates a Managed Agents session via the official Anthropic PHP SDK (`anthropic/anthropic-sdk-php`), passes the LifeOS MCP server URL and the agent's tool allowlist, streams events into an `agent_runs` table, and exits when the session terminates.
- **Reject** external cron hitting the Managed Agents API directly: duplicates scheduling logic and loses the ability to gate runs behind a tenancy/feature-flag check.

### 6. Anthropic client: official `anthropic/anthropic-sdk-php` + HTTP fallback inside `ManagedAgentsClient`

**Why this is needed at all.** `laravel/ai` does not cover Managed Agents — verified on both the installed version (`v0.4.2`, per `composer.lock`) and the latest release (`v0.6.6`, published 2026-05-02). `AnthropicProvider` in both versions implements only `TextProvider`, `FileProvider`, `SupportsWebFetch`, `SupportsWebSearch`, and a recursive listing of the v0.6.6 source tree returns zero paths matching `managed`, `session`, or `agent`. The existing `LifeOsAssistant` is therefore an in-process agent against the Messages API, not a Managed Agents client. We need a separate client for the hosted runtime.

**Approach.** A new `App\Services\Agents\ManagedAgentsClient` exposes the minimum surface Phase 3 needs: `createSession`, `streamEvents`, `sendUserMessage`, `cancel`, `getSession`. Internally:

1. **Primary path:** `anthropic/anthropic-sdk-php` (added to composer in Phase 3, when it's first needed). Each `ManagedAgentsClient` method delegates to the SDK where the SDK exposes the corresponding Managed Agents endpoint.
2. **Fallback path:** for any endpoint the SDK doesn't yet expose, the same method falls back to `Http::withHeaders(['anthropic-beta' => 'managed-agents-2026-04-01', 'x-api-key' => ...])`. The fallback is isolated to the client class so callers (`agents:run`, tests) never see the difference.
3. **Migration path:** as new SDK versions cover more endpoints, the per-method `if ($this->sdkSupports(...))` checks shrink and the fallback eventually goes away. No caller changes.

This way we benefit from the SDK's idiomatic interfaces, type safety, retries, and streaming where available, while remaining unblocked for any endpoint the SDK still lags on.

**Phase boundary.** No SDK dependency change in Phases 1 or 2. `composer require anthropic/anthropic-sdk-php` happens at the start of Phase 3, alongside the Managed Agents client implementation.

### 7. Agent definitions: file-based registry

- New top-level `agents/` directory. Per agent: `agents/{slug}/agent.json` (model, mcp allowlist, tool allowlist, schedule, resource limits, feature flag), `agents/{slug}/system.md` (system prompt with `{{...}}` placeholders for runtime context), `agents/{slug}/skills/` (symlinks/refs into `.claude/skills/` plus agent-specific skill folders).
- An `App\Services\Agents\AgentRegistry` reads these at boot and exposes `AgentRegistry::find($slug): AgentDefinition`. The artisan command and the dashboard both consume the registry — no DB-side definition state, so agent changes go through code review.

### 8. Skills

Move/create domain skills under `.claude/skills/` so both Claude Code (dev) and Managed Agents (runtime, via the Skills feature) consume the same files:

- `expense-categorization/SKILL.md` — category map, vendor aliases (user-supplied content; Phase 3 blocks on this).
- `vendor-aliases/SKILL.md` — extracted from above if it grows.
- `cv/SKILL.md` — user's CV for job-search agent (Phase 8).
- `job-criteria/SKILL.md` — must-haves, dealbreakers, salary band (Phase 8).
- `budget-structure/SKILL.md` — budget categories, periods, rollover preferences (Phase 4+).

Existing `laravel-best-practices` and `tailwindcss-development` skills stay untouched.

---

## Directory layout (new)

```
agents/
  email-ingestion/
    agent.json
    system.md
    skills/                 # symlink: ../../.claude/skills/expense-categorization
  investments-sync/
  bank-statements/
  ...
app/
  Mcp/
    Server.php              # registered with Mcp::web('/mcp/lifeos', ...)
    Middleware/
      ResolveAgentTenant.php
    Tools/
      Dashboard/Summary.php
      Expenses/{ListExpenses, CreateExpense, BulkImportExpenses, CategorizeExpense}.php
      Subscriptions/...
      ...
  Services/
    Agents/
      AgentRegistry.php
      ManagedAgentsClient.php
      AgentRunRecorder.php
      PendingActionApplier.php
      IdempotencyKey.php
    Expenses/ExpenseService.php          # Phase 2
    Subscriptions/SubscriptionService.php # Phase 4
    ...
  Models/
    AgentToken.php
    AgentRun.php
    PendingAction.php
  Console/Commands/
    AgentsRun.php           # php artisan agents:run {slug}
docs/agents/
  IMPLEMENTATION_PLAN.md    # this file, post-approval
  MCP_TOOLS.md              # tool reference
  AGENTS.md                 # agent reference
  RUNBOOK.md                # how to enable/disable, troubleshoot
resources/js/pages/
  PendingActions/
    Index.tsx
    Show.tsx
routes/
  ai.php                    # MCP routes
tests/
  Feature/Mcp/...
  Feature/Agents/...
  Unit/Services/Agents/IdempotencyKeyTest.php
```

---

## Pending Actions schema

```php
Schema::create('pending_actions', function (Blueprint $t) {
    $t->id();
    $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $t->foreignId('user_id')->constrained()->cascadeOnDelete();
    $t->foreignId('agent_token_id')->nullable()->constrained();
    $t->string('agent_slug');                    // 'email-ingestion'
    $t->string('session_id');                    // Anthropic session id
    $t->string('tool');                          // 'expenses.create'
    $t->string('action');                        // 'create' | 'update' | 'delete' | 'bulk_create'
    $t->nullableMorphs('target');                // applies-to model when known (update/delete)
    $t->json('payload');                         // validated tool input
    $t->json('preview')->nullable();             // human-readable diff snapshot
    $t->string('idempotency_key', 64);           // deterministic per natural fields
    $t->string('status');                        // pending|approved|rejected|applied|failed|reverted|superseded
    $t->json('applied_diff')->nullable();        // before/after when applied
    $t->string('failure_reason')->nullable();
    $t->foreignId('reviewed_by')->nullable()->constrained('users');
    $t->timestamp('reviewed_at')->nullable();
    $t->timestamp('applied_at')->nullable();
    $t->foreignId('reverted_by')->nullable()->constrained('users');
    $t->timestamp('reverted_at')->nullable();
    $t->foreignId('reverted_pending_action_id')->nullable()->constrained('pending_actions');
    $t->timestamps();
    $t->unique(['tenant_id', 'tool', 'idempotency_key']);
    $t->index(['tenant_id', 'status', 'created_at']);
});
```

**Idempotency key generation** (`App\Services\Agents\IdempotencyKey`):

- `expenses.create`: sha256(tenant_id|merchant_normalized|amount_cents|currency|expense_date)
- `subscriptions.create`: sha256(tenant_id|service_name_normalized|currency|billing_cycle)
- `bank.line`: sha256(tenant_id|account|posted_at|amount_cents|fingerprint)
- Per-tool generators live next to the tool; tested in `tests/Unit/Services/Agents/IdempotencyKeyTest.php`.

**Auto-apply rules** (Phase 2+):

- Default: every write produces a `pending` action.
- Allowed only when (a) `tool_auto_apply` config for the tenant and tool says yes, AND (b) an identical `idempotency_key` was previously approved (proves the user has explicitly accepted this shape of write before).
- Multi-tenant guard: a tenant flagged as `agents_writes_disabled` rejects all writes regardless of token abilities.

**Revert**: applying a `pending_action` records the inverse payload as `applied_diff`. Reverting creates a new `pending_action` with `action='revert'`, `reverted_pending_action_id` set, status `applied` immediately if within the configured window (default 10 minutes) and the reviewer is the original applier.

---

## Audit log additions

- `agent_runs` table: id, tenant_id, agent_slug, session_id, started_at, ended_at, status (running/completed/failed/cancelled), tools_called (json — count per tool), pending_actions_created (count), tokens_in, tokens_out, cost_usd, error (nullable text). Created at session start, updated as events stream.
- `agent_run_events` table (append-only, lightweight): agent_run_id, sequence, type (tool_call/tool_result/text/error), payload (json, redacted), occurred_at. Capped per run, pruned after 90 days.
- Every model write that an agent ultimately applies gets `created_by_agent_id` and `source` ('agent' | 'user' | 'import') columns added in a single shared migration. Existing rows backfill to `'user'`. Observers populate these from request context.

---

## Multi-tenant resolution for inbound MCP requests

1. Anthropic Managed Agents calls `POST https://lifeos.example.com/mcp/lifeos` with `Authorization: Bearer <agent_token>`.
2. `auth.agent` middleware:
   - Hashes the bearer, looks up `agent_tokens.token_hash`, asserts not revoked/expired.
   - Loads `User` and forces `current_tenant_id = agent_token.tenant_id`. Calls `Auth::setUser($user)` for the request lifetime.
   - Pushes `agent_token` and tool allowlist into the request container.
   - Rejects with 403 if `Tenant::where('id', $token->tenant_id)->where('agents_writes_disabled', false)->doesntExist()` for write-classed tools.
3. MCP tools call services that rely on the existing `TenantScope` global scope and `BelongsToTenant` trait. No new tenancy plumbing inside tools.
4. Background `agents:run` command, before creating a Managed Agents session, sets the same context locally so any synchronous setup runs under the right tenant; the session itself authenticates via the issued `agent_token`.

---

## Tool surface (full list, name + signature)

Schemas are JSON-Schema fragments. Read tools land in Phase 1, write tools in their respective phases.

### Read tools (Phase 1)

- `dashboard.summary` → `{ period?: '30d'|'90d'|'12m' }` → `{ totals, upcoming, alerts }`
- `expenses.list` → `{ from?: date, to?: date, category?: string, merchant?: string, limit?: int, cursor?: string }` → `{ items[], next_cursor? }`
- `subscriptions.list` → `{ status?: 'active'|'cancelled'|'paused', due_within_days?: int }` → `{ items[] }`
- `investments.portfolio` → `{}` → `{ positions[], totals_by_currency, last_priced_at }`
- `bills.upcoming` → `{ within_days?: int }` → `{ items[] }`
- `contracts.list` → `{ status?, expiring_within_days?: int }` → `{ items[] }`
- `warranties.list` → `{ status?, expiring_within_days?: int }` → `{ items[] }`
- `iou.list` → `{ direction?: 'owe'|'owed', status?: 'pending'|'paid'|'overdue' }` → `{ items[] }`
- `jobs.pipeline` → `{ status?, stale_after_days?: int }` → `{ stages[], counts }`
- `cycleMenu.currentWeek` → `{}` → `{ week_start, days[] }`
- `notifications.list` → `{ unread_only?: bool, limit?: int }` → `{ items[] }`

### Write tools (Phase 2: Expenses only)

- `expenses.create` → `{ amount: number, currency: string, expense_date: date, merchant: string, category?: string, subcategory?: string, payment_method?: string, description?: string, tags?: string[], receipt_url?: string, source_email_id?: string, auto_apply?: bool }` → `{ pending_action_id, status, idempotency_key }`
- `expenses.bulkImport` → `{ items: <expenses.create payload>[] }` → `{ pending_action_ids[], skipped_duplicates[] }`
- `expenses.categorize` → `{ expense_id: int, category: string, subcategory?: string, confidence?: number }` → `{ pending_action_id, status }`

### Write tools (Phases 4–10, listed up-front for visibility, implemented later)

- Subscriptions: `create`, `update`, `cancel`, `pause`, `resume`, `recordRenewal`
- Contracts: `create`, `addAmendment`, `terminate`, `renew`
- Warranties: `create`, `recordClaim`, `transfer`
- Investments: `recordTransaction`, `recordDividend`, `repriceLot`, `bulkImportTransactions`
- Utility Bills: `create`, `recordPayment`, `markPaid`
- IOU: `create`, `recordPayment`, `markSettled`
- Budgets: `create`, `update`, `archive`
- Jobs: `createApplication`, `addInterview`, `updateStatus`, `addOffer`, `archive`
- Cycle Menu: `addItem`, `setWeek`, `regenerateShoppingList`
- Notifications: `markRead`, `dismiss` (no auto-apply restriction; non-destructive)
- Cross-cutting: `pendingActions.list`, `pendingActions.approve`, `pendingActions.reject` (only for an admin/reviewer agent, gated by ability)

Full JSON-Schema files live under `docs/agents/MCP_TOOLS.md`, generated from PHP attribute definitions per tool.

---

## Phased delivery

### Phase 0 — Plan & confirm (this file)

Deliverables:
- This plan committed to `docs/agents/IMPLEMENTATION_PLAN.md`.
- Open questions resolved with the user.

**Stop. Wait for written approval.**

### Phase 1 — Read-only MCP server

- Promote `laravel/mcp` to a direct dependency.
- Create `agent_tokens` table + `AgentToken` model + `auth.agent` middleware + `php artisan agents:tokens:issue {user} {tenant} --abilities=...` command.
- Register the LifeOS MCP server in `routes/ai.php` under `/mcp/lifeos`, guarded by `auth.agent`.
- Implement all 11 read tools above. Each calls existing query paths (controllers' index logic extracted to lightweight read services / `*Reader` classes — small extraction, no event coverage required).
- Document tool schemas in `docs/agents/MCP_TOOLS.md`.
- Add `.mcp.json` entry pointing at the local URL so Claude Code can call the LifeOS MCP locally during dev.
- Tests (PHPUnit feature tests):
  - Token rejection (missing/expired/revoked) → 401.
  - Cross-tenant token cannot read another tenant's data.
  - One happy-path test per read tool asserting shape and tenant scoping.

**Stop. Demo from Claude Code via the new MCP entry. Wait for confirmation.**

### Phase 2 — Pending Actions + Expenses writes

- Add `pending_actions` migration + model + policy.
- Add cross-cutting `created_by_agent_id` + `source` columns migration.
- Build `PendingActionApplier` service: validates payload via the same FormRequest used by the controller (`StoreExpenseRequest`), calls `ExpenseService` (newly extracted), records `applied_diff`. Idempotency keys computed by per-tool generators.
- Extract `App\Services\Expenses\ExpenseService` from `ExpenseController`. Methods: `create`, `update`, `delete`, `categorize`, `bulkImport`, `markReimbursed`. Existing controller delegates to it. Existing `app/Ai/Tools/CreateExpense.php` updated to delegate to it as well.
- Implement MCP write tools: `expenses.create`, `expenses.bulkImport`, `expenses.categorize`. All write to the queue. `auto_apply: true` only honoured when (a) the tenant has `agents_writes_disabled = false`, (b) per-tenant `tool_auto_apply.expenses.create` is enabled, AND (c) an identical idempotency key was previously approved.
- "Pending Agent Actions" page in React (`/dashboard/pending-actions`):
  - List with filters (agent, status, module, date range), badge in sidebar with pending count.
  - Detail page with payload, preview/diff, approve / reject / bulk approve.
  - Undo within 10 minutes (configurable) creates an inverse pending action.
  - Uses existing shadcn/ui components (Dialog, Table, Badge, Tabs).
- Tests: idempotency (resubmit returns existing pending action), tenant isolation, approve flow, reject flow, revert flow, FormRequest enforcement (invalid payload → tool error, no pending action created).

**Stop. Demo end-to-end with a dummy payload from Claude Code. Wait for confirmation.**

### Phase 3 — Email ingestion agent (Expenses only)

- Define `agents/email-ingestion/agent.json`: model `claude-opus-4-7` (with sonnet fallback config), MCP allowlist = Gmail MCP + LifeOS MCP `expenses.*`, max_session_duration 10 min, max_tool_calls 200, feature flag `agents.email_ingestion.enabled`.
- `agents/email-ingestion/system.md`: instructions to read recent receipt-shaped emails, extract one expense per email, dedupe via idempotency key, create pending actions only (no auto-apply allowed in Phase 3).
- Skill `.claude/skills/expense-categorization/SKILL.md` — **content blocks on user input; this is an open question below**.
- `agent_runs` + `agent_run_events` migrations.
- `php artisan agents:run email-ingestion` command:
  - Creates Managed Agents session via `ManagedAgentsClient` with beta header `managed-agents-2026-04-01`.
  - Streams events; writes one `agent_run_event` per significant event; updates `agent_runs.tools_called`, `tokens_in/out`, `cost_usd`, `pending_actions_created` live.
  - Honours feature flag and tenancy gate.
- Schedule entry: every 30 min, behind feature flag.
- Dashboard: agent runs list under `/dashboard/agents` with filters and a per-run timeline.
- Feature test: run the agent against a fixture mailbox (recorded JSON of Gmail MCP responses); assert `pending_actions` count and shape.

**Stop. User runs against real Gmail for a week. Wait for feedback.**

### Phases 4–10 (one per agent, each gated)

Each phase ships: (a) any service-extraction needed, (b) module write tools added to the MCP surface with idempotency generators, (c) updated agent definition with extended tool allowlist, (d) feature tests, (e) dashboard surfacing.

4. **Email ingestion expansion** — Subscriptions, Utility Bills, IOUs, Contracts, Warranties, Job status updates.
5. **Investments sync agent** — reuse `wvp-fondovi-scraper` skill (confirm location with user) and add scrapers for any other broker the user names.
6. **Bank/card statement processor** — reconciles against pending and applied expenses; introduces `bank_lines` table + matcher.
7. **Receipt/document OCR agent** — Drive folder watcher + OCR via Anthropic vision.
8. **Job search hunter** — gated by `agents.job_search.enabled` feature flag, only enabled during active job-search windows. Requires `cv` and `job-criteria` skills.
9. **Cycle menu planner** — weekly rotation, shopping-list generation tool.
10. **Weekly digest agent** — Sunday 21:00 cron, emails one-page summary; read-only tool surface.

---

## Quality bars (apply throughout)

- **Multi-tenant safety.** `auth.agent` is the single tenancy entry point. `agents_writes_disabled` tenant flag short-circuits all writes. Tests assert cross-tenant tokens fail.
- **Idempotency.** Per-tool key generators with unit tests. Resubmits return the existing pending action, never duplicates.
- **No domain bypass.** Every write tool runs payload through the controller's FormRequest, then calls a service that emits the same events as a manual write. Where events are missing today (Iou, JobApplication), add them in the phase that touches the module.
- **Observability.** Every run records tool calls, pending actions, tokens, cost, errors, duration. `/dashboard/agents` surfaces it.
- **Reversibility.** Applied actions store an `applied_diff`. Reverts create a compensating pending action; the audit chain stays linear.
- **Tests.** Feature tests per tool. Unit tests per idempotency-key generator. Per-agent integration test with fixtures. Run `php artisan test --compact tests/Feature/Mcp tests/Feature/Agents` after each phase. Run Pint (`vendor/bin/pint --dirty --format agent`) before each commit.
- **Docs.** `docs/agents/MCP_TOOLS.md`, `docs/agents/AGENTS.md`, `docs/agents/RUNBOOK.md` updated within the phase that changes them. Prefer table-of-tools that auto-generates from PHP attributes if feasible.

---

## Test strategy

- **Unit:** idempotency key generators; payload-to-event mappings for new services; `PendingActionApplier`.
- **Feature (MCP layer):** auth (token valid/expired/revoked/cross-tenant), each tool's happy path, FormRequest rejections returned as MCP errors, tenant-isolation invariants, auto-apply rule enforcement, revert-window behaviour.
- **Feature (Agent layer):** record fixtures of Gmail/Drive MCP responses, replay them through `agents:run`, assert resulting `pending_actions` and `agent_runs` rows. One golden test per agent.
- **End-to-end smoke:** an `artisan agents:smoke` command that runs each enabled agent against a sandbox tenant with fixture data, used in CI on the agent branch.

---

## Resolved decisions (from plan-mode Q&A)

- **Anthropic client.** `laravel/ai` does not cover Managed Agents (verified against source on both v0.4.2 installed and v0.6.6 latest). Recommended approach selected: official `anthropic/anthropic-sdk-php` is the primary path inside `ManagedAgentsClient`, with raw-HTTP fallback for any endpoint the SDK doesn't yet expose, all behind one interface. SDK is added to composer at the start of Phase 3 (no dependency change in Phases 1–2).
- **Token model.** Dedicated `agent_tokens` table.
- **Phase 2 auto-apply.** Strict — every write produces a pending action in Phase 2. The auto-apply rule is implemented as plumbing (config + idempotency-key match check) but the config defaults to `false` for every (tenant, tool) pair and only flips after the user approves it explicitly in a later phase.
- **Branch cadence.** One PR per phase, child branches off `claude/implement-managed-agents-L1MeH`. Final merge to `main` once all phases land.

## Open questions

Resolved 2026-05-06:

- **MCP exposure target.** Deployed URL only — no dev tunnel playbook. The Phase 3 `agents:run` command points Managed Agents at the production (or staging) MCP URL; local development uses `php artisan boost:mcp` for Claude Code, not Managed Agents.
- **Pending Actions UI placement.** Both — a dedicated `/dashboard/pending-actions` page with a sidebar badge for the actual review/approve/reject workflow, plus entries surfaced in the Notification Center for discoverability. Notification entries deep-link into the dedicated page.

Still open:

1. **Scope of `wvp-fondovi-scraper`.** Where does this live today, and is it a Claude Code skill or a separate tool? (Needed before Phase 5.)
2. **Skill content.** User to provide the expense-category map and vendor-alias list before Phase 3 starts — Phase 3 hard-blocks on this so the agent never invents categories.

---

## Verification (Phase 1 acceptance)

End-to-end check after Phase 1:

1. `composer install && php artisan migrate` cleanly applies new migrations.
2. `php artisan agents:tokens:issue user@example.com tenant-slug --abilities=read:*` prints a token.
3. Add the LifeOS MCP entry to `.mcp.json` (URL + token header).
4. From Claude Code, run `/mcp` then call `dashboard.summary`, `expenses.list`, `subscriptions.list` — assert structured JSON.
5. Issue a second token bound to a different tenant; confirm cross-tenant data does not leak.
6. `php artisan test --compact tests/Feature/Mcp` is green.

Phase 2 and onward have analogous acceptance steps; spelt out in `docs/agents/RUNBOOK.md` once that file lands.

## Critical files to be modified or created

- `composer.json` — promote `laravel/mcp` (Phase 1); add `anthropic/anthropic-sdk-php` (Phase 3).
- `routes/ai.php` (new), `bootstrap/app.php` — register routes file and `auth.agent` alias.
- `app/Mcp/**` (new tree).
- `app/Models/{AgentToken,AgentRun,AgentRunEvent,PendingAction}.php` (new).
- `app/Services/Agents/**`, `app/Services/Expenses/ExpenseService.php` (new).
- `database/migrations/...` for `agent_tokens`, `agent_runs`, `agent_run_events`, `pending_actions`, `add_source_and_agent_columns`.
- `resources/js/pages/PendingActions/{Index,Show}.tsx` (Phase 2).
- `resources/js/pages/Agents/{Index,Show}.tsx` (Phase 3).
- `agents/{slug}/agent.json + system.md` per agent.
- `.claude/skills/{expense-categorization,vendor-aliases,...}/SKILL.md` (user-supplied content).
- `docs/agents/{IMPLEMENTATION_PLAN,MCP_TOOLS,AGENTS,RUNBOOK}.md`.
- `tests/Feature/Mcp/**`, `tests/Feature/Agents/**`, `tests/Unit/Services/Agents/**`.

## Existing functions/utilities to reuse

- `BelongsToTenant` trait + `TenantScope` (`app/Traits/`, `app/Scopes/`) — every new model uses these; no new tenancy plumbing.
- `App\Ai\Tools\TenantScopedTool` — pattern for reading the active tenant inside a tool; the MCP middleware will set the same context.
- `App\Services\InvoicingService` — reference template for service-layer extraction (createDraft/issue/recordPayment/etc.).
- `App\Services\ReceiptParserService` + `ParseReceiptAndCreateExpense` job — Phase 3 agent can leverage existing receipt-parsing logic; the MCP tool layer wraps it rather than reimplementing.
- `App\Support\NotificationDeduplicator` — pattern (and likely direct reuse) for agent-triggered notifications.
- `expenses.unique_key`, `subscriptions.unique_key` columns — already exist; keep populating them via the new idempotency-key generators so manual and agent paths share dedupe surface.
- Existing `app/Ai/Tools/Query*` — query logic stays canonical; MCP read tools delegate where shapes match.
- Existing `AssistantController` patterns (`auth + tenant` middleware, throttling) — mirror for MCP route.
