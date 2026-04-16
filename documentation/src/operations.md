**OPERATIONS & GOVERNANCE**

**OPERATIONS & GOVERNANCE**

This document consolidates production readiness, logging, monitoring, runbooks,
backups, and SLA targets for Finella apps.

**PRODUCTION READINESS CHECKLIST**

**ENVIRONMENT**
**-** `APP_ENV=prod` and `APP_DEBUG=0`.
**-** Set `APP_NAME` and `APP_VERSION` (appears in structured logs).
**-** If the app lives under a sub-path, set `APP_BASE_PATH=/your/base`.
**-** Configure `TRUSTED_PROXIES` when behind a load balancer or reverse proxy.
**-** Keep `APP_URL` updated for canonical URLs.

**SECURITY**
**-** Keep `SECURITY_HEADERS_ENABLED=1` and enable HSTS in production.
**-** Force HTTPS (`REDIRECTS_FORCE_HTTPS=1`).
**-** Use secure cookies and sessions (defaults already secure in `prod`).
**-** Keep CSRF enabled for state-changing web routes.
**-** Rotate `APP_KEY` only with a migration plan (encrypted data).

**RELIABILITY**
**-** Use a real queue driver in production (avoid `sync`).
**-** Set `QUEUE_TRIES`, `QUEUE_RETRY_AFTER`, `QUEUE_BACKOFF`.
**-** For Redis queues, set `QUEUE_DRIVER=redis` and `REDIS_*` (or `QUEUE_REDIS_*`) values.
**-** Run the scheduler (`php bin/finella schedule:run`) via cron/worker.
**-** Define timeouts for external HTTP calls.

**PERFORMANCE**
**-** Enable OPcache in PHP.
**-** Configure cache driver (`CACHE_DRIVER`) and cache paths.
**-** For Redis cache, set `CACHE_DRIVER=redis` and `REDIS_*` (or `CACHE_REDIS_*`) values.
**-** Use config/routes cache for stable deployments.
**-** Enable gzip/brotli at the reverse proxy.
Warm kernel: for long-running servers, call `HttpKernel::boot()` once to avoid bootstrapping on each request.
Ensure request-scoped state uses scoped services or resetters.

Performance defaults (recommended):
**-** `ROUTES_CACHE_ENABLED=1`
**-** `ROUTES_CACHE_ENVS=prod,staging`
**-** `APP_WARM_KERNEL=1` for long-running servers

**OPERATIONS**
**-** Run `scripts/release/release-gate.sh` before tagging releases.
**-** Back up the database and `storage/` regularly.
**-** Use `/health` for liveness checks and `/ready` for readiness.
**-** `/ready` reports Redis status when cache/queue uses Redis.
**-** Have a rollback plan (migrations + last known good tag).
**-** Ensure database migrations are reversible or use expand/contract patterns.

**ERRORS AND LOGGING**

**ERROR HANDLING**
`HttpKernel` wraps the request lifecycle and uses `ExceptionHandler` to report
and render errors. In production, stack traces are suppressed.

In the starter app, `public/index.php` wraps the kernel in a `try/catch` to avoid
leaking stack traces in production.

**DEBUG MODE**
**-** `APP_DEBUG=1` enables a minimal error message in responses.
**-** `APP_DEBUG=0` returns a generic `Server error` message.

**LOGGING**
Finella is compatible with PSR-3. For production logging, use the official
core logging (framework). It provides a Monolog-backed logger and supports
structured JSON logs with optional request ids.

Suggested production defaults:
**-** `LOG_FORMAT=json`
**-** `LOG_REQUEST_ID=1`
**-** `APP_NAME`, `APP_ENV`, `APP_VERSION` included via `config/log/log.php`
**-** Ensure log shipping (Filebeat, fluent-bit, or vendor agent).
Include `X-Request-Id`, `X-Trace-Id`, and `X-Span-Id` in logs for end-to-end tracing.

**ERROR REPORTING HOOK**
If you want to send exceptions to an external service (e.g. Sentry), bind
`Finella\Contracts\Log\ErrorReporterInterface` in the container. The core
`ExceptionHandler` will call it alongside the logger.

Example provider:
```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Finella\Contracts\Log\ErrorReporterInterface;
use Finella\Core\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ErrorReporterInterface::class, fn () => new \App\Support\SentryReporter());
    }
}
```
Register the provider in `config/providers/providers.php` under `manual`.

**RECOMMENDATIONS**
**-** Log structured data (context array) instead of concatenated strings.
**-** Avoid logging sensitive data (passwords, tokens).
**-** Rotate and archive logs in production.

**OBSERVABILITY QUICKSTART (10 MINUTES)**
**-** Ensure logging is enabled (core, included in `finella/standard`).
**-** Set environment defaults:
   **-** `LOG_FORMAT=json`
   **-** `LOG_REQUEST_ID=1`
   **-** `APP_NAME`, `APP_ENV`, `APP_VERSION`
**-** Keep `/health` for liveness and `/ready` for readiness.
**-** Bind `ErrorReporterInterface` to send exceptions to Sentry/Bugsnag.

**METRICS AND ALERTS**
Track at least:
**-** Request rate, latency (p50/p95), and error rate.
**-** Queue depth, retries, and failed jobs.
**-** DB connection errors and migration failures.

Minimum alerts:
**-** High error rate (5xx > 1% for 5 minutes).
**-** Latency spike (p95 > 1s for 5 minutes).
**-** Queue backlog growing for > 10 minutes.
**-** Low disk space on DB/app nodes (< 15%).
**-** Backup job failures.

**RUNBOOKS**

**INCIDENT TRIAGE (DEFAULT)**
**-** Check dashboards for error rate, latency, and queue backlog.
**-** Confirm current deploy version (from logs or `APP_VERSION`).
**-** If regression suspected, roll back to last known good tag.
**-** Capture logs around the incident window.

**HIGH 5XX ERROR RATE**
**-** Check recent deploys and config changes.
**-** Verify DB connectivity and migrations.
**-** Roll back if errors started immediately after deploy.

**LATENCY SPIKE**
**-** Check slow queries and external dependencies.
**-** Reduce heavy background work or disable non-critical features.
**-** Scale workers or instances if needed.

**QUEUE BACKLOG**
**-** Check worker health and `QUEUE_DRIVER`.
**-** Increase worker count or shorten `QUEUE_RETRY_AFTER`.
**-** Inspect failed jobs and error traces.

**DATABASE OUTAGE**
**-** Put the app in maintenance mode (if supported).
**-** Fail fast with a clear error page.
**-** Restore from backup if data corruption is confirmed.

**POST-INCIDENT**
**-** Write a short timeline and root cause.
**-** Add a regression test if possible.
**-** Update the runbook with new learnings.

**BACKUP AND RESTORE**

**DATABASE BACKUPS**
**-** Schedule daily full backups and frequent incremental backups.
**-** Store backups in a separate location (not on the same host).
**-** Keep at least 7-30 days of retention depending on policy.
**-** Encrypt backups at rest.

**FILE STORAGE BACKUPS**
**-** Back up `storage/` (user uploads, cache if needed).
**-** Exclude transient cache directories if you can rebuild them.

**RESTORE TESTING**
**-** Perform a restore test at least monthly.
**-** Validate application boot and key workflows on restored data.
**-** Time the restore to confirm RTO/RPO targets.

**SECRETS**
**-** Back up encryption keys and environment secrets securely.
**-** Ensure access is limited to authorised operators.
**-** Log access to secrets and rotate when staff changes.

**SLA / RTO / RPO TEMPLATE**

**DEFINITIONS**
**-** SLA (Service Level Agreement): uptime commitment (e.g. 99.9%).
**-** RTO (Recovery Time Objective): maximum acceptable downtime after an incident.
**-** RPO (Recovery Point Objective): maximum acceptable data loss window.

**EXAMPLE TARGETS**
**-** SLA: 99.9%
**-** RTO: 30 minutes
**-** RPO: 15 minutes

**AVAILABILITY TIERS (EXAMPLE)**
**-** Tier 1: customer-facing, revenue-impacting
**-** Tier 2: internal tools
**-** Tier 3: batch/analytics

**SERVICE CATALOGUE (FILL IN)**
**-** Service name:
**-** Owner:
**-** Criticality:
**-** Dependencies:

