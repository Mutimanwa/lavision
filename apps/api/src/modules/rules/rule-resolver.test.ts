import { describe, expect, it } from "vitest";
import { RULE_DEFINITIONS } from "@lavision/contracts";
import { compareExactDecimals, resolveRules, versionAppliesToContext, type ResolverVersion } from "./rule-resolver.js";

const organizationId = "00000000-0000-4000-8000-000000000001";
const programId = "00000000-0000-4000-8000-000000000002";
const otherProgramId = "00000000-0000-4000-8000-000000000003";
const definitions = RULE_DEFINITIONS.filter((entry) =>
  ["academic.grading.scale_min", "academic.grading.scale_max", "academic.pass.mark"].includes(entry.key),
);
const scopeOrder = ["organization", "program"] as const;
const context = { organizationId, programId };

function version(
  id: string,
  number: number,
  effectiveFrom: string,
  scopeType: "organization" | "program",
  scopeId: string,
  values: Array<{ key: string; value: unknown }>,
  status = "PUBLISHED",
): ResolverVersion {
  return {
    id,
    version: number,
    status,
    effectiveFrom,
    effectiveTo: null,
    values: values.map((entry) => ({ ...entry, notApplicable: false, reason: null })),
    bindings: [{ scopeType, scopeId }],
  };
}

describe("résolution déterministe des règles", () => {
  it("compare les décimales sans erreur binaire", () => {
    expect(compareExactDecimals("120.50", "120.5")).toBe(0);
    expect(compareExactDecimals("0.010", "0.009")).toBeGreaterThan(0);
    expect(compareExactDecimals("-10", "-2")).toBeLessThan(0);
  });

  it("fait hériter un programme de l’organisation et conserve la provenance", () => {
    const result = resolveRules({
      ruleSetId: "set-1",
      asOf: "2026-10-01",
      scopeOrder,
      definitions,
      context,
      versions: [
        version("v1", 1, "2026-01-01", "organization", organizationId, [
          { key: "academic.grading.scale_min", value: "0" },
          { key: "academic.grading.scale_max", value: "20" },
          { key: "academic.pass.mark", value: "10" },
        ]),
        version("v2", 2, "2026-01-01", "program", programId, [
          { key: "academic.grading.scale_max", value: "120" },
          { key: "academic.pass.mark", value: "60" },
        ]),
      ],
    });
    expect(result.status).toBe("RESOLVED");
    expect(result.values.find((entry) => entry.key === "academic.grading.scale_max")).toMatchObject({
      value: "120",
      source: { versionId: "v2", inherited: false },
    });
    expect(result.values.find((entry) => entry.key === "academic.grading.scale_min")).toMatchObject({
      value: "0",
      source: { versionId: "v1", inherited: true },
    });
  });

  it("n’applique une version future qu’à sa date et choisit la succession la plus récente", () => {
    const base = version("v1", 1, "2026-01-01", "program", programId, [
      { key: "academic.grading.scale_max", value: "20" },
    ]);
    const future = version("v2", 2, "2027-01-01", "program", programId, [
      { key: "academic.grading.scale_max", value: "120" },
    ], "SCHEDULED");
    const common = { ruleSetId: "set-1", scopeOrder, definitions: definitions.filter((entry) => entry.key === "academic.grading.scale_max"), context, versions: [base, future] };
    expect(resolveRules({ ...common, asOf: "2026-12-31" }).values[0]?.value).toBe("20");
    expect(resolveRules({ ...common, asOf: "2027-01-01" }).values[0]?.value).toBe("120");
  });

  it("exige une portée réellement applicable pour la preuve de simulation", () => {
    const candidate = version("v1", 1, "2026-01-01", "program", programId, [
      { key: "academic.grading.scale_max", value: "120" },
    ]);
    expect(versionAppliesToContext(candidate, "2026-10-01", scopeOrder, { organizationId })).toBe(false);
    expect(versionAppliesToContext(candidate, "2026-10-01", scopeOrder, { organizationId, programId })).toBe(true);
    expect(versionAppliesToContext(candidate, "2025-12-31", scopeOrder, { organizationId, programId })).toBe(false);
  });

  it("signale un conflit de même portée/date et une règle obligatoire absente", () => {
    const left = version("v1", 1, "2026-01-01", "program", programId, [
      { key: "academic.grading.scale_max", value: "20" },
    ]);
    const right = version("v2", 2, "2026-01-01", "program", programId, [
      { key: "academic.grading.scale_max", value: "120" },
    ]);
    const result = resolveRules({
      ruleSetId: "set-1",
      asOf: "2026-10-01",
      scopeOrder,
      definitions,
      context,
      versions: [left, right],
    });
    expect(result.status).toBe("CONFLICT");
    expect(result.conflicts[0]?.code).toBe("AMBIGUOUS_SAME_SCOPE_AND_DATE");
    expect(result.missing.some((entry) => entry.key === "academic.grading.scale_min")).toBe(true);
  });

  it("utilise le même chemin pour un brouillon candidat et détecte les incohérences", () => {
    const base = version("v1", 1, "2026-01-01", "organization", organizationId, [
      { key: "academic.grading.scale_min", value: "0" },
      { key: "academic.grading.scale_max", value: "20" },
      { key: "academic.pass.mark", value: "10" },
    ]);
    const candidate = version("v2", 2, "2026-06-01", "program", programId, [
      { key: "academic.grading.scale_max", value: "5" },
      { key: "academic.pass.mark", value: "10" },
    ], "DRAFT");
    const result = resolveRules({
      ruleSetId: "set-1",
      asOf: "2026-10-01",
      scopeOrder,
      definitions,
      context,
      versions: [base],
      candidate,
    });
    expect(result.status).toBe("CONFLICT");
    expect(result.conflicts.some((entry) => entry.code === "PASS_MARK_OUTSIDE_SCALE")).toBe(true);
    expect(result.values.find((entry) => entry.key === "academic.grading.scale_max")?.source.versionId).toBe("v2");
  });

  it("ne mélange pas deux programmes", () => {
    const result = resolveRules({
      ruleSetId: "set-1",
      asOf: "2026-10-01",
      scopeOrder,
      definitions: definitions.filter((entry) => entry.key === "academic.grading.scale_max"),
      context: { organizationId, programId: otherProgramId },
      versions: [version("v1", 1, "2026-01-01", "program", programId, [{ key: "academic.grading.scale_max", value: "120" }])],
    });
    expect(result.values).toHaveLength(0);
    expect(result.missing[0]?.code).toBe("MISSING_REQUIRED");
  });
});
