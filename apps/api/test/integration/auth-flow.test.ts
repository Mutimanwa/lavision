import { createHash } from "node:crypto";
import type { NestFastifyApplication } from "@nestjs/platform-fastify";
import { generate } from "otplib";
import { afterAll, beforeAll, beforeEach, describe, expect, it } from "vitest";
import {
  AuthTokenKind,
  PrismaClient,
  Role,
  UserStatus,
} from "../../src/database/client.js";
import { AuthConfigService } from "../../src/modules/auth/auth.config.js";
import { AuthCryptoService } from "../../src/modules/auth/auth.crypto.js";
import {
  createAuthApp,
  createOrganization,
  createSession,
  createUser,
  responseCookies,
  sessionHeaders,
  AuthMailStub,
  TEST_AUTH_KEY,
  TEST_PASSWORD,
} from "./auth-helpers.js";
import { createTestPrismaClient, resetCoreDatabase } from "./helpers.js";

let app: NestFastifyApplication;
let prisma: PrismaClient;
let mail: AuthMailStub;
let passwordHash: string;

function http() {
  return app.getHttpAdapter().getInstance();
}

async function login(email: string, password = TEST_PASSWORD) {
  const response = await http().inject({
    method: "POST",
    url: "/api/v1/auth/login",
    payload: { email, password },
  });
  return { response, cookies: responseCookies(response.headers) };
}

async function createAdmin(organizationId: string) {
  const admin = await createUser(prisma, {
    organizationId,
    email: "admin@example.invalid",
    passwordHash,
    role: Role.TECHNICAL_ADMIN,
  });
  const session = await createSession(prisma, admin.id);
  return { admin, session };
}

