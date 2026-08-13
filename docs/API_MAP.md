# API Map

Base path: `/api/v1`

All private endpoints are owner-scoped. Foreign resource identifiers resolve as `404` rather than leaking existence.

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
| GET | `/opportunities` | List active owned opportunities | Implemented |
| POST | `/opportunities` | Create an owned opportunity with server-controlled `SAVED` status | Implemented |
| GET | `/opportunities/{id}` | Read one owned opportunity, including archived records | Implemented |
| PATCH | `/opportunities/{id}` | Update ordinary editable fields | Implemented |
| DELETE | `/opportunities/{id}` | Permanently delete owned opportunity | Implemented |
| POST | `/opportunities/{id}/archive` | Archive without changing status | Implemented |
| POST | `/opportunities/{id}/restore` | Restore archived item | Implemented |
| POST | `/opportunities/{id}/status` | Change status and append event | Planned |
| GET | `/opportunities/{id}/events` | List user-facing activity history | Planned |

Ordinary create/update payloads cannot set `owner_id`, `status`, deadline fields, next-action fields or `archived_at`. Ownership is assigned from the authenticated account relationship. Status writes are reserved for the workflow/history step so they can be recorded transactionally.

## Dashboard

| Method | Path | Purpose | Status |
|---|---|---|---|
| GET | `/dashboard/summary` | Due-soon, overdue, status and recent-activity summary | Planned |

## List query parameters

The current list endpoint returns the authenticated owner's non-archived opportunities ordered by most recently updated. Search, pagination and allowlisted filtering are planned for the search/filter step:

- `q` — bounded text search over title and organization
- `status`
- `type`
- `priority`
- `archived` — `true` or `false`
- `deadline_from`
- `deadline_to`
- `page`

Arbitrary SQL column/direction input will not be accepted.

## Response conventions

Successful object responses use a top-level `data` member. Validation and domain failures use stable machine-readable error codes.

Implemented errors:

- `UNAUTHENTICATED` — 401
- `NOT_FOUND` — 404 for missing or foreign opportunity identifiers
- `CSRF_TOKEN_MISMATCH` — 419 when the framework CSRF middleware rejects a state-changing request
- `VALIDATION_FAILED` — 422
- `RATE_LIMITED` — 429

Planned where applicable:

- `FORBIDDEN` — 403 only for non-resource authorization cases
- `DEPENDENCY_UNAVAILABLE` — 503

Exact schemas will be frozen in OpenAPI before V1 release.
