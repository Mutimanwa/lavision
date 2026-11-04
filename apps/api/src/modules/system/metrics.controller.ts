import { Controller, ForbiddenException, Get, Header, NotFoundException, Req } from "@nestjs/common";
import { ERROR_CODES } from "@lavision/contracts";
import type { FastifyRequest } from "fastify";
import { Public } from "../auth/auth.decorators.js";
import { metricsRegistry, metricsTokenMatches } from "../../common/observability.js";

@Public()
@Controller("metrics")
export class MetricsController {
  @Get()
  @Header("Content-Type", "text/plain; version=0.0.4; charset=utf-8")
  getMetrics(@Req() request: FastifyRequest): string {
    const expected = process.env.METRICS_TOKEN?.trim();
    if (!expected) throw new NotFoundException({ code: ERROR_CODES.NOT_FOUND });
    const header = request.headers["x-metrics-token"];
    const authorization = request.headers.authorization;
    const bearer = typeof authorization === "string" && authorization.startsWith("Bearer ") ? authorization.slice(7) : undefined;
    const presented = typeof header === "string" ? header : bearer;
    if (!metricsTokenMatches(expected, presented)) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
    return metricsRegistry.render();
  }
}
