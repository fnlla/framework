**GETTING STARTED**

This guide is the canonical setup path for the `fnlla/framework` repository.

**REQUIREMENTS**
**-** PHP >= 8.5
**-** Composer >= 2
**-** Git

**REPOSITORY SCOPE**
`fnlla/framework` contains:
**-** `framework/` core runtime
**-** `packages/` optional official packages
**-** `tools/harness/` development and smoke-test harness
**-** `documentation/src/` source-of-truth Markdown docs

The public starter application is in a separate repository: `fnlla/fnlla`.

**QUICK START (HARNESS)**
```bash
git clone https://github.com/fnlla/framework.git framework
cd framework/tools/harness
copy .env.example .env
composer install
php bin/finella db:bootstrap
composer run dev
```

Open `http://127.0.0.1:8000`.

**QUALITY GATE (MINIMAL)**
From `tools/harness/`:
```bash
composer run smoke
```

From repo root:
```bash
bash scripts/release/check-release-hygiene.sh
```
(Windows alternative: `scripts/release/check-release-hygiene.ps1`.)

**STATIC DOCS BUILD**
Rebuild static HTML docs from `documentation/src/*`:
```bash
php scripts/docs/build-static-docs.php
```
Output is written to `documentation/build/`.

**WORKING WITH STARTER (`fnlla/fnlla`)**
If you work across both repositories, use side-by-side layout:
```text
<repos>/framework   # github.com/fnlla/framework
<repos>/fnlla       # github.com/fnlla/fnlla
```

Then in `fnlla/` use `composer.dev.json` for local package resolution.

**NAME ORIGIN AND TECHNICAL SLUG**
**-** Product name: `fnlla (finella)` (name origin: fnlla (finella) Gardens, Dundee, UK).
**-** Technical slug: `fnlla` (`github.com/fnlla`, `fnlla.co.uk`).
**-** Why `fnlla`: short ASCII-only identifier for repositories, package/tooling paths, and domain naming.

**NEXT READING**
**-** `documentation/src/framework.md`
**-** `documentation/src/operations.md`
**-** `documentation/src/developer-experience.md`
**-** `documentation/src/packages.md`
