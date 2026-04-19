**DISCOVERY AND CACHE**

Finella can auto-discover service providers from Composer packages and cache the results.

**DISCOVERY**
Discovery reads `vendor/composer/installed.json` and collects providers from:
```
extra.finella.providers
```

**CACHE**
The cache file lives at:
**-** `bootstrap/cache/providers.php`

The cache contains:
**-** `providers` (list of FQCN)
**-** `meta` (package name, version, source)

**COMPOSER SCRIPTS**
The starter app runs discovery in `post-install-cmd` and `post-update-cmd` using `bin/finella-discover`.

**CLEARING CACHE**
Delete `bootstrap/cache/providers.php` and re-run `bin/finella-discover`.

**ROUTES CACHE**
Routes cache can be compiled with `Finella\Http\RouteCacheCompiler` or `routes:cache`.
Set `routes_cache_strict=false` if you want cache generation to emit a disabled cache file
when closures are present (the runtime will ignore it and load routes normally).

**TROUBLESHOOTING**
**-** Ensure the package exposes `extra.finella.providers`.
**-** Ensure the class exists and is autoloadable.
**-** Check disabled rules in `config/providers/providers.php`.
