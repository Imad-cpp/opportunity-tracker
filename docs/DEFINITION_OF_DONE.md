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
- [x] Next action is visible in list/detail/dashboard where relevant.
  - Browser E2E verifies the saved next action in detail and the dashboard next-actions region; the opportunity list also presents the next action when present.
- [x] Date-only and exact-time deadline semantics are tested.

## Security and privacy

- [x] Every implemented opportunity resource is server-side owner scoped.
- [x] Foreign opportunity UUIDs return `404` on reads and mutations, including history reads and workflow changes.
- [x] Authentication endpoints are rate limited.
- [x] Session/CSRF behavior is tested at the browser boundary.
  - Session rotation/logout invalidation are covered at the application boundary, and running-stack CI verifies Sanctum CSRF bootstrap, trusted credentialed CORS, 419 rejection without the XSRF header and the stable `CSRF_TOKEN_MISMATCH` JSON envelope.
- [x] CORS does not allow an unconfigured origin to become an allowed origin.
- [x] URL scheme validation rejects non-HTTP(S) values.
- [x] User-authored notes are rendered as text, not HTML.
  - Browser E2E stores literal HTML/script text, verifies the exact rendered text and verifies that no script element is created beneath the notes surface.
- [x] Logs/tests/demos contain no secrets or real personal application data.
  - Automated fixtures and browser E2E use synthetic account/application data, no E2E screenshots or videos are persisted by default, and full Git history passes the Gitleaks gate.

## Frontend

- [x] Responsive mobile and desktop layouts.
- [x] Keyboard navigation and visible focus states.
- [x] Loading, empty, error, success and disabled states.
- [x] Forms expose validation errors accessibly.
  - Authentication and opportunity forms expose a `role="alert"` summary and associate field errors through `aria-invalid` and `aria-describedby`.
- [x] No core workflow depends on hover only.

## API and data

- [x] Database migrations and rollback path are reviewed for the full V1 schema.
  - Permanent CI applies PostgreSQL migrations, rolls back the full current batch, reapplies it and verifies migration status.
- [x] Useful owner/status/deadline/event indexes exist on the implemented persistence boundary.
- [x] Pagination and allowlisted filtering are covered by tests.
- [x] Stable identity/resource error envelopes are documented and exercised.
- [x] Workflow mutations and corresponding event writes are transactionally coupled.
- [x] Event history is owner scoped and delete cascades dependent product events.
- [x] OpenAPI matches implemented V1 routes and schemas.
  - OpenAPI 3.1 is committed and contract CI checks route coverage, private/public security requirements, references and critical enums.

## Automated evidence

- [x] Backend formatting/static analysis/tests are green.
  - Formatting, PostgreSQL-backed tests and PHPStan level 1 are permanent CI gates; PHPStan runs from an isolated pinned toolchain without changing the application lockfile.
- [x] Frontend lint/typecheck/tests/build are green.
  - Locked frontend lint, typecheck and build run in Application Quality, while the Playwright workflow exercises the complete browser product journey against the Docker stack.
- [x] Integration tests use PostgreSQL, not a different database engine as a substitute.
- [x] End-to-end demo proves register → create → update status → filter/dashboard → archive/restore → delete.
  - The Playwright V1 workflow performs this journey through the browser without API shortcuts and uses synthetic data.
- [x] Dependency audit and secret-hygiene checks are green.
  - Composer audit, unconditional `npm audit --omit=dev --audit-level=high`, and full-history Gitleaks scanning are permanent CI gates.
- [x] CI actions are pinned and permissions are least-privilege for the permanent workflow.

## Release evidence

- [x] README and architecture/security/data/API docs match the complete V1 implementation.
  - Product/browser, runtime, API, security, decision, roadmap and Definition-of-Done documentation are synchronized through the browser V1 and dependency-maintenance slices.
- [ ] CHANGELOG and release notes are prepared.
  - CHANGELOG is current; dedicated release notes remain to be prepared for the tag.
- [ ] Exact release commit is green before tag creation.
- [ ] Tag/release points to that exact verified commit.
