**CHANGELOG**

All notable changes to the fnlla (finella) monorepo will be documented in this file.
This format follows Keep a Changelog and Semantic Versioning.

**RELEASE SUMMARY (FRAMEWORK)**
fnlla (finella) ships the framework and official packages on one coordinated release line.

**FRAMEWORK**
**-** Full changelog: `framework/CHANGELOG.md`

**[UNRELEASED]**

**CHANGED**
**-** Grouped configuration files into subdirectories for clarity (starter, template, harness).
**-** Updated documentation and tooling to reference grouped config paths.
**-** Added starter migration baseline templates under `app/database/migrations-baseline` and `tools/harness/database/migrations-baseline` with neutral `000001_...` naming for new app bootstraps.

**[3.0.0] - 2026-04-18**

**HIGHLIGHTS**
**-** Version-line alignment release for the public 3.x framework line.
**-** Runtime version constant, release metadata, and CI PHP baseline are now consistent.

**CHANGED**
**-** Updated framework runtime version constant to `3.0.0`.
**-** Aligned framework CI/release workflows to PHP 8.5 to match package requirements.
**-** Aligned operations documentation environment variable names with the starter app (`Fnlla_*`).

**[2.5.6] - 2026-04-15**

**HIGHLIGHTS**
**-** Stabilized CI and release quality gates after the large 2.5.5 consolidation.
**-** Aligned app and harness readiness logic to keep strict sync and hardening checks green.

**CHANGED**
**-** CI and CodeQL workflow configuration tuned for consistent pass/fail behavior on `main`.
**-** Release notes and README release metadata normalized for the current 2.5.x line.

**FIXED**
**-** Fixed AI doctor command runtime issue (`$actionsEnabled` undefined).
**-** Fixed static-analysis findings in docs parser, monitoring middleware, search HTTP client, and readiness services.
**-** Fixed app/template strict sync drift for readiness service logic between `app` and `tools/harness`.

**[2.5.5] - 2026-04-14**

**HIGHLIGHTS**
**-** Monorepo consolidation and cleanup across framework, app, packages, tools, docs, and UI.
**-** Documentation moved to the dedicated `documentation/` structure with generated static outputs.
**-** UI migration completed to top-level `ui/` with regenerated documentation index assets.

**ADDED**
**-** Support policy source page (`documentation/src/support-policy/index.md`) and related checks in release flow.
**-** Starter and harness migration baselines for neutral-prefix bootstrap paths.

**CHANGED**
**-** Refactored release tooling and CI/release-gate flow for stricter app/harness sync and docs generation consistency.
**-** Regenerated `ui/documentation/index.html` as part of the consolidated release build outputs.

**REMOVED**
**-** Removed legacy workspace-specific package surfaces and obsolete docs/layout stubs from active release paths.
**-** Removed deprecated package/template remnants no longer used by the consolidated monorepo layout.

**FIXED**
**-** Restored strict app-harness bootstrap/public sync required by release gate.

**[2.5.4] - 2026-04-14**

**HIGHLIGHTS**
**-** Pre-release hardening cycle for 2.5.x with CI stability, dependency refreshes, and quality gates.
**-** Unified baseline/analysis behavior across tools to reduce false negatives in release checks.

**ADDED**
**-** Composer audit checks integrated in CI and release gate.
**-** Baseline drift guard and additional support/config coverage in CI checks.

**CHANGED**
**-** CI dependencies upgraded (checkout/upload-artifact/paths-filter/CodeQL and related toolchain bumps).
**-** Coverage and static-analysis configuration tightened for the 2.5.x line.
**-** Minimum platform expectations aligned with PHP 8.4 in release workflows.

**FIXED**
**-** Fixed multiple CI workflow regressions (security scans, CodeQL/OSV behavior, coverage filter, env parsing, view smoke paths).
**-** Fixed grouped config path alignment and harness/app template sync issues discovered during hardening.

**[2.5.3] - 2026-04-04**

**ADDED**
**-** Ops checks for admin/docs/warm-kernel readiness (`app/scripts/ops-check.php`) and `/ready` signals.
**-** Config loader support for grouped config subdirectories (e.g. `config/ai/*`).

