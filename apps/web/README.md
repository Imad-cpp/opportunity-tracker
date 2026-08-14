# Opportunity Tracker Web

Next.js 16.3 + React 19 + TypeScript browser surface for the Opportunity Tracker monorepo.

## Boundary

The web app renders the authenticated product shell, action-first dashboard and opportunity workflows, and reads the Laravel API through the existing first-party session. Authentication authority, validation, owner scoping and domain behavior stay server-side in `apps/api`.

The browser does not store long-lived bearer tokens and does not calculate authoritative ownership, lifecycle state or deadline attention.

## Environment

```text
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
```

The root `compose.yaml` supplies this value for the local web container.

## Commands

```bash
npm ci
npm run lint
npm run typecheck
npm run build
npm run dev
```

Node.js 24 LTS is the supported V1 runtime line. Exact dependencies are committed in `package-lock.json` and production dependencies are blocked by the permanent high-severity npm audit gate.

## Current browser evidence

- first-party register, sign-in and sign-out flows;
- opportunity list, search, filters and pagination;
- create, edit, detail, status, archive, restore and permanent-delete flows;
- date-only/exact deadlines, next actions and plain-text notes;
- active pipeline counts, overdue and seven-day deadline attention;
- due-soon next actions and recent product activity;
- loading, empty, unauthenticated and generic error states;
- refresh and disabled behavior;
- responsive mobile/desktop layouts;
- visible keyboard focus and reduced-motion support;
- full Playwright register-through-delete browser E2E on the Docker application stack.
