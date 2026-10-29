import { randomUUID } from "node:crypto";
import type { NestFastifyApplication } from "@nestjs/platform-fastify";
import { afterAll, beforeAll, beforeEach, describe, expect, it } from "vitest";
import { AccessResourceType, PrismaClient, Role } from "../../src/database/client.js";
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
  return { user, session: await createSession(prisma, user.id) };
}

async function program(organizationId: string) {
  return prisma.accessResource.create({
    data: { id: randomUUID(), organizationId, type: AccessResourceType.PROGRAM },
  });
}

function setInput(organizationId: string) {
  return {
    organizationId,
    domain: "academic",
    code: "GRADING",
    name: "Barèmes du programme",
    purpose: "Tester une configuration fictive et ses héritages.",
    scopeOrder: ["organization", "program"],
    definitionKeys: ["academic.grading.scale_min", "academic.grading.scale_max", "academic.pass.mark"],
  };
}

function versionInput(
  organizationId: string,
  values: Array<{ key: string; value: unknown }>,
  bindings: Array<{ scopeType: string; scopeId: string }>,
  effectiveFrom = "2026-01-01",
) {
  return {
    organizationId,
    effectiveFrom,
    timezone: "Africa/Bujumbura",
    source: "Fixture fictive de recette #39",
    reason: "Configuration de test à faire valider.",
    values,
    bindings,
  };
}

async function createSet(organizationId: string, session: { token: string; csrfToken: string }) {
  const response = await http().inject({
    method: "POST",
    url: "/api/v1/rules/sets",
    headers: sessionHeaders(session),
    payload: setInput(organizationId),
  });
  expect(response.statusCode).toBe(201);
  return response.json().id as string;
}

async function createVersion(
  setId: string,
  session: { token: string; csrfToken: string },
  input: ReturnType<typeof versionInput>,
) {
  const response = await http().inject({
    method: "POST",
    url: `/api/v1/rules/sets/${setId}/versions`,
    headers: sessionHeaders(session),
    payload: input,
  });
  expect(response.statusCode).toBe(201);
  return response.json();
}

async function publishVersion(
  versionId: string,
  organizationId: string,
  creator: { token: string; csrfToken: string },
  direction: { token: string; csrfToken: string },
  asOf: string,
  scope: Record<string, string> = {},
) {
  const simulation = await http().inject({
    method: "POST",
    url: `/api/v1/rules/versions/${versionId}/simulate`,
    headers: sessionHeaders(creator),
    payload: { organizationId, cases: [{ name: "nominal", kind: "normal", asOf, scope }] },
  });
  expect(simulation.statusCode).toBe(201);
  expect(simulation.json().status).toBe("PASSED");
  for (const [action, session] of [["submit", creator], ["review", direction], ["approve", direction], ["publish", direction]] as const) {
    const response = await http().inject({
      method: "POST",
      url: `/api/v1/rules/versions/${versionId}/${action}`,
      headers: sessionHeaders(session),
      payload: { organizationId, reason: `Étape ${action} de la fixture.` },
    });
    expect(response.statusCode).toBe(200);
  }
  return http().inject({
    method: "GET",
    url: `/api/v1/rules/versions/${versionId}?organizationId=${organizationId}`,
    headers: sessionHeaders(creator),
  });
}

