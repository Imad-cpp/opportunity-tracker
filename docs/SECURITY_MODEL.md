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
- V1 does not issue personal access tokens; browser-stored long-lived bearer tokens are outside the authentication contract.
- Registration and login regenerate the session identifier.
- Logout invalidates the current server-side session and regenerates the CSRF token.
- `/sanctum/csrf-cookie` provides the first-party SPA CSRF bootstrap.
- CSRF middleware remains enabled for state-changing stateful browser requests.
- Laravel's PHPUnit environment bypasses CSRF verification by framework design, so repository tests verify CSRF bootstrap/session behavior rather than pretending an ordinary HTTP test proves the production 419 path. Browser-level CSRF enforcement evidence remains a release-hardening item.
- Registration is throttled at the route boundary. Failed login attempts are limited to five attempts per minute using a hash of normalized email plus requester IP; raw email is not used in the limiter key.
- Deployed environments must use secure cookies over HTTPS. Local Compose keeps secure-cookie enforcement off only because the local URLs are HTTP.

## Authorization

Opportunity persistence is keyed by an authenticated owner's UUID. The Eloquent model exposes a reusable `ownedBy(User)` scope, and automated tests prove that the scope excludes another account's record.

Public opportunity CRUD endpoints are not implemented yet. When they are added, resource resolution must begin from the owner-scoped query rather than resolving globally and checking ownership afterward.

A valid UUID owned by another account must return `404`, reducing object-existence leakage and preventing IDOR/BOLA behavior.

## Input rules

Implemented account rules:

- Names, normalized RFC email addresses and passwords are bounded/validated server-side.
- Registration passwords require at least 12 characters with mixed case, a number and a symbol.
- Account time zones must match an IANA time-zone identifier.

Planned opportunity rules:

- Strict enum allowlists for type, status, priority and deadline precision.
- Bounded strings and notes.
- Notes remain plain text; user-authored HTML is not rendered.
- Source URLs accept absolute `http`/`https` only.
- The API never fetches the supplied source URL in V1, avoiding SSRF surface from this field.
- Search, sort and filter parameters are allowlisted.

## Privacy and logging

Logs must not contain passwords, session cookies, CSRF tokens, authorization headers or full request bodies.

Event history is product history, not a forensic security log. `changed_fields` records field names, not note bodies or secret values.

Test/demo data must be synthetic.

## Deletion

Permanent delete will remove the owned opportunity and cascade its product-history events after those workflows are implemented. Archive is the reversible retention mechanism.

## CORS

CORS is configured from an explicit first-party allowlist and supports credentials for the SPA session flow. Wildcard origins are not the V1 steady state. Automated tests verify that an untrusted origin is not reflected as an allowed origin.

## V1 excluded attack surfaces

There is no remote URL fetching, file upload, rich HTML input, public sharing, team membership, webhook ingestion or OAuth integration in V1. Adding any of those requires a security review and a documented decision.
