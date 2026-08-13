# Data Model

All primary identifiers are UUIDs.

## Implementation status

The `users`, `sessions`, `password_reset_tokens` and `opportunities` tables are implemented. Opportunity CRUD is implemented. `opportunity_events` remains planned.

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

The model exposes `ownedBy(User)`. New records are created through the authenticated user's relationship and `owner_id` is not mass assignable.

### Implemented CRUD behavior

- Create assigns `SAVED` server-side.
- `type` and `priority` use fixed allowlists.
- Title and organization are required, trimmed and bounded.
- Source URL accepts valid HTTP(S) URLs only.
- Location and notes are optional bounded strings.
- Ordinary CRUD does not write status, deadline, next-action or archive fields directly.
- Archive/restore is separate from status.
- The default list excludes archived records; owned detail reads may inspect them.

### Deferred field behavior

- Status changes and history: workflow/history step.
- Next action: workflow/history step.
- Deadline precision/time-zone normalization: search/deadline step.

## opportunity_events

**Not implemented yet.** Planned fields:

- `id` UUID primary key
- `opportunity_id` UUID foreign key → opportunities
- `actor_id` UUID foreign key → users
- `type` enum-like string
- `from_status` nullable string
- `to_status` nullable string
- `changed_fields` nullable JSON array
- `created_at` timestamp

Planned event types: `CREATED`, `UPDATED`, `STATUS_CHANGED`, `ARCHIVED`, `RESTORED`.

## Delete behavior

Permanent deletion currently removes the owned opportunity row. When events are added, dependent events must be removed with the opportunity.

## Useful indexes

Implemented:

- `(owner_id, archived_at, updated_at)`
- `(owner_id, status)`
- `(owner_id, type)`
- `(owner_id, priority)`
- `(owner_id, deadline_at)`

Planned with events:

- `(opportunity_id, created_at)`
