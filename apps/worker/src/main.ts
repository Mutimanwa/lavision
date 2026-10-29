import { createServer } from "node:http";
import { Client } from "pg";
import { Redis } from "ioredis";
import { readWorkerConfig } from "./config.js";
import { metricsAuthorized, workerLog, workerMetrics } from "./observability.js";

const config = readWorkerConfig();
const startedAt = Date.now();
let redisReady = false;
let databaseReady = false;
let shuttingDown = false;

// Aucun job métier n’est enregistré ici : ce processus est le socle supervisé
// du worker. Les consommateurs BullMQ/outbox seront ajoutés avec leurs cas d’usage.
const redis = new Redis(config.redisUrl, {
  lazyConnect: true,
  maxRetriesPerRequest: null,
  enableOfflineQueue: false,
});
const database = new Client({ connectionString: config.databaseUrl });

redis.on("error", () => {
  redisReady = false;
  if (!shuttingDown) workerLog("warn", "worker.redis.unavailable");
});
database.on("error", () => {
  databaseReady = false;
  if (!shuttingDown) workerLog("warn", "worker.postgresql.unavailable");
});

async function checkDependencies(): Promise<void> {
  try {
    await redis.ping();
    redisReady = true;
  } catch {
    redisReady = false;
  }
  try {
    await database.query("SELECT 1");
    databaseReady = true;
  } catch {
    databaseReady = false;
  }
}

const server = createServer((request, response) => {
  const path = request.url?.split("?", 1)[0] ?? "/";
  if (path === "/health/live") {
    response.writeHead(200, { "content-type": "application/json" });
    response.end(JSON.stringify({ status: "ok", service: "lavision-worker" }));
    return;
  }
  if (path === "/health/ready") {
    const ready = redisReady && databaseReady;
    response.writeHead(ready ? 200 : 503, { "content-type": "application/json" });
    response.end(JSON.stringify({ status: ready ? "ready" : "not_ready", checks: { redis: redisReady ? "up" : "down", database: databaseReady ? "up" : "down" } }));
    return;
  }
  if (path === "/metrics") {
    const expected = process.env.METRICS_TOKEN?.trim();
    const header = request.headers["x-metrics-token"];
    const authorization = request.headers.authorization;
    const bearer = typeof authorization === "string" && authorization.startsWith("Bearer ") ? authorization.slice(7) : undefined;
    const presented = typeof header === "string" ? header : bearer;
    if (!metricsAuthorized(expected, presented)) {
      response.writeHead(404, { "content-type": "application/json" });
      response.end(JSON.stringify({ status: "not_found" }));
      return;
    }
    response.writeHead(200, { "content-type": "text/plain; version=0.0.4; charset=utf-8" });
    response.end(workerMetrics({ uptime: (Date.now() - startedAt) / 1000, redis: redisReady, database: databaseReady }));
    return;
  }
  response.writeHead(404, { "content-type": "application/json" });
  response.end(JSON.stringify({ status: "not_found" }));
});

async function shutdown(signal: string): Promise<void> {
  if (shuttingDown) return;
  shuttingDown = true;
  workerLog("info", "worker.shutdown", { signal });
  clearInterval(heartbeat);
  await new Promise<void>((resolve) => server.close(() => resolve()));
  await Promise.allSettled([redis.quit(), database.end()]);
  process.exitCode = 0;
}

await redis.connect();
await database.connect();
await checkDependencies();
const heartbeat = setInterval(() => {
  void checkDependencies();
}, config.heartbeatMs);
server.listen(config.port, "0.0.0.0", () => {
  workerLog("info", "worker.started", { port: config.port });
});
process.once("SIGTERM", () => void shutdown("SIGTERM"));
process.once("SIGINT", () => void shutdown("SIGINT"));
