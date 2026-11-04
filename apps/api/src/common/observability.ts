import { timingSafeEqual } from "node:crypto";
import { performance } from "node:perf_hooks";
import type { FastifyRequest } from "fastify";
import type { NestFastifyApplication } from "@nestjs/platform-fastify";

type LogLevel = "debug" | "info" | "warn" | "error";
type LogFields = Record<string, string | number | boolean | undefined>;

const priorities: Record<LogLevel, number> = { debug: 10, info: 20, warn: 30, error: 40 };

export function structuredLog(level: LogLevel, event: string, fields: LogFields = {}): void {
  const configured = process.env.LOG_LEVEL?.toLowerCase();
  if (configured === "silent" || (process.env.NODE_ENV === "test" && !configured)) return;
  const minimum = configured && configured in priorities ? priorities[configured as LogLevel] : priorities.info;
  if (priorities[level] < minimum) return;
  const record = {
    timestamp: new Date().toISOString(),
    level,
    service: "lavision-api",
    environment: process.env.APP_ENV ?? "local",
    version: process.env.APP_VERSION ?? "0.1.0",
    event,
    ...Object.fromEntries(Object.entries(fields).filter(([, value]) => value !== undefined)),
  };
  const output = `${JSON.stringify(record)}\n`;
  if (level === "error") process.stderr.write(output);
  else process.stdout.write(output);
}

export function metricsTokenMatches(expected: string | undefined, presented: string | undefined): boolean {
  if (!expected || !presented) return false;
  const left = Buffer.from(expected);
  const right = Buffer.from(presented);
  return left.length === right.length && timingSafeEqual(left, right);
}

function label(value: string): string {
  return value.replaceAll("\\", "\\\\").replaceAll('"', '\\"').replaceAll("\n", "\\n");
}

interface RequestMetric {
  count: number;
  durationSeconds: number;
}

export class MetricsRegistry {
  private readonly requests = new Map<string, RequestMetric>();

  recordRequest(method: string, route: string, status: number, durationSeconds: number): void {
    const key = JSON.stringify([method, route, status]);
    const current = this.requests.get(key) ?? { count: 0, durationSeconds: 0 };
    current.count += 1;
    current.durationSeconds += durationSeconds;
    this.requests.set(key, current);
  }

  reset(): void {
    this.requests.clear();
  }

  render(): string {
    const lines = [
      "# HELP lavision_up Whether the API process is running.",
      "# TYPE lavision_up gauge",
      "lavision_up 1",
      "# HELP lavision_http_requests_total HTTP requests by method, route and status.",
      "# TYPE lavision_http_requests_total counter",
    ];
    for (const [key, metric] of this.requests) {
      const [method, route, status] = JSON.parse(key) as [string, string, number];
      lines.push(`lavision_http_requests_total{method="${label(method)}",route="${label(route)}",status="${status}"} ${metric.count}`);
    }
    lines.push(
      "# HELP lavision_http_request_duration_seconds_sum Total HTTP request duration.",
      "# TYPE lavision_http_request_duration_seconds_sum counter",
    );
    for (const [key, metric] of this.requests) {
      const [method, route, status] = JSON.parse(key) as [string, string, number];
      lines.push(`lavision_http_request_duration_seconds_sum{method="${label(method)}",route="${label(route)}",status="${status}"} ${metric.durationSeconds.toFixed(6)}`);
    }
    lines.push(
      "# HELP lavision_http_request_duration_seconds_count HTTP request duration observations.",
      "# TYPE lavision_http_request_duration_seconds_count counter",
    );
    for (const [key, metric] of this.requests) {
      const [method, route, status] = JSON.parse(key) as [string, string, number];
      lines.push(`lavision_http_request_duration_seconds_count{method="${label(method)}",route="${label(route)}",status="${status}"} ${metric.count}`);
    }
    const memory = process.memoryUsage();
    lines.push(
      "# HELP lavision_process_resident_memory_bytes Resident memory of the API process.",
      "# TYPE lavision_process_resident_memory_bytes gauge",
      `lavision_process_resident_memory_bytes ${memory.rss}`,
      "# HELP lavision_process_uptime_seconds API process uptime.",
      "# TYPE lavision_process_uptime_seconds gauge",
      `lavision_process_uptime_seconds ${process.uptime().toFixed(3)}`,
      "",
    );
    return lines.join("\n");
  }
}

export const metricsRegistry = new MetricsRegistry();

function routeName(request: FastifyRequest): string {
  // Ne jamais utiliser request.url : il peut contenir des identifiants et une query sensible.
  return request.routeOptions.url ?? "unmatched";
}

export function installObservability(app: NestFastifyApplication): void {
  const fastify = app.getHttpAdapter().getInstance();
  const startedAt = new WeakMap<FastifyRequest, number>();
  fastify.addHook("onRequest", async (request) => {
    startedAt.set(request, performance.now());
  });
  fastify.addHook("onResponse", async (request, reply) => {
    const durationSeconds = Math.max(0, (performance.now() - (startedAt.get(request) ?? performance.now())) / 1000);
    const route = routeName(request);
    metricsRegistry.recordRequest(request.method, route, reply.statusCode, durationSeconds);
    structuredLog(reply.statusCode >= 500 ? "error" : "info", "http.request", {
      requestId: String(request.id),
      method: request.method,
      route,
      status: reply.statusCode,
      durationMs: Number((durationSeconds * 1000).toFixed(2)),
    });
  });
  fastify.addHook("onError", async (request, _reply, error) => {
    structuredLog("error", "http.exception", {
      requestId: String(request.id),
      method: request.method,
      route: routeName(request),
      errorType: error instanceof Error ? error.name : "unknown",
    });
  });
}
