# Engineering Decisions

## D-001 — V1 is a personal owner-scoped tracker

V1 serves one authenticated user's own data. Teams, shared workspaces and public profiles are deferred.

## D-002 — Use a Next.js + Laravel monorepo

Next.js/React/TypeScript own the browser experience. Laravel owns API, validation, authorization and domain behavior.

## D-003 — PostgreSQL is the only V1 source of truth

No secondary database or search service is introduced for V1 correctness.

## D-004 — Use first-party session authentication

Laravel Sanctum session cookies are preferred over browser-stored long-lived bearer tokens.

## D-005 — Manual capture before automation

V1 does not scrape or import third-party opportunities automatically. Manual capture keeps provenance and user intent explicit while avoiding brittle/legal/security surface.

## D-006 — Status is user-controlled and evented

The product exposes a fixed status vocabulary but does not impose one universal transition graph. The user chooses status, and every change is recorded.

## D-007 — Preserve deadline precision

Date-only deadlines remain distinguishable from exact date/time deadlines. The system must not manufacture false precision.

## D-008 — Archive is reversible; delete is permanent

Archive hides an item from the default workspace. Delete removes the opportunity and dependent product-history events.

## D-009 — Notes are plain text in V1

Rich text/HTML is unnecessary for core value and would enlarge XSS/sanitization surface.

## D-010 — URL fields are data only

V1 validates source URLs but never fetches them server-side.

## D-011 — Avoid premature infrastructure

Redis, queues, object storage, dedicated search and microservices are excluded until a concrete product requirement needs them.

## D-012 — MIT license

The public portfolio repository is released under the MIT License.

## D-013 — Use stable supported scaffold lines

The V1 scaffold uses Next.js 16.2.x, Node.js 24 LTS, Laravel 13, PHP 8.4 and PostgreSQL 18.x. Preview/canary framework releases are excluded from the baseline. Exact resolved application dependencies are committed in lockfiles.

## D-014 — Baseline CI mirrors both application boundaries

Permanent CI validates foundation documentation, performs locked frontend lint/typecheck/build checks, runs Laravel quality/tests against PostgreSQL, and smoke-tests the Docker application stack. Floating GitHub Action tags are not used.

## D-015 — Upstream npm security exceptions must be exact, bounded and expiring

The stable Next.js 16.2.11 dependency tree currently reports high-severity advisories through its resolved `postcss` 8.4.31 and `sharp` 0.34.5 dependencies. CI does not disable `npm audit` and does not accept arbitrary high-severity findings. The temporary exception is limited to those exact resolved package versions, the exact currently-known GHSA identifiers and the expected Next.js transitive chain. Any new high/critical package, advisory, version change or critical-severity finding fails CI. The exception expires on 2026-08-27 and must be removed earlier if a stable Next.js release resolves the upstream findings. Preview/canary Next.js releases are not adopted solely to silence the audit.

## D-016 — Sanctum authentication is session-only in V1

Sanctum is used for first-party SPA cookie/session authentication with CSRF protection. V1 does not issue personal access tokens and private routes authenticate through the session-backed `web` guard.

## D-017 — Establish ownership in persistence before exposing CRUD

The opportunity model and table established the UUID owner boundary before public CRUD routes were added.

## D-018 — Status changes use a dedicated workflow path

Create assigns `SAVED`. Ordinary PATCH does not accept `status`. Status and history move together through the dedicated workflow endpoint.

## D-019 — Ownership is assigned from the authenticated relationship

`owner_id` is not an editable CRUD field. New opportunities are created through the authenticated user's relationship.

## D-020 — Workflow history commits with the mutation

A product-history event and the opportunity mutation it represents are written in one PostgreSQL transaction. Mutating lookups remain owner scoped and take a row lock before state changes.

## D-021 — No-op requests do not manufacture history

History represents product changes rather than request volume. Repeating the same status, archive state, restore state or ordinary field values does not append a duplicate event. `UPDATED` stores sorted changed field names rather than copied user content.
