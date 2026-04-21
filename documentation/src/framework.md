**FRAMEWORK**

**DEVELOPER GUIDE**

This guide is a single-file overview of the fnlla (finella) framework and its ecosystem. It is intentionally concise and keeps links to deeper docs in `documentation/src/`.

**CONTENTS**
**-** Overview
**-** Quick start
**-** Architecture
**-** Routing
**-** Middleware
**-** Container and services
**-** Configuration
**-** Providers and discovery
**-** Views
**-** Error handling and logging
**-** Packages
**-** Testing
**-** Deployment
**-** Upgrade policy

**OVERVIEW**
fnlla (finella) is an AI-assisted, modular PHP framework with a minimal core: kernel, router, container, configuration, and error handling. Optional features live in packages.

**QUICK START**
```php
// routes/web.php
use Fnlla\\Http\Router;
use Fnlla\\Http\Response;

return static function (Router $router): void {
    $router->get('/', [\App\Controllers\HomeController::class, 'index']);
};
```

**ARCHITECTURE**
**-** `public/index.php` loads autoload and the kernel.
**-** `bootstrap/app.php` builds the container and config.
**-** `HttpKernel` sets up middleware and routing.

**COMPONENT MAP (HIGH-LEVEL)**
```text
App (starter or custom)
  -> Framework Core (HTTP, container, config, errors)
  -> Core Modules (auth, database, ORM, cache, sessions, logging, console)
  -> Optional Packages (queue, scheduler, mail, docs, ops, ai, etc.)
```
Notes:
**-** The framework core is always required.
**-** Optional packages are installed only when you need them.

**WARM KERNEL (LONG-RUNNING)**
For long-running servers, boot once and reuse the kernel:
```php
$kernel = new \Fnlla\\Http\HttpKernel();
$kernel->boot();
```
Register resetters for per-request cleanup:
```php
$app->registerResetter(new \App\Support\MyResetter());
```

**ROUTING**
**-** Supports parameters, named routes, and controller handlers.
**-** Uses container DI when available.
**-** Routes cache is intended for production and is skipped when `APP_DEBUG=1` or `APP_ENV=local`.
**-** Cached routes require string handlers and middleware (closures are not cacheable).

**MIDDLEWARE**
**-** Global middleware in `config/http/http.php`.
**-** Per-route middleware via router methods.
**-** Group middleware via `middlewareGroup()`.
**-** Built-ins include `rate:` throttling and `can:` authorization.

**CONTAINER AND SERVICES**
Use `bind`, `singleton`, `instance`, and `call` to register and resolve services. The container auto-resolves constructor dependencies when possible.

**CONFIGURATION**
Config is loaded from `config/**/*.php` via `ConfigRepository`. Most settings are environment driven through `env()` keys.
You can add `config/schema.php` to validate critical configuration on boot.

**PROVIDERS AND DISCOVERY**
Providers register and boot services. Discovery reads Composer metadata and caches to `bootstrap/cache/providers.php`. App templates refresh the cache when Composer metadata changes.

**VIEWS**
Templates live in `resources/views`. Use `view('pages/home')` or `View::render()`.

**ERROR HANDLING AND LOGGING**
`ExceptionHandler` renders safe responses. Use the core logging module for PSR-3 logging. Responses include `X-Request-Id`, `X-Trace-Id`, and `X-Span-Id` by default (configurable in `config/http/http.php`).

**PACKAGES**
Core modules live in the framework (auth, database, ORM, sessions, cookies, CSRF, cache, logging, request logging, console). Optional packages provide queue, scheduler, mail, notifications, docs, monitoring, storage adapters, and more.

**TESTING**
**-** `php scripts/smoke/run-smoke-tests.php`
**-** `php app/scripts/http-smoke.php`
**-** `tools/vendor/bin/phpunit -c tools/phpunit.xml`

**DEPLOYMENT**
**-** DocumentRoot `public/`
**-** `composer install --no-dev --optimize-autoloader`
**-** Enable route cache for production

