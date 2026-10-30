export interface ApiIssue {
  path: string;
  code: string;
  message: string;
}

interface ProblemDetails {
  title?: string;
  detail?: string;
  code?: string;
  requestId?: string;
  issues?: ApiIssue[];
}

export class ApiError extends Error {
  readonly status: number;
  readonly code: string | null;
  readonly requestId: string | null;
  readonly issues: ApiIssue[];

  constructor(status: number, problem: ProblemDetails = {}) {
    super(problem.detail ?? problem.title ?? `La requête API a échoué (${status}).`);
    this.name = "ApiError";
    this.status = status;
    this.code = problem.code ?? null;
    this.requestId = problem.requestId ?? null;
    this.issues = Array.isArray(problem.issues) ? problem.issues : [];
  }
}

const API_PREFIX = "/api/v1";

function isTauriRuntime(): boolean {
  if (typeof window === "undefined") return false;
  const desktopWindow = window as Window & { __TAURI_INTERNALS__?: unknown; __TAURI__?: unknown };
  return Boolean(desktopWindow.__TAURI_INTERNALS__ || desktopWindow.__TAURI__)
    || window.location.protocol === "tauri:"
    || window.location.hostname === "tauri.localhost";
}

function cookieValue(name: string): string | null {
  if (typeof document === "undefined") return null;
  const prefix = `${name}=`;
  const entry = document.cookie.split(";").map((part) => part.trim()).find((part) => part.startsWith(prefix));
  if (!entry) return null;
  try {
    return decodeURIComponent(entry.slice(prefix.length));
  } catch {
    return null;
  }
}

async function responseBody(response: Response): Promise<unknown> {
  const text = await response.text();
  if (!text) return null;
  try {
    return JSON.parse(text) as unknown;
  } catch {
    return null;
  }
}

/** Client same-origin : le proxy Web/Nginx évite toute URL localhost dans le navigateur. */
export async function apiFetch<T>(path: string, init: RequestInit = {}): Promise<T> {
  if (!path.startsWith("/")) throw new Error("Le chemin API doit être relatif à /api/v1.");
  if (isTauriRuntime()) {
    throw new ApiError(0, {
      code: "API_ORIGIN_UNAVAILABLE",
      detail: "Le shell desktop statique ne dispose pas encore d’un pont API sécurisé. Utilisez le Web servi avec son proxy API ; aucune session ou configuration locale ne sera simulée.",
    });
  }
  const method = (init.method ?? "GET").toUpperCase();
  const headers = new Headers(init.headers);
  headers.set("accept", "application/json");
  if (init.body !== undefined && !headers.has("content-type")) headers.set("content-type", "application/json");
  if (!["GET", "HEAD", "OPTIONS"].includes(method)) {
    const csrf = cookieValue("lavision_csrf");
    if (csrf && !headers.has("x-csrf-token")) headers.set("x-csrf-token", csrf);
  }

  const response = await fetch(`${API_PREFIX}${path}`, {
    ...init,
    method,
    headers,
    credentials: "include",
    cache: init.cache ?? "no-store",
  });
  const body = await responseBody(response);
  if (!response.ok) {
    const problem = typeof body === "object" && body !== null ? body as ProblemDetails : {};
    throw new ApiError(response.status, problem);
  }
  if (body === null && response.status !== 204) {
    throw new ApiError(502, {
      code: "INVALID_API_RESPONSE",
      detail: "Le serveur a renvoyé une réponse vide ou non JSON. Vérifiez que le proxy /api pointe bien vers l’API.",
    });
  }
  return body as T;
}

export function isApiError(error: unknown, status?: number): error is ApiError {
  return error instanceof ApiError && (status === undefined || error.status === status);
}
