# LifeOS GitHub project management — setup plan (Phase 0)

This document is the Phase 0 deliverable for the GitHub project management
setup work tracked on branch `claude/setup-github-project-DQvtF`.

It is a proposal. Nothing is created until you approve.

---

## 1. Repo state today

Inspected on 2026-05-07.

### What already exists

- `.github/workflows/` with `claude.yml`, `junie.yml`, `phpunit.yml`. No issue
  templates, no `PULL_REQUEST_TEMPLATE/` directory, no `CODEOWNERS`.
- `PULL_REQUEST_TEMPLATE.md` lives at the repo root (not under `.github/`).
- `docs/` contains only `docs/designs/ai-assistant-panel.md`. No
  `docs/agents/` directory. The required `docs/agents/CLAUDE_CODE_BRIEF.md`
  is **missing**.
- `CLAUDE.md` and `AGENTS.md` are byte-identical Laravel Boost guideline
  files. They contain no agent workflow content yet.
- One open issue: **#23** "[Junie] Implement the Trading 212 API
  integration" (module: investments, type: feature).
- Recent merged PRs are mostly autonomous Claude/Junie fixes, so the agent
  loop is already in active use; this work formalizes it.
- Branch `claude/setup-github-project-DQvtF` exists locally and on origin.
  Working tree clean.

### What does NOT exist (from quick inspection)

- No issue forms / templates.
- No labels visible to me from this environment (see tool gap below). I will
  treat the label namespace as empty and create labels idempotently when
  unblocked.
- No `docs/agents/` directory at all.
- No Definition of Ready, Workflow, or Migration docs.
- No GitHub Project v2 board linked to the repo (cannot verify from here —
  see tool gap).
- No milestones referenced in any open issue or recent PR.

---

## 2. Tool gap — needs your decision

The GitHub MCP server in this session exposes:

- `get_label` (single, by name) — no list/create/update/delete.
- Issue read/write, PR read/write, file commit, branch create.
- No milestones API.
- No Projects v2 API (no field/view/automation tools).

`gh` CLI is **not installed** in this sandbox. So this session can deliver
Phases 0, 2, 3, 5 (all file-based) end to end. **Phases 1, 4, 6, 7 cannot be
executed from here**, because they need label CRUD, Projects v2 GraphQL,
and bulk issue label edits.

Recommended options for the gated phases (pick one):

- **A. CI-driven.** I commit a `.github/workflows/project-bootstrap.yml`
  workflow plus a `scripts/github/bootstrap.sh` (idempotent, uses `gh` and
  the GraphQL API). You trigger it manually after merging the plan PR.
  Best for reproducibility.
- **B. You run a script locally.** I commit the same script, you run it
  from your machine with `gh auth login` already done.
- **C. You execute the manual steps yourself** from the GitHub UI using
  this plan as the spec. Slowest, most flexible.

I recommend **A** because it is idempotent, auditable, and re-runnable.

---

## 3. The bigger blocker — the missing brief

The prompt says label phase numbers and seed issues must align with the
agent implementation phases described in `docs/agents/CLAUDE_CODE_BRIEF.md`.
That file does not exist. I do not know what Phase 0 through Phase 10 are
meant to be.

**Please choose how to resolve before I move past Phase 0:**

- (i) Paste the brief here so I can write it to `docs/agents/CLAUDE_CODE_BRIEF.md`
  and seed issues from it.
- (ii) Tell me to draft a brief from the existing repo state (modules,
  Managed Agents, MCP, etc.) for your review — slower, you'll want to edit
  it heavily.
- (iii) Skip Phase 7 (seed issues) and the `phase:*` labels for now, and
  do them once the brief lands.

Without one of these, Phase 7 cannot run, and Phase 1's `phase:0..phase:10`
labels become guesses.

---

## 4. Module taxonomy — proposed

Modules from `README.md` map cleanly to the labels you specified:

| Label | Source |
| --- | --- |
| `module:subscriptions` | README "Payment Subscriptions Tracking" |
| `module:contracts` | README "Contracts Management" |
| `module:warranties` | README "Warranties Tracking" |
| `module:investments` | README "Investments Portfolio" |
| `module:expenses` | README "Expenses Management" |
| `module:utility-bills` | README "Utility Bills Tracking" |
| `module:iou` | README "IOU / Debt Tracking" |
| `module:budgets` | README "Budget Management" |
| `module:job-applications` | README "Job Application Tracking" |
| `module:cycle-menu` | README "Cycle Menu (MVP)" |
| `module:notifications` | README "Notification System" |
| `module:dashboard` | README "Unified Dashboard" |
| `module:agents` | the Managed Agents work (per spec) |
| `module:mcp` | the LifeOS MCP server (per spec) |
| `module:infra` | Docker/CI/tenancy/deploys (per spec) |

