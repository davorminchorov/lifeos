# LifeOS MCP — Tool Reference

The LifeOS MCP server is registered at `/mcp/lifeos` (Streamable HTTP, JSON-RPC 2.0) and guarded by the `auth.agent` middleware. Every request must include `Authorization: Bearer <agent_token>`. The bound `(user, tenant)` pair is resolved from the token; all reads are filtered by the existing `BelongsToTenant` global scope using that tenant.

Phase 1 ships read-only tools. Each tool returns structured JSON via `Response::structured(...)`; the same content is also serialized as text content for clients that don't render structured output.

## Issuing a token

```
php artisan agents:tokens:issue user@example.com tenant-slug \
    --abilities="read:*" \
    --name="claude-code dev" \
    --expires="+30 days"
```

Print is one-shot. The plaintext token shown is `lifeos_agent_<48-char-random>`. The server stores only the SHA-256 hash.

## Abilities

Abilities are tool-name patterns:

- `*` — any tool
- `expenses.*` — any tool starting with `expenses.`
- `expenses.list` — exact match
- `read:*` — convention for "any read tool" (Phase 1 tools all match Phase 1 read-tool names; the server enforces literal pattern matching against the tool name, so `read:*` only works if a tool has a name starting with `read:`. Issue tokens with concrete patterns until Phase 2 adds the formal read-classifier.)

For Phase 1, the recommended ability set is one of:

- `*` — full access (development/testing only)
- `dashboard.*,expenses.*,subscriptions.*,investments.*,bills.*,contracts.*,warranties.*,iou.*,jobs.*,cycleMenu.*,notifications.*` — explicit allowlist for the read tools.

## Tool catalogue

### `dashboard.summary`

Cross-module snapshot for the authenticated tenant.

**Input**

| field | type | description |
|---|---|---|
| `upcoming_window_days` | int | Day window for "upcoming" items (default 30). |

**Output (structured)**

```json
{
  "as_of": "2026-05-07T00:00:00+00:00",
  "window_days": 30,
  "totals": {
    "subscriptions_active": 0,
    "contracts_active": 0,
    "warranties_active": 0,
    "investments_total": 0,
    "jobs_active": 0,
    "iou_pending_owe": 0,
    "iou_pending_owed": 0,
    "expenses_this_month_count": 0,
    "expenses_this_month_amount": 0.0
  },
  "upcoming": {
    "subscription_renewals": [],
    "contracts_expiring": [],
    "warranties_expiring": [],
    "bills_due": []
  },
  "alerts": {
    "overdue_bills": 0,
    "overdue_iou": 0,
    "jobs_with_overdue_action": 0
  }
}
```

### `expenses.list`

Filterable expense list.

**Input**

| field | type | description |
|---|---|---|
| `from` | date | Inclusive lower bound (YYYY-MM-DD). |
| `to` | date | Inclusive upper bound. |
| `category` | string | Substring match. |
| `merchant` | string | Substring match. |
| `min_amount` | number | Minimum amount in expense currency. |
| `max_amount` | number | Maximum amount in expense currency. |
| `limit` | int | Default 50, max 200. |

**Output**

```json
{ "count": 0, "limit": 50, "items": [
  { "id": 1, "expense_date": "2026-05-01", "amount": 12.50, "currency": "EUR",
    "merchant": "Lidl", "category": "groceries", "subcategory": null,
    "description": null, "payment_method": "card", "status": "applied" }
] }
```

### `subscriptions.list`

| field | type | description |
|---|---|---|
| `status` | string | e.g. "active", "cancelled", "paused". |
| `due_within_days` | int | Filter by upcoming `next_billing_date`. |
| `category` | string | Substring match. |
| `limit` | int | Default 100, max 500. |

Items: `id, service_name, category, cost, currency, billing_cycle, next_billing_date, status, auto_renewal`.

### `investments.portfolio`

| field | type | description |
|---|---|---|
| `investment_type` | string | Filter by `investment_type` (e.g. "stock", "etf", "fund"). |

Returns `{ count, totals_by_currency, last_priced_at, positions[] }`. Each position carries `cost_basis`, `market_value`, `unrealized_gain_loss` derived from `quantity * purchase_price` and `quantity * current_value`.

### `bills.upcoming`

| field | type | description |
|---|---|---|
| `within_days` | int | Default 30. |
| `utility_type` | string | Filter by `utility_type`. |
| `include_overdue` | bool | Default true. |
| `limit` | int | Default 100, max 500. |

