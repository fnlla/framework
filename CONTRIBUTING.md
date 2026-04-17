# Contributing Workflow Contract (Framework Repository)

This document defines how we work across two repositories:
- `fnlla/framework` (this repository)
- `fnlla/fnlla` (starter application repository)

The goal is simple: product and domain focus stays with the maintainer, while technical implementation and delivery mechanics stay with Codex.

## Repository Responsibilities

- `fnlla/framework`:
  - framework runtime code
  - packages in `packages/*`
  - shared tooling and CI gates for framework internals
  - framework release versions and tags
- `fnlla/fnlla`:
  - starter application and project-level configuration
  - app-level integration of framework releases
  - starter lockfiles and starter release versions

## Task Routing Rules

- Request prefixed with `framework:` means changes only in `fnlla/framework`.
- Request prefixed with `fnlla:` means changes only in `fnlla/fnlla`.
- Request prefixed with `cross-repo:` means coordinated changes in both repositories.
- If no prefix is provided, Codex selects the smallest safe scope and explains assumptions in the delivery summary.

Default behavior is not to modify both repositories unless needed.

## Cross-Repo Delivery Order

For changes that touch both repositories, use this order:

1. Implement and verify framework changes in `fnlla/framework`.
2. Push framework commit/PR and complete framework CI.
3. Create framework release/tag (when release is required).
4. Update dependency and integration in `fnlla/fnlla` (including lockfile changes).
5. Push starter commit/PR and complete starter CI.
6. Create starter release/tag (if needed).

## Commit and Push Policy

- Keep commits repository-local: one repository, one intent, one commit series.
- Do not bundle framework and starter edits into a single repository history.
- Mention linked commit/PR/tag between repositories when work is cross-repo.

## Definition of Done

A task is done only when all are true for touched repositories:

1. Required CI checks are green.
2. No unintended local diff remains.
3. Documentation and release notes are updated when behavior changes.
4. Dependency/version relationships are consistent between repositories.

## Responsibility Split

- Maintainer focus: product direction, prompts, and domain decisions.
- Codex focus: implementation, refactors, CI fixes, dependency wiring, release mechanics, and technical guardrails.

This split is intentional and is the default collaboration model for this project.