**CHANGED**
**-** AI config files grouped under `config/ai/` in starter, template, and harness.
**-** AI doctor/smoke checks and docs updated for the grouped config layout.

**REMOVED**
**-** Education package and related starter/harness recipes and dashboard/report assets.

**[2.5.2] - 2026-04-02**

**ADDED**
**-** DX showcase quickstart (`documentation/src/developer-experience.md`).
**-** ORM ergonomics spec (`documentation/src/framework.md`).
**-** CLI generators: `make:service`, `make:repository`, `make:seeder`.
**-** CLI generator: `make:crud`.
**-** CLI blueprint generator: `make:blueprint` (crm, school, crm-school, saas, commerce, marketplace, erp, healthcare, real-estate, logistics).
**-** ORM relation filters: `has`/`whereHas` with operator + count, `whereDoesntHave`, nested relation support, and count aliases.
**-** ORM aggregates: `sum`, `avg`, `min`, `max`, plus relation aggregates (`withSum`, `withAvg`, `withMin`, `withMax`).
**-** ORM global scopes and pivot metadata (`withPivot`, `withTimestamps`, `pivot` relation), with per-scope toggles.
**-** New recipes for Sentry, Stripe, and S3 storage adapters.
**-** CRM and SaaS domain recipes with ready dashboards + report templates.
**-** Commerce and Marketplace domain recipes with ready dashboards + report templates.
**-** Public-sector compliance checklist template added.
**-** Dashboard preview index view added for quick browsing.

**CHANGED**
**-** Starter dev tooling now includes `fnlla/debugbar` in require-dev for first-run debugging.
**-** Developer experience docs updated with DX showcase and ORM ergonomics links.
**-** Database query builder now supports `whereIn` for internal pivot queries.
**-** Starter now preloads search, OAuth/OIDC, and monitoring adapters for plug-and-play integrations.
**-** Debug error pages now include a local code excerpt in dev.
**-** Support policy checks now validate README against the LTS commitments.
**-** Removed Mermaid diagrams and rendering scripts pending accuracy review.
**-** Core runtime components (auth, cache, console, cookie, csrf, database, log, ORM, request logging, session) were absorbed into the framework to reduce shim packages and simplify installs.

**REMOVED**
**-** Legacy core shim packages (auth, cache, console, cookie, csrf, database, log, ORM, request-logging, session) from `packages/` after moving their functionality into the framework.
**-** Legacy Database public API classes removed from `Fnlla\\Database\*` (e.g., `Database`, `DatabaseManager`, `Migrator`, `QueryBuilder`, `SchemaInspector`) after framework consolidation.

**[2.5.1] - 2026-03-31**

**ADDED**
**-** Docs access protection with optional token gating (`DOCS_PUBLIC`, `DOCS_ACCESS_TOKEN`).
**-** Admin dev safety switches (`ADMIN_DEV_ENABLED`, `ADMIN_ALLOW_UNCONFIGURED`).
**-** Workspace registration gate (`WORKSPACE_REGISTRATION_ENABLED`) for production.
**-** Workspace DB manager allowlist (`WORKSPACE_DB_MANAGER_ALLOWLIST`) and safer defaults.
**-** AI Actions confirmation token + session-stored plans with TTL.
**-** Additional feature tests for docs, admin, registration, AI actions, and DB allowlist.

**CHANGED**
**-** Workspace access token/public gating enforced consistently before login.
**-** CORS defaults now use explicit allowlist (`CORS_ALLOWED_ORIGINS`) instead of wildcard.
**-** AI policy defaults tightened in production (RAG/min-sources/output limits).
**-** AI prompt/input redaction applies to user prompts and insight inputs.

**FIXED**
**-** Invite links now work when workspace access is token-gated.
**-** Admin and docs routes now follow production-safe defaults by default.

**SECURITY**
**-** Removed tracked `.env` from the repo and documented secret handling.

**[2.5.0] - 2026-03-31**

