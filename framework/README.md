**FINELLA FRAMEWORK**

Finella is a lightweight PHP web framework focused on clarity, small surface area, and production-ready defaults. The core is intentionally minimal: kernel, router, container, configuration, and error handling.

**REQUIREMENTS**
**-** PHP >= 8.4

**INSTALLATION**
Offline install:
**-** Add `finella/framework` to `composer.json` in `require`.
**-** Configure local path repositories (see `documentation/src/getting-started.md`).
**-** Run `composer install`.

**QUICKSTART**

**BOOTSTRAP/APP.PHP**
```php
<?php

declare(strict_types=1);

use Finella\Core\Application;
use Finella\Core\ConfigRepository;
use Finella\Http\HttpKernel;

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) {
    define('APP_ROOT', $root);
}

$configRepo = ConfigRepository::fromRoot($root);
$app = new Application($root, $configRepo);

return new HttpKernel($app);
```

**PUBLIC/INDEX.PHP**
```php
<?php

declare(strict_types=1);

use Finella\Contracts\Http\KernelInterface;
use Finella\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$kernel = require __DIR__ . '/../bootstrap/app.php';
if (!$kernel instanceof KernelInterface) {
    http_response_code(500);
    echo 'Bootstrap must return a KernelInterface.';
    exit(1);
}

$request = Request::fromGlobals();
$response = $kernel->handle($request);
$response->send();
```

**WARM KERNEL (LONG-RUNNING)**
For long-running servers, boot once and reuse the kernel per request:
```php
use Finella\Http\HttpKernel;

$kernel = new HttpKernel();
$kernel->boot();
```
Ensure request-scoped state uses scoped services or resetters, for example:
```php
$app->registerResetter(new \App\Support\MyResetter());
```

**TRACING HEADERS**
Finella includes `X-Request-Id`, `X-Trace-Id`, and `X-Span-Id` on responses by default.
Disable via `config/http/http.php` (`request_id_header`, `trace_id_header`, `span_id_header`).

**ROUTES CACHE (COMPILE)**
Generate a routes cache file for production deployments:
```php
use Finella\Http\RouteCacheCompiler;

$compiler = new RouteCacheCompiler();
$path = $compiler->compile();
```

**ROUTING EXAMPLE**
```php
use Finella\Http\Router;
use Finella\Http\Response;

return static function (Router $router): void {
    $router->get('/', fn () => Response::text('Hello Finella'));
    $router->get('/users/{id}', function ($request): Response {
        return Response::text('User ' . $request->getAttribute('id'));
    });
};
```

**MIDDLEWARE EXAMPLE (OPTIONAL MODULE)**
`config/http/http.php`
```php
use Finella\SecurityHeaders\SecurityHeadersMiddleware;

return [
    'global' => [
        SecurityHeadersMiddleware::class,
    ],
];
```
Requires `finella/ops`.

**CONFIGURATION**
Finella loads configuration from `config/**/*.php`. See `documentation/src/framework.md` for the full reference.

**ENVIRONMENT**
The framework does not load `.env` by itself. The starter app uses `Finella\Support\Dotenv` and provides an `env()` helper.

**CORE VS OPTIONAL MODULES**
Framework core now includes the full app foundation: HTTP kernel, router, container, config, error handling,
logging, request tracing, sessions, auth, cookies, CSRF, database, cache, ORM, and CLI tooling.
Optional packages add specialised capabilities.

**OPTIONAL PACKAGES**
**-** `finella/ops` - security headers, CORS, rate limiting, redirects, maintenance, static cache, and forms.
**-** `finella/queue` - queue manager (sync/database/redis drivers).
**-** `finella/scheduler` - schedule registry and `schedule:run`.
**-** `finella/mail` - Symfony Mailer adapter.
**-** `finella/debugbar` - debug tooling for development.
**-** `finella/tenancy` - multi-tenant context and model scoping.

**ENABLING OPTIONAL MODULES**
**-** Install the package with Composer.
**-** Ensure its provider is auto-discovered (or register manually).
**-** Add any middleware to your `config/http/http.php` pipeline.

Example:
**-** Add `finella/ops` to `composer.json` in `require`.
**-** Run `composer install`.
```php
use Finella\RateLimit\RateLimitMiddleware;

return [
    'global' => [
        RateLimitMiddleware::class,
    ],
];
```

**NOT INCLUDED BY DESIGN**
**-** Admin UI presets (use `finella/ui` admin stubs)
**-** Cron/daemon management (use system cron or supervisor)
**-** CMS/editor experience (use `finella/content` or a dedicated CMS)

**STABILITY & VERSIONING**
**-** Finella follows Semantic Versioning (SemVer).
**-** The 2.x line is the current stable core.
**-** Patch releases (2.x.y) contain fixes only, no breaking changes.
**-** Minor releases (2.x.0) add backward-compatible features.
**-** Major releases (3.0.0) may include breaking changes with upgrade notes.

**CREDITS**
**-** Author / Organisation: TechAyo.co.uk
**-** Maintainer: Marcin Kordyaczny

**LICENCE**
Proprietary (see LICENSE.md).
