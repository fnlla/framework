**CONTRIBUTING**

Finella is a public, proprietary product by TechAyo LTD. Public contributions
are welcome via issues and pull requests. By contributing, you agree that your
contribution may be used, modified, and distributed under the Finella license.

**ATTRIBUTION**
Finella requires an Attribution Notice in product source code or repository
documentation when used in applications. Use `NOTICE` as the standard template.

**REQUIREMENTS**
**-** PHP 8.4+
**-** Composer 2
**-** Git

**REPO LAYOUT (HIGH LEVEL)**
**-** `framework/` - core framework
**-** `packages/` - optional modules
**-** `app/` - starter app
**-** `tools/harness/` - dev/test harness
**-** `docs/` - documentation

**WORKFLOW**
**-** Create a feature branch from `main`.
**-** Make focused commits (do not start commit subjects with `chore:`).
**-** Run checks locally (see below).
**-** Open a PR and wait for required checks.

**RUNNING CHECKS**
From the repo root:
```bash
php scripts/smoke/run-smoke-tests.php
php scripts/ci/public-api-snapshot.php
php scripts/ci/check-baseline-drift.php
```

From `tools/harness/`:
```bash
composer run smoke
```

**CODING STANDARDS**
**-** Follow PSR-12.
**-** Keep core lightweight; optional features belong in packages.
**-** Update docs when behaviour changes.

**HYGIENE**
Do not commit:
**-** `vendor/`
**-** Composer cache directories
**-** runtime artefacts under `storage/`

Use the hygiene checker:
```bash
bash scripts/release/check-release-hygiene.sh
```
