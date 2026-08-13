# Data Model

All primary identifiers are UUIDs.

## users

- `id` UUID primary key
- `name` string
- `email` normalized unique string
- `password` hash
- `timezone` validated IANA time-zone identifier
- timestamps

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

### Invariants

- Every opportunity belongs to exactly one owner.
- `source_url`, when present, must be an absolute `http` or `https` URL.
- Notes are plain text and bounded in size.
- `deadline_precision` is null when no deadline exists.
- `deadline_timezone` is required when an exact deadline time was supplied.
- Date-only deadline input records `DATE` precision.
- Archive does not alter status automatically.

## opportunity_events

Append-oriented user-facing history for meaningful lifecycle activity.

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

Permanent deletion cascades dependent `opportunity_events`. V1 prefers user data minimization over retaining a hidden forensic tombstone for deleted personal tracking data.

## Useful indexes

At minimum:

- `(owner_id, archived_at, updated_at)`
- `(owner_id, status)`
- `(owner_id, type)`
- `(owner_id, priority)`
- `(owner_id, deadline_at)`
- `(opportunity_id, created_at)` on events
