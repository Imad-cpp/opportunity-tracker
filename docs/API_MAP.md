# API Map

Base path: `/api/v1`

All private endpoints are owner-scoped. Foreign resource identifiers must resolve as `404`.

## Authentication

| Method | Path | Purpose |
|---|---|---|
| POST | `/auth/register` | Create account and authenticated session |
| POST | `/auth/login` | Start authenticated session |
| POST | `/auth/logout` | End current session |
| GET | `/me` | Return current account profile |

Sanctum CSRF/session bootstrap may use Laravel's framework endpoint outside `/api/v1` as required by first-party SPA authentication.

## Opportunities

| Method | Path | Purpose |
|---|---|---|
| GET | `/opportunities` | List/search/filter owned opportunities |
| POST | `/opportunities` | Create opportunity |
| GET | `/opportunities/{id}` | Read one owned opportunity |
| PATCH | `/opportunities/{id}` | Update editable fields |
| DELETE | `/opportunities/{id}` | Permanently delete opportunity |
| POST | `/opportunities/{id}/status` | Change status and append event |
| POST | `/opportunities/{id}/archive` | Archive without deleting |
| POST | `/opportunities/{id}/restore` | Restore archived item |
| GET | `/opportunities/{id}/events` | List user-facing activity history |

## Dashboard

| Method | Path | Purpose |
|---|---|---|
| GET | `/dashboard/summary` | Due-soon, overdue, status and recent-activity summary |

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

Planned baseline errors:

- `UNAUTHENTICATED` — 401
- `FORBIDDEN` — 403 only for non-resource authorization cases
- `NOT_FOUND` — 404
- `VALIDATION_FAILED` — 422
- `RATE_LIMITED` — 429
- `DEPENDENCY_UNAVAILABLE` — 503 where applicable

Exact schemas will be frozen in OpenAPI before V1 release.