### Open questions on modules

- The codebase contains a **substantial Invoicing module** (models
  `Invoice`, `InvoiceItem`, `RecurringInvoice`, `CreditNote`, `Payment`,
  `Refund`, `TaxRate`, `Sequence`, `Customer`, `Discount`, plus three
  `INVOICING_*.md` docs at repo root). It is not in the README feature
  list. Do you want a `module:invoicing` label? My recommendation: **yes**,
  since real code already exists; flag during your review.
- Cross-cutting concerns I considered as labels but did not propose, to
  keep the list short: `module:currency`, `module:tenancy`, `module:auth`,
  `module:ai`. If you want any of these as their own label, say so. By
  default I'll roll them into `module:infra` (tenancy/auth/currency) and
  `module:agents` (AI).

---

## 5. Final label taxonomy

Color families chosen to be distinct on the issues UI. Hex without `#`.

### Type — blue family

| Name | Color | Description |
| --- | --- | --- |
| `type:feature` | `1F6FEB` | New functionality |
| `type:bug` | `D73A4A` | Defect (red, kept distinct intentionally) |
| `type:chore` | `8B949E` | Maintenance, refactor, deps |
| `type:docs` | `0E8A16` | Documentation only |
| `type:spike` | `5319E7` | Research / no production code |

### Priority — orange/red family

| Name | Color | Description |
| --- | --- | --- |
| `priority:p0` | `B60205` | Drop everything |
| `priority:p1` | `D93F0B` | This week |
| `priority:p2` | `F9A825` | This month |
| `priority:p3` | `FBCA04` | Someday |

### Module — teal family (`0F766E` base, lighter shades per module)

All modules share base `0F766E`. Easier to scan as a group; individual
distinguishability is provided by the label text itself.

| Name | Description |
| --- | --- |
| `module:subscriptions` | Recurring payment tracking |
| `module:contracts` | Contracts and renewals |
| `module:warranties` | Product warranties |
| `module:investments` | Portfolio, holdings, transactions |
| `module:expenses` | Expense tracking |
| `module:utility-bills` | Utility bills |
| `module:iou` | IOU / debt tracking |
| `module:budgets` | Budgets |
| `module:job-applications` | Job applications pipeline |
| `module:cycle-menu` | Cycle menu / meal plan |
| `module:notifications` | Notification system |
| `module:dashboard` | Unified dashboard |
| `module:agents` | Managed Agents |
| `module:mcp` | LifeOS MCP server |
| `module:infra` | Docker, CI, tenancy, deploys |
| `module:invoicing` | (proposed; remove if you reject) |

### Workflow — purple family

| Name | Color | Description |
| --- | --- | --- |
| `status:needs-triage` | `C5DEF5` | Default for new issues |
| `status:needs-info` | `FBCA04` | Blocked on owner |
| `status:ready` | `0E8A16` | Passes Definition of Ready |
| `agent-eligible` | `7B61FF` | Ready to hand to `@claude` |
| `agent-in-progress` | `5319E7` | Claude is working on it |
| `agent-blocked` | `B60205` | Claude needs human input |
| `needs-review` | `1F6FEB` | PR ready for review |
| `wontfix` | `FFFFFF` | Closed without action |
| `duplicate` | `CFD3D7` | Closed as duplicate |

### Phase — grey family (`6E7681`)

`phase:0` through `phase:10`. **Will not be created in Phase 1 of this
work** until the brief lands and I know what each phase means.

---

## 6. Issue template list

All under `.github/ISSUE_TEMPLATE/`:

- `feature.yml` — form-based; goal, user-visible behavior, acceptance
  criteria checklist, module dropdown, out of scope, references. Auto-
  applies `type:feature`, `status:needs-triage`.
- `bug.yml` — what happened, expected, repro, environment (browser /
  mobile / desktop / tenant), severity dropdown
  (data-loss/broken/degraded/cosmetic), module dropdown. Auto-applies
  `type:bug`, `status:needs-triage`.
- `agent-task.yml` — opinionated form. Goal one-sentence, why,
  acceptance criteria, files likely involved (required), out of scope
  (required), test expectations (required), references, phase dropdown,
  module dropdown, "ready for autonomous pickup" checkbox. Auto-applies
  `type:feature`/`type:chore` (dropdown), `status:needs-triage`, plus
  `agent-eligible` if the box is checked. Description links to the
  Definition of Ready.
- `chore.yml` — goal, rationale, module, done-when. Auto-applies
  `type:chore`, `status:needs-triage`.
