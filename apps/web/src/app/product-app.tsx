"use client";

import { FormEvent, useCallback, useEffect, useMemo, useState } from "react";
import DashboardApp from "./dashboard";
import {
  ApiError,
  Account,
  DeadlinePrecision,
  Opportunity,
  OpportunityEvent,
  OpportunityFilters,
  OpportunityInput,
  OpportunityList,
  OpportunityPriority,
  OpportunityStatus,
  OpportunityType,
  archiveOpportunity,
  createOpportunity,
  deleteOpportunity,
  getMe,
  getOpportunity,
  getOpportunityEvents,
  listOpportunities,
  login,
  logout,
  register,
  restoreOpportunity,
  updateOpportunity,
  updateOpportunityStatus,
} from "@/lib/product-api";

const TYPES: OpportunityType[] = ["JOB", "INTERNSHIP", "SCHOLARSHIP", "PROGRAM", "OTHER"];
const PRIORITIES: OpportunityPriority[] = ["LOW", "MEDIUM", "HIGH"];
const STATUSES: OpportunityStatus[] = [
  "SAVED",
  "PREPARING",
  "APPLIED",
  "INTERVIEWING",
  "OFFERED",
  "ACCEPTED",
  "REJECTED",
  "WITHDRAWN",
  "EXPIRED",
];

const labels: Record<string, string> = {
  JOB: "Job",
  INTERNSHIP: "Internship",
  SCHOLARSHIP: "Scholarship",
  PROGRAM: "Program",
  OTHER: "Other",
  LOW: "Low",
  MEDIUM: "Medium",
  HIGH: "High",
  SAVED: "Saved",
  PREPARING: "Preparing",
  APPLIED: "Applied",
  INTERVIEWING: "Interviewing",
  OFFERED: "Offered",
  ACCEPTED: "Accepted",
  REJECTED: "Rejected",
  WITHDRAWN: "Withdrawn",
  EXPIRED: "Expired",
  CREATED: "Created",
  UPDATED: "Updated",
  STATUS_CHANGED: "Status changed",
  ARCHIVED: "Archived",
  RESTORED: "Restored",
};

function label(value: string): string {
  return labels[value] ?? value.replaceAll("_", " ").toLowerCase().replace(/^./, (letter) => letter.toUpperCase());
}

function browserTimeZone(): string {
  return Intl.DateTimeFormat().resolvedOptions().timeZone || "UTC";
}

function zonedInputValue(value: string | null, timeZone: string, includeTime: boolean): string {
  if (!value) return "";
  const formatter = new Intl.DateTimeFormat("en-CA", {
    timeZone,
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    ...(includeTime
      ? { hour: "2-digit", minute: "2-digit", hourCycle: "h23" as const }
      : {}),
  });
  const parts = Object.fromEntries(formatter.formatToParts(new Date(value)).map((part) => [part.type, part.value]));
  const date = `${parts.year}-${parts.month}-${parts.day}`;
  return includeTime ? `${date}T${parts.hour}:${parts.minute}` : date;
}

function displayDate(value: string | null, timeZone?: string | null): string {
  if (!value) return "Not set";
  return new Intl.DateTimeFormat(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    timeZone: timeZone || undefined,
    timeZoneName: "short",
  }).format(new Date(value));
}

function inputDateTimeToIso(value: string): string | null {
  if (!value) return null;
  const parsed = new Date(value);
  return Number.isNaN(parsed.getTime()) ? null : parsed.toISOString();
}

function firstFieldError(error: ApiError | null, field: string): string | null {
  return error?.details[field]?.[0] ?? null;
}

function FieldError({ error, id }: { error: string | null; id: string }) {
  if (!error) return null;
  return <span className="field-error" id={id}>{error}</span>;
}

type AuthMode = "login" | "register";

