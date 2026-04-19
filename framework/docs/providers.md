**PROVIDERS**

Service providers register services and bootstrap integrations.

**LIFECYCLE**
**-** `register()` binds services into the container.
**-** `boot()` runs after registration to wire middleware, routes, or listeners.

**BASE CLASS**
Extend `Finella\Support\ServiceProvider` for default behaviour.

**MANIFEST**
Providers can expose a manifest for discovery:
```php
public static function manifest(): \Finella\Support\ProviderManifest
```
This can declare capabilities (routes, views, config, middleware) and resources.

**PROVIDER REPORT**
If enabled, the application can write a provider report to `storage/logs/finella-providers.log` in debug mode.