describe("Moteur de règles versionné #39", () => {
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

  it("résout deux programmes, planifie une succession et conserve le snapshot historique", async () => {
    const organization = await createOrganization(prisma);
    const firstProgram = await program(organization.id);
    const secondProgram = await program(organization.id);
    const { session: creator } = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const { session: direction } = await actor(organization.id, Role.DIRECTION);
    const setId = await createSet(organization.id, creator);

    const base = await createVersion(setId, creator, versionInput(organization.id, [
      { key: "academic.grading.scale_min", value: 0 },
      { key: "academic.grading.scale_max", value: 20 },
      { key: "academic.pass.mark", value: 10 },
    ], [{ scopeType: "organization", scopeId: organization.id }]));
    await publishVersion(base.id, organization.id, creator, direction, "2026-01-01");

    const first = await createVersion(setId, creator, versionInput(organization.id, [
      { key: "academic.grading.scale_max", value: 120 },
      { key: "academic.pass.mark", value: 60 },
    ], [{ scopeType: "program", scopeId: firstProgram.id }]));
    await publishVersion(first.id, organization.id, creator, direction, "2026-01-01", { programId: firstProgram.id });

    const second = await createVersion(setId, creator, versionInput(organization.id, [
      { key: "academic.grading.scale_max", value: 80 },
      { key: "academic.pass.mark", value: 40 },
    ], [{ scopeType: "program", scopeId: secondProgram.id }]));
    await publishVersion(second.id, organization.id, creator, direction, "2026-01-01", { programId: secondProgram.id });

    const resolve = async (programId: string, asOf: string) => http().inject({
      method: "POST",
      url: "/api/v1/rules/resolve",
      headers: sessionHeaders(creator),
      payload: { organizationId: organization.id, ruleSetId: setId, asOf, scope: { programId } },
    });
    const firstResolved = await resolve(firstProgram.id, "2026-10-01");
    const secondResolved = await resolve(secondProgram.id, "2026-10-01");
    expect(firstResolved.statusCode).toBe(200);
    expect(firstResolved.json().status).toBe("RESOLVED");
    expect(firstResolved.json().values.find((entry: { key: string }) => entry.key === "academic.grading.scale_max").value).toBe("120");
    expect(firstResolved.json().values.find((entry: { key: string }) => entry.key === "academic.grading.scale_min").source.inherited).toBe(true);
    expect(secondResolved.json().values.find((entry: { key: string }) => entry.key === "academic.grading.scale_max").value).toBe("80");

    const duplicate = await http().inject({
      method: "POST",
      url: `/api/v1/rules/versions/${first.id}/duplicate`,
      headers: sessionHeaders(creator),
      payload: {
        organizationId: organization.id,
        effectiveFrom: "2027-01-01",
        timezone: "Africa/Bujumbura",
        source: "Duplication fictive pour nouvelle période",
        reason: "Préparer une adaptation future sans réécrire la version courante.",
      },
    });
    expect(duplicate.statusCode).toBe(201);
    const futureId = duplicate.json().id as string;
    const updated = await http().inject({
      method: "PATCH",
      url: `/api/v1/rules/versions/${futureId}`,
      headers: sessionHeaders(creator),
      payload: {
        organizationId: organization.id,
        values: [
          { key: "academic.grading.scale_max", value: 140 },
          { key: "academic.pass.mark", value: 70 },
        ],
      },
    });
    expect(updated.statusCode).toBe(200);
    const futurePublished = await publishVersion(futureId, organization.id, creator, direction, "2027-01-01", { programId: firstProgram.id });
    expect(futurePublished.json().status).toBe("SCHEDULED");
    expect((await resolve(firstProgram.id, "2026-12-31")).json().values.find((entry: { key: string }) => entry.key === "academic.grading.scale_max").value).toBe("120");
    expect((await resolve(firstProgram.id, "2027-01-01")).json().values.find((entry: { key: string }) => entry.key === "academic.grading.scale_max").value).toBe("140");

    const documentId = randomUUID();
    const snapshot = await http().inject({
      method: "POST",
      url: "/api/v1/rules/snapshots",
      headers: sessionHeaders(direction),
      payload: {
        organizationId: organization.id,
        documentType: "bulletin",
        documentId,
        ruleSetVersionId: first.id,
        asOf: "2026-10-01",
        scope: { programId: firstProgram.id },
      },
    });
    expect(snapshot.statusCode).toBe(201);
    expect(snapshot.json().resolved.ruleContentHash).toBe(first.contentHash);
    expect(snapshot.json().resolved.resolution.values.find((entry: { key: string }) => entry.key === "academic.grading.scale_max").value).toBe("120");

    const patchedPublished = await http().inject({
      method: "PATCH",
      url: `/api/v1/rules/versions/${first.id}`,
      headers: sessionHeaders(creator),
      payload: { organizationId: organization.id, reason: "Tentative interdite." },
    });
    expect(patchedPublished.statusCode).toBe(409);
    await expect(prisma.ruleSetVersion.update({ where: { id: first.id }, data: { source: "Réécriture interdite" } })).rejects.toThrow();
    const storedValue = await prisma.ruleValue.findFirstOrThrow({ where: { ruleSetVersionId: first.id, definitionKey: "academic.grading.scale_max" } });
    await expect(prisma.ruleValue.update({ where: { id: storedValue.id }, data: { value: "999" } })).rejects.toThrow();

    const history = await http().inject({
      method: "GET",
      url: `/api/v1/rules/versions/${first.id}/revisions?organizationId=${organization.id}`,
      headers: sessionHeaders(creator),
    });
    expect(history.statusCode).toBe(200);
    expect(history.json().revisions.length).toBeGreaterThanOrEqual(5);
  });

  it("rejette une formule, une portée étrangère et une soumission réellement incomplète", async () => {
    const organization = await createOrganization(prisma);
    const otherOrganization = await createOrganization(prisma, "OTHER");
    const foreignProgram = await program(otherOrganization.id);
    const { session: creator } = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const { session: accountant } = await actor(organization.id, Role.ACCOUNTANT);
    const setId = await createSet(organization.id, creator);

    const invalid = await http().inject({
      method: "POST",
      url: `/api/v1/rules/sets/${setId}/versions`,
      headers: sessionHeaders(creator),
      payload: versionInput(organization.id, [{ key: "academic.grading.scale_max", value: "=120" }], [{ scopeType: "organization", scopeId: organization.id }]),
    });
    expect(invalid.statusCode).toBe(400);
    expect(invalid.json().issues[0].code).toBe("INVALID_DECIMAL");

    const foreign = await http().inject({
      method: "POST",
      url: `/api/v1/rules/sets/${setId}/versions`,
      headers: sessionHeaders(creator),
      payload: versionInput(organization.id, [{ key: "academic.grading.scale_max", value: 120 }], [{ scopeType: "program", scopeId: foreignProgram.id }]),
    });
    expect(foreign.statusCode).toBe(400);
    expect(foreign.json().issues[0].code).toBe("SCOPE_NOT_FOUND");

    const incomplete = await createVersion(setId, creator, versionInput(organization.id, [{ key: "academic.grading.scale_max", value: 120 }], [{ scopeType: "organization", scopeId: organization.id }]));
    const submitted = await http().inject({
      method: "POST",
      url: `/api/v1/rules/versions/${incomplete.id}/submit`,
      headers: sessionHeaders(creator),
      payload: { organizationId: organization.id, reason: "Tenter une soumission incomplète." },
    });
    expect(submitted.statusCode).toBe(400);
    expect(submitted.json().issues.some((issue: { code: string }) => issue.code === "MISSING_REQUIRED")).toBe(true);

    const denied = await http().inject({
      method: "POST",
      url: "/api/v1/rules/sets",
      headers: sessionHeaders(accountant),
      payload: setInput(organization.id),
    });
    expect(denied.statusCode).toBe(403);
  });

  it("impose la séparation du préparateur et bloque les adaptations ambiguës", async () => {
    const organization = await createOrganization(prisma);
    const { session: creator } = await actor(organization.id, Role.ACADEMIC_MANAGER);
    const { session: direction } = await actor(organization.id, Role.DIRECTION);
    const setId = await createSet(organization.id, creator);
    const draft = await createVersion(setId, creator, versionInput(organization.id, [
      { key: "academic.grading.scale_min", value: 0 },
      { key: "academic.grading.scale_max", value: 20 },
      { key: "academic.pass.mark", value: 10 },
    ], [{ scopeType: "organization", scopeId: organization.id }]));
    const simulation = await http().inject({
      method: "POST",
      url: `/api/v1/rules/versions/${draft.id}/simulate`,
      headers: sessionHeaders(creator),
      payload: { organizationId: organization.id, cases: [{ name: "nominal", asOf: "2026-01-01", scope: {} }] },
    });
    expect(simulation.json().status).toBe("PASSED");
    const submit = await http().inject({
      method: "POST",
      url: `/api/v1/rules/versions/${draft.id}/submit`,
      headers: sessionHeaders(creator),
      payload: { organizationId: organization.id, reason: "Soumettre pour revue." },
    });
    expect(submit.statusCode).toBe(200);
    const selfReview = await http().inject({
      method: "POST",
      url: `/api/v1/rules/versions/${draft.id}/review`,
      headers: sessionHeaders(creator),
      payload: { organizationId: organization.id, reason: "Auto-validation interdite." },
    });
    expect(selfReview.statusCode).toBe(403);
    for (const action of ["review", "approve", "publish"]) {
      const response = await http().inject({
        method: "POST",
        url: `/api/v1/rules/versions/${draft.id}/${action}`,
        headers: sessionHeaders(direction),
        payload: { organizationId: organization.id, reason: `Étape ${action} après contrôle de séparation.` },
      });
      expect(response.statusCode).toBe(200);
    }

    const competing = await createVersion(setId, creator, versionInput(organization.id, [
      { key: "academic.grading.scale_max", value: 30 },
    ], [{ scopeType: "organization", scopeId: organization.id }]));
    const conflict = await http().inject({
      method: "POST",
      url: `/api/v1/rules/versions/${competing.id}/submit`,
      headers: sessionHeaders(creator),
      payload: { organizationId: organization.id, reason: "Tester une adaptation ambiguë." },
    });
    expect(conflict.statusCode).toBe(400);
    expect(conflict.json().issues.some((issue: { code: string }) => issue.code === "AMBIGUOUS_SAME_SCOPE_AND_DATE")).toBe(true);
  });
});