function AuthScreen({ onAuthenticated }: { onAuthenticated: (account: Account) => void }) {
  const [mode, setMode] = useState<AuthMode>("login");
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<ApiError | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      const account = mode === "login"
        ? await login(email, password)
        : await register({ name, email, password, timezone: browserTimeZone() });
      onAuthenticated(account);
    } catch (caught) {
      setError(caught instanceof ApiError ? caught : new ApiError(500, {}));
    } finally {
      setSubmitting(false);
    }
  }

  function changeMode(nextMode: AuthMode) {
    setMode(nextMode);
    setError(null);
    setPassword("");
  }

  const nameError = firstFieldError(error, "name");
  const emailError = firstFieldError(error, "email");
  const passwordError = firstFieldError(error, "password");

  return (
    <main className="auth-page">
      <section className="auth-story" aria-labelledby="auth-title">
        <div className="auth-brand">
          <span className="brand-mark" aria-hidden="true">OT</span>
          <strong>Opportunity Tracker</strong>
        </div>
        <div>
          <p className="hero-kicker">Private application workspace</p>
          <h1 id="auth-title">Turn saved opportunities into clear next actions.</h1>
          <p className="auth-lede">
            Track deadlines, application progress and follow-ups in one owner-scoped workspace built around what needs attention next.
          </p>
        </div>
        <ul className="auth-proof" aria-label="Workspace guarantees">
          <li>First-party session authentication</li>
          <li>No scraping or automatic status changes</li>
          <li>Your application data stays owner-scoped</li>
        </ul>
      </section>

      <section className="auth-card" aria-labelledby="auth-form-title">
        <div className="auth-tabs" role="tablist" aria-label="Account access">
          <button type="button" role="tab" aria-selected={mode === "login"} onClick={() => changeMode("login")}>Sign in</button>
          <button type="button" role="tab" aria-selected={mode === "register"} onClick={() => changeMode("register")}>Create account</button>
        </div>

        <div className="auth-heading">
          <p className="section-eyebrow">{mode === "login" ? "Welcome back" : "Start tracking"}</p>
          <h2 id="auth-form-title">{mode === "login" ? "Sign in to your workspace" : "Create your private workspace"}</h2>
        </div>

        {error ? (
          <div className="form-alert" role="alert">
            <strong>{error.code === "INVALID_CREDENTIALS" ? "Sign-in failed" : "Check the form"}</strong>
            <span>{error.message}</span>
          </div>
        ) : null}

        <form className="product-form" onSubmit={(event) => void submit(event)} noValidate>
          {mode === "register" ? (
            <label className="field">
              <span>Name</span>
              <input
                name="name"
                autoComplete="name"
                value={name}
                onChange={(event) => setName(event.target.value)}
                aria-invalid={Boolean(nameError)}
                aria-describedby={nameError ? "auth-name-error" : undefined}
                required
              />
              <FieldError id="auth-name-error" error={nameError} />
            </label>
          ) : null}

          <label className="field">
            <span>Email</span>
            <input
              name="email"
              type="email"
              autoComplete="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              aria-invalid={Boolean(emailError)}
              aria-describedby={emailError ? "auth-email-error" : undefined}
              required
            />
            <FieldError id="auth-email-error" error={emailError} />
          </label>

          <label className="field">
            <span>Password</span>
            <input
              name="password"
              type="password"
              autoComplete={mode === "login" ? "current-password" : "new-password"}
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              aria-invalid={Boolean(passwordError)}
              aria-describedby={passwordError ? "auth-password-error" : mode === "register" ? "password-help" : undefined}
              minLength={mode === "register" ? 12 : undefined}
              required
            />
            {mode === "register" ? <small id="password-help">12+ characters with mixed case, a number and a symbol.</small> : null}
            <FieldError id="auth-password-error" error={passwordError} />
          </label>

          <button className="primary-button product-submit" type="submit" disabled={submitting}>
            {submitting ? "Working…" : mode === "login" ? "Sign in" : "Create account"}
          </button>
        </form>
      </section>
    </main>
  );
}

type FormState = {
  type: OpportunityType;
  priority: OpportunityPriority;
  title: string;
  organization: string;
  sourceUrl: string;
  location: string;
  notes: string;
  nextAction: string;
  nextActionAt: string;
  deadlinePrecision: DeadlinePrecision | "";
  deadlineAt: string;
  deadlineTimezone: string;
};

function blankForm(timeZone: string): FormState {
  return {
    type: "INTERNSHIP",
    priority: "MEDIUM",
    title: "",
    organization: "",
    sourceUrl: "",
    location: "",
    notes: "",
    nextAction: "",
    nextActionAt: "",
    deadlinePrecision: "",
    deadlineAt: "",
    deadlineTimezone: timeZone,
  };
}

function formFromOpportunity(opportunity: Opportunity, accountTimeZone: string): FormState {
  const deadlineTimeZone = opportunity.deadline_timezone || accountTimeZone;
  return {
    type: opportunity.type,
    priority: opportunity.priority,
    title: opportunity.title,
    organization: opportunity.organization,
    sourceUrl: opportunity.source_url ?? "",
    location: opportunity.location ?? "",
    notes: opportunity.notes ?? "",
    nextAction: opportunity.next_action ?? "",
    nextActionAt: zonedInputValue(opportunity.next_action_at, accountTimeZone, true),
    deadlinePrecision: opportunity.deadline_precision ?? "",
    deadlineAt: opportunity.deadline_precision
      ? zonedInputValue(opportunity.deadline_at, deadlineTimeZone, opportunity.deadline_precision === "DATETIME")
      : "",
    deadlineTimezone: deadlineTimeZone,
  };
}

