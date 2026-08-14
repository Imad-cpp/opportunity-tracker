# Opportunity Tracker Web

Next.js 16.2 + React 19 + TypeScript browser surface for the Opportunity Tracker monorepo.

## Boundary

The web app renders the action-first dashboard and reads the Laravel API through the existing first-party session. Authentication authority, validation, owner scoping and domain behavior stay server-side in `apps/api`.

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

Node.js 24 LTS is the supported V1 runtime line. Exact dependencies are committed in `package-lock.json`.

## Current dashboard evidence

- active pipeline counts;
- overdue and seven-day deadline attention;
- due-soon next actions;
- recent product activity;
- loading, empty, unauthenticated and generic error states;
- refresh and disabled behavior;
- responsive mobile/desktop layouts;
- visible keyboard focus and reduced-motion support.

Product-level frontend tests and the complete browser authentication/capture/edit flow remain tracked in the root Definition of Done.
