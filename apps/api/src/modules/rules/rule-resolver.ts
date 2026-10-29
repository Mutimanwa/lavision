import type {
  RuleDefinition,
  RuleResolutionScope,
  RuleScope,
} from "@lavision/contracts";

export interface ResolverValue {
  key: string;
  value: unknown;
  notApplicable: boolean;
  reason: string | null;
}

export interface ResolverBinding {
  scopeType: RuleScope;
  scopeId: string;
}

export interface ResolverVersion {
  id: string;
  version: number;
  status: string;
  effectiveFrom: string;
  effectiveTo: string | null;
  archivedAt?: string | null;
  values: readonly ResolverValue[];
  bindings: readonly ResolverBinding[];
}

export interface ResolutionIssue {
  key: string;
  code: string;
  message: string;
}

export interface ResolvedValue {
  key: string;
  value: unknown;
  notApplicable: boolean;
  reason: string | null;
  source: {
    versionId: string;
    version: number;
    scopeType: RuleScope;
    scopeId: string;
    inherited: boolean;
  };
}

export interface ResolutionResult {
  ruleSetId: string;
  asOf: string;
  status: "RESOLVED" | "INCOMPLETE" | "CONFLICT";
  values: ResolvedValue[];
  missing: ResolutionIssue[];
  unconfigured: string[];
  conflicts: ResolutionIssue[];
  warnings: string[];
}

export interface ResolveRulesInput {
  ruleSetId: string;
  asOf: string;
  scopeOrder: readonly RuleScope[];
  definitions: readonly RuleDefinition[];
  context: RuleResolutionScope & { organizationId: string };
  versions: readonly ResolverVersion[];
  candidate?: ResolverVersion;
}

function canonical(value: unknown): string {
  if (value === null || typeof value !== "object") return JSON.stringify(value);
  if (Array.isArray(value)) return `[${value.map(canonical).join(",")}]`;
  return `{${Object.entries(value as Record<string, unknown>)
    .filter(([, entry]) => entry !== undefined)
    .sort(([left], [right]) => left.localeCompare(right))
    .map(([key, entry]) => `${JSON.stringify(key)}:${canonical(entry)}`)
    .join(",")}}`;
}

function scopeIds(context: ResolveRulesInput["context"]): Record<RuleScope, string | undefined> {
  return {
    organization: context.organizationId,
    site: context.siteId,
    school_year: context.schoolYearId,
    level: context.levelId,
    section: context.sectionId,
    option: context.optionId,
    program: context.programId,
    class: context.classId,
    subject: context.subjectId,
  };
}

function applies(version: ResolverVersion, asOf: string): boolean {
  return (
    !version.archivedAt &&
    version.effectiveFrom <= asOf &&
    (version.effectiveTo === null || asOf < version.effectiveTo)
  );
}

interface Candidate {
  version: ResolverVersion;
  binding: ResolverBinding;
  value: ResolverValue;
  priority: number;
}

function decimalParts(value: unknown): { sign: number; digits: string; scale: number } | null {
  if (typeof value !== "string" && typeof value !== "number") return null;
  const raw = String(value);
  if (!/^-?(?:0|[1-9]\d*)(?:\.\d+)?$/.test(raw)) return null;
  const negative = raw.startsWith("-");
  const unsigned = negative ? raw.slice(1) : raw;
  const [integer, fraction = ""] = unsigned.split(".");
  const digits = `${integer}${fraction}`.replace(/^0+(?=\d)/, "");
  return { sign: negative && Number(unsigned) !== 0 ? -1 : 1, digits, scale: fraction.length };
}

/** Comparaison exacte de décimales normalisées, sans les convertir en float. */
export function compareExactDecimals(left: unknown, right: unknown): number | null {
  const a = decimalParts(left);
  const b = decimalParts(right);
  if (!a || !b) return null;
  if (a.sign !== b.sign) return a.sign < b.sign ? -1 : 1;
  const scale = Math.max(a.scale, b.scale);
  const aDigits = a.digits.padEnd(a.digits.length + scale - a.scale, "0");
  const bDigits = b.digits.padEnd(b.digits.length + scale - b.scale, "0");
  const comparison = aDigits.length === bDigits.length
    ? aDigits.localeCompare(bDigits)
    : aDigits.length - bDigits.length;
  return a.sign < 0 ? -comparison : comparison;
}

