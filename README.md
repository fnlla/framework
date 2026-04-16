**FINELLA**

[![Release](https://img.shields.io/badge/release-v2.5.6-blue)](https://github.com/kordyaczny/finella/releases)
[![Powered by Finella](https://img.shields.io/badge/powered%20by-Finella-0A66C2)](https://github.com/kordyaczny/finella)
[![TechAyo LTD](https://img.shields.io/badge/TechAyo-LTD-111827)](https://github.com/kordyaczny/finella)

Finella is an AI-assisted (optional), modular PHP framework by TechAyo LTD, with a companion product: Finella UI (design system + Elements). The core framework runs fully without AI. AI is a first-class, opt-in layer: governance, routing, telemetry, and autonomous insights are built in, but remain optional and safe by default.

**Status:** Public release (proprietary license).

**Core idea:** Finella Framework + Finella UI
Finella UI lives in `ui/` at the repo root.
Release notes are summarised in `CHANGELOG.md` with dedicated logs for `framework/` and `ui/`.

**WHAT IS FINELLA?**
**-** **Finella Framework**: minimal, modern PHP core focused on HTTP, routing, container, config, and error handling.
**-** **Finella UI**: no-build design system + Elements for fast product UIs.
**-** Modular ecosystem of optional packages (auth, database, ORM, cache, queue, mail, docs, etc.).
**-** Starter app in `app/` that stays minimal by default; add modules when needed.
**-** Optional AI stack with RAG, governance, and deterministic autonomous insights.

**AI POSITIONING**
**-** AI is optional and never required to boot, run, or deploy a Finella app.
**-** The AI layer is designed to improve workflow, quality, and documentation without blocking delivery.

**AI BOUNDARIES (DOES)**
**-** Drafts, summarizes, and proposes changes with explicit human review.
**-** Runs deterministic insights without external providers when configured.
**-** Applies governance (policy, redaction, routing, telemetry) when enabled.

**AI BOUNDARIES (DOES NOT)**
**-** Write or apply changes automatically without preview/confirmation.
**-** Require network calls or provider keys to operate the core framework.
**-** Replace engineering ownership or the SDLC decision process.

**WHY PHP FOR FINELLA?**
Because it is the fastest path to reliable, enterprise web delivery with a low barrier to entry and predictable total cost of ownership.

**PERFORMANCE AND OBSERVABILITY**
**-** Warm kernel support for long-running servers (`HttpKernel::boot()`).
**-** Tracing headers by default: `X-Request-Id`, `X-Trace-Id`, `X-Span-Id` (configurable).

**STABILITY AND SUPPORT**
**-** Support policy: `documentation/src/operations.md`.
**-** Current supported line: 2.5.x (active support until 5 April 2027, security-only until 5 October 2027).
**-** SemVer + deprecation policy (breaking changes only in major versions).
**-** Deprecations registry + migration notes: `documentation/src/operations.md`.

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

**QUICK START (STARTER APP)**
```bash
git clone https://github.com/kordyaczny/finella.git finella
cd finella/app
copy .env.example .env
composer run deps:dev:install
php bin/finella db:bootstrap
composer run dev
```

For production/stable installs (outside monorepo dev), use:
```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

Open:
**-** Product App: `http://127.0.0.1:8000/`

**STARTER APP GUIDE**

Optional env presets (apply on top of `.env.example`):
**-** `.env.local.example`

Auth scaffold is built-in:
**-** `/auth/login`, `/auth/register`, `/auth/logout`
**-** `/auth/password/forgot`, `/auth/password/reset/{token}`
**-** First-run onboarding: `/onboarding`

Default package stack:
**-** `finella/standard` (default web stack)
**-** Optional packages as needed (`ai`, `queue`, `scheduler`, `mail`, `tenancy`, `webmail`, `notifications`, `pdf`, `storage-s3`, `stripe`, `sentry`)
**-** `finella/debugbar` in `require-dev`

UI assets (optional):
**-** `public/assets/ui.css`
**-** `resources/views/layouts/ui.php`

Admin presets (from UI package):
```bash
php bin/finella ui:admin:publish --app=.
```
Monorepo helper script:
```bash
php scripts/release/publish-ui-admin.php --app=app
```

Docs UI and docs generation:
**-** Docs home: `GET /docs`
```bash
php bin/finella docs:generate
php bin/finella docs:generate --publish
```

CLI and testing:
```bash
php bin/finella list
php bin/finella routes:cache
composer run test
```

**HELLO WORLD ROUTE**
**-** Route: `routes/web.php`
**-** Controller: `app/src/Controllers/HomeController.php`
**-** View: `resources/views/pages/home.php`

```php
$router->get('/', [HomeController::class, 'index']);
```

**READINESS ENDPOINT**
**-** Route: `GET /ready`
**-** Controller: `app/src/Controllers/ReadinessController.php`
**-** Service: `app/src/Services/AppReadinessService.php`

Returns `200` when dependencies are ready, and `503` otherwise.

**DOCUMENTATION**
**-** Getting Started: `documentation/src/getting-started.md`
**-** Framework guide: `documentation/src/framework.md`
**-** Packages catalog: `documentation/src/packages.md`
**-** Operations & governance: `documentation/src/operations.md`
**-** AI integrations: `documentation/src/ai-integrations.md`
**-** Developer experience: `documentation/src/developer-experience.md`
**-** Full index: `documentation/src/index.md`

**THIRD-PARTY NOTICES WORKFLOW**
`THIRD_PARTY_NOTICES.md` is generated from Composer lock files:
**-** `app/composer.lock`
**-** `tools/composer.lock`
**-** `tools/harness/composer.lock`

Use:
```bash
php scripts/ci/generate-third-party-notices.php
php scripts/ci/generate-third-party-notices.php --check
```
`--check` is used in CI and release gate to ensure notices stay in sync.

**ATTRIBUTION STANDARD**
Finella requires an Attribution Notice in your product source code or repository documentation. Use `NOTICE` as a template, for example:
**-** "Built with the Finella Framework and Finella Components."

**REQUIREMENTS**
**-** PHP >= 8.4
**-** Composer >= 2

**LICENSE**
See `LICENSE.md` at the repo root and per-package LICENSE files.