Items: `id, utility_type, service_provider, bill_amount, currency, due_date, payment_status, account_number, days_until_due`.

### `contracts.list`

| field | type | description |
|---|---|---|
| `status` | string | e.g. "active", "terminated". |
| `expiring_within_days` | int | Filter by `expiringSoon`. |
| `contract_type` | string | Exact match. |
| `limit` | int | Default 100, max 500. |

Items: `id, title, counterparty, contract_type, start_date, end_date, notice_period_days, auto_renewal, contract_value, status, days_until_expiration`.

### `warranties.list`

| field | type | description |
|---|---|---|
| `current_status` | string | e.g. "active", "expired", "claimed". |
| `expiring_within_days` | int | Filter by `expiringSoon`. |
| `brand` | string | Substring match. |
| `limit` | int | Default 100, max 500. |

Items: `id, product_name, brand, model, serial_number, purchase_date, purchase_price, retailer, warranty_expiration_date, warranty_type, current_status, days_until_expiration`.

### `iou.list`

| field | type | description |
|---|---|---|
| `direction` | string | "owe" or "owed". |
| `status` | string | e.g. "pending", "partially_paid", "paid". |
| `person_name` | string | Substring match. |
| `overdue_only` | bool | Default false. |
| `limit` | int | Default 100, max 500. |

Items: `id, direction, person_name, amount, amount_paid, remaining, currency, transaction_date, due_date, description, status, category`.

### `jobs.pipeline`

| field | type | description |
|---|---|---|
| `include_archived` | bool | Default false. |
| `remote_only` | bool | Default false. |
| `limit` | int | Default 200, max 500. |

Returns `{ count, counts_by_status, items[] }`. Items: `id, company_name, job_title, location, remote, salary_min, salary_max, currency, status, source, priority, applied_at, next_action_at, archived`.

### `cycleMenu.currentWeek`

No input. Returns the active cycle menu mapped to the next 7 days starting today, aligning today to `((now - starts_on) mod cycle_length_days)`.

```json
{
  "menu": { "id": 1, "name": "Standard", "starts_on": "2026-04-01", "cycle_length_days": 7 },
  "today_day_index": 2,
  "week": [
    { "date": "2026-05-07", "day_index": 2, "notes": null, "items": [...] }
  ]
}
```

### `notifications.list`

| field | type | description |
|---|---|---|
| `unread_only` | bool | Default false. |
| `limit` | int | Default 50, max 200. |

Items: `id, type, data, read_at, created_at`.

## Multi-tenant guarantees

- Tokens carry exactly one `tenant_id`. Cross-tenant access is impossible by construction.
- The `AuthenticateAgent` middleware sets `current_tenant_id` on the bound user before tools run, so the existing `TenantScope` global scope filters every Eloquent query.
- The middleware does not modify the database (only the in-memory `User` model state), so concurrent web sessions for the same user are unaffected.

## Write tools (Phase 2)

The Phase 2 write tools never mutate live data on call. Each one creates a row in `pending_actions` (idempotent by `idempotency_key`) and returns the row's id and status. The user reviews and approves at `/dashboard/pending-actions`. Auto-apply is allowed only when (a) `tenants.agents_writes_disabled = false` AND (b) `tenants.tool_auto_apply[tool] = true` AND (c) an identical idempotency key was previously approved on this tenant. The default is `false` for every (tenant, tool) pair.

### `expenses.create`

| field | type | description |
|---|---|---|
| `amount` | number | Required. |
| `currency` | string | ISO 4217. Defaults to MKD. |
| `expense_date` | date | YYYY-MM-DD. Required. |
| `merchant` | string | Vendor / store. |
| `category` | string | Required. |
| `subcategory` | string | Optional. |
| `description` | string | Required (falls back to `merchant`). |
| `payment_method` | string | Optional. |
| `expense_type` | string | "business" or "personal". |
| `is_tax_deductible` | bool | Optional. |
| `tags` | string[] | Optional. |
| `source_email_id` | string | Optional, used as an idempotency disambiguator (e.g. Gmail message id). |

Idempotency key: `sha256("expenses.create|<tenant>|<merchant_normalized>|<amount_cents>|<currency>|<expense_date>|<source_email_id>")`.

Returns: `{ pending_action_id, status, idempotency_key, auto_applied }`.

### `expenses.bulkImport`

| field | type | description |
|---|---|---|
| `items` | array | Each item uses the same shape as `expenses.create`. |

