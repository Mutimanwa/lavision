import {
  BadRequestException,
  ConflictException,
  ForbiddenException,
  Injectable,
  NotFoundException,
  UnauthorizedException,
} from "@nestjs/common";
import {
  ERROR_CODES,
  type AcceptInvitationInput,
  type AuthenticatedResponse,
  type AuthMeResponse,
  type InvitationInput,
  type InvitationResponse,
  type LoginInput,
  type MfaRecoveryInput,
  type MfaRequiredResponse,
  type MfaSetupInput,
  type MfaSetupResponse,
  type MfaVerifyInput,
  type OrganizationActionInput,
  type PasswordResetConfirmInput,
  type PasswordResetRequestInput,
  type SessionsResponse,
} from "@lavision/contracts";
import {
  AuthEventOutcome,
  AuthRateAction,
  AuthTokenKind,
  MembershipStatus,
  Prisma,
  Role,
  UserStatus,
  type AuthOneTimeToken,
} from "../../database/client.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import { AUTH_POLICY } from "./auth.config.js";
import { AuthCryptoService } from "./auth.crypto.js";
import { AuthEventService } from "./auth.events.js";
import { AuthMailService } from "./auth.mail.js";
import {
  PRISMA_ROLE_BY_ROLE_ID,
  requiresMfa,
} from "./auth.policy.js";
import {
  presentAuthenticated,
  presentMembership,
  presentSession,
} from "./auth.presenters.js";
import { AuthRateLimitService } from "./auth.rate-limit.js";
import {
  authSessionInclude,
  type AuthRequestContext,
  type IssuedSession,
  type SessionAuthContext,
} from "./auth.types.js";

const userAuthInclude = {
  mfaCredential: true,
  memberships: {
    include: { roleAssignments: true },
  },
} satisfies Prisma.UserInclude;

type UserWithAuth = Prisma.UserGetPayload<{ include: typeof userAuthInclude }>;
type TokenWithUser = AuthOneTimeToken & { user: UserWithAuth };
type Db = Prisma.TransactionClient | PrismaService;

export interface AuthenticationResult {
  issued: IssuedSession;
  response: AuthenticatedResponse & { recoveryCodes?: string[] };
}

export type LoginResult =
  | { status: "authenticated"; authentication: AuthenticationResult }
  | { status: "mfa_required"; response: MfaRequiredResponse };

