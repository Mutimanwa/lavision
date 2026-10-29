import { randomUUID } from "node:crypto";
import { NestFactory } from "@nestjs/core";
import {
  FastifyAdapter,
  type NestFastifyApplication,
} from "@nestjs/platform-fastify";
import { afterAll, beforeAll, describe, expect, it } from "vitest";
import { AppModule } from "../../src/app.module.js";
import { configureApp } from "../../src/app.setup.js";

let app: NestFastifyApplication;
let previousMetricsToken: string | undefined;

describe("HTTP et PostgreSQL", () => {
  beforeAll(async () => {
    process.env.AUTH_ENCRYPTION_KEY ??= Buffer.alloc(32, 7).toString("base64");
    previousMetricsToken = process.env.METRICS_TOKEN;
    process.env.METRICS_TOKEN = "integration-metrics-token";
    app = await NestFactory.create<NestFastifyApplication>(
      AppModule,
      new FastifyAdapter({ genReqId: () => randomUUID() }),
      { logger: false },
    );
    await configureApp(app);
    await app.init();
    await app.getHttpAdapter().getInstance().ready();
  });

  afterAll(async () => {
    await app.close();
    if (previousMetricsToken === undefined) delete process.env.METRICS_TOKEN;
    else process.env.METRICS_TOKEN = previousMetricsToken;
  });

  it("confirme le readiness avec une vraie requête PostgreSQL", async () => {
    const response = await app.getHttpAdapter().getInstance().inject({
      method: "GET",
      url: "/api/v1/health/ready",
    });

    expect(response.statusCode).toBe(200);
    expect(response.json()).toEqual({
      status: "ready",
      service: "lavision-api",
      checks: { database: "up" },
    });
  });

  it("expose OpenAPI et normalise une route inconnue", async () => {
    const fastify = app.getHttpAdapter().getInstance();
    const openapi = await fastify.inject({
      method: "GET",
      url: "/api/v1/openapi.json",
    });
    const missing = await fastify.inject({
      method: "GET",
      url: "/api/v1/route-inconnue",
    });

    expect(openapi.statusCode).toBe(200);
    expect(openapi.json().openapi).toBe("3.1.0");
    expect(missing.statusCode).toBe(404);
    expect(missing.headers["content-type"]).toContain(
      "application/problem+json",
    );
    expect(missing.json()).toMatchObject({
      status: 404,
      code: "NOT_FOUND",
      requestId: missing.headers["x-request-id"],
    });
  });

  it("protège les métriques et expose seulement des séries techniques", async () => {
    const fastify = app.getHttpAdapter().getInstance();
    const denied = await fastify.inject({ method: "GET", url: "/api/v1/metrics" });
    expect(denied.statusCode).toBe(403);
    const response = await fastify.inject({
      method: "GET",
      url: "/api/v1/metrics",
      headers: { authorization: "Bearer integration-metrics-token" },
    });
    expect(response.statusCode).toBe(200);
    expect(response.headers["content-type"]).toContain("text/plain");
    expect(response.body).toContain("lavision_up 1");
    expect(response.body).toContain("lavision_http_requests_total");
    expect(response.body).not.toContain("integration-metrics-token");
    expect(response.body).not.toContain("requestId");
  });
});
