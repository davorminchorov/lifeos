# Definition of Ready

An issue is **Ready** — and may be tagged `agent-eligible` or handed to
`@claude` — only when every box below is checked. If even one is unchecked,
the issue stays in `status:needs-triage` or `status:needs-info`.

This bar exists because autonomous agents produce useful PRs in proportion
to how well-scoped the task is. Vague input produces vague output, and
vague output costs review time.

## Checklist

- [ ] **Goal in one sentence.** Anyone reading the title and first line
      understands what we're building.
- [ ] **Acceptance criteria are concrete and testable.** Each bullet
      describes an observable behavior or a verifiable code property — not
      a wish.
- [ ] **Module label applied** (`module:*`).
- [ ] **Files likely involved are listed.** A best guess is fine. Even an
      imperfect guess saves the agent navigation time.
- [ ] **Out-of-scope is explicit.** Anything the agent must not touch.
- [ ] **Test expectations are stated.** What tests should exist when the
      PR lands?
- [ ] **No open questions in the issue body.** "TBD", "we should decide",
      or "?" anywhere = not ready.
- [ ] **Tenancy implications noted** if the change touches multi-tenant
      code (queries on tenant-scoped models, anything under
      `app/Scopes/TenantScope.php`, anything that hits `tenants` or
      `tenant_members`, etc.).
- [ ] **Pending Actions queue behavior specified** if the change performs
      financial writes (anything that creates or mutates rows in
      financial tables — `Investment`, `InvestmentTransaction`,
      `Expense`, `Payment`, `Invoice`, `IOU`, `Subscription`, etc.).

## When this checklist fails

- Missing one or two items → leave the issue with `status:needs-info` and
  `@`-mention the owner with the specific gap.
- More than two missing → drop back to `status:needs-triage`. Probably
  needs a fresh draft.

## How this is enforced

- The `agent-task.yml` issue template surfaces every required field as a
  form input — most of them are required at submit time.
- A `agent-eligible` label without these fields is a process bug. Anyone
  may remove it and ping the submitter.
- The Phase 5 workflow doc describes the weekly triage loop where this
  checklist is applied.
