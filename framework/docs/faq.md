# FAQ

## Where should I put views?
By default, views live in `resources/views`. Configure the path via `config/app.php` (`views_path`).

## Why is DocumentRoot `public/`?
It prevents direct access to config, storage, and vendor files.

## How do I enable or disable a provider?
Use `config/providers/providers.php`:
- `disabled` rules can disable a provider globally or per environment.
- `manual` can add extra providers.

## How do I debug provider discovery?
- Delete `bootstrap/cache/providers.php`.
- Run `bin/finella-discover`.
- Check `storage/logs/finella-providers.log` if `APP_DEBUG=1`.

## How do I add middleware?
Add it to `config/http/http.php` (global) or pass it to routes.

## How do I add a new package?
Create a Composer package, expose a service provider, and add it to `extra.finella.providers`.
