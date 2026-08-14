export type DashboardOpportunity = {
  id: string;
  type: "JOB" | "INTERNSHIP" | "SCHOLARSHIP" | "PROGRAM" | "OTHER";
  status: "SAVED" | "PREPARING" | "APPLIED" | "INTERVIEWING" | "OFFERED";
  priority: "LOW" | "MEDIUM" | "HIGH";
  title: string;
  organization: string;
  deadline_at: string | null;
  deadline_precision: "DATE" | "DATETIME" | null;
  deadline_timezone: string | null;
  deadline_attention: "OVERDUE" | "DUE_SOON" | "UPCOMING" | null;
  next_action: string | null;
  next_action_at: string | null;
};

export type DashboardActivity = {
  id: string;
  type: "CREATED" | "UPDATED" | "STATUS_CHANGED" | "ARCHIVED" | "RESTORED";
  from_status: string | null;
  to_status: string | null;
  changed_fields: string[] | null;
  created_at: string | null;
  opportunity: {
    id: string;
    title: string;
    organization: string;
    status: string;
  };
};

export type DashboardSummary = {
  generated_at: string;
  horizon_days: number;
  status_counts: Record<"SAVED" | "PREPARING" | "APPLIED" | "INTERVIEWING" | "OFFERED", number>;
  due_soon: DashboardOpportunity[];
  overdue: DashboardOpportunity[];
  next_actions: DashboardOpportunity[];
  recent_activity: DashboardActivity[];
};

export class DashboardRequestError extends Error {
  status: number;

  constructor(status: number, message: string) {
    super(message);
    this.name = "DashboardRequestError";
    this.status = status;
  }
}

const apiBaseUrl = process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost:8000/api/v1";

export async function readDashboard(signal?: AbortSignal): Promise<DashboardSummary> {
  const response = await fetch(`${apiBaseUrl}/dashboard/summary`, {
    credentials: "include",
    headers: { Accept: "application/json" },
    signal,
  });

  if (!response.ok) {
    let message = "The dashboard could not be loaded.";

    try {
      const payload = (await response.json()) as { error?: { message?: string } };
      message = payload.error?.message ?? message;
    } catch {
      // Keep the stable fallback message when the response is not JSON.
    }

    throw new DashboardRequestError(response.status, message);
  }

  const payload = (await response.json()) as { data: DashboardSummary };
  return payload.data;
}
