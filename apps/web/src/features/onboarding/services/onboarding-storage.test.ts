import { afterEach, describe, expect, it, vi } from "vitest";
import {
  loadWorkspaceRole,
  saveWorkspaceRole,
} from "@/features/auth/services/workspace-storage";
import {
  loadModulePreferences,
  saveModulePreferences,
} from "@/features/navigation/services/module-storage";
import {
  loadGuideProgress,
  saveGuideProgress,
} from "@/features/onboarding/services/onboarding-storage";

function stubLocalStorage() {
  const values = new Map<string, string>();
  vi.stubGlobal("window", {
    localStorage: {
      getItem: (key: string) => values.get(key) ?? null,
      setItem: (key: string, value: string) => values.set(key, value),
    },
  });
  return values;
}

afterEach(() => {
  vi.unstubAllGlobals();
});

describe("persistance locale des préférences UX", () => {
  it("restaure uniquement un rôle de démonstration connu", () => {
    const values = stubLocalStorage();
    saveWorkspaceRole("teacher");
    expect(loadWorkspaceRole()).toBe("teacher");

    values.set("lavision.workspace.role.v1", JSON.stringify("super_admin"));
    expect(loadWorkspaceRole()).toBeNull();

    values.set("lavision.workspace.role.v1", "{invalide");
    expect(loadWorkspaceRole()).toBeNull();
  });

  it("restaure les modules et ignore les préférences d’un mauvais type", () => {
    const values = stubLocalStorage();
    saveModulePreferences({ grades: false, students: true });
    expect(loadModulePreferences()).toMatchObject({ grades: false, students: true, settings: true });

    values.set("lavision.modules.v1", JSON.stringify({ grades: "false", reports: false }));
    expect(loadModulePreferences()).toMatchObject({ grades: true, reports: false, settings: true });
  });

  it("reprend la progression du guide séparément pour chaque rôle", () => {
    stubLocalStorage();
    saveGuideProgress({
      teacher: { status: "in_progress", step: 2 },
      direction: { status: "completed", step: 3 },
    });
    expect(loadGuideProgress()).toEqual({
      teacher: { status: "in_progress", step: 2 },
      direction: { status: "completed", step: 3 },
    });
  });

  it("borne les étapes et écarte les progressions invalides", () => {
    const values = stubLocalStorage();
    values.set("lavision.onboarding.v1", JSON.stringify({
      teacher: { status: "in_progress", step: 99 },
      accountant: { status: "completed", step: -4 },
      student: { status: "unknown", step: 1 },
      employee: null,
    }));
    expect(loadGuideProgress()).toEqual({
      teacher: { status: "in_progress", step: 3 },
      accountant: { status: "completed", step: 0 },
    });
  });
});
