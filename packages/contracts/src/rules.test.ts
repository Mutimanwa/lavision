import { describe, expect, it } from "vitest";
import {
  RULE_DEFINITIONS,
  ruleCatalogResponseSchema,
  ruleValidationInputSchema,
} from "./index.js";

describe("contrats du catalogue de règles #39", () => {
  it("expose un catalogue typé et borné, sans schéma de formule libre", () => {
    expect(RULE_DEFINITIONS.length).toBeGreaterThan(20);
    expect(RULE_DEFINITIONS.some((entry) => entry.key === "academic.grading.scale_max")).toBe(true);
    expect(RULE_DEFINITIONS.every((entry) => entry.scopes.length > 0)).toBe(true);
    expect(
      ruleValidationInputSchema.safeParse({
        organizationId: "00000000-0000-4000-8000-000000000001",
        key: "academic.grading.scale_max",
        value: 120,
        formula: "value * 2",
      }).success,
    ).toBe(false);
  });

  it("refuse une réponse de catalogue avec une portée inconnue", () => {
    expect(
      ruleCatalogResponseSchema.safeParse({
        catalogVersion: 1,
        definitions: [{ ...RULE_DEFINITIONS[0], scopes: ["unknown"] }],
      }).success,
    ).toBe(false);
  });
});
