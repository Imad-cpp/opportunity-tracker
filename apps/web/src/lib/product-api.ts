import type { DashboardSummary } from "./dashboard-api";

export type Account = {
  id: string;
  name: string;
  email: string;
  timezone: string;
};

export type OpportunityType = "JOB" | "INTERNSHIP" | "SCHOLARSHIP" | "PROGRAM" | "OTHER";
export type OpportunityStatus =
  | "SAVED"
  | "PREPARING"
  | "APPLIED"
  | "INTERVIEWING"
  | "OFFERED"
  | "ACCEPTED"
  | "REJECTED"
  | "WITHDRAWN"
  | "EXPIRED";
export type OpportunityPriority = "LOW" | "MEDIUM" | "HIGH";
export type DeadlinePrecision = "DATE" | "DATETIME";

export type Opportunity = {
  id: string;
  type: OpportunityType;
  status: OpportunityStatus;
  priority: OpportunityPriority;
  title: string;
  organization: string;
  source_url: string | null;
  location: string | null;
  notes: string | null;
  deadline_at: string | null;
  deadline_precision: DeadlinePrecision | null;
  deadline_timezone: string | null;
  deadline_attention: "OVERDUE" | "DUE_SOON" | "UPCOMING" | null;
  next_action: string | null;
  next_action_at: string | null;
  archived_at: string | null;
  created_at: string | null;
  updated_at: string | null;
};

export type OpportunityEvent = {
  id: string;
  type: "CREATED" | "UPDATED" | "STATUS_CHANGED" | "ARCHIVED" | "RESTORED";
  from_status: OpportunityStatus | null;
  to_status: OpportunityStatus | null;
  changed_fields: string[] | null;
  created_at: string | null;
};

export type OpportunityInput = {
  type: OpportunityType;
  priority: OpportunityPriority;
  title: string;
  organization: string;
  source_url: string | null;
  location: string | null;
  notes: string | null;
  next_action: string | null;
  next_action_at: string | null;
  deadline_at: string | null;
  deadline_precision: DeadlinePrecision | null;
  deadline_timezone: string | null;
};

export type OpportunityList = {
  data: Opportunity[];
  links: {
    first?: string | null;
    last?: string | null;
    prev?: string | null;
    next?: string | null;
  };
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
};

export type OpportunityFilters = {
  q?: string;
  status?: OpportunityStatus | "";
  type?: OpportunityType | "";
  priority?: OpportunityPriority | "";
  archived?: boolean;
  page?: number;
};

export type ValidationDetails = Record<string, string[]>;

type ErrorEnvelope = {
  error?: {
    code?: string;
    message?: string;
    details?: ValidationDetails;
  };
};

export class ApiError extends Error {
  status: number;
  code: string;
  details: ValidationDetails;

  constructor(status: number, payload: ErrorEnvelope) {
    super(payload.error?.message ?? "The request could not be completed.");
    this.name = "ApiError";
    this.status = status;
    this.code = payload.error?.code ?? "REQUEST_FAILED";
    this.details = payload.error?.details ?? {};
  }
}

const apiBaseUrl = process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost:8000/api/v1";
const apiOrigin = new URL(apiBaseUrl).origin;

function xsrfToken(): string | null {
  if (typeof document === "undefined") return null;
  const prefix = "XSRF-TOKEN=";
  const value = document.cookie
    .split("; ")
    .find((entry) => entry.startsWith(prefix))
    ?.slice(prefix.length);
  return value ? decodeURIComponent(value) : null;
}

async function parseError(response: Response): Promise<ApiError> {
  try {
    return new ApiError(response.status, (await response.json()) as ErrorEnvelope);
  } catch {
    return new ApiError(response.status, {});
  }
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(`${apiBaseUrl}${path}`, {
    ...init,
    credentials: "include",
    headers: { Accept: "application/json", ...init.headers },
  });

  if (!response.ok) throw await parseError(response);
  if (response.status === 204) return undefined as T;
  return (await response.json()) as T;
}