export function resolvedCoherenceIssues(values: readonly ResolvedValue[]): ResolutionIssue[] {
  const byKey = new Map(values.map((entry) => [entry.key, entry]));
  const issues: ResolutionIssue[] = [];
  const min = byKey.get("academic.grading.scale_min");
  const max = byKey.get("academic.grading.scale_max");
  if (min && max && !min.notApplicable && !max.notApplicable) {
    const comparison = compareExactDecimals(max.value, min.value);
    if (comparison === null || comparison <= 0) {
      issues.push({
        key: "academic.grading.scale_max",
        code: "INCOHERENT_SCALE",
        message: "Le maximum doit être strictement supérieur au minimum dans le même contexte résolu.",
      });
    }
  }
  const pass = byKey.get("academic.pass.mark");
  if (pass && min && max && !pass.notApplicable && !min.notApplicable && !max.notApplicable) {
    const aboveMin = compareExactDecimals(pass.value, min.value);
    const belowMax = compareExactDecimals(pass.value, max.value);
    if (aboveMin === null || belowMax === null || aboveMin < 0 || belowMax > 0) {
      issues.push({
        key: "academic.pass.mark",
        code: "PASS_MARK_OUTSIDE_SCALE",
        message: "Le seuil de réussite doit rester dans le barème résolu.",
      });
    }
  }
  return issues;
}

/**
 * Résolveur unique utilisé par la lecture publiée, les simulations et les snapshots.
 * L’ordre des portées est fourni par le RuleSet ; aucune priorité métier n’est cachée ici.
 */
export function resolveRules(input: ResolveRulesInput): ResolutionResult {
  const ids = scopeIds(input.context);
  const requestedScopes = input.scopeOrder.filter((scope) => ids[scope] !== undefined);
  const mostSpecific = requestedScopes.at(-1);
  const versions = input.versions.filter(
    (version) =>
      version.id !== input.candidate?.id &&
      (version.status === "PUBLISHED" || version.status === "SCHEDULED") &&
      applies(version, input.asOf),
  );
  if (input.candidate && applies(input.candidate, input.asOf)) versions.push(input.candidate);

  const resolved: ResolvedValue[] = [];
  const missing: ResolutionIssue[] = [];
  const unconfigured: string[] = [];
  const conflicts: ResolutionIssue[] = [];
  const warnings = new Set<string>();

  for (const definition of input.definitions) {
    const candidates: Candidate[] = [];
    for (const version of versions) {
      const value = version.values.find((entry) => entry.key === definition.key);
      if (!value) continue;
      for (const binding of version.bindings) {
        const priority = input.scopeOrder.indexOf(binding.scopeType);
        if (
          priority < 0 ||
          ids[binding.scopeType] !== binding.scopeId ||
          !definition.scopes.includes(binding.scopeType)
        ) continue;
        candidates.push({ version, binding, value, priority });
      }
    }

    if (candidates.length === 0) {
      unconfigured.push(definition.key);
      if (definition.required) {
        missing.push({
          key: definition.key,
          code: "MISSING_REQUIRED",
          message: "Aucune valeur publiée applicable à cette date et cette portée.",
        });
      }
      continue;
    }

    const highestPriority = Math.max(...candidates.map((candidate) => candidate.priority));
    const atScope = candidates.filter((candidate) => candidate.priority === highestPriority);
    const latestDate = atScope.reduce(
      (latest, candidate) => candidate.version.effectiveFrom > latest ? candidate.version.effectiveFrom : latest,
      "",
    );
    const atDate = atScope.filter((candidate) => candidate.version.effectiveFrom === latestDate);
    const distinctValues = new Map(
      atDate.map((candidate) => [
        canonical({ value: candidate.value.value, notApplicable: candidate.value.notApplicable }),
        candidate,
      ]),
    );
    if (distinctValues.size > 1) {
      conflicts.push({
        key: definition.key,
        code: "AMBIGUOUS_SAME_SCOPE_AND_DATE",
        message: "Plusieurs versions incompatibles ont la même portée et la même date d’effet.",
      });
      continue;
    }

    const selected = atDate.sort((left, right) => right.version.version - left.version.version)[0]!;
    if (atDate.length > 1) warnings.add(`Plusieurs versions équivalentes existent pour ${definition.key} ; la version la plus récente est retenue.`);
    resolved.push({
      key: definition.key,
      value: selected.value.value,
      notApplicable: selected.value.notApplicable,
      reason: selected.value.reason,
      source: {
        versionId: selected.version.id,
        version: selected.version.version,
        scopeType: selected.binding.scopeType,
        scopeId: selected.binding.scopeId,
        inherited: !mostSpecific || selected.binding.scopeType !== mostSpecific || selected.binding.scopeId !== ids[mostSpecific],
      },
    });
  }

  conflicts.push(...resolvedCoherenceIssues(resolved));
  const status = conflicts.length > 0 ? "CONFLICT" : missing.length > 0 ? "INCOMPLETE" : "RESOLVED";
  return {
    ruleSetId: input.ruleSetId,
    asOf: input.asOf,
    status,
    values: resolved,
    missing,
    unconfigured,
    conflicts,
    warnings: [...warnings].sort(),
  };
}
