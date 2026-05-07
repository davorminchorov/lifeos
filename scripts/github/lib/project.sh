#!/usr/bin/env bash
# Idempotent setup of the "LifeOS" Project v2.
#
# Operates on a user-owned project. Requires GH_PROJECT_TOKEN with
# 'project' scope, because the default workflow GITHUB_TOKEN cannot
# read or mutate user-owned Projects v2.
#
# What this manages:
#   - The project itself (creates if missing)
#   - Linking the project to the repo (so issues are in scope)
#   - Single-select fields: Status, Priority, Module, Effort, Phase, Agent
#   - Iteration field: Iteration (2-week cadence from $ITERATION_START)
#
# What this does NOT manage:
#   - Project views (Board, Agent Queue, By Module, Phase Roadmap, My Week)
#     — the Projects v2 GraphQL API does not expose view CRUD as of writing.
#   - Built-in workflow toggles (Auto-add to project, Item closed, etc.)
#     — partial GraphQL support, brittle to automate. See post-run notes.
#
# Both are documented as manual one-time setup at the end of bootstrap.

PROJECT_NUMBER=""

_gh_project() {
  run_with_token GH_PROJECT_TOKEN gh "$@"
}

# Find an existing project by title. Sets PROJECT_NUMBER if found.
find_project() {
  local json
  json="$(_gh_project gh project list --owner "$PROJECT_OWNER" --format json --limit 100 2>/dev/null || echo '{"projects":[]}')"
  PROJECT_NUMBER="$(printf '%s' "$json" | jq -r --arg t "$PROJECT_TITLE" '.projects[]? | select(.title==$t) | .number' | head -n1)"
  [[ -n "${PROJECT_NUMBER:-}" ]]
}

create_project() {
  log "Creating Project v2 '$PROJECT_TITLE' under $PROJECT_OWNER"
  if [[ "${DRY_RUN:-0}" == "1" ]]; then
    PROJECT_NUMBER="DRY_RUN"
    return 0
  fi
  local out
  out="$(run_with_token GH_PROJECT_TOKEN gh project create --owner "$PROJECT_OWNER" --title "$PROJECT_TITLE" --format json)"
  PROJECT_NUMBER="$(printf '%s' "$out" | jq -r '.number')"
  [[ -n "$PROJECT_NUMBER" && "$PROJECT_NUMBER" != "null" ]] || err "Failed to read project number from create output"
  log "Created project number: $PROJECT_NUMBER"
}

link_project_to_repo() {
  log "Linking project #$PROJECT_NUMBER to $REPO_FULL"
  # `gh project link` is idempotent: linking an already-linked repo is a no-op.
  run_with_token GH_PROJECT_TOKEN gh project link "$PROJECT_NUMBER" --owner "$PROJECT_OWNER" --repo "$REPO_FULL" >/dev/null 2>&1 || \
    warn "Could not link project to repo (may already be linked, or token lacks scope)."
}

# field_exists FIELD_NAME — 0 if a field with that name exists, 1 otherwise.
field_exists() {
  local name="$1"
  local json
  json="$(run_with_token GH_PROJECT_TOKEN gh project field-list "$PROJECT_NUMBER" --owner "$PROJECT_OWNER" --format json --limit 100 2>/dev/null || echo '{"fields":[]}')"
  printf '%s' "$json" | jq -e --arg n "$name" '.fields[]? | select(.name==$n)' >/dev/null
}

create_single_select() {
  local name="$1"
  local options_csv="$2"
  if field_exists "$name"; then
    log "Field exists, skipping: $name"
    return 0
  fi
  log "Creating single-select field: $name ($options_csv)"
  run_with_token GH_PROJECT_TOKEN gh project field-create "$PROJECT_NUMBER" \
    --owner "$PROJECT_OWNER" \
    --name "$name" \
    --data-type SINGLE_SELECT \
    --single-select-options "$options_csv"
}

create_iteration_field() {
  local name="$1"
  if field_exists "$name"; then
    log "Iteration field exists, skipping: $name"
    return 0
  fi
  # gh CLI does not expose iteration field creation flags as of writing.
  # Use GraphQL instead.
  log "Creating iteration field: $name (start=$ITERATION_START, 14d cadence)"
  if [[ "${DRY_RUN:-0}" == "1" ]]; then
    log "(dry-run) skipping GraphQL iteration creation"
    return 0
  fi

  local project_id
  project_id="$(run_with_token GH_PROJECT_TOKEN gh project view "$PROJECT_NUMBER" --owner "$PROJECT_OWNER" --format json | jq -r '.id')"
  [[ -n "$project_id" && "$project_id" != "null" ]] || err "Could not resolve project node id"

  run_with_token GH_PROJECT_TOKEN gh api graphql -f query='
    mutation($projectId: ID!, $name: String!, $startDate: Date!) {
      createProjectV2Field(input: {
        projectId: $projectId,
        dataType: ITERATION,
        name: $name,
        iterationConfiguration: {
          duration: 14,
          startDate: $startDate
        }
      }) {
        projectV2Field {
          ... on ProjectV2IterationField { id name }
        }
      }
    }' \
    -f projectId="$project_id" \
    -f name="$name" \
    -f startDate="$ITERATION_START" >/dev/null
}

setup_project() {
  if find_project; then
    log "Found existing project '$PROJECT_TITLE' (#$PROJECT_NUMBER)"
  else
    create_project
  fi

  link_project_to_repo

  create_single_select "Status"   "Backlog,Ready,In Progress,In Review,Blocked,Done"
  create_single_select "Priority" "P0,P1,P2,P3"
  create_single_select "Module"   "subscriptions,contracts,warranties,investments,expenses,utility-bills,iou,budgets,job-applications,cycle-menu,notifications,dashboard,invoicing,agents,mcp,infra"
  create_single_select "Effort"   "XS,S,M,L,XL"
  create_single_select "Phase"    "n/a,phase:0,phase:1,phase:2,phase:3,phase:4,phase:5,phase:6,phase:7,phase:8,phase:9,phase:10"
  create_single_select "Agent"    "not-eligible,eligible,in-progress,blocked,done"
  create_iteration_field "Iteration"

  cat >&2 <<EOF

[bootstrap] Project setup complete. Manual one-time steps remaining:

  1. Open the project: https://github.com/users/$PROJECT_OWNER/projects/$PROJECT_NUMBER
  2. Create the five views (the API does not expose view CRUD):
       - Board: kanban grouped by Status, filtered to current iteration + Backlog
       - Agent Queue: table; filter label:agent-eligible status:Ready,In Progress,Blocked; sort Priority asc, Effort asc
       - By Module: board grouped by Module, filtered is:open
       - Phase Roadmap: board grouped by Phase, filtered is:open
       - My Week: table; filter iteration:@current
  3. Enable the built-in workflows (settings -> Workflows):
       - Auto-add to project (issues + PRs from this repo)
       - Item closed -> Status = Done
       - Pull request opened -> linked issue Status = In Review
       - Pull request merged -> Status = Done

EOF
}