**ADDED**
**-** `fnlla/ai` package with OpenAI Responses API client and provider wiring.
**-** AI configuration stubs and docs (`documentation/src/ai-integrations.md`) for starter and app templates.
**-** RAG embeddings store and indexing helpers (`ai_rag` config + workspace reindex).
**-** Workspace AI assistant UI with RAG-backed answers and realtime token minting.
**-** AI governance policy (`ai_policy`) with temperature/output/input limits and RAG grounding rules.
**-** AI redaction and model routing configs (`ai_redaction`, `ai_router`) with fallback model support.
**-** AI telemetry storage (`ai_runs`) with workspace eval metadata.
**-** Workspace AI actions (task/bug/ADR/doc generation) with preview + apply flow.
**-** Async workspace reindex job and optional doc intelligence summaries/tags.
**-** Workspace AI insights hub with briefing, risk, backlog, ADR, meeting, forecast, ownership, docs, test gap, and explainer reports.
**-** Multi-provider AI routing with fallback provider, cost route, and A/B routing options.
**-** AI policy packs and skill packs for consistent governance and prompt guidance.
**-** Expanded Workspace AI insights (bootstrap, requirements, release notes, bug triage, API drafts, roadmap optimizer, ownership balancer, test plan, tech debt clusters, doc sync, knowledge health, quality gate).
**-** Workspace AI actions extended to modules and milestones with safety guardrails.
**-** Workspace AI CLI commands for quality gate checks and telemetry eval summaries.
**-** Autonomous Workspace insights (rule-based) with optional fallback when no LLM is configured.
**-** New CLI command `workspace:ai:autonomous-digest` for daily/weekly autonomous briefings.
**-** Autonomous smart defaults insight for cache/queue/rate-limit tuning.
**-** Framework intelligence CLI commands (scaffold, config advisor, observability, security lint, docs sync).
**-** AI readiness CLI command (`ai:doctor`) with configuration checks and suggestions.
**-** Optional daily autonomous digest schedule hook in the app template (`WORKSPACE_AI_AUTONOMOUS_SCHEDULED`).
**-** New `fnlla/storage` package with local storage and image pipeline helpers.
**-** New `fnlla/mail-preview` package with preview routes and template stubs.
**-** Added `view_path()` helper for rendering UI stubs directly from views.
**-** Admin login flow and access middleware for starter admin routes (with env-driven credentials).

**CHANGED**
**-** Updated framework positioning to "AI-assisted" across headers and docs.

**DEPRECATED**

**REMOVED**
**-** Removed offline artifact distribution (`third_party/`) and offline install docs/scripts.

**FIXED**

**SECURITY**

**[2.2.0] - 2026-03-25**

**ADDED**
**-** Warm kernel support for long-running servers (`HttpKernel::boot()`).
**-** Response tracing headers by default: `X-Request-Id`, `X-Trace-Id`, `X-Span-Id` (configurable).
**-** Routes cache compiler (`Fnlla\\Http\RouteCacheCompiler`) and CLI commands (`routes:cache`, `routes:clear`).
**-** ConfigRepository-first accessors (`Application::configRepository()`, `Container::configRepository()`).
**-** `routes_cache_strict` to control whether route cache generation fails on non-cacheable handlers.
**-** Resetter registry for per-request cleanup in long-running mode.
**-** Framework utilities: upload policy, schema inspector, HTTP client, report paginator, validation helper, fallback logger.
**-** Warm-kernel smoke test coverage for resetters and headers.

**CHANGED**
**-** Request logging now includes trace and span ids in headers and log context.
**-** App bootstraps now construct `Application` from `ConfigRepository`.
**-** Docs refreshed for routing cache, warm kernel, tracing, and versioning.

**FIXED**
**-** Cleaned up doc encoding/arrow artifacts in workspace and CLI docs.

**[2.1.0] - 2026-03-09**

**ADDED**
**-** Project Blueprint (UI + storage) initialized during onboarding.
**-** Workspace presets for common project types (modules, milestones, docs, ADR topics).
**-** Insights foundation: health warnings, activity stream, and metrics snapshots with trend deltas.
**-** Architecture Guard Lite warnings for module ownership/docs/ADR gaps.
**-** Recommendation layer for next actions on the dashboard.
**-** Workspace CLI helpers (`workspace:init`, `workspace:health`, `workspace:seed`, `workspace:metrics:snapshot`).
**-** Protected Preview Mode for Product Application with workspace-managed password gating.
**-** Public docs for Workspace, Quick Start, and release notes.