function toOpportunityInput(form: FormState): OpportunityInput {
  const deadlineAt = form.deadlineAt || null;
  return {
    type: form.type,
    priority: form.priority,
    title: form.title,
    organization: form.organization,
    source_url: form.sourceUrl.trim() || null,
    location: form.location.trim() || null,
    notes: form.notes.trim() || null,
    next_action: form.nextAction.trim() || null,
    next_action_at: inputDateTimeToIso(form.nextActionAt),
    deadline_at: deadlineAt,
    deadline_precision: deadlineAt ? form.deadlinePrecision || null : null,
    deadline_timezone: deadlineAt && form.deadlinePrecision === "DATETIME" ? form.deadlineTimezone : null,
  };
}

function OpportunityForm({
  account,
  opportunity,
  onSaved,
  onCancel,
}: {
  account: Account;
  opportunity?: Opportunity;
  onSaved: (opportunity: Opportunity) => void;
  onCancel: () => void;
}) {
  const [form, setForm] = useState<FormState>(() => opportunity ? formFromOpportunity(opportunity, account.timezone) : blankForm(account.timezone));
  const [error, setError] = useState<ApiError | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const editing = Boolean(opportunity);

  function set<K extends keyof FormState>(key: K, value: FormState[K]) {
    setForm((current) => ({ ...current, [key]: value }));
  }

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      const payload = toOpportunityInput(form);
      const saved = opportunity
        ? await updateOpportunity(opportunity.id, payload)
        : await createOpportunity(payload);
      onSaved(saved);
    } catch (caught) {
      setError(caught instanceof ApiError ? caught : new ApiError(500, {}));
    } finally {
      setSubmitting(false);
    }
  }

  const fieldError = (field: string) => firstFieldError(error, field);

  return (
    <section className="workspace-panel form-panel" aria-labelledby="opportunity-form-title">
      <div className="panel-heading">
        <div>
          <p className="section-eyebrow">{editing ? "Edit opportunity" : "Capture opportunity"}</p>
          <h2 id="opportunity-form-title">{editing ? opportunity?.title : "Add something worth tracking"}</h2>
        </div>
        <button className="secondary-button" type="button" onClick={onCancel}>Cancel</button>
      </div>

      {error ? <div className="form-alert" role="alert"><strong>Check the form</strong><span>{error.message}</span></div> : null}

      <form className="opportunity-form" onSubmit={(event) => void submit(event)} noValidate>
        <div className="form-grid form-grid--two">
          <label className="field">
            <span>Type</span>
            <select value={form.type} onChange={(event) => set("type", event.target.value as OpportunityType)}>
              {TYPES.map((type) => <option key={type} value={type}>{label(type)}</option>)}
            </select>
          </label>
          <label className="field">
            <span>Priority</span>
            <select value={form.priority} onChange={(event) => set("priority", event.target.value as OpportunityPriority)}>
              {PRIORITIES.map((priority) => <option key={priority} value={priority}>{label(priority)}</option>)}
            </select>
          </label>
        </div>

        <div className="form-grid form-grid--two">
          <label className="field">
            <span>Title</span>
            <input value={form.title} onChange={(event) => set("title", event.target.value)} required maxLength={200} aria-invalid={Boolean(fieldError("title"))} aria-describedby={fieldError("title") ? "title-error" : undefined} />
            <FieldError id="title-error" error={fieldError("title")} />
          </label>
          <label className="field">
            <span>Organization</span>
            <input value={form.organization} onChange={(event) => set("organization", event.target.value)} required maxLength={200} aria-invalid={Boolean(fieldError("organization"))} aria-describedby={fieldError("organization") ? "organization-error" : undefined} />
            <FieldError id="organization-error" error={fieldError("organization")} />
          </label>
        </div>

        <div className="form-grid form-grid--two">
          <label className="field">
            <span>Source URL</span>
            <input type="url" value={form.sourceUrl} onChange={(event) => set("sourceUrl", event.target.value)} placeholder="https://…" aria-invalid={Boolean(fieldError("source_url"))} aria-describedby={fieldError("source_url") ? "source-url-error" : undefined} />
            <FieldError id="source-url-error" error={fieldError("source_url")} />
          </label>
          <label className="field">
            <span>Location</span>
            <input value={form.location} onChange={(event) => set("location", event.target.value)} maxLength={200} aria-invalid={Boolean(fieldError("location"))} aria-describedby={fieldError("location") ? "location-error" : undefined} />
            <FieldError id="location-error" error={fieldError("location")} />
          </label>
        </div>

        <fieldset className="deadline-fieldset">
          <legend>Deadline</legend>
          <div className="form-grid form-grid--three">
            <label className="field">
              <span>Precision</span>
              <select
                value={form.deadlinePrecision}
                onChange={(event) => {
                  const precision = event.target.value as DeadlinePrecision | "";
                  setForm((current) => ({
                    ...current,
                    deadlinePrecision: precision,
                    deadlineAt: "",
                    deadlineTimezone: precision === "DATETIME" ? current.deadlineTimezone || account.timezone : account.timezone,
                  }));
                }}
              >
                <option value="">No deadline</option>
                <option value="DATE">Date only</option>
                <option value="DATETIME">Exact date & time</option>
              </select>
            </label>
            {form.deadlinePrecision ? (
              <label className="field">
                <span>{form.deadlinePrecision === "DATE" ? "Date" : "Date & time"}</span>
                <input
                  type={form.deadlinePrecision === "DATE" ? "date" : "datetime-local"}
                  value={form.deadlineAt}
                  onChange={(event) => set("deadlineAt", event.target.value)}
                  aria-invalid={Boolean(fieldError("deadline_at"))}
                  aria-describedby={fieldError("deadline_at") ? "deadline-error" : undefined}
                />
                <FieldError id="deadline-error" error={fieldError("deadline_at")} />
              </label>
            ) : <div />}
            {form.deadlinePrecision === "DATETIME" ? (
              <label className="field">
                <span>Time zone</span>
                <input value={form.deadlineTimezone} onChange={(event) => set("deadlineTimezone", event.target.value)} aria-invalid={Boolean(fieldError("deadline_timezone"))} aria-describedby={fieldError("deadline_timezone") ? "deadline-timezone-error" : undefined} />
                <FieldError id="deadline-timezone-error" error={fieldError("deadline_timezone")} />
              </label>
            ) : <div />}
          </div>
          {form.deadlinePrecision === "DATE" ? <small>Date-only deadlines use your account time zone: {account.timezone}.</small> : null}
        </fieldset>

        <div className="form-grid form-grid--two">
          <label className="field">
            <span>Next action</span>
            <input value={form.nextAction} onChange={(event) => set("nextAction", event.target.value)} maxLength={500} placeholder="e.g. Finish portfolio review" aria-invalid={Boolean(fieldError("next_action"))} aria-describedby={fieldError("next_action") ? "next-action-error" : undefined} />
            <FieldError id="next-action-error" error={fieldError("next_action")} />
          </label>
          <label className="field">
            <span>Next action date</span>
            <input type="datetime-local" value={form.nextActionAt} onChange={(event) => set("nextActionAt", event.target.value)} aria-invalid={Boolean(fieldError("next_action_at"))} aria-describedby={fieldError("next_action_at") ? "next-action-at-error" : undefined} />
            <FieldError id="next-action-at-error" error={fieldError("next_action_at")} />
          </label>
        </div>

        <label className="field">
          <span>Notes</span>
          <textarea value={form.notes} onChange={(event) => set("notes", event.target.value)} rows={6} maxLength={10000} aria-invalid={Boolean(fieldError("notes"))} aria-describedby={fieldError("notes") ? "notes-error" : "notes-help"} />
          <small id="notes-help">Plain text only. Notes are never rendered as HTML.</small>
          <FieldError id="notes-error" error={fieldError("notes")} />
        </label>

        <div className="form-actions">
          <button className="primary-button" type="submit" disabled={submitting}>{submitting ? "Saving…" : editing ? "Save changes" : "Add opportunity"}</button>
          <button className="secondary-button" type="button" onClick={onCancel} disabled={submitting}>Cancel</button>
        </div>
      </form>
    </section>
  );
}

