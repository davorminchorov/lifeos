#!/usr/bin/env bash
# scripts/github/bootstrap.sh
#
# Idempotent bootstrap of LifeOS GitHub project management:
#   - Labels (always)
#   - Project v2 + fields + views (requires GH_PROJECT_TOKEN with `project` scope)
#   - Migration of existing open issues onto the new label/project state
#
# Safe to re-run. Existing artifacts are detected and updated, never duplicated.
# This script never deletes labels or closes issues.
#
# Required env:
#   GH_TOKEN          GitHub token with `repo` scope (the workflow's GITHUB_TOKEN works for labels + issue edits)
#
# Optional env:
#   GH_PROJECT_TOKEN  GitHub PAT with `project` scope. Required for Project v2 setup.
#                     Without it, the script will skip Project v2 work and exit with a warning, not an error.
#   PROJECT_OWNER     Login of the user/org that owns the Project v2. Defaults to the repo owner.
#   PROJECT_TITLE     Title of the Project v2. Defaults to "LifeOS".
#   ITERATION_START   ISO date the first iteration starts on. Defaults to 2026-05-11.
#   DRY_RUN           If "1", print what would happen without mutating anything.

set -euo pipefail

REPO_FULL="${GITHUB_REPOSITORY:-davorminchorov/lifeos}"
REPO_OWNER="${REPO_FULL%/*}"
REPO_NAME="${REPO_FULL##*/}"

PROJECT_OWNER="${PROJECT_OWNER:-$REPO_OWNER}"
PROJECT_TITLE="${PROJECT_TITLE:-LifeOS}"
ITERATION_START="${ITERATION_START:-2026-05-11}"
DRY_RUN="${DRY_RUN:-0}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LIB_DIR="$SCRIPT_DIR/lib"

# shellcheck source=lib/util.sh
source "$LIB_DIR/util.sh"
# shellcheck source=lib/labels.sh
source "$LIB_DIR/labels.sh"
# shellcheck source=lib/project.sh
source "$LIB_DIR/project.sh"
# shellcheck source=lib/migrate.sh
source "$LIB_DIR/migrate.sh"

main() {
  require_cmd gh
  require_cmd jq

  log "Repo: $REPO_FULL"
  log "Project owner: $PROJECT_OWNER, title: $PROJECT_TITLE"
  if [[ "$DRY_RUN" == "1" ]]; then
    log "DRY_RUN=1 — no mutations will be made."
  fi

  log "=== Phase 1: labels ==="
  sync_labels

  log "=== Phase 4: Project v2 ==="
  if [[ -z "${GH_PROJECT_TOKEN:-}" ]]; then
    warn "GH_PROJECT_TOKEN not set — skipping Project v2 setup."
    warn "Add a repo secret with name GH_PROJECT_TOKEN (PAT with 'project' + 'repo' scope) and re-run."
    PROJECT_NUMBER=""
  else
    setup_project
  fi

  log "=== Phase 6: migrate existing issues ==="
  migrate_existing_issues

  log "Done."
}

main "$@"
