**PACKAGES**

This document summarizes every module inside `packages/` with its purpose, current scope, usage, improvement ideas, potential roadmap, and the end-user problem it solves.
For external services and adapters, see `documentation/src/ai-integrations.md`.

**CORE MODULES (BUNDLED IN FRAMEWORK)**
These capabilities ship inside `finella/framework` and do not appear in `packages/`.
**-** auth (guards, middleware, providers)
**-** database (connections, migrations, query builder)
**-** orm (active record + query helpers)
**-** sessions + cookies
**-** csrf protection
**-** cache
**-** logging + request logging
**-** console cli

**AI**
**-** Purpose: AI provider integration, routing, and governance.
**-** Current scope: model configuration, provider drivers, and policy hooks.
**-** Useful for: assistants, summaries, and workflow automation.
**-** Improve: more providers, better caching, and cost/latency observability.
**-** Expand: richer tool calling and offline fallback strategies.
**-** End-user problem: reduces manual analysis and repetitive work.

**ANALYTICS**
**-** Purpose: lightweight analytics integration.
**-** Current scope: hooks for tracking and basic analytics wiring.
**-** Useful for: understanding traffic and feature usage.
**-** Improve: richer event schema and dashboard-ready exports.
**-** Expand: built-in privacy controls and data retention options.
**-** End-user problem: lack of visibility into product adoption.

**AUDIT**
**-** Purpose: audit trail of key actions.
**-** Current scope: event recording and storage.
**-** Useful for: compliance, troubleshooting, accountability.
**-** Improve: richer metadata and filtering APIs.
**-** Expand: UI viewer and export to external SIEM.
**-** End-user problem: missing traceability of critical actions.

**CONTENT**
**-** Purpose: simple content management.
**-** Current scope: content routing and storage basics.
**-** Useful for: static pages and lightweight CMS needs.
**-** Improve: editor UI and revision history.
**-** Expand: workflows for approvals and scheduling.
**-** End-user problem: slow updates to public content.

**DEBUGBAR**
**-** Purpose: in-app debug tooling.
**-** Current scope: request diagnostics, SQL/message/error counters, and an embedded dev panel UI.
**-** Useful for: faster developer troubleshooting.
**-** Improve: richer timeline events, sampling controls, and storage for recent requests.
**-** Expand: query explain plans and distributed tracing bridges.
**-** End-user problem: long debugging cycles.

**DEPLOY**
**-** Purpose: deployment helpers and scripts.
**-** Current scope: deploy-oriented tooling.
**-** Useful for: consistent release processes.
**-** Improve: CI/CD presets and rollback tooling.
**-** Expand: blue/green deployment guides.
**-** End-user problem: risky and inconsistent deployments.

**DOCS**
**-** Purpose: documentation generation and publishing.
**-** Current scope: docs build and publish flows.
**-** Useful for: keeping docs consistent and updated.
**-** Improve: templates and searchable index.
**-** Expand: public doc portal hosting.
**-** End-user problem: outdated or missing documentation.

**MAIL**
**-** Purpose: email sending.
**-** Current scope: SMTP integration and mail dispatch.
**-** Useful for: transactional emails.
**-** Improve: templates and retry logic.
**-** Expand: multiple provider fallback.
**-** End-user problem: missing transactional communication.

**MAIL-PREVIEW**
**-** Purpose: email preview in dev.
**-** Current scope: previewing outbound messages locally.
**-** Useful for: verifying templates before production.
**-** Improve: inbox UI and filters.
**-** Expand: snapshot testing for emails.
**-** End-user problem: broken or unreviewed emails.

**MONITORING**
**-** Purpose: lightweight metrics and monitoring utilities.
**-** Current scope: counters, profiler summary, recent request traces, debugbar-derived metrics, and a metrics endpoint.
**-** Useful for: basic service visibility without external agents.
**-** Improve: exporters and structured metrics tags.
**-** Expand: Prometheus/OpenTelemetry adapters.
**-** End-user problem: limited production insight.

**NOTIFICATIONS**
**-** Purpose: notification primitives and API endpoints.
**-** Current scope: backend delivery orchestration (email/SMS) without opinionated admin/customer preset templates.
**-** Useful for: alerts and user updates.
**-** Improve: delivery channels, preferences, and templates.
**-** Expand: push/SMS/email integration packs.
**-** End-user problem: users miss important events.

**OAUTH**
**-** Purpose: OAuth/OIDC client integration.
**-** Current scope: provider config and auth flows.
**-** Useful for: social login and SSO.
**-** Improve: more providers and token refresh helpers.
**-** Expand: enterprise SSO presets.
**-** End-user problem: slow identity integrations.

**OPS**
**-** Purpose: operations middleware bundle.
**-** Current scope: security headers, CORS, rate limit, redirects, maintenance, static cache, and forms honeypot.
**-** Useful for: safe production defaults.
**-** Improve: clearer profile presets per environment.
**-** Expand: policy-as-code packaging.
**-** End-user problem: inconsistent ops hardening.