**CHANGED**
**-** Onboarding v2 captures project type, initial stage, delivery mode, and optional preset.
**-** Dashboard, progress, and empty states refined for first-run clarity.
**-** Unified Workspace IA and naming across sidebar, headers, and breadcrumbs.
**-** Access model tightened: production-safe defaults with optional access token gating.
**-** Invites flow hardened (expiry, reuse prevention, and acceptance guardrails).
**-** Capability-based role gating applied in UI and controllers.

**FIXED**
**-** Prevented write actions on Workspace modules before onboarding completes.
**-** Resolved several UI empty-state edge cases on empty projects.

**SECURITY**
**-** Added Workspace access middleware for production gating (`WORKSPACE_PUBLIC` + `WORKSPACE_ACCESS_TOKEN`).

**[2.0.0] - 2026-03-08**

**ADDED**
**-** fnlla (finella) Workspace subsystem for the starter app (`/workspace`) with separate routing and layout.
**-** Workspace metadata layer backed by `workspace_` tables and models namespace.
**-** Workspace onboarding flow that creates the initial project + owner.
**-** MVP modules: overview, progress, preÃ¢â¬âflight, roadmap, milestones, kanban, backlog, bugs,
  tech debt, module registry, ADR, documentation hub, health, activity, metrics.

**CHANGED**
**-** Starter root (`/`) now represents the product application, while `/workspace` is the developer operating layer.
**-** Added workspace toggle via `WORKSPACE_ENABLED` in the starter `.env.example`.

**[1.3.8] - 2026-03-07**

**ADDED**
**-** `fnlla/docs` package for docs automation (technical/user skeletons + CLI).
**-** Starter docs UI with manual editing and PDF export.
**-** Docs publish action for copying compiled docs into `resources/docs`.
**-** `docs:generate --publish` flag to publish docs after generation.
**-** `fnlla/pdf` pitch-deck template and sample `/api/pdf/pitch-deck` route.

**CHANGED**
**-** `/docs` now renders Markdown as HTML for a cleaner reading experience.

**[1.3.7] - 2026-03-07**

**ADDED**
**-** `fnlla/pdf` package with Dompdf renderer, invoice HTML template, and documentation.
**-** Starter pre-flight workflow at `/pre-flight` with Markdown + PDF exports.
**-** Admin kit publish command (`admin-kit:publish`) and publish docs.

**CHANGED**
**-** Docs consolidated into clearer core sections (`getting-started`, `security`, `forms-validation`, `structure-conventions`) and mirrored in starter.
**-** `fnlla/standard` now tracks baseline + ops guidance after profile removal.
**-** Offline Composer artifacts are now opt-in; `third_party/dist` is no longer shipped by default.
**-** `tools/harness` now includes PDF dependencies for smoke coverage.
**-** App skeleton now includes ORM, queue/scheduler, and mail by default for end-to-end checks.
**-** App template now ships a `config/pdf/pdf.php` stub.
**-** PHPStan toolchain includes Dompdf to analyse PDF classes.

**REMOVED**
**-** Profile packages: `fnlla/profile-backoffice`, `fnlla/profile-commerce`, `fnlla/profile-saas`.

**FIXED**
**-** PDF manager now accepts the app config wrapper used by the container.
**-** Public API snapshot refreshed (`documentation/build/api/public-api.json`).

**[1.3.6] - 2026-03-07**

**ADDED**
**-** `scripts/release/publish-release.php` to publish GitHub releases from `CHANGELOG.md` sections with encoding validation.
**-** New backend/API packages: `fnlla/notifications`, `fnlla/webmail`.
**-** Docs for notifications and webmail APIs.
**-** Webmail settings API to persist IMAP/SMTP credentials and drive runtime webmail configuration.
**-** Webmail diagnostics endpoint and encrypted storage for webmail passwords.
**-** Webmail hardening options (tenant-scoped settings, allowlists, async send, test toggles, encryption requirement).

**CHANGED**
**-** Release docs/checklist now use `scripts/release/publish-release.php` as the default path for release notes publishing.

**[1.3.5] - 2026-02-28**

