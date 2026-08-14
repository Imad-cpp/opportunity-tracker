# Security Model

## Assets

V1 stores account identity and personal opportunity-tracking data: opportunity metadata, deadlines, plain-text notes, next actions and user-facing activity history. It intentionally excludes resumes, identity documents, uploads and inbox contents.

## Trust boundaries

- Browser input is untrusted.
- Client-side filtering and hidden controls are not authorization.
- URL fields are data only and are never fetched server-side in V1.
- PostgreSQL is authoritative for ownership and lifecycle state.
- Dashboard aggregation is owner-scoped server-side.

## Authentication

- Laravel Sanctum provides first-party cookie/session authentication.
- V1 does not issue personal access tokens.
- Registration and login regenerate the session identifier.
- Logout invalidates the current server-side session and regenerates CSRF state.
- `/sanctum/csrf-cookie` provides the SPA CSRF bootstrap.
- State-changing stateful browser requests remain protected by CSRF middleware.
- Authentication endpoints have bounded request rates.
- Deployed environments must use secure cookies over HTTPS; local Compose uses localhost HTTP settings only.

## Authorization

Every private product route requires the authenticated session. Opportunity reads and mutations, event history and dashboard aggregation are owner-scoped server-side.

Foreign opportunity UUIDs use the same stable `404 NOT_FOUND` response as missing UUIDs. Ownership is assigned through the authenticated user's relationship; `owner_id` is not a writable product field.

Dashboard summaries intentionally omit owner identifiers and note bodies. Dashboard activity also omits actor identifiers.

## Input rules

Implemented account rules include bounded names, normalized RFC email addresses, strong registration passwords and IANA time zones.

Implemented opportunity rules include:

- fixed type, status and priority allowlists;
- required bounded title and organization on create;
- bounded optional location, notes and next action;
- HTTP(S)-only source URLs;
- server-controlled initial `SAVED` status and a dedicated status endpoint;
- `DATE` versus `DATETIME` deadline precision with time-zone validation and UTC normalization for exact date/time input;
- bounded title/organization search;
- allowlisted status, type, priority, archive state, deadline range and page filters;
- rejection of reversed deadline ranges and arbitrary SQL sort/column input.

## History and deletion

Workflow mutations and corresponding product-history events commit together. Repeated no-op requests do not manufacture duplicate history. `UPDATED` history records changed field names rather than copied user content.

Permanent delete removes the owned opportunity and dependent product-history events through the database cascade. Archive is reversible and preserves history.

## CORS and browser boundary

CORS uses an explicit first-party allowlist and supports credentials for the SPA session flow. Automated tests reject an unconfigured origin. Stack smoke verifies the configured first-party origin, credentialed CORS and CSRF rejection behavior against the running Docker application.

## Contract and CI controls

- The committed OpenAPI 3.1 contract documents the implemented V1 HTTP boundary.
- Contract CI checks Laravel route coverage, public/private session requirements, local references and critical enums.
- Git history is scanned by a pinned Gitleaks action.
- PHP syntax, strict PSR autoloading and platform requirements are checked independently from feature tests.
- PHPStan runs from an isolated pinned toolchain without modifying the application lockfile.
- npm and Composer install from committed lockfiles; dependency audits remain blocking except for the exact documented expiring web-framework exception.
- Permanent GitHub Actions are pinned to immutable commit SHAs.

## Data-handling evidence

Tests and demos use synthetic data. Application history is product history, not a forensic security log. Sensitive authentication material is not part of product payloads or dashboard responses.

## V1 excluded surfaces

There is no remote URL fetching, file upload, rich HTML input, public sharing, team membership, webhook ingestion or OAuth integration in V1.
