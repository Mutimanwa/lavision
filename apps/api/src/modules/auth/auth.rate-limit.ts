import { createHmac } from "node:crypto";
import { HttpException, HttpStatus, Injectable } from "@nestjs/common";
import { ERROR_CODES } from "@lavision/contracts";
import {
  AuthEventOutcome,
  AuthRateAction,
} from "../../database/client.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import { AUTH_POLICY, AuthConfigService } from "./auth.config.js";
import { AuthEventService } from "./auth.events.js";
import type { AuthRequestContext } from "./auth.types.js";

interface RateKey {
  keyHash: string;
  threshold: number;
  scope: "subject" | "ip";
}

@Injectable()
export class AuthRateLimitService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly config: AuthConfigService,
    private readonly events: AuthEventService,
  ) {}

  async assertAllowed(
    action: AuthRateAction,
    context: AuthRequestContext,
  ): Promise<void> {
    const keys = this.keys(action, context);
    if (keys.length === 0) return;
    const now = new Date();
    const buckets = await this.prisma.authRateBucket.findMany({
      where: {
        action,
        OR: keys.map(({ keyHash }) => ({ keyHash })),
        blockedUntil: { gt: now },
      },
    });

    if (buckets.length > 0) {
      await this.events.recordSafe({
        action: `RATE_LIMIT_${action}`,
        outcome: AuthEventOutcome.RATE_LIMITED,
        reason: "BLOCKED",
        context,
      });
      throw new HttpException(
        { code: ERROR_CODES.RATE_LIMITED },
        HttpStatus.TOO_MANY_REQUESTS,
      );
    }
  }

  async registerFailure(
    action: AuthRateAction,
    context: AuthRequestContext,
  ): Promise<void> {
    const now = new Date();
    const windowStart = new Date(
      now.getTime() - AUTH_POLICY.rateWindowSeconds * 1000,
    );

    for (const key of this.keys(action, context)) {
      await this.prisma.authRateBucket.updateMany({
        where: {
          action,
          keyHash: key.keyHash,
          windowStartedAt: { lt: windowStart },
        },
        data: { windowStartedAt: now, failureCount: 0, blockedUntil: null },
      });

      const bucket = await this.prisma.authRateBucket.upsert({
        where: { action_keyHash: { action, keyHash: key.keyHash } },
        create: {
          action,
          keyHash: key.keyHash,
          windowStartedAt: now,
          failureCount: 1,
        },
        update: { failureCount: { increment: 1 } },
      });

      if (bucket.failureCount >= key.threshold) {
        const level = Math.floor(
          (bucket.failureCount - key.threshold) / key.threshold,
        );
        const blockedSeconds = Math.min(3600, 60 * 2 ** level);
        await this.prisma.authRateBucket.update({
          where: { id: bucket.id },
          data: {
            blockedUntil: new Date(now.getTime() + blockedSeconds * 1000),
          },
        });
      }
    }
  }

  async reset(action: AuthRateAction, context: AuthRequestContext): Promise<void> {
    const accountKeys = this.keys(action, context).filter(
      (key) => key.scope === "subject",
    );
    if (accountKeys.length === 0) return;
    await this.prisma.authRateBucket.deleteMany({
      where: {
        action,
        OR: accountKeys.map(({ keyHash }) => ({ keyHash })),
      },
    });
  }

  private keys(action: AuthRateAction, context: AuthRequestContext): RateKey[] {
    const keys: RateKey[] = [];
    if (context.subject) {
      keys.push({
        keyHash: this.hmac(`${action}:subject:${context.subject}`),
        scope: "subject",
        threshold:
          action === AuthRateAction.LOGIN
            ? AUTH_POLICY.loginAccountThreshold
            : AUTH_POLICY.mfaMaxAttempts,
      });
    }
    if (context.ipAddress) {
      keys.push({
        keyHash: this.hmac(`${action}:ip:${context.ipAddress}`),
        scope: "ip",
        threshold: AUTH_POLICY.loginIpThreshold,
      });
    }
    return keys;
  }

  private hmac(value: string): string {
    return createHmac("sha256", this.config.encryptionKey)
      .update(value)
      .digest("hex");
  }
}