function ActivityTimeline({ events }: { events: OpportunityEvent[] }) {
  if (!events.length) return <p className="empty-state">No activity has been recorded yet.</p>;
  return (
    <ol className="detail-timeline">
      {events.map((event) => (
        <li key={event.id}>
          <span className="activity-dot" aria-hidden="true" />
          <div>
            <strong>{label(event.type)}</strong>
            {event.type === "STATUS_CHANGED" && event.from_status && event.to_status ? <p>{label(event.from_status)} → {label(event.to_status)}</p> : null}
            {event.type === "UPDATED" && event.changed_fields?.length ? <p>{event.changed_fields.map(label).join(", ")}</p> : null}
          </div>
          <time dateTime={event.created_at ?? undefined}>{displayDate(event.created_at)}</time>
        </li>
      ))}
    </ol>
  );
}

function OpportunityDetail({
  account,
  id,
  onBack,
  onChanged,
  onDeleted,
  onSessionExpired,
}: {
  account: Account;
  id: string;
  onBack: () => void;
  onChanged: () => void;
  onDeleted: () => void;
  onSessionExpired: () => void;
}) {
  const [opportunity, setOpportunity] = useState<Opportunity | null>(null);
  const [events, setEvents] = useState<OpportunityEvent[] | null>(null);
  const [error, setError] = useState<ApiError | null>(null);
  const [editing, setEditing] = useState(false);
  const [status, setStatus] = useState<OpportunityStatus>("SAVED");
  const [mutating, setMutating] = useState(false);
  const [confirmDelete, setConfirmDelete] = useState(false);

  const reload = useCallback(async () => {
    try {
      const [item, history] = await Promise.all([getOpportunity(id), getOpportunityEvents(id)]);
      setOpportunity(item);
      setStatus(item.status);
      setEvents(history);
      setError(null);
    } catch (caught) {
      const apiError = caught instanceof ApiError ? caught : new ApiError(500, {});
      if (apiError.status === 401) onSessionExpired();
      else setError(apiError);
    }
  }, [id, onSessionExpired]);

  useEffect(() => {
    const controller = new AbortController();
    Promise.all([getOpportunity(id, controller.signal), getOpportunityEvents(id, controller.signal)])
      .then(([item, history]) => {
        setOpportunity(item);
        setStatus(item.status);
        setEvents(history);
      })
      .catch((caught) => {
        if (caught instanceof DOMException && caught.name === "AbortError") return;
        const apiError = caught instanceof ApiError ? caught : new ApiError(500, {});
        if (apiError.status === 401) onSessionExpired();
        else setError(apiError);
      });
    return () => controller.abort();
  }, [id, onSessionExpired]);

  async function mutate(action: () => Promise<Opportunity>) {
    setMutating(true);
    setError(null);
    try {
      const item = await action();
      setOpportunity(item);
      setStatus(item.status);
      await reload();
      onChanged();
    } catch (caught) {
      const apiError = caught instanceof ApiError ? caught : new ApiError(500, {});
      if (apiError.status === 401) onSessionExpired();
      else setError(apiError);
    } finally {
      setMutating(false);
    }
  }

  async function remove() {
    setMutating(true);
    setError(null);
    try {
      await deleteOpportunity(id);
      onDeleted();
    } catch (caught) {
      const apiError = caught instanceof ApiError ? caught : new ApiError(500, {});
      if (apiError.status === 401) onSessionExpired();
      else setError(apiError);
      setMutating(false);
    }
  }

  if (error && !opportunity) {
    return <section className="workspace-panel state-card"><p className="section-eyebrow">Unable to open</p><h2>{error.message}</h2><button className="secondary-button" type="button" onClick={onBack}>Back to opportunities</button></section>;
  }
  if (!opportunity || !events) return <section className="workspace-panel loading-panel" aria-busy="true">Loading opportunity…</section>;

  if (editing) {
    return <OpportunityForm account={account} opportunity={opportunity} onCancel={() => setEditing(false)} onSaved={(saved) => { setOpportunity(saved); setEditing(false); void reload(); onChanged(); }} />;
  }

  return (
    <section className="workspace-panel detail-panel" aria-labelledby="detail-title">
      <div className="panel-heading detail-heading">
        <div>
          <button className="text-button" type="button" onClick={onBack}>← Opportunities</button>
          <div className="detail-badges">
            <span className={`priority priority--${opportunity.priority.toLowerCase()}`}>{label(opportunity.priority)}</span>
            <span className="status-chip">{label(opportunity.status)}</span>
            {opportunity.archived_at ? <span className="status-chip status-chip--muted">Archived</span> : null}
          </div>
          <h2 id="detail-title">{opportunity.title}</h2>
          <p>{opportunity.organization}</p>
        </div>
        <button className="secondary-button" type="button" onClick={() => setEditing(true)}>Edit</button>
      </div>

      {error ? <div className="form-alert" role="alert"><strong>Action failed</strong><span>{error.message}</span></div> : null}

      <div className="detail-grid">
        <article className="detail-card">
          <span>Type</span><strong>{label(opportunity.type)}</strong>
        </article>
        <article className="detail-card">
          <span>Location</span><strong>{opportunity.location || "Not set"}</strong>
        </article>
        <article className="detail-card">
          <span>Deadline</span><strong>{displayDate(opportunity.deadline_at, opportunity.deadline_timezone)}</strong>
        </article>
        <article className="detail-card">
          <span>Next action</span><strong>{opportunity.next_action || "Not set"}</strong>
          {opportunity.next_action_at ? <small>{displayDate(opportunity.next_action_at, account.timezone)}</small> : null}
        </article>
      </div>

      {opportunity.source_url ? <p className="source-link"><a href={opportunity.source_url} target="_blank" rel="noreferrer">Open original source ↗</a></p> : null}

      <section className="detail-section" aria-labelledby="notes-title">
        <div className="section-heading"><div><p className="section-eyebrow">Context</p><h3 id="notes-title">Notes</h3></div></div>
        <p className="plain-notes">{opportunity.notes || "No notes yet."}</p>
      </section>

      <section className="detail-section" aria-labelledby="workflow-title">
        <div className="section-heading"><div><p className="section-eyebrow">Workflow</p><h3 id="workflow-title">Application status</h3></div></div>
        <div className="status-control">
          <label className="field"><span>Status</span><select value={status} onChange={(event) => setStatus(event.target.value as OpportunityStatus)}>{STATUSES.map((item) => <option key={item} value={item}>{label(item)}</option>)}</select></label>
          <button className="primary-button" type="button" disabled={mutating || status === opportunity.status} onClick={() => void mutate(() => updateOpportunityStatus(id, status))}>Update status</button>
        </div>
      </section>

      <section className="detail-section" aria-labelledby="history-title">
        <div className="section-heading"><div><p className="section-eyebrow">History</p><h3 id="history-title">Recent activity</h3></div></div>
        <ActivityTimeline events={events} />
      </section>

      <section className="detail-section danger-zone" aria-labelledby="record-actions-title">
        <div className="section-heading"><div><p className="section-eyebrow">Record actions</p><h3 id="record-actions-title">Archive or delete</h3></div></div>
        <div className="record-actions">
          {opportunity.archived_at
            ? <button className="secondary-button" type="button" disabled={mutating} onClick={() => void mutate(() => restoreOpportunity(id))}>Restore opportunity</button>
            : <button className="secondary-button" type="button" disabled={mutating} onClick={() => void mutate(() => archiveOpportunity(id))}>Archive opportunity</button>}
          {!confirmDelete ? (
            <button className="danger-button" type="button" disabled={mutating} onClick={() => setConfirmDelete(true)}>Delete permanently</button>
          ) : (
            <div className="delete-confirm" role="alert">
              <span>This permanently removes the opportunity and its history.</span>
              <button className="danger-button" type="button" disabled={mutating} onClick={() => void remove()}>Confirm delete</button>
              <button className="text-button" type="button" onClick={() => setConfirmDelete(false)}>Cancel</button>
            </div>
          )}
        </div>
      </section>
    </section>
  );
}

