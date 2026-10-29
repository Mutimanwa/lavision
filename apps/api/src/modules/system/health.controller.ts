import {
  Controller,
  Get,
  ServiceUnavailableException,
} from "@nestjs/common";
import {
  ERROR_CODES,
  SERVICE_NAME,
  type LivenessResponse,
  type ReadinessResponse,
} from "@lavision/contracts";
import { PrismaService } from "../infrastructure/prisma.service.js";
import { Public } from "../auth/auth.decorators.js";

@Public()
@Controller("health")
export class HealthController {
  constructor(private readonly prisma: PrismaService) {}

  @Get("live")
  getLiveness(): LivenessResponse {
    return { status: "ok", service: SERVICE_NAME };
  }

  @Get("ready")
  async getReadiness(): Promise<ReadinessResponse> {
    try {
      await this.prisma.isReady();
    } catch {
      throw new ServiceUnavailableException({
        code: ERROR_CODES.DATABASE_UNAVAILABLE,
      });
    }

    return {
      status: "ready",
      service: SERVICE_NAME,
      checks: { database: "up" },
    };
  }
}
