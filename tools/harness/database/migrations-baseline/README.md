**MIGRATION BASELINE (NEUTRAL PREFIXES)**

This directory is an optional starter baseline for brand-new apps.

Usage:
**-** Run migrations from this path for first bootstrap: `php bin/fnlla migrate --path=database/migrations-baseline`
**-** Or set `MIGRATIONS_PATH=database/migrations-baseline` in `.env`
**-** Keep regular project migrations in `database/migrations`

Important:
**-** Do not rename or replace already-shipped migration files for existing installs.
**-** Use this baseline only for new projects that start from a clean database.