**ADDED**
**-** App scaffold sync guard (`scripts/ci/check-app-template-sync.php`) to detect unexpected drift between `tools/harness` and `app`.

**CHANGED**
**-** Synced `tools/harness` with `app` for shared app scaffold files (env/config/index/test helpers), reducing intentional drift to role-specific files only.

**FIXED**
**-** App template sync check now ignores runtime-only files under `tools/harness/storage/*` and generated `bootstrap/cache/*.php` files.

**[1.3.4] - 2026-02-25**

**ADDED**
**-** Docs sidebar with search filter in starter.
**-** New docs: `start-here`, `conventions`, `environments`, `security-backup`.
**-** Local docs for API JSON and CLI help (`documentation/build/api/public-api.md`, `documentation/build/artifacts/cli-help.md`).
**-** Starter 404/500 pages with an error controller catch-all.

**CHANGED**
**-** Starter topbar partial with active states and updated typography.
**-** Docs routing now supports nested `index.md`/`README.md`.
**-** Operations guidance expanded (alerts, backups, RTO/RPO).
**-** Link colours updated for light/dark mode defaults.

**[1.3.3] - 2026-02-25**

**ADDED**
**-** Starter now bundles local Markdown docs under `resources/docs`.

**CHANGED**
**-** Docs are served via `/docs/{slug}` with an index list rendered from local files.

**[1.3.2] - 2026-02-25**

**ADDED**
**-** Starter local docs pages at `/docs` and `/docs/cli`.

**CHANGED**
**-** Starter homepage menu now links to local docs instead of private GitHub URLs.

**[1.3.1] - 2026-02-25**

**CHANGED**
**-** Starter app homepage typography refreshed and long-path wrapping improved.

**FIXED**
**-** Starter `.env.example` now quotes `TENANCY_REQUIRED_MESSAGE` to avoid dotenv parse errors.

**[1.3.0] - 2026-02-25**

**CHANGED**
**-** Baseline 1.3.0 release for new application starts (no functional changes vs 1.2.9).
**-** Updated package constraints and branch aliases to 1.3.x.
**-** Framework version constant now reports 1.3.0.

**[1.2.9] - 2026-02-25**

**ADDED**
**-** Redis cache driver with locking support and Redis queue driver.
**-** `fnlla/tenancy` package with middleware and tenant-scoped model base.
**-** Readiness checks now validate Redis when cache/queue use it.
**-** Beginner cheat sheets in PL and EN-GB.
**-** Name origin documented (fnlla (finella) Gardens, Dundee, UK).

**CHANGED**
**-** Support policy updated to include Redis cache/queue.
**-** Documentation refreshed and links validated across the docs set.

**[1.2.8] - 2026-02-24**

**ADDED**
**-** Starter service-layer example (`/status`) with `StatusController` and `AppStatusService`.
**-** Starter readiness endpoint (`/ready`) with `ReadinessController` and `AppReadinessService`.
**-** Observability quickstart section (now in `documentation/src/operations.md`).
**-** 15-minute onboarding path (now in `documentation/src/getting-started.md`).
**-** Service/repository/action conventions in structure docs.
**-** CI gating guidance (now in `documentation/src/operations.md`).

**CHANGED**
**-** Docs index now links to the getting started guide.

**[1.2.7] - 2026-02-24**

**ADDED**
**-** Structured logging options in the core logging module (JSON formatter, request id, app/env/version context).
**-** `APP_NAME`, `APP_VERSION`, `APP_BASE_PATH`, `TRUSTED_PROXIES` env support in app configs.
**-** Operations guidance (now consolidated in `documentation/src/operations.md`).
**-** Starter app health endpoint (`/health`).

**CHANGED**
**-** Logging configuration now reads from `log` or `logging` config keys.
**-** Performance guidance updated to keep request logging when needed for observability.
**-** Source headers updated to reflect proprietary licensing.

**[1.2.6] - 2026-02-24**

**ADDED**
**-** Middleware aliases (`middleware_aliases`) for shorter route/group definitions.

**CHANGED**
**-** `Request::wantsJson()` now honors `Accept` quality values and AJAX detection.

**FIXED**
**-** Routes cache is validated before loading; invalid cache is ignored (or throws in debug).
**-** Config cache now validates basic structure before loading.

