**FNLLA (FINELLA)**

[![Developed by TechAyo](https://img.shields.io/badge/Developed%20by-TechAyo-f59e0b?style=flat-square&labelColor=f97316&color=facc15)](https://techayo.co.uk)
[![Latest Release](https://img.shields.io/badge/Latest%20Release-3.0.6-22c55e?style=flat-square&labelColor=0ea5e9)](https://github.com/fnlla/framework/releases)

fnlla (finella) is an AI-assisted (optional), modular PHP framework by TechAyo (techayo.co.uk). The core framework runs fully without AI. AI is a first-class, opt-in layer: governance, routing, telemetry, and autonomous insights are built in, but remain optional and safe by default.

**Status:** Public release (proprietary license).

**OWNERSHIP**
**-** Author: [TechAyo](https://techayo.co.uk)
**-** Project Manager: Marcin Kordyaczny

**ABOUT**
fnlla (finella) Framework is a production-focused PHP platform for teams that want a clear core architecture, optional modular capabilities, predictable operations at scale, and AI capabilities that remain opt-in by design.

**Core idea:** fnlla (finella) Framework + companion starter repository
Starter application lives in the `fnlla/fnlla` repository.
Release notes for framework changes are summarised in `CHANGELOG.md`.

**WHAT IS FNLLA (FINELLA)?**
**-** **fnlla (finella) Framework**: minimal, modern PHP core focused on HTTP, routing, container, config, and error handling.
**-** Modular ecosystem of optional packages (auth, database, ORM, cache, queue, mail, docs, etc.).
**-** Starter app distributed in a separate repository (`fnlla/fnlla`).
**-** Optional AI stack with RAG, governance, and deterministic autonomous insights.

**AI POSITIONING**
**-** AI is optional and never required to boot, run, or deploy a fnlla (finella) app.
**-** The AI layer is designed to improve workflow, quality, and documentation without blocking delivery.

**AI BOUNDARIES (DOES)**
**-** Drafts, summarizes, and proposes changes with explicit human review.
**-** Runs deterministic insights without external providers when configured.
**-** Applies governance (policy, redaction, routing, telemetry) when enabled.

**AI BOUNDARIES (DOES NOT)**
**-** Write or apply changes automatically without preview/confirmation.
**-** Require network calls or provider keys to operate the core framework.
**-** Replace engineering ownership or the SDLC decision process.

**WHY PHP FOR FNLLA (FINELLA)?**
Because it is the fastest path to reliable, enterprise web delivery with a low barrier to entry and predictable total cost of ownership.

**PERFORMANCE AND OBSERVABILITY**
**-** Warm kernel support for long-running servers (`HttpKernel::boot()`).
**-** Tracing headers by default: `X-Request-Id`, `X-Trace-Id`, `X-Span-Id` (configurable).

**STABILITY AND SUPPORT**
**-** Support policy: `documentation/src/operations.md`.
**-** Current supported line: 3.0.x (active support until 2027-04-30, security-only until 2028-04-30).
**-** SemVer + deprecation policy (breaking changes only in major versions).
**-** Deprecations registry + migration notes: `documentation/src/operations.md`.

**NAME ORIGIN AND TECHNICAL SLUG**
**-** Product name: `fnlla (finella)` (name origin: fnlla (finella) Gardens, Dundee, UK).
**-** Technical slug: `fnlla` (`github.com/fnlla`, `fnlla.co.uk`).
**-** Why `fnlla (finella)`: short ASCII-only identifier, easy to type in CLI/paths, and stable across package/repository naming.

**ROADMAP**
**-** Public roadmap in `documentation/src/operations.md`.

**DEVELOPER EXPERIENCE**
**-** CLI-first workflow, smoke tests, debugbar, and lightweight testing helpers.
**-** Task-oriented docs and feature index: `documentation/src/developer-experience.md`.
**-** Package catalog: `documentation/src/packages.md`.
**-** ORM ergonomics spec: `documentation/src/framework.md`.

**ORM ERGONOMICS (QUICK EXAMPLE)**
```php
$active = User::whereHas('posts', fn ($q) => $q->where('status', 'published'))
    ->withCount('posts')
    ->latest()
    ->take(10)
    ->get();
```

**QUICK START (HARNESS)**
```bash
git clone https://github.com/fnlla/framework.git framework
cd framework/tools/harness
copy .env.example .env
composer install
php bin/fnlla db:bootstrap
php -S 127.0.0.1:8000 -t public
```

For production/stable installs (outside monorepo dev), use:
```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

Open:
**-** Product App: `http://127.0.0.1:8000/`

**STARTER**
**-** Starter application repository: `https://github.com/fnlla/fnlla`
**-** Public bootstrap command: `composer create-project fnlla/fnlla my-app`

Docs UI and docs generation:
**-** Docs home: `GET /docs`
```bash
php bin/fnlla docs:generate
php bin/fnlla docs:generate --publish
```

CLI and testing:
```bash
php bin/fnlla list
php bin/fnlla routes:cache
composer run smoke
```

CLI/runtime note:
**-** In local/dev environments, framework smoke scripts and harness CLI auto-reexec to PHP 8.5.5 when started with any other PHP binary.
**-** In CI, any PHP 8.5.x runtime is accepted.
**-** Override the preferred binary with `Fnlla_PHP_BIN` (or `Fnlla_PHP85_BIN`) pointing to PHP 8.5.5.
**-** `composer run serve` in `tools/harness` uses the same guard; bind can be changed with `Fnlla_DEV_HOST` / `Fnlla_DEV_PORT`.

**HELLO WORLD ROUTE**
**-** Route: `tools/harness/routes/web.php`
**-** Controller: `tools/harness/src/Controllers/HomeController.php`
**-** View: `tools/harness/resources/views/pages/home.php`

```php
$router->get('/', [HomeController::class, 'index']);
```

**READINESS ENDPOINT**
**-** Route: `GET /ready`
**-** Controller: `tools/harness/src/Controllers/HealthController.php`
**-** Service: `tools/harness/src/Services/AppReadinessService.php`

Returns `200` when dependencies are ready, and `503` otherwise.

**DOCUMENTATION**
**-** Getting Started: `documentation/src/getting-started.md`
**-** Framework guide: `documentation/src/framework.md`
**-** Packages catalog: `documentation/src/packages.md`
**-** Operations & governance: `documentation/src/operations.md`
**-** Enterprise go-live checklist: `framework/docs/enterprise-go-live-checklist.md`
**-** AI integrations: `documentation/src/ai-integrations.md`
**-** Developer experience: `documentation/src/developer-experience.md`
**-** Full index: `documentation/src/index.md`

**THIRD-PARTY NOTICES WORKFLOW**
`THIRD_PARTY_NOTICES.md` is generated from available Composer lock files discovered by
`scripts/ci/generate-third-party-notices.php` (currently sourced from `tools/harness/composer.lock`).

Use:
```bash
php scripts/ci/generate-third-party-notices.php
php scripts/ci/generate-third-party-notices.php --check
```
`--check` is used in CI and release gate to ensure notices stay in sync.

**ATTRIBUTION STANDARD**
fnlla (finella) requires an Attribution Notice in your product source code or repository documentation. Use `NOTICE` as a template, for example:
**-** "Built with the fnlla (finella) Framework and fnlla (finella) Components."

**REQUIREMENTS**
**-** PHP >= 8.5
**-** Composer >= 2

**LICENSE**
See `LICENSE.md` at the repo root and per-package LICENSE files.

**MAINTAINER DISTRIBUTION PLAYBOOK**
This section is the operational source for maintainers responsible for public package distribution and `composer create-project`.

**SOURCE OF TRUTH**
**-** `fnlla/framework` stays private and is the only source of truth.
**-** Public distribution is served through `fnlla/packages` (single public package hub).
**-** Starter repository is `fnlla/fnlla` and is public for `composer create-project fnlla/fnlla`.
**-** Installer repository is `fnlla/installer` and provides `fnlla new`.

**PACKAGE SPLIT (PUBLIC VS PRIVATE)**
**-** Manifest file: `scripts/release/distribution-packages.json`
**-** Print current split: `php scripts/release/list-distribution-packages.php`
**-** Guard check: `php scripts/release/check-public-distribution.php`

Public core (`public_core`) currently includes:
**-** `fnlla/framework`
**-** `fnlla/ai`
**-** `fnlla/audit`
**-** `fnlla/deploy`
**-** `fnlla/monitoring`
**-** `fnlla/oauth`
**-** `fnlla/standard`
**-** `fnlla/queue`
**-** `fnlla/scheduler`
**-** `fnlla/mail`
**-** `fnlla/ops`
**-** `fnlla/pdf`
**-** `fnlla/rbac`
**-** `fnlla/search`
**-** `fnlla/settings`
**-** `fnlla/docs`
**-** `fnlla/testing`
**-** `fnlla/debugbar`

Private/pro (`private_pro`) currently includes optional modules not required by starter:
**-** `fnlla/analytics`
**-** `fnlla/content`
**-** `fnlla/seo`
**-** `fnlla/notifications`
**-** `fnlla/webmail`
**-** `fnlla/storage-s3`
**-** `fnlla/stripe`
**-** `fnlla/sentry`
**-** `fnlla/tenancy`

Rule: packages required by starter (runtime and dev) must stay in `public_core`.
Private registry is for optional pro add-ons only.

**PUBLIC PACKAGE HUB**
Public package distribution lives in:
**-** `fnlla/packages`
**-** Composer metadata: `repository/packages.json`
**-** Package archives: `repository/dist/*`
**-** Composer URL used by starter: `https://raw.githubusercontent.com/fnlla/packages/main/repository`

**COMPOSER METADATA RULES (PUBLIC PACKAGE REPOS)**
**-** Keep valid package `name` in each package `composer.json` (for example `fnlla/ops`).
**-** Do not set a fixed `version` in `composer.json`; versions come from Git tags.
**-** Keep `extra.branch-alias.dev-main` aligned with the current release line (for example `3.0.x-dev`).
**-** Keep dependency constraints aligned to the release line (for example `fnlla/framework:^3.0`).

**ONE-TIME PACKAGIST PUBLISH (PUBLIC PACKAGES)**
**-** Open Packagist account with org access.
**-** Submit `https://github.com/fnlla/packages` if publishing hub metadata workflow, or submit per-package repository URLs used by your package strategy.
**-** Confirm package names and versions resolve correctly.
**-** Enable auto-update webhook/service in Packagist.
**-** Verify all starter-required packages are installable from public sources.
**-** Packagist reads `composer.json` from the repository root of the default branch. For `fnlla/framework`, keep the root `composer.json` present and aligned with `framework/composer.json`.

**ONE-TIME PACKAGIST PUBLISH (STARTER)**
**-** Ensure starter repo `fnlla/fnlla` is public.
**-** Confirm starter `composer.json` contains only remote dependencies (no `path` repositories).
**-** Submit `https://github.com/fnlla/fnlla` to Packagist as `fnlla/fnlla`.
**-** Verify `composer create-project fnlla/fnlla my-app` resolves from Packagist.

**STARTER RULES (REQUIRED)**
**-** In `fnlla/fnlla`, keep only remote dependencies in `composer.json`.
**-** Do not commit `composer.lock` in the starter template repo.
**-** Local monorepo development may use `composer.dev.json` with `path` repositories, but this file is for maintainers only.
**-** Use `composer run lock:check` in starter CI to reject local/path lock sources.

**RELEASE FLOW (REQUIRED EVERY RELEASE)**
**-** Tag in private source-of-truth monorepo:
   `git tag vX.Y.Z && git push origin vX.Y.Z`
**-** Sync public package sources/artifacts into `fnlla/packages`.
**-** Rebuild `repository/` metadata in `fnlla/packages` and publish to `main`.
**-** Refresh Packagist package metadata (auto-hook or manual update).
**-** Release starter (`fnlla/fnlla`) with aligned dependency constraints.
**-** Validate public bootstrap:
   `composer create-project fnlla/fnlla my-app`
**-** Validate install/boot in created app:
   `composer install && php bin/fnlla db:bootstrap`

**MAINTAINER CHECKLIST (SHORT)**
**-** Update code/docs in private `fnlla/framework`.
**-** Run release gate + distribution checks.
**-** Tag monorepo.
**-** Sync/update `fnlla/packages` public sources.
**-** Rebuild and publish `fnlla/packages/repository`.
**-** Refresh/verify Packagist versions.
**-** Release `fnlla/fnlla`.
**-** Validate `composer create-project`.
