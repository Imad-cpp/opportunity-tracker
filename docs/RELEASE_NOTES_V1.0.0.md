# Opportunity Tracker v1.0.0

Release date: 2026-08-14

## What this release is

Opportunity Tracker V1 is a private, owner-scoped application workspace for turning saved opportunities into a clear workflow: capture, prioritize, prepare, apply, follow up and close.

It is deliberately a product-engineering portfolio project rather than a scraper, job board, public profile system or AI recommendation product.

## Product capabilities

- First-party email/password registration, sign-in, sign-out and session-backed identity.
- Owner-scoped opportunity create, read, edit, archive, restore and permanent delete.
- Opportunity types for jobs, internships, scholarships, programs and other tracked items.
- User-controlled application status with append-oriented activity history.
- Priority, organization, source URL, location, plain-text notes, deadlines and next actions.
- Search plus allowlisted status, type, priority, archive and deadline filters.
- Deterministic 20-item pagination.
- Date-only and exact-time deadline precision with time-zone-aware normalization.
- Derived overdue, due-soon and upcoming attention states.
- Action-first dashboard with active pipeline counts, deadline attention, next actions and recent activity.
- Responsive browser product shell with accessible validation, keyboard focus, reduced-motion support and explicit loading/empty/error/disabled states.

## Security and privacy model

- PostgreSQL is the only V1 source of truth.
- Every private opportunity read and mutation is server-side owner scoped.
- Foreign opportunity identifiers return `404` rather than leaking resource existence.
- Laravel Sanctum provides first-party cookie/session authentication with CSRF protection; V1 does not issue browser-stored long-lived bearer tokens.
- Authentication endpoints are rate limited.
- Source URLs are validated as HTTP(S) data only and are never fetched server-side.
- User-authored notes are rendered as plain text rather than HTML.
- Tests, fixtures and browser evidence use synthetic data.
- Full Git history is scanned for secrets.

## Engineering stack

- Next.js 16.3.1, React 19 and TypeScript for the browser application.
- Laravel 13 and PHP 8.4 for the API and domain behavior.
- PostgreSQL 18.x for persistence.
- Docker Compose for the reproducible local/integration stack.
- Node.js 24 LTS as the supported V1 JavaScript runtime line.

## Release evidence

The V1 candidate is required to pass the repository's permanent CI gates on the exact release commit:

- Application Quality: documentation checks, locked web install, lint, typecheck, production build, unconditional high-severity npm audit, Laravel formatting/tests against PostgreSQL, Composer audit and Docker stack smoke.
- Browser E2E: register → sign out/sign in → create → edit → status → search → dashboard → archive → archived filter → restore → permanent delete.
- Browser Stack Security: Sanctum CSRF bootstrap, trusted credentialed CORS, rejection of missing XSRF headers and the stable 419 error envelope.
- Contract Quality: OpenAPI/route/security/enum drift checks.
- PHP Static Analysis.
- Secret Hygiene.
- Migration Rollback and reapply verification.

## Dependency security

The web runtime was upgraded from Next.js 16.2.11 to 16.3.1 with the matching ESLint configuration. The stable dependency graph resolves patched PostCSS and Sharp releases, so the previous temporary npm audit exception has been removed. Production dependency audit is again an unconditional hard-fail gate for high/critical findings.

## Deliberate V1 non-goals

V1 does not include scraping, automatic importing, AI matching or writing, CV/document storage, inbox synchronization, public profiles, shared workspaces, native mobile apps, automated status changes or production notification infrastructure.

## Release integrity

Create the `v1.0.0` tag and GitHub Release only after the exact target commit is green across all required permanent checks. The tag and release must point to that same verified commit.
