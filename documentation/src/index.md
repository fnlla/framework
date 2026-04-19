**FINELLA DOCUMENTATION**

This documentation is written in UK English. It covers the framework, the starter app, and the official packages.
Finella is an AI-assisted (optional), modular framework focused on the framework runtime, starter app, and optional packages.
AI is a first-class pillar: governance, routing, telemetry, and autonomous insights are built in, but remain optional and safe by default.
Why "AI-assisted"? Because we are building the future of AI for product teams, but we keep the promise grounded:
autonomous insights run without providers, AI integrations are optional, and guardrails keep outputs safe and predictable.
We continue to push boundaries toward an AI-focused future while keeping AI optional today (see `documentation/src/ai-integrations.md` for the current scope and boundaries).

Note on dependencies: Finella uses a small set of infrastructure libraries (for example, `symfony/mailer`).
Dotenv handling is implemented by Finella itself (see `Finella\Support\Dotenv`) as internal building blocks.

**NAME ORIGIN AND TECHNICAL SLUG**
**-** Product name: `Finella` (name origin: Finella Gardens, Dundee, UK).
**-** Technical slug: `fnlla` (`github.com/fnlla`, `fnlla.co.uk`).
**-** Why `fnlla`: short ASCII-only identifier for repositories, package/tooling paths, and domain naming.

Note: `tools/harness/` inside the monorepo is a **dev/test harness** for framework development. The public starter app lives in `fnlla/fnlla` and is installed via `composer create-project finella/starter`.

**HOW TO READ THIS DOCUMENTATION**
Start with `getting-started.md` and `framework.md`. Use the developer experience guide if you enable optional modules.

**STRUCTURE UPDATE (APRIL 2026)**
Documentation was consolidated into fewer pages. If you are looking for older paths:
**-** Legacy AI index is now `documentation/src/ai-integrations.md`.
**-** Legacy deprecations index is now the Deprecations Registry in `documentation/src/operations.md`.
**-** Legacy HTTP/core docs are consolidated in `documentation/src/framework.md`.

**CHANGELOG (DOCS)**
**-** April 2026: consolidated docs into seven core pages and updated all routes to the new structure.
**-** April 2026: moved deprecations registry into `documentation/src/operations.md` with inline migration sections.

**GETTING STARTED**
For framework development, use `tools/harness/` and follow the root `README.md`.
See `getting-started.md` for the fastest path and full setup notes.

**RELEASE SUMMARY (3.X LINE)**
**-** Framework and official packages ship on the 3.x line.
**-** PHP baseline is 8.5+ across framework and starter workflows.
**-** Docs and release governance are maintained from `documentation/src/*`.
See `CHANGELOG.md` for release-specific details.

**STABILITY AND ROADMAP**
Finella publishes a clear support policy and a public roadmap.
See `documentation/src/operations.md` for support windows, roadmap, and release governance.
Deprecations and migrations are tracked in `documentation/src/operations.md`.
Third-party notices workflow is documented in `documentation/src/operations.md` under **Tooling -> Third-party notices**.
Public distribution split is enforced via `scripts/release/check-public-distribution.php`.

**DEVELOPER EXPERIENCE**
Task-oriented entry points live in `documentation/src/developer-experience.md`, alongside the feature index and CLI notes.
Full package catalog lives in `documentation/src/packages.md`.

**INDUSTRY BLUEPRINTS**
Finella ships CLI blueprints for common product domains to speed up scaffolding:
crm, school, crm-school, saas, commerce, marketplace, erp, healthcare, real-estate, logistics.
Use `php bin/finella make:blueprint <name> --module --plan` to preview before generating files.

**PUBLIC SECTOR COMPLIANCE**
We ship a public-sector compliance checklist template in `documentation/src/operations.md`.

**INTEGRATIONS**
Current and planned integrations are documented in `documentation/src/ai-integrations.md`.

**AI PRIORITIES**
The current priority areas for AI are tracked in `documentation/src/operations.md`, including:
**-** AI evaluation harness and safety rails.
**-** AI routing and RAG diagnostics.

**OBSERVABILITY AND PERFORMANCE**
Finella adds `X-Request-Id`, `X-Trace-Id`, and `X-Span-Id` to responses by default (see `framework.md`).
For long-running servers, you can boot once and reuse the kernel (see `framework.md` and `operations.md`).

**EXAMPLE (DOCS GENERATION)**
Generate and publish technical + user docs in one step, then review at the /docs endpoint:
```bash
php bin/finella docs:generate --publish
```
Open `GET /docs` for published docs.

**CONTENTS**
**-** [Getting Started](getting-started.md) - setup and structure conventions.
**-** [Framework](framework.md) - framework guide, ORM ergonomics, and caching.
**-** [AI & Integrations](ai-integrations.md) - AI provider setup and integrations.
**-** [Operations & Governance](operations.md) - operations, releases, support, enterprise readiness, roadmap, and migrations.
**-** [Developer Experience](developer-experience.md) - CLI, task guides, and feature index.
**-** [Packages](packages.md) - full package catalog and ownership notes.

**PACKAGES**
Package metadata lives in each package `composer.json`. Use `packages.md` for the full catalog and `operations.md` for registry setup and release rules.

**LICENSING**
Finella is proprietary. See the root `LICENSE.md` and per-package LICENSE files.
