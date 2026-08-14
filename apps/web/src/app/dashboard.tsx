"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import {
  DashboardActivity,
  DashboardOpportunity,
  DashboardRequestError,
  DashboardSummary,
  readDashboard,
} from "@/lib/dashboard-api";

const pipeline = ["SAVED", "PREPARING", "APPLIED", "INTERVIEWING", "OFFERED"] as const;

const labels: Record<string, string> = {
  SAVED: "Saved",
  PREPARING: "Preparing",
  APPLIED: "Applied",
  INTERVIEWING: "Interviewing",
  OFFERED: "Offered",
  JOB: "Job",
  INTERNSHIP: "Internship",
  SCHOLARSHIP: "Scholarship",
  PROGRAM: "Program",
  OTHER: "Other",
  CREATED: "Created",
  UPDATED: "Updated",
  STATUS_CHANGED: "Status changed",
  ARCHIVED: "Archived",
  RESTORED: "Restored",
};

function readable(value: string): string {
  return labels[value] ?? value.toLowerCase().replaceAll("_", " ").replace(/^./, (letter) => letter.toUpperCase());
}

function formatMoment(value: string | null, timeZone?: string | null): string {
  if (!value) return "No date";

  return new Intl.DateTimeFormat(undefined, {
    dateStyle: "medium",
    timeStyle: "short",
    timeZone: timeZone || undefined,
    timeZoneName: "short",
  }).format(new Date(value));
}

function formatDeadline(opportunity: DashboardOpportunity): string {
  if (!opportunity.deadline_at) return "No deadline";

  if (opportunity.deadline_precision === "DATE") {
    return new Intl.DateTimeFormat(undefined, {
      dateStyle: "medium",
      timeZone: opportunity.deadline_timezone || undefined,
    }).format(new Date(opportunity.deadline_at));
  }

  return formatMoment(opportunity.deadline_at, opportunity.deadline_timezone);
}

function OpportunityRow({ opportunity, mode }: { opportunity: DashboardOpportunity; mode: "deadline" | "action" }) {
  const schedule = mode === "deadline" ? formatDeadline(opportunity) : formatMoment(opportunity.next_action_at);

  return (
    <article className="opportunity-row">
      <div className="opportunity-row__main">
        <div className="opportunity-row__meta">
          <span className={`priority priority--${opportunity.priority.toLowerCase()}`}>{readable(opportunity.priority)}</span>
          <span>{readable(opportunity.type)}</span>
          <span>{readable(opportunity.status)}</span>
        </div>
        <h3>{opportunity.title}</h3>
        <p>{opportunity.organization}</p>
        {mode === "action" && opportunity.next_action ? (
          <p className="next-action-copy">{opportunity.next_action}</p>
        ) : null}
      </div>
      <time dateTime={mode === "deadline" ? opportunity.deadline_at ?? undefined : opportunity.next_action_at ?? undefined}>
        {schedule}
      </time>
    </article>
  );
}

function AttentionPanel({
  title,
  eyebrow,
  items,
  empty,
  tone,
}: {
  title: string;
  eyebrow: string;
  items: DashboardOpportunity[];
  empty: string;
  tone: "danger" | "warning";
}) {
  return (
    <section className={`dashboard-card attention-card attention-card--${tone}`} aria-labelledby={`${tone}-title`}>
      <div className="section-heading">
        <div>
          <p className="section-eyebrow">{eyebrow}</p>
          <h2 id={`${tone}-title`}>{title}</h2>
        </div>
        <span className="count-badge" aria-label={`${items.length} items`}>
          {items.length}
        </span>
      </div>
      {items.length ? (
        <div className="row-list">
          {items.map((opportunity) => (
            <OpportunityRow key={opportunity.id} opportunity={opportunity} mode="deadline" />
          ))}
        </div>
      ) : (
        <p className="empty-state">{empty}</p>
      )}
    </section>
  );
}

function activityDescription(activity: DashboardActivity): string {
  if (activity.type === "STATUS_CHANGED" && activity.from_status && activity.to_status) {
    return `${readable(activity.from_status)} → ${readable(activity.to_status)}`;
  }

  if (activity.type === "UPDATED" && activity.changed_fields?.length) {
    return `Updated ${activity.changed_fields.map(readable).join(", ")}`;
  }

  return readable(activity.type);
}

function ActivityList({ items }: { items: DashboardActivity[] }) {
  if (!items.length) return <p className="empty-state">No activity yet. Meaningful workflow changes will appear here.</p>;

  return (
    <ol className="activity-list">
      {items.map((activity) => (
        <li key={activity.id}>
          <span className="activity-dot" aria-hidden="true" />
          <div>
            <div className="activity-line">
              <strong>{activity.opportunity.title}</strong>
              <span>{activityDescription(activity)}</span>
            </div>
            <p>{activity.opportunity.organization}</p>
          </div>
          <time dateTime={activity.created_at ?? undefined}>{formatMoment(activity.created_at)}</time>
        </li>
      ))}
    </ol>
  );
}

function DashboardLoading() {
  return (
    <main className="workspace" aria-busy="true" aria-live="polite">
      <div className="loading-shell">
        <span className="loading-mark" />
        <p>Loading your application workspace…</p>
      </div>
    </main>
  );
}