A single pending action is created for the whole batch. Idempotency key is derived from the sorted hashes of the per-item `expenses.create` keys, so reorderings collide.

Returns: `{ pending_action_id, status, idempotency_key, item_count, auto_applied }`.

### `expenses.categorize`

| field | type | description |
|---|---|---|
| `expense_id` | int | Required. The expense must belong to the authenticated tenant (enforced via the global tenant scope). |
| `category` | string | Required. |
| `subcategory` | string | Optional. |
| `confidence` | number | Optional 0-1 score. |

Returns: `{ pending_action_id, status, idempotency_key, auto_applied }`.

## Write tools (Phase 4)

Phase 4 widens the email-ingestion agent's reach. Each tool follows the same shape as the Phase 2 tools: validate via the controller's FormRequest, call the module's service, record agent attribution on the new row, and queue the result for human approval. Idempotency keys are deterministic per natural fields and include `source_email_id` where available.

### `subscriptions.create`

| field | type | description |
|---|---|---|
| `service_name` | string | Required. |
| `cost` | number | Required. |
| `currency` | string | Defaults to MKD. |
| `billing_cycle` | string | Required (`"monthly"`, `"yearly"`, `"weekly"`, `"custom"`). |
| `billing_cycle_days` | int | When `billing_cycle = "custom"`. |
| `start_date` | date | Required. |
| `next_billing_date` | date | Optional. |
| `category` | string | Optional. |
| `payment_method` | string | Optional. |
| `auto_renewal` | bool | Optional. |
| `source_email_id` | string | Optional, used for idempotency disambiguation. |

Idempotency key: `sha256("subscriptions.create|<tenant>|<service_normalized>|<currency>|<cycle>|<source_email_id>")`.

### `contracts.create`

| field | type | description |
|---|---|---|
| `title` | string | Required. |
| `counterparty` | string | Required. |
| `contract_type` | string | Optional. |
| `start_date` | date | Required. |
| `end_date` | date | Optional. |
| `notice_period_days` | int | Optional. |
| `auto_renewal` | bool | Optional. |
| `contract_value` | number | Optional. |
| `payment_terms` | string | Optional. |
| `notes` | string | Optional. |
| `source_email_id` | string | Optional, used for idempotency. |

### `warranties.create`

| field | type | description |
|---|---|---|
| `product_name` | string | Required. |
| `brand` | string | Optional. |
| `model` | string | Optional. |
| `serial_number` | string | Optional, included in idempotency key when present. |
| `purchase_date` | date | Required. |
| `purchase_price` | number | Optional. |
| `retailer` | string | Optional. |
| `warranty_duration_months` | int | Optional. |
| `warranty_expiration_date` | date | Required. |
| `warranty_type` | string | Optional. |
| `warranty_terms` | string | Optional. |
| `source_email_id` | string | Optional. |

### `iou.create`

| field | type | description |
|---|---|---|
| `type` | string | `"owe"` or `"owed"`. Required. |
| `person_name` | string | Required. |
| `amount` | number | Required. |
| `currency` | string | Defaults to MKD. |
| `transaction_date` | date | Required. |
| `due_date` | date | Optional. |
| `description` | string | Required. |
| `category` | string | Optional. |
| `source_email_id` | string | Optional. |

### `utilityBills.create`

| field | type | description |
|---|---|---|
| `utility_type` | string | Required. |
| `service_provider` | string | Required. |
| `account_number` | string | Optional. |
| `service_address` | string | Optional. |
| `bill_amount` | number | Required. |
| `currency` | string | Defaults to MKD. |
| `usage_amount` | number | Optional. |
| `usage_unit` | string | Optional. |
| `bill_period_start` | date | Optional. |
| `bill_period_end` | date | Optional, included in idempotency. |
| `due_date` | date | Required. |
| `source_email_id` | string | Optional. |

### `jobs.updateStatus`

| field | type | description |
|---|---|---|
| `job_application_id` | int | Required. Application must belong to the authenticated tenant. |
| `status` | string | Required. New pipeline status. |
| `next_action_at` | datetime | Optional. ISO 8601. |
| `source_email_id` | string | Optional. |

### `jobs.addInterview`

| field | type | description |
|---|---|---|
| `job_application_id` | int | Required. |
| `scheduled_at` | datetime | Required. ISO 8601. |
| `interview_type` | string | Optional. |
| `interviewer_name` | string | Optional. |
| `location` | string | Optional. |
| `notes` | string | Optional. |
| `source_email_id` | string | Optional. |