**INCIDENT RESPONSE**
**-** On-call rotation:
**-** Escalation path:
**-** Post-incident review window:

**DEPLOYMENT**

**PRODUCTION CHECKLIST**
**-** Set `APP_ENV=prod` and `APP_DEBUG=0`.
**-** DocumentRoot must be `public/`.
**-** Ensure `storage/` and `bootstrap/cache/` are writable.
**-** Install dependencies with `--no-dev` and optimised autoloader.

**TYPICAL DEPLOYMENT STEPS**
```bash
git pull
composer install --no-dev --optimize-autoloader
```

**MIGRATIONS AND CACHE**
**-** Run migrations after deploy: `php bin/finella migrate`.
**-** Warm provider cache: `php bin/finella-discover` (if needed).
**-** Consider routes/config cache for stable releases.

**STARTER MIGRATION NAMING (SAFE PLAN)**
If you want starter migrations without date-based prefixes for brand-new apps, use this migration-safe approach:
**-** Keep existing starter migration filenames unchanged in released lines (do not rename already shipped files).
**-** For new projects, start from the shipped baseline in `database/migrations-baseline` (neutral prefixes like `000001_create_users_table.php`) and customise as needed.
**-** Run them via path override (`php bin/finella migrate --path=database/migrations-baseline`) or `MIGRATIONS_PATH`.
**-** Generate all project-specific migrations with `php bin/finella make:migration <name>` in your main migrations path.

This avoids re-running old migrations on existing installs while giving new apps full naming control.

**ROLLBACK**
**-** Re-deploy the last known good tag.
**-** If migrations are irreversible, restore from backup.
**-** Validate `/health` before resuming traffic.

**WEB SERVER NOTES**
**-** Do not expose `storage/` or `config/`.
**-** Disable directory listing.
**-** Ensure HTTPS is enabled.

**NGINX EXAMPLE (MINIMAL)**
```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/my-project/public;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php-fpm.sock;
    }
}
```

**PRE-FLIGHT CHECKLIST**
The pre-flight checklist is kept as a standalone, printable document:
**-** Pre-Flight section below

**PERFORMANCE NOTES**

**KEY TIPS**
**-** Enable OPcache in production.
**-** Keep global middleware minimal.
**-** Avoid heavy work in route handlers.
**-** Use caching where appropriate.

**CACHES**
**-** Provider discovery cache: `bootstrap/cache/providers.php` (generated by `bin/finella-discover`).
**-** Routes cache: optional `storage/cache/routes.php` if you precompile routes.
**-** Config cache: `storage/cache/config.php` if `APP_CONFIG_CACHE` is enabled.
Generate routes cache via your CLI or `Finella\Http\RouteCacheCompiler`.

**ROUTES CACHE**
Routes cache is intended for production and is skipped when `APP_DEBUG=true` or `APP_ENV=local`.
Cached routes require string handlers and middleware, so closures are not cacheable.
Generate it with your CLI (for app package: `php cli/finella.php routes:cache`) or `Finella\Http\RouteCacheCompiler`.
Clear with `routes:clear` when you need a fresh compile.
If you want `routes:cache` to be non-fatal when closures are present, set `ROUTES_CACHE_STRICT=0`
to emit a disabled cache file (runtime will ignore it and load routes normally).

**HTTP RESPONSES**
Prefer returning `Response` objects for better control over headers and status codes.

**DEBUG TOOLS**
Disable debugbar in production. Keep request logging enabled if you rely on log-based observability; otherwise reduce log level or sample logs.

---

**OPERATIONS & GOVERNANCE**

This section consolidates environment, security, tooling, and governance guidance.

**ENVIRONMENTS**

Define expectations for dev, staging, and production early. It reduces release
friction and keeps behaviour consistent.

**ENVIRONMENT MATRIX**

**DEV**
**-** `APP_ENV=local`
**-** `APP_DEBUG=1`
**-** `LOG_FORMAT=line`
**-** `CACHE_DRIVER=file` (or redis if shared)
**-** `QUEUE_DRIVER=sync` (unless testing async)

**STAGING**
**-** `APP_ENV=staging`
**-** `APP_DEBUG=0`
**-** `LOG_FORMAT=json`
**-** `CACHE_DRIVER=redis`
**-** `QUEUE_DRIVER=redis`

**PRODUCTION**
**-** `APP_ENV=prod`
**-** `APP_DEBUG=0`
**-** `LOG_FORMAT=json`
**-** `CACHE_DRIVER=redis`
**-** `QUEUE_DRIVER=redis`

**REQUIRED CONFIG**
**-** `APP_NAME`, `APP_VERSION`, `APP_URL`
**-** `LOG_PATH`, `LOG_LEVEL`, `LOG_FORMAT`
**-** `REDIS_*` when using redis
**-** `DB_*` for database connections
Optional (for first-time setup):
**-** `DB_BOOTSTRAP=1` to auto-create the database and run migrations.
**-** `DB_ROOT_USERNAME` / `DB_ROOT_PASSWORD` (MySQL/Postgres) to allow database creation.

**ENV FILE GUIDE (.ENV)**
Use `.env.example` as the canonical template and keep your `.env` aligned to it.
**-** Copy `.env.example` to `.env` for each app (`app/`, `tools/harness/`, or your standalone app).
**-** Fill only the values you need to change; keep the order and section headers for fast scanning.
**-** Keep secrets local. Never commit `.env` to version control.
**-** When adding new env keys, always update `.env.example` in the same section.
**-** Use one key per line and quote values with spaces (for example `TENANCY_REQUIRED_MESSAGE="Tenant identifier required."`).
**-** Prefer environment-specific overrides (`APP_ENV=local|staging|prod`) over ad-hoc flags.
**-** If you enable AI integrations, update the related section and reindex knowledge as needed.

Section layout (starter app):
App -> Performance -> Docs -> Logging -> Request logging -> Security headers -> Rate limit -> Database -> Cache -> Redis -> Queue -> Tenancy -> Mail -> AI -> AI Policy/Redaction/Router/Telemetry/RAG -> Webmail -> Content -> SEO -> Redirects -> Forms -> Analytics -> Static cache.

**ENV QUICK CHECKLIST**
**-** Copy `.env.example` to `.env` and keep the same section order.
**-** Set `APP_ENV=local` for dev, `APP_ENV=staging` for pre-prod, `APP_ENV=prod` for production.
**-** Configure `DB_*` and `APP_URL` for your environment.
**-** If you want automatic database creation, set `DB_BOOTSTRAP=1` and fill `DB_ROOT_*`.
**-** Use Redis in production (`CACHE_DRIVER=redis`, `QUEUE_DRIVER=redis`) and fill `REDIS_*`.
**-** Enable route caching in prod (`ROUTES_CACHE_ENABLED=1`, `ROUTES_CACHE_ENVS=prod,staging`).
**-** For long-running servers, set `APP_WARM_KERNEL=1`.
**-** Protect docs with `DOCS_PUBLIC=0` and `DOCS_ACCESS_TOKEN` (or disable `DOCS_ENABLED=0`).
**-** Keep `ADMIN_DEV_ENABLED=0` and `ADMIN_ALLOW_UNCONFIGURED=0` unless you intentionally expose `/admin` in dev.
**-** Set `CORS_ALLOWED_ORIGINS` to a comma-separated allowlist (avoid `*`).
**-** Turn on `LOG_FORMAT=json` in staging/prod for structured logs.
**-** If AI is enabled, set `AI_DRIVER` and `OPENAI_API_KEY`.
**-** If Admin is enabled, set `ADMIN_LOGIN_EMAIL` and `ADMIN_LOGIN_PASSWORD` (or `ADMIN_LOGIN_PASSWORD_HASH`) and keep `ADMIN_LOGIN_REQUIRED=1`.

Example prod values (adjust to your infrastructure):
**-** `APP_ENV=prod` and `APP_DEBUG=0`.
**-** `APP_URL=https://app.example.com`.
**-** `LOG_FORMAT=json` and `LOG_LEVEL=info`.
**-** `CACHE_DRIVER=redis`, `QUEUE_DRIVER=redis`, `REDIS_HOST=10.0.0.12`.
**-** `OPENAI_API_KEY=sk-live-***` (if AI is enabled).

