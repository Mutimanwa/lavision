import { randomUUID } from "node:crypto";
import type { NestFastifyApplication } from "@nestjs/platform-fastify";
import { afterAll, beforeAll, beforeEach, describe, expect, it } from "vitest";
import {
  AccessRelationType,
  AccessResourceType,
  AuditOutcome,
  PrismaClient,
  Role,
} from "../../src/database/client.js";
import { PERMISSIONS } from "@lavision/contracts";
import { AuditService } from "../../src/modules/access/audit.service.js";
import { AuthorizationService } from "../../src/modules/access/authorization.service.js";
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
import {
  createRelation,
  createResource,
  membershipFor,
} from "./access-helpers.js";
import { createTestPrismaClient, resetCoreDatabase } from "./helpers.js";

let app: NestFastifyApplication;
let prisma: PrismaClient;
let passwordHash: string;
let authorization: AuthorizationService;
let audit: AuditService;

function http() {
  return app.getHttpAdapter().getInstance();
}

async function adminSession(organizationId: string) {
  const admin = await createUser(prisma, {
    organizationId,
    email: `admin-${randomUUID()}@example.invalid`,
    passwordHash,
    role: Role.TECHNICAL_ADMIN,
  });
  return { admin, session: await createSession(prisma, admin.id) };
}

async function userSession(
  organizationId: string,
  role: Role | null,
  email = `user-${randomUUID()}@example.invalid`,
) {
  const user = await createUser(prisma, {
    organizationId,
    email,
    passwordHash,
    role,
  });
  return { user, session: await createSession(prisma, user.id) };
}

