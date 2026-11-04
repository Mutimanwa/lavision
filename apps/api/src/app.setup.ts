import fastifyCookie from "@fastify/cookie";
import type { NestFastifyApplication } from "@nestjs/platform-fastify";
import { API_PREFIX } from "@lavision/contracts";
import { ProblemDetailsFilter } from "./common/filters/problem-details.filter.js";
import { installObservability } from "./common/observability.js";

export async function configureApp(app: NestFastifyApplication): Promise<void> {
  await app.register(fastifyCookie);
  app.setGlobalPrefix(API_PREFIX.slice(1));
  app.useGlobalFilters(new ProblemDetailsFilter());
  installObservability(app);
}