## Write tools (Phase 5)

Phase 5 adds the investments-sync agent's tool surface. None of these create the parent `Investment` row — that's a one-time manual setup the user does. All four record activity *against* an existing investment.

### `investments.recordTransaction`

| field | type | description |
|---|---|---|
| `investment_id` | int | Required. Must belong to the authenticated tenant. |
| `transaction_type` | string | Required. One of `buy`, `sell`, `dividend_reinvestment`, `transfer_in`, `transfer_out`, `stock_split`, `stock_dividend`. |
| `quantity` | number | Required. |
| `price_per_share` | number | Required. |
| `total_amount` | number | Auto-computed from `quantity * price_per_share` if absent. |
| `fees`, `taxes` | number | Optional. |
| `transaction_date` | date | Required. |
| `settlement_date` | date | Optional. |
| `order_id` | string | Broker order id. **Strongest idempotency anchor.** |
| `confirmation_number` | string | Broker confirmation number. Fallback idempotency anchor. |
| `broker` | string | Optional. |
| `currency` | string | ISO 4217. Defaults to investment currency. |
| `notes` | string | Optional. |
| `source_email_id` | string | Optional Gmail message id. |

Idempotency: `order_id` (when present) → `confirmation_number` (when present) → `(investment, type, qty, price, date, source_email_id)`.

### `investments.recordDividend`

| field | type | description |
|---|---|---|
| `investment_id` | int | Required. |
| `amount` | number | Required. Total dividend amount. |
| `payment_date` | date | Required. |
| `record_date`, `ex_dividend_date` | date | Optional. |
| `dividend_type` | string | Optional. `"ordinary"`, `"qualified"`, `"special"`, etc. |
| `frequency` | string | Optional. |
| `dividend_per_share`, `shares_held` | number | Optional. |
| `tax_withheld` | number | Optional. |
| `currency` | string | Defaults to investment currency. |
| `reinvested` | bool | Optional. |
| `notes` | string | Optional. |
| `source_email_id` | string | Optional. |

Idempotency: `(investment, payment_date, amount, source_email_id)`.

### `investments.repriceLot`

Mark-to-market — update an investment's per-share `current_value`.

| field | type | description |
|---|---|---|
| `investment_id` | int | Required. |
| `current_value` | number | Required. New per-share value. |
| `as_of` | date | Defaults to today. |

Idempotency: `(investment, as_of)`. Re-running on the same date collapses to the same pending action so the agent can call this once per position per session safely. Revert restores the prior `current_value` and `last_price_update`.

### `investments.bulkImportTransactions`

Queue an entire brokerage statement as a single pending action.

| field | type | description |
|---|---|---|
| `items` | array | Each item uses the same shape as `investments.recordTransaction`. |

Idempotency: derived from the sorted hashes of the per-item `recordTransaction` keys, so reorderings collide.

## Bank reconciliation tools (Phase 6)

Phase 6 ships the bank-statements agent. It ingests parsed lines from a brokerage / card statement, runs them through the reconciliation matcher, and links each line to the existing expense it represents (so a single purchase doesn't end up as two separate rows). High-confidence matches link automatically; the rest are saved as `unmatched` for human review at `/dashboard/pending-actions` (with the matcher's top-3 candidates available via `bank.unmatched`).

### Matcher rules (deterministic, no ML)

For each bank line, candidate expenses must be in the same tenant, use the same currency, and have an absolute amount (in cents) that matches exactly. Within that pool, each candidate gets a score:

- Base 0.5 (amount + currency match).
- +0.30 for same date, +0.20 for ±1 day, +0.10 for ±3 days.
- +0.20 if merchant similarity ≥80%, +0.10 if ≥50% (PHP `similar_text`, normalized to lowercase / no punctuation).

Auto-link rules: best candidate score ≥ 0.85 **and** at least 0.10 above the runner-up. Otherwise the line stays unmatched and the top-3 candidates are persisted in `match_candidates` for review. An expense already linked to another bank line is excluded from the candidate pool.

### `bank.recordLines`

Submit parsed lines from a single statement as one pending action.