Example `.env` (prod, copy-paste starter):
```ini
APP_ENV=prod
APP_DEBUG=0
APP_URL=https://app.example.com
LOG_FORMAT=json
LOG_LEVEL=info
DB_CONNECTION=mysql
DB_HOST=10.0.0.20
DB_PORT=3306
DB_DATABASE=finella
DB_USERNAME=finella
DB_PASSWORD=change-me
CACHE_DRIVER=redis
QUEUE_DRIVER=redis
REDIS_HOST=10.0.0.12
REDIS_PORT=6379
SESSION_SECURE=1
COOKIE_SECURE=1
```

**NOTES**
**-** Use `.env` for secrets and deploy-time configuration.
**-** Use `finella/settings` for runtime, admin-editable values (e.g. SEO, limits, toggles).
**-** Never enable `APP_DEBUG=1` in production.
**-** Use `LOG_REQUEST_ID=1` for traceability.
**-** Tracing headers are enabled by default (`TRACE_ID_HEADER=1`, `SPAN_ID_HEADER=1`).
**-** Lock down `TRUSTED_PROXIES` for reverse proxy setups.

**SECURITY**

This document consolidates authentication, authorisation, and baseline security practices.

**AUTHENTICATION**
Auth routes and middleware live in core and are included in the
default stack (`finella/standard`).

Basic usage:
```php
use Finella\Auth\AuthRoutes;
use Finella\Http\Router;

return static function (Router $router): void {
    AuthRoutes::register($router, [
        'prefix' => '/auth',
        'middleware' => ['web'],
    ]);
};
```

Protect routes with middleware:
```php
$router->get('/dashboard', fn () => view('dashboard'), middleware: ['auth']);
```

**AUTHORISATION**
Policies and gates are provided by `finella/rbac`.

Use `can:` middleware:
```php
$router->get('/admin', fn () => view('admin'), middleware: ['can:admin']);
```

**SECURITY DEFAULTS**
**-** CSRF enabled for state-changing web routes.
**-** Security headers enabled by default in production.
**-** Secure cookies and sessions when `APP_ENV=prod`.
**-** Trusted proxies support via `TRUSTED_PROXIES`.

**SECURITY CHECKLIST**
**-** Use strong `APP_KEY` values and keep them secret.
**-** Set `APP_DEBUG=0` in production.
**-** Enable security headers (`SECURITY_HEADERS_ENABLED=1`).
**-** Enforce HTTPS redirects in production (`REDIRECTS_FORCE_HTTPS=1`).
**-** Review `RATE_LIMIT_*` for public endpoints.

**SECRETS**
**-** Store secrets in `.env` or a secrets manager.
**-** Never commit secrets to git.
**-** Rotate secrets on incident or staff changes.
**-** If you store encrypted settings (e.g. webmail passwords), plan key rotation and re-encrypt data.

**BACKUPS**
**-** Define database backup frequency (daily minimum).
**-** Test restore process monthly.
**-** Document RTO/RPO targets.

**MONITORING**
**-** Track error rates and latency.
**-** Alert on failed backups and high error rates.

**RECOMMENDATIONS**
**-** Keep `SECURITY_HEADERS_ENABLED=1` in production.
**-** Force HTTPS (`REDIRECTS_FORCE_HTTPS=1`).
**-** Avoid logging sensitive data.

**PRE-FLIGHT**

Use this checklist before any serious project starts. The goal is to confirm
scope, infra fit, and gaps early. This document is intentionally standalone so
it can be shared or printed.

**1) PROJECT BASICS**
**-** Project name:
**-** Team name:
**-** Product type (B2C / B2B / internal):
**-** Traffic expectations (low / medium / high):
**-** Data sensitivity (low / medium / high):
**-** Launch date:

**2) REQUIREMENTS FIT**
**-** Needs Redis? (Yes/No)
**-** Needs advanced queues (SQS/Rabbit/Kafka)? (Yes/No)
**-** Needs SSO/SAML/OIDC? (Yes/No)
**-** Multi-tenant? (Yes/No)
**-** Background jobs heavy? (Yes/No)
**-** File uploads? (Yes/No, max size)
**-** Real-time features (WebSockets)? (Yes/No)

**3) INFRA & OPS**
**-** Target environment (VPS / Kubernetes / Serverless):
**-** Deployment strategy (manual / CI/CD / GitHub Actions):
**-** Rollback strategy defined? (Yes/No)
**-** Monitoring target (Sentry / Datadog / Loki / other):
**-** Log storage (ELK/Loki/Datadog/none):
**-** SLA/RTO/RPO defined? (Yes/No)

**4) FINELLA PACKAGE MAP**
Tick required packages:
**-** finella/standard + optional modules
**-** core database + ORM
**-** finella/queue
**-** finella/mail
**-** core logging
**-** finella/ops
**-** core request logging
**-** finella/scheduler
**-** core cache
**-** finella/tenancy
**-** core auth / finella/rbac

**5) STARTER CUSTOMISATION**
**-** Replace starter homepage? (Yes/No)
**-** Add /ready checks (DB/queue) confirmed? (Yes/No)
**-** Decide on service layer conventions? (Yes/No)

**6) RISKS & GAPS**
List any gaps that require new package or infra work:
-
-
-

**7) GO/NO-GO**
**-** Is Finella sufficient for v2? (Yes/No)
**-** If No, list blockers:
-
-

**OUTCOME**
**-** Next steps:
**-** Owner:
**-** Due date:

**TOOLING**

This document consolidates console, testing, and fakes.

**CLI**
List commands:
```bash
php bin/finella list
```
Generate docs (requires `finella/docs`):
```bash
php bin/finella docs:generate
```
Generate and publish in one step:
```bash
php bin/finella docs:generate --publish
```
Sync monorepo docs into the app (source-of-truth):
```bash
php bin/finella docs:generate --publish --app=.
```
Publish to a custom target:
```bash
php bin/finella docs:generate --publish --publish-target=resources/docs
```
Compile routes cache:
```bash
php bin/finella routes:cache
```
Clear routes cache:
```bash
php bin/finella routes:clear
```

AI CLI (deterministic, no API key):
```bash
php bin/finella ai:scaffold User --resource --all
php bin/finella ai:doctor
php bin/finella ai:config-advisor
php bin/finella ai:security-lint
php bin/finella ai:observability --lines=2000
php bin/finella ai:docs-sync
```

**TESTING**
Run tests:
```bash
composer run test
```
Run the monorepo smoke suite (packages + AI):
```bash
php scripts/smoke/run-smoke-tests.php
```
This also runs `routes-cache-smoke` to verify closure-based routes disable cache with a clear log message.

**THIRD-PARTY NOTICES**
`THIRD_PARTY_NOTICES.md` is generated from lock files:
**-** `app/composer.lock`
**-** `tools/composer.lock`
**-** `tools/harness/composer.lock`

Generate/update notices:
```bash
php scripts/ci/generate-third-party-notices.php
```

Verify sync (used in CI and release gate):
```bash
php scripts/ci/generate-third-party-notices.php --check
```

**HARDENING KIT (ENTERPRISE PHP)**
Finella ships a hardening kit under `tools/` with PHPStan (strict), Psalm, and Rector configs.

Install tools:
```bash
cd tools
composer install
```

Run PHPStan (strict):
```bash
vendor/bin/phpstan analyse -c phpstan.enterprise.neon
```

Run Psalm:
```bash
vendor/bin/psalm --config=psalm.xml
```

Run Rector (safe upgrades):
```bash
vendor/bin/rector process --config=rector.php
```

**FAKES**
Use fakes for mail and queue in tests to avoid real side effects.

**ENTERPRISE PHP PRACTICES**

This checklist defines the defaults we expect in enterprise-grade Finella codebases.
Treat it as a living policy: adopt the standard stack first, then iterate.

**1) TYPE SAFETY AND STRICTNESS**
**-** Always use `declare(strict_types=1)`.
**-** Prefer typed properties and return types for public APIs.
**-** Avoid `mixed` unless boundary layers require it.
**-** Use DTOs for request/response payloads.

**2) STATIC ANALYSIS (HARDENING KIT)**
Finella ships a Hardening Kit in `tools/`.

Install tooling:
```bash
cd tools
composer install
```

