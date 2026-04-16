**GETTING STARTED**

**GETTING STARTED**

This page replaces the old Start Here, Onboarding, and Starter guides. It is the
recommended entry point for new teams.

**REQUIREMENTS**
**-** PHP >= 8.4
**-** Composer >= 2
**-** A web server for production or the PHP built-in server for local dev

**QUICK START (5 MINUTES)**
**-** Install:
```bash
git clone https://github.com/kordyaczny/finella.git finella
cd finella/app
copy .env.example .env
composer run deps:dev:install
```

**-** Bootstrap database + migrations:
```bash
php bin/finella db:bootstrap
```

**-** Run the dev server:
```bash
composer run dev
```

**-** Open the app:
**-** Product App: `http://127.0.0.1:8000/`

Docs tip (monorepo):
```bash
php bin/finella docs:generate --publish --app=.
```

The starter app is wired to local packages in this monorepo. If you want a
standalone app repository, copy `app/` into a new repo and replace the
path repositories in its `composer.json` with released package versions.
The starter is intentionally minimal; add optional modules (AI, queue, mail,
etc.) only when you need them.

**COMMON STACKS (RECOMMENDED)**
**-** Default web stack (recommended): `finella/standard`
**-** Typical product app: standard + `finella/mail` + `finella/queue` + `finella/scheduler`
**-** Content-heavy sites: standard + `finella/ops` (static cache) + `finella/seo` + `finella/content`

Note: use `.env` for secrets and deploy-time settings, and `finella/settings` for
runtime, admin-editable values.

Default stack: the starter uses `finella/standard` by default. Add optional modules
in `composer.json` only when needed (ORM, queue, scheduler, mail, tenancy,
webmail, notifications, PDF).

Example (read a runtime setting):
```php
use Finella\Settings\SettingsStore;

$settings = app()->make(SettingsStore::class);
$siteTitle = $settings->get('site_title', 'Finella');
```

Example (write a runtime setting):
```php
use Finella\Settings\SettingsStore;

$settings = app()->make(SettingsStore::class);
$settings->set('site_title', 'New title');
```

**15-MINUTE PATH**
**-** Verify liveness and readiness: `GET /health`, `GET /status`, `GET /ready`.
**-** Add a simple route in `routes/web.php`:
```php
use Finella\Http\Router;
use Finella\Http\Response;

return static function (Router $router): void {
    $router->get('/ping', fn () => Response::json(['pong' => true]));
};
```
**-** Add a controller:
```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Finella\Http\Response;

final class PingController
{
    public function __invoke(): Response
    {
        return Response::json(['pong' => true]);
    }
}
```
Register it in `routes/web.php`:
```php
use App\Controllers\PingController;
use Finella\Http\Router;

return static function (Router $router): void {
    $router->get('/ping', PingController::class);
};
```

**1-HOUR PATH (EXPANDED)**
**-** Controllers and views
**-** Service layer (route -> controller -> service -> response)
**-** Config and environment
**-** Database + migrations
**-** Auth routes and middleware

Use the starter service example:
**-** `app/src/Controllers/StatusController.php`
**-** `app/src/Services/AppStatusService.php`

**FIRST DAY PATH**
**-** Read `documentation/src/getting-started.md` (how the codebase is organised).
**-** Read `documentation/src/operations.md` (logging, readiness, security, runbooks).
**-** Read `documentation/src/operations.md` (standard + optional modules).
**-** Read `documentation/src/framework.md` (HTTP, data, async, and utilities).
**-** Check `documentation/src/developer-experience.md` for task-oriented entry points and discovery.

**STARTER MAINTENANCE (MONOREPO)**
Update the starter whenever the framework or official packages change in a way that affects:
**-** bootstrapping (`bootstrap/app.php`)
**-** routing defaults (`routes/web.php`)
**-** config defaults (`config/**/*.php`)
**-** required packages / versions

Steps:
**-** Pull the latest framework changes.
**-** Update `app/composer.json` if package requirements or PHP constraints changed.
**-** Refresh the lock file:
```bash
cd app
COMPOSER=composer.dev.json composer update
```
**-** Verify the app boots:
```bash
composer run dev
# or
php -S localhost:8000 -t public
```

**STARTER OUTSIDE THE MONOREPO (OPTIONAL)**
If you want a standalone app repository:
**-** Copy `app/` into a new repo.
**-** Use `composer.json` (stable profile) for production package sources.
**-** Keep `composer.dev.json` only for local monorepo development.
**-** In monorepo workflows, `app/composer.lock` is generated from `composer.dev.json`.

**EXISTING PROJECT (MINIMAL)**
If you want to add the framework to your own project:
**-** Add `finella/framework` to `composer.json` in `require`.
**-** Create the minimal layout:
```
public/
  index.php
bootstrap/
  app.php
config/
routes/
  web.php
resources/
  views/
storage/
```
**-** Create your bootstrap and entry point (see `framework/docs/getting-started.md`).

