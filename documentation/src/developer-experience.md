**DEVELOPER EXPERIENCE**

**CLI**
Finella ships with a small CLI to generate docs, publish assets, and run project tooling. The command list depends on installed packages and entries in `config/console/console.php`.

**QUICK START**
**-** Run commands from your app root.
**-** `php bin/finella` shows available commands.
**-** `php bin/finella list` prints the full command list.

**REQUIRED `--app` FLAG**
Most publish commands need the application root path. Use `--app=.` when you are already in the app directory.
```bash
php bin/finella docs:generate --app=.
```

**DOCS WORKFLOWS**
**-** `php bin/finella docs:generate --app=.`
**-** `php bin/finella docs:generate --app=. --publish`

Docs are generated into `storage/docs/generated`. Publishing copies them into `resources/docs`.

**MARKDOWN FORMATTING**
Repo markdown formatting is standardised by:
**-** `scripts/docs/format-markdown.php`

Common commands (repo root):
**-** `php scripts/docs/format-markdown.php --profile project --scope all`
**-** `php scripts/docs/format-markdown.php --check --profile project --scope all`
**-** `php scripts/docs/format-markdown.php --check --profile release --scope github`

Starter shortcuts (from `app/`):
**-** `composer run format:markdown`
**-** `composer run lint:markdown`
**-** `composer run format:github`
**-** `composer run lint:github`

**UI AND MAIL PREVIEW**
**-** `php bin/finella ui:publish --app=.`
**-** `php bin/finella ui:admin:publish --app=.`
**-** `php bin/finella ui:elements:publish --app=.`
**-** Deprecated alias: `php bin/finella ui:examples:publish --app=.`
**-** `php bin/finella mail-preview:publish --app=.`

Tip: use `--only=login,pricing` or `--exclude=carousel,cheatsheet` for a smaller UI Elements gallery.

**CACHE WORKFLOWS**
**-** `php bin/finella routes:cache`
**-** `php bin/finella routes:clear`

**SCAFFOLDING**
**-** `php bin/finella make:controller UserController`
**-** `php bin/finella make:request StoreUserRequest`
**-** `php bin/finella make:model User --migration --factory`
**-** `php bin/finella make:crud User`
**-** `php bin/finella make:service BillingService`
**-** `php bin/finella make:repository UserRepository`
**-** `php bin/finella make:seeder UsersSeeder`

**DATABASE BOOTSTRAP**
Create the database (when missing) and run migrations:
**-** `php bin/finella db:bootstrap`
**-** `php bin/finella db:bootstrap --create --root-user=root --root-pass=secret`

**TROUBLESHOOTING**
**-** "Missing --app=PATH" means pass `--app=.` or an absolute path.
**-** "App path not found" means the path in `--app` is wrong.

**AI COMMANDS (CORE)**
These commands are deterministic and run without any provider API key.
**-** `php bin/finella ai:scaffold User --resource --all`
**-** `php bin/finella ai:doctor`
**-** `php bin/finella ai:config-advisor`
**-** `php bin/finella ai:security-lint`
**-** `php bin/finella ai:observability --lines=2000`
**-** `php bin/finella ai:docs-sync`
**-** `php bin/finella ai:test-plan Checkout`
**-** `php bin/finella ai:roadmap-balance`
**-** `php bin/finella ai:release-notes --version=Unreleased`

**PACKAGES**
Full package catalog lives in `documentation/src/packages.md`.

**INTERNAL `_shared`**
`packages/_shared` is internal scaffolding for package development and tests.
Do not require or import it as an end-user package.

**TASK GUIDES**

**I WANT A WEB APP WITH AUTH**
**-** Install standard: `composer require finella/standard`
**-** Register auth middleware alias in `config/http/http.php`
**-** Add login routes in `routes/web.php`
**-** See `documentation/src/framework.md` for guard usage

**I WANT AN API-ONLY SERVICE**
**-** Keep views minimal and use `Response::json`
**-** Add CORS and rate limit middleware
**-** Use core database + ORM for data
**-** See `documentation/src/framework.md` (HTTP + validation)

**I WANT MIGRATIONS AND MODELS**
**-** `php bin/finella make:migration create_users_table`
**-** `php bin/finella migrate`
**-** See `documentation/src/framework.md` (database + ORM)

**I WANT A QUEUE WORKER**
**-** `composer require finella/queue`
**-** Set `QUEUE_DRIVER=database` or `redis` in `.env`
**-** Run `php bin/finella queue:work`
**-** See `documentation/src/framework.md` (queue)

**I WANT SCHEDULED JOBS**
**-** `composer require finella/scheduler`
**-** Define jobs in `routes/schedule.php`
**-** Run `php bin/finella schedule:run`
**-** See `documentation/src/framework.md` (scheduler)

**I WANT MAIL SENDING**
**-** `composer require finella/mail`
**-** Configure `MAIL_*` env values
**-** Send mail via `MailManager`
**-** See `packages/mail/README.md`

**I WANT PDF GENERATION**
**-** `composer require finella/pdf`
**-** Configure `PDF_*` env values
**-** Use `PdfManager` and templates
**-** See `documentation/src/framework.md` (PDF)

**I WANT AI ASSISTANCE (OPTIONAL)**
**-** Keep `AI_DRIVER=mock` for local demo
**-** Set `AI_DRIVER=openai` and `OPENAI_API_KEY` to enable providers
**-** Review `documentation/src/ai-integrations.md` for governance and boundaries

**FEATURE INDEX**
Use this index when you are looking for a capability rather than a package name.

**CORE FRAMEWORK**
**-** Application container and config: `documentation/src/framework.md`
**-** HTTP lifecycle, routing, middleware: `documentation/src/framework.md`
**-** Responses, requests, and helpers: `documentation/src/framework.md`

**DATA AND STORAGE**
**-** Database + migrations: `documentation/src/framework.md`
**-** ORM models, scopes, soft deletes: `documentation/src/framework.md`
**-** Runtime settings: `documentation/src/framework.md` (settings)
**-** Caching patterns: `documentation/src/framework.md`
**-** Storage (local): `packages/storage/README.md`
**-** Storage (S3): `packages/storage-s3/README.md`

**SECURITY AND ACCESS**
**-** CSRF protection: `documentation/src/framework.md` (core)
**-** Security headers and ops middleware: `packages/ops/README.md`
**-** RBAC and roles: `packages/rbac/README.md`

**ASYNC AND JOBS**
**-** Queue workers: `documentation/src/framework.md`
**-** Scheduler: `documentation/src/framework.md`

**EMAILS AND NOTIFICATIONS**
**-** Mail sending: `packages/mail/README.md`
**-** Mail preview: `packages/mail-preview/README.md`
**-** Notifications: `packages/notifications/README.md`

**OBSERVABILITY AND OPS**
**-** Logging and request logging: `documentation/src/operations.md`
**-** Monitoring endpoints: `packages/monitoring/README.md`
**-** Health and readiness: `documentation/src/operations.md`
**-** Deploy tooling: `packages/deploy/README.md`
**-** Error tracking (Sentry): `packages/sentry/README.md`

**SEARCH AND IDENTITY**
**-** Search adapter (Meilisearch): `packages/search/README.md`
**-** OAuth/OIDC: `packages/oauth/README.md`

**UI AND DOCS**
**-** Finella UI: `ui/index.md`
**-** Packages catalog: `documentation/src/packages.md`
**-** Docs generation: `documentation/src/operations.md` and `packages/docs/README.md`

**TESTING AND DX**
**-** Testing helpers: `packages/testing/README.md`
**-** Debug tooling: `packages/debugbar/README.md`