Run PHPStan (strict):
```bash
vendor/bin/phpstan analyse -c phpstan.enterprise.neon
```

Run Psalm:
```bash
vendor/bin/psalm --config=psalm.xml
```

Run Rector (safe upgrades):
```bash
vendor/bin/rector process --config=rector.php
```

**3) TESTS AS A RELEASE GATE**
**-** Add feature tests for critical flows.
**-** Add smoke tests for new packages and modules.
**-** Use CI to enforce a minimum pass rate before release.

**4) DEPENDENCY DISCIPLINE**
**-** Keep `require` minimal.
**-** Pin `require-dev` and keep it out of production builds.
**-** Avoid introducing heavy dependencies for small wins.

**5) SECURITY DEFAULTS**
**-** Keep CSRF enabled for web routes.
**-** Enforce secure cookies and strict session defaults in prod.
**-** Enable security headers and rate limiting by default.

**6) PERFORMANCE DEFAULTS**
**-** Enable route caching in production.
**-** Warm the kernel for long-running servers.
**-** Use Redis for cache and queue in production.

**7) OBSERVABILITY**
**-** Always emit request/trace/span headers.
**-** Keep structured logs in production (JSON).
**-** Maintain minimal audit logs for critical actions.

**8) UPGRADE CADENCE**
**-** Run Rector periodically to keep compatibility with the latest PHP minor versions.
**-** Keep CI on the newest stable PHP within the supported range.

**WORKFLOW**

This is the default workflow for working on Finella.

**DAILY FLOW (6 STEPS)**
**-** Create a short-lived branch:
```bash
git checkout -b feature/your-task
```

**-** Make changes and commit:
```bash
git status -sb
git add .
git commit -m "Describe the change"
```

**-** Push the branch:
```bash
git push -u origin feature/your-task
```

**-** Open a pull request (PR) to `main`.

**-** Wait for CI to be green (required checks):
**-** `monorepo-gate (8.4)`
**-** `starter-fresh-install (8.4)`
**-** `windows-hygiene`

**-** Merge the PR. The branch is deleted automatically.

**BUILD ARTEFACTS POLICY**
**-** Never commit `vendor/` or Composer cache directories.
**-** If you need to share an archive for review, create it from a clean tree.

**RESTORING DEPENDENCIES**
From the app (dev harness):
```bash
cd tools/harness
composer install
```

From the framework or packages:
```bash
cd framework
composer install

cd packages/<package>
composer install
```

**RELEASE HYGIENE CHECKS**
Run the hygiene check before tagging:
```bash
bash scripts/release/check-release-hygiene.sh
```

**RELEASES**
Follow `documentation/src/operations.md` for tagging and publishing.

**MONOREPO HEALTH**

This document defines the minimum rules to keep the Finella monorepo fast, stable, and predictable.

**SCOPE**
Applies to:
**-** `framework/`
**-** `packages/`
**-** `ui/`
**-** `tools/harness/`
**-** `app/`
**-** `scripts/`

**NON-NEGOTIABLES**
**-** **No cross-boundary imports.**
   **-** `app` must not import from `tools/harness`.
   **-** `packages/*` must not import from `app/`.
   **-** `ui/` must not import from `app/`.
**-** **One public API per package.**
   **-** Add or change API only through documented package entrypoints.
**-** **No undocumented side effects.**
   **-** New migrations or CLI commands must be documented.
**-** **CI must be green before merge.**
   **-** Do not bypass release gates without explicit approval.

**VERSIONING RULES**
**-** **Single release train.**
   **-** All `finella/*` packages move in sync with the repo release.
**-** **Branch aliases must match release line.**
   **-** Example: `dev-main` = `2.5.x-dev`.
**-** **Composer constraints must be aligned.**
   **-** Apps and packages must depend on `^2.5` (or current release line).

**CODE OWNERSHIP**
**-** `framework/` is core runtime and requires highest review bar.
**-** `packages/` must not break `app` or `tools/harness`.
**-** `ui/` must not break `app` or `tools/harness`.
**-** `tools/harness` is for framework development only.

**HYGIENE**
**-** No generated artifacts committed unless explicitly required.
**-** Keep `app` aligned with the template checks.
**-** Use `documentation/src/` for public documentation and sync/publish into `resources/docs` only when needed.
   **-** Verify with `php scripts/ci/check-docs-sync.php --app=app` (runs docs:generate + publish).

**CI MODEL (FAST + SAFE)**
We run CI based on change scope:
**-** **Monorepo gate** runs only when `framework/`, `packages/`, `ui/`, `tools/harness/`, or `scripts/` change.
**-** **Starter install tests** run when `app/` or core packages change.
**-** **Windows hygiene** runs when any tracked documentation/starter/core change.

This keeps release-critical checks while avoiding full CI for docs-only changes.

**WHEN TO SPLIT REPOS**
Consider multi-repo if:
**-** CI consistently exceeds 15-20 minutes per PR.
**-** External contributors only need a subset of the codebase.
**-** Packages need independent release cycles.

**DOCS SYSTEM**

Finella ships with a docs automation package (`finella/docs`). The goal is to keep documentation easy to generate and easy to maintain via files and CLI.

**TWO TRACKS**
**-** Automation: generate technical and user-guide Markdown skeletons from the app.
**-** Manual: edit or override docs by editing Markdown files directly.

**STORAGE MODEL**
**-** Generated docs: `storage/docs/generated`
**-** Manual docs: `storage/docs/manual`
**-** Published docs (served by the docs UI): `resources/docs`

The Docs UI reads in this order: manual -> generated -> published. Manual docs always override generated docs of the same slug.

**SOURCE OF TRUTH (MONOREPO)**
In the monorepo, `documentation/src/` is the single source of truth. The starter app reads
directly from the root `documentation/src/` when it exists, and falls back to `resources/docs`
when you publish or sync.
Never edit generated starter docs directly in the monorepo - run a sync instead.

Sync from repo root into the starter:
```bash
php scripts/ci/check-docs-sync.php --app=app
```

Or via the CLI (from the starter app):
```bash
php bin/finella docs:generate --publish --app=.
```

**DOCS UI**
**-** Docs home: `GET /docs`

**RENDERING**
Docs pages render Markdown to HTML for a cleaner reading experience. The raw Markdown still lives in the storage paths above.

**PUBLISHING DOCUMENTATION**
Use the CLI to copy compiled docs into `resources/docs`. Manual docs override
generated docs during publishing as well. This is useful when you want to ship
docs with the app or commit them to a standalone repo.

**CLI**
Register the command in `config/console/console.php`:
```php
return [
    'commands' => [
        Finella\Docs\Commands\DocsGenerateCommand::class,
    ],
];
```
Run generation:
```bash
php bin/finella docs:generate
```
Generate and publish in one step:
```bash
php bin/finella docs:generate --publish
```
Optional override:
```bash
php bin/finella docs:generate --publish --publish-target=/path/to/published/docs
```
You can override the target path with `--target=/path/to/dir`.

**CONFIGURATION**
Docs paths are configured in `config/docs/docs.php`:
```php
return [
    'paths' => [
        'manual' => 'storage/docs/manual',
        'generated' => 'storage/docs/generated',
        'published' => 'resources/docs',
    ],
    'paths_extra' => [
        // Optional extra search roots, e.g. the monorepo `documentation/src/`.
        '{root}/../../docs',
    ],
];
```

**SECURITY NOTES**
**-** Disable docs entirely in production: `DOCS_ENABLED=0`.

**RELEASING**

This repository is a monorepo. The framework and optional modules are published as separate Composer packages. Finella UI lives in `ui/`. The dev/test harness lives in `tools/harness/`. The starter app lives in `app/` (and can be split out into a separate repo if needed).

**PRE-RELEASE CHECKLIST**
**-** Ensure your working tree is clean.
**-** Update `CHANGELOG.md` (root and/or package-level, if used).
**-** Run the full quality gate locally:
   **-** `composer validate` in `framework`, `packages/*`, `ui`, `tools/harness`
   **-** `composer install` in `framework` and `tools/harness`
   **-** `php -l` for `framework/src`, `packages/*/src`, and `ui/src`
   **-** `composer run smoke` in `tools/harness`