**NEXT STEPS**
**-** `documentation/src/getting-started.md` for layout and naming
**-** `documentation/src/framework.md` for routing, middleware, configuration, data, and async
**-** `documentation/src/operations.md` for production readiness, observability, and governance

**ONBOARDING**

This guide standardises onboarding for new developers in the Finella monorepo.
Follow it step by step to avoid drift between the framework, packages, and starter app.

**PREREQUISITES**
**-** PHP 8.4+
**-** Composer 2+
**-** Git

**SETUP (ONE-TIME)**
**-** Clone the monorepo:
```bash
git clone https://github.com/kordyaczny/finella.git finella
```
**-** Install starter dependencies:
```bash
cd finella/app
copy .env.example .env
composer run deps:dev:install
```
**-** Bootstrap the database:
```bash
php bin/finella db:bootstrap
```
**-** Run the dev server:
```bash
composer run dev
```
Open `http://127.0.0.1:8000`.

**DOCS SYNC (SOURCE OF TRUTH)**
**-** Treat `documentation/src/` as the canonical documentation.
**-** Sync into the starter before editing docs inside the app:
```bash
php scripts/ci/check-docs-sync.php --app=app
```

**FIRST-DAY CHECKLIST**
**-** Read `documentation/src/getting-started.md`.
**-** Read `documentation/src/operations.md` (CLI + testing).

**COMMON PITFALLS**
**-** Editing published docs directly (use sync or publish from `documentation/src/` instead).
**-** Forgetting to run `db:bootstrap`.
**-** Changing package versions without updating the starter lockfile.

**STANDARD UPDATE FLOW**
When you touch framework/packages:
**-** Update package code and docs in `documentation/src/`.
**-** Run `php scripts/ci/check-docs-sync.php --app=app`.
**-** Update `app/composer.json` if package constraints changed.
**-** Run `composer update` in `app`.

**SUPPORT**
If anything fails, check:
**-** `documentation/src/operations.md`

**STRUCTURE & CONVENTIONS**

This document describes the recommended application layout for Finella projects and the conventions that keep codebases consistent across teams.

**RECOMMENDED APP LAYOUT (STARTER OR STANDALONE APP)**
```
app/
  Controllers/
  Services/
  Repositories/
  Actions/
  Models/
  Requests/
  Policies/
  Jobs/
  Commands/
  Listeners/
  Modules/
bootstrap/
  app.php
  cache/
config/
database/
  migrations/
  factories/
  seeders/
public/
  index.php
resources/
  views/
routes/
  web.php
schedule.php
storage/
  cache/
  docs/
  logs/
  sessions/
```

**MONOREPO LAYOUT (THIS REPOSITORY)**
```
app/          # starter app wired to local packages
framework/
packages/
documentation/
scripts/
  ci/
  dev/
  docs/
  release/
  smoke/
tests/
tools/
  harness/     # dev/test harness used by the monorepo
```

Notes:
**-** `tools/harness/` is not the public starter. It exists to run smoke/EC tests and dev workflows.
**-** `app/` is the starter app. You can copy it out into a standalone repo if you want an app-only checkout.

**MODULE LAYOUT (FEATURE-ORIENTED)**
Use `app/src/Modules/<ModuleName>/` for large features.
```
app/
  Modules/
    Billing/
      Controllers/
      Requests/
      Policies/
      Jobs/
      Views/
      routes.php
```

**NAMING**
**-** Controllers: `*Controller.php` (e.g. `InvoiceController`).
**-** Application services: `*Service.php` (e.g. `InvoiceService`).
**-** Domain entities: nouns (`Invoice`, `Customer`).
**-** Repositories: `*Repository.php`.
**-** Middleware: `*Middleware.php`.

**LAYERS**
**-** `App\Http\*` - web layer: controllers, middleware.
**-** `App\Application\*` - use cases and application services.
**-** `App\Domain\*` - entities, value objects, domain services.
**-** `App\Infrastructure\*` - integrations, repositories, adapters.
**-** `App\Modules\*` - optional bounded contexts (feature modules).

**FILES AND FOLDERS**
**-** Keep config in `config/` and reference via `.env`.
**-** Routes live in `routes/` and should stay small and declarative.
**-** Views live in `resources/views/`.
**-** Static assets live in `public/`.

**CODE STYLE**
**-** Prefer small, explicit methods.
**-** Keep controllers thin (request -> service -> response).
**-** Avoid putting business rules in controllers.
**-** Use value objects for non-trivial primitives (money, dates, IDs).

**ROUTING CONVENTIONS**
Put global routes in `routes/web.php`. For modules, include them from the main router:
```php
require __DIR__ . '/../app/src/Modules/Billing/routes.php';
```

**CONFIGURATION**
Configuration is file-based in `config/**/*.php` and uses `env()` for environment variables.
For editable runtime settings (admin panels, non-secret flags), use `finella/settings` and store values in the database.

**STORAGE**
Runtime data belongs in `storage/` and is not committed:
```
storage/
  cache/
  logs/
  sessions/
```
