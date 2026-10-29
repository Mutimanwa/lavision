import { createHash } from "node:crypto";
import type { RuleBindingInput, RuleScope } from "@lavision/contracts";
import { Prisma } from "../../database/client.js";
import type { RuleVersionWithChildren } from "./rule-presenters.js";
import type { ResolverVersion } from "./rule-resolver.js";

export interface NormalizedRuleValue {
  key: string;
  value: unknown;
  notApplicable: boolean;
  reason: string | null;
}

export function canonicalJson(value: unknown): string {
  if (value === undefined) return '"__undefined__"';
  if (value === null || typeof value !== "object") return JSON.stringify(value);
  if (Array.isArray(value)) return `[${value.map(canonicalJson).join(",")}]`;
  return `{${Object.entries(value as Record<string, unknown>)
    .filter(([, entry]) => entry !== undefined)
    .sort(([left], [right]) => left.localeCompare(right))
    .map(([key, entry]) => `${JSON.stringify(key)}:${canonicalJson(entry)}`)
    .join(",")}}`;
}

export function sha256(value: unknown): string {
  return createHash("sha256").update(canonicalJson(value)).digest("hex");
}

export function asJson(value: unknown): Prisma.InputJsonValue {
  return JSON.parse(JSON.stringify(value)) as Prisma.InputJsonValue;
}

export function dateOnly(value: string): Date {
  return new Date(`${value}T00:00:00.000Z`);
}

export function isoDate(value: Date): string {
  return value.toISOString().slice(0, 10);
}

export function isValidTimezone(timezone: string): boolean {
  try {
    new Intl.DateTimeFormat("en-US", { timeZone: timezone }).format();
    return true;
  } catch {
    return false;
  }
}

export function dateInTimezone(date: Date, timezone: string): string {
  const parts = new Intl.DateTimeFormat("en-US", {
    timeZone: timezone,
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  }).formatToParts(date);
  const values = Object.fromEntries(parts.map((part) => [part.type, part.value]));
  return `${values.year}-${values.month}-${values.day}`;
}

export function versionContentHash(input: {
  scopeOrder: readonly RuleScope[];
  definitionKeys: readonly string[];
  effectiveFrom: string;
  effectiveTo: string | null;
  timezone: string;
  values: readonly NormalizedRuleValue[];
  bindings: readonly RuleBindingInput[];
}): string {
  return sha256({
    scopeOrder: input.scopeOrder,
    definitionKeys: [...input.definitionKeys].sort(),
    effectiveFrom: input.effectiveFrom,
    effectiveTo: input.effectiveTo,
    timezone: input.timezone,
    values: [...input.values]
      .sort((left, right) => left.key.localeCompare(right.key))
      .map((entry) => ({ key: entry.key, value: entry.value, notApplicable: entry.notApplicable, reason: entry.reason })),
    bindings: [...input.bindings]
      .sort((left, right) => `${left.scopeType}:${left.scopeId}`.localeCompare(`${right.scopeType}:${right.scopeId}`)),
  });
}

export function toResolverVersion(version: RuleVersionWithChildren): ResolverVersion {
  return {
    id: version.id,
    version: version.version,
    status: version.status,
    effectiveFrom: isoDate(version.effectiveFrom),
    effectiveTo: version.effectiveTo ? isoDate(version.effectiveTo) : null,
    archivedAt: version.archivedAt?.toISOString() ?? null,
    values: version.values.map((entry) => ({
      key: entry.definitionKey,
      value: entry.value,
      notApplicable: entry.notApplicable,
      reason: entry.reason,
    })),
    bindings: version.bindings.map((entry) => ({
      scopeType: entry.scopeType as RuleScope,
      scopeId: entry.scopeId,
    })),
  };
}
