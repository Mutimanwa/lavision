import { describe, expect, it } from "vitest";
import { isValidGradeInput, parseGradeInput } from "./grade-input";

describe("saisie des notes du prototype", () => {
  it("accepte les décimales avec virgule ou point", () => {
    expect(parseGradeInput("12,75")).toBe(12.75);
    expect(parseGradeInput(" 12.75 ")).toBe(12.75);
  });

  it("distingue une cellule vide d’une valeur invalide", () => {
    expect(parseGradeInput("")).toBeNull();
    expect(isValidGradeInput("")).toBe(true);
    expect(parseGradeInput("abc")).toBeNull();
    expect(isValidGradeInput("abc")).toBe(false);
  });

  it("contrôle les bornes déjà affichées par le prototype", () => {
    expect(isValidGradeInput("0")).toBe(true);
    expect(isValidGradeInput("20")).toBe(true);
    expect(isValidGradeInput("-0,25")).toBe(false);
    expect(isValidGradeInput("20,25")).toBe(false);
  });
});
