**fnlla (finella) APP HARNESS**

This `tools/harness/` directory is a **development and test harness** used inside the monorepo.
It is **not** the public starter skeleton. The starter app lives in `fnlla/fnlla`.

**REQUIREMENTS**
**-** PHP >= 8.5
**-** Composer >= 2

**LOCAL DEVELOPMENT (HARNESS)**
```bash
composer install
php -S localhost:8000 -t public
```

**COMPOSER CACHE IN RESTRICTED ENVIRONMENTS**
If your environment cannot write to the global Composer cache, set a local cache directory:
```bash
export COMPOSER_CACHE_DIR=.composer-cache
```
On Windows (PowerShell):
```powershell
$env:COMPOSER_CACHE_DIR = ".composer-cache"
```
Then run Composer normally.

**ENVIRONMENT**
**-** `.env` is created from `.env.example` during initialisation.
**-** Set `APP_KEY` when using features that encrypt data at rest.

**SESSION CONFIGURATION**
`config/session/session.php` reads the following environment variables:
**-** `SESSION_LOCK` (default `1`) enables file locking during session load/save.
**-** `SESSION_GC_PROBABILITY` (default `1`) is the percentage chance (1-100) to run session garbage collection per request.

**ROUTING**
Routes are defined in `routes/web.php`.

**MODULES INCLUDED**
**-** `fnlla/framework` - core HTTP stack, routing, container, views.
**-** Core modules (bundled in framework): database, ORM, cache, sessions, cookies, auth, CSRF, logging, request logging.
**-** `fnlla/queue` - queue manager (sync + database).
**-** `fnlla/scheduler` - schedule registry and `schedule:run`.
**-** `fnlla/mail` - Symfony Mailer adapter.
**-** `fnlla/seo` - SEO helpers (meta/OG/JSON-LD).
**-** `fnlla/content` - content repository helpers.
**-** `fnlla/analytics` - analytics event helpers.
**-** `fnlla/deploy` - deploy utilities (health + warmup commands).
**-** `fnlla/ops` - security headers, CORS, rate limiting, redirects, maintenance, static cache, forms.
**-** `fnlla/rbac` - roles and permissions with gate integration.
**-** `fnlla/settings` - key/value runtime settings store.
**-** `fnlla/audit` - audit logging helpers.
**-** `fnlla/debugbar` - debug tooling (dev only).

**MAINTENANCE COMMANDS (HARNESS)**
**-** `composer update`
**-** `composer run smoke`
**-** `bin/fnlla-migrate status`
**-** `bin/fnlla make:controller ExampleController`
**-** `bin/fnlla queue:work --once`
**-** `bin/fnlla schedule:run`

**CREDITS**
**-** Framework: fnlla (finella)
**-** Author / Organisation: [TechAyo](https://techayo.co.uk)
**-** Project Manager: Marcin Kordyaczny
