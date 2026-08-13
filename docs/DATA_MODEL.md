# Data Model

All primary identifiers are UUIDs.

## Implementation status

The `users`, `sessions`, `password_reset_tokens` and `opportunities` tables are implemented. `opportunity_events` remains planned for the workflow/history step.

## users

- `id` UUID primary key
- `name` string
- `email` normalized unique string
- `password` hash
- `timezone` validated IANA time-zone identifier
- timestamps

Account identifiers are generated server-side through Eloquent UUID support. Registration normalizes email casing and surrounding whitespace before uniqueness validation/persistence.

## sessions

Laravel's server-side session table stores the authenticated browser session. Its nullable `user_id` column is UUID-compatible with `users.id`.

V1 does not introduce a personal-access-token table as part of the public authentication contract; Sanctum is used for first-party session authentication.

## opportunities

- `id` UUID primary key
- `owner_id` UUID foreign key → users
- `type` enum-like string
- `status` enum-like string
- `priority` enum-like string
- `title` string
- `organization` string
- `source_url` nullable string
- `location` nullable string
- `notes` nullable plain text
- `deadline_at` nullable UTC timestamp
- `deadline_precision` nullable `DATE` or `DATETIME`
- `deadline_timezone` nullable IANA zone
- `next_action` nullable string
- `next_action_at` nullable UTC timestamp
- `archived_at` nullable timestamp
- timestamps

The model exposes an `ownedBy(User)` query scope. Public CRUD endpoints are not implemented yet, so the table/model currently establishes the persistence and authorization boundary for the next step rather than exposing an incomplete API.

### Invariants

- Every opportunity belongs to exactly one owner.
- `source_url`, when present, must be an absolute `http` or `https` URL.
- Notes are plain text and bounded in size.
- `deadline_precision` is null when no deadline exists.
- `deadline_timezone` is required when an exact deadline time was supplied.
- Date-only deadline input records `DATE` precision.
- Archive does not alter status automatically.

Validation for the opportunity-field invariants above is planned with CRUD and must not be inferred merely from the current database columns.

## opportunity_events

Append-oriented user-facing history for meaningful lifecycle activity. **Not implemented yet.**

- `id` UUID primary key
- `opportunity_id` UUID foreign key → opportunities
- `actor_id` UUID foreign key → users
- `type` enum-like string
- `from_status` nullable string
- `to_status` nullable string
- `changed_fields` nullable JSON array of field names
- `created_at` timestamp

Initial V1 event types:

- `CREATED`
- `UPDATED`
- `STATUS_CHANGED`
- `ARCHIVED`
- `RESTORED`

Event metadata must not duplicate notes, session data, credentials or other unnecessary sensitive content. For ordinary updates, record changed field names rather than before/after body values.

## Delete behavior

Permanent deletion will cascade dependent `opportunity_events` once that table is implemented. V1 prefers user data minimization over retaining a hidden forensic tombstone for deleted personal tracking data.

## Useful indexes

Implemented on `opportunities`:

- `(owner_id, archived_at, updated_at)`
- `(owner_id, status)`
- `(owner_id, type)`
- `(owner_id, priority)`
- `(owner_id, deadline_at)`

Planned with events:

- `(opportunity_id, created_at)`
