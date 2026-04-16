# Getting Started

This guide shows the minimal steps required to run a Finella application using the framework package.

## 1) Install
Offline install:
1) Add `finella/framework` to `composer.json` in `require`.
2) Configure local path repositories (see `documentation/src/getting-started.md`).
3) Run `composer install`.

## 2) Create the bootstrap
`bootstrap/app.php`
```php
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

## 3) Create the entry point
`public/index.php`
```php
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

## 4) Create the first route
`routes/web.php`
```php
use Finella\Http\Router;
use Finella\Http\Response;

return static function (Router $router): void {
    $router->get('/', fn () => Response::text('Hello Finella'));
};
```

## 5) Run locally
```bash
php -S localhost:8000 -t public
```

## Next steps
- See `routing.md` and `middleware.md` for advanced routing.
- Configure views with `config/app.php`.
- Use packages for database, cache, queue, and mail.
