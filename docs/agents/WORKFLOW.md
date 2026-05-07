# LifeOS agent workflow

How work moves from idea to merged code in this repo. Optimized for
single-maintainer + autonomous agent (Claude / Junie) collaboration.

## The loop

```
Capture → Triage → Ready check → Pickup → Review → Merge
```

### 1. Capture

Describe the work in plain language. Two paths:

- **Chat with Claude** (web or app). Ask Claude to draft an issue. Claude
  picks the right template (`feature.yml`, `bug.yml`, `agent-task.yml`,
  or `chore.yml`) and files it through the GitHub MCP.
- **GitHub UI directly.** New issue → pick the template → fill the form.

Either way, the issue lands with `status:needs-triage` plus the type
label that the template applied.

<!-- TODO screenshot: new issue template picker -->

### 2. Triage (weekly)

Once a week — or whenever the queue grows past ~10 unstaffed issues —
move the new arrivals from `status:needs-triage` to `status:ready`:

- Apply a `module:*` label.
- Apply a `priority:*` label (P0..P3).
- Set Effort on the project (XS..XL).
- If you have questions, set `status:needs-info` and `@`-mention the
  submitter (often yourself, after the chat-driven capture).

<!-- TODO screenshot: project board "Backlog" column with triage filters -->

### 3. Ready check

For each issue you intend to hand to an agent, verify it passes the
[Definition of Ready](DEFINITION_OF_READY.md). When it does, apply
`agent-eligible`.

This is a one-minute check. The form fields in `agent-task.yml` already
cover most of it — you're mostly verifying that no field was filled with
"TBD" and that the acceptance criteria are testable, not aspirational.

### 4. Pickup

Two flavors of pickup:

- **Autonomous.** Comment `@claude` on the issue. The agent picks it up,
  opens a branch, implements, and opens a PR. Apply
  `agent-in-progress` when this starts and watch for `agent-blocked` if
  Claude needs clarification.
- **Local.** Open Claude Code in the repo and say
  "implement issue #N." Same outcome, faster iteration on tricky
  problems where you want to nudge mid-flight.

For non-agent work, just open the branch yourself.

<!-- TODO screenshot: PR opened by Claude with issue link -->

### 5. Review

Review the PR. To request changes:

- Plain GitHub review comments work for non-agent PRs.
- For agent-authored PRs, prefix follow-ups with `@claude` to trigger
  another autonomous round, e.g. "@claude please add a test for the
  empty-array case."
- If something deeper is wrong, request changes in plain language —
  the agent reads the whole review thread.

Apply `needs-review` if the PR is waiting on you specifically; remove it
once you've reviewed.

### 6. Merge

Squash-merge with a clean title. The board automation moves linked
issues to **Done** automatically. The branch is deleted by the workflow
or by you, manually.

## Project board views

The board is at the GitHub Project named **LifeOS** (linked to this
repo). Five views, picked by what you're doing:

- **Board** — your default landing view. Kanban grouped by Status,
  filtered to current iteration plus Backlog.
- **Agent Queue** — what's eligible for autonomous pickup right now.
  Sorted by Priority then Effort so the cheapest-and-most-important
  issue is on top.
- **By Module** — when you want to think within one module.
- **Phase Roadmap** — the Managed Agents implementation roadmap.
- **My Week** — the current iteration only.

<!-- TODO screenshot: "Agent Queue" view -->

## Labels at a glance

- `type:*` — feature / bug / chore / docs / spike.
- `priority:*` — p0..p3.
- `module:*` — one per module from the README plus `agents`, `mcp`,
  `infra`, and `invoicing`.
- `status:*` — needs-triage, needs-info, ready.
- `agent-*` — eligible, in-progress, blocked.
- `needs-review`, `wontfix`, `duplicate`.
- `phase:*` — `phase:0..phase:10` for the Managed Agents implementation
  roadmap. *Created lazily once the implementation brief lands.*

## When the loop breaks

- **Issue sat in Ready for >2 weeks unattended** → re-triage. Priority
  was probably wrong.
- **Agent opens a PR you don't want** → close it, comment why, and
  refine the issue's acceptance criteria. The next attempt will be
  better.
- **Agent keeps asking the same clarifying question** → that's a
  Definition of Ready failure. Patch the issue body, don't keep
  answering in comments.
- **Board is out of sync with reality** → the built-in workflows handle
  most of it; if something is stuck, edit the project item field
  manually. Don't let drift accumulate.
