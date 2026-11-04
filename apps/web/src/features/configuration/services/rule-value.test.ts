import { describe, expect, it } from "vitest";
import { formatRuleValue, parseRuleValue, ruleSetCode } from "./rule-value";

describe("conversion explicite des paramètres", () => {
  it("ne transforme jamais une valeur vide en valeur par défaut", () => {
    for (const type of ["boolean", "integer", "decimal", "enum", "currency", "date", "structured"] as const) {
      expect(parseRuleValue({ type }, "  ")).toEqual({ value: undefined });
    }
  });

  it("exige un choix booléen explicite", () => {
    expect(parseRuleValue({ type: "boolean" }, "true")).toEqual({ value: true });
    expect(parseRuleValue({ type: "boolean" }, "false")).toEqual({ value: false });
    expect(parseRuleValue({ type: "boolean" }, "0").error).toBeTruthy();
  });

  it("préserve les décimales exactes et borne les entiers", () => {
    expect(parseRuleValue({ type: "decimal" }, "0.10")).toEqual({ value: "0.10" });
    expect(parseRuleValue({ type: "integer" }, "12")).toEqual({ value: 12 });
    expect(parseRuleValue({ type: "integer" }, "12.5").error).toBeTruthy();
    expect(parseRuleValue({ type: "integer" }, "9007199254740992").error).toBeTruthy();
  });

  it("accepte uniquement du JSON syntaxiquement valide pour une structure", () => {
    expect(parseRuleValue({ type: "structured" }, '{"items":[]}')).toEqual({ value: { items: [] } });
    expect(parseRuleValue({ type: "structured" }, "[1,2]")).toEqual({ value: [1, 2] });
    expect(parseRuleValue({ type: "structured" }, "{invalide").error).toBeTruthy();
  });

  it("formate une valeur normalisée sans la faire passer pour une valeur saisie", () => {
    expect(formatRuleValue("0.10")).toBe("0.10");
    expect(formatRuleValue({ ok: true })).toBe('{\n  "ok": true\n}');
  });

  it("produit un code stable et borné sans contenir la valeur métier", () => {
    const code = ruleSetCode("academic.grading.scale_max");
    expect(code).toBe(ruleSetCode("academic.grading.scale_max"));
    expect(code.length).toBeLessThanOrEqual(64);
    expect(code).not.toBe(ruleSetCode("academic.grading.scale_min"));
    expect(ruleSetCode("règle.établissement")).toMatch(/^[A-Za-z][A-Za-z0-9_-]{0,63}$/);
    expect(ruleSetCode("règle.établissement")).not.toBe(ruleSetCode("regle.etablissement"));
    expect(ruleSetCode("règle.établissement")).not.toBe(ruleSetCode("règle.établissement", 1));
  });
});