describe("authentification, sessions et MFA", () => {
  beforeAll(async () => {
    process.env.AUTH_ENCRYPTION_KEY = TEST_AUTH_KEY;
    process.env.AUTH_COOKIE_SECURE = "false";
    process.env.NODE_ENV = "test";
    mail = new AuthMailStub();
    app = await createAuthApp(mail);
    prisma = createTestPrismaClient();
    passwordHash = await new AuthCryptoService(
      new AuthConfigService(),
    ).hashPassword(TEST_PASSWORD);
  });

  beforeEach(async () => {
    await resetCoreDatabase(prisma);
    mail.invitations.length = 0;
    mail.passwordResets.length = 0;
  });

  afterAll(async () => {
    await app.close();
    await prisma.$disconnect();
  });

  it("ouvre une session avec cookies sécurisés, impose CSRF et déconnecte", async () => {
    const organization = await createOrganization(prisma);
    await createUser(prisma, {
      organizationId: organization.id,
      email: "teacher@example.invalid",
      passwordHash,
    });

    const { response, cookies } = await login("teacher@example.invalid");
    expect(response.statusCode).toBe(200);
    expect(response.json()).toMatchObject({
      status: "authenticated",
      user: { email: "teacher@example.invalid" },
    });
    expect(cookies.token).toHaveLength(43);
    expect(cookies.csrfToken).toHaveLength(43);
    const setCookies = String(response.headers["set-cookie"]);
    expect(setCookies).toContain("HttpOnly");
    expect(setCookies).toContain("SameSite=Strict");

    const me = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(cookies, false),
    });
    expect(me.statusCode).toBe(200);
    expect(me.json().memberships[0].roles).toEqual(["teacher"]);

    const missingCsrf = await http().inject({
      method: "POST",
      url: "/api/v1/auth/logout",
      headers: sessionHeaders(cookies, false),
    });
    expect(missingCsrf.statusCode).toBe(403);
    expect(missingCsrf.json().code).toBe("CSRF_INVALID");

    const logout = await http().inject({
      method: "POST",
      url: "/api/v1/auth/logout",
      headers: sessionHeaders(cookies),
    });
    expect(logout.statusCode).toBe(204);
    const afterLogout = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(cookies, false),
    });
    expect(afterLogout.statusCode).toBe(401);
    expect(
      await prisma.authSession.findUnique({ where: { id: response.json().session.id } }),
    ).toMatchObject({ revocationReason: "USER_LOGOUT" });
  });

  it("tourne les jetons et invalide immédiatement l’ancienne session", async () => {
    const organization = await createOrganization(prisma);
    await createUser(prisma, {
      organizationId: organization.id,
      email: "rotation@example.invalid",
      passwordHash,
    });
    const first = await login("rotation@example.invalid");
    const refreshed = await http().inject({
      method: "POST",
      url: "/api/v1/auth/refresh",
      headers: sessionHeaders(first.cookies),
    });
    expect(refreshed.statusCode).toBe(200);
    const secondCookies = responseCookies(refreshed.headers);
    expect(secondCookies.token).not.toBe(first.cookies.token);
    expect(secondCookies.csrfToken).not.toBe(first.cookies.csrfToken);

    const oldSession = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(first.cookies, false),
    });
    expect(oldSession.statusCode).toBe(401);
    const current = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(secondCookies, false),
    });
    expect(current.statusCode).toBe(200);
    const sessions = await http().inject({
      method: "GET",
      url: "/api/v1/auth/sessions",
      headers: sessionHeaders(secondCookies, false),
    });
    expect(sessions.statusCode).toBe(200);
    expect(sessions.json().sessions).toHaveLength(1);
    expect(sessions.json().sessions[0].current).toBe(true);
    const stored = await prisma.authSession.findUniqueOrThrow({
      where: { id: refreshed.json().session.id },
    });
    expect(stored.tokenHash).toMatch(/^[a-f0-9]{64}$/);
    expect(stored.tokenHash).not.toBe(secondCookies.token);
  });

  it("révoque une session expirée avec un code stable", async () => {
    const organization = await createOrganization(prisma);
    await createUser(prisma, {
      organizationId: organization.id,
      email: "expiration@example.invalid",
      passwordHash,
    });
    const { response, cookies } = await login("expiration@example.invalid");
    await prisma.authSession.update({
      where: { id: response.json().session.id },
      data: { expiresAt: new Date(Date.now() - 1000) },
    });

    const expired = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(cookies, false),
    });
    expect(expired.statusCode).toBe(401);
    expect(expired.json().code).toBe("SESSION_EXPIRED");
    expect(
      await prisma.authSession.findUnique({ where: { id: response.json().session.id } }),
    ).toMatchObject({ revocationReason: "EXPIRED" });
  });

  it("récupère un compte, révoque les sessions et consomme le lien une seule fois", async () => {
    const organization = await createOrganization(prisma);
    const user = await createUser(prisma, {
      organizationId: organization.id,
      email: "recovery@example.invalid",
      passwordHash,
    });
    const old = await login(user.email);
    const request = await http().inject({
      method: "POST",
      url: "/api/v1/auth/password-reset/request",
      payload: { email: user.email },
    });
    expect(request.statusCode).toBe(202);
    expect(mail.passwordResets).toHaveLength(1);
    const unknown = await http().inject({
      method: "POST",
      url: "/api/v1/auth/password-reset/request",
      payload: { email: "unknown@example.invalid" },
    });
    expect(unknown.statusCode).toBe(202);
    expect(mail.passwordResets).toHaveLength(1);

    const nextPassword = "Nouvelle-phrase-2026!";
    const confirmed = await http().inject({
      method: "POST",
      url: "/api/v1/auth/password-reset/confirm",
      payload: {
        token: mail.passwordResets[0]!.token,
        password: nextPassword,
      },
    });
    expect(confirmed.statusCode).toBe(204);
    const oldSession = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(old.cookies, false),
    });
    expect(oldSession.statusCode).toBe(401);
    const replay = await http().inject({
      method: "POST",
      url: "/api/v1/auth/password-reset/confirm",
      payload: {
        token: mail.passwordResets[0]!.token,
        password: nextPassword,
      },
    });
    expect(replay.statusCode).toBe(400);
    expect(replay.json().code).toBe("TOKEN_INVALID");
    expect((await login(user.email)).response.statusCode).toBe(401);
    expect((await login(user.email, nextPassword)).response.statusCode).toBe(200);
    const stored = await prisma.user.findUniqueOrThrow({ where: { id: user.id } });
    expect(stored.passwordHash).toContain("$argon2id$");
    expect(stored.passwordHash).not.toContain(nextPassword);
  });

  it("impose SEC-18 à l’invitation direction et protège TOTP et récupération", async () => {
    const organization = await createOrganization(prisma);
    const { session: adminSession } = await createAdmin(organization.id);
    const invited = await http().inject({
      method: "POST",
      url: "/api/v1/auth/admin/invitations",
      headers: sessionHeaders(adminSession),
      payload: {
        organizationId: organization.id,
        email: "direction@example.invalid",
        displayName: "Direction Test",
        roles: ["direction"],
      },
    });
    expect(invited.statusCode).toBe(201);
    const invitationToken = mail.invitations[0]!.token;
    const accepted = await http().inject({
      method: "POST",
      url: "/api/v1/auth/invitations/accept",
      payload: { token: invitationToken, password: TEST_PASSWORD },
    });
    expect(accepted.statusCode).toBe(202);
    expect(accepted.json()).toMatchObject({
      status: "mfa_required",
      enrollmentRequired: true,
    });
    expect(responseCookies(accepted.headers).token).toBe("");
    const invitedUser = await prisma.user.findUniqueOrThrow({
      where: { email: "direction@example.invalid" },
    });
    expect(await prisma.authSession.count({ where: { userId: invitedUser.id } })).toBe(0);

    const challenge = accepted.json().challengeToken as string;
    const setup = await http().inject({
      method: "POST",
      url: "/api/v1/auth/mfa/setup",
      payload: { challengeToken: challenge },
    });
    expect(setup.statusCode).toBe(200);
    const secret = setup.json().secret as string;
    const code = await generate({ secret });
    const confirmed = await http().inject({
      method: "POST",
      url: "/api/v1/auth/mfa/setup/confirm",
      payload: { challengeToken: challenge, code },
    });
    expect(confirmed.statusCode).toBe(200);
    expect(confirmed.json().recoveryCodes).toHaveLength(10);
    const mfaCookies = responseCookies(confirmed.headers);
    const credential = await prisma.mfaCredential.findUniqueOrThrow({
      where: { userId: invitedUser.id },
    });
    expect(credential.secretCiphertext).not.toContain(secret);
    expect(credential.confirmedAt).not.toBeNull();
    const recoveryCodes = confirmed.json().recoveryCodes as string[];
    expect(await prisma.mfaRecoveryCode.count({ where: { userId: invitedUser.id } })).toBe(10);
    expect(
      JSON.stringify(await prisma.mfaRecoveryCode.findMany({ where: { userId: invitedUser.id } })),
    ).not.toContain(recoveryCodes[0]);

    const me = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(mfaCookies, false),
    });
    expect(me.statusCode).toBe(200);
    expect(me.json().memberships[0].roles).toEqual(["direction"]);
    await http().inject({
      method: "POST",
      url: "/api/v1/auth/logout",
      headers: sessionHeaders(mfaCookies),
    });

    const secondLogin = await login(invitedUser.email);
    expect(secondLogin.response.statusCode).toBe(202);
    const loginChallenge = secondLogin.response.json().challengeToken as string;
    const replay = await http().inject({
      method: "POST",
      url: "/api/v1/auth/mfa/verify",
      payload: { challengeToken: loginChallenge, code },
    });
    expect(replay.statusCode).toBe(401);
    expect(replay.json().code).toBe("MFA_INVALID");

    const recovered = await http().inject({
      method: "POST",
      url: "/api/v1/auth/mfa/recovery",
      payload: { challengeToken: loginChallenge, recoveryCode: recoveryCodes[0] },
    });
    expect(recovered.statusCode).toBe(200);
    const recoveredCookies = responseCookies(recovered.headers);
    await http().inject({
      method: "POST",
      url: "/api/v1/auth/logout",
      headers: sessionHeaders(recoveredCookies),
    });
    const thirdLogin = await login(invitedUser.email);
    const reusedRecovery = await http().inject({
      method: "POST",
      url: "/api/v1/auth/mfa/recovery",
      payload: {
        challengeToken: thirdLogin.response.json().challengeToken,
        recoveryCode: recoveryCodes[0],
      },
    });
    expect(reusedRecovery.statusCode).toBe(401);
  });

  it("accepte une invitation enseignant sans exiger de MFA", async () => {
    const organization = await createOrganization(prisma);
    const { session: adminSession } = await createAdmin(organization.id);
    await http().inject({
      method: "POST",
      url: "/api/v1/auth/admin/invitations",
      headers: sessionHeaders(adminSession),
      payload: {
        organizationId: organization.id,
        email: "invited-teacher@example.invalid",
        displayName: "Enseignant Test",
        roles: ["teacher"],
      },
    });
    const accepted = await http().inject({
      method: "POST",
      url: "/api/v1/auth/invitations/accept",
      payload: { token: mail.invitations[0]!.token, password: TEST_PASSWORD },
    });
    expect(accepted.statusCode).toBe(200);
    expect(accepted.json().status).toBe("authenticated");
    const me = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(responseCookies(accepted.headers), false),
    });
    expect(me.statusCode).toBe(200);
    expect(me.json().memberships[0].roles).toEqual(["teacher"]);
  });

  it("désactive un compte et révoque ses sessions sans désactiver l’administrateur", async () => {
    const organization = await createOrganization(prisma);
    const { session: adminSession } = await createAdmin(organization.id);
    const target = await createUser(prisma, {
      organizationId: organization.id,
      email: "target@example.invalid",
      passwordHash,
    });
    const targetSession = await createSession(prisma, target.id);
    expect(
      (
        await http().inject({
          method: "GET",
          url: "/api/v1/auth/me",
          headers: sessionHeaders(targetSession, false),
        })
      ).statusCode,
    ).toBe(200);

    const disabled = await http().inject({
      method: "POST",
      url: `/api/v1/auth/admin/users/${target.id}/disable`,
      headers: sessionHeaders(adminSession),
      payload: { organizationId: organization.id },
    });
    expect(disabled.statusCode).toBe(204);
    const targetAfter = await http().inject({
      method: "GET",
      url: "/api/v1/auth/me",
      headers: sessionHeaders(targetSession, false),
    });
    expect(targetAfter.statusCode).toBe(401);
    expect(await prisma.user.findUniqueOrThrow({ where: { id: target.id } })).toMatchObject({
      status: UserStatus.DISABLED,
    });
    expect(
      await prisma.authSession.count({ where: { userId: target.id, revokedAt: null } }),
    ).toBe(0);
    expect(
      (
        await http().inject({
          method: "GET",
          url: "/api/v1/auth/me",
          headers: sessionHeaders(adminSession, false),
        })
      ).statusCode,
    ).toBe(200);
  });

  it("limite la force brute et journalise sans secret ni email en clair", async () => {
    const organization = await createOrganization(prisma);
    const user = await createUser(prisma, {
      organizationId: organization.id,
      email: "brute@example.invalid",
      passwordHash,
    });
    for (let attempt = 0; attempt < 5; attempt += 1) {
      const response = await login(user.email, "mauvais-mot-de-passe");
      expect(response.response.statusCode).toBe(401);
    }
    const limited = await login(user.email, "mauvais-mot-de-passe");
    expect(limited.response.statusCode).toBe(429);
    expect(limited.response.json().code).toBe("RATE_LIMITED");
    const events = await prisma.authEvent.findMany();
    expect(events.some((event) => event.outcome === "RATE_LIMITED")).toBe(true);
    const serialized = JSON.stringify(events);
    expect(serialized).not.toContain(TEST_PASSWORD);
    expect(serialized).not.toContain("mauvais-mot-de-passe");
    expect(serialized).not.toContain(user.email);
    expect(serialized).not.toContain("passwordHash");
    expect(serialized).not.toContain("tokenHash");
    expect(serialized).not.toContain("secretCiphertext");
    expect(await prisma.authRateBucket.count()).toBeGreaterThan(0);
  });

  it("permet de révoquer une autre session puis la session courante", async () => {
    const organization = await createOrganization(prisma);
    const user = await createUser(prisma, {
      organizationId: organization.id,
      email: "sessions@example.invalid",
      passwordHash,
    });
    const first = await login(user.email);
    const second = await login(user.email);
    const listed = await http().inject({
      method: "GET",
      url: "/api/v1/auth/sessions",
      headers: sessionHeaders(first.cookies, false),
    });
    expect(listed.json().sessions).toHaveLength(2);
    const secondId = second.response.json().session.id as string;
    const revoked = await http().inject({
      method: "DELETE",
      url: `/api/v1/auth/sessions/${secondId}`,
      headers: sessionHeaders(first.cookies),
    });
    expect(revoked.statusCode).toBe(204);
    expect(
      (
        await http().inject({
          method: "GET",
          url: "/api/v1/auth/me",
          headers: sessionHeaders(second.cookies, false),
        })
      ).statusCode,
    ).toBe(401);
    const current = await http().inject({
      method: "DELETE",
      url: `/api/v1/auth/sessions/${first.response.json().session.id}`,
      headers: sessionHeaders(first.cookies),
    });
    expect(current.statusCode).toBe(204);
    expect(
      (
        await http().inject({
          method: "GET",
          url: "/api/v1/auth/me",
          headers: sessionHeaders(first.cookies, false),
        })
      ).statusCode,
    ).toBe(401);
  });

  it("refuse une invitation expirée sans créer de session", async () => {
    const organization = await createOrganization(prisma);
    const { session: adminSession } = await createAdmin(organization.id);
    await http().inject({
      method: "POST",
      url: "/api/v1/auth/admin/invitations",
      headers: sessionHeaders(adminSession),
      payload: {
        organizationId: organization.id,
        email: "expired-invite@example.invalid",
        displayName: "Invitation Expirée",
        roles: ["teacher"],
      },
    });
    const rawToken = mail.invitations[0]!.token;
    await prisma.authOneTimeToken.update({
      where: { tokenHash: createHash("sha256").update(rawToken).digest("hex") },
      data: { expiresAt: new Date(Date.now() - 1000) },
    });
    const response = await http().inject({
      method: "POST",
      url: "/api/v1/auth/invitations/accept",
      payload: { token: rawToken, password: TEST_PASSWORD },
    });
    expect(response.statusCode).toBe(400);
    expect(response.json().code).toBe("TOKEN_INVALID");
    expect(
      await prisma.authSession.count({
        where: { user: { email: "expired-invite@example.invalid" } },
      }),
    ).toBe(0);
    expect(
      await prisma.authOneTimeToken.findFirst({
        where: { kind: AuthTokenKind.INVITATION, consumedAt: null },
      }),
    ).not.toBeNull();
  });
});
