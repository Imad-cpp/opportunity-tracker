# Definition of Done

A V1 release is complete only when the same exact release commit satisfies the applicable evidence below.

## Product

- [x] Register/login/logout/me flow works.
- [x] Authenticated user can create, read and edit an opportunity.
- [x] Status changes append visible history.
- [x] Archive/restore works without losing history.
- [x] Permanent delete removes the opportunity and dependent events.
- [x] Search and V1 filters work together predictably.
- [x] Dashboard shows due soon, overdue, active-status counts and recent activity.
- [ ] Next action is visible in list/detail/dashboard where relevant.
  - Backend capture/update, API representation and dashboard presentation are implemented; dedicated list/detail frontend surfaces remain open.
- [x] Date-only and exact-time deadline semantics are tested.

## Security and privacy

- [x] Every implemented opportunity resource is server-side owner scoped.
- [x] Foreign opportunity UUIDs return `404` on reads and mutations, including history reads and workflow changes.
- [x] Authentication endpoints are rate limited.
- [x] Session/CSRF behavior is tested at the browser boundary.
  - Session rotation/logout invalidation are covered at the application boundary, and running-stack CI verifies Sanctum CSRF bootstrap, trusted credentialed CORS, 419 rejection without the XSRF header and the stable `CSRF_TOKEN_MISMATCH` JSON envelope.
- [x] CORS does not allow an unconfigured origin to become an allowed origin.
- [x] URL scheme validation rejects non-HTTP(S) values.
- [ ] User-authored notes are rendered as text, not HTML.
  - Backend accepts bounded string notes; a note-rendering frontend surface does not exist yet.
- [ ] Logs/tests/demos contain no secrets or real personal application data.
  - Automated fixtures are synthetic and full Git history passes the Gitleaks gate; final release demo evidence remains open.

## Frontend

- [x] Responsive mobile and desktop layouts.
  - The dashboard uses responsive pipeline, attention, opportunity and activity layouts with explicit mobile breakpoints.
- [x] Keyboard navigation and visible focus states.
- [x] Loading, empty, error, success and disabled states.
- [ ] Forms expose validation errors accessibly.
  - Dashboard V1 has no form surface yet; this remains required when capture/edit UI is added.
- [x] No core workflow depends on hover only.

## API and data

- [ ] Database migrations and rollback path are reviewed for the full V1 schema.
- [x] Useful owner/status/deadline/event indexes exist on the implemented persistence boundary.
- [x] Pagination and allowlisted filtering are covered by tests.
- [x] Stable identity/resource error envelopes are documented and exercised.
- [x] Workflow mutations and corresponding event writes are transactionally coupled.
- [x] Event history is owner scoped and delete cascades dependent product events.
- [x] OpenAPI matches implemented V1 routes and schemas.
  - OpenAPI 3.1 is committed and contract CI checks route coverage, private/public security requirements, references and critical enums.

## Automated evidence

- [x] Backend formatting/static analysis/tests are green.
  - Formatting, PostgreSQL-backed tests and PHPStan level 1 are green; PHPStan runs from an isolated pinned toolchain without changing the application lockfile.
- [ ] Frontend lint/typecheck/tests/build are green.
  - Lint, typecheck and build are green for the dashboard surface; product-level frontend tests are not present yet.
- [x] Integration tests use PostgreSQL, not a different database engine as a substitute.
- [ ] End-to-end demo proves register → create → update status → filter/dashboard → archive/restore → delete.
- [x] Dependency audit and secret-hygiene checks are green.
  - Composer audit, the bounded web dependency gate and full-history Gitleaks scanning are active CI gates.
- [x] CI actions are pinned and permissions are least-privilege for the permanent workflow.

## Release evidence

- [ ] README and architecture/security/data/API docs match the complete V1 implementation.
  - Product/API/security/decision/roadmap documentation is synchronized through hardening; remaining browser product surfaces and release evidence are still open.
- [ ] CHANGELOG and release notes are prepared.
- [ ] Exact release commit is green before tag creation.
- [ ] Tag/release points to that exact verified commit.
