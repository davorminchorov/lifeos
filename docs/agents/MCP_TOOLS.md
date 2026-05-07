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

## Approval surface

Reviewers act through `/dashboard/pending-actions`:

- Index (filterable list) with bulk-approve.
- Detail page with payload, applied diff (when applied), and approve / reject (with reason) / revert (within a 10-minute window) actions.
- Sidebar shows a count badge fed by a global Inertia share `pendingActions.count`.

`PendingActionPolicy` gates view, approve, reject, revert. Revert is only allowed for `applied` actions inside the configurable window.
