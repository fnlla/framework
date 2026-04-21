#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT/tools/harness"

if [ ! -f vendor/autoload.php ]; then
  COMPOSER_CACHE_DIR="${COMPOSER_CACHE_DIR:-$ROOT/.composer-cache}"
  export COMPOSER_CACHE_DIR
  composer install --no-interaction --prefer-dist
fi

php bin/fnlla --help >/dev/null
