import { randomUUID } from "node:crypto";
import { NestFactory } from "@nestjs/core";
import {
  FastifyAdapter,
  type NestFastifyApplication,
} from "@nestjs/platform-fastify";
import { afterAll, beforeAll, describe, expect, it } from "vitest";
import { AppModule } from "../../src/app.module.js";
import { ProblemDetailsFilter } from "../../src/common/filters/problem-details.filter.js";

let app: NestFastifyApplication;

describe("HTTP et PostgreSQL", () => {
  beforeAll(async () => {
    app = await NestFactory.create<NestFastifyApplication>(
      AppModule,
      new FastifyAdapter({ genReqId: () => randomUUID() }),
      { logger: false },
    );
    app.setGlobalPrefix("api/v1");
    app.useGlobalFilters(new ProblemDetailsFilter());
    await app.init();
    await app.getHttpAdapter().getInstance().ready();
  });

  afterAll(async () => {
    await app.close();
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
});
