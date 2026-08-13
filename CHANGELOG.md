# Changelog

All meaningful project changes are recorded here.

## Unreleased

### Added

- Initial Opportunity Tracker product contract and V1 scope.
- Architecture, data model, API map and security model.
- Engineering decisions, Definition of Done and roadmap.
- MIT license.
- Documentation quality validation for required files and local Markdown links.
- Added the `apps/web` Next.js 16.2 application with a committed npm lockfile.
- Added the `apps/api` Laravel 13 application with a committed Composer lockfile.
- Added PostgreSQL 18.4 and non-root application Docker runtimes through `compose.yaml`.
- Added a minimal API liveness endpoint for stack verification.
- Recorded stable scaffold/toolchain and baseline-CI decisions.
- Added a narrowly scoped, time-limited dependency-audit policy for the current stable web framework dependency tree; unexpected findings and version changes remain blocking.
- Corrected the PostgreSQL 18 Compose volume target to the image's version-aware parent data directory.
- Added first-party account session endpoints for registration, login, logout and current-account reads.
- Added UUID accounts, normalized email storage and validated account time zones.
- Added explicit browser-origin/session configuration and bounded account-request limits.
- Added the UUID owner-linked opportunity persistence boundary and owner-scope tests ahead of CRUD.
- Replaced generated placeholder API tests with synthetic identity and ownership coverage.
- Added authenticated opportunity create, list, detail, update, archive, restore and delete endpoints.
- Added type, priority, source URL and bounded text validation for ordinary opportunity edits.
- Added stable missing-resource behavior across opportunity reads and mutations.
- Kept status, deadline and next-action changes outside ordinary CRUD for their dedicated roadmap steps.
