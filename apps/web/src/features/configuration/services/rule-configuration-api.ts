import type {
  AuthMeResponse,
  RuleDefinitionResponse,
  RuleDomain,
  RuleScope,
  RuleSetCreateInput,
  RuleValidationResponse,
  RuleVersionCreateInput,
} from "@lavision/contracts";
import { apiFetch } from "@/lib/api-client";
import { ruleSetCode } from "./rule-value";

export interface RuleCatalogResult {
  catalogVersion: number;
  definitions: RuleDefinitionResponse[];
}

export interface RuleSetSummary {
  id: string;
  organizationId: string;
  domain: RuleDomain;
  code: string;
  name: string;
  purpose: string;
  scopeOrder: RuleScope[];
  definitionKeys: string[];
  archivedAt: string | null;
}

export interface RuleDraftResult {
  id: string;
  ruleSetId: string;
  version: number;
  status: string;
}

export function getConfigurationSession() {
  return apiFetch<AuthMeResponse>("/auth/me");
}

export function getRuleCatalog(organizationId: string, domain: RuleDomain, search = "") {
  const query = new URLSearchParams({ organizationId, domain });
  if (search.trim()) query.set("search", search.trim());
  return apiFetch<RuleCatalogResult>(`/rules/catalog?${query.toString()}`);
}

export function validateRuleConfigurationValue(organizationId: string, key: string, value: unknown) {
  return apiFetch<RuleValidationResponse>("/rules/validate", {
    method: "POST",
    body: JSON.stringify({ organizationId, key, value }),
  });
}

function listRuleSets(organizationId: string, domain: RuleDomain) {
  const query = new URLSearchParams({ organizationId, domain, includeArchived: "true" });
  return apiFetch<{ sets: RuleSetSummary[] }>(`/rules/sets?${query.toString()}`);
}

function createRuleSet(input: RuleSetCreateInput) {
  return apiFetch<RuleSetSummary>("/rules/sets", { method: "POST", body: JSON.stringify(input) });
}

function createRuleVersion(setId: string, input: RuleVersionCreateInput) {
  return apiFetch<RuleDraftResult>(`/rules/sets/${setId}/versions`, {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export interface PrepareRuleDraftInput {
  organizationId: string;
  definition: RuleDefinitionResponse;
  value?: unknown;
  notApplicable?: boolean;
  effectiveFrom: string;
  timezone: string;
  source: string;
  reason: string;
}

/** Crée uniquement un brouillon API : aucune transition de validation ou publication. */
export async function prepareRuleDraft(input: PrepareRuleDraftInput): Promise<RuleDraftResult> {
  const { organizationId, definition } = input;
  if (input.notApplicable && input.value !== undefined) {
    throw new Error("Ne fournissez pas de valeur avec un non-applicable.");
  }
  if (input.notApplicable && definition.required) {
    throw new Error("Une règle obligatoire ne peut pas être déclarée non applicable.");
  }
  if (input.notApplicable && !input.reason.trim()) {
    throw new Error("Le non-applicable exige un motif explicite.");
  }
  if (!input.notApplicable && input.value === undefined) {
    throw new Error("Une valeur est requise avant de créer un brouillon ; elle ne sera pas déduite.");
  }
  const existing = await listRuleSets(organizationId, definition.domain);
  let set = existing.sets.find((candidate) =>
    !candidate.archivedAt
    && candidate.definitionKeys.length === 1
    && candidate.definitionKeys[0] === definition.key
    && candidate.scopeOrder.join(",") === definition.scopes.join(","),
  );

  if (!set) {
    const usedCodes = new Set(existing.sets.map((candidate) => candidate.code));
    let attempt = 0;
    let code = ruleSetCode(definition.key);
    while (usedCodes.has(code)) {
      attempt += 1;
      code = ruleSetCode(definition.key, attempt);
    }
    set = await createRuleSet({
      organizationId,
      domain: definition.domain,
      code,
      name: `Configuration — ${definition.label}`.slice(0, 160),
      purpose: "Paramètre préparé depuis Configuration scolaire ; aucune valeur officielle implicite.",
      scopeOrder: [...definition.scopes],
      definitionKeys: [definition.key],
    });
  }

  return createRuleVersion(set.id, {
    organizationId,
    effectiveFrom: input.effectiveFrom,
    timezone: input.timezone,
    source: input.source,
    reason: input.reason,
    values: [{
      key: definition.key,
      ...(input.notApplicable
        ? { notApplicable: true, reason: input.reason }
        : { value: input.value, notApplicable: false }),
    }],
    bindings: [{ scopeType: "organization", scopeId: organizationId }],
  });
}
