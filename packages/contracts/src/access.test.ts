import { describe, expect, it } from "vitest";
import {
  auditQuerySchema,
  permissionGrantInputSchema,
  ROLE_PERMISSIONS,
} from "./index.js";
import { ROLE_IDS } from "./auth.js";
import { PERMISSIONS } from "./permissions.js";

const organizationId = "00000000-0000-4000-8000-000000000001";
const userId = "00000000-0000-4000-8000-000000000002";

describe("contrats RBAC et audit", () => {
  it("couvre les treize rôles sans donner la gestion des permissions aux rôles métier", () => {
    for (const role of ROLE_IDS) {
      expect(ROLE_PERMISSIONS[role]).toBeDefined();
      if (role !== "technical_admin") {
        expect(ROLE_PERMISSIONS[role]).not.toContain(
          PERMISSIONS.securityPermissionsManage,
        );
      }
    }
    expect(ROLE_PERMISSIONS.teacher).not.toContain(PERMISSIONS.paymentsWrite);
    expect(ROLE_PERMISSIONS.guardian).toContain(PERMISSIONS.studentsRead);
  });

  it("refuse une permission inconnue ou une portée avec champ supplémentaire", () => {
    const valid = {
      organizationId,
      userId,
      permission: PERMISSIONS.notesWrite,
      scope: { type: "class", id: organizationId },
    };
    expect(permissionGrantInputSchema.safeParse(valid).success).toBe(true);
    expect(
      permissionGrantInputSchema.safeParse({
        ...valid,
        permission: "notes.delete",
      }).success,
    ).toBe(false);
    expect(
      permissionGrantInputSchema.safeParse({
        ...valid,
        scope: { ...valid.scope, organizationId: userId },
      }).success,
    ).toBe(false);
  });

  it("borne et normalise la consultation du journal", () => {
    expect(auditQuerySchema.parse({ organizationId }).limit).toBe(50);
    expect(auditQuerySchema.parse({ organizationId, limit: "100" }).limit).toBe(
      100,
    );
    expect(auditQuerySchema.safeParse({ organizationId, limit: "101" }).success).toBe(
      false,
    );
  });
});
