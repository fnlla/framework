#!/usr/bin/env bash

# PowerShell fallback for environments without bash:
#   pwsh -File scripts/ci/run-phpunit-coverage.ps1
if [ -z "${BASH_VERSION:-}" ] && command -v pwsh >/dev/null 2>&1; then
  pwsh -File "$(dirname "$0")/run-phpunit-coverage.ps1"
  exit $?
fi

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TOOLS_DIR="${ROOT_DIR}/tools"
HARNESS_DIR="${ROOT_DIR}/tools/harness"
PHPUNIT_BIN="${TOOLS_DIR}/vendor/bin/phpunit"
COVERAGE_PATH="${COVERAGE_PATH:-${ROOT_DIR}/.artifacts/coverage.xml}"
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

if [ ! -f "${PHPUNIT_BIN}" ]; then
  echo "==> Installing PHPUnit toolchain"
  (cd "${TOOLS_DIR}" && composer install --no-interaction --prefer-dist --no-progress)
fi

if [ ! -f "${HARNESS_DIR}/vendor/autoload.php" ]; then
  echo "==> Installing harness dependencies"
  (cd "${HARNESS_DIR}" && composer install --no-interaction --prefer-dist --no-progress)
fi

echo "==> Running PHPUnit with coverage (core + packages)"
mkdir -p "$(dirname "${COVERAGE_PATH}")"
"${PHPUNIT_BIN}" -c "${ROOT_DIR}/tools/phpunit.xml" --coverage-clover "${COVERAGE_PATH}"
