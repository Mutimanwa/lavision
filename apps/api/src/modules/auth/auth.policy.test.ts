import { describe, expect, it } from "vitest";
import { MFA_REQUIRED_ROLE_IDS, ROLE_IDS } from "@lavision/contracts";
import { Role } from "../../database/client.js";
import {
  PRISMA_ROLE_BY_ROLE_ID,
  requiresMfa,
  ROLE_ID_BY_PRISMA_ROLE,
} from "./auth.policy.js";

describe("politique MFA", () => {
  it("couvre exactement les treize rôles de la matrice", () => {
    expect(Object.keys(ROLE_ID_BY_PRISMA_ROLE)).toHaveLength(13);
    expect(Object.keys(PRISMA_ROLE_BY_ROLE_ID)).toHaveLength(13);
    for (const roleId of ROLE_IDS) {
      const prismaRole = PRISMA_ROLE_BY_ROLE_ID[roleId];
      expect(ROLE_ID_BY_PRISMA_ROLE[prismaRole]).toBe(roleId);
    }
  });

  it.each(MFA_REQUIRED_ROLE_IDS)("impose MFA au rôle %s", (roleId) => {
    expect(requiresMfa([PRISMA_ROLE_BY_ROLE_ID[roleId]])).toBe(true);
  });

  it("n’impose pas MFA à un enseignant sans autre rôle sensible", () => {
    expect(requiresMfa([Role.TEACHER])).toBe(false);
    expect(requiresMfa([Role.TEACHER, Role.CASHIER])).toBe(true);
  });
});
