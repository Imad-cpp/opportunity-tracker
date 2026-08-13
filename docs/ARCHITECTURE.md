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
- Event recording.
- Dashboard aggregation.

### PostgreSQL

PostgreSQL is the only source of truth for V1 application data. No search engine, secondary document database or cache is required for correctness.

## Authentication boundary

V1 uses Laravel Sanctum first-party session authentication with secure HTTP-only cookies and CSRF protection. The browser must not persist long-lived bearer tokens in local storage.

Local development may run web and API on different ports, but the deployment model should present them under one trusted site boundary or an explicitly configured first-party origin.

## Monorepo principles

- `apps/web` and `apps/api` may evolve independently but must agree on the public API contract.
- API response behavior is tested server-side.
- Critical user journeys are exercised end-to-end before a tagged V1 release.
- Synthetic fixtures only.

## No premature infrastructure

V1 does not require Redis, Meilisearch, object storage, microservices, Kubernetes or a message broker. Infrastructure is added only when a product requirement creates a demonstrated need.

## Planned local environment

Docker Compose will provide PostgreSQL and the Laravel API runtime. The Next.js web application may run in Compose or through the local Node toolchain, but CI must prove a reproducible build for both applications.
