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

## 6. Search, filters and deadlines ✅

Owner-scoped bounded search, deterministic 20-item pagination, allowlisted filters, date-only/exact deadline behavior, account-time-zone boundaries and due-soon/overdue/upcoming derivation.

## 7. Dashboard product surface ✅

Owner-scoped action-first summary API, active pipeline counts, due-soon and overdue attention, next actions, recent activity, typed Next.js consumption and responsive/loading/empty/error/accessibility states.

## 8. Contract and hardening ✅

OpenAPI 3.1 contract and route/security drift checks, PHPStan static analysis, strict PHP/PSR/platform checks, locked dependency audits, full-history Gitleaks scanning, running-stack CSRF/CORS evidence and PostgreSQL/Docker integration CI.

## 9. V1 release evidence ⏳

Completed release evidence includes authenticated account entry, opportunity list/detail/create/edit flows, plain-text note rendering, next-action visibility, status/archive/restore/delete workflows, Playwright register-through-delete browser E2E and PostgreSQL migration rollback/reapply verification.

Remaining release work is intentionally narrow: final Definition-of-Done/documentation audit, release notes, verification that the exact release commit is green, then the tagged `v1.0.0` release.

## Deferred after V1

- Reminders/notifications.
- Tags/custom labels.
- Calendar export/integration.
- Import helpers.
- Optional analytics over personal pipeline history.
- Any AI capability only after a clear user value and grounded data contract exist.
