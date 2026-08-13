# Definition of Done

A V1 release is complete only when the same exact release commit satisfies the applicable evidence below.

## Product

- [x] Register/login/logout/me flow works.
- [x] Authenticated user can create, read and edit an opportunity.
- [x] Status changes append visible history.
- [x] Archive/restore works without losing history.
- [x] Permanent delete removes the opportunity and dependent events.
- [x] Search and V1 filters work together predictably.
- [ ] Dashboard shows due soon, overdue, active-status counts and recent activity.
- [ ] Next action is visible in list/detail/dashboard where relevant.
  - Backend capture/update and API representation are implemented; frontend/dashboard presentation remains open.
- [x] Date-only and exact-time deadline semantics are tested.

## Security and privacy

- [x] Every implemented opportunity resource is server-side owner scoped.
- [x] Foreign opportunity UUIDs return `404` on reads and mutations, including history reads and workflow changes.
- [x] Authentication endpoints are rate limited.
- [ ] Session/CSRF behavior is fully tested at the browser boundary.
  - Session rotation, logout invalidation and Sanctum CSRF-cookie bootstrap are covered. Browser-level rejection evidence remains open.
- [x] CORS does not allow an unconfigured origin to become an allowed origin.
- [x] URL scheme validation rejects non-HTTP(S) values.
- [ ] User-authored notes are rendered as text, not HTML.
  - Backend accepts bounded string notes; frontend rendering evidence is still required.
- [ ] Logs/tests/demos contain no secrets or real personal application data.
  - Current automated fixtures are synthetic; final release-wide secret/demo evidence remains open.

## Frontend

- [ ] Responsive mobile and desktop layouts.
- [ ] Keyboard navigation and visible focus states.
- [ ] Loading, empty, error, success and disabled states.
- [ ] Forms expose validation errors accessibly.
- [ ] No core workflow depends on hover only.

## API and data

- [ ] Database migrations and rollback path are reviewed for the full V1 schema.
- [x] Useful owner/status/deadline/event indexes exist on the implemented persistence boundary.
- [x] Pagination and allowlisted filtering are covered by tests.
- [x] Stable identity/resource error envelopes are documented and exercised.
- [x] Workflow mutations and corresponding event writes are transactionally coupled.
- [x] Event history is owner scoped and delete cascades dependent product events.
- [ ] OpenAPI matches implemented V1 routes and schemas.

## Automated evidence

- [ ] Backend formatting/static analysis/tests are green.
  - Formatting and PostgreSQL-backed tests are green; static analysis is added in the hardening step.
- [ ] Frontend lint/typecheck/tests/build are green.
  - Lint, typecheck and build are green; product-level frontend tests are not present yet.
- [x] Integration tests use PostgreSQL, not a different database engine as a substitute.
- [ ] End-to-end demo proves register → create → update status → filter/dashboard → archive/restore → delete.
- [ ] Dependency audit and secret-hygiene checks are green.
  - Composer audit and the bounded web dependency gate run now; full secret-hygiene evidence is added in hardening.
- [x] CI actions are pinned and permissions are least-privilege for the permanent workflow.

## Release evidence

- [ ] README and architecture/security/data/API docs match the complete V1 implementation.
  - Documents are synchronized through search/filters/deadlines; later V1 steps remain intentionally marked planned.
- [ ] CHANGELOG and release notes are prepared.
- [ ] Exact release commit is green before tag creation.
- [ ] Tag/release points to that exact verified commit.