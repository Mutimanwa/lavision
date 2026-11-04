import { describe, expect, it } from "vitest";
import { readWorkerConfig } from "./config.js";

const environment = {
  DATABASE_URL: "postgresql://example.invalid/lavision",
  REDIS_URL: "redis://example.invalid:6379",
} satisfies NodeJS.ProcessEnv;

describe("configuration du worker", () => {
  it("exige les deux dépendances et applique des bornes sûres", () => {
    expect(readWorkerConfig(environment)).toEqual({
      databaseUrl: environment.DATABASE_URL,
      redisUrl: environment.REDIS_URL,
      port: 3002,
      heartbeatMs: 5000,
    });
    expect(() => readWorkerConfig({ REDIS_URL: environment.REDIS_URL })).toThrow("DATABASE_URL");
    expect(() => readWorkerConfig({ DATABASE_URL: environment.DATABASE_URL })).toThrow("REDIS_URL");
    expect(() => readWorkerConfig({ ...environment, WORKER_HEARTBEAT_MS: "100" })).toThrow("WORKER_HEARTBEAT_MS");
  });
});