**-** Generate and review the public API snapshot diff:
   **-** `php scripts/ci/public-api-snapshot.php`
   **-** Compare `documentation/build/api/public-api.json` with the previous tag (see "API snapshot gate" below).
**-** Review docs for accuracy (no unimplemented features).
**-** Verify harness/starter scaffold presence:
   **-** `php scripts/ci/check-app-template-sync.php`
   **-** The check ensures both `tools/harness` and `app` include required entrypoints (bootstrap, config, routes, public index).

**RELEASE HYGIENE**
All tags and release archives must be free of build artefacts.
**-** No `vendor/` anywhere in the repo.
**-** No `.composer-cache/` or `.composer-home/`.
**-** No cached bootstrap files committed (for example `bootstrap/cache/*.php`).
**-** No `storage/sessions/*` or other runtime session files.
**-** No `tmp-*.zip~`, `.DS_Store`, or `Thumbs.db`.

What must NOT be included in releases:
**-** `vendor/`
**-** Composer cache directories (`.composer-cache`, `.composer-home`)
**-** generated caches (for example `bootstrap/cache/*.php`)

Local verification:
```bash
bash scripts/release/check-release-hygiene.sh
php scripts/ci/check-app-template-sync.php
```
On Windows (PowerShell):
```powershell
scripts\\release\\check-release-hygiene.ps1
```

Cleaning tips (if the check fails):
**-** Remove all `vendor/` directories.
**-** Delete `.composer-cache/`, `.composer-home/`, and `.composer-home-tmp/`.
**-** Remove `*/storage/sessions/*` files.
**-** Remove `tmp-*.zip~`, `.DS_Store`, and `Thumbs.db`.

Requirement:
**-** Tagging is blocked if the hygiene check fails.
Why PRs are tracked-only:
**-** Developers may have local `vendor/` or Composer cache directories for faster workflows.
**-** We only block when those artefacts are tracked in git; releases must be clean.
CI note:
**-** In CI, the strict hygiene check must run before Composer cache directories are created, otherwise tags will fail.

**TAGGING**
**-** Use Semantic Versioning (SemVer): `v2.5.0`, `v2.5.1`, `v2.6.0`, etc.
**-** Tag the monorepo after all checks pass:
  **-** `git tag v2.5.0`
  **-** `git push origin v2.5.0`

**PACKAGE RELEASE PIPELINE (STANDARD)**
Finella publishes the framework and official packages together on the same version line.
This keeps package compatibility predictable and simplifies dependency graphs.

Exception: Finella UI has its own release line and changelog (`ui/CHANGELOG.md`)
so UI iterations can ship independently of core framework releases.

Standard steps:
**-** Update `CHANGELOG.md` and package docs.
**-** Ensure all packages require the same `finella/framework` version (e.g. `^2.5`).
**-** Run `php scripts/smoke/run-smoke-tests.php` and app smoke tests.
**-** Tag the monorepo with `vX.Y.Z`.
**-** If packages are mirrored to separate repositories, tag each with the same `vX.Y.Z`.

If a package must diverge, mark it as `@experimental` in the docs and document the exception in the Versioning section below.

**PUBLISHING GITHUB RELEASE NOTES (SAFE PATH)**
To avoid encoding/escaping issues (for example corrupted characters like `<invalid-utf8>`) do not paste
release notes manually from PowerShell.

Use:
```bash
php scripts/release/publish-release.php --version 2.5.0
```

Starter shortcut (from `app/`):
```bash
composer run release:notes:check
composer run release:notes:publish -- --version 2.5.0
composer run release:notes:sync
```

What it does:
**-** extracts notes from `CHANGELOG.md` section `## [2.5.0]`
**-** validates notes for control/replacement characters
**-** creates or updates GitHub release `v2.5.0` with a UTF-8 notes file

Required release notes structure:
**-** `### Highlights`
**-** `### Added`
**-** `### Changed`
**-** `### Deprecated`
**-** `### Removed`
**-** `### Fixed`
**-** `### Security`

Template:
```text
scripts/release/release-notes-template.md
```

Format guard:
```bash
php scripts/release/check-release-notes-format.php
```

Backfill/sync release descriptions on GitHub from `CHANGELOG.md`:
```bash
php scripts/release/sync-release-notes.php --version 2.5.0
# or all releases:
php scripts/release/sync-release-notes.php
```

Markdown formatting standard:
**-** Script name: `scripts/docs/format-markdown.php`
**-** Profiles:
  **-** `project`: safe formatting for repository markdown (`documentation/src`, package docs, framework docs, etc.).
  **-** `release`: release-style formatting for GitHub-facing docs (`README.md`, `CONTRIBUTING.md`, `SECURITY.md`, `CODE_OF_CONDUCT.md`, `LICENSE.md`).
**-** Local usage (repo root):
```bash
php scripts/docs/format-markdown.php --profile project --scope all
php scripts/docs/format-markdown.php --check --profile project --scope all
php scripts/docs/format-markdown.php --check --profile release --scope github
```
**-** Starter shortcuts (from `app/`):
```bash
composer run format:markdown
composer run lint:markdown
composer run format:github
composer run lint:github
```
**-** CI and `scripts/release/release-gate.sh` enforce both checks.

If you split packages into separate release repositories (e.g. via subtree/subsplit), tag each package repository with the same version.

**BRANCH ALIASES**
For Composer compatibility during development, keep branch aliases aligned with the 2.x line:
**-** In each published `composer.json` add:
  **-** `"extra": { "branch-alias": { "dev-main": "2.5.x-dev" } }`
**-** This keeps `dev-main` compatible with `^2.5` constraints.

**HOTFIXES**
**-** Hotfix releases are patch versions: `v2.5.1`, `v2.5.2`, etc.
**-** Only bug fixes and security patches are allowed in hotfixes.
**-** Process:
  **-** Cherry-pick fix onto the release branch (if used) or main.
  **-** Update `CHANGELOG.md` under the target version.
  **-** Run the full quality gate.
  **-** Tag `v2.5.x` and publish.

**BACKWARDS COMPATIBILITY (BC) POLICY**
**-** **2.x is stable.** Public API changes must be backwards compatible.
**-** **Patch**: bug fixes only, no API changes.
**-** **Minor**: new features and deprecations, no breaking changes.
**-** **Major**: breaking changes (3.0+).
**-** Deprecations:
  **-** mark with `@deprecated` in PHPDoc
  **-** keep until next major

**MILESTONE RELEASE CHECKLIST: WEBMAIL + NOTIFICATIONS**
This checklist is intended for the milestone release that introduces the new
API packages and their docs.

**SCOPE**
**-** `finella/webmail`
**-** `finella/notifications`

**PRE-RELEASE CHECKLIST**
**-** Verify package `composer.json` metadata and version constraints.
**-** Ensure providers are auto-discovered via `extra.finella.providers`.
**-** Confirm config stubs exist in `app/config/`.
**-** Ensure `.env.example` includes new variables if required.
**-** Validate API routes and docs match (`documentation/src/*.md`).
**-** Update `CHANGELOG.md` under `Unreleased`.

**QA CHECKLIST**
**-** Run `php scripts/smoke/run-smoke-tests.php`.
**-** Run `php packages/notifications/tests/smoke.php`.
**-** Run `php packages/webmail/tests/smoke.php`.
**-** Confirm `GET /api/webmail/settings` hides passwords.
**-** Confirm `PUT /api/webmail/settings` stores new values.
**-** Confirm `POST /api/webmail/test` returns expected results.

**RELEASE STEPS**
**-** Update version constants if required (`framework/src/Core/Application.php`).
**-** Tag release and publish release notes from `CHANGELOG.md`.
**-** Verify package list in `README.md` and `packages/README.md`.

**POST-RELEASE VERIFICATION**
**-** Install the new packages into a clean app.
**-** Verify routes and configs load without errors.
**-** Confirm docs appear in the starter docs index.

**RELEASE CHECKLIST (2.X)**
**-** Confirm no build artefacts are tracked:
   **-** no `vendor/`
   **-** no `.composer-cache/` or `.composer-home/`
   **-** no generated caches committed
**-** Ensure branch aliases match `2.5.x-dev` across packages.
**-** Update `CHANGELOG.md` and the release docs if needed.
**-** Run CI-equivalent checks locally (validate, install, lint, smoke).
**-** Tag and push `vX.Y.Z`.
**-** Validate `app` install and boot the app locally.

