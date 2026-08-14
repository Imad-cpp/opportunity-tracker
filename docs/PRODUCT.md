# Product Contract

## Problem

Opportunities are often scattered across browser tabs, messages, documents and memory. A saved link does not answer whether the opportunity is worth pursuing, when it closes, what has already been done, or what action should happen next.

## V1 user

One authenticated person tracking their own applications. V1 is not a team workspace and has no public profile surface.

## Core job

Capture an opportunity quickly, decide whether it deserves attention, move it through an application process and keep the next deadline or action visible.

## Opportunity types

- `JOB`
- `INTERNSHIP`
- `SCHOLARSHIP`
- `PROGRAM`
- `OTHER`

## Lifecycle statuses

- `SAVED`
- `PREPARING`
- `APPLIED`
- `INTERVIEWING`
- `OFFERED`
- `ACCEPTED`
- `REJECTED`
- `WITHDRAWN`
- `EXPIRED`

V1 does not impose a rigid universal transition graph because different opportunity types have different real-world processes. The owner may choose any valid status. Every change is recorded as an event, and the product never changes status silently.

## Priority

- `LOW`
- `MEDIUM`
- `HIGH`

Priority is user-defined. The system does not pretend to calculate opportunity quality.

## Required opportunity fields

- title
- organization
- type
- status
- priority

Optional fields include source URL, location, notes, deadline and next action.

## Deadline behavior

V1 preserves the difference between a date-only deadline and an exact date/time deadline.

- Date-only input is stored with precision `DATE` and interpreted as the end of that calendar day in the account time zone for due/overdue calculations.
- Exact date/time input is normalized to UTC for storage while preserving the time zone used for display and auditability.
- The API must never silently convert an unknown deadline time into a falsely precise timestamp without recording that the original precision was date-only.

## Dashboard questions

The dashboard should prioritize useful actions over vanity analytics:

- What is due in the next seven days?
- What active items are overdue?
- What has a next action due soon?
- How many active applications are in each status?
- What changed recently?

For dashboard purposes, the active pipeline is `SAVED`, `PREPARING`, `APPLIED`, `INTERVIEWING` and `OFFERED`; terminal outcomes are excluded from active counts. Opportunity-deadline urgency is limited to the pre-application `SAVED` and `PREPARING` states so a past application deadline does not make an already-applied record look overdue. Next actions may surface for any active state. Recent activity remains owner-scoped and may include archived opportunities so archive/restore history is still visible.

## Archive vs delete

Archive hides an opportunity from the default active workspace without destroying its history. Restore reverses archive.

Permanent delete removes the opportunity and its dependent user-facing event history. The UI must require explicit confirmation before issuing the delete request.

## V1 non-goals

- Aggregating opportunities from external websites.
- Scraping, crawling or browser automation.
- AI scoring, matching or generated application content.
- CV/resume/document storage.
- Email inbox synchronization.
- Public sharing or collaborative workspaces.
- Push/email/SMS notifications.
- Native mobile applications.
