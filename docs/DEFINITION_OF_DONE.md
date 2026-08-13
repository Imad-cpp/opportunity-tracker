# Definition of Done

A V1 release is complete only when the same exact release commit satisfies the applicable evidence below.

## Product

- [x] Register/login/logout/me flow works.
- [x] Authenticated user can create, read and edit an opportunity.
- [ ] Status changes append visible history.
- [ ] Archive/restore works without losing history.
  - Archive/restore behavior works now; event-history retention is added with the workflow/history step.
- [ ] Permanent delete removes the opportunity and dependent events.
  - Opportunity deletion works now; dependent event deletion is verified after events exist.
- [ ] Search and V1 filters work together predictably.
- [ ] Dashboard shows due soon, overdue, active-status counts and recent activity.
- [ ] Next action is visible in list/detail/dashboard where relevant.
- [ ] Date-only and exact-time deadline semantics are tested.

## Security and privacy

- [x] Every implemented opportunity resource is server-side owner scoped.
- [x] Foreign opportunity UUIDs return `404` on reads and mutations.
- [x] Authentication endpoints are rate limited.
- [ ] Session/CSRF behavior is fully tested at the browser boundary.
  - Session rotation, logout invalidation and Sanctum CSRF-cookie bootstrap are covered. Browser-level rejection evidence remains open.
- [x] CORS does not allow an unconfigured origin to become an allowed origin.
- [x] URL scheme validation rejects non-HTTP(S) values.
- [ ] User-authored notes are rendered as text, not HTML.
  - Backend accepts bounded string notes; frontend rendering evidence is still required.
- [ ] Logs/tests/demos contain no secrets or real personal application data.

## Frontend

- [ ] Responsive mobile and desktop layouts.
- [ ] Keyboard navigation and visible focus states.
- [ ] Loading, empty, error, success and disabled states.
- [ ] Forms expose validation errors accessibly.
- [ ] No core workflow depends on hover only.

## API and data

- [ ] Database migrations and rollback path are reviewed for the full V1 schema.
- [x] Useful owner/status/deadline indexes exist on the opportunity persistence boundary.
- [ ] Pagination and allowlisted filtering are covered by tests.
- [x] Stable identity/resource error envelopes are documented and exercised.
- [ ] OpenAPI matches implemented V1 routes and schemas.

## Automated evidence

- [ ] Backend formatting/static analysis/tests are green.
  - Formatting and tests are green; static analysis is added in the hardening step.
- [ ] Frontend lint/typecheck/tests/build are green.
  - Lint, typecheck and build are green; product-level frontend tests are not present yet.
- [x] Integration tests use PostgreSQL, not a different database engine as a substitute.
- [ ] End-to-end demo proves register → create → update status → filter/dashboard → archive/restore → delete.
- [ ] Dependency audit and secret-hygiene checks are green.
- [x] CI actions are pinned and permissions are least-privilege for the permanent workflow.

## Release evidence

- [ ] README and architecture/security/data/API docs match the complete V1 implementation.
- [ ] CHANGELOG and release notes are prepared.
- [ ] Exact release commit is green before tag creation.
- [ ] Tag/release points to that exact verified commit.