@Injectable()
export class AuthService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly crypto: AuthCryptoService,
    private readonly rateLimit: AuthRateLimitService,
    private readonly events: AuthEventService,
    private readonly mail: AuthMailService,
  ) {}

  async login(
    input: LoginInput,
    context: AuthRequestContext,
  ): Promise<LoginResult> {
    const eventContext = { ...context, subject: input.email };
    await this.rateLimit.assertAllowed(AuthRateAction.LOGIN, eventContext);
    const user = await this.prisma.user.findUnique({
      where: { email: input.email },
      include: userAuthInclude,
    });
    const passwordValid = await this.crypto.verifyPassword(
      input.password,
      user?.passwordHash,
    );

    if (!user || !passwordValid || user.status !== UserStatus.ACTIVE) {
      await this.rateLimit.registerFailure(AuthRateAction.LOGIN, eventContext);
      await this.events.recordSafe({
        action: "LOGIN_PASSWORD",
        outcome: AuthEventOutcome.FAILURE,
        reason: "INVALID_CREDENTIALS",
        userId: user?.id,
        context: eventContext,
      });
      throw new UnauthorizedException({ code: ERROR_CODES.UNAUTHORIZED });
    }

    await this.rateLimit.reset(AuthRateAction.LOGIN, eventContext);
    if (this.userNeedsMfa(user)) {
      const enrollmentRequired = !user.mfaCredential?.confirmedAt;
      const challengeToken = await this.issueToken(
        user.id,
        enrollmentRequired ? AuthTokenKind.MFA_SETUP : AuthTokenKind.MFA_LOGIN,
        AUTH_POLICY.mfaChallengeSeconds,
      );
      await this.events.recordSafe({
        action: "LOGIN_PASSWORD",
        outcome: AuthEventOutcome.SUCCESS,
        reason: "MFA_REQUIRED",
        userId: user.id,
        context: eventContext,
      });
      return {
        status: "mfa_required",
        response: {
          status: "mfa_required",
          challengeToken,
          enrollmentRequired,
        },
      };
    }

    const authentication = await this.prisma.$transaction(async (transaction) => {
      await transaction.user.update({
        where: { id: user.id },
        data: {
          lastLoginAt: new Date(),
          ...(this.crypto.needsRehash(user.passwordHash!)
            ? { passwordHash: await this.crypto.hashPassword(input.password) }
            : {}),
        },
      });
      return this.createAuthentication(user.id, eventContext, null, transaction);
    });
    await this.events.recordSafe({
      action: "LOGIN",
      outcome: AuthEventOutcome.SUCCESS,
      userId: user.id,
      context: eventContext,
    });
    return { status: "authenticated", authentication };
  }

  async invite(
    actor: SessionAuthContext,
    input: InvitationInput,
    context: AuthRequestContext,
  ): Promise<InvitationResponse> {
    await this.assertTechnicalAdmin(actor, input.organizationId);
    const eventContext = { ...context, subject: input.email };
    const existing = await this.prisma.user.findUnique({
      where: { email: input.email },
      select: { id: true },
    });
    if (existing) {
      throw new ConflictException({ code: ERROR_CODES.USER_CONFLICT });
    }

    const created = await this.prisma.$transaction(async (transaction) => {
      const user = await transaction.user.create({
        data: {
          email: input.email,
          displayName: input.displayName,
          status: UserStatus.INVITED,
          memberships: {
            create: {
              organizationId: input.organizationId,
              roleAssignments: {
                create: input.roles.map((role) => ({
                  role: PRISMA_ROLE_BY_ROLE_ID[role],
                  scopeId: input.organizationId,
                })),
              },
            },
          },
        },
      });
      const token = await this.issueToken(
        user.id,
        AuthTokenKind.INVITATION,
        AUTH_POLICY.invitationSeconds,
        transaction,
      );
      return { user, token };
    });

    try {
      await this.mail.sendInvitation(created.user.email, created.token);
    } catch (error) {
      await this.prisma.$transaction(async (transaction) => {
        await transaction.authOneTimeToken.deleteMany({
          where: { userId: created.user.id },
        });
        await transaction.organizationMembership.deleteMany({
          where: { userId: created.user.id },
        });
        await transaction.user.delete({ where: { id: created.user.id } });
      });
      await this.events.recordSafe({
        action: "INVITATION_CREATE",
        outcome: AuthEventOutcome.FAILURE,
        reason: "MAIL_UNAVAILABLE",
        context: eventContext,
      });
      throw error;
    }

    await this.events.recordSafe({
      action: "INVITATION_CREATE",
      outcome: AuthEventOutcome.SUCCESS,
      userId: created.user.id,
      context: eventContext,
    });
    return { status: "invited", userId: created.user.id };
  }

  async acceptInvitation(
    input: AcceptInvitationInput,
    context: AuthRequestContext,
  ): Promise<LoginResult> {
    const token = await this.validToken(input.token, AuthTokenKind.INVITATION);
    if (token.user.status !== UserStatus.INVITED) throw this.invalidToken();
    const eventContext = { ...context, subject: token.user.email };
    const passwordHash = await this.crypto.hashPassword(input.password);
    const needsMfa = this.userNeedsMfa(token.user);

    const result: LoginResult = await this.prisma.$transaction(
      async (transaction) => {
        await this.consumeToken(transaction, token);
        await transaction.user.update({
          where: { id: token.userId },
          data: {
            status: UserStatus.ACTIVE,
            passwordHash,
            authVersion: { increment: 1 },
            ...(needsMfa ? {} : { lastLoginAt: new Date() }),
          },
        });
        await transaction.authOneTimeToken.updateMany({
          where: {
            userId: token.userId,
            kind: AuthTokenKind.INVITATION,
            consumedAt: null,
          },
          data: { consumedAt: new Date() },
        });

        if (needsMfa) {
          const challengeToken = await this.issueToken(
            token.userId,
            AuthTokenKind.MFA_SETUP,
            AUTH_POLICY.mfaChallengeSeconds,
            transaction,
          );
          return {
            status: "mfa_required",
            response: {
              status: "mfa_required",
              challengeToken,
              enrollmentRequired: true,
            },
          };
        }

        const authentication = await this.createAuthentication(
          token.userId,
          eventContext,
          null,
          transaction,
        );
        return { status: "authenticated", authentication };
      },
    );
    await this.events.recordSafe({
      action: "INVITATION_ACCEPT",
      outcome: AuthEventOutcome.SUCCESS,
      reason: needsMfa ? "MFA_REQUIRED" : undefined,
      userId: token.userId,
      context: eventContext,
    });
    return result;
  }

  async requestPasswordReset(
    input: PasswordResetRequestInput,
    context: AuthRequestContext,
  ): Promise<void> {
    const eventContext = { ...context, subject: input.email };
    await this.rateLimit.assertAllowed(AuthRateAction.PASSWORD_RESET, eventContext);
    await this.rateLimit.registerFailure(
      AuthRateAction.PASSWORD_RESET,
      eventContext,
    );
    const user = await this.prisma.user.findFirst({
      where: { email: input.email, status: UserStatus.ACTIVE },
    });
    if (!user) {
      await this.events.recordSafe({
        action: "PASSWORD_RESET_REQUEST",
        outcome: AuthEventOutcome.SUCCESS,
        reason: "ACCEPTED",
        context: eventContext,
      });
      return;
    }

    const token = await this.prisma.$transaction(async (transaction) => {
      await transaction.authOneTimeToken.updateMany({
        where: {
          userId: user.id,
          kind: AuthTokenKind.PASSWORD_RESET,
          consumedAt: null,
        },
        data: { consumedAt: new Date() },
      });
      return this.issueToken(
        user.id,
        AuthTokenKind.PASSWORD_RESET,
        AUTH_POLICY.passwordResetSeconds,
        transaction,
      );
    });

    try {
      await this.mail.sendPasswordReset(user.email, token);
      await this.events.recordSafe({
        action: "PASSWORD_RESET_REQUEST",
        outcome: AuthEventOutcome.SUCCESS,
        userId: user.id,
        context: eventContext,
      });
    } catch {
      await this.events.recordSafe({
        action: "PASSWORD_RESET_REQUEST",
        outcome: AuthEventOutcome.FAILURE,
        reason: "MAIL_UNAVAILABLE",
        userId: user.id,
        context: eventContext,
      });
    }
  }

  async confirmPasswordReset(
    input: PasswordResetConfirmInput,
    context: AuthRequestContext,
  ): Promise<void> {
    const token = await this.validToken(
      input.token,
      AuthTokenKind.PASSWORD_RESET,
    );
    if (token.user.status !== UserStatus.ACTIVE) throw this.invalidToken();
    const passwordHash = await this.crypto.hashPassword(input.password);
    const eventContext = { ...context, subject: token.user.email };

    await this.prisma.$transaction(async (transaction) => {
      await this.consumeToken(transaction, token);
      await transaction.user.update({
        where: { id: token.userId },
        data: {
          passwordHash,
          authVersion: { increment: 1 },
        },
      });
      await transaction.authSession.updateMany({
        where: { userId: token.userId, revokedAt: null },
        data: { revokedAt: new Date(), revocationReason: "PASSWORD_RESET" },
      });
      await transaction.authOneTimeToken.updateMany({
        where: { userId: token.userId, consumedAt: null },
        data: { consumedAt: new Date() },
      });
    });
    await this.events.recordSafe({
      action: "PASSWORD_RESET_CONFIRM",
      outcome: AuthEventOutcome.SUCCESS,
      userId: token.userId,
      context: eventContext,
    });
  }

  async setupMfa(
    input: MfaSetupInput,
    context: AuthRequestContext,
  ): Promise<MfaSetupResponse> {
    const token = await this.validToken(
      input.challengeToken,
      AuthTokenKind.MFA_SETUP,
    );
    if (token.user.mfaCredential?.confirmedAt) throw this.invalidToken();
    const secret = this.crypto.createTotpSecret();
    await this.prisma.mfaCredential.upsert({
      where: { userId: token.userId },
      create: {
        userId: token.userId,
        secretCiphertext: this.crypto.encryptSecret(secret),
      },
      update: {
        secretCiphertext: this.crypto.encryptSecret(secret),
        confirmedAt: null,
        lastAcceptedTimeStep: null,
      },
    });
    await this.events.recordSafe({
      action: "MFA_SETUP_START",
      outcome: AuthEventOutcome.SUCCESS,
      userId: token.userId,
      context: { ...context, subject: token.userId },
    });
    return {
      status: "mfa_setup",
      secret,
      otpauthUrl: this.crypto.createTotpUri(token.user.email, secret),
    };
  }

  async confirmMfaSetup(
    input: MfaVerifyInput,
    context: AuthRequestContext,
  ): Promise<AuthenticationResult> {
    const token = await this.validToken(
      input.challengeToken,
      AuthTokenKind.MFA_SETUP,
    );
    const eventContext = { ...context, subject: token.userId };
    await this.rateLimit.assertAllowed(AuthRateAction.MFA, eventContext);
    const credential = token.user.mfaCredential;
    if (!credential || credential.confirmedAt) throw this.invalidToken();
    const secret = this.crypto.decryptSecret(credential.secretCiphertext);
    const verification = await this.crypto.verifyTotp(secret, input.code);
    if (!verification.valid || !("timeStep" in verification)) {
      return this.rejectMfa(token, eventContext);
    }

    const recoveryCodes = this.crypto.generateRecoveryCodes();
    const authentication = await this.prisma.$transaction(async (transaction) => {
      await this.consumeToken(transaction, token);
      const accepted = await transaction.mfaCredential.updateMany({
        where: {
          id: credential.id,
          confirmedAt: null,
          OR: [
            { lastAcceptedTimeStep: null },
            { lastAcceptedTimeStep: { lt: verification.timeStep } },
          ],
        },
        data: {
          confirmedAt: new Date(),
          lastAcceptedTimeStep: verification.timeStep,
        },
      });
      if (accepted.count !== 1) {
        throw new UnauthorizedException({ code: ERROR_CODES.MFA_INVALID });
      }
      await transaction.mfaRecoveryCode.deleteMany({
        where: { userId: token.userId },
      });
      await transaction.mfaRecoveryCode.createMany({
        data: recoveryCodes.map((code) => ({
          userId: token.userId,
          codeHash: this.crypto.hashToken(
            this.crypto.normalizeRecoveryCode(code),
          ),
        })),
      });
      await transaction.authOneTimeToken.updateMany({
        where: {
          userId: token.userId,
          kind: AuthTokenKind.MFA_SETUP,
          consumedAt: null,
        },
        data: { consumedAt: new Date() },
      });
      return this.createAuthentication(
        token.userId,
        eventContext,
        new Date(),
        transaction,
        recoveryCodes,
      );
    });
    await this.rateLimit.reset(AuthRateAction.MFA, eventContext);
    await this.events.recordSafe({
      action: "MFA_SETUP_CONFIRM",
      outcome: AuthEventOutcome.SUCCESS,
      userId: token.userId,
      context: eventContext,
    });
    return authentication;
  }

  async verifyMfa(
    input: MfaVerifyInput,
    context: AuthRequestContext,
  ): Promise<AuthenticationResult> {
    const token = await this.validToken(
      input.challengeToken,
      AuthTokenKind.MFA_LOGIN,
    );
    const eventContext = { ...context, subject: token.userId };
    await this.rateLimit.assertAllowed(AuthRateAction.MFA, eventContext);
    const credential = token.user.mfaCredential;
    if (!credential?.confirmedAt) throw this.invalidToken();
    const secret = this.crypto.decryptSecret(credential.secretCiphertext);
    const verification = await this.crypto.verifyTotp(
      secret,
      input.code,
      credential.lastAcceptedTimeStep,
    );
    if (!verification.valid || !("timeStep" in verification)) {
      return this.rejectMfa(token, eventContext);
    }

    const authentication = await this.prisma.$transaction(async (transaction) => {
      await this.consumeToken(transaction, token);
      const accepted = await transaction.mfaCredential.updateMany({
        where: {
          id: credential.id,
          confirmedAt: { not: null },
          OR: [
            { lastAcceptedTimeStep: null },
            { lastAcceptedTimeStep: { lt: verification.timeStep } },
          ],
        },
        data: { lastAcceptedTimeStep: verification.timeStep },
      });
      if (accepted.count !== 1) {
        throw new UnauthorizedException({ code: ERROR_CODES.MFA_INVALID });
      }
      await transaction.user.update({
        where: { id: token.userId },
        data: { lastLoginAt: new Date() },
      });
      return this.createAuthentication(
        token.userId,
        eventContext,
        new Date(),
        transaction,
      );
    });
    await this.rateLimit.reset(AuthRateAction.MFA, eventContext);
    await this.events.recordSafe({
      action: "LOGIN_MFA",
      outcome: AuthEventOutcome.SUCCESS,
      userId: token.userId,
      context: eventContext,
    });
    return authentication;
  }

  async recoverMfa(
    input: MfaRecoveryInput,
    context: AuthRequestContext,
  ): Promise<AuthenticationResult> {
    const token = await this.validToken(
      input.challengeToken,
      AuthTokenKind.MFA_LOGIN,
    );
    const eventContext = { ...context, subject: token.userId };
    await this.rateLimit.assertAllowed(AuthRateAction.MFA, eventContext);
    const codeHash = this.crypto.hashToken(
      this.crypto.normalizeRecoveryCode(input.recoveryCode),
    );
    const recoveryCode = await this.prisma.mfaRecoveryCode.findUnique({
      where: { userId_codeHash: { userId: token.userId, codeHash } },
    });
    if (!recoveryCode || recoveryCode.usedAt) {
      return this.rejectMfa(token, eventContext);
    }

    const authentication = await this.prisma.$transaction(async (transaction) => {
      const consumed = await transaction.mfaRecoveryCode.updateMany({
        where: { id: recoveryCode.id, usedAt: null },
        data: { usedAt: new Date() },
      });
      if (consumed.count !== 1) {
        throw new UnauthorizedException({ code: ERROR_CODES.MFA_INVALID });
      }
      await this.consumeToken(transaction, token);
      await transaction.user.update({
        where: { id: token.userId },
        data: { lastLoginAt: new Date() },
      });
      return this.createAuthentication(
        token.userId,
        eventContext,
        new Date(),
        transaction,
      );
    });
    await this.rateLimit.reset(AuthRateAction.MFA, eventContext);
    await this.events.recordSafe({
      action: "LOGIN_MFA_RECOVERY",
      outcome: AuthEventOutcome.SUCCESS,
      userId: token.userId,
      context: eventContext,
    });
    return authentication;
  }

  async refresh(
    auth: SessionAuthContext,
    context: AuthRequestContext,
  ): Promise<AuthenticationResult> {
    const token = this.crypto.createOpaqueToken();
    const csrfToken = this.crypto.createOpaqueToken();
    const now = new Date();
    const expiresAt = new Date(
      Math.min(
        now.getTime() + AUTH_POLICY.sessionIdleSeconds * 1000,
        auth.session.absoluteExpiresAt.getTime(),
      ),
    );
    const rotated = await this.prisma.authSession.updateMany({
      where: {
        id: auth.session.id,
        tokenHash: this.crypto.hashToken(auth.sessionToken),
        revokedAt: null,
        expiresAt: { gt: now },
        absoluteExpiresAt: { gt: now },
      },
      data: {
        tokenHash: this.crypto.hashToken(token),
        csrfTokenHash: this.crypto.hashToken(csrfToken),
        expiresAt,
        lastSeenAt: now,
      },
    });
    if (rotated.count !== 1) {
      throw new UnauthorizedException({ code: ERROR_CODES.UNAUTHORIZED });
    }
    const session = await this.prisma.authSession.findUniqueOrThrow({
      where: { id: auth.session.id },
      include: authSessionInclude,
    });
    await this.events.recordSafe({
      action: "SESSION_REFRESH",
      outcome: AuthEventOutcome.SUCCESS,
      userId: session.userId,
      context: { ...context, subject: session.userId },
    });
    return this.authenticationResult({ token, csrfToken, session });
  }

  me(auth: SessionAuthContext): AuthMeResponse {
    return {
      user: {
        id: auth.session.user.id,
        email: auth.session.user.email,
        displayName: auth.session.user.displayName,
        status: auth.session.user.status,
      },
      memberships: auth.session.user.memberships
        .filter(
          (membership) =>
            membership.status === MembershipStatus.ACTIVE &&
            !membership.archivedAt,
        )
        .map(presentMembership),
      session: presentSession(auth.session),
    };
  }

  async listSessions(auth: SessionAuthContext): Promise<SessionsResponse> {
    const now = new Date();
    const sessions = await this.prisma.authSession.findMany({
      where: {
        userId: auth.session.userId,
        authVersion: auth.session.user.authVersion,
        revokedAt: null,
        expiresAt: { gt: now },
        absoluteExpiresAt: { gt: now },
      },
      orderBy: { createdAt: "desc" },
    });
    return {
      sessions: sessions.map((session) =>
        presentSession(session, auth.session.id),
      ),
    };
  }

  async logout(
    auth: SessionAuthContext,
    context: AuthRequestContext,
  ): Promise<void> {
    await this.prisma.authSession.updateMany({
      where: { id: auth.session.id, revokedAt: null },
      data: { revokedAt: new Date(), revocationReason: "USER_LOGOUT" },
    });
    await this.events.recordSafe({
      action: "LOGOUT",
      outcome: AuthEventOutcome.SUCCESS,
      userId: auth.session.userId,
      context: { ...context, subject: auth.session.userId },
    });
  }

  async revokeSession(
    auth: SessionAuthContext,
    sessionId: string,
    context: AuthRequestContext,
  ): Promise<boolean> {
    const target = await this.prisma.authSession.findFirst({
      where: { id: sessionId, userId: auth.session.userId },
    });
    if (!target) throw new NotFoundException({ code: ERROR_CODES.NOT_FOUND });
    await this.prisma.authSession.updateMany({
      where: { id: target.id, revokedAt: null },
      data: { revokedAt: new Date(), revocationReason: "USER_REVOCATION" },
    });
    await this.events.recordSafe({
      action: "SESSION_REVOKE",
      outcome: AuthEventOutcome.SUCCESS,
      userId: auth.session.userId,
      context: { ...context, subject: auth.session.userId },
    });
    return target.id === auth.session.id;
  }

  async disableUser(
    actor: SessionAuthContext,
    userId: string,
    input: OrganizationActionInput,
    context: AuthRequestContext,
  ): Promise<void> {
    await this.assertTechnicalAdmin(actor, input.organizationId);
    if (actor.session.userId === userId) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
    await this.assertUserInOrganization(userId, input.organizationId);
    await this.prisma.$transaction(async (transaction) => {
      await transaction.user.update({
        where: { id: userId },
        data: {
          status: UserStatus.DISABLED,
          authVersion: { increment: 1 },
        },
      });
      await transaction.authSession.updateMany({
        where: { userId, revokedAt: null },
        data: { revokedAt: new Date(), revocationReason: "ACCOUNT_DISABLED" },
      });
      await transaction.authOneTimeToken.updateMany({
        where: { userId, consumedAt: null },
        data: { consumedAt: new Date() },
      });
    });
    await this.events.recordSafe({
      action: "ACCOUNT_DISABLE",
      outcome: AuthEventOutcome.SUCCESS,
      userId,
      context: { ...context, subject: userId },
    });
  }

  async revokeUserSessions(
    actor: SessionAuthContext,
    userId: string,
    input: OrganizationActionInput,
    context: AuthRequestContext,
  ): Promise<void> {
    await this.assertTechnicalAdmin(actor, input.organizationId);
    await this.assertUserInOrganization(userId, input.organizationId);
    await this.prisma.authSession.updateMany({
      where: { userId, revokedAt: null },
      data: { revokedAt: new Date(), revocationReason: "ADMIN_REVOCATION" },
    });
    await this.events.recordSafe({
      action: "SESSION_REVOKE_ALL",
      outcome: AuthEventOutcome.SUCCESS,
      userId,
      context: { ...context, subject: userId },
    });
  }

  private async createAuthentication(
    userId: string,
    context: AuthRequestContext,
    mfaVerifiedAt: Date | null,
    db: Db,
    recoveryCodes?: string[],
  ): Promise<AuthenticationResult> {
    const user = await db.user.findUniqueOrThrow({ where: { id: userId } });
    const now = new Date();
    const token = this.crypto.createOpaqueToken();
    const csrfToken = this.crypto.createOpaqueToken();
    const session = await db.authSession.create({
      data: {
        userId,
        tokenHash: this.crypto.hashToken(token),
        csrfTokenHash: this.crypto.hashToken(csrfToken),
        authVersion: user.authVersion,
        mfaVerifiedAt,
        expiresAt: new Date(
          now.getTime() + AUTH_POLICY.sessionIdleSeconds * 1000,
        ),
        absoluteExpiresAt: new Date(
          now.getTime() + AUTH_POLICY.sessionAbsoluteSeconds * 1000,
        ),
        userAgent: context.userAgent?.slice(0, 512),
      },
      include: authSessionInclude,
    });
    return this.authenticationResult(
      { token, csrfToken, session },
      recoveryCodes,
    );
  }

  private authenticationResult(
    issued: IssuedSession,
    recoveryCodes?: string[],
  ): AuthenticationResult {
    return {
      issued,
      response: presentAuthenticated(issued.session, recoveryCodes),
    };
  }

  private async issueToken(
    userId: string,
    kind: AuthTokenKind,
    ttlSeconds: number,
    db: Db = this.prisma,
  ): Promise<string> {
    const token = this.crypto.createOpaqueToken();
    await db.authOneTimeToken.create({
      data: {
        userId,
        kind,
        tokenHash: this.crypto.hashToken(token),
        expiresAt: new Date(Date.now() + ttlSeconds * 1000),
      },
    });
    return token;
  }

  private async validToken(
    rawToken: string,
    kind: AuthTokenKind,
  ): Promise<TokenWithUser> {
    const token = await this.prisma.authOneTimeToken.findUnique({
      where: { tokenHash: this.crypto.hashToken(rawToken) },
      include: { user: { include: userAuthInclude } },
    });
    if (
      !token ||
      token.kind !== kind ||
      token.consumedAt ||
      token.expiresAt <= new Date() ||
      token.user.status === UserStatus.DISABLED ||
      ((kind === AuthTokenKind.MFA_LOGIN || kind === AuthTokenKind.MFA_SETUP) &&
        token.user.status !== UserStatus.ACTIVE)
    ) {
      throw this.invalidToken();
    }
    return token;
  }

  private async consumeToken(db: Db, token: TokenWithUser): Promise<void> {
    const consumed = await db.authOneTimeToken.updateMany({
      where: { id: token.id, consumedAt: null },
      data: { consumedAt: new Date() },
    });
    if (consumed.count !== 1) throw this.invalidToken();
  }

  private async rejectMfa(
    token: TokenWithUser,
    context: AuthRequestContext,
  ): Promise<never> {
    await this.rateLimit.registerFailure(AuthRateAction.MFA, context);
    const updated = await this.prisma.authOneTimeToken.update({
      where: { id: token.id },
      data: { attemptCount: { increment: 1 } },
    });
    if (updated.attemptCount >= AUTH_POLICY.mfaMaxAttempts) {
      await this.prisma.authOneTimeToken.update({
        where: { id: token.id },
        data: { consumedAt: new Date() },
      });
    }
    await this.events.recordSafe({
      action: "LOGIN_MFA",
      outcome: AuthEventOutcome.FAILURE,
      reason: "INVALID_MFA",
      userId: token.userId,
      context,
    });
    throw new UnauthorizedException({ code: ERROR_CODES.MFA_INVALID });
  }

  private userNeedsMfa(user: UserWithAuth): boolean {
    const roles = user.memberships
      .filter(
        (membership) =>
          membership.status === MembershipStatus.ACTIVE && !membership.archivedAt,
      )
      .flatMap((membership) =>
        membership.roleAssignments
          .filter((assignment) => !assignment.archivedAt)
          .map((assignment) => assignment.role),
      );
    return requiresMfa(roles) || Boolean(user.mfaCredential?.confirmedAt);
  }

  private async assertTechnicalAdmin(
    actor: SessionAuthContext,
    organizationId: string,
  ): Promise<void> {
    const membership = await this.prisma.organizationMembership.findFirst({
      where: {
        organizationId,
        userId: actor.session.userId,
        status: MembershipStatus.ACTIVE,
        archivedAt: null,
        roleAssignments: {
          some: { role: Role.TECHNICAL_ADMIN, archivedAt: null },
        },
      },
      select: { id: true },
    });
    if (!membership) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
  }

  private async assertUserInOrganization(
    userId: string,
    organizationId: string,
  ): Promise<void> {
    const membership = await this.prisma.organizationMembership.findFirst({
      where: { userId, organizationId, archivedAt: null },
      select: { id: true },
    });
    if (!membership) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
  }

  private invalidToken(): BadRequestException {
    return new BadRequestException({ code: ERROR_CODES.TOKEN_INVALID });
  }
}
