**FINELLA STARTER (POINTER)**

Canonical starter documentation was moved to the root [README.md](../README.md).

Use this app directory as runtime root (`cd app`) and follow:
**-** Quick start
**-** Starter app guide
**-** CLI and testing
**-** Docs and admin preset publishing

Primary references:
**-** Root README: `../README.md`
**-** Operations: `../documentation/src/operations.md`
**-** Developer experience: `../documentation/src/developer-experience.md`
**-** Packages: `../documentation/src/packages.md`

Release notes tooling (from `app/`):
**-** Validate release notes format: `composer run release:notes:check`
**-** Publish one release from changelog: `composer run release:notes:publish -- --version 2.5.6`
**-** Sync existing GitHub releases from changelog: `composer run release:notes:sync`

Dependency profiles (from `app/`):
**-** Monorepo/dev profile install: `composer run deps:dev:install`
**-** Monorepo lock refresh: `COMPOSER=composer.dev.json composer update`
**-** Production profile check: `composer run ops:check:prod`
**-** Runbook drill (backup/restore + rollback dry-run): `composer run runbook:drill`

Markdown formatting tooling (from `app/`):
**-** Format project markdown: `composer run format:markdown`
**-** Check project markdown: `composer run lint:markdown`
**-** Format GitHub-facing docs (release style): `composer run format:github`
**-** Check GitHub-facing docs (release style): `composer run lint:github`