| field | type | description |
|---|---|---|
| `lines` | array | Required. Each item: |
| `lines[].account` | string | Required. Same string for every row in the statement. |
| `lines[].posted_at` | date | Required. YYYY-MM-DD. |
| `lines[].amount_cents` | int | Required. Signed (negative = debit). |
| `lines[].currency` | string | Required. ISO 4217. |
| `lines[].merchant_raw` | string | Optional. Bank's printed merchant string. |
| `lines[].description` | string | Optional. Full row text. |
| `lines[].balance_after_cents` | int | Optional. |
| `lines[].statement_id` | string | Optional. Statement document id. |
| `lines[].statement_row` | int | Optional. 1-based row index within the statement. |

Server-side computed fields per line: `fingerprint` (deterministic SHA-256 over natural fields). Re-importing the same statement collapses to the same pending action because the per-line fingerprints match.

Idempotency: derived from the sorted set of per-line fingerprints.

### `bank.linkExpense`

| field | type | description |
|---|---|---|
| `bank_line_id` | int | Required. |
| `expense_id` | int | Required. |

Forces a link regardless of matcher confidence. Used when the agent (or a human reviewer) knows better than the matcher.

### `bank.unmatched`

Read-only triage helper.

| field | type | description |
|---|---|---|
| `within_days` | int | Default 30. |
| `account` | string | Optional filter. |
| `limit` | int | Default 100, max 500. |

Returns `{ count, within_days, items[] }`. Each item carries the line plus the matcher's persisted top-3 candidates from `match_candidates`.

## Receipts / OCR (Phase 7)

Phase 7 adds the receipts-OCR agent. It uses Anthropic vision via the Drive MCP to extract structured fields from receipt photos and PDFs, then writes through the **existing** create-style tools — no new write tools per se, but every relevant tool now accepts a `source_file_id` and includes it in the idempotency key so re-running OCR over the same Drive file collapses to one pending action.

### Tools updated to accept `source_file_id`

| tool | new field | semantics |
|---|---|---|
| `expenses.create` | `source_file_id` | Drive file id when extracted from a receipt scan or PDF. Combined with `source_email_id` in the idempotency key. |
| `warranties.create` | `source_file_id` | Drive file id of the warranty document or original receipt. |
| `utilityBills.create` | `source_file_id` | Drive file id when the bill arrived as a PDF rather than an email. |

Backward-compatibility: when `source_file_id` is absent, the legacy idempotency-key encoding is preserved verbatim, so existing pending-action rows continue to dedupe across agent runs. When `source_file_id` is present, the encoding extends with a `|file:<id>` suffix (still deterministic).

### `receipts.processed` (read)

| field | type | description |
|---|---|---|
| `within_days` | int | Default 60. Limit search to actions created in the last N days. |
| `limit` | int | Default 500, max 2000. |

Returns `{ count, within_days, items[] }` where each item is `{ source_file_id, tool, pending_action_id, first_seen_at }`. The agent calls this **once** at the start of a session and skips Drive files whose ids are in the result set, saving a vision call per skipped file.

The tool walks both single-payload tools (`expenses.create`, `warranties.create`, `utilityBills.create`) and bulk-style tools (`expenses.bulkImport`) so any agent that attached `source_file_id` is reflected.

## Job-search hunter (Phase 8)

Phase 8 ships a single new write tool plus the `job-search` agent definition and two new skills (`cv`, `job-criteria`). The agent is intentionally narrow: it discovers postings and records them — it never applies on the user's behalf.

### `jobs.createApplication`

| field | type | description |
|---|---|---|
| `company_name` | string | Required. |
| `job_title` | string | Required. |
| `job_description` | string | Optional, one-paragraph summary. |
| `job_url` | url | Optional canonical URL. |
| `location` | string | Optional. |
| `remote` | bool | Optional. |
| `salary_min` | number | Optional. |
| `salary_max` | number | Optional. |
| `currency` | string | ISO 4217. Defaults to MKD. |
| `status` | string | `"discovered"` (default) or `"shortlisted"`. |
| `source` | string | One of `linkedin`, `company_website`, `job_board`, `referral`, `recruiter`, `networking`, `other`. |
| `priority` | int | 1-3 only. (4-5 are reserved for the user.) |
| `contact_name`, `contact_email` | string | Optional. |
| `notes` | string | Required-by-convention. One-line rationale. |
| `source_email_id` | string | Optional Gmail message id. Strongest idempotency anchor. |
| `source_file_id` | string | Optional Drive file id (for saved listings). |