**UPGRADE POLICY**
fnlla (finella) follows SemVer with clear patch/minor/major rules.

For detailed documentation, see the individual files in `documentation/src/`.

**FRAMEWORK**

This guide consolidates the core framework docs (HTTP, data, storage, forms, async, and utilities) into one place.
Use it as the single reference for building product apps.

**CORE**

This document consolidates container usage, configuration, and support policy.

**CONTAINER BASICS**
The container supports binding, singleton services, and callable invocation.

```php
$app->singleton(Foo::class, fn () => new Foo());
$foo = $app->make(Foo::class);
```

For long-running servers, register per-request resetters:
```php
$app->registerResetter(new \App\Support\MyResetter());
```

**CONFIGURATION**
Configuration is loaded from `config/**/*.php` and uses `env()`.
Primary access is via `Fnlla\\Core\ConfigRepository`.

```php
$config = $app->configRepository();
$debug = $config->get('debug', false);
```

```php
return [
    'name' => env('APP_NAME', 'fnlla (finella)'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', false),
];
```

Optional runtime configuration can be stored in the database with `fnlla/settings`.
For action logs, enable `fnlla/audit`.

**ENVIRONMENT**
The starter uses `Fnlla\\Support\Dotenv` and loads `.env` when present.

**SUPPORT POLICY**
**-** PHP 8.5+ (CI: 8.5)
**-** Databases: SQLite, MySQL/MariaDB, PostgreSQL (PDO drivers)
**-** Cache: filesystem (default), array (in-memory), Redis (ext-redis)
**-** Queue: sync, database, Redis (ext-redis)

**HTTP**

This document consolidates HTTP lifecycle, routing, and middleware in fnlla (finella).

**REQUEST LIFECYCLE**
`HttpKernel` wraps the request lifecycle and runs middleware + routing.

**REQUEST/BOOT DIAGRAM (ASCII)**
```text
PSR-7 Request
  |
  v
HttpKernel::handle()
  |
  |-- Resolve app root + load config (config/**/*.php)
  |-- Build ConfigRepository + Application
  |-- Set timezone + validate config schema (optional)
  |-- Register providers -> boot providers
  |-- Load plugins (optional)
  |
  |-- Build fnlla (finella) Request + Router
  |-- Register middleware groups + aliases + global middleware
  |-- Load routes cache OR config/routes.php + routes/web.php
  |
  v
Router::dispatch()
  |
  |-- Match route
  |-- Build middleware pipeline (global -> group -> route)
  |-- Resolve handler via container (DI)
  v
Response
  |
  |-- ExceptionHandler on errors
  v
HTTP Response
```

**WARM KERNEL (LONG-RUNNING)**
For long-running servers you can boot once and reuse the kernel per request:
```php
$kernel = new \Fnlla\\Http\HttpKernel();
$kernel->boot();
// handle requests in a loop
```
This skips provider/plugin bootstrapping on each request. Use resetters for per-request cleanup:
```php
$app->registerResetter(new \App\Support\MyResetter());
```

**ROUTING**
Routes support parameters and DI when the container is available.

**ROUTES CACHE (PRODUCTION)**
Routes cache is intended for production and is skipped when `APP_DEBUG=true` or `APP_ENV=local`.
Cached routes require string handlers and middleware, so closures are not cacheable.

**BASIC ROUTE**
`routes/web.php`
```php
use Fnlla\\Http\Router;
use Fnlla\\Http\Response;

return static function (Router $router): void {
    $router->get('/', fn () => Response::text('Hello fnlla (finella)'));
};
```

**ROUTE PARAMETERS**
```php
use Fnlla\\Http\Request;
use Fnlla\\Http\Response;

$router->get('/users/{id}', function (Request $request): Response {
    return Response::json(['id' => $request->getAttribute('id')]);
});
```

**CONTROLLERS**
```php
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/account', 'AccountController@index');
```

**OTHER VERBS**
```php
$router->patch('/cards/{id}', [CardsController::class, 'update']);
$router->add('DELETE', '/cards/{id}', [CardsController::class, 'delete']);
```

