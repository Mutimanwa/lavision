import type { RuleDefinition, RuleValueType } from "@lavision/contracts";

export interface ParsedRuleValue {
  value?: unknown;
  error?: string;
}

export function parseRuleValue(definition: Pick<RuleDefinition, "type">, raw: string): ParsedRuleValue {
  const input = raw.trim();
  if (!input) return { value: undefined };

  switch (definition.type) {
    case "boolean":
      if (input === "true") return { value: true };
      if (input === "false") return { value: false };
      return { error: "Choisissez oui ou non, sans valeur implicite." };
    case "integer": {
      const value = Number(input);
      return Number.isSafeInteger(value) ? { value } : { error: "Saisissez un entier valide." };
    }
    case "decimal":
      return { value: input };
    case "enum":
    case "currency":
    case "date":
      return { value: input };
    case "structured":
      try {
        return { value: JSON.parse(input) as unknown };
      } catch {
        return { error: "Saisissez une liste ou un objet JSON valide." };
      }
    default: {
      const exhaustive: never = definition.type;
      return { error: `Type de paramètre non pris en charge : ${String(exhaustive)}` };
    }
  }
}

export function formatRuleValue(value: unknown): string {
  if (value === undefined) return "";
  if (typeof value === "string") return value;
  return JSON.stringify(value, null, 2);
}

export function ruleValueTypeLabel(type: RuleValueType): string {
  const labels: Record<RuleValueType, string> = {
    boolean: "Booléen",
    integer: "Entier",
    decimal: "Décimal exact",
    enum: "Choix",
    currency: "Devise",
    date: "Date",
    structured: "Structure JSON",
  };
  return labels[type];
}

export function ruleSetCode(key: string, discriminator = 0): string {
  const readable = Array.from(key).map((character) => {
    if (/^[A-Za-z0-9]$/.test(character)) return character.toUpperCase();
    if ((character.codePointAt(0) ?? 0) <= 0x7f) return "_";
    const normalized = character.normalize("NFKD").replace(/\p{M}/gu, "");
    if (/^[A-Za-z0-9]$/.test(normalized)) return normalized.toUpperCase();
    return `X${(character.codePointAt(0) ?? 0).toString(16).toUpperCase()}`;
  }).join("").replace(/_+/g, "_").replace(/^_+|_+$/g, "").slice(0, 36);
  let hash = 2166136261;
  const material = `${key}\u0000${discriminator}`;
  // Hacher chaque unité de la clé complète, y compris les caractères non ASCII.
  for (let index = 0; index < material.length; index += 1) {
    hash ^= material.charCodeAt(index);
    hash = Math.imul(hash, 16777619);
  }
  const suffix = (hash >>> 0).toString(36).toUpperCase();
  const discriminatorSuffix = discriminator > 0 ? `_${discriminator.toString(36).toUpperCase()}` : "";
  return `CFG_${readable}_${suffix}${discriminatorSuffix}`.slice(0, 64);
}
