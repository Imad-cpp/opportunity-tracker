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
| POST | `/opportunities` | Create an owned opportunity with server-controlled `SAVED` status and a `CREATED` event | Implemented |
| GET | `/opportunities/{id}` | Read one owned opportunity, including archived records | Implemented |
| PATCH | `/opportunities/{id}` | Update ordinary editable fields and append `UPDATED` when values change | Implemented |
| DELETE | `/opportunities/{id}` | Permanently delete owned opportunity and dependent product history | Implemented |
| POST | `/opportunities/{id}/archive` | Archive without changing status and append `ARCHIVED` on a real transition | Implemented |
| POST | `/opportunities/{id}/restore` | Restore archived item and append `RESTORED` on a real transition | Implemented |
| POST | `/opportunities/{id}/status` | Change status and append `STATUS_CHANGED` with from/to values | Implemented |
| GET | `/opportunities/{id}/events` | List owned user-facing activity history, newest first | Implemented |

Ordinary create/update payloads cannot set `owner_id`, `status`, deadline fields or `archived_at`. `next_action` and `next_action_at` are editable ordinary product fields. Ownership is assigned from the authenticated account relationship. Status writes use the dedicated workflow route so the state mutation and history event commit atomically.

Repeated requests that do not change data do not fabricate `UPDATED`, `STATUS_CHANGED`, `ARCHIVED` or `RESTORED` events.

Event responses expose product-history metadata only: event id/type, status from/to values, changed field names and creation time. They do not expose note bodies or the actor identifier.

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
