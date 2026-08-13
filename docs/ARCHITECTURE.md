# Architecture

## Shape

Opportunity Tracker is a monorepo with two application boundaries:

```text
apps/web  →  Next.js + React + TypeScript
    │
    │ JSON over HTTPS
    ↓
apps/api  →  Laravel API
    │
    ↓
PostgreSQL
```

## Responsibilities

### Web

- Authentication screens and authenticated application shell.
- Dashboard, opportunity list, filters, forms and history presentation.
- Client-side interaction state only; server data remains authoritative.
- Accessible keyboard/focus behavior and responsive layouts.

### API

- Authentication/session authority.
- Validation and normalization.
- Owner-scoped authorization.
- Opportunity lifecycle and archive/delete behavior.
- Deadline normalization and derived due/overdue semantics.
- Transactional product-history event recording.
- Dashboard aggregation.

### PostgreSQL

PostgreSQL is the only source of truth for V1 application data. No search engine, secondary document database or cache is required for correctness.

The current schema contains UUID-backed users, server-side sessions, owner-linked opportunities and append-oriented opportunity events. State mutations that require product history commit the opportunity change and event in one transaction.

## Authentication boundary

V1 uses Laravel Sanctum for first-party cookie/session authentication with CSRF protection. Registration/login rotate the session identifier; logout invalidates the session and rotates the CSRF token. `/sanctum/csrf-cookie` is the SPA bootstrap endpoint.

Sanctum personal access tokens are not part of V1. Private API routes authenticate through the session-backed `web` guard, and the browser must not persist long-lived bearer tokens in local storage.

Local development runs web and API on different ports with an explicit first-party CORS/stateful-domain allowlist. A deployed environment should present them under one trusted site boundary or an equivalently explicit first-party origin and use secure cookies over HTTPS.

## Ownership boundary

`opportunities.owner_id` is a UUID foreign key to the account. The domain model exposes an `ownedBy(User)` query scope. Read and mutation handlers start from that owner-scoped query so a foreign UUID is indistinguishable from a missing one.

Workflow mutations additionally use an owner-scoped row lock inside the PostgreSQL transaction. Event history is reached through the already-owned opportunity, so event-list access inherits the same object boundary.

## Workflow/history boundary

Status cannot be changed through ordinary PATCH. The dedicated status endpoint writes the new status and `STATUS_CHANGED` event atomically. Create/update/archive/restore use the same transaction pattern for their corresponding events.

History is intentionally append-oriented but not noisy: requests that produce no product change do not append synthetic events. `UPDATED` records changed field names rather than copied user content.

## Monorepo principles

- `apps/web` and `apps/api` may evolve independently but must agree on the public API contract.
- API response behavior is tested server-side.
- Critical user journeys are exercised end-to-end before a tagged V1 release.
- Synthetic fixtures only.

## No premature infrastructure

V1 does not require Redis, Meilisearch, object storage, microservices, Kubernetes or a message broker. Infrastructure is added only when a product requirement creates a demonstrated need.

## Local environment

Docker Compose provides PostgreSQL 18.4, the Laravel 13 API runtime and the Next.js 16.2 web runtime. Node.js 24 LTS and PHP 8.4 are the scaffold toolchain baselines. Both applications keep committed dependency lockfiles, and CI proves reproducible installs/builds plus a real PostgreSQL migration/test boundary.
