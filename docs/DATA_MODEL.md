# Data Model

All primary identifiers are UUIDs.

## Implementation status

The `users`, `sessions`, `password_reset_tokens`, `opportunities` and `opportunity_events` tables are implemented. Opportunity CRUD and workflow/history persistence are implemented.

## users

- `id` UUID primary key
- `name` string
- `email` normalized unique string
- `password` hash
- `timezone` validated IANA time-zone identifier
- timestamps

## sessions

Laravel's server-side session table stores the authenticated browser session. Its nullable `user_id` column is UUID-compatible with `users.id`.

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

The model exposes `ownedBy(User)` and an `events()` relationship. New records are created through the authenticated user's relationship and `owner_id` is not mass assignable.

### Implemented behavior

- Create assigns `SAVED` server-side and appends `CREATED`.
- `type` and `priority` use fixed allowlists.
- Title and organization are required, trimmed and bounded.
- Source URL accepts valid HTTP(S) URLs only.
- Location and notes are optional bounded strings.
- `next_action` and `next_action_at` can be captured or updated without changing status.
- Ordinary CRUD cannot write status, deadline or archive fields directly.
- Status changes use the dedicated workflow route and record old/new status.
- Archive/restore is separate from status and records only real transitions.
- No-op ordinary updates and repeated status/archive/restore requests do not append false history.
- The default list excludes archived records; owned detail reads may inspect them.

### Deferred field behavior

- Deadline precision/time-zone normalization: search/deadline step.

## opportunity_events

Append-oriented user-facing product history is implemented with:

- `id` UUID primary key
- `opportunity_id` UUID foreign key → opportunities, cascade delete
- `actor_id` UUID foreign key → users
- `type` enum-like string
- `from_status` nullable string
- `to_status` nullable string
- `changed_fields` nullable JSON array of field names
- `created_at` timestamp

Implemented event types:

- `CREATED`
- `UPDATED`
- `STATUS_CHANGED`
- `ARCHIVED`
- `RESTORED`

Opportunity state changes and their corresponding history events are written in the same PostgreSQL transaction. Mutating handlers take an owner-scoped row lock before workflow changes.

`UPDATED.changed_fields` stores sorted field names only. It does not duplicate note bodies or before/after content values. The public event resource omits `actor_id`.

## Delete behavior

Permanent deletion removes the owned opportunity and cascades its dependent `opportunity_events`. V1 therefore does not keep a hidden personal-data tombstone after explicit deletion.

## Useful indexes

Implemented:

- `(owner_id, archived_at, updated_at)`
- `(owner_id, status)`
- `(owner_id, type)`
- `(owner_id, priority)`
- `(owner_id, deadline_at)`
- `(opportunity_id, created_at)`
