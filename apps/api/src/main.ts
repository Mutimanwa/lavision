import "reflect-metadata";
import { randomUUID } from "node:crypto";
import { NestFactory } from "@nestjs/core";
import {
  FastifyAdapter,
  type NestFastifyApplication,
} from "@nestjs/platform-fastify";
import { API_PREFIX } from "@lavision/contracts";
import { AppModule } from "./app.module.js";
import { configureApp } from "./app.setup.js";
import { structuredLog } from "./common/observability.js";

async function bootstrap(): Promise<void> {
  const adapter = new FastifyAdapter({
    bodyLimit: 1_048_576,
    genReqId: () => randomUUID(),
    trustProxy: process.env.TRUST_PROXY === "true",
  });
  const app = await NestFactory.create<NestFastifyApplication>(
    AppModule,
    adapter,
  );

  await configureApp(app);
  app.enableShutdownHooks();

  const port = Number.parseInt(process.env.PORT ?? "3001", 10);
  if (!Number.isInteger(port) || port < 1 || port > 65_535) {
    throw new Error("PORT doit être un entier compris entre 1 et 65535.");
  }

  await app.listen(port, "0.0.0.0");
  structuredLog("info", "api.started", { apiPrefix: API_PREFIX, port });
}

void bootstrap();
