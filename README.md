**FINELLA**

[![Developed by TechAyo](https://img.shields.io/badge/Developed%20by-TechAyo-111827)](https://techayo.co.uk)

Finella is an AI-assisted (optional), modular PHP framework by TechAyo (techayo.co.uk). The core framework runs fully without AI. AI is a first-class, opt-in layer: governance, routing, telemetry, and autonomous insights are built in, but remain optional and safe by default.

**Status:** Public release (proprietary license).

**OWNERSHIP**
**-** Author: [TechAyo](https://techayo.co.uk)
**-** Project Manager: Marcin Kordyaczny

**ABOUT**
Finella Framework is a production-focused PHP platform for teams that want a clear core architecture, optional modular capabilities, predictable operations at scale, and AI capabilities that remain opt-in by design.

**Core idea:** Finella Framework + companion starter repository
Starter application lives in the `fnlla/fnlla` repository.
Release notes for framework changes are summarised in `CHANGELOG.md`.

**WHAT IS FINELLA?**
**-** **Finella Framework**: minimal, modern PHP core focused on HTTP, routing, container, config, and error handling.
**-** Modular ecosystem of optional packages (auth, database, ORM, cache, queue, mail, docs, etc.).
**-** Starter app distributed in a separate repository (`fnlla/fnlla`).
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
**-** Current supported line: 3.0.x (active support until 2027-04-30, security-only until 2028-04-30).
**-** SemVer + deprecation policy (breaking changes only in major versions).
**-** Deprecations registry + migration notes: `documentation/src/operations.md`.

**NAME ORIGIN AND TECHNICAL SLUG**
**-** Product name: `Finella` (name origin: Finella Gardens, Dundee, UK).
**-** Technical slug: `fnlla` (`github.com/fnlla`, `fnlla.co.uk`).
**-** Why `fnlla`: short ASCII-only identifier, easy to type in CLI/paths, and stable across package/repository naming.

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
php bin/finella db:bootstrap
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
composer run smoke
```

CLI/runtime note:
**-** In local/dev environments, framework smoke scripts and harness CLI auto-reexec to PHP 8.5.5 when started with any other PHP binary.
**-** In CI, any PHP 8.5.x runtime is accepted.
**-** Override the preferred binary with `FINELLA_PHP_BIN` (or `FINELLA_PHP85_BIN`) pointing to PHP 8.5.5.
**-** `composer run serve` in `tools/harness` uses the same guard; bind can be changed with `FINELLA_DEV_HOST` / `FINELLA_DEV_PORT`.

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
Finella requires an Attribution Notice in your product source code or repository documentation. Use `NOTICE` as a template, for example:
**-** "Built with the Finella Framework and Finella Components."

**REQUIREMENTS**
**-** PHP >= 8.5
**-** Composer >= 2

**LICENSE**
See `LICENSE.md` at the repo root and per-package LICENSE files.
