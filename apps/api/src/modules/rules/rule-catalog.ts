import {
  RULE_DEFINITIONS,
  type RuleDefinition,
  type RuleValidationIssue,
} from "@lavision/contracts";

export interface RuleValueValidationResult {
  valid: boolean;
  normalizedValue?: unknown;
  issues: RuleValidationIssue[];
}

const decimalPattern = /^-?(?:0|[1-9]\d*)(?:\.\d+)?$/;
const currencyPattern = /^[A-Z]{3}$/;

export function findRuleDefinition(key: string): RuleDefinition | undefined {
  return RULE_DEFINITIONS.find((definition) => definition.key === key);
}

function invalid(code: string, message: string): RuleValueValidationResult {
  return { valid: false, issues: [{ path: "value", code, message }] };
}

function checkBounds(
  value: number,
  definition: RuleDefinition,
): RuleValueValidationResult | undefined {
  if (definition.constraints?.min !== undefined && value < definition.constraints.min) {
    return invalid("OUT_OF_RANGE", `La valeur doit être supérieure ou égale à ${definition.constraints.min}.`);
  }
  if (definition.constraints?.max !== undefined && value > definition.constraints.max) {
    return invalid("OUT_OF_RANGE", `La valeur doit être inférieure ou égale à ${definition.constraints.max}.`);
  }
  return undefined;
}

/** Validation fermée : aucune expression, fonction ou code fourni par un client n’est évalué. */
export function validateRuleValue(
  definition: RuleDefinition,
  value: unknown,
): RuleValueValidationResult {
  if (value === undefined) {
    return definition.required
      ? invalid("REQUIRED", "Une valeur est requise pour cette définition.")
      : { valid: true, issues: [] };
  }
  if (value === null || value === "") {
    return invalid("EMPTY_VALUE", "Une valeur vide ou nulle n’est pas une configuration applicable.");
  }

  switch (definition.type) {
    case "boolean":
      return typeof value === "boolean"
        ? { valid: true, normalizedValue: value, issues: [] }
        : invalid("INVALID_TYPE", "La valeur doit être un booléen.");
    case "integer": {
      if (typeof value !== "number" || !Number.isSafeInteger(value)) {
        return invalid("INVALID_TYPE", "La valeur doit être un entier sûr.");
      }
      return checkBounds(value, definition) ?? {
        valid: true,
        normalizedValue: value,
        issues: [],
      };
    }
    case "decimal": {
      const raw = typeof value === "number" ? String(value) : typeof value === "string" ? value.trim() : "";
      if (!raw || !decimalPattern.test(raw)) {
        return invalid("INVALID_DECIMAL", "Utilisez un nombre décimal, sans formule ni exposant.");
      }
      const numeric = Number(raw);
      if (!Number.isFinite(numeric)) {
        return invalid("INVALID_DECIMAL", "Le nombre décimal est hors limites.");
      }
      const bounds = checkBounds(numeric, definition);
      // Les décimales sont conservées comme texte pour ne pas introduire d’arrondi binaire.
      return bounds ?? { valid: true, normalizedValue: raw, issues: [] };
    }
    case "enum": {
      const allowed = definition.constraints?.values ?? [];
      return typeof value === "string" && allowed.includes(value)
        ? { valid: true, normalizedValue: value, issues: [] }
        : invalid("INVALID_ENUM", `Valeurs autorisées : ${allowed.join(", ")}.`);
    }
    case "currency":
      return typeof value === "string" && currencyPattern.test(value)
        ? { valid: true, normalizedValue: value, issues: [] }
        : invalid("INVALID_CURRENCY", "Utilisez un code devise à trois lettres majuscules.");
    case "date": {
      const validDate = typeof value === "string" && /^\d{4}-\d{2}-\d{2}$/.test(value)
        ? new Date(`${value}T00:00:00.000Z`)
        : null;
      return validDate && !Number.isNaN(validDate.getTime()) && validDate.toISOString().slice(0, 10) === value
        ? { valid: true, normalizedValue: value, issues: [] }
        : invalid("INVALID_DATE", "Utilisez une date ISO au format YYYY-MM-DD.");
    }
  }
  return invalid("UNSUPPORTED_TYPE", "Ce type de définition n’est pas encore validable.");
}
