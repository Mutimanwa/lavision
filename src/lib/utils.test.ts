import { describe, expect, it } from "vitest";
import { createSeedState } from "./seed";
import { isAppState } from "./utils";

describe("validation des sauvegardes", () => {
  it("accepte une sauvegarde lavision valide", () => {
    expect(isAppState(createSeedState())).toBe(true);
  });

  it("refuse une version de schéma inconnue", () => {
    expect(isAppState({ ...createSeedState(), version: 2 })).toBe(false);
  });

  it("refuse une note hors du barème", () => {
    const state = createSeedState();
    state.grades[0].value = 25;
    expect(isAppState(state)).toBe(false);
  });

  it("refuse un dossier élève sans responsable exploitable", () => {
    const state = createSeedState();
    const invalid = { ...state.students[0], guardian: null };
    expect(isAppState({ ...state, students: [invalid] })).toBe(false);
  });

  it("refuse un seuil de réussite invalide", () => {
    const state = createSeedState();
    state.settings.passMark = Number.NaN;
    expect(isAppState(state)).toBe(false);
  });
});
