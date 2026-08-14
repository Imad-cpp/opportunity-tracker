# Opportunity Tracker

A full-stack personal application tracker for saving opportunities, managing deadlines, recording application progress and keeping the next useful action visible.

> V1 is deliberately manual and owner-scoped. It is a product-engineering portfolio project, not an opportunity scraper, job board or AI recommendation engine.

## Product goal

Turn scattered opportunity links and application notes into one reliable workflow:

`Capture → Prioritize → Prepare → Apply → Follow up → Close`

The system should answer four questions quickly:

1. What opportunities am I actively tracking?
2. What deadline or next action is closest?
3. Where is each application in the process?
4. What changed recently?

## V1 capabilities

- Email/password account and secure first-party session.
- Create, read, edit, archive, restore and permanently delete owned opportunities.
- Opportunity types: `JOB`, `INTERNSHIP`, `SCHOLARSHIP`, `PROGRAM`, `OTHER`.
- Statuses: `SAVED`, `PREPARING`, `APPLIED`, `INTERVIEWING`, `OFFERED`, `ACCEPTED`, `REJECTED`, `WITHDRAWN`, `EXPIRED`.
- Priority, organization, source URL, location, plain-text notes, deadline and next action.
- Search and filters by status, type, priority, archive state and deadline window.
- Dashboard summaries for due soon, overdue, active pipeline counts, next actions and recent activity.
- Append-oriented user-facing activity history for meaningful lifecycle changes.
- Explicit deadline precision/time-zone handling instead of silently inventing times.
- Owner isolation on every private read and mutation.
- Responsive authenticated browser workflows from registration through permanent delete.

## Architecture

```text
Browser
  ↓
Next.js + React + TypeScript
  ↓ HTTPS / JSON
Laravel API
  ↓
PostgreSQL
```

The repository is a monorepo. The web client and API stay separate at the application boundary while sharing one product contract and one source of truth.

## V1 non-goals

- Scraping or automatic importing from third-party sites.
- AI matching, ranking or application writing.
- Resume/CV or sensitive document storage.
- Browser extension or native mobile app.
- Inbox/email synchronization.
- Public profiles, public sharing or multi-user workspaces.
- Automated status changes.
- Production notification infrastructure.

## Engineering principles

- Product behavior before UI decoration.
- PostgreSQL is the source of truth.
- Every private object is owner-scoped server-side.
- Foreign resource identifiers return `404` rather than leaking existence.
- Plain-text notes only in V1; no user-authored HTML.
- URLs accept only `http` and `https` schemes.
- Deadline semantics preserve whether the user entered a date only or an exact time.
- Important lifecycle changes are recorded as events.
- Synthetic data only in tests, screenshots and demos.
- CI evidence must match the claims made in this repository.

## Verification

The V1 HTTP boundary is frozen in OpenAPI 3.1 and checked against Laravel route/security/enumeration sources. CI runs PostgreSQL-backed application tests, unconditional production dependency audits, PHPStan, strict PHP/PSR/platform checks, full-history Gitleaks scanning, Docker stack smoke, running-stack CSRF/CORS assertions, PostgreSQL migration rollback/reapply evidence and a Playwright register-through-delete browser workflow.

## Documentation

- [Product contract](docs/PRODUCT.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Data model](docs/DATA_MODEL.md)
- [API map](docs/API_MAP.md)
- [OpenAPI contract](docs/openapi.json)
- [Security model](docs/SECURITY_MODEL.md)
- [Engineering decisions](docs/DECISIONS.md)
- [Definition of Done](docs/DEFINITION_OF_DONE.md)
- [Roadmap](docs/ROADMAP.md)
- [v1.0.0 release notes](docs/RELEASE_NOTES_V1.0.0.md)

## Status

**Foundation through browser V1 evidence is implemented.** Identity, owner-scoped CRUD, workflow/history, search/filters/deadlines, the action-first dashboard, authenticated browser workflows, OpenAPI contract checks, static analysis, secret hygiene, CSRF/CORS evidence, full browser E2E and migration rollback/reapply evidence are complete slices.

The `v1.0.0` changelog entry and dedicated release notes are prepared. The remaining tagged-release gate is to verify the exact release commit is green and then create the tag/GitHub Release on that same verified commit.

V1 uses Sanctum for first-party cookie/session authentication only; it does not issue personal access tokens.

## License

MIT — see [LICENSE](LICENSE).