**[1.2.5] - 2026-02-23**

**ADDED**
**-** Global URL helpers: `route()`, `url()`, `site_url()`, `absolute_url()`, `asset()`.

**CHANGED**
**-** Router now returns `204` for `OPTIONS` when a route exists and sets the `Allow` header.

**FIXED**
**-** 405 responses now include an `Allow` header with permitted methods.

**[1.2.4] - 2026-02-23**

**ADDED**
**-** VS Code workspace (`fnlla (finella).code-workspace`) with common tasks and recommendations.
**-** Router test covering `ResponseInterface` normalization.

**CHANGED**
**-** CI starter fresh install now runs on PHP 8.3 and 8.4.
**-** App skeleton no longer ships local absolute path repositories.

**FIXED**
**-** Router now accepts any `ResponseInterface` from handlers/middleware and normalizes it.
**-** Release gate now parses Composer version without ANSI noise.

**[1.2.3] - 2026-02-22**

**ADDED**
**-** Mojibake/BOM scan script (`scripts/ci/check-mojibake.php`) for early encoding issues.
**-** Queue worker helper (`scripts/dev/queue-worker.php`) for the database driver.
**-** Mail test helper (`scripts/dev/send-test-mail.php`) to validate mail configuration.

**CHANGED**
**-** Default `REDIRECTS_FORCE_HTTPS` to off in dev for starter apps.
**-** Strip UTF-8 BOM from `.env` before loading in app bootstraps.
**-** Smoke runner now includes mojibake and security sanity checks.
**-** Refresh starter welcome page with dark mode and docs/CLI links.

**FIXED**
**-** Mojibake scan now skips its own source file to avoid false positives.

**[1.2.2] - 2026-02-22**

**ADDED**
**-** Add new optional packages to `fnlla/standard` for default starter installs.

**[1.2.1] - 2026-02-22**

**ADDED**
**-** Wire redirects, static cache, and honeypot middleware in starter HTTP config.

**CHANGED**
**-** Bump fnlla (finella) package constraints to `^1.2` and branch aliases to `1.2.x-dev`.

**[1.2.0] - 2026-02-22**

**ADDED**
**-** New packages: `fnlla/seo`, `fnlla/content`, `fnlla/redirects`, `fnlla/forms`,
  `fnlla/analytics`, `fnlla/cache-static`, `fnlla/deploy`.
**-** New deploy commands: `deploy:health`, `deploy:warmup`.
**-** Add `THIRD_PARTY_NOTICES.md` with Composer-based dependency list.
**-** Starter config stubs for SEO, content, redirects, forms, analytics, and static cache.
**-** Added local development handover notes for internal tooling.

**CHANGED**
**-** SEO and analytics providers now honor config defaults/enable flags.
**-** Expand starter `.env.example` with app features and package toggles.
**-** Update CLI help output to include deploy commands.

**[1.1.1] - 2026-02-22**

**CHANGED**
**-** Switch licensing to proprietary for internal TechAyo use.
**-** Refresh README and internal policies (Code of Conduct, Contributing, Security).

**REMOVED**
**-** Remove legacy v1.0 planning/release docs and duplicate stubs.

**[1.1.0] - 2026-02-22**

**ADDED**
**-** New CLI generators: `make:middleware`, `make:mail`, `make:test`.
**-** New packages: `fnlla/cors` and `fnlla/maintenance` (middleware).
**-** Starter app config set for standard stack (providers + HTTP middleware + base module configs).

**CHANGED**
**-** Starter app now uses `fnlla/standard` and includes dev tooling (core CLI + `fnlla/testing`).
**-** CLI now supports short flags (e.g. `-mf` for `make:model`).

**FIXED**
**-** `fnlla-test` now constructs PHPUnit tests with method names for PHPUnit 10 compatibility.

**[1.0.4] - 2026-02-22**

**CHANGED**
**-** Clarify release hygiene and CI ordering notes in `documentation/src/operations.md`.
**-** Add a workflow comment documenting why hygiene checks run before Composer cache setup.

**[1.0.0] - 2026-02-17**

**ADDED**
**-** Initial 1.0.0 release of the monorepo packages and starter app.