**NAMED ROUTES**
```php
$router->get('/users/{id}', [UserController::class, 'show'], name: 'users.show');
route('users.show', ['id' => 123]);
```

**GROUPING**
```php
$router->group(['prefix' => '/admin', 'middleware' => ['auth']], function (Router $router): void {
    $router->get('/users', [AdminUsersController::class, 'index']);
});
```

**MIDDLEWARE**
Middleware can be global, grouped, or per-route.

**GLOBAL MIDDLEWARE**
`config/http/http.php`
```php
use Fnlla\\Http\Middleware\SecurityHeadersMiddleware;

return [
    'global' => [
        SecurityHeadersMiddleware::class,
    ],
];
```

**PER-ROUTE MIDDLEWARE**
```php
$router->get('/dashboard', fn () => view('dashboard'), middleware: ['auth']);
```

**MIDDLEWARE ALIASES**
Aliases can shorten route middleware definitions:
```php
return [
    'middleware_aliases' => [
        'auth' => \Fnlla\\Auth\AuthMiddleware::class,
    ],
];
```

**RESPONSES**
Helpers:
**-** `Response::text()`
**-** `Response::html()`
**-** `Response::json()`
**-** `Response::xml()`

fnlla (finella) adds `X-Request-Id`, `X-Trace-Id`, and `X-Span-Id` to responses by default.
Disable with `http.request_id_header=false`, `http.trace_id_header=false`, or `http.span_id_header=false`.

**DATABASE**

This document consolidates migrations and ORM usage.

**MIGRATIONS**
Create and run migrations with the CLI:
```bash
php bin/fnlla make:migration create_users_table
php bin/fnlla migrate
```

**ORM BASICS**
Define a model:
```php
use Fnlla\\Orm\Model;

final class User extends Model
{
    protected string $table = 'users';
}
```

Query examples:
```php
$users = User::query()->where('active', true)->get();
$recent = User::where('status', 'active')->latest()->take(10)->get();
$exists = User::where('email', 'ada@example.test')->exists();
$names = User::orderBy('created_at')->pluck('name');
```

Accessors and mutators:
```php
final class User extends Model
{
    public function getNameAttribute(?string $value): string
    {
        return strtoupper($value ?? '');
    }
}
```

**RELATIONSHIPS**
Use model relations where applicable (see ORM docs in framework package for details).
Supported relations: `belongsTo`, `hasMany`, `hasOne`, and `belongsToMany`.

Eager loading supports nested relations:
```php
$users = User::query()->with(['posts.comments'])->get();
```

Relation helpers:
```php
$user->posts()->create(['title' => 'Hello']);
$role->users()->attach($userId);
$role->users()->sync([$userId, $adminId]);
```

**SOFT DELETES AND SCOPES**
Models can opt into soft deletes by using `Fnlla\\Orm\SoftDeletes`.
```php
use Fnlla\\Orm\SoftDeletes;

final class User extends Model
{
    use SoftDeletes;
}
```
Query helpers:
```php
User::query()->withTrashed()->get();
User::query()->onlyTrashed()->get();
User::whereBetween('created_at', ['2026-01-01', '2026-12-31'])->get();
User::whereNotIn('status', ['blocked'])->get();
User::query()->sum('balance');
User::query()->avg('score');
User::query()->min('created_at');
User::query()->max('created_at');
```
Local scopes:
```php
final class User extends Model
{
    public function scopeActive(\Fnlla\\Orm\QueryBuilder $query): void
    {
        $query->where('status', 'active');
    }
}
```

Global scopes:
```php
final class User extends Model
{
    protected static array $globalScopes = ['active' => 'activeScope'];

    public function activeScope(\Fnlla\\Orm\QueryBuilder $query): void
    {
        $query->where('status', 'active');
    }
}

$raw = User::withoutGlobalScope('active')->get();
$raw = User::withoutGlobalScopes()->get();
```

