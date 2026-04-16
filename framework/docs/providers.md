# Providers

Service providers register services and bootstrap integrations.

## Lifecycle
- `register()` binds services into the container.
- `boot()` runs after registration to wire middleware, routes, or listeners.

## Base class
Extend `Finella\Support\ServiceProvider` for default behaviour.

## Manifest
Providers can expose a manifest for discovery:
```php
public static function manifest(): \Finella\Support\ProviderManifest
```
This can declare capabilities (routes, views, config, middleware) and resources.

## Provider report
If enabled, the application can write a provider report to `storage/logs/finella-providers.log` in debug mode.
