import "reflect-metadata";
import { randomUUID } from "node:crypto";
import { Logger } from "@nestjs/common";
import { NestFactory } from "@nestjs/core";
import {
  FastifyAdapter,
  type NestFastifyApplication,
} from "@nestjs/platform-fastify";
import { API_PREFIX } from "@lavision/contracts";
import { AppModule } from "./app.module.js";
import { ProblemDetailsFilter } from "./common/filters/problem-details.filter.js";

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

  app.setGlobalPrefix(API_PREFIX.replace(/^\//, ""));
  app.useGlobalFilters(new ProblemDetailsFilter());
  app.enableShutdownHooks();

  const port = Number.parseInt(process.env.PORT ?? "3001", 10);
  if (!Number.isInteger(port) || port < 1 || port > 65_535) {
    throw new Error("PORT doit être un entier compris entre 1 et 65535.");
  }

  await app.listen(port, "0.0.0.0");
  Logger.log(`lavision API disponible sur ${API_PREFIX}`, "Bootstrap");
}

void bootstrap();
