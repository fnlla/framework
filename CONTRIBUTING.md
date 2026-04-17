**CONTRIBUTING WORKFLOW CONTRACT (FRAMEWORK REPOSITORY)**

This document defines how we work across two repositories:
**-** `fnlla/framework` (this repository)
**-** `fnlla/fnlla` (starter application repository)

**REPOSITORY RESPONSIBILITIES**

**-** `fnlla/framework`:
  **-** framework runtime code
  **-** packages in `packages/*`
  **-** shared tooling and CI gates for framework internals
  **-** framework release versions and tags
**-** `fnlla/fnlla`:
  **-** starter application and project-level configuration
  **-** app-level integration of framework releases
  **-** starter lockfiles and starter release versions

**TASK ROUTING RULES**

**-** Request prefixed with `framework:` means changes only in `fnlla/framework`.
**-** Request prefixed with `fnlla:` means changes only in `fnlla/fnlla`.
**-** Request prefixed with `cross-repo:` means coordinated changes in both repositories.
**-** If no prefix is provided, the implementation workflow selects the smallest safe scope and explains assumptions in the delivery summary.

Default behavior is not to modify both repositories unless needed.

**CROSS-REPO DELIVERY ORDER**

For changes that touch both repositories, use this order:

**-** Implement and verify framework changes in `fnlla/framework`.
**-** Push framework commit/PR and complete framework CI.
**-** Create framework release/tag (when release is required).
**-** Update dependency and integration in `fnlla/fnlla` (including lockfile changes).
**-** Push starter commit/PR and complete starter CI.
**-** Create starter release/tag (if needed).

**COMMIT AND PUSH POLICY**

**-** Keep commits repository-local: one repository, one intent, one commit series.
**-** Do not bundle framework and starter edits into a single repository history.
**-** Mention linked commit/PR/tag between repositories when work is cross-repo.

**DEFINITION OF DONE**

A task is done only when all are true for touched repositories:

**-** Required CI checks are green.
**-** No unintended local diff remains.
**-** Documentation and release notes are updated when behavior changes.
**-** Dependency/version relationships are consistent between repositories.

This split is intentional and is the default collaboration model for this project.
