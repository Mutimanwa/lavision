import { describe, expect, it } from "vitest";
import { createSeedState } from "@/features/school/data/seed";
import { formatDate, isAppState } from "./utils";

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

  it("refuse des coordonnées d’établissement incomplètes", () => {
    const state = createSeedState();
    const settings: Record<string, unknown> = { ...state.settings };
    delete settings.email;
    expect(isAppState({ ...state, settings })).toBe(false);
  });

  it("refuse un dossier élève avec un sexe inconnu ou une date de naissance manquante", () => {
    const state = createSeedState();
    expect(isAppState({
      ...state,
      students: [{ ...state.students[0], gender: "Inconnu" }],
    })).toBe(false);
    expect(isAppState({
      ...state,
      students: [{ ...state.students[0], birthDate: undefined }],
    })).toBe(false);
  });

  it("refuse une note sans horodatage exploitable", () => {
    const state = createSeedState();
    expect(isAppState({
      ...state,
      grades: [{ ...state.grades[0], updatedAt: undefined }],
    })).toBe(false);
  });

  it("accepte les anciennes sauvegardes sans localisation du bulletin", () => {
    const state = createSeedState();
    const settings: Record<string, unknown> = { ...state.settings };
    delete settings.country;
    delete settings.city;
    expect(isAppState({ ...state, settings })).toBe(true);
  });

  it("refuse une localisation de bulletin d’un mauvais type", () => {
    const state = createSeedState();
    expect(isAppState({
      ...state,
      settings: { ...state.settings, city: null },
    })).toBe(false);
  });

  it("refuse une capacité de classe fractionnaire", () => {
    const state = createSeedState();
    expect(isAppState({
      ...state,
      classes: [{ ...state.classes[0], capacity: 12.5 }],
    })).toBe(false);
  });

  it("refuse une date de dossier qui ne peut pas être affichée", () => {
    const state = createSeedState();
    expect(isAppState({
      ...state,
      students: [{ ...state.students[0], birthDate: "date-invalide" }],
    })).toBe(false);
  });

  it("affiche un repli plutôt que de faire échouer le rendu pour une date invalide", () => {
    expect(formatDate("date-invalide")).toBe("—");
  });
});
