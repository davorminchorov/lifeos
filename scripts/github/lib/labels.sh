#!/usr/bin/env bash
# Sync the LifeOS label taxonomy. Idempotent: never deletes, always
# upserts (creates or edits) so that color and description stay in sync.
#
# Source of truth lives in this file. To add a new label, append a line
# to LABELS.

# Each row: name | color (hex without #) | description
# Module rows share base color 0F766E intentionally.
read -r -d '' LABELS <<'EOF' || true
type:feature|1F6FEB|New functionality
type:bug|D73A4A|Defect
type:chore|8B949E|Maintenance, refactor, or dependency work
type:docs|0E8A16|Documentation only
type:spike|5319E7|Research or exploratory; no production code expected
priority:p0|B60205|Drop everything
priority:p1|D93F0B|This week
priority:p2|F9A825|This month
priority:p3|FBCA04|Someday
module:subscriptions|0F766E|Recurring payment tracking
module:contracts|0F766E|Contracts and renewals
module:warranties|0F766E|Product warranties
module:investments|0F766E|Portfolio, holdings, transactions
module:expenses|0F766E|Expense tracking
module:utility-bills|0F766E|Utility bills
module:iou|0F766E|IOU / debt tracking
module:budgets|0F766E|Budgets
module:job-applications|0F766E|Job applications pipeline
module:cycle-menu|0F766E|Cycle menu / meal plan
module:notifications|0F766E|Notification system
module:dashboard|0F766E|Unified dashboard
module:invoicing|0F766E|Invoicing, credit notes, payments
module:agents|0F766E|Managed Agents
module:mcp|0F766E|LifeOS MCP server
module:infra|0F766E|Docker, CI, tenancy, deploys
status:needs-triage|C5DEF5|Default for new issues
status:needs-info|FBCA04|Blocked on owner clarification
status:ready|0E8A16|Passes Definition of Ready
agent-eligible|7B61FF|Ready to hand to an autonomous agent
agent-in-progress|5319E7|Claude is working on it
agent-blocked|B60205|Agent needs human input
needs-review|1F6FEB|PR ready for review
wontfix|FFFFFF|Closed without action
duplicate|CFD3D7|Closed as duplicate
EOF

# Note: phase:0..phase:10 labels are deferred. They will be added once
# docs/agents/CLAUDE_CODE_BRIEF.md exists and we know what each phase
# represents. To add them later, append rows here and re-run.

label_exists() {
  local name="$1"
  gh label view "$name" --repo "$REPO_FULL" >/dev/null 2>&1
}

upsert_label() {
  local name="$1"
  local color="$2"
  local desc="$3"

  if label_exists "$name"; then
    log "Updating label: $name"
    run gh label edit "$name" --repo "$REPO_FULL" --color "$color" --description "$desc"
  else
    log "Creating label: $name"
    run gh label create "$name" --repo "$REPO_FULL" --color "$color" --description "$desc"
  fi
}

sync_labels() {
  while IFS='|' read -r name color desc; do
    [[ -z "${name:-}" ]] && continue
    upsert_label "$name" "$color" "$desc"
  done <<<"$LABELS"
  log "Label sync complete."
}
