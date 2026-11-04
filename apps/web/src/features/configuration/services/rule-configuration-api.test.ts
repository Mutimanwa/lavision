import { beforeEach, describe, expect, it, vi } from "vitest";
import type { RuleDefinitionResponse } from "@lavision/contracts";
import { apiFetch } from "@/lib/api-client";
import { prepareRuleDraft, type RuleSetSummary } from "./rule-configuration-api";
import { ruleSetCode } from "./rule-value";

vi.mock("@/lib/api-client", () => ({ apiFetch: vi.fn() }));

const definition: RuleDefinitionResponse = {
  key: "academic.grading.scale_max",
  domain: "academic",
  label: "Maximum du barème",
  description: "Maximum propre au cours et au programme.",
  type: "decimal",
  required: true,
  scopes: ["organization", "school_year", "program", "subject"],
  constraints: { min: 0.000001, max: 1000000 },
};

const input = {
  organizationId: "org-1",
  definition,
  value: "120",
  effectiveFrom: "2026-09-01",
  timezone: "Africa/Bujumbura",
  source: "Délibération à vérifier",
  reason: "Préparation d’une proposition soumise au circuit.",
};

function set(overrides: Partial<RuleSetSummary> = {}): RuleSetSummary {
  return {
    id: "set-1",
    organizationId: "org-1",
    domain: "academic",
    code: ruleSetCode(definition.key),
    name: "Configuration",
    purpose: "Préparation",
    scopeOrder: [...definition.scopes],
    definitionKeys: [definition.key],
    archivedAt: null,
    ...overrides,
  };
}

beforeEach(() => {
  vi.mocked(apiFetch).mockReset();
});

describe("préparation d’un brouillon de règle", () => {
  it("réutilise uniquement l’ensemble API compatible et ne publie rien", async () => {
    vi.mocked(apiFetch).mockImplementation(async (path) => {
      if (path.startsWith("/rules/sets?")) return { sets: [set()] };
      if (path === "/rules/sets/set-1/versions") return { id: "version-1", ruleSetId: "set-1", version: 1, status: "DRAFT" };
      throw new Error(`Requête inattendue : ${path}`);
    });

    await expect(prepareRuleDraft(input)).resolves.toMatchObject({ id: "version-1", status: "DRAFT" });
    expect(vi.mocked(apiFetch)).toHaveBeenCalledTimes(2);
    const [path, init] = vi.mocked(apiFetch).mock.calls[1];
    expect(path).toBe("/rules/sets/set-1/versions");
    expect(init?.method).toBe("POST");
    expect(JSON.parse(String(init?.body))).toMatchObject({
      organizationId: "org-1",
      values: [{ key: definition.key, value: "120", notApplicable: false }],
      bindings: [{ scopeType: "organization", scopeId: "org-1" }],
    });
    expect(vi.mocked(apiFetch).mock.calls.some(([url]) => url.includes("publish"))).toBe(false);
  });

  it("crée l’ensemble avec toutes les portées du catalogue, sans valeur implicite", async () => {
    vi.mocked(apiFetch).mockImplementation(async (path) => {
      if (path.startsWith("/rules/sets?")) return { sets: [] };
      if (path === "/rules/sets") return set();
      if (path === "/rules/sets/set-1/versions") return { id: "version-1", ruleSetId: "set-1", version: 1, status: "DRAFT" };
      throw new Error(`Requête inattendue : ${path}`);
    });

    await prepareRuleDraft(input);
    const createCall = vi.mocked(apiFetch).mock.calls.find(([path]) => path === "/rules/sets");
    expect(createCall).toBeDefined();
    expect(JSON.parse(String(createCall?.[1]?.body))).toMatchObject({
      scopeOrder: definition.scopes,
      definitionKeys: [definition.key],
    });
  });

  it("conserve un non-applicable motivé sans fabriquer de valeur", async () => {
    const optional = { ...definition, key: "academic.average.provisional_display", type: "boolean" as const, required: false, scopes: ["organization", "school_year", "level"] as RuleDefinitionResponse["scopes"] };
    vi.mocked(apiFetch).mockImplementation(async (path) => {
      if (path.startsWith("/rules/sets?")) return { sets: [set({ definitionKeys: [optional.key], scopeOrder: [...optional.scopes] })] };
      if (path === "/rules/sets/set-1/versions") return { id: "version-na", ruleSetId: "set-1", version: 2, status: "DRAFT" };
      throw new Error(`Requête inattendue : ${path}`);
    });

    await prepareRuleDraft({ ...input, definition: optional, value: undefined, notApplicable: true });
    const body = JSON.parse(String(vi.mocked(apiFetch).mock.calls[1][1]?.body)) as { values: Array<Record<string, unknown>> };
    expect(body.values[0]).toEqual({ key: optional.key, notApplicable: true, reason: input.reason });
    expect(body.values[0]).not.toHaveProperty("value");
  });

  it("refuse une valeur absente ou contradictoire avant toute écriture API", async () => {
    await expect(prepareRuleDraft({ ...input, value: undefined })).rejects.toThrow("Une valeur est requise");
    await expect(prepareRuleDraft({ ...input, notApplicable: true })).rejects.toThrow("Ne fournissez pas de valeur");
    await expect(prepareRuleDraft({ ...input, value: undefined, notApplicable: true })).rejects.toThrow("obligatoire");
    expect(vi.mocked(apiFetch)).not.toHaveBeenCalled();
  });

  it("évite de réutiliser un code déjà occupé par une autre définition", async () => {
    const occupied = set({ id: "other", code: ruleSetCode(definition.key), definitionKeys: ["academic.other"] });
    vi.mocked(apiFetch).mockImplementation(async (path) => {
      if (path.startsWith("/rules/sets?")) return { sets: [occupied] };
      if (path === "/rules/sets") return set({ id: "new-set", code: "generated" });
      if (path === "/rules/sets/new-set/versions") return { id: "version-1", ruleSetId: "new-set", version: 1, status: "DRAFT" };
      throw new Error(`Requête inattendue : ${path}`);
    });

    await prepareRuleDraft(input);
    const createCall = vi.mocked(apiFetch).mock.calls.find(([path]) => path === "/rules/sets");
    const body = JSON.parse(String(createCall?.[1]?.body)) as { code: string };
    expect(body.code).not.toBe(occupied.code);
    expect(body.code).toMatch(/^[A-Za-z][A-Za-z0-9_-]{0,63}$/);
  });
});
