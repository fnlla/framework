# Routing

## Defining routes
```php
use Finella\Http\Router;
use Finella\Http\Response;

return static function (Router $router): void {
    $router->get('/', fn () => Response::text('Home'));
    $router->post('/contact', fn () => Response::text('OK'));
    $router->add('PUT', '/profile', fn () => Response::text('Updated'));
};
```

## Route parameters
```php
$router->get('/users/{id}', function ($request): Response {
    return Response::text('User ' . $request->getAttribute('id'));
});
```
You can constrain parameters with regex: `{id:\\d+}`.

## Named routes
```php
$router->get('/users/{id}', [UserController::class, 'show'], 'users.show');
$url = $router->url('users.show', ['id' => 10]);
```

## Controllers
Handlers can be closures, arrays, or `Controller@method` strings:
```php
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/account', 'AccountController@index');
```

## Middleware groups
```php
$router->middlewareGroup('web', [
    \Finella\Csrf\CsrfMiddleware::class,
    \Finella\SecurityHeaders\SecurityHeadersMiddleware::class,
]);
```
Requires the core CSRF module and `finella/ops`.

## 404 / 405 behaviour
- No matching path -> 404
- Path matches but method does not -> 405

## DI in handlers
If the container is available, handlers are called via `container->call()`:
```php
use Finella\Http\Request;
use Finella\Http\Response;

$router->get('/agent', function (Request $request): Response {
    return Response::text($request->getHeaderLine('User-Agent'));
});
```

## Route caching
Routes can be cached to `storage/cache/routes.php`. Only controller strings/arrays are cacheable; closures are not.
Routes cache is intended for production and is skipped when `APP_DEBUG=true` or `APP_ENV=local`.
Compile routes with `Finella\Http\RouteCacheCompiler` or `routes:cache`.