- `config.yml` — `blank_issues_enabled: false`, contact link to repo
  Discussions (placeholder URL — confirm or replace).

---

## 7. Definition of Ready (`docs/agents/DEFINITION_OF_READY.md`)

Checklist:

- [ ] Goal in one sentence.
- [ ] Acceptance criteria are concrete and testable.
- [ ] Module label applied.
- [ ] Files likely involved listed (best-guess OK).
- [ ] Out-of-scope explicit.
- [ ] Test expectations stated.
- [ ] No open questions in body.
- [ ] If multi-tenant, tenancy implications noted.
- [ ] If financial writes, Pending Actions queue behavior specified.

Referenced from `CLAUDE.md` (will append a short section) and from
`agent-task.yml`.

---

## 8. GitHub Project v2 — proposed

Single Project named **"LifeOS"** linked to the repo.

### Fields

| Field | Type | Options |
| --- | --- | --- |
| Status | single-select | Backlog, Ready, In Progress, In Review, Blocked, Done |
| Priority | single-select | P0, P1, P2, P3 |
| Module | single-select | one option per `module:*` label |
| Effort | single-select | XS, S, M, L, XL |
| Phase | single-select | phase:0..phase:10, n/a |
| Agent | single-select | not-eligible, eligible, in-progress, blocked, done |
| Iteration | iteration | 2-week iterations starting next Monday (2026-05-11) |

### Views

- **Board** — group by Status; filter `iteration:@current OR status:Backlog`.
- **Agent Queue** — table; filter `label:agent-eligible status:Ready,In Progress,Blocked`; sort Priority asc, Effort asc.
- **By Module** — board grouped by Module; filter `is:open`.
- **Phase Roadmap** — board grouped by Phase; filter `is:open`.
- **My Week** — table; filter `iteration:@current`.

### Built-in workflows

- Auto-add new repo issues → Status = Backlog.
- Issue closed → Status = Done.
- PR opened linking issue → linked issue Status = In Review.
- PR merged → linked issues Status = Done.

---

## 9. Migration plan for existing issues

The only open issue is **#23**. Proposed migration:

| # | Title (truncated) | Proposed labels | Project status | Notes |
| --- | --- | --- | --- | --- |
| 23 | [Junie] Trading 212 API integration | `type:feature`, `module:investments`, `priority:p2`, `status:needs-triage` | Backlog | Body untouched. Belongs to investments module clearly. Effort guess: L. |

Closed issues will not be touched.

---

## 10. Seed issues (Phase 7) — placeholder

**Cannot be drafted without `docs/agents/CLAUDE_CODE_BRIEF.md`.** Once you
provide that brief (option 4.i), I will:

- Create one parent tracking issue "LifeOS Managed Agents implementation"
  with `module:agents`, no `phase` label, Effort = XL.
- Create 11 sub-issues (`phase:0`..`phase:10`), each using
  `agent-task.yml`, body = the phase summary from the brief, labels
  `module:agents` + the right `phase:N`. `agent-eligible` only on
  Phase 0.
- Link sub-issues from the tracking issue using GitHub's sub-issue API.

If you choose option 4.iii (skip seeds for now), I will skip this phase
entirely until the brief exists.

---

## 11. Workflow doc (`docs/agents/WORKFLOW.md`)

Will document the loop — Capture → Triage → Ready check → Pickup → Review
→ Merge — with `<!-- TODO screenshot -->` placeholders, and reference both
the templates and the Definition of Ready.

---

## 12. Open questions for you

1. **CLAUDE_CODE_BRIEF.md is missing.** Provide it, ask me to draft one,
   or defer Phase 7 + `phase:*` labels?
2. **`module:invoicing`** — add it (recommended) or skip until invoicing
   lands in the README?
3. **Tool gap** — choose execution path A (CI workflow), B (local script),
   or C (manual)? I recommend A.
4. **PR template location.** A `PULL_REQUEST_TEMPLATE.md` already exists
   at the repo root. Move it under `.github/` for clarity, or leave it?
   This isn't in scope for the prompt; flag only.
5. **Contact links** in `config.yml` — point to GitHub Discussions
   (need to confirm Discussions is enabled), or to your email/handle?
6. **Iteration start date** — confirm 2026-05-11 (next Monday) is fine.

---

## 13. Quality bars I will hold

- Idempotent: detect existing artifacts before creating; never duplicate.
- Non-destructive: never delete labels, close issues, or remove project
  items I didn't create.
- Verifiable: each phase ends with a list of created artifacts and URLs.
- Stoppable: I pause for your approval between phases.
- No commits to `main`: everything goes through a PR titled
  `chore: GitHub project management setup` against `main` from
  `claude/setup-github-project-DQvtF`, with this plan in the description.
