import { randomUUID } from "node:crypto";
import type { NestFastifyApplication } from "@nestjs/platform-fastify";
import { afterAll, beforeAll, beforeEach, describe, expect, it } from "vitest";
import { PrismaClient, Role } from "../../src/database/client.js";
import { AuthConfigService } from "../../src/modules/auth/auth.config.js";
import { AuthCryptoService } from "../../src/modules/auth/auth.crypto.js";
import {
  AuthMailStub,
  createAuthApp,
  createOrganization,
  createSession,
  createUser,
  sessionHeaders,
  TEST_AUTH_KEY,
  TEST_PASSWORD,
} from "./auth-helpers.js";
import { createTestPrismaClient, resetCoreDatabase } from "./helpers.js";

let app: NestFastifyApplication;
let prisma: PrismaClient;
let passwordHash: string;

function http() {
  return app.getHttpAdapter().getInstance();
}

async function actor(organizationId: string, role: Role) {
  const user = await createUser(prisma, {
    organizationId,
    email: `${role.toLowerCase()}-${randomUUID()}@example.invalid`,
    passwordHash,
    role,
  });
  return createSession(prisma, user.id);
}

describe("Catalogue et validation des règles #39", () => {
  beforeAll(async () => {
    process.env.AUTH_ENCRYPTION_KEY = TEST_AUTH_KEY;
    process.env.AUTH_COOKIE_SECURE = "false";
    process.env.NODE_ENV = "test";
    app = await createAuthApp(new AuthMailStub());
    prisma = createTestPrismaClient();
    passwordHash = await new AuthCryptoService(new AuthConfigService()).hashPassword(TEST_PASSWORD);
  });

  beforeEach(async () => {
    await resetCoreDatabase(prisma);
  });

  afterAll(async () => {
    await app.close();
    await prisma.$disconnect();
  });

  it("expose les définitions typées et filtre par domaine", async () => {
    const organization = await createOrganization(prisma);
    const session = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const response = await http().inject({
      method: "GET",
      url: `/api/v1/rules/catalog?organizationId=${organization.id}&domain=academic`,
      headers: sessionHeaders(session),
    });
    expect(response.statusCode).toBe(200);
    expect(response.json().catalogVersion).toBe(2);
    expect(response.json().definitions.length).toBeGreaterThan(10);
    expect(response.json().definitions.every((entry: { domain: string }) => entry.domain === "academic")).toBe(true);
    expect(response.json().definitions.some((entry: { key: string }) => entry.key === "academic.grading.scale_max")).toBe(true);
  });

  it("normalise une décimale exacte et rejette une formule sans rien exécuter", async () => {
    const organization = await createOrganization(prisma);
    const session = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const valid = await http().inject({
      method: "POST",
      url: "/api/v1/rules/validate",
      headers: sessionHeaders(session),
      payload: { organizationId: organization.id, key: "academic.grading.scale_max", value: 120 },
    });
    expect(valid.statusCode).toBe(200);
    expect(valid.json()).toMatchObject({ valid: true, normalizedValue: "120", issues: [] });

    const invalid = await http().inject({
      method: "POST",
      url: "/api/v1/rules/validate",
      headers: sessionHeaders(session),
      payload: { organizationId: organization.id, key: "academic.grading.scale_max", value: "=120" },
    });
    expect(invalid.statusCode).toBe(200);
    expect(invalid.json().valid).toBe(false);
    expect(invalid.json().issues[0].code).toBe("INVALID_DECIMAL");
  });

  it("accepte une structure de paramètre incertain sans exécuter de contenu", async () => {
    const organization = await createOrganization(prisma);
    const session = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const response = await http().inject({
      method: "POST",
      url: "/api/v1/rules/validate",
      headers: sessionHeaders(session),
      payload: {
        organizationId: organization.id,
        key: "academic.appreciation.scale",
        value: [{ minimum: "0", label: "À faire valider" }],
      },
    });
    expect(response.statusCode).toBe(200);
    expect(response.json()).toMatchObject({ valid: true, issues: [] });

    const invalid = await http().inject({
      method: "POST",
      url: "/api/v1/rules/validate",
      headers: sessionHeaders(session),
      payload: { organizationId: organization.id, key: "academic.appreciation.scale", value: "=SUM(A1:A2)" },
    });
    expect(invalid.statusCode).toBe(200);
    expect(invalid.json().valid).toBe(false);
    expect(invalid.json().issues[0].code).toBe("INVALID_STRUCTURE");
  });

  it("applique la permission du domaine en plus de la permission de validation", async () => {
    const organization = await createOrganization(prisma);
    const academic = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const accountant = await actor(organization.id, Role.ACCOUNTANT);
    const denied = await http().inject({
      method: "POST",
      url: "/api/v1/rules/validate",
      headers: sessionHeaders(academic),
      payload: { organizationId: organization.id, key: "finance.currency.code", value: "BIF" },
    });
    expect(denied.statusCode).toBe(403);

    const allowed = await http().inject({
      method: "POST",
      url: "/api/v1/rules/validate",
      headers: sessionHeaders(accountant),
      payload: { organizationId: organization.id, key: "finance.currency.code", value: "BIF" },
    });
    expect(allowed.statusCode).toBe(200);
    expect(allowed.json().valid).toBe(true);
  });

  it("refuse une clé hors catalogue et un utilisateur sans lecture du catalogue", async () => {
    const organization = await createOrganization(prisma);
    const academic = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const teacher = await actor(organization.id, Role.TEACHER);
    const unknown = await http().inject({
      method: "POST",
      url: "/api/v1/rules/validate",
      headers: sessionHeaders(academic),
      payload: { organizationId: organization.id, key: "academic.custom.formula", value: "1 + 1" },
    });
    expect(unknown.statusCode).toBe(200);
    expect(unknown.json()).toMatchObject({ valid: false });
    expect(unknown.json().issues[0].code).toBe("UNKNOWN_RULE");

    const denied = await http().inject({
      method: "GET",
      url: `/api/v1/rules/catalog?organizationId=${organization.id}`,
      headers: sessionHeaders(teacher),
    });
    expect(denied.statusCode).toBe(403);
  });
});