Relation filters:
```php
User::whereHas('posts')->get();
User::whereHas('posts.comments')->get();
User::withCount('posts')->get();
User::withCount('posts as published_posts')->get();
User::withSum('posts', 'score')->get();
```

**RUNTIME SETTINGS & AUDIT**
If you need runtime configuration or audit trails stored in the database:
**-** `fnlla/settings` - simple key/value store for admin-configurable settings.
**-** `fnlla/audit` - audit log for tracking who changed what.

**MULTI-TENANCY**
For tenant-scoped data, enable `fnlla/tenancy` and extend `TenantModel`:
```php
use Fnlla\\Tenancy\TenantModel;

final class Project extends TenantModel
{
    protected string $table = 'projects';
}
```
Set `TENANCY_ENABLED=1` and add the `TenantMiddleware` in `config/http/http.php`.

**STORAGE**

**FORMS & VALIDATION**

fnlla (finella) provides a lightweight HTML form flow built on top of validation, session flash, and response helpers.

**VALIDATION QUICK USAGE**
Validate inside a route or controller:
```php
use Fnlla\\Http\Request;
use Fnlla\\Support\ValidationException;

$router->post('/signup', function (Request $request) {
    try {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email',
            'age' => 'nullable|integer|min:18',
        ], [], 'signup');

        // $data contains validated values.
        return \Fnlla\\Http\Response::json(['ok' => true]);
    } catch (ValidationException $e) {
        // For HTML requests, fnlla (finella) flashes errors + old input to session
        // and redirects back automatically (see below).
        return \Fnlla\\Http\Response::redirect('/signup');
    }
});
```

For JSON requests (`Accept: application/json` or `/api`), the exception handler returns:
```json
{
  "message": "Validation failed.",
  "errors": { "field": ["..."] },
  "old": { "field": "..." },
  "bag": "default"
}
```
with HTTP status `422 Unprocessable Entity`.

**SUPPORTED RULES**
Rules can be strings (`'required|string|min:3'`) or arrays (for closures and custom rules).

Basic types:
**-** `string`, `integer`, `numeric`, `boolean`, `date`, `email`, `url`, `uuid`
**-** `array` (must be array)
**-** `list` (array with sequential numeric keys)
**-** `file` (instance of `UploadedFile`)

Presence:
**-** `required`
**-** `nullable` (skips other rules if the value is empty)
**-** `sometimes` (validate only when the field exists)
**-** `confirmed` (requires `<field>_confirmation`)

Constraints:
**-** `min:x` / `max:x`
  **-** For strings: length
  **-** For arrays: count
  **-** For numbers: numeric value
  **-** For files: byte size
**-** `in:a,b,c`
**-** `regex:/pattern/`
**-** `mimes:image/png,image/jpeg` or `mimes:jpg,png,pdf`
**-** `size:1024` (bytes for files)
**-** `date:Y-m-d` (format-aware date validation)

**NESTED ARRAYS AND LISTS**
Use dot notation with `*` wildcards:
```php
$request->validate([
    'items' => 'required|array',
    'items.*.sku' => 'required|string',
    'items.*.qty' => 'required|integer|min:1',
]);
```
Errors will be keyed with concrete paths such as `items.0.qty`.

**FILE VALIDATION**
```php
$request->validate([
    'avatar' => 'required|file|mimes:image/png,image/jpeg|size:204800',
]);
```
`size` is in bytes. The `mimes` rule checks the client MIME type first and falls back to file extension.

**CUSTOM RULES**
Inline rule callbacks:
```php
$request->validate([
    'slug' => [
        'required',
        'string',
        function ($value, string $field) {
            return preg_match('/^[a-z0-9-]+$/', (string) $value) === 1
                ? true
                : 'Slug may only contain a-z, 0-9 and dashes.';
        },
    ],
]);
```

Register reusable rules:
```php
use Fnlla\\Support\Validator;

Validator::extend('upper', function ($value): bool {
    return is_string($value) && strtoupper($value) === $value;
}, 'The :attribute must be uppercase.');

$data = Validator::make($input, [
    'code' => 'required|upper',
])->validated();
```