function OpportunityRow({ opportunity, onOpen }: { opportunity: Opportunity; onOpen: () => void }) {
  return (
    <article className="tracker-row">
      <button className="tracker-row__open" type="button" onClick={onOpen}>
        <span className="tracker-row__identity"><strong>{opportunity.title}</strong><span>{opportunity.organization}</span></span>
        <span className="tracker-row__meta"><span>{label(opportunity.type)}</span><span className={`priority priority--${opportunity.priority.toLowerCase()}`}>{label(opportunity.priority)}</span><span>{label(opportunity.status)}</span></span>
        <span className="tracker-row__action"><small>{opportunity.next_action || "No next action"}</small>{opportunity.next_action_at ? <time dateTime={opportunity.next_action_at}>{displayDate(opportunity.next_action_at)}</time> : null}</span>
        <span className="tracker-row__deadline"><small>Deadline</small><strong>{opportunity.deadline_at ? displayDate(opportunity.deadline_at, opportunity.deadline_timezone) : "Not set"}</strong></span>
        <span aria-hidden="true">→</span>
      </button>
    </article>
  );
}

function OpportunitiesWorkspace({ account, onSessionExpired }: { account: Account; onSessionExpired: () => void }) {
  const [draftFilters, setDraftFilters] = useState<OpportunityFilters>({});
  const [filters, setFilters] = useState<OpportunityFilters>({});
  const [list, setList] = useState<OpportunityList | null>(null);
  const [error, setError] = useState<ApiError | null>(null);
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [creating, setCreating] = useState(false);
  const [reloadVersion, setReloadVersion] = useState(0);

  const reloadList = useCallback(() => {
    setList(null);
    setReloadVersion((version) => version + 1);
  }, []);

  useEffect(() => {
    const controller = new AbortController();
    listOpportunities(filters, controller.signal)
      .then((result) => {
        setList(result);
        setError(null);
      })
      .catch((caught) => {
        if (caught instanceof DOMException && caught.name === "AbortError") return;
        const apiError = caught instanceof ApiError ? caught : new ApiError(500, {});
        if (apiError.status === 401) onSessionExpired();
        else setError(apiError);
      });
    return () => controller.abort();
  }, [filters, onSessionExpired, reloadVersion]);

  if (creating) {
    return <OpportunityForm account={account} onCancel={() => setCreating(false)} onSaved={(saved) => { setCreating(false); setSelectedId(saved.id); reloadList(); }} />;
  }

  if (selectedId) {
    return <OpportunityDetail account={account} id={selectedId} onBack={() => setSelectedId(null)} onChanged={reloadList} onDeleted={() => { setSelectedId(null); reloadList(); }} onSessionExpired={onSessionExpired} />;
  }

  function applyFilters(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setList(null);
    setFilters({ ...draftFilters, page: 1 });
  }

  function clearFilters() {
    setDraftFilters({});
    setList(null);
    setFilters({});
  }

  const hasFilters = Boolean(filters.q || filters.status || filters.type || filters.priority || filters.archived);

  return (
    <section className="tracker-workspace" aria-labelledby="opportunities-title">
      <div className="workspace-titlebar">
        <div><p className="section-eyebrow">Application pipeline</p><h1 id="opportunities-title">Opportunities</h1><p>Search what you saved, keep the next action visible and move each application deliberately.</p></div>
        <button className="primary-button" type="button" onClick={() => setCreating(true)}>+ Add opportunity</button>
      </div>

      <form className="filter-bar" onSubmit={applyFilters}>
        <label className="filter-search"><span className="sr-only">Search title or organization</span><input type="search" placeholder="Search title or organization" value={draftFilters.q ?? ""} onChange={(event) => setDraftFilters((current) => ({ ...current, q: event.target.value }))} /></label>
        <label><span className="sr-only">Status</span><select value={draftFilters.status ?? ""} onChange={(event) => setDraftFilters((current) => ({ ...current, status: event.target.value as OpportunityStatus | "" }))}><option value="">All statuses</option>{STATUSES.map((status) => <option key={status} value={status}>{label(status)}</option>)}</select></label>
        <label><span className="sr-only">Type</span><select value={draftFilters.type ?? ""} onChange={(event) => setDraftFilters((current) => ({ ...current, type: event.target.value as OpportunityType | "" }))}><option value="">All types</option>{TYPES.map((type) => <option key={type} value={type}>{label(type)}</option>)}</select></label>
        <label><span className="sr-only">Priority</span><select value={draftFilters.priority ?? ""} onChange={(event) => setDraftFilters((current) => ({ ...current, priority: event.target.value as OpportunityPriority | "" }))}><option value="">All priorities</option>{PRIORITIES.map((priority) => <option key={priority} value={priority}>{label(priority)}</option>)}</select></label>
        <label className="archive-toggle"><input type="checkbox" checked={Boolean(draftFilters.archived)} onChange={(event) => setDraftFilters((current) => ({ ...current, archived: event.target.checked }))} /><span>Archived</span></label>
        <button className="secondary-button" type="submit">Apply</button>
        {hasFilters ? <button className="text-button" type="button" onClick={clearFilters}>Clear</button> : null}
      </form>

      {error ? <div className="form-alert" role="alert"><strong>Could not load opportunities</strong><span>{error.message}</span></div> : null}

      <div className="tracker-table-heading" aria-hidden="true"><span>Opportunity</span><span>Stage</span><span>Next action</span><span>Deadline</span><span /></div>
      {!list ? <div className="loading-panel" aria-busy="true">Loading opportunities…</div> : list.data.length ? (
        <div className="tracker-list">
          {list.data.map((opportunity) => <OpportunityRow key={opportunity.id} opportunity={opportunity} onOpen={() => setSelectedId(opportunity.id)} />)}
        </div>
      ) : (
        <div className="workspace-empty">
          <p className="section-eyebrow">{hasFilters ? "No matches" : "Empty pipeline"}</p>
          <h2>{hasFilters ? "Nothing matches these filters." : "Capture your first opportunity."}</h2>
          <p>{hasFilters ? "Try clearing a filter or changing your search." : "Start with the opportunity you are most likely to forget or miss a deadline for."}</p>
          {!hasFilters ? <button className="primary-button" type="button" onClick={() => setCreating(true)}>Add opportunity</button> : <button className="secondary-button" type="button" onClick={clearFilters}>Clear filters</button>}
        </div>
      )}

      {list && list.meta.last_page > 1 ? (
        <nav className="pagination" aria-label="Opportunity pages">
          <button className="secondary-button" type="button" disabled={list.meta.current_page <= 1} onClick={() => { setList(null); setFilters((current) => ({ ...current, page: list.meta.current_page - 1 })); }}>Previous</button>
          <span>Page {list.meta.current_page} of {list.meta.last_page} · {list.meta.total} total</span>
          <button className="secondary-button" type="button" disabled={list.meta.current_page >= list.meta.last_page} onClick={() => { setList(null); setFilters((current) => ({ ...current, page: list.meta.current_page + 1 })); }}>Next</button>
        </nav>
      ) : null}
    </section>
  );
}

