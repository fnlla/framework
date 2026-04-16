# Finella Framework Documentation

This documentation targets the `finella/framework` package. It is written in UK English and reflects the actual behaviour of the 2.x line.

## How to read this documentation
Start with `getting-started.md`, then go through routing, middleware, and configuration. Use the monorepo docs (`documentation/src/getting-started.md`, `documentation/src/framework.md`, `documentation/src/operations.md`) for app-level guidance.

## Core vs Optional Modules
The framework core is intentionally minimal (kernel, router, container, configuration, error handling, and HTTP primitives).
Optional features such as auth, sessions, cookies, CSRF, rate limiting, security headers, and request logging live in separate packages.

## Highlights
- Warm kernel support for long-running servers (`HttpKernel::boot()`).
- Response tracing headers: `X-Request-Id`, `X-Trace-Id`, `X-Span-Id` (configurable).

## Enabling optional modules
1. Install the package via Composer.
2. Ensure its service provider is discovered (or register manually).
3. Add middleware to your HTTP pipeline if the module provides one.

## Contents
- `getting-started.md`
- `architecture.md`
- `requests-responses.md`
- `views.md`
- `error-handling.md`
- `discovery-and-cache.md`
- `providers.md`
- `extensions.md`
- `deployment-vps.md`
- `directory-structure.md`
- `upgrading.md`
- `faq.md`
- `glossary.md`

## Version compatibility
These docs target Finella v2.0 and later. If you are on a different major version, check the matching documentation.
