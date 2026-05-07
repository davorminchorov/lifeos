#!/usr/bin/env bash
# Common utilities for the bootstrap scripts.

log() {
  printf '\n[bootstrap] %s\n' "$*" >&2
}

warn() {
  printf '[bootstrap] WARN: %s\n' "$*" >&2
}

err() {
  printf '[bootstrap] ERROR: %s\n' "$*" >&2
  exit 1
}

require_cmd() {
  command -v "$1" >/dev/null 2>&1 || err "$1 is required but not on PATH."
}

# run <cmd...> — runs the command unless DRY_RUN=1, in which case it prints.
run() {
  if [[ "${DRY_RUN:-0}" == "1" ]]; then
    printf '[dry-run] %q ' "$@" >&2
    printf '\n' >&2
    return 0
  fi
  "$@"
}

# run_with_token TOKEN_VAR cmd...
# Runs the given command with GH_TOKEN replaced by the value of $TOKEN_VAR for
# its duration. Used to swap to GH_PROJECT_TOKEN for Project v2 calls.
run_with_token() {
  local var="$1"
  shift
  local saved="${GH_TOKEN:-}"
  GH_TOKEN="${!var}"
  export GH_TOKEN
  if [[ "${DRY_RUN:-0}" == "1" ]]; then
    printf '[dry-run] (with %s) %q ' "$var" "$@" >&2
    printf '\n' >&2
    GH_TOKEN="$saved"
    return 0
  fi
  local rc=0
  "$@" || rc=$?
  GH_TOKEN="$saved"
  export GH_TOKEN
  return $rc
}
