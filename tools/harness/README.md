**FINELLA APP HARNESS**

This `tools/harness/` directory is a **development and test harness** used inside the monorepo.
It is **not** the public starter skeleton. The starter app lives in `app/`.

**REQUIREMENTS**
**-** PHP >= 8.4
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
**-** `finella/framework` - core HTTP stack, routing, container, views.
**-** Core modules (bundled in framework): database, ORM, cache, sessions, cookies, auth, CSRF, logging, request logging.
**-** `finella/queue` - queue manager (sync + database).
**-** `finella/scheduler` - schedule registry and `schedule:run`.
**-** `finella/mail` - Symfony Mailer adapter.
**-** `finella/seo` - SEO helpers (meta/OG/JSON-LD).
**-** `finella/content` - content repository helpers.
**-** `finella/analytics` - analytics event helpers.
**-** `finella/deploy` - deploy utilities (health + warmup commands).
**-** `finella/ops` - security headers, CORS, rate limiting, redirects, maintenance, static cache, forms.
**-** `finella/rbac` - roles and permissions with gate integration.
**-** `finella/settings` - key/value runtime settings store.
**-** `finella/audit` - audit logging helpers.
**-** `finella/ui` - UI system with admin presets (`ui:admin:publish`).
**-** `finella/debugbar` - debug tooling (dev only).

**MAINTENANCE COMMANDS (HARNESS)**
**-** `composer update`
**-** `composer run smoke`
**-** `bin/finella-migrate status`
**-** `bin/finella make:controller ExampleController`
**-** `bin/finella queue:work --once`
**-** `bin/finella schedule:run`

**CREDITS**
**-** Framework: Finella
**-** Author / Organisation: TechAyo.co.uk
**-** Maintainer: Marcin Kordyaczny
