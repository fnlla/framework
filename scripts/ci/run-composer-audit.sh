#!/usr/bin/env bash

# PowerShell fallback for environments without bash:
#   pwsh -File scripts/ci/run-composer-audit.ps1
if [ -z "${BASH_VERSION:-}" ] && command -v pwsh >/dev/null 2>&1; then
  pwsh -File "$(dirname "$0")/run-composer-audit.ps1"
  exit $?
fi

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

if ! command -v composer >/dev/null 2>&1; then
  echo "ERROR: composer is required for dependency audit."
  exit 1
fi

mapfile -d '' locks < <(find "${ROOT_DIR}" -name composer.lock -not -path "*/vendor/*" -print0)
if [ "${#locks[@]}" -eq 0 ]; then
  echo "No composer.lock files found. Skipping audit."
  exit 0
fi

for lock in "${locks[@]}"; do
  dir="$(dirname "${lock}")"
  echo "==> composer audit in ${dir}"
  (cd "${dir}" && composer audit --no-interaction --no-ansi)
done
