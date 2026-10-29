import {
  CanActivate,
  ExecutionContext,
  ForbiddenException,
  Injectable,
  UnauthorizedException,
} from "@nestjs/common";
import { Reflector } from "@nestjs/core";
import { ERROR_CODES } from "@lavision/contracts";
import type { FastifyRequest } from "fastify";
import {
  AuthEventOutcome,
  MembershipStatus,
  UserStatus,
} from "../../database/client.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import { AuthCookieService } from "./auth.cookies.js";
import { AuthCryptoService } from "./auth.crypto.js";
import {
  authRequestContext,
  IS_PUBLIC_ROUTE,
} from "./auth.decorators.js";
import { AuthEventService } from "./auth.events.js";
import { requiresMfa } from "./auth.policy.js";
import {
  authSessionInclude,
  type AuthenticatedRequest,
} from "./auth.types.js";

@Injectable()
export class SessionGuard implements CanActivate {
  constructor(
    private readonly reflector: Reflector,
    private readonly prisma: PrismaService,
    private readonly crypto: AuthCryptoService,
    private readonly cookies: AuthCookieService,
    private readonly events: AuthEventService,
  ) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const isPublic = this.reflector.getAllAndOverride<boolean>(IS_PUBLIC_ROUTE, [
      context.getHandler(),
      context.getClass(),
    ]);
    if (isPublic) return true;

    const request = context
      .switchToHttp()
      .getRequest<AuthenticatedRequest>();
    const token = this.cookies.sessionToken(request);
    if (!token) throw this.unauthorized(ERROR_CODES.UNAUTHORIZED);

    const session = await this.prisma.authSession.findUnique({
      where: { tokenHash: this.crypto.hashToken(token) },
      include: authSessionInclude,
    });
    if (!session) throw this.unauthorized(ERROR_CODES.UNAUTHORIZED);

    const now = new Date();
    if (
      session.revokedAt ||
      session.user.status !== UserStatus.ACTIVE ||
      session.authVersion !== session.user.authVersion
    ) {
      await this.reject(session.id, "ACCOUNT_OR_SESSION_REVOKED", session.userId, request);
      throw this.unauthorized(ERROR_CODES.UNAUTHORIZED);
    }

    if (now >= session.expiresAt || now >= session.absoluteExpiresAt) {
      await this.prisma.authSession.update({
        where: { id: session.id },
        data: { revokedAt: now, revocationReason: "EXPIRED" },
      });
      throw this.unauthorized(ERROR_CODES.SESSION_EXPIRED);
    }

    const roles = session.user.memberships
      .filter(
        (membership) =>
          membership.status === MembershipStatus.ACTIVE && !membership.archivedAt,
      )
      .flatMap((membership) =>
        membership.roleAssignments
          .filter((assignment) => !assignment.archivedAt)
          .map((assignment) => assignment.role),
      );
    if (
      (requiresMfa(roles) || session.user.mfaCredential?.confirmedAt) &&
      !session.mfaVerifiedAt
    ) {
      await this.reject(session.id, "MFA_REQUIRED", session.userId, request);
      throw new ForbiddenException({ code: ERROR_CODES.MFA_REQUIRED });
    }

    if (!["GET", "HEAD", "OPTIONS"].includes(request.method)) {
      const csrfToken = this.cookies.csrfToken(request);
      const csrfCookie = this.cookies.csrfCookie(request);
      if (
        !csrfToken ||
        !csrfCookie ||
        csrfToken !== csrfCookie ||
        this.crypto.hashToken(csrfToken) !== session.csrfTokenHash
      ) {
        throw new ForbiddenException({ code: ERROR_CODES.CSRF_INVALID });
      }
    }

    await this.prisma.authSession.update({
      where: { id: session.id },
      data: { lastSeenAt: now },
    });
    session.lastSeenAt = now;
    request.auth = { session, sessionToken: token };
    return true;
  }

  private async reject(
    sessionId: string,
    reason: string,
    userId: string,
    request: FastifyRequest,
  ): Promise<void> {
    await this.prisma.authSession.updateMany({
      where: { id: sessionId, revokedAt: null },
      data: { revokedAt: new Date(), revocationReason: reason },
    });
    await this.events.recordSafe({
      action: "SESSION_REJECTED",
      outcome: AuthEventOutcome.FAILURE,
      reason,
      userId,
      context: authRequestContext(request),
    });
  }

  private unauthorized(code: string): UnauthorizedException {
    return new UnauthorizedException({ code });
  }
}