type ProductSection = "dashboard" | "opportunities";

function SignedInApp({ account, onSignedOut }: { account: Account; onSignedOut: () => void }) {
  const [section, setSection] = useState<ProductSection>("dashboard");
  const [signingOut, setSigningOut] = useState(false);

  const expireSession = useCallback(() => onSignedOut(), [onSignedOut]);

  async function signOut() {
    setSigningOut(true);
    try {
      await logout();
    } finally {
      onSignedOut();
    }
  }

  return (
    <div className="product-app">
      <header className="product-topbar">
        <button className="product-brand" type="button" onClick={() => setSection("dashboard")} aria-label="Opportunity Tracker dashboard">
          <span className="brand-mark" aria-hidden="true">OT</span>
          <span><strong>Opportunity Tracker</strong><small>Application workspace</small></span>
        </button>
        <nav className="product-nav" aria-label="Workspace">
          <button type="button" aria-current={section === "dashboard" ? "page" : undefined} onClick={() => setSection("dashboard")}>Dashboard</button>
          <button type="button" aria-current={section === "opportunities" ? "page" : undefined} onClick={() => setSection("opportunities")}>Opportunities</button>
        </nav>
        <div className="account-menu">
          <span><strong>{account.name}</strong><small>{account.email}</small></span>
          <button className="secondary-button" type="button" disabled={signingOut} onClick={() => void signOut()}>{signingOut ? "Signing out…" : "Sign out"}</button>
        </div>
      </header>
      {section === "dashboard"
        ? <DashboardApp embedded onUnauthenticated={expireSession} />
        : <OpportunitiesWorkspace account={account} onSessionExpired={expireSession} />}
    </div>
  );
}

