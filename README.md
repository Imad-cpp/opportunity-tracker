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
- Priority, organization, source URL, location, notes, deadline and next action.
- Search and filters by status, type, priority, archive state and deadline window.
- Dashboard summaries for due soon, overdue, active and recently changed opportunities.
- Append-oriented user-facing activity history for meaningful lifecycle changes.
- Explicit deadline precision/time-zone handling instead of silently inventing times.
- Owner isolation on every private read and mutation.

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

## Documentation

- [Product contract](docs/PRODUCT.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Data model](docs/DATA_MODEL.md)
- [API map](docs/API_MAP.md)
- [Security model](docs/SECURITY_MODEL.md)
- [Engineering decisions](docs/DECISIONS.md)
- [Definition of Done](docs/DEFINITION_OF_DONE.md)
- [Roadmap](docs/ROADMAP.md)

## Status

**Owner-scoped search, filters and deadline semantics are implemented.** The list endpoint now supports bounded title/organization search, allowlisted status/type/priority/archive/deadline filters, deterministic fixed-size pagination and account-time-zone date boundaries. Deadline create/update/clear preserves date-only versus exact precision, while API responses derive `OVERDUE`, `DUE_SOON` and `UPCOMING` attention without silently changing lifecycle status.

Identity, CRUD, workflow/history and search/filters/deadlines are complete roadmap slices. The action-first dashboard and frontend product states are the next implementation step.

V1 uses Sanctum for first-party cookie/session authentication only; it does not issue personal access tokens.

## License

MIT — see [LICENSE](LICENSE).