async function csrf(): Promise<void> {
  const response = await fetch(`${apiOrigin}/sanctum/csrf-cookie`, {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
  if (!response.ok) throw await parseError(response);
}

async function mutate<T>(path: string, method: "POST" | "PATCH" | "DELETE", body?: unknown): Promise<T> {
  await csrf();
  const token = xsrfToken();
  if (!token) throw new ApiError(419, { error: { code: "CSRF_TOKEN_MISMATCH", message: "CSRF bootstrap did not issue a readable XSRF token." } });

  return request<T>(path, {
    method,
    headers: { "Content-Type": "application/json", "X-XSRF-TOKEN": token },
    body: body === undefined ? undefined : JSON.stringify(body),
  });
}

function queryString(filters: OpportunityFilters): string {
  const query = new URLSearchParams();
  if (filters.q?.trim()) query.set("q", filters.q.trim());
  if (filters.status) query.set("status", filters.status);
  if (filters.type) query.set("type", filters.type);
  if (filters.priority) query.set("priority", filters.priority);
  if (filters.archived) query.set("archived", "true");
  if (filters.page && filters.page > 1) query.set("page", String(filters.page));
  const encoded = query.toString();
  return encoded ? `?${encoded}` : "";
}

export async function getMe(signal?: AbortSignal): Promise<Account> {
  const response = await request<{ data: Account }>("/me", { signal });
  return response.data;
}

export async function login(email: string, password: string): Promise<Account> {
  const response = await mutate<{ data: Account }>("/auth/login", "POST", { email, password });
  return response.data;
}

export async function register(input: { name: string; email: string; password: string; timezone: string }): Promise<Account> {
  const response = await mutate<{ data: Account }>("/auth/register", "POST", input);
  return response.data;
}

export async function logout(): Promise<void> {
  await mutate<void>("/auth/logout", "POST");
}

export async function getDashboard(signal?: AbortSignal): Promise<DashboardSummary> {
  const response = await request<{ data: DashboardSummary }>("/dashboard/summary", { signal });
  return response.data;
}

export async function listOpportunities(filters: OpportunityFilters, signal?: AbortSignal): Promise<OpportunityList> {
  return request<OpportunityList>(`/opportunities${queryString(filters)}`, { signal });
}

export async function getOpportunity(id: string, signal?: AbortSignal): Promise<Opportunity> {
  const response = await request<{ data: Opportunity }>(`/opportunities/${id}`, { signal });
  return response.data;
}

export async function getOpportunityEvents(id: string, signal?: AbortSignal): Promise<OpportunityEvent[]> {
  const response = await request<{ data: OpportunityEvent[] }>(`/opportunities/${id}/events`, { signal });
  return response.data;
}

export async function createOpportunity(input: OpportunityInput): Promise<Opportunity> {
  const response = await mutate<{ data: Opportunity }>("/opportunities", "POST", input);
  return response.data;
}

export async function updateOpportunity(id: string, input: OpportunityInput): Promise<Opportunity> {
  const response = await mutate<{ data: Opportunity }>(`/opportunities/${id}`, "PATCH", input);
  return response.data;
}

export async function updateOpportunityStatus(id: string, status: OpportunityStatus): Promise<Opportunity> {
  const response = await mutate<{ data: Opportunity }>(`/opportunities/${id}/status`, "POST", { status });
  return response.data;
}

export async function archiveOpportunity(id: string): Promise<Opportunity> {
  const response = await mutate<{ data: Opportunity }>(`/opportunities/${id}/archive`, "POST");
  return response.data;
}

export async function restoreOpportunity(id: string): Promise<Opportunity> {
  const response = await mutate<{ data: Opportunity }>(`/opportunities/${id}/restore`, "POST");
  return response.data;
}

export async function deleteOpportunity(id: string): Promise<void> {
  await mutate<void>(`/opportunities/${id}`, "DELETE");
}
