import { randomUUID, randomBytes, createHash } from "node:crypto";
import { NestFastifyApplication } from "@nestjs/platform-fastify";
import { FastifyAdapter } from "@nestjs/platform-fastify";
import { Test } from "@nestjs/testing";
import { AppModule } from "../../src/app.module.js";
import { configureApp } from "../../src/app.setup.js";
import {
  MembershipStatus,
  PrismaClient,
  Role,
  UserStatus,
} from "../../src/database/client.js";
import { AuthMailService } from "../../src/modules/auth/auth.mail.js";

export const TEST_AUTH_KEY = Buffer.alloc(32, 7).toString("base64");
export const TEST_PASSWORD = "Correct-Horse-2026!";

export class AuthMailStub {
  readonly invitations: Array<{ email: string; token: string }> = [];
  readonly passwordResets: Array<{ email: string; token: string }> = [];

  async sendInvitation(email: string, token: string): Promise<void> {
    this.invitations.push({ email, token });
  }

  async sendPasswordReset(email: string, token: string): Promise<void> {
    this.passwordResets.push({ email, token });
  }
}

export async function createAuthApp(mail: AuthMailStub) {
  const moduleRef = await Test.createTestingModule({ imports: [AppModule] })
    .overrideProvider(AuthMailService)
    .useValue(mail)
    .compile();
  const app = moduleRef.createNestApplication<NestFastifyApplication>(
    new FastifyAdapter({ genReqId: () => randomUUID() }),
    { logger: false },
  );
  await configureApp(app);
  await app.init();
  await app.getHttpAdapter().getInstance().ready();
  return app;
}

export async function createOrganization(
  prisma: PrismaClient,
  code = "TEST",
) {
  return prisma.organization.create({
    data: { code, name: "Établissement de test" },
  });
}

export async function createUser(
  prisma: PrismaClient,
  input: {
    organizationId: string;
    email: string;
    passwordHash: string;
    role?: Role;
    status?: UserStatus;
  },
) {
  return prisma.user.create({
    data: {
      email: input.email,
      displayName: "Utilisateur de test",
      passwordHash: input.passwordHash,
      status: input.status ?? UserStatus.ACTIVE,
      memberships: {
        create: {
          organizationId: input.organizationId,
          status: MembershipStatus.ACTIVE,
          roleAssignments: {
            create: { role: input.role ?? Role.TEACHER },
          },
        },
      },
    },
  });
}

export async function createSession(
  prisma: PrismaClient,
  userId: string,
  mfaVerified = true,
) {
  const user = await prisma.user.findUniqueOrThrow({ where: { id: userId } });
  const token = randomBytes(32).toString("base64url");
  const csrfToken = randomBytes(32).toString("base64url");
  const now = new Date();
  const session = await prisma.authSession.create({
    data: {
      userId,
      tokenHash: createHash("sha256").update(token).digest("hex"),
      csrfTokenHash: createHash("sha256").update(csrfToken).digest("hex"),
      authVersion: user.authVersion,
      mfaVerifiedAt: mfaVerified ? now : null,
      expiresAt: new Date(now.getTime() + 60 * 60 * 1000),
      absoluteExpiresAt: new Date(now.getTime() + 24 * 60 * 60 * 1000),
    },
  });
  return { id: session.id, token, csrfToken };
}

export function sessionHeaders(
  session: { token: string; csrfToken: string },
  withCsrf = true,
) {
  return {
    cookie: `lavision_session=${session.token}; lavision_csrf=${session.csrfToken}`,
    ...(withCsrf ? { "x-csrf-token": session.csrfToken } : {}),
  };
}

export function responseCookies(headers: Record<string, unknown>) {
  const raw = headers["set-cookie"];
  const values = Array.isArray(raw) ? raw : raw ? [raw] : [];
  const cookies = new Map<string, string>();
  for (const value of values) {
    const [pair] = String(value).split(";");
    const separator = pair!.indexOf("=");
    cookies.set(pair!.slice(0, separator), pair!.slice(separator + 1));
  }
  return {
    token: cookies.get("lavision_session") ?? "",
    csrfToken: cookies.get("lavision_csrf") ?? "",
    raw: values.map(String).join("\n"),
  };
}
