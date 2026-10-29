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
  return { user, session: await createSession(prisma, user.id) };
}

function yearInput(organizationId: string, code = "2026-2027") {
  return {
    organizationId,
    code,
    name: `Année ${code}`,
    startDate: "2026-09-01",
    endDate: "2027-07-31",
  };
}

function periodInput(
  organizationId: string,
  schoolYearId: string,
  code: string,
  sequence: number,
  startDate: string,
  endDate: string,
) {
  return { organizationId, schoolYearId, code, name: code, sequence, startDate, endDate };
}

async function createYearWithPeriod(
  organizationId: string,
  secretary: { token: string; csrfToken: string },
  code = "2026-2027",
) {
  const year = await http().inject({
    method: "POST",
    url: "/api/v1/school/years",
    headers: sessionHeaders(secretary),
    payload: yearInput(organizationId, code),
  });
  expect(year.statusCode).toBe(201);
  const yearId = year.json().id as string;
  const period = await http().inject({
    method: "POST",
    url: "/api/v1/school/periods",
    headers: sessionHeaders(secretary),
    payload: periodInput(
      organizationId,
      yearId,
      "P1",
      1,
      "2026-09-01",
      "2026-12-20",
    ),
  });
  expect(period.statusCode).toBe(201);
  return { yearId, periodId: period.json().id as string };
}

