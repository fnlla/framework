# Architecture

## Request lifecycle
1. `public/index.php` loads Composer autoload and the bootstrap.
2. `bootstrap/app.php` builds the container and returns the HTTP kernel.
3. `HttpKernel` builds request context, loads config, and sets up middleware.
4. `Router` matches the route and invokes the handler.
5. A `Response` is returned and sent.

## ASCII diagram
```
Request
  |
  v
public/index.php
  |
  v
bootstrap/app.php
  |
  v
HttpKernel
  |
  v
Router -> Middleware -> Handler
  |
  v
Response
```

## Warm kernel (long-running)
You can boot once and reuse the kernel for long-running servers:
```php
$kernel = new \Finella\Http\HttpKernel();
$kernel->boot();
```
This avoids reloading providers and plugins on each request. Register resetters for per-request cleanup:
```php
$app->registerResetter(new \App\Support\MyResetter());
```

## Container and DI
The container is used by the router to resolve controllers and inject dependencies into handlers via `container->call()`.

## Configuration
Configuration is loaded from `config/**/*.php` through `ConfigRepository::fromRoot()`.

## Routes cache
Routes cache is intended for production and is skipped when `APP_DEBUG=true` or `APP_ENV=local`.
Cached routes require string handlers and middleware, so closures are not cacheable.

## Providers and extensions
Service providers register services and boot integration features. Discovery and caching are provided by the support layer and can be enabled in the app bootstrap.