describe("RBAC, portées et audit", () => {
  beforeAll(async () => {
    process.env.AUTH_ENCRYPTION_KEY = TEST_AUTH_KEY;
    process.env.AUTH_COOKIE_SECURE = "false";
    process.env.NODE_ENV = "test";
    app = await createAuthApp(new AuthMailStub());
    prisma = createTestPrismaClient();
    passwordHash = await new AuthCryptoService(
      new AuthConfigService(),
    ).hashPassword(TEST_PASSWORD);
    authorization = app.get(AuthorizationService);
    audit = app.get(AuditService);
  });

  beforeEach(async () => {
    await resetCoreDatabase(prisma);
  });

  afterAll(async () => {
    await app.close();
    await prisma.$disconnect();
  });

  it("SEC-01/16 : un enseignant ne peut écrire que dans sa classe assignée", async () => {
    const organization = await createOrganization(prisma);
    const site = await prisma.site.create({
      data: {
        organizationId: organization.id,
        code: "SITE-A",
        name: "Site A",
      },
    });
    const { user: teacher } = await userSession(organization.id, Role.TEACHER);
    const classA = await createResource(prisma, {
      organizationId: organization.id,
      siteId: site.id,
      type: AccessResourceType.CLASS,
    });
    const classB = await createResource(prisma, {
      organizationId: organization.id,
      siteId: site.id,
      type: AccessResourceType.CLASS,
    });
    const membership = await membershipFor(prisma, teacher.id, organization.id);
    await createRelation(prisma, {
      membershipId: membership.id,
      relation: AccessRelationType.TEACHER_CLASS,
      resourceId: classA.id,
    });

    await expect(
      authorization.authorize({
        userId: teacher.id,
        organizationId: organization.id,
        permission: PERMISSIONS.notesWrite,
        resourceId: classA.id,
        resourceType: "class",
      }),
    ).resolves.toEqual({ allowed: true, reason: "ALLOWED" });
    await expect(
      authorization.authorize({
        userId: teacher.id,
        organizationId: organization.id,
        permission: PERMISSIONS.notesWrite,
        resourceId: classB.id,
        resourceType: "class",
      }),
    ).resolves.toEqual({ allowed: false, reason: "RELATION_REQUIRED" });
  });

  it("SEC-02/11 : un responsable ne peut pas changer d’enfant ou d’établissement", async () => {
    const organization = await createOrganization(prisma);
    const otherOrganization = await createOrganization(prisma, "OTHER");
    const { user: guardian } = await userSession(organization.id, Role.GUARDIAN);
    const child = await createResource(prisma, {
      organizationId: organization.id,
      type: AccessResourceType.STUDENT,
    });
    const otherChild = await createResource(prisma, {
      organizationId: organization.id,
      type: AccessResourceType.STUDENT,
    });
    const membership = await membershipFor(prisma, guardian.id, organization.id);
    await createRelation(prisma, {
      membershipId: membership.id,
      relation: AccessRelationType.GUARDIAN_STUDENT,
      resourceId: child.id,
    });

    await expect(
      authorization.authorize({
        userId: guardian.id,
        organizationId: organization.id,
        permission: PERMISSIONS.studentsRead,
        resourceId: child.id,
        resourceType: "student",
      }),
    ).resolves.toEqual({ allowed: true, reason: "ALLOWED" });
    await expect(
      authorization.authorize({
        userId: guardian.id,
        organizationId: organization.id,
        permission: PERMISSIONS.studentsRead,
        resourceId: otherChild.id,
        resourceType: "student",
      }),
    ).resolves.toEqual({ allowed: false, reason: "RELATION_REQUIRED" });
    await expect(
      authorization.authorize({
        userId: guardian.id,
        organizationId: otherOrganization.id,
        permission: PERMISSIONS.studentsRead,
        resourceId: child.id,
        resourceType: "student",
      }),
    ).resolves.toEqual({ allowed: false, reason: "RESOURCE_MISMATCH" });
  });

  it("contrôle les gardes HTTP, l’élévation verticale et la révocation de permission", async () => {
    const organization = await createOrganization(prisma);
    const { session: admin } = await adminSession(organization.id);
    const { user: teacher, session: teacherSession } = await userSession(
      organization.id,
      Role.TEACHER,
    );
    const payment = await createResource(prisma, {
      organizationId: organization.id,
      type: AccessResourceType.PAYMENT,
    });
    const payload = {
      organizationId: organization.id,
      userId: teacher.id,
      permission: PERMISSIONS.paymentsWrite,
      scope: { type: "organization", id: organization.id },
    };

    const denied = await http().inject({
      method: "POST",
      url: "/api/v1/security/permissions/grant",
      headers: sessionHeaders(teacherSession),
      payload,
    });
    expect(denied.statusCode).toBe(403);
    expect(denied.json().code).toBe("FORBIDDEN");

    const granted = await http().inject({
      method: "POST",
      url: "/api/v1/security/permissions/grant",
      headers: sessionHeaders(admin),
      payload,
    });
    expect(granted.statusCode).toBe(201);
    expect(granted.json().permission).toBe(PERMISSIONS.paymentsWrite);
    await expect(
      authorization.authorize({
        userId: teacher.id,
        organizationId: organization.id,
        permission: PERMISSIONS.paymentsWrite,
        resourceId: payment.id,
        resourceType: "payment",
      }),
    ).resolves.toEqual({ allowed: true, reason: "ALLOWED" });

    const revoked = await http().inject({
      method: "POST",
      url: "/api/v1/security/permissions/revoke",
      headers: sessionHeaders(admin),
      payload: { organizationId: organization.id, grantId: granted.json().id },
    });
    expect(revoked.statusCode).toBe(200);
    expect(revoked.json().archivedAt).not.toBeNull();
    await expect(
      authorization.authorize({
        userId: teacher.id,
        organizationId: organization.id,
        permission: PERMISSIONS.paymentsWrite,
        resourceId: payment.id,
        resourceType: "payment",
      }),
    ).resolves.toEqual({ allowed: false, reason: "NO_PERMISSION" });
  });

  it("ne laisse pas une attribution explicite contourner la relation enseignant–classe", async () => {
    const organization = await createOrganization(prisma);
    const { session: admin } = await adminSession(organization.id);
    const { user: teacher } = await userSession(organization.id, Role.TEACHER);
    const classResource = await createResource(prisma, {
      organizationId: organization.id,
      type: AccessResourceType.CLASS,
    });
    const granted = await http().inject({
      method: "POST",
      url: "/api/v1/security/permissions/grant",
      headers: sessionHeaders(admin),
      payload: {
        organizationId: organization.id,
        userId: teacher.id,
        permission: PERMISSIONS.notesWrite,
        scope: { type: "organization", id: organization.id },
      },
    });
    expect(granted.statusCode).toBe(201);
    await expect(
      authorization.authorize({
        userId: teacher.id,
        organizationId: organization.id,
        permission: PERMISSIONS.notesWrite,
        resourceId: classResource.id,
        resourceType: "class",
      }),
    ).resolves.toEqual({ allowed: false, reason: "RELATION_REQUIRED" });
  });

  it("refuse la gestion cross-tenant même à un administrateur d’une autre organisation", async () => {
    const first = await createOrganization(prisma);
    const second = await createOrganization(prisma, "SECOND");
    const { admin, session: adminCookie } = await adminSession(first.id);
    const { user: target } = await userSession(second.id, Role.TEACHER);

    const response = await http().inject({
      method: "POST",
      url: "/api/v1/security/permissions/grant",
      headers: sessionHeaders(adminCookie),
      payload: {
        organizationId: second.id,
        userId: target.id,
        permission: PERMISSIONS.paymentsWrite,
        scope: { type: "organization", id: second.id },
      },
    });
    expect(response.statusCode).toBe(403);
    expect(response.json().code).toBe("FORBIDDEN");
    expect(
      await prisma.auditEvent.findFirst({
        where: {
          organizationId: second.id,
          action: "authorization.denied",
          actorUserId: admin.id,
        },
      }),
    ).not.toBeNull();
  });

  it("applique une attribution de rôle limitée à une classe", async () => {
    const organization = await createOrganization(prisma);
    const { session: admin } = await adminSession(organization.id);
    const { user: target } = await userSession(organization.id, null);
    const classA = await createResource(prisma, {
      organizationId: organization.id,
      type: AccessResourceType.CLASS,
    });
    const classB = await createResource(prisma, {
      organizationId: organization.id,
      type: AccessResourceType.CLASS,
    });
    const assigned = await http().inject({
      method: "POST",
      url: "/api/v1/security/roles/assign",
      headers: sessionHeaders(admin),
      payload: {
        organizationId: organization.id,
        userId: target.id,
        role: "teacher",
        scope: { type: "class", id: classA.id },
      },
    });
    expect(assigned.statusCode).toBe(201);
    const membership = await membershipFor(prisma, target.id, organization.id);
    await createRelation(prisma, {
      membershipId: membership.id,
      relation: AccessRelationType.TEACHER_CLASS,
      resourceId: classA.id,
    });

    await expect(
      authorization.authorize({
        userId: target.id,
        organizationId: organization.id,
        permission: PERMISSIONS.notesWrite,
        resourceId: classA.id,
        resourceType: "class",
      }),
    ).resolves.toEqual({ allowed: true, reason: "ALLOWED" });
    await expect(
      authorization.authorize({
        userId: target.id,
        organizationId: organization.id,
        permission: PERMISSIONS.notesWrite,
        resourceId: classB.id,
        resourceType: "class",
      }),
    ).resolves.toEqual({ allowed: false, reason: "SCOPE_MISMATCH" });
  });

  it("restreint la lecture d’audit et conserve les événements domaine append-only", async () => {
    const organization = await createOrganization(prisma);
    const { user: auditor, session: auditorSession } = await userSession(
      organization.id,
      Role.AUDITOR,
    );
    const { session: outsider } = await userSession(organization.id, Role.TEACHER);
    const actions = [
      ["notes.write", "note"],
      ["payments.write", "payment"],
      ["payroll.write", "payroll"],
      ["proclamations.publish", "proclamation"],
      ["permissions.grant", "permission"],
    ] as const;
    for (const [action, resourceType] of actions) {
      await audit.record({
        organizationId: organization.id,
        actorUserId: auditor.id,
        action,
        resourceType,
        resourceId: randomUUID(),
        outcome: AuditOutcome.SUCCESS,
        requestId: randomUUID(),
        context: {
          permission: action,
          scope: "organization",
          password: "ne-jamais-journaliser",
        },
      });
    }

    const response = await http().inject({
      method: "GET",
      url: `/api/v1/security/audit?organizationId=${organization.id}`,
      headers: sessionHeaders(auditorSession, false),
    });
    expect(response.statusCode).toBe(200);
    expect(response.json().events).toHaveLength(5);
    expect(response.json().events.map((event: { action: string }) => event.action)).toEqual(
      expect.arrayContaining(actions.map(([action]) => action)),
    );
    expect(JSON.stringify(response.json())).not.toContain("ne-jamais-journaliser");
    expect(JSON.stringify(response.json())).not.toContain("password");

    const forbidden = await http().inject({
      method: "GET",
      url: `/api/v1/security/audit?organizationId=${organization.id}`,
      headers: sessionHeaders(outsider, false),
    });
    expect(forbidden.statusCode).toBe(403);

    const stored = await prisma.auditEvent.findFirstOrThrow({
      where: { organizationId: organization.id },
    });
    await expect(
      prisma.auditEvent.update({
        where: { id: stored.id },
        data: { reason: "tamper" },
      }),
    ).rejects.toThrow();
    await expect(prisma.auditEvent.delete({ where: { id: stored.id } })).rejects.toThrow();
    expect(await prisma.auditEvent.count({ where: { organizationId: organization.id } })).toBe(6);
  });
});
