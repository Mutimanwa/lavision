import { Module } from "@nestjs/common";
import { HealthController } from "./health.controller.js";
import { OpenApiController } from "./openapi.controller.js";
import { VersionController } from "./version.controller.js";
import { MetricsController } from "./metrics.controller.js";

@Module({
  controllers: [HealthController, OpenApiController, VersionController, MetricsController],
})
export class SystemModule {}
