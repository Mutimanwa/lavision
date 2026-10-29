export interface WorkerConfig {
  databaseUrl: string;
  redisUrl: string;
  port: number;
  heartbeatMs: number;
}

function required(name: string, env: NodeJS.ProcessEnv): string {
  const value = env[name]?.trim();
  if (!value) throw new Error(`${name} est requis pour le worker.`);
  return value;
}

function boundedInteger(name: string, fallback: number, min: number, max: number, env: NodeJS.ProcessEnv): number {
  const value = Number.parseInt(env[name] ?? String(fallback), 10);
  if (!Number.isInteger(value) || value < min || value > max) {
    throw new Error(`${name} doit être un entier compris entre ${min} et ${max}.`);
  }
  return value;
}

export function readWorkerConfig(env: NodeJS.ProcessEnv = process.env): WorkerConfig {
  return {
    databaseUrl: required("DATABASE_URL", env),
    redisUrl: required("REDIS_URL", env),
    port: boundedInteger("WORKER_PORT", 3002, 1, 65_535, env),
    heartbeatMs: boundedInteger("WORKER_HEARTBEAT_MS", 5_000, 1_000, 60_000, env),
  };
}