**V2.5.0 CHECKLIST**
**-** Confirm all official packages require `finella/framework:^2.5`.
**-** Ensure `finella/standard` includes framework + ops + rbac + settings + audit + deploy and `debugbar` is dev-only.
**-** Verify starter app boots with secure defaults (CSRF, headers, rate limit).
**-** Verify ORM v1 docs + smoke tests cover migrations, relations, and seeding flow.
**-** Run full quality gate locally (validate/install/lint/smoke).
**-** Ensure no build artefacts are tracked (`vendor/`, caches).
**-** Tag `v2.5.0` and push.
**-** Run `app` install and boot the app (`composer run dev` or `php -S localhost:8000 -t public`).

**API SNAPSHOT GATE (REQUIRED FOR V2.X)**
This repo uses a lightweight public API snapshot to prevent breaking changes in 2.x.

**PRS (INFORMATIONAL)**
On pull requests, CI generates the snapshot and prints a diff. It does **not** fail the build.
**-** `php scripts/ci/public-api-snapshot.php`
**-** `git diff -- documentation/build/api/public-api.json`

**TAGS/RELEASES (BLOCKING)**
On tags, CI compares the new snapshot against the previous tag and **fails** if breaking changes are detected.
**-** `php scripts/ci/public-api-snapshot.php`
**-** `git show <previous-tag>:documentation/build/api/public-api.json > documentation/build/api/public-api.prev.json`
**-** `php scripts/ci/public-api-check-breaking.php --base documentation/build/api/public-api.prev.json --current documentation/build/api/public-api.json`

For interpretation of diffs, see the Versioning section below.

---

**PACKAGING, VERSIONING, AND RELEASE NOTES**

This section consolidates release metadata, package strategy, and registry setup.

**VERSIONING**

This document consolidates SemVer, API stability, and deprecation policy.

**SEMANTIC VERSIONING**
Finella follows SemVer. Breaking changes only in major releases.

**PACKAGE RELEASE LINES**
Core packages follow the framework release cadence, but some products can ship
independently. Finella UI has its own SemVer line and changelog so design
changes can move faster without forcing framework upgrades.

**API STABILITY**
The 2.x line is stable. Public APIs are documented and maintained.

**STABILITY LEVELS**
**-** **Stable**: documented in `documentation/src/`, backwards compatible within 2.x.
**-** **Experimental**: available but subject to change in minor releases.
**-** **Internal**: not part of the public API, may change anytime.

**HOW TO MARK APIS**
Use PHPDoc annotations:
**-** `@api` for stable, public surface area.
**-** `@experimental` for features that may change.
**-** `@internal` for internal-only code.

Public docs must explicitly label experimental features and avoid promising long-term compatibility.

**DEPRECATIONS**
Deprecated APIs remain for a period before removal. See changelog notes for migrations.

**DEPRECATION RULES**
**-** Add `@deprecated` + a replacement hint in PHPDoc.
**-** Keep deprecated APIs until the next major release.
**-** Announce removals in `CHANGELOG.md`.
**-** Register each deprecation in `documentation/src/operations.md` with a migration note in the **Migrations and cache** section.
**-** CI enforces the registry via `php scripts/ci/check-deprecations.php`.

**RELEASE NOTES**

**SUMMARY**
Release notes are tracked in `CHANGELOG.md`. Use this section to highlight the current release focus and any migration guidance.

**PRIVATE REGISTRY**

If you want Finella to be fully independent from the monorepo root, the best
approach is to publish `finella/*` as private Composer packages and let the
starter app pull them during install.

For the recommended public-core vs private-pro split, see `documentation/src/operations.md`.

Use `php scripts/release/list-distribution-packages.php` to print the current split from the
manifest before publishing.

This keeps the developer workflow simple:
**-** the starter app only ships `composer.json`
**-** packages resolve from your private registry
**-** no direct access to the monorepo is required

**RECOMMENDED: PRIVATE PACKAGIST**
Private Packagist is the quickest way to host private Composer packages with
access control.

**-** Publish each package (`finella/ui`, `finella/ai`, etc.).
**-** Add the registry credentials to your environment.
**-** Require packages as usual in `composer.json`.

Example `composer.json`:
```json
{
  "require": {
    "finella/ui": "^1.0",
    "finella/ai": "^1.0"
  }
}
```

Example auth via `COMPOSER_AUTH`:
```bash
export COMPOSER_AUTH='{"http-basic":{"repo.example.com":{"username":"token","password":"YOUR_TOKEN"}}}'
```

**SELF-HOSTED: SATIS**
Satis lets you host a private Composer registry yourself.

Example `satis.json`:
```json
{
  "name": "Finella Registry",
  "homepage": "https://packages.example.com",
  "repositories": [
    {"type": "vcs", "url": "git@github.com:techayo/ui.git"},
    {"type": "vcs", "url": "git@github.com:techayo/finella-ai.git"}
  ],
  "require-all": true
}
```

Then configure your app:
```json
{
  "repositories": [
    {"type": "composer", "url": "https://packages.example.com"}
  ]
}
```

**GITHUB PACKAGES**
GitHub Packages works well if your repos already live in GitHub.

**-** Publish each package as a Composer package.
**-** Add a `repositories` entry pointing at GitHub Packages.
**-** Provide a token via `COMPOSER_AUTH` or `auth.json`.

Example `composer.json`:
```json
{
  "repositories": [
    {"type": "composer", "url": "https://composer.pkg.github.com/techayo"}
  ],
  "require": {
    "finella/ui": "^1.0"
  }
}
```

Example `auth.json`:
```json
{
  "http-basic": {
    "composer.pkg.github.com": {
      "username": "YOUR_GITHUB_USERNAME",
      "password": "YOUR_GITHUB_TOKEN"
    }
  }
}
```

**STARTER WORKFLOW**
Once the registry is configured, developers only need to run:
```bash
composer install
```
That will download the entire Finella package set without needing access to the
monorepo root.

**NOTES**
**-** Keep package versions aligned (use tags like `v1.2.0`).

**PACKAGES**

The `packages/` directory contains optional modules for the Finella ecosystem. Each package is versioned independently and follows SemVer.

**OFFICIAL PACKAGES**
**-** `finella/queue` - queue manager and worker (sync/database/redis).
**-** `finella/scheduler` - schedule registry and `schedule:run`.
**-** `finella/mail` - Symfony Mailer adapter.
**-** `finella/mail-preview` - mail preview routes and templates.
**-** `finella/notifications` - notification delivery (email/SMS) + API endpoints.
**-** `finella/webmail` - webmail backend API (IMAP/SMTP integration).
**-** `finella/pdf` - HTML-to-PDF rendering (Dompdf) with template helpers.
**-** `finella/docs` - docs automation helpers.
**-** `finella/ui` - UI grid, components, and templates.
**-** `finella/storage` - local storage and image pipeline helpers.
**-** `finella/content` - content repository helpers (JSON/Markdown).
**-** `finella/seo` - SEO helpers (meta, OpenGraph, JSON-LD).
**-** `finella/standard` - default web stack meta-package (framework + ops + rbac + settings + audit + deploy).
**-** `finella/ops` - security headers, CORS, rate limiting, redirects, maintenance, static cache, forms.
**-** `finella/analytics` - analytics event helpers.
**-** `finella/ai` - OpenAI Responses API client and AI helpers.
**-** `finella/tenancy` - multi-tenant request context and model scoping.
**-** `finella/rbac` - roles and permissions with gate integration.
**-** `finella/settings` - key/value runtime settings store.
**-** `finella/audit` - audit logging helpers.
**-** `finella/debugbar` - debug tooling for development (do not enable in production).
**-** `finella/deploy` - deploy utilities (health + warmup commands).
**-** `finella/testing` - lightweight HTTP feature testing helpers.

**INSTALLATION**
Install packages individually as needed:
```bash
composer require finella/queue
```

**PRIVATE REGISTRY**
If your team does not have access to the monorepo root, publish `finella/*`
as private Composer packages and point the starter app at your registry.
See the Private Registry section below for the recommended setup.

**VERSIONING**
**-** Packages are compatible with `finella/framework ^2.5`.
**-** Minor and patch releases follow SemVer rules.

