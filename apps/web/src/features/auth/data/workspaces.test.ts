import { describe, expect, it } from "vitest";
import { ROLE_DEFINITIONS } from "@/features/auth/data/roles";
import { ROLE_WORKSPACES } from "@/features/auth/data/workspaces";

describe("configuration des espaces de travail", () => {
  it("couvre exactement les treize rôles fonctionnels", () => {
    expect(ROLE_DEFINITIONS).toHaveLength(13);
    expect(Object.keys(ROLE_WORKSPACES).sort()).toEqual(ROLE_DEFINITIONS.map((role) => role.id).sort());
  });

  it("fournit un accueil et un guide non bloquant pour chaque rôle", () => {
    for (const role of ROLE_DEFINITIONS) {
      const workspace = ROLE_WORKSPACES[role.id];
      expect(workspace.title.length).toBeGreaterThan(3);
      expect(workspace.spaceLabel.length).toBeGreaterThan(3);
      expect(workspace.primaryAction.href).toMatch(/^\//);
      expect(workspace.guideIntroduction.length).toBeGreaterThan(3);
      expect(workspace.tasks.length).toBeGreaterThanOrEqual(2);
    }
  });
});
