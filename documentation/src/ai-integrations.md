**AI & INTEGRATIONS**

**AI**

Finella includes an optional AI package (`finella/ai`) with a built-in OpenAI
Responses API client. This keeps AI integration consistent across apps while
remaining provider-friendly for the future. Starter apps default to
`AI_DRIVER=mock` for safe, offline demos.

**POSITIONING: AI-ASSISTED AND OPTIONAL**
Finella is AI-assisted by design, but the core framework runs fully without AI.
The AI layer is opt-in and safe by default, intended to improve workflow,
quality, and documentation without blocking delivery.

**AI BOUNDARIES**
What AI does:
**-** drafts, summarizes, and proposes changes with explicit human review
**-** runs deterministic insights without external providers when configured
**-** applies governance (policy, redaction, routing, telemetry) when enabled

What AI does not do:
**-** apply changes automatically without preview/confirmation
**-** require network calls or provider keys to operate the core framework
**-** replace engineering ownership or SDLC decision-making

**QUICKSTART (5 MINUTES)**
**-** Install AI: `composer require finella/ai`
**-** In `.env` set `AI_DRIVER=openai` and `OPENAI_API_KEY=your-key` for live models (mock is default).
**-** Review the governance settings in `config/ai/policy.php` and `config/ai/redaction.php`.

**QUICKSTART: AI CLI (NO API KEY)**
Use these deterministic commands to make the core framework feel smarter without any provider.
**-** Scaffold a feature: `php bin/finella ai:scaffold Invoice --resource --all`
**-** Run a readiness check: `php bin/finella ai:doctor`
**-** Check smart defaults: `php bin/finella ai:config-advisor`
**-** Lint security risks: `php bin/finella ai:security-lint`
**-** Summarise logs: `php bin/finella ai:observability --lines=2000`
**-** Detect doc drift: `php bin/finella ai:docs-sync`
**-** Generate a test plan: `php bin/finella ai:test-plan Checkout`
**-** Balance a roadmap: `php bin/finella ai:roadmap-balance`
**-** Draft release notes: `php bin/finella ai:release-notes --version=Unreleased`

**GOVERNANCE AND POLICY**
AI governance lives under `config/ai/`:
**-** `policy.php` for temperature/output/input limits
**-** `redaction.php` for prompt/input redaction and masking
**-** `router.php` for provider and model routing rules
**-** `telemetry.php` for logging and storage
**-** `rag.php` for retrieval settings

Recommended defaults:
**-** Keep low temperatures in production.
**-** Enforce input/output caps to control cost and variance.
**-** Require redaction where secrets or PII are possible.

**TELEMETRY (OPTIONAL)**
Telemetry can capture inputs, outputs, sources, and provider metadata for audits
and evals. Enable it with:
```
AI_TELEMETRY_ENABLED=1
AI_TELEMETRY_STORE_INPUT=1
AI_TELEMETRY_STORE_OUTPUT=1
AI_TELEMETRY_STORE_SOURCES=1
```
Tune retention and storage size with `AI_TELEMETRY_MAX_CHARS`.

**RAG (OPTIONAL)**
RAG is available for grounding answers in your own docs and data. Configure
`config/ai/rag.php` and integrate your ingestion pipeline with the RAG store.
`ai:docs-sync` can help keep docs aligned.

**INTEGRATIONS**
Finella supports first-class integrations through packages:
**-** Search (`finella/search`)
**-** OAuth/OIDC (`finella/oauth`)
**-** Monitoring (`finella/monitoring`)
**-** Webmail (`finella/webmail`)
**-** PDF (`finella/pdf`)
**-** SEO (`finella/seo`)
**-** Storage (`finella/storage-s3`)

For the current integration list and configuration guidance, see
`documentation/src/operations.md`.
