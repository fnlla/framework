#!/usr/bin/env bash
set -euo pipefail

# Deterministic environment (reduce locale/time flakiness)
export TZ=UTC
export LANG=C
export LC_ALL=C

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

echo "Release gate mode: monorepo"

verify_environment() {
  echo "==> Environment check"
  echo "PHP: $(php -r 'echo PHP_VERSION;')"
if ! php -r "exit(version_compare(PHP_VERSION, '8.4.0', '>=') ? 0 : 1);"; then
  echo "ERROR: PHP >= 8.4.0 is required."
  exit 1
fi
  if ! command -v composer >/dev/null 2>&1; then
    echo "ERROR: composer is required."
    exit 1
  fi
  local composer_version
  composer_version="$(composer --no-ansi --version | awk '{print $3}')"
  echo "Composer: ${composer_version}"
  if ! php -r "exit(version_compare('${composer_version}', '2.2.0', '>=') ? 0 : 1);"; then
    echo "ERROR: Composer >= 2.2.0 is required."
    exit 1
  fi
}

require_clean_git() {
  local phase="$1"
  if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "Git not detected; skipping clean tree check (${phase})."
    return 0
  fi
  if ! git diff --exit-code >/dev/null 2>&1; then
    echo "ERROR: working tree has unstaged changes (${phase})."
    git status --porcelain
    exit 1
  fi
  if ! git diff --cached --exit-code >/dev/null 2>&1; then
    echo "ERROR: index has staged changes (${phase})."
    git status --porcelain
    exit 1
  fi
  if [ -n "$(git status --porcelain)" ]; then
    echo "ERROR: working tree is dirty (${phase})."
    git status --porcelain
    exit 1
  fi
  echo "Git working tree clean (${phase})."
}

run_hygiene() {
  bash scripts/release/check-release-hygiene.sh
}

check_app_template_sync() {
  if [ -f "scripts/ci/check-app-template-strict-sync.php" ]; then
    php scripts/ci/check-app-template-strict-sync.php
    return 0
  fi

  php scripts/ci/check-app-template-sync.php
}

check_third_party_notices() {
  php scripts/ci/generate-third-party-notices.php --check
}

check_release_notes_format() {
  php scripts/release/check-release-notes-format.php
}

check_markdown_format() {
  php scripts/docs/format-markdown.php --check --scope all --profile project
  php scripts/docs/format-markdown.php --check --scope github --profile release
}

