import { createHmac } from "node:crypto";
import { Injectable, Logger } from "@nestjs/common";
import { AuthEventOutcome } from "../../database/client.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import { AuthConfigService } from "./auth.config.js";
import type { AuthRequestContext } from "./auth.types.js";

@Injectable()
export class AuthEventService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly config: AuthConfigService,
  ) {}

  async recordSafe(input: {
    action: string;
    outcome: AuthEventOutcome;
    reason?: string;
    userId?: string;
    context: AuthRequestContext;
  }): Promise<void> {
    try {
      await this.prisma.authEvent.create({
        data: {
          userId: input.userId,
          action: input.action,
          outcome: input.outcome,
          reason: input.reason,
          requestId: input.context.requestId,
          subjectHash: input.context.subject
            ? this.hash("subject", input.context.subject)
            : undefined,
          ipHash: input.context.ipAddress
            ? this.hash("ip", input.context.ipAddress)
            : undefined,
          userAgent: input.context.userAgent?.slice(0, 512),
        },
      });
    } catch {
      Logger.error("Échec d’écriture d’un événement d’authentification.", "AuthEvent");
    }
  }

  private hash(kind: string, value: string): string {
    return createHmac("sha256", this.config.encryptionKey)
      .update(`${kind}:${value}`)
      .digest("hex");
  }
}
