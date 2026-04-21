**fnlla (finella) PACKAGES**

The `packages/` directory contains optional modules for the fnlla (finella) ecosystem. Each package is versioned independently and follows SemVer.

For the recommended public-core vs private-pro split, see `documentation/src/operations.md`.

**CORE MODULES (BUILT INTO FRAMEWORK)**
These capabilities are shipped inside `fnlla/framework` and are no longer separate packages.
**-** Database
**-** ORM
**-** Auth
**-** Sessions
**-** Cookies
**-** CSRF
**-** Cache
**-** Logging
**-** Request logging
**-** Console CLI

**OFFICIAL PACKAGES**
**-** `fnlla/queue` - queue manager and worker (sync/database/redis).
**-** `fnlla/scheduler` - schedule registry and `schedule:run`.
**-** `fnlla/mail` - Symfony Mailer adapter.
**-** `fnlla/notifications` - notification delivery (email/SMS) + API endpoints.
**-** `fnlla/webmail` - webmail backend API (IMAP/SMTP integration).
**-** `fnlla/pdf` - HTML-to-PDF rendering (Dompdf) with template helpers.
**-** `fnlla/docs` - docs automation for fnlla (finella) apps.
**-** `fnlla/storage-s3` - S3 storage adapter.
**-** `fnlla/stripe` - Stripe payments adapter.
**-** `fnlla/sentry` - Sentry error tracking adapter.
**-** `fnlla/search` - Meilisearch adapter.
**-** `fnlla/oauth` - OAuth/OIDC adapter.
**-** `fnlla/monitoring` - lightweight monitoring utilities + metrics endpoint.
**-** `fnlla/content` - content repository helpers (JSON/Markdown).
**-** `fnlla/seo` - SEO helpers (meta, OpenGraph, JSON-LD).
**-** `fnlla/standard` - default web stack meta-package (framework + ops + rbac + settings + audit + deploy).
**-** `fnlla/ops` - security headers, CORS, rate limiting, redirects, maintenance, static cache, forms.
**-** `fnlla/analytics` - analytics event helpers.
**-** `fnlla/ai` - OpenAI Responses API client and AI helpers.
**-** `fnlla/tenancy` - multi-tenant request context and model scoping.
**-** `fnlla/rbac` - roles and permissions with gate integration.
**-** `fnlla/settings` - key/value runtime settings store.
**-** `fnlla/audit` - audit logging helpers.
**-** `fnlla/debugbar` - debug tooling for development (do not enable in production).
**-** `fnlla/deploy` - deploy utilities (health + warmup commands).
**-** `fnlla/testing` - lightweight HTTP feature testing helpers.

**INSTALLATION**
Install packages individually as needed:
```bash
composer require fnlla/queue
```

**VERSIONING**
**-** Packages are compatible with `fnlla/framework ^3.0`.
**-** Minor and patch releases follow SemVer rules.
**-** Official packages share the same release version as the framework (monorepo tag).

**AUTO-DISCOVERY**
Packages may expose service providers via `extra.fnlla.providers`. The starter app caches discovered providers in `bootstrap/cache/providers.php`.