describe("Référentiels et calendrier scolaire #8", () => {
  beforeAll(async () => {
    process.env.AUTH_ENCRYPTION_KEY = TEST_AUTH_KEY;
    process.env.AUTH_COOKIE_SECURE = "false";
    process.env.NODE_ENV = "test";
    app = await createAuthApp(new AuthMailStub());
    prisma = createTestPrismaClient();
    passwordHash = await new AuthCryptoService(
      new AuthConfigService(),
    ).hashPassword(TEST_PASSWORD);
  });

  beforeEach(async () => {
    await resetCoreDatabase(prisma);
  });

  afterAll(async () => {
    await app.close();
    await prisma.$disconnect();
  });

  it("configure une année active et bloque doublons et chevauchements, y compris en SQL", async () => {
    const organization = await createOrganization(prisma);
    const { session: secretary } = await actor(organization.id, Role.SCHOOL_SECRETARY);
    const { session: direction } = await actor(organization.id, Role.DIRECTION);
    const { yearId } = await createYearWithPeriod(organization.id, secretary);

    const second = await http().inject({
      method: "POST",
      url: "/api/v1/school/periods",
      headers: sessionHeaders(secretary),
      payload: periodInput(
        organization.id,
        yearId,
        "P2",
        2,
        "2027-01-04",
        "2027-03-31",
      ),
    });
    expect(second.statusCode).toBe(201);

    const overlap = await http().inject({
      method: "POST",
      url: "/api/v1/school/periods",
      headers: sessionHeaders(secretary),
      payload: periodInput(
        organization.id,
        yearId,
        "P3",
        3,
        "2027-03-30",
        "2027-05-01",
      ),
    });
    expect(overlap.statusCode).toBe(409);
    expect(overlap.json().code).toBe("CONFLICT");

    const duplicate = await http().inject({
      method: "POST",
      url: "/api/v1/school/periods",
      headers: sessionHeaders(secretary),
      payload: periodInput(
        organization.id,
        yearId,
        "P1",
        4,
        "2027-04-01",
        "2027-05-01",
      ),
    });
    expect(duplicate.statusCode).toBe(409);

    await expect(
      prisma.schoolPeriod.create({
        data: {
          schoolYearId: yearId,
          code: "SQL-OVERLAP",
          name: "Chevauchement SQL",
          sequence: 99,
          startDate: new Date("2026-12-20T00:00:00.000Z"),
          endDate: new Date("2027-01-03T00:00:00.000Z"),
        },
      }),
    ).rejects.toThrow();

    const deniedActivation = await http().inject({
      method: "POST",
      url: `/api/v1/school/years/${yearId}/activate`,
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id },
    });
    expect(deniedActivation.statusCode).toBe(403);
    const activated = await http().inject({
      method: "POST",
      url: `/api/v1/school/years/${yearId}/activate`,
      headers: sessionHeaders(direction),
      payload: { organizationId: organization.id },
    });
    expect(activated.statusCode).toBe(200);
    expect(activated.json().status).toBe("ACTIVE");

    const otherYear = await http().inject({
      method: "POST",
      url: "/api/v1/school/years",
      headers: sessionHeaders(secretary),
      payload: yearInput(organization.id, "2027-2028"),
    });
    const otherYearId = otherYear.json().id as string;
    const otherPeriod = await http().inject({
      method: "POST",
      url: "/api/v1/school/periods",
      headers: sessionHeaders(secretary),
      payload: periodInput(
        organization.id,
        otherYearId,
        "P1",
        1,
        "2026-09-01",
        "2026-12-20",
      ),
    });
    expect(otherPeriod.statusCode).toBe(201);
    const secondActivation = await http().inject({
      method: "POST",
      url: `/api/v1/school/years/${otherYearId}/activate`,
      headers: sessionHeaders(direction),
      payload: { organizationId: organization.id },
    });
    expect(secondActivation.statusCode).toBe(409);

    const years = await http().inject({
      method: "GET",
      url: `/api/v1/school/years?organizationId=${organization.id}`,
      headers: sessionHeaders(secretary, false),
    });
    expect(years.statusCode).toBe(200);
    expect(years.json().years).toHaveLength(2);
  });

  it("rend une année clôturée immuable et exige une réouverture direction auditée", async () => {
    const organization = await createOrganization(prisma);
    const { session: secretary } = await actor(organization.id, Role.SCHOOL_SECRETARY);
    const { session: direction } = await actor(organization.id, Role.DIRECTION);
    const { session: auditor } = await actor(organization.id, Role.AUDITOR);
    const { yearId } = await createYearWithPeriod(organization.id, secretary);
    await http().inject({
      method: "POST",
      url: `/api/v1/school/years/${yearId}/activate`,
      headers: sessionHeaders(direction),
      payload: { organizationId: organization.id },
    });
    const closed = await http().inject({
      method: "POST",
      url: `/api/v1/school/years/${yearId}/close`,
      headers: sessionHeaders(direction),
      payload: { organizationId: organization.id, reason: "Clôture annuelle validée" },
    });
    expect(closed.statusCode).toBe(200);
    expect(closed.json().status).toBe("CLOSED");

    const updateClosed = await http().inject({
      method: "PATCH",
      url: `/api/v1/school/years/${yearId}`,
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id, name: "Modification interdite" },
    });
    expect(updateClosed.statusCode).toBe(409);
    const periodClosed = await http().inject({
      method: "POST",
      url: "/api/v1/school/periods",
      headers: sessionHeaders(secretary),
      payload: periodInput(
        organization.id,
        yearId,
        "P2",
        2,
        "2027-01-04",
        "2027-03-31",
      ),
    });
    expect(periodClosed.statusCode).toBe(409);

    const forbiddenReopen = await http().inject({
      method: "POST",
      url: `/api/v1/school/years/${yearId}/reopen`,
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id, reason: "Tentative non autorisée" },
    });
    expect(forbiddenReopen.statusCode).toBe(403);
    const reopened = await http().inject({
      method: "POST",
      url: `/api/v1/school/years/${yearId}/reopen`,
      headers: sessionHeaders(direction),
      payload: { organizationId: organization.id, reason: "Réouverture motivée par la direction" },
    });
    expect(reopened.statusCode).toBe(200);
    expect(reopened.json().status).toBe("DRAFT");
    const updated = await http().inject({
      method: "PATCH",
      url: `/api/v1/school/years/${yearId}`,
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id, name: "Année rouverte et corrigée" },
    });
    expect(updated.statusCode).toBe(200);

    const history = await http().inject({
      method: "GET",
      url: `/api/v1/school/history?organizationId=${organization.id}&entityType=school_year&entityId=${yearId}`,
      headers: sessionHeaders(auditor, false),
    });
    expect(history.statusCode).toBe(200);
    const actions = history.json().revisions.map((revision: { action: string }) => revision.action);
    expect(actions).toEqual(
      expect.arrayContaining([
        "school.year.create",
        "school.year.activate",
        "school.year.close",
        "school.year.reopen",
        "school.year.update",
      ]),
    );
    const closeRevision = history.json().revisions.find(
      (revision: { action: string }) => revision.action === "school.year.close",
    );
    expect(closeRevision.before.status).toBe("ACTIVE");
    expect(closeRevision.after.status).toBe("CLOSED");
    expect(closeRevision.reason).toBe("Clôture annuelle validée");

    const audit = await http().inject({
      method: "GET",
      url: `/api/v1/security/audit?organizationId=${organization.id}`,
      headers: sessionHeaders(auditor, false),
    });
    expect(audit.statusCode).toBe(200);
    expect(JSON.stringify(audit.json())).toContain("school.year.close");
  });

  it("gère niveaux, salles et archivage sans suppression ni fuite cross-tenant", async () => {
    const organization = await createOrganization(prisma);
    const otherOrganization = await createOrganization(prisma, "OTHER");
    const { session: secretary } = await actor(organization.id, Role.SCHOOL_SECRETARY);
    const { session: otherSecretary } = await actor(otherOrganization.id, Role.SCHOOL_SECRETARY);
    const site = await http().inject({
      method: "POST",
      url: "/api/v1/school/sites",
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id, code: "SITE-A", name: "Site A" },
    });
    expect(site.statusCode).toBe(201);
    const level = await http().inject({
      method: "POST",
      url: "/api/v1/school/levels",
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id, code: "7E", name: "Septième", rank: 7 },
    });
    expect(level.statusCode).toBe(201);
    const room = await http().inject({
      method: "POST",
      url: "/api/v1/school/rooms",
      headers: sessionHeaders(secretary),
      payload: {
        organizationId: organization.id,
        siteId: site.json().id,
        code: "A101",
        name: "Salle A101",
        capacity: 40,
      },
    });
    expect(room.statusCode).toBe(201);
    const roomId = room.json().id as string;
    const duplicate = await http().inject({
      method: "POST",
      url: "/api/v1/school/rooms",
      headers: sessionHeaders(secretary),
      payload: {
        organizationId: organization.id,
        siteId: site.json().id,
        code: "A101",
        name: "Doublon",
      },
    });
    expect(duplicate.statusCode).toBe(409);

    const archived = await http().inject({
      method: "POST",
      url: `/api/v1/school/rooms/${roomId}/archive`,
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id, reason: "Salle réaménagée" },
    });
    expect(archived.statusCode).toBe(200);
    expect(archived.json().archivedAt).not.toBeNull();
    const activeRooms = await http().inject({
      method: "GET",
      url: `/api/v1/school/rooms?organizationId=${organization.id}`,
      headers: sessionHeaders(secretary, false),
    });
    expect(activeRooms.json().rooms).toHaveLength(0);
    const allRooms = await http().inject({
      method: "GET",
      url: `/api/v1/school/rooms?organizationId=${organization.id}&includeArchived=true`,
      headers: sessionHeaders(secretary, false),
    });
    expect(allRooms.json().rooms).toHaveLength(1);

    const crossTenant = await http().inject({
      method: "PATCH",
      url: `/api/v1/school/levels/${level.json().id}`,
      headers: sessionHeaders(otherSecretary),
      payload: { organizationId: otherOrganization.id, name: "Intrusion" },
    });
    expect(crossTenant.statusCode).toBe(403);
    expect(
      await prisma.academicLevel.findUniqueOrThrow({ where: { id: level.json().id } }),
    ).toMatchObject({ name: "Septième" });
  });

  it("valide les jours non ouvrés par année et site", async () => {
    const organization = await createOrganization(prisma);
    const { session: secretary } = await actor(organization.id, Role.SCHOOL_SECRETARY);
    const { yearId } = await createYearWithPeriod(organization.id, secretary);
    const site = await prisma.site.create({
      data: { organizationId: organization.id, code: "SITE-B", name: "Site B" },
    });
    const input = {
      organizationId: organization.id,
      schoolYearId: yearId,
      day: "2026-12-25",
      reason: "Jour non ouvré",
    };
    const created = await http().inject({
      method: "POST",
      url: "/api/v1/school/non-working-days",
      headers: sessionHeaders(secretary),
      payload: input,
    });
    expect(created.statusCode).toBe(201);
    const duplicate = await http().inject({
      method: "POST",
      url: "/api/v1/school/non-working-days",
      headers: sessionHeaders(secretary),
      payload: input,
    });
    expect(duplicate.statusCode).toBe(409);
    const siteDay = await http().inject({
      method: "POST",
      url: "/api/v1/school/non-working-days",
      headers: sessionHeaders(secretary),
      payload: { ...input, siteId: site.id, reason: "Fermeture du site" },
    });
    expect(siteDay.statusCode).toBe(201);
    const invalid = await http().inject({
      method: "PATCH",
      url: `/api/v1/school/non-working-days/${created.json().id}`,
      headers: sessionHeaders(secretary),
      payload: { organizationId: organization.id, day: "2028-01-01" },
    });
    expect(invalid.statusCode).toBe(409);
    const days = await http().inject({
      method: "GET",
      url: `/api/v1/school/years/${yearId}/non-working-days?organizationId=${organization.id}`,
      headers: sessionHeaders(secretary, false),
    });
    expect(days.statusCode).toBe(200);
    expect(days.json().days).toHaveLength(2);
  });
});