export default function ProductApp() {
  const [account, setAccount] = useState<Account | null | undefined>(undefined);
  const [bootError, setBootError] = useState<ApiError | null>(null);

  useEffect(() => {
    const controller = new AbortController();
    getMe(controller.signal)
      .then((current) => setAccount(current))
      .catch((caught) => {
        if (caught instanceof DOMException && caught.name === "AbortError") return;
        const apiError = caught instanceof ApiError ? caught : new ApiError(500, {});
        if (apiError.status === 401) setAccount(null);
        else setBootError(apiError);
      });
    return () => controller.abort();
  }, []);

  const signedOut = useCallback(() => {
    setAccount(null);
    setBootError(null);
  }, []);

  if (bootError) {
    return (
      <main className="auth-page auth-page--single">
        <section className="auth-card state-card" role="alert">
          <p className="section-eyebrow">Workspace unavailable</p>
          <h1>We could not check your session.</h1>
          <p>{bootError.message}</p>
          <button className="primary-button" type="button" onClick={() => window.location.reload()}>Try again</button>
        </section>
      </main>
    );
  }

  if (account === undefined) {
    return <main className="auth-page auth-page--single" aria-busy="true"><div className="loading-shell"><span className="loading-mark" aria-hidden="true" /><p>Opening your private workspace…</p></div></main>;
  }

  if (account === null) return <AuthScreen onAuthenticated={setAccount} />;
  return <SignedInApp account={account} onSignedOut={signedOut} />;
}