validate_composer() {
  if [ -f "app/composer.json" ]; then
    echo "==> composer validate in app (stable profile, lock-agnostic)"
    (cd app && composer validate --no-interaction --no-check-publish --no-check-lock)
  fi
  if [ -f "app/composer.dev.json" ]; then
    echo "==> composer validate in app (dev profile + lock)"
    (cd app && COMPOSER=composer.dev.json composer validate --no-interaction --no-check-publish)
  fi
  for dir in framework packages/* tools/harness; do
    if [ ! -f "$dir/composer.json" ]; then
      continue
    fi
    if [ "$(basename "$dir")" = "_package-template" ]; then
      continue
    fi
    echo "==> composer validate in $dir"
    (cd "$dir" && composer validate --no-interaction --no-check-publish)
  done
}

audit_composer() {
  bash scripts/ci/run-composer-audit.sh
}

lint_php() {
  lint_dir() {
    local dir="$1"
    if [ ! -d "$dir" ]; then
      return 0
    fi
    find "$dir" -type f -name "*.php" -not -path "*/vendor/*" -print0 \
      | xargs -0 -n 1 php -l > /dev/null
  }
  lint_dir "framework/src"
  lint_dir "packages"
  lint_dir "tools/harness"
}

ensure_install() {
  local dir="$1"
  if [ -f "$dir/vendor/autoload.php" ]; then
    return 0
  fi
  local had_lock=0
  if [ -f "$dir/composer.lock" ]; then
    had_lock=1
  fi
  echo "==> composer install in $dir"
  (cd "$dir" && composer install --no-interaction --prefer-dist)
  if [ $had_lock -eq 0 ] && [ -f "$dir/composer.lock" ]; then
    rm -f "$dir/composer.lock"
  fi
}

run_api_snapshot() {
  php scripts/ci/public-api-snapshot.php
  if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "Not a git repository; skipping API diff."
    return 0
  fi
  if [[ "${RELEASE_GATE_STRICT_API:-0}" == "1" ]]; then
    BASE_TAG="$(git tag --sort=-creatordate | head -n1 || true)"
    if [ -z "$BASE_TAG" ]; then
      echo "No tags found; skipping breaking-change check."
      return 0
    fi
    if ! git show "${BASE_TAG}:documentation/build/api/public-api.json" > documentation/build/api/public-api.prev.json; then
      echo "Base tag has no documentation/build/api/public-api.json; skipping breaking-change check."
      return 0
    fi
    php scripts/ci/public-api-check-breaking.php --base documentation/build/api/public-api.prev.json --current documentation/build/api/public-api.json
  else
    git diff -- documentation/build/api/public-api.json || true
  fi
}

run_monorepo_tests() {
  local cleanup_dirs=()
  ensure_install "framework"
  ensure_install "app"
  ensure_install "tools/harness"
  if [ -f "tools/harness/vendor/autoload.php" ] && [ ! -d "tools/harness/vendor/.git" ]; then
    cleanup_dirs+=("tools/harness/vendor")
  fi

  echo "==> composer install in app (monorepo path repos, temp)"
  local tmp_home tmp_cache composer_json composer_backup status tmp_dir
  tmp_home="$(mktemp -d)"
  tmp_cache="${ROOT_DIR}/.composer-cache"
  tmp_dir="${ROOT_DIR}/tmp-app"
  rm -rf "$tmp_dir" || true
  mkdir -p "$tmp_dir"
  cp -R app/. "$tmp_dir"
  rm -f "${tmp_dir}/composer.lock"
  composer_json="${tmp_dir}/composer.json"
  composer_backup="$(mktemp)"
  mkdir -p "$tmp_cache"
  cp "$composer_json" "$composer_backup"
  (cd "$tmp_dir" && COMPOSER_HOME="$tmp_home" COMPOSER_CACHE_DIR="$tmp_cache" \
    composer config repositories.framework path "${ROOT_DIR}/framework")
  if [ -f "${ROOT_DIR}/ui/composer.json" ]; then
    (cd "$tmp_dir" && COMPOSER_HOME="$tmp_home" COMPOSER_CACHE_DIR="$tmp_cache" \
      composer config repositories.ui path "${ROOT_DIR}/ui")
  fi
  (cd "$tmp_dir" && for dir in "$ROOT_DIR"/packages/*; do
    if [ -f "$dir/composer.json" ]; then
      name="$(basename "$dir")"
      if [ "$name" = "_package-template" ]; then
        continue
      fi
      COMPOSER_HOME="$tmp_home" COMPOSER_CACHE_DIR="$tmp_cache" \
        composer config "repositories.${name}" path "$dir"
    fi
  done)
  (cd "$tmp_dir" && COMPOSER_HOME="$tmp_home" COMPOSER_CACHE_DIR="$tmp_cache" \
    composer config minimum-stability dev)
  (cd "$tmp_dir" && COMPOSER_HOME="$tmp_home" COMPOSER_CACHE_DIR="$tmp_cache" \
    composer config prefer-stable true)
  set +e
  (cd "$tmp_dir" && COMPOSER_HOME="$tmp_home" COMPOSER_CACHE_DIR="$tmp_cache" \
    composer install --no-interaction --prefer-dist)
  status=$?
  mv "$composer_backup" "$composer_json"
  rm -f "${tmp_dir}/composer.lock"
  rm -rf "$tmp_home"
  set -e
  if [ $status -ne 0 ]; then
    rm -rf "$tmp_dir"
    exit $status
  fi

  php scripts/smoke/run-smoke-tests.php
  php scripts/ci/check-docs-sync.php --app=app
  php scripts/docs/build-ui-docs.php
  (cd tools/harness && composer run smoke --no-interaction)
  (cd "$tmp_dir" && composer run smoke --no-interaction)
  rm -rf "$tmp_dir"

  if [ "${#cleanup_dirs[@]}" -gt 0 ]; then
    for dir in "${cleanup_dirs[@]}"; do
      rm -rf "$dir" || true
    done
  fi
}

verify_environment
require_clean_git "pre"
run_hygiene
check_app_template_sync
check_third_party_notices
check_release_notes_format
check_markdown_format
validate_composer
audit_composer
lint_php
run_api_snapshot
run_monorepo_tests

require_clean_git "post"
echo "Release gate passed."