**PDF**
**-** Purpose: PDF generation.
**-** Current scope: templates and rendering.
**-** Useful for: invoices and reports.
**-** Improve: template gallery and localization.
**-** Expand: bulk generation and signing.
**-** End-user problem: manual document creation.

**QUEUE**
**-** Purpose: background job processing.
**-** Current scope: queue drivers and worker integration.
**-** Useful for: async processing and scalability.
**-** Improve: dashboards and retry policies.
**-** Expand: distributed queues and priorities.
**-** End-user problem: slow or blocking operations.

**RBAC**
**-** Purpose: roles and capabilities.
**-** Current scope: role checks and permissions.
**-** Useful for: secure access control.
**-** Improve: admin UI for permission management.
**-** Expand: attribute-based access control.
**-** End-user problem: unauthorized access.

**SCHEDULER**
**-** Purpose: scheduled job execution.
**-** Current scope: scheduled task runner.
**-** Useful for: recurring tasks.
**-** Improve: dashboards and history.
**-** Expand: distributed scheduling.
**-** End-user problem: manual recurring operations.

**SEARCH**
**-** Purpose: search adapter integration.
**-** Current scope: lightweight HTTP Meilisearch client wrapper and helpers (no external SDK dependency).
**-** Useful for: product search and discovery.
**-** Improve: indexing pipelines and schema helpers.
**-** Expand: Elasticsearch adapter.
**-** End-user problem: slow or missing search experiences.

**SENTRY**
**-** Purpose: error tracking via Sentry.
**-** Current scope: SDK init and capture helpers.
**-** Useful for: production error visibility and alerting.
**-** Improve: automatic breadcrumb capture.
**-** Expand: performance tracing helpers.
**-** End-user problem: silent production failures.

**SEO**
**-** Purpose: SEO utilities.
**-** Current scope: meta tag helpers.
**-** Useful for: better search visibility.
**-** Improve: structured data helpers.
**-** Expand: sitemap automation.
**-** End-user problem: poor discovery in search.

**SETTINGS**
**-** Purpose: runtime settings store.
**-** Current scope: key/value settings with storage.
**-** Useful for: feature flags and admin settings.
**-** Improve: UI and versioning.
**-** Expand: environment overrides.
**-** End-user problem: rigid configuration requiring deploys.

**STANDARD**
**-** Purpose: recommended default meta-package.
**-** Current scope: framework + ops + rbac + settings + audit + deploy.
**-** Useful for: quick, consistent production-ready setups.
**-** Improve: profiles for app types.
**-** Expand: lite and enterprise presets.
**-** End-user problem: slow initial package selection.

**STORAGE**
**-** Purpose: file storage abstraction.
**-** Current scope: local storage support.
**-** Useful for: uploads and assets.
**-** Improve: cloud drivers (S3, GCS).
**-** Expand: signed URLs and lifecycle policies.
**-** End-user problem: insecure or inconsistent file storage.

**STORAGE-S3**
**-** Purpose: S3 storage adapter for finella/storage.
**-** Current scope: basic S3 disk operations + URL helper.
**-** Useful for: cloud uploads and media assets.
**-** Improve: multipart uploads and streaming.
**-** Expand: presigned URLs and caching controls.
**-** End-user problem: lack of scalable storage.

**STRIPE**
**-** Purpose: payments adapter for Stripe.
**-** Current scope: client factory + webhook verification helper.
**-** Useful for: checkout flows and billing.
**-** Improve: ready-made payment intents helpers.
**-** Expand: subscriptions and invoicing flows.
**-** End-user problem: slow payment integration.

**TENANCY**
**-** Purpose: multi-tenant support.
**-** Current scope: tenant resolution and isolation helpers.
**-** Useful for: SaaS apps.
**-** Improve: tenant provisioning and billing hooks.
**-** Expand: data isolation enforcement policies.
**-** End-user problem: multi-tenant data leakage risk.

**TESTING**
**-** Purpose: test utilities and helpers.
**-** Current scope: testing base classes and HTTP helpers.
**-** Useful for: faster test writing.
**-** Improve: fixtures and mocks.
**-** Expand: contract testing utilities.
**-** End-user problem: slow or brittle test suites.

**WEBMAIL**
**-** Purpose: IMAP/SMTP integration.
**-** Current scope: inbound/outbound mail handling.
**-** Useful for: in-app mail features.
**-** Improve: inbox UI and sync performance.
**-** Expand: threading and search.
**-** End-user problem: lack of integrated mail workflows.

**_SHARED**
**-** Purpose: internal shared assets/utilities.
**-** Current scope: shared resources for dev.
**-** Useful for: reducing duplication across packages.
**-** Notes: not a public package; do not require it directly.
**-** Improve: better API contracts and documentation.
**-** Expand: stricter ownership boundaries.
**-** End-user problem: not end-user facing (internal).
