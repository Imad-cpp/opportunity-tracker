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