**AUTO-DISCOVERY**
Packages may expose service providers via `extra.finella.providers`. The starter app caches discovered providers in `bootstrap/cache/providers.php`.

**SUPPORT POLICY**

This policy defines the support windows for Finella framework and official packages.
Effective date: 5 April 2026.

**RELEASE LINES**
**-** Standard line: regular minor releases (for example 2.6.x).
**-** Finella does not designate an LTS line at this time.

**SUPPORT WINDOWS**
Active support includes bug fixes, security fixes, and compatibility updates.
Security-only support includes security fixes only.

**-** Standard: 12 months active support + 6 months security-only.

**RELEASE CADENCE (PUBLIC)**
Finella aims to publish on a predictable cadence:
**-** Patch releases: every 4-6 weeks (bug fixes and security fixes only).
**-** Minor releases: quarterly (features + deprecations).

**CURRENT COMMITMENTS**
**-** The supported line is always the latest minor release line.
**-** Exact support dates are communicated in release notes and updated here when published.

**SEMVER AND DEPRECATIONS**
**-** Patch releases: bug fixes only.
**-** Minor releases: new features and deprecations, no breaking changes.
**-** Major releases: breaking changes and removals.

Deprecation rules:
**-** Deprecate in a minor release with @deprecated and a replacement hint.
**-** Remove in the next major release.
**-** Document changes in CHANGELOG.md and migration guides.
**-** Register each deprecation in documentation/src/operations.md with a migration file.

**SECURITY FIXES**
**-** Security fixes may ship outside the normal cadence.
**-** If a fix is not backwards compatible, it will be communicated with a mitigation guide.

**END OF LIFE**
Once a line reaches end of security support, no further fixes are provided.

**VISIBILITY**
**-** The current support matrix lives in this file.
**-** The public roadmap lives in documentation/src/operations.md.

**GITHUB STARTER PACK (FINELLA)**
This is the recommended rollout order for repository governance and release safety.

**-** Baseline security and CI (already enabled in this repo):
**-** `CI`, `CodeQL`, and `OSV Scan` workflows.
**-** `SECURITY.md` policy in `.github/SECURITY.md`.
**-** Dependabot updates via `.github/dependabot.yml`.

**-** Contribution flow (implemented in this repo):
**-** `CODEOWNERS` in `.github/CODEOWNERS`.
**-** PR template in `.github/PULL_REQUEST_TEMPLATE.md`.
**-** Issue forms in `.github/ISSUE_TEMPLATE/*.yml`.

**-** Release automation (implemented in this repo):
**-** Final published release notes come from `CHANGELOG.md` via `scripts/release/publish-release.php`.
**-** Draft release generation is intentionally disabled.
**-** GitHub release workflow (`.github/workflows/release.yml`) is manual-only (`workflow_dispatch`).

**-** Dependency maintenance automation (implemented in this repo):
**-** Dependabot updates are enabled via `.github/dependabot.yml`.
**-** Auto-merge is intentionally disabled; dependency PRs are merged manually after checks.

**-** Repository settings to enable in GitHub UI (manual):
**-** Rulesets / branch protection for `main`:
  **-** Require pull request before merge.
  **-** Require status checks (`CI`, `CodeQL`, `OSV Scan`).
  **-** Require up-to-date branch before merging.
  **-** Block force-push and deletion.
**-** Enable Merge Queue for `main`.
**-** Configure environments (for example `production`) with required reviewers.
**-** Optionally enable Discussions and GitHub Projects for roadmap/community flow.

**ENTERPRISE READINESS**

This checklist is a practical baseline for positioning Finella in enterprise environments.
Treat it as a living document: tick items as they become true in your delivery process.

**1) SECURITY POSTURE**
**-** Dependency scanning enabled (Dependabot + OSV scans in CI).
**-** SAST enabled (CodeQL or equivalent) and reviewed regularly.
**-** Secrets never committed; `.env` never committed.
**-** Production defaults hardened (`APP_DEBUG=0`, security headers, HTTPS redirects).
**-** Vulnerability reporting process documented (`.github/SECURITY.md`).

**2) SDLC QUALITY GATES**
**-** CI runs static analysis (PHPStan, optional Psalm).
**-** CI runs unit/integration tests with clear pass/fail gates.
**-** Coverage report is generated and a minimum coverage gate enforced.
**-** Release gate must be green before tagging.
**-** Deprecations and migrations documented for every release.

**3) OBSERVABILITY**
**-** Structured logs in production (`LOG_FORMAT=json`).
**-** Request and trace IDs enabled (`X-Request-Id`, `X-Trace-Id`, `X-Span-Id`).
**-** `/health` and `/ready` wired into monitoring.
**-** Errors are routed to an error reporter (Sentry/Bugsnag/etc).

**4) RELIABILITY**
**-** Background queues use a real driver (Redis/SQS) in production.
**-** Scheduler is running and monitored.
**-** Cache is configured for production (Redis or equivalent).
**-** Rollback strategy documented and tested.
**-** Database migrations follow expand/contract patterns for zero downtime.

**5) DATA PROTECTION**
**-** Backups are scheduled and tested.
**-** RPO/RTO targets are defined.
**-** Secrets and encryption keys are stored and rotated safely.
**-** Sensitive logs are redacted or avoided entirely.

**6) COMPLIANCE & GOVERNANCE**
**-** Access controls documented and audited for admin/docs.
**-** Security headers and CSP configured where needed.
**-** Support policy published and upheld.
**-** Incident response playbook defined and used.

**7) ENTERPRISE INTEGRATION READINESS (OPTIONAL)**
**-** OAuth/OIDC supported (finella/oauth).
**-** SSO/SAML integration plan defined (if required).
**-** SCIM/IdP provisioning plan defined (if required).
**-** Audit trail and activity logging enabled where needed.

**MINIMUM ENTERPRISE BAR (SUMMARY)**
To claim enterprise readiness, we recommend meeting at least:
**-** Security scanning (Dependabot + OSV + SAST).
**-** CI quality gates (tests + coverage).
**-** Observability + readiness checks.
**-** Backup/restore + rollback plan.
**-** Support policy + documented security process.

**DISTRIBUTION MODEL**

This document defines the recommended distribution model for Finella when you want broad adoption without exposing proprietary modules. The core framework remains public, while the high-value modules ship through a private registry.

**SUMMARY**
**-** **Public core**: available to everyone via Packagist (or a public Composer registry).
**-** **Private pro modules**: available only to licensed teams via a private registry.

This keeps onboarding friction low while protecting the competitive advantage.

**PUBLIC CORE PACKAGES (RECOMMENDED)**
These packages are safe to expose publicly and form the base developer experience:
**-** `finella/framework`
**-** `finella/standard`
**-** `finella/queue`
**-** `finella/scheduler`
**-** `finella/mail`
**-** `finella/ops`
**-** `finella/rbac`
**-** `finella/settings`
**-** `finella/docs`
**-** `finella/testing`
**-** `finella/debugbar` (dev only)

Starter app: shipped in the monorepo at `app/` (not a Composer package).

**PRIVATE PRO MODULES (RECOMMENDED)**
These are the differentiated, high-value modules you may want to keep private:
**-** `finella/ai`
**-** `finella/ui`
**-** `finella/analytics`
**-** `finella/content`
**-** `finella/seo`
**-** `finella/notifications`
**-** `finella/webmail`
**-** `finella/pdf`
**-** `finella/deploy`
**-** `finella/tenancy`

Adjust the split to match your business model. A good rule: **public = enable adoption**, **private = protect advantage**.

The split is tracked in a machine-readable manifest:
**-** `scripts/release/distribution-packages.json`
**-** Use `php scripts/release/list-distribution-packages.php` to print and verify the list.

**PUBLIC REGISTRY SETUP (CORE)**
**-** Create public repos for each core package (or automated split from monorepo).
**-** Register on Packagist (one-time).
**-** Submit each package repo to Packagist.
**-** Tag releases (SemVer) so Packagist can resolve versions.

**PRIVATE REGISTRY SETUP (PRO MODULES)**
Choose one:
**-** **Private Packagist** (managed, enterprise-friendly)
**-** **Satis** (self-hosted)
**-** **GitHub Packages** (simple to start)

