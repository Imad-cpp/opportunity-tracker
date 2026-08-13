# Security Model

## Assets

V1 stores personal application-tracking data: account identity, opportunity metadata, deadlines, notes and activity history. It intentionally does not store resumes, identity documents, email inbox contents or uploaded files.

## Trust boundaries

- Browser input is untrusted.
- URL fields are untrusted data, not instructions to fetch remote content.
- Client-side filtering and hidden controls are not authorization.
- PostgreSQL is authoritative for ownership and lifecycle state.

## Authentication

- Laravel Sanctum first-party session cookies.
- V1 does not issue personal access tokens.
- Registration and login regenerate the session identifier.
- Logout invalidates the current server-side session and regenerates the CSRF token.
- `/sanctum/csrf-cookie` provides the first-party SPA CSRF bootstrap.
- CSRF middleware remains enabled for state-changing stateful browser requests.
- Laravel's PHPUnit environment bypasses CSRF verification by framework design. Browser-level CSRF enforcement evidence therefore remains a release-hardening item.
- Registration and failed login attempts have bounded request rates.
- Deployed environments must use secure cookies over HTTPS. Local Compose uses HTTP-only local settings.

## Authorization

Every implemented opportunity endpoint starts from the authenticated owner's query scope. Collection reads and object reads/mutations use the same owner-scoped boundary.

A foreign opportunity UUID returns the same stable `404 NOT_FOUND` response as a missing UUID. Automated tests cover foreign read, update, archive, restore and delete operations.

`owner_id` is not mass assignable. New opportunities are created through the authenticated user's relationship, so ownership comes from the session identity rather than request payload data.

## Input rules

Implemented account rules:

- Names, normalized RFC email addresses and passwords are bounded/validated server-side.
- Registration passwords require at least 12 characters with mixed case, a number and a symbol.
- Account time zones must match an IANA time-zone identifier.

Implemented ordinary opportunity CRUD rules:

- `type` and `priority` use fixed allowlists.
- Title and organization are required, trimmed and bounded.
- Location and notes are optional bounded strings; empty optional values normalize to null.
- Source URLs must use a valid `http` or `https` URL.
- The API does not fetch supplied source URLs in V1.
- `owner_id`, `status`, deadline fields, next-action fields and `archived_at` are not accepted through ordinary CRUD payloads.
- Create assigns `SAVED` server-side. Status changes are reserved for the workflow/history endpoint.

Planned rules:

- Deadline precision/time-zone validation and normalization.
- Next-action validation.
- Search, sort and filter allowlists.

## Privacy and logging

Logs must not contain passwords, session cookies, CSRF tokens, authorization headers or full request bodies.

Event history is product history, not a forensic security log. Test/demo data must be synthetic.

## Deletion

Permanent delete currently removes the owned opportunity row. Once `opportunity_events` is implemented, dependent product-history events must be deleted with it. Archive remains the reversible retention mechanism and does not change status.

## CORS

CORS is configured from an explicit first-party allowlist and supports credentials for the SPA session flow. Automated tests verify that an unconfigured origin is not reflected as an allowed origin.

## V1 excluded surfaces

There is no remote URL fetching, file upload, rich HTML input, public sharing, team membership, webhook ingestion or OAuth integration in V1.
