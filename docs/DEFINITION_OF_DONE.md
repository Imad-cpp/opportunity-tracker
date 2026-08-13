# Definition of Done

A V1 release is complete only when the same exact release commit satisfies the applicable evidence below.

## Product

- [ ] Register/login/logout/me flow works.
- [ ] Authenticated user can create, read and edit an opportunity.
- [ ] Status changes append visible history.
- [ ] Archive/restore works without losing history.
- [ ] Permanent delete removes the opportunity and dependent events.
- [ ] Search and V1 filters work together predictably.
- [ ] Dashboard shows due soon, overdue, active-status counts and recent activity.
- [ ] Next action is visible in list/detail/dashboard where relevant.
- [ ] Date-only and exact-time deadline semantics are tested.

## Security and privacy

- [ ] Every private resource is server-side owner scoped.
- [ ] Foreign opportunity UUIDs return `404` on reads and mutations.
- [ ] Authentication endpoints are rate limited.
- [ ] Session/CSRF behavior is tested.
- [ ] CORS is deny-by-default outside configured first-party origins.
- [ ] URL scheme validation rejects non-HTTP(S) values.
- [ ] User-authored notes are rendered as text, not HTML.
- [ ] Logs/tests/demos contain no secrets or real personal application data.

## Frontend

- [ ] Responsive mobile and desktop layouts.
- [ ] Keyboard navigation and visible focus states.
- [ ] Loading, empty, error, success and disabled states.
- [ ] Forms expose validation errors accessibly.
- [ ] No core workflow depends on hover only.

## API and data

- [ ] Database migrations and rollback path are reviewed.
- [ ] Useful owner/status/deadline indexes exist.
- [ ] Pagination and allowlisted filtering are covered by tests.
- [ ] Stable error envelope is documented.
- [ ] OpenAPI matches implemented V1 routes and schemas.

## Automated evidence

- [ ] Backend formatting/static analysis/tests are green.
- [ ] Frontend lint/typecheck/tests/build are green.
- [ ] Integration tests use PostgreSQL, not a different database engine as a substitute.
- [ ] End-to-end demo proves register → create → update status → filter/dashboard → archive/restore → delete.
- [ ] Dependency audit and secret-hygiene checks are green.
- [ ] CI actions are pinned and permissions are least-privilege.

## Release evidence

- [ ] README and architecture/security/data/API docs match implementation.
- [ ] CHANGELOG and release notes are prepared.
- [ ] Exact release commit is green before tag creation.
- [ ] Tag/release points to that exact verified commit.