Then:
**-** Publish your private package repos (or split from monorepo).
**-** Configure Composer auth for your team (token-based).
**-** Add a private repository entry in the app's `composer.json`.

Example (private registry entry in app `composer.json`):
```json
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://your-private-registry.example.com"
    }
  ]
}
```

**DEVELOPER WORKFLOW (FOR USERS)**
Public core install:
```bash
git clone https://github.com/kordyaczny/finella.git finella
cp -R finella/app myapp
cd myapp
composer install
```

Pro module install (after private registry auth):
```bash
composer require finella/ai
```

**RELEASE DISCIPLINE**
**-** Tag every public release.
**-** Keep core and pro modules on compatible SemVer lines.
**-** Document which packages are public vs private in release notes.

**LICENSING NOTE**
If the core is public while pro modules are private, clearly state:
**-** what is public and free to use,
**-** what requires a licence,
**-** how to request access to the private registry.

**DEPRECATIONS REGISTRY**

This registry tracks all deprecated APIs that remain available in the 2.x line.
Each entry must match a PHPDoc tag in code: `@deprecated [DEP-YYYY-NN] ...`.

**ACTIVE DEPRECATIONS**

**[DEP-2026-01] FINELLA\SUPPORT\CACHESTOREINTERFACE**
Replacement: `Finella\Contracts\Cache\CacheStoreInterface`
Removal: 3.0
Migration: see **Migration DEP-2026-01** below.

**[DEP-2026-02] FINELLA\SUPPORT\ARRAYCACHESTORE**
Replacement: `Finella\Cache\ArrayStore`
Removal: 3.0
Migration: see **Migration DEP-2026-02** below.

**[DEP-2026-03] FINELLA\SUPPORT\FILECACHESTORE**
Replacement: `Finella\Cache\FileStore`
Removal: 3.0
Migration: see **Migration DEP-2026-03** below.

**[DEP-2026-04] FINELLA\SUPPORT\QUEUE**
Replacement: `Finella\Queue\SyncQueue`
Removal: 3.0
Migration: see **Migration DEP-2026-04** below.

**RULES**
**-** Every `@deprecated` must include an ID like `[DEP-2026-01]`.
**-** Every ID must be listed in this file with Replacement, Removal, and Migration.
**-** Migration details must exist below in this document.
**-** Removals only in the next major release.

**MIGRATION DEP-2026-01**

**Deprecated:** `Finella\Support\CacheStoreInterface`
**Replacement:** `Finella\Contracts\Cache\CacheStoreInterface`

**WHAT CHANGED**
Cache store contracts moved into the `Finella\Contracts\Cache` namespace to
separate contracts from legacy helpers.

**MIGRATION STEPS**
**-** Update type hints and `use` statements to the new interface.
**-** Ensure any custom cache stores implement the new contract.

**EXAMPLE**
```php
use Finella\Contracts\Cache\CacheStoreInterface;
```

**MIGRATION DEP-2026-02**

**Deprecated:** `Finella\Support\ArrayCacheStore`
**Replacement:** `Finella\Cache\ArrayStore`

**WHAT CHANGED**
Array cache store implementations moved into core (framework).

**MIGRATION STEPS**
**-** Swap the class reference in your bindings or config.
**-** Ensure you are on a framework version that includes the core cache store.

**EXAMPLE**
```php
use Finella\Cache\ArrayStore;
```

**MIGRATION DEP-2026-03**

**Deprecated:** `Finella\Support\FileCacheStore`
**Replacement:** `Finella\Cache\FileStore`

**WHAT CHANGED**
File cache store implementations moved into the core cache module.

**MIGRATION STEPS**
**-** Swap the class reference in your bindings or config.
**-** Ensure the core cache module is enabled.

**EXAMPLE**
```php
use Finella\Cache\FileStore;
```

**MIGRATION DEP-2026-04**

**Deprecated:** `Finella\Support\Queue`
**Replacement:** `Finella\Queue\SyncQueue`

**WHAT CHANGED**
The legacy queue helper was moved to the `finella/queue` package.

**MIGRATION STEPS**
**-** Update class references to `Finella\Queue\SyncQueue`.
**-** Ensure `finella/queue` is installed and configured.

**EXAMPLE**
```php
use Finella\Queue\SyncQueue;
```

**ROADMAP**

This roadmap is directional and may evolve as feedback lands. Items are grouped by the next 12 months to keep priorities clear.

**NOW (0-3 MONTHS)**
**-** Task-oriented docs and a feature-by-feature index for faster discovery.
**-** Migration guides for Laravel and Symfony teams.
**-** Improve the default "common stack" guidance for new apps.
**-** Strengthen AI governance docs and boundaries (already in progress).
**-** Debugbar v2 UI/UX aligned with Finella UI tokens (summary cards, query filters, timeline tab, slow-query highlighting).
**-** Ship standard-stack integrations for S3, Stripe, and Sentry.
**-** Certified integrations (P0): search, OAuth/SSO, monitoring, cache/CDN, Sentry, Stripe, S3.
**-** Blueprint generators (P0): crm, school, crm-school, saas, commerce, marketplace, erp, healthcare, real-estate, logistics.
**-** AI evaluation harness (quality, safety, latency, and cost checks in CI).
**-** AI actions safety rail v2 (explicit approval steps + signed intents).

**NEXT (3-6 MONTHS)**
**-** Storage cloud drivers (GCS, Azure) and signed URL support.
**-** Stripe subscriptions and invoicing helpers.
**-** Sentry breadcrumb capture and tracing presets.
**-** ORM ergonomics: relationships, eager loading, and pagination helpers.
**-** Auth scaffolding: password reset, remember-me, and guard presets.
**-** Dev UX: richer error pages + improved debug tooling.
**-** Certified integrations (P1): additional OAuth providers, Elasticsearch, Prometheus/OpenTelemetry exporters.
**-** AI route orchestration improvements (cost/latency budget routing).
**-** RAG diagnostics and knowledge health reports.

**LATER (6-12 MONTHS)**
**-** Search adapters (Meilisearch / Elasticsearch) with index helpers.
**-** OAuth/SSO integration templates (OIDC, Google, Microsoft).
**-** Queue monitoring dashboard and job history snapshots.
**-** Stronger multi-tenant policies (isolation checks + tenant-aware queues).
**-** AI action pipelines with step-level approvals and audits.
**-** Enterprise-grade RBAC tooling and audit exports.
**-** AI-based regression triage (changelog + test impact analysis).

**PRINCIPLES**
**-** Roadmap is public and subject to change, but items marked as "Now" are priority work.
**-** Breaking changes remain reserved for major versions only.
**-** Every roadmap item ships with docs and migration notes.

**PUBLIC SECTOR COMPLIANCE CHECKLIST**

Use this checklist as a baseline for public-sector readiness. Adapt it to local
requirements, contracts, and the specific jurisdiction.

**DATA HANDLING**
**-** Data minimisation documented for each data flow.
**-** Legal basis recorded for each dataset (consent, contract, legal obligation).
**-** Retention policy defined and reviewed.
**-** Export formats and schemas documented (CSV/JSON/XML).
**-** Personal data encrypted at rest and in transit.

**ACCESS CONTROL**
**-** Role-based access enforced for admin and operational roles.
**-** Principle of least privilege applied.
**-** Audit trail enabled for critical actions.
**-** Access to `/admin` and `/docs` is gated and reviewed.

**LOGGING AND AUDITABILITY**
**-** Centralised logging with request IDs.
**-** Sensitive events have structured audit logs.
**-** Log retention aligned with policy.
**-** Incident response runbook exists and is tested.

**REPORTING & COMPLIANCE OUTPUTS**
**-** Required reports mapped to templates.
**-** Public-sector export profiles defined and tested.
**-** Compliance reports reviewed on a schedule.
**-** Evidence pack can be generated on demand.

**SECURITY & RESILIENCE**
**-** Security headers enabled.
**-** Rate limiting enabled on public endpoints.
**-** Backups verified and restore tested.
**-** Dependency updates are tracked and reviewed.

**DOCUMENTATION & GOVERNANCE**
**-** Architecture docs up to date.
**-** Change log maintained with release notes.
**-** Deprecations documented with migration notes.
**-** Support policy acknowledged for the deployment line.
