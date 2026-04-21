**FNLLA/OPS**

fnlla (finella) ops middleware bundle for security, routing hygiene, and baseline HTTP controls.

**INCLUDES**
**-** Security headers (`Finella\SecurityHeaders\*`)
**-** CORS (`Finella\Cors\*`)
**-** Rate limiting (`Finella\RateLimit\*`)
**-** Redirects (`Finella\Redirects\*`)
**-** Maintenance mode (`Finella\Maintenance\*`)
**-** Static cache (`Finella\CacheStatic\*`)
**-** Forms honeypot (`Finella\Forms\*`)

**INSTALL**
```
composer require fnlla/ops
```

The package registers all middleware service providers via auto-discovery.

**CONFIG**
Each module uses its existing config file:
**-** `config/security/security.php`
**-** `config/cors/cors.php`
**-** `config/rate_limit.php`
**-** `config/redirects/redirects.php`
**-** `config/maintenance/maintenance.php`
**-** `config/cache/cache_static.php`
**-** `config/forms/forms.php`
