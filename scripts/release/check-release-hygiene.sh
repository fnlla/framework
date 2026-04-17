#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

MODE="tracked"
if [[ "${1:-}" == "--strict" ]]; then
  MODE="strict"
fi

if ! command -v php >/dev/null 2>&1; then
  echo "ERROR: php is required to run release hygiene checks."
  exit 1
fi

if [ -f "ui/index.md" ]; then
  php scripts/docs/build-ui-docs.php
else
  echo "UI docs source not found (ui/index.md). Skipping UI docs build."
fi

git_available=0
if command -v git >/dev/null 2>&1; then
  if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    git_available=1
  fi
fi

fail=0
present_dirs=()
tracked_dirs=()
present_files=()
tracked_files=()

# Allowlist (paths relative to repo root). Add entries if you intentionally keep vendor in fixtures.
ALLOWLIST=(
)

is_allowed() {
  local path="$1"
  for allowed in "${ALLOWLIST[@]}"; do
    if [[ "$path" == "$allowed"* ]]; then
      return 0
    fi
  done
  return 1
}

is_tracked() {
  local path="$1"
  if [[ "$git_available" -ne 1 ]]; then
    return 1
  fi
  if [[ -n "$(git ls-files -z -- "$path")" ]]; then
    return 0
  fi
  return 1
}

collect_dirs() {
  local name="$1"
  while IFS= read -r -d '' dir; do
    local clean="${dir#./}"
    if is_allowed "$clean"; then
      continue
    fi
    present_dirs+=("$clean")
    if is_tracked "$clean"; then
      tracked_dirs+=("$clean")
    fi
  done < <(find . -type d -name "$name" -print0)
}

collect_files_name() {
  local name="$1"
  while IFS= read -r -d '' file; do
    local clean="${file#./}"
    if is_allowed "$clean"; then
      continue
    fi
    present_files+=("$clean")
    if is_tracked "$clean"; then
      tracked_files+=("$clean")
    fi
  done < <(find . -type f -name "$name" -print0)
}

collect_files_path() {
  local path="$1"
  while IFS= read -r -d '' file; do
    local clean="${file#./}"
    if is_allowed "$clean"; then
      continue
    fi
    present_files+=("$clean")
    if is_tracked "$clean"; then
      tracked_files+=("$clean")
    fi
  done < <(find . -type f -path "$path" -print0)
}

collect_dirs "vendor"
collect_dirs ".composer-cache"
collect_dirs ".composer-home*"
collect_dirs ".phpunit.cache"
collect_dirs "coverage"
collect_dirs ".idea"
collect_dirs ".vscode"

collect_files_path "*/storage/sessions/*"
collect_files_path "*/storage/logs/*"
collect_files_path "*/storage/cache/*"
collect_files_name "*.log"
collect_files_name "tmp-*.zip~"
collect_files_name ".phpunit.result.cache"
collect_files_name ".DS_Store"
collect_files_name "Thumbs.db"

echo "Release hygiene check (${MODE} mode)"
if [ "${#tracked_dirs[@]}" -gt 0 ] || [ "${#tracked_files[@]}" -gt 0 ]; then
  echo "TRACKED DIRS:"
  if [ "${#tracked_dirs[@]}" -gt 0 ]; then
    printf ' - %s\n' "${tracked_dirs[@]}"
  else
    echo " - (none)"
  fi
  echo "TRACKED FILES:"
  if [ "${#tracked_files[@]}" -gt 0 ]; then
    printf ' - %s\n' "${tracked_files[@]}"
  else
    echo " - (none)"
  fi
else
  echo "TRACKED DIRS: (none)"
  echo "TRACKED FILES: (none)"
fi

if [ "${#present_dirs[@]}" -gt 0 ] || [ "${#present_files[@]}" -gt 0 ]; then
  echo "PRESENT DIRS:"
  if [ "${#present_dirs[@]}" -gt 0 ]; then
    printf ' - %s\n' "${present_dirs[@]}"
  else
    echo " - (none)"
  fi
  echo "PRESENT FILES:"
  if [ "${#present_files[@]}" -gt 0 ]; then
    printf ' - %s\n' "${present_files[@]}"
  else
    echo " - (none)"
  fi
else
  echo "PRESENT DIRS: (none)"
  echo "PRESENT FILES: (none)"
fi

if [ "$MODE" = "strict" ]; then
  if [ "${#present_dirs[@]}" -gt 0 ] || [ "${#present_files[@]}" -gt 0 ]; then
    fail=1
  fi
else
  if [ "${#tracked_dirs[@]}" -gt 0 ] || [ "${#tracked_files[@]}" -gt 0 ]; then
    fail=1
  fi
fi

if [ "$fail" -ne 0 ]; then
  echo "Release hygiene check failed."
  echo "Remove the listed paths above (vendor/, Composer caches, storage/{sessions,logs,cache}, tmp-*.zip~, phpunit caches, coverage/, .DS_Store, Thumbs.db, .idea/, .vscode/) and re-run:"
  echo "  bash scripts/release/check-release-hygiene.sh"
  exit 1
fi

echo "Release hygiene check passed."
