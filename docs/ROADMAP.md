# Roadmap

## 1. Foundation ✅

Product contract, architecture, data model, API map, security model, engineering decisions, Definition of Done and docs CI.

## 2. Application scaffold ✅

Monorepo structure, Next.js web, Laravel API, PostgreSQL, Docker/local environment and baseline CI.

## 3. Identity and ownership ✅

Sanctum first-party session authentication, UUID accounts, CSRF bootstrap/session behavior, account time zone validation, authentication rate limits and owner-scoped persistence tests.

## 4. Opportunity CRUD ✅

Authenticated create/read/update, strict editable-field validation, HTTP(S) source URL rules, owner-scoped lookup, archive/restore and permanent delete.

## 5. Workflow and history ✅

Transactional status changes, editable next actions and append-oriented `CREATED`, `UPDATED`, `STATUS_CHANGED`, `ARCHIVED` and `RESTORED` opportunity history with owner-scoped reads and no-op suppression.

## 6. Search, filters and deadlines

Search, pagination, allowlisted filters, date-only/exact deadline behavior, due-soon and overdue derivation.

## 7. Dashboard product surface

Action-first dashboard, recent activity and responsive/accessibility states.

## 8. Contract and hardening

OpenAPI, static analysis, dependency locking/audits, secret hygiene, CORS/security evidence and PostgreSQL integration CI.

## 9. V1 release evidence

Reproducible end-to-end demo, final Definition-of-Done audit, changelog/release notes and tagged `v1.0.0`.

## Deferred after V1

- Reminders/notifications.
- Tags/custom labels.
- Calendar export/integration.
- Import helpers.
- Optional analytics over personal pipeline history.
- Any AI capability only after a clear user value and grounded data contract exist.