**CUSTOM ERROR MESSAGES**
Provide messages when building a validator manually:
```php
$messages = [
    'email.required' => 'Email is required.',
    'email.email' => 'Email must be valid.',
    'required' => 'This field is required.',
];

$validator = Validator::make($input, $rules, $messages);
```
Keys are either `field.rule` or just `rule` for global fallbacks.

**HTML FORMS AND REDIRECTS**
When a `ValidationException` is thrown during an HTML request, fnlla (finella) automatically:
**-** redirects back to the previous page
**-** flashes validation errors
**-** flashes old input

For JSON requests the response remains a `422` with `{"errors":{...}}`.

**RESPONSE HELPERS**
```php
use Fnlla\\Http\Response;

return Response::redirect('/form')
    ->withErrors(['name' => ['Required']], 'default')
    ->withInput(['name' => 'Ada']);
```

Global helpers:
```php
return redirect('/form');
return back();
```

**OLD INPUT**
```php
<input name="name" value="<?= htmlspecialchars((string) old('name', ''), ENT_QUOTES, 'UTF-8') ?>">
```

**ERROR BAG**
```php
<?php $errors = errors(); ?>

<?php if ($errors->has('email')): ?>
    <p><?= htmlspecialchars($errors->first('email'), ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
```

**EXAMPLE ROUTE**
```php
$router->post('/form', function (Request $request): Response {
    $request->validate([
        'name' => 'required|string|min:2',
        'email' => 'required|email',
    ]);

    return Response::redirect('/form');
});
```

**NOTES**
**-** Flash data lasts for the next request only.
**-** File uploads are excluded from old input by default.

**NOTIFICATIONS**

`fnlla/notifications` provides backend notification delivery with basic API
endpoints. Email uses `fnlla/mail`. SMS uses a pluggable sender interface.

**CONFIG**
Create `config/notifications/notifications.php`:
```php
return [
    'auto_migrate' => false,
    'table' => 'notifications',
    'default_channel' => 'email',
];
```

**ROUTES**
```php
use Fnlla\\Notifications\NotificationsRoutes;

NotificationsRoutes::register($router, [
    'prefix' => '/api/notifications',
    'middleware' => ['auth'],
]);
```

Endpoints:
**-** `GET /api/notifications`
**-** `GET /api/notifications/{id}`
**-** `POST /api/notifications/send`

**MAIL PREVIEW**

**PDF**

`fnlla/pdf` provides HTML-to-PDF rendering via Dompdf plus ready templates for invoices and pitch decks.

**INSTALL**
```bash
composer require fnlla/pdf
```

**CONFIGURATION**
Create `config/pdf/pdf.php`:
```php
return [
    'paper' => env('PDF_PAPER', 'A4'),
    'orientation' => env('PDF_ORIENTATION', 'portrait'),
    'default_font' => env('PDF_DEFAULT_FONT', 'DejaVu Sans'),
    'remote_enabled' => (bool) env('PDF_REMOTE_ENABLED', false),
    'download_name' => env('PDF_DOWNLOAD_NAME', 'document.pdf'),
];
```

**ROUTES (OPTIONAL)**
```php
use Fnlla\\Pdf\PdfRoutes;

return static function (Router $router): void {
    PdfRoutes::register($router, ['prefix' => '/api/pdf']);
};
```
This adds `GET /api/pdf/invoice` (invoice) and `GET /api/pdf/pitch-deck` (pitch deck sample).

**USAGE**
```php
use Fnlla\\Pdf\PdfManager;
use Fnlla\\Pdf\Templates\InvoiceTemplate;
use Fnlla\\Pdf\Templates\PitchDeckTemplate;

public function invoice(PdfManager $pdf): Response
{
    $template = new InvoiceTemplate();
    $html = $template->render([
        'number' => 'INV-2026-0001',
        'client' => 'Acme Ltd',
        'currency' => 'USD',
        'items' => [
            ['label' => 'Website design', 'qty' => 1, 'price' => 2400],
            ['label' => 'Hosting (12 months)', 'qty' => 1, 'price' => 300],
        ],
    ]);

    $binary = $pdf->render($html);
    return $pdf->download($binary, 'invoice.pdf');
}
```

