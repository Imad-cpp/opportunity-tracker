# API Map

Base path: `/api/v1`

All private endpoints are owner-scoped. Foreign resource identifiers must resolve as `404`.

## Authentication

| Method | Path | Purpose | Status |
|---|---|---|---|
| POST | `/auth/register` | Create account and authenticated session | Implemented |
| POST | `/auth/login` | Start authenticated session | Implemented |
| POST | `/auth/logout` | End current session | Implemented |
| GET | `/me` | Return current account profile | Implemented |

Sanctum CSRF/session bootstrap uses Laravel's `/sanctum/csrf-cookie` framework endpoint outside `/api/v1`. V1 uses first-party cookie/session authentication only and does not issue personal access tokens.

## Opportunities

| Method | Path | Purpose | Status |
|---|---|---|---|
| GET | `/opportunities` | List/search/filter owned opportunities | Planned |
| POST | `/opportunities` | Create opportunity | Planned |
| GET | `/opportunities/{id}` | Read one owned opportunity | Planned |
| PATCH | `/opportunities/{id}` | Update editable fields | Planned |
| DELETE | `/opportunities/{id}` | Permanently delete opportunity | Planned |
| POST | `/opportunities/{id}/status` | Change status and append event | Planned |
| POST | `/opportunities/{id}/archive` | Archive without deleting | Planned |
| POST | `/opportunities/{id}/restore` | Restore archived item | Planned |
| GET | `/opportunities/{id}/events` | List user-facing activity history | Planned |

The opportunity table/model and reusable owner scope exist, but these public CRUD routes are intentionally deferred to the next roadmap step.

## Dashboard

| Method | Path | Purpose | Status |
|---|---|---|---|
| GET | `/dashboard/summary` | Due-soon, overdue, status and recent-activity summary | Planned |

## List query parameters

Planned V1 filters:

- `q` — bounded text search over title and organization
- `status`
- `type`
- `priority`
- `archived` — `true` or `false`
- `deadline_from`
- `deadline_to`
- `page`

Sorting is server-defined and allowlisted. Arbitrary SQL column/direction input is not accepted.

## Response conventions

Successful object responses use a top-level `data` member. Validation and domain failures use stable machine-readable error codes.

Implemented baseline errors:

- `UNAUTHENTICATED` — 401
- `VALIDATION_FAILED` — 422
- `RATE_LIMITED` — 429
- `CSRF_TOKEN_MISMATCH` — 419 when the framework CSRF middleware rejects a state-changing request

Planned resource/domain errors:

- `FORBIDDEN` — 403 only for non-resource authorization cases
- `NOT_FOUND` — 404
- `DEPENDENCY_UNAVAILABLE` — 503 where applicable

Exact schemas will be frozen in OpenAPI before V1 release.
