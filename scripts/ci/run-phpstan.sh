#!/usr/bin/env bash

# PowerShell fallback for environments without bash:
#   pwsh -File scripts/ci/run-phpstan.ps1
if [ -z "${BASH_VERSION:-}" ] && command -v pwsh >/dev/null 2>&1; then
  pwsh -File "$(dirname "$0")/run-phpstan.ps1"
  exit $?
fi

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TOOLS_DIR="${ROOT_DIR}/tools"
PHPSTAN_BIN="${TOOLS_DIR}/vendor/bin/phpstan"
LOCK_PATH="${TOOLS_DIR}/composer.lock"
lock_present=0
if [ -f "${LOCK_PATH}" ]; then
  lock_present=1
fi

cleanup() {
  rm -rf "${TOOLS_DIR}/vendor"
  if [ $lock_present -eq 0 ]; then
    rm -f "${LOCK_PATH}"
  fi
}

trap cleanup EXIT

if [ ! -f "${PHPSTAN_BIN}" ]; then
  echo "==> Installing PHPStan toolchain"
  (cd "${TOOLS_DIR}" && composer install --no-interaction --prefer-dist --no-progress)
fi

echo "==> Running PHPStan"
"${PHPSTAN_BIN}" analyse -c "${ROOT_DIR}/tools/phpstan.neon"