Generate a pitch deck PDF:
```php
$template = new PitchDeckTemplate();
$html = $template->render([
    'project' => ['name' => 'Acme', 'tagline' => 'Next-gen workflow'],
    'company' => ['website' => 'https://acme.test'],
]);
$binary = $pdf->render($html);
return $pdf->download($binary, 'pitch-deck.pdf');
```

**UI INTEGRATION**
A UI can call `/api/pdf/invoice?download=1` or `/api/pdf/pitch-deck?download=1` to trigger a file download.
Use your own controller/template if you need custom layouts.

**ASYNC**

This document consolidates events, queues, and schedulers.

**EVENTS**
Register listeners and dispatch events for decoupled workflows.

**QUEUE**
Configure the queue driver in `config/queue/queue.php`.

Use the worker:
```bash
php bin/fnlla queue:work
```

Redis driver:
```bash
QUEUE_DRIVER=redis
QUEUE_NAME=default
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```
Requires the `ext-redis` PHP extension.
The worker supports both `database` and `redis` drivers.

**SCHEDULER**
Define schedules in `routes/schedule.php` and run:
```bash
php bin/fnlla schedule:run
```

**UTILITIES**

This page lists utility helpers that are available out of the box in fnlla (finella).

**UPLOADPOLICY**
Validate uploads and sanitize filenames:
```php
use Fnlla\\Support\UploadPolicy;

$errors = UploadPolicy::validate($file, [
    'max_bytes' => 10 * 1024 * 1024,
    'allowed_mimes' => ['image/png', 'image/jpeg'],
    'blocked_extensions' => ['php', 'exe'],
]);

$safeName = UploadPolicy::sanitizeFilename($file->getClientFilename() ?? '');
```

**SCHEMAINSPECTOR**
Check for a column across SQLite/MySQL/Postgres:
```php
use Fnlla\\Database\SchemaInspector;

$inspector = new SchemaInspector();
if ($inspector->hasColumn('users', 'last_seen')) {
    // safe to read/write this column
}
```

**HTTPCLIENT**
Simple HTTP wrapper with JSON helpers:
```php
use Fnlla\\Support\HttpClient;

$client = new HttpClient();
$response = $client->getJson('https://example.com/api/status');
if ($response['ok']) {
    $data = $response['data'];
}
```

**REPORTPAGINATOR**
Lightweight paginator for non-ORM datasets:
```php
use Fnlla\\Support\ReportPaginator;

$paginator = new ReportPaginator($rows, $total, 25, $page, '/reports');
echo $paginator->links('partials.pagination');
```

**VALIDATIONHELPER**
Flatten validation errors into a clean list:
```php
use Fnlla\\Support\ValidationHelper;

$errors = ValidationHelper::errors($request, [
    'email' => ['required', 'email'],
]);

if ($errors !== []) {
    $html = ValidationHelper::formatAsHtmlList($errors);
}
```

**FALLBACKLOGGER**
Fallback logging to file when a PSR logger is unavailable:
```php
use Fnlla\\Support\FallbackLogger;

FallbackLogger::error('Invoice failed', ['invoice_id' => 123]);
```

**ORM ERGONOMICS**

This document defines the ORM ergonomics standard for fnlla (finella). It lists
what is already delivered and what remains on the roadmap.

