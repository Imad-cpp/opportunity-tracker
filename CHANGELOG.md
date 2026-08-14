# Changelog

## Unreleased

### Added

- Product and engineering foundation for Opportunity Tracker V1.
- Next.js web, Laravel API, PostgreSQL and Docker scaffold with CI quality gates.
- First-party session identity and owner-scoped opportunity data.
- Opportunity CRUD, archive/restore, status workflow, next actions and activity history.
- Search across title and organization with allowlisted filters and 20-item pagination.
- Date-only and exact deadline semantics with time-zone-aware boundaries.
- Derived overdue, due-soon and upcoming attention states.
- Action-first owner-scoped dashboard summary with active pipeline counts, overdue/due-soon deadlines, next actions and recent activity.
- Responsive Next.js dashboard surface with loading, empty, error, refresh, disabled, keyboard-focus and reduced-motion states.
- PostgreSQL-backed dashboard aggregation and authenticated endpoint contract tests using synthetic data.
- OpenAPI 3.1 contract for the implemented V1 HTTP boundary with automated Laravel route, security, reference and enum drift checks.
- PHPStan level-1 static analysis from an isolated pinned toolchain, plus PHP syntax, strict PSR autoloading and platform requirement checks.
- Full-history Gitleaks scanning through a pinned, read-only GitHub Action.
- Running-stack browser security evidence for Sanctum CSRF bootstrap, trusted credentialed CORS, missing-XSRF rejection and the stable CSRF error envelope.
- CI diagnostics for dependency-audit failures.

### Changed

- Aligned the dashboard TypeScript deadline precision contract with the backend `DATE` / `DATETIME` vocabulary.
- Made API resources explicitly model-typed for static analysis instead of relying on implicit resource-property forwarding.
- Removed the unused generated console inspiration command from the V1 application boundary.
- Preserved `CSRF_TOKEN_MISMATCH` after Laravel converts token mismatches into HTTP 419 exceptions at runtime.
