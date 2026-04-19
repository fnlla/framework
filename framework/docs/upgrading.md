# Upgrading

Finella follows Semantic Versioning (SemVer).

## SemVer rules
- Patch releases (3.x.y) contain bug fixes only.
- Minor releases (3.x.0) add backward-compatible features.
- Major releases (4.0.0+) may introduce breaking changes.

## Recommended workflow
1. Read the changelog.
2. Update `composer.json` constraints if needed.
3. Run `composer update`.
4. Run smoke tests.

## Extensions
Upgrade packages independently but keep compatibility with `finella/framework ^3.0`.
