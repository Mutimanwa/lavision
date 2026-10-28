import { describe, expect, it } from "vitest";
import { ROLE_DEFINITIONS, type RoleId } from "./roles";

const EXPECTED_ROLE_IDS: RoleId[] = [
  "technical_admin",
  "direction",
  "academic_manager",
  "school_secretary",
  "student_affairs",
  "teacher",
  "accountant",
  "cashier",
  "hr_manager",
  "employee",
  "guardian",
  "student",
  "auditor",
];

describe("catalogue des rôles", () => {
  it("expose exactement les rôles initiaux prévus", () => {
    expect(ROLE_DEFINITIONS.map((role) => role.id)).toEqual(EXPECTED_ROLE_IDS);
  });

  it("ne contient aucun identifiant dupliqué", () => {
    const ids = ROLE_DEFINITIONS.map((role) => role.id);
    expect(new Set(ids).size).toBe(ids.length);
  });

  it("fournit un libellé et une description pour chaque rôle", () => {
    for (const role of ROLE_DEFINITIONS) {
      expect(role.label.trim().length).toBeGreaterThan(0);
      expect(role.description.trim().length).toBeGreaterThan(0);
    }
  });
});
