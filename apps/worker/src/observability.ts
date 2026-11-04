import { timingSafeEqual } from "node:crypto";

export function workerLog(level: "info" | "warn" | "error", event: string, fields: Record<string, string | number | boolean> = {}): void {
  if (process.env.NODE_ENV === "test" || process.env.LOG_LEVEL === "silent") return;
  const record = {
    timestamp: new Date().toISOString(),
    level,
    service: "lavision-worker",
    environment: process.env.APP_ENV ?? "local",
    version: process.env.APP_VERSION ?? "0.1.0",
    event,
    ...fields,
  };
  process.stdout.write(`${JSON.stringify(record)}\n`);
}

export function metricsAuthorized(expected: string | undefined, presented: string | undefined): boolean {
  if (!expected || !presented) return false;
  const left = Buffer.from(expected);
  const right = Buffer.from(presented);
  return left.length === right.length && timingSafeEqual(left, right);
}

export function workerMetrics(input: { uptime: number; redis: boolean; database: boolean }): string {
  return [
    "# HELP lavision_worker_up Whether the worker process is running.",
    "# TYPE lavision_worker_up gauge",
    "lavision_worker_up 1",
    "# HELP lavision_worker_dependency_up Dependency availability observed by the worker.",
    "# TYPE lavision_worker_dependency_up gauge",
    `lavision_worker_dependency_up{dependency="redis"} ${input.redis ? 1 : 0}`,
    `lavision_worker_dependency_up{dependency="postgresql"} ${input.database ? 1 : 0}`,
    "# HELP lavision_process_uptime_seconds Worker process uptime.",
    "# TYPE lavision_process_uptime_seconds gauge",
    `lavision_process_uptime_seconds ${input.uptime.toFixed(3)}`,
    "",
  ].join("\n");
}
