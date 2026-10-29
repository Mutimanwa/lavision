import { describe, expect, it } from "vitest";
import { RULE_DEFINITIONS } from "@lavision/contracts";
import { findRuleDefinition, validateRuleValue } from "./rule-catalog.js";

const definition = (key: string) => {
  const result = findRuleDefinition(key);
  if (!result) throw new Error(`Définition absente : ${key}`);
  return result;
};

describe("catalogue fermé et validation des valeurs #39", () => {
  it("conserve des clés uniques et ne fournit aucune valeur par défaut", () => {
    const keys = RULE_DEFINITIONS.map((entry) => entry.key);
    expect(new Set(keys).size).toBe(keys.length);
    expect(RULE_DEFINITIONS.every((entry) => !("defaultValue" in entry))).toBe(true);
  });

  it("accepte le maximum local 120 sans le transformer en règle globale", () => {
    expect(validateRuleValue(definition("academic.grading.scale_max"), 120)).toEqual({
      valid: true,
      normalizedValue: "120",
      issues: [],
    });
    expect(validateRuleValue(definition("academic.grading.scale_max"), "120.50").normalizedValue).toBe("120.50");
  });

  it("rejette les formules, les objets et les valeurs hors bornes", () => {
    for (const value of ["=120", "120; DROP TABLE rules", { value: 120 }, -1, 0]) {
      expect(validateRuleValue(definition("academic.grading.scale_max"), value).valid).toBe(false);
    }
    expect(validateRuleValue(definition("academic.rounding.calculation_precision"), 7).valid).toBe(false);
    expect(validateRuleValue(definition("academic.rounding.mode"), "half_even").valid).toBe(true);
    expect(validateRuleValue(definition("academic.rounding.mode"), "javascript:alert(1)").valid).toBe(false);
  });

  it("préserve la différence entre valeur absente et valeur invalide", () => {
    expect(validateRuleValue(definition("academic.grading.scale_max"), undefined).issues[0]?.code).toBe("REQUIRED");
    expect(validateRuleValue(definition("academic.grading.input_increment"), undefined)).toEqual({ valid: true, issues: [] });
    expect(validateRuleValue(definition("academic.grading.input_increment"), null).valid).toBe(false);
    expect(validateRuleValue(definition("academic.average.provisional_display"), "true").valid).toBe(false);
  });

  it("valide les codes devise et les dates sans accepter de date normalisée", () => {
    expect(validateRuleValue(definition("finance.currency.code"), "BIF").valid).toBe(true);
    expect(validateRuleValue(definition("finance.currency.code"), "bif").valid).toBe(false);
    expect(validateRuleValue(definition("academic.rounding.mode"), null).valid).toBe(false);
  });
});
