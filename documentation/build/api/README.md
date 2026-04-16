**API SNAPSHOT**

This directory stores the generated public API snapshot (`public-api.json`).

**GENERATE / UPDATE**
From the repo root:
```bash
php scripts/ci/public-api-snapshot.php
```

**NOTES**
**-** The snapshot is used by CI for the API stability gate.
**-** PRs show diffs (informational); tags/releases fail on breaking changes.
**-** Public APIs are annotated in code with `@api` (see `documentation/src/operations.md` - Versioning section).
