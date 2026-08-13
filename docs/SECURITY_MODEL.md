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
- Secure, HTTP-only cookies in deployed environments.
- CSRF protection for state-changing browser requests.
- Session rotation on authentication where framework behavior supports it.
- Server-side rate limits on authentication endpoints.

## Authorization

Every opportunity query and mutation is scoped by the authenticated owner's UUID before resolving the resource.

A valid UUID owned by another account returns `404`, reducing object-existence leakage and preventing IDOR/BOLA behavior.

## Input rules

- Strict enum allowlists for type, status, priority and deadline precision.
- Bounded strings and notes.
- Notes remain plain text; user-authored HTML is not rendered.
- Source URLs accept absolute `http`/`https` only.
- The API never fetches the supplied source URL in V1, avoiding SSRF surface from this field.
- Search, sort and filter parameters are allowlisted.
- Time-zone identifiers are validated rather than trusted as arbitrary strings.

## Privacy and logging

Logs must not contain passwords, session cookies, CSRF tokens, authorization headers or full request bodies.

Event history is product history, not a forensic security log. `changed_fields` records field names, not note bodies or secret values.

Test/demo data must be synthetic.

## Deletion

Permanent delete removes the owned opportunity and cascades its product-history events. Archive is the reversible retention mechanism.

## CORS

Browser access is deny-by-default unless an explicit first-party origin is configured. Wildcard origins are not the V1 steady state.

## V1 excluded attack surfaces

There is no remote URL fetching, file upload, rich HTML input, public sharing, team membership, webhook ingestion or OAuth integration in V1. Adding any of those requires a security review and a documented decision.
