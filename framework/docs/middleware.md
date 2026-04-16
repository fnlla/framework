# Middleware

## Overview
Middleware wrap route handlers to perform cross-cutting tasks such as security headers, logging, or CSRF checks.

## Global middleware
Defined in `config/http/http.php`:
```php
return [
    'global' => [
        \Finella\SecurityHeaders\SecurityHeadersMiddleware::class,
    ],
];
```
Requires `finella/ops`.

## Per-route middleware
```php
$router->get('/account', [AccountController::class, 'index'], 'account', [
    \Finella\Auth\Middleware\AuthMiddleware::class,
]);
```
Requires the core Auth module.

## Middleware groups
```php
$router->middlewareGroup('web', [
    \Finella\Csrf\CsrfMiddleware::class,
    \Finella\SecurityHeaders\SecurityHeadersMiddleware::class,
]);
```
Requires the core CSRF module and `finella/ops`.

## Middleware aliases
Define aliases in `config/http/http.php`:
```php
return [
    'middleware_aliases' => [
        'auth' => \Finella\Auth\Middleware\AuthMiddleware::class,
        'csrf' => \Finella\Csrf\CsrfMiddleware::class,
    ],
];
```
Then use aliases in routes:
```php
$router->get('/account', [AccountController::class, 'index'], 'account', ['auth']);
```

## Execution order
1. Global middleware
2. Group middleware
3. Route-specific middleware
4. Handler

## Writing middleware
A middleware can be:
- a closure: `function (Request $request, callable $next): Response`
- a class implementing `Finella\Support\Psr\Http\Server\MiddlewareInterface`

## Core middleware
Core middleware ships in the framework:
- Auth (`AuthMiddleware`)
- CSRF (`CsrfMiddleware`)
- Request logging (`RequestLoggerMiddleware`)

## Optional middleware
Optional middleware is delivered as packages and only enabled when you install them:
- `finella/ops` (SecurityHeadersMiddleware, RateLimitMiddleware, CorsMiddleware, RedirectsMiddleware, MaintenanceMiddleware, StaticCacheMiddleware, HoneypotMiddleware)