function DashboardFailure({ error, retry }: { error: DashboardRequestError | Error; retry: () => void }) {
  const unauthenticated = error instanceof DashboardRequestError && error.status === 401;

  return (
    <main className="workspace workspace--centered">
      <section className="state-card" role="alert">
        <p className="section-eyebrow">{unauthenticated ? "Session required" : "Dashboard unavailable"}</p>
        <h1>{unauthenticated ? "Your workspace is private." : "We could not load the workspace."}</h1>
        <p>
          {unauthenticated
            ? "An authenticated first-party session is required before the dashboard can read your opportunities."
            : error.message}
        </p>
        <button className="primary-button" type="button" onClick={retry}>
          Try again
        </button>
      </section>
    </main>
  );
}

export default function DashboardApp() {
  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [error, setError] = useState<DashboardRequestError | Error | null>(null);
  const [refreshing, setRefreshing] = useState(false);

  const load = useCallback(async () => {
    setError(null);

    try {
      setSummary(await readDashboard());
    } catch (caught) {
      setError(caught instanceof Error ? caught : new Error("The dashboard could not be loaded."));
    }
  }, []);

  useEffect(() => {
    const controller = new AbortController();

    readDashboard(controller.signal)
      .then((data) => setSummary(data))
      .catch((caught) => {
        if (caught instanceof DOMException && caught.name === "AbortError") return;
        setError(caught instanceof Error ? caught : new Error("The dashboard could not be loaded."));
      });

    return () => controller.abort();
  }, []);

  const refresh = useCallback(async () => {
    setRefreshing(true);
    await load();
    setRefreshing(false);
  }, [load]);

  const activeTotal = useMemo(
    () => (summary ? pipeline.reduce((total, status) => total + summary.status_counts[status], 0) : 0),
    [summary],
  );

  if (error && !summary) return <DashboardFailure error={error} retry={() => void load()} />;
  if (!summary) return <DashboardLoading />;

  return (
    <main className="workspace">
      <header className="app-header">
        <div className="brand-lockup">
          <span className="brand-mark" aria-hidden="true">OT</span>
          <div>
            <strong>Opportunity Tracker</strong>
            <span>Personal application workspace</span>
          </div>
        </div>
        <button className="secondary-button" type="button" onClick={() => void refresh()} disabled={refreshing}>
          {refreshing ? "Refreshing…" : "Refresh"}
        </button>
      </header>

      <section className="dashboard-hero" aria-labelledby="dashboard-title">
        <div>
          <p className="hero-kicker">Action-first dashboard</p>
          <h1 id="dashboard-title">Know what needs your attention next.</h1>
          <p>
            Deadlines, next actions and recent progress—without turning your application pipeline into vanity analytics.
          </p>
        </div>
        <div className="active-total" aria-label={`${activeTotal} active opportunities`}>
          <span>{activeTotal}</span>
          <small>active opportunities</small>
        </div>
      </section>

      {error ? (
        <div className="inline-alert" role="status">
          <span>{error.message}</span>
          <button type="button" onClick={() => void refresh()}>Retry</button>
        </div>
      ) : null}

      <section className="pipeline-section" aria-labelledby="pipeline-title">
        <div className="section-heading section-heading--outside">
          <div>
            <p className="section-eyebrow">Pipeline</p>
            <h2 id="pipeline-title">Active status</h2>
          </div>
          <p>Only open application states are counted.</p>
        </div>
        <div className="pipeline-grid">
          {pipeline.map((status) => (
            <article className="metric-card" key={status}>
              <span>{readable(status)}</span>
              <strong>{summary.status_counts[status]}</strong>
            </article>
          ))}
        </div>
      </section>

      <div className="attention-grid">
        <AttentionPanel
          title="Overdue"
          eyebrow="Needs attention"
          items={summary.overdue}
          empty="Nothing overdue. Your actionable deadlines are clear."
          tone="danger"
        />
        <AttentionPanel
          title={`Due in ${summary.horizon_days} days`}
          eyebrow="Coming up"
          items={summary.due_soon}
          empty="No actionable deadlines in the next seven days."
          tone="warning"
        />
      </div>

      <section className="dashboard-card" aria-labelledby="next-actions-title">
        <div className="section-heading">
          <div>
            <p className="section-eyebrow">Move the work forward</p>
            <h2 id="next-actions-title">Next actions</h2>
          </div>
          <span className="count-badge" aria-label={`${summary.next_actions.length} next actions`}>
            {summary.next_actions.length}
          </span>
        </div>
        {summary.next_actions.length ? (
          <div className="row-list">
            {summary.next_actions.map((opportunity) => (
              <OpportunityRow key={opportunity.id} opportunity={opportunity} mode="action" />
            ))}
          </div>
        ) : (
          <p className="empty-state">No next action is due soon. Add one to an active opportunity when follow-up matters.</p>
        )}
      </section>

      <section className="dashboard-card" aria-labelledby="activity-title">
        <div className="section-heading">
          <div>
            <p className="section-eyebrow">History</p>
            <h2 id="activity-title">Recent activity</h2>
          </div>
          <span className="count-badge" aria-label={`${summary.recent_activity.length} recent events`}>
            {summary.recent_activity.length}
          </span>
        </div>
        <ActivityList items={summary.recent_activity} />
      </section>

      <footer className="workspace-footer">
        <span>Private, owner-scoped workspace</span>
        <span>Updated {formatMoment(summary.generated_at)}</span>
      </footer>
    </main>
  );
}
