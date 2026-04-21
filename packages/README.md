**FNLLA (FINELLA) PACKAGES**

The `packages/` directory contains optional modules for the fnlla (finella) ecosystem. Each package is versioned independently and follows SemVer.

For the recommended public-core vs private-pro split, see `documentation/src/operations.md`.

**CORE MODULES (BUILT INTO FRAMEWORK)**
These capabilities are shipped inside `finella/framework` and are no longer separate packages.
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
**-** `finella/queue` - queue manager and worker (sync/database/redis).
**-** `finella/scheduler` - schedule registry and `schedule:run`.
**-** `finella/mail` - Symfony Mailer adapter.
**-** `finella/notifications` - notification delivery (email/SMS) + API endpoints.
**-** `finella/webmail` - webmail backend API (IMAP/SMTP integration).
**-** `finella/pdf` - HTML-to-PDF rendering (Dompdf) with template helpers.
**-** `finella/docs` - docs automation for fnlla (finella) apps.
**-** `finella/storage-s3` - S3 storage adapter.
**-** `finella/stripe` - Stripe payments adapter.
**-** `finella/sentry` - Sentry error tracking adapter.
**-** `finella/search` - Meilisearch adapter.
**-** `finella/oauth` - OAuth/OIDC adapter.
**-** `finella/monitoring` - lightweight monitoring utilities + metrics endpoint.
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

**VERSIONING**
**-** Packages are compatible with `finella/framework ^3.0`.
**-** Minor and patch releases follow SemVer rules.
**-** Official packages share the same release version as the framework (monorepo tag).

**AUTO-DISCOVERY**
Packages may expose service providers via `extra.finella.providers`. The starter app caches discovered providers in `bootstrap/cache/providers.php`.
