import { afterEach, describe, expect, it, vi } from "vitest";
import {
  getProductTourStorageKey,
  loadProductTourProgress,
  saveProductTourProgress,
} from "./product-tour-storage";

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

describe("persistance du Product Tour", () => {
  it("conserve une progression distincte pour chaque rôle et chaque page", () => {
    stubLocalStorage();
    const teacherGrades = getProductTourStorageKey("teacher", "grades");
    const directionDashboard = getProductTourStorageKey("direction", "dashboard");
    saveProductTourProgress({
      [teacherGrades]: { status: "in_progress", step: 3 },
      [directionDashboard]: { status: "completed", step: 8 },
    });

    expect(loadProductTourProgress()).toEqual({
      [teacherGrades]: { status: "in_progress", step: 3 },
      [directionDashboard]: { status: "completed", step: 8 },
    });
  });

  it("écarte les clés et les valeurs invalides et borne les étapes", () => {
    const values = stubLocalStorage();
    values.set("lavision.product-tour.v1", JSON.stringify({
      "teacher:grades": { status: "in_progress", step: 999 },
      "direction:dashboard": { status: "completed", step: -4 },
      "unknown:grades": { status: "completed", step: 1 },
      "teacher:unknown": { status: "completed", step: 1 },
      "teacher:classes": { status: "unknown", step: 1 },
      "teacher:reports": null,
    }));

    expect(loadProductTourProgress()).toEqual({
      "teacher:grades": { status: "in_progress", step: 100 },
      "direction:dashboard": { status: "completed", step: 0 },
    });
  });

  it("ne laisse pas l’ancien guide textuel masquer la nouvelle visite", () => {
    const values = stubLocalStorage();
    values.set("lavision.onboarding.v1", JSON.stringify({
      direction: { status: "completed", step: 3 },
    }));

    expect(loadProductTourProgress()).toEqual({});
  });
});