Idempotency: `(tenant, normalized company, normalized title, source_email_id || source_file_id || lowercased job_url)`. Re-runs over the same email or canonical URL collapse to the same pending action. Cross-channel discovery (e.g. once via LinkedIn email, once via the company's career page) deliberately produces *separate* pending actions because the `source` differs and the URLs aren't identical — the user merges them manually if needed.

### Skills

- **`cv`** — the user's CV. Authoritative for fit assessment. Replace placeholder content before enabling the agent.
- **`job-criteria`** — hard and soft criteria, plus an active-search-window date range. The agent exits immediately if today is outside the window or if either skill is still placeholder content.

## Cycle menu (Phase 9)

Phase 9 adds three tools backing the cycle-menu-planner agent. The agent never creates the parent `CycleMenu` row (the user owns the rotation), and never overwrites a populated day — it only fills empty days.

### `cycleMenu.addItem` (write)

| field | type | description |
|---|---|---|
| `cycle_menu_id` | int | Required. |
| `day_index` | int | Required. 0-based day in the rotation. |
| `title` | string | Required. |
| `meal_type` | string | Required. `"breakfast"`, `"lunch"`, `"dinner"`, `"snack"`, or `"other"`. |
| `time_of_day` | string | Optional `HH:MM`. |
| `quantity` | string | Optional free-text serving (e.g. `"1 bowl"`, `"250 g"`). |

Idempotency: `(tenant, menu, day_index, normalized title, meal_type)`.

### `cycleMenu.setWeek` (write, bulk)

| field | type | description |
|---|---|---|
| `cycle_menu_id` | int | Required. |
| `items_by_day_index` | object | Required. Map of `day_index → array of items`. Each item = `{ title, meal_type, time_of_day?, quantity? }`. |

Existing items on every covered `day_index` are deleted on apply; revert fully restores them. Idempotency is order-insensitive within a day and across days.

### `cycleMenu.shoppingList` (read)

| field | type | description |
|---|---|---|
| `cycle_menu_id` | int | Optional — defaults to the tenant's active menu. |
| `window_days` | int | Default 7, max 30. |

Returns `{ menu, window_days, item_count, items[] }` where each item is `{ title, meal_type, count, quantities[], days[] }`. Quantities are returned as a list rather than summed because the schema carries free-text (`"1 bowl"`, `"250 g"`).

## Weekly digest (Phase 10)

Phase 10 ships the weekly-digest agent. The agent itself is read-only across LifeOS data; the only write tool it uses is `digest.send`, which queues the email body. On apply, the email is dispatched via Laravel Mail and a row is recorded in `digest_logs`.

### `digest.send` (write)

| field | type | description |
|---|---|---|
| `week_starts_on` | date | Required. YYYY-MM-DD of the Monday of the digest week. **Idempotency anchor.** |
| `subject` | string | Required. Email subject. |
| `body_text` | string | Required. Plaintext (Markdown OK; renders as preformatted text). |
| `body_html` | string | Optional. Pre-rendered HTML body. |
| `recipient_email` | email | Optional. Defaults to the bound user's email. |
| `structured_summary` | object | Optional. Machine-readable highlights for archive use. |

Idempotency: `(tenant, week_starts_on)`. Re-running the agent on the same Sunday returns the same pending action. The unique `(tenant_id, week_starts_on)` constraint on `digest_logs` is the second line of defence — if two pending actions for the same week ever both apply, only one row writes and only one email sends.

### Auto-apply exception

Most write tools require the **same idempotency key** to have been previously approved before auto-apply fires. `digest.send` is the one documented exception: by design every week's key differs, so the rule is relaxed to "any prior `digest.send` approved by the same tenant in the last 90 days." The exception lives in `PendingActionApplier::autoApplyAllowsAnyPriorApproval()` and is unit-tested.

To enable Sunday-night auto-send: approve one weekly digest manually, then set `tenants.tool_auto_apply.digest.send = true`. From the next week on the digest sends without intervention.

### Revert behaviour

The email can't be unsent. Revert deletes the `digest_logs` row so the unique constraint allows a corrected digest to be re-queued for the same week if needed. The original email already in your inbox is on you.

## Approval surface

Reviewers act through `/dashboard/pending-actions`:

- Index (filterable list) with bulk-approve.
- Detail page with payload, applied diff (when applied), and approve / reject (with reason) / revert (within a 10-minute window) actions.
- Sidebar shows a count badge fed by a global Inertia share `pendingActions.count`.

`PendingActionPolicy` gates view, approve, reject, revert. Revert is only allowed for `applied` actions inside the configurable window.