**DELIVERED (3.X)**
**-** Query helpers: `firstOrFail`, `count`, `exists`, `pluck`, `take`, `skip`.
**-** Query aggregates: `sum`, `avg`, `min`, `max`.
**-** Soft deletes: `withTrashed`, `onlyTrashed`, `restore`, `forceDelete`.
**-** Scopes: `scopeX` on models with `Model::query()->x()`.
**-** Global scopes: automatic constraints applied to every query, named and toggleable.
**-** Relations: `belongsTo`, `hasMany`, `hasOne`, `belongsToMany`.
**-** Relation helpers: `create`, `save`, `saveMany`, `attach`, `detach`, `sync`.
**-** Pivot metadata: `withPivot([...])`, `withTimestamps()`, and `pivot` relation on results.
**-** Relation filters: `has` with operator/count, `whereHas`, `orWhereHas`, `whereDoesntHave`, nested `whereHas`, `withCount` (alias support).
**-** Aggregates: `withSum`, `withAvg`, `withMin`, `withMax`.
**-** Accessors/mutators: `getFooAttribute`, `setFooAttribute`.
**-** Casts: `int`, `float`, `bool`, `datetime`, `json`.

**UX GUARANTEES (GOLDEN PATH)**
**-** Model API must support Eloquent-style statics:
   **-** `User::where(...)->get()` and `User::latest()->first()`.
**-** Relation chaining must be fluent:
   **-** `$user->posts()->where(...)->latest()->get()`.
**-** Soft deletes must be opt-in and safe by default.
**-** Errors must be explicit (no silent failures for missing classes).

**ROADMAP (NEXT ERGONOMICS LAYER)**
**-** `firstOrNew`, `updateOrCreate` parity with additional options.
**-** Pivot metadata helpers (timestamps, custom columns).
**-** Lightweight query profiler for dev.

**3.0 MUST-HAVE CHECKLIST (PRIORITIES + TARGET DATES)**
P0 (target: 30 Sep 2026)
**-** `withCount` for nested relations using deep aggregates.

P1 (target: 31 Mar 2027)
**-** `belongsToMany` pivot model casting and mutators.
**-** Polymorphic relations (`morphTo`, `morphMany`).
**-** Query macros and fluent scopes registry.

**USAGE EXAMPLES**
```php
$project = Project::firstOrCreate(
    ['name' => 'Acme'],
    ['status' => 'active']
);

$active = Project::active()->withTrashed()->get();

// Relation counts with operator + alias.
$teams = Team::has('members', '>=', 5)->withCount('members as member_total')->get();

// Pivot timestamps + metadata.
$user->roles()->withTimestamps()->attach([
    1 => ['scope' => 'admin'],
    2 => ['scope' => 'editor'],
]);
```

**CACHING**

This guide summarises common caching patterns in fnlla (finella) and how to apply them safely.

**1) REQUEST-LEVEL CACHE**
**-** Use `cache()` to store small computed results.
**-** Prefer short TTLs and explicit keys.
**-** Keep values serialisable and stable.

Example:
```php
$value = cache()->remember('pricing:v1', 300, fn () => computePricing());
```

**2) TAGGED CACHE**
**-** Use tags for domain-level invalidation.
**-** Works best with Redis or file cache drivers.

Example:
```php
$cache = cache()->tags(['products', 'pricing']);
$cache->set('plan:starter', $data, 600);
```

**3) STATIC CACHE (PUBLIC PAGES)**
**-** Use `fnlla/ops` (static cache module) to cache HTML for public GET routes.
**-** Configure exclusions for admin/auth routes.

Config:
```
CACHE_STATIC_ENABLED=1
CACHE_STATIC_TTL=3600
CACHE_STATIC_EXCLUDE=/admin,/auth
```

**4) STAMPEDE PROTECTION**
**-** Use `CacheManager::remember()` which includes a lightweight lock.
**-** Keep TTLs reasonable to avoid long lock contention.

**5) CDN + ASSET CACHING**
**-** Set `ASSET_URL` to your CDN domain.
**-** Keep cache-busting in asset names (`app.12345.css`).

**6) INVALIDATION STRATEGY**
**-** Prefer explicit invalidation on write paths.
**-** Use tags for group invalidation (products, users, billing, docs).

**REFERENCES**
**-** core cache (framework)
**-** `fnlla/ops` (static HTML cache + redirects)
