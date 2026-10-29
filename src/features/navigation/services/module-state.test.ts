import { describe, expect, it } from "vitest";
import { createSeedState } from "@/features/school/data/seed";
import {
  MODULE_DEFINITIONS,
  type ModuleDefinition,
  type ModuleId,
} from "@/features/navigation/data/modules";
import { canRoleOpenModule, resolveModule } from "@/features/navigation/services/module-state";

function definition(id: ModuleId): ModuleDefinition {
  const resolved = MODULE_DEFINITIONS.find((item) => item.id === id);
  if (!resolved) throw new Error(`Module de test inconnu : ${id}`);
  return resolved;
}

describe("résolution des modules par rôle", () => {
  it("active les modules implémentés et conserve les futurs modules comme planifiés", () => {
    const state = createSeedState();
    expect(resolveModule(definition("students"), state).status).toBe("active");
    expect(resolveModule(definition("settings"), state).status).toBe("active");
    expect(resolveModule(definition("finance"), state).status).toBe("planned");
    expect(resolveModule(definition("family_portal"), state).status).toBe("planned");
  });

  it("désactive un module et ferme son accès de présentation", () => {
    const state = createSeedState();
    const resolved = resolveModule(definition("grades"), state, { grades: false });
    expect(resolved.status).toBe("disabled");
    expect(canRoleOpenModule(resolved, "teacher")).toBe(false);
  });

  it("signale les matières manquantes avant les notes, bulletins et proclamations", () => {
    const state = { ...createSeedState(), subjects: [] };
    for (const id of ["grades", "reports", "proclamation"] as const) {
      const resolved = resolveModule(definition(id), state);
      expect(resolved.status).toBe("configuring");
      expect(resolved.missingPrerequisites.some((item) => item.includes("matière"))).toBe(true);
    }
  });

  it("signale les classes manquantes avant les inscriptions et présences", () => {
    const state = { ...createSeedState(), classes: [] };
    for (const id of ["enrollments", "attendance"] as const) {
      const resolved = resolveModule(definition(id), state);
      expect(resolved.status).toBe("configuring");
      expect(resolved.missingPrerequisites.some((item) => item.includes("classe"))).toBe(true);
    }
  });

  it("filtre les modules selon le rôle sans transformer ce filtre en autorisation serveur", () => {
    const state = createSeedState();
    const students = resolveModule(definition("students"), state);
    const notes = resolveModule(definition("grades"), state);
    expect(canRoleOpenModule(students, "direction")).toBe(true);
    expect(canRoleOpenModule(students, "teacher")).toBe(false);
    expect(canRoleOpenModule(notes, "teacher")).toBe(true);
  });

  it("maintient les paramètres essentiels actifs même après une préférence de désactivation", () => {
    const resolved = resolveModule(definition("settings"), createSeedState(), { settings: false });
    expect(resolved.enabled).toBe(true);
    expect(resolved.status).toBe("active");
  });
});
