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

const forbiddenStructuredKeys = new Set(["__proto__", "prototype", "constructor"]);

/**
 * Les paramètres encore inconnus peuvent être conservés comme données structurées,
 * jamais comme formule exécutable. Le schéma métier détaillé est ajouté avant le
 * calcul qui les consomme ; cette validation borne déjà la taille et la profondeur.
 */
function validateStructuredValue(
  definition: RuleDefinition,
  value: unknown,
): RuleValueValidationResult {
  if (definition.constraints?.structure === "array" && !Array.isArray(value)) {
    return invalid("INVALID_STRUCTURE", "Une liste structurée est requise.");
  }
  if (definition.constraints?.structure === "object" && (
    typeof value !== "object" || value === null || Array.isArray(value)
  )) {
    return invalid("INVALID_STRUCTURE", "Un objet structuré est requis.");
  }
  if (typeof value !== "object" || value === null) {
    return invalid("INVALID_STRUCTURE", "Une liste ou un objet structuré est requis.");
  }

  let nodes = 0;
  const visit = (entry: unknown, depth: number): string | null => {
    nodes += 1;
    if (nodes > 2000) return "La structure contient trop d’éléments.";
    if (depth > 10) return "La structure est trop profonde.";
    if (entry === null || typeof entry === "boolean") return null;
    if (typeof entry === "number") return Number.isFinite(entry) ? null : "Les nombres doivent être finis.";
    if (typeof entry === "string") return entry.length <= 1000 ? null : "Un texte de la structure est trop long.";
    if (Array.isArray(entry)) {
      if (entry.length > 200) return "Une liste contient trop d’éléments.";
      for (const child of entry) {
        const error = visit(child, depth + 1);
        if (error) return error;
      }
      return null;
    }
    if (typeof entry !== "object") return "Seules des données JSON sont acceptées.";
    const prototype = Object.getPrototypeOf(entry);
    if (prototype !== Object.prototype && prototype !== null) return "Les objets natifs ou instances de classe ne sont pas acceptés.";
    const entries = Object.entries(entry);
    if (entries.length > 100) return "Un objet contient trop de champs.";
    for (const [key, child] of entries) {
      if (forbiddenStructuredKeys.has(key)) return "Une clé de structure dangereuse a été refusée.";
      if (key.length > 100) return "Une clé de structure est trop longue.";
      const error = visit(child, depth + 1);
      if (error) return error;
    }
    return null;
  };

  const error = visit(value, 0);
  if (error) return invalid("INVALID_STRUCTURE", error);
  const serialized = JSON.stringify(value);
  if (!serialized || serialized.length > 65536) {
    return invalid("STRUCTURE_TOO_LARGE", "La structure dépasse la taille maximale de 64 Ko.");
  }
  return { valid: true, normalizedValue: value, issues: [] };
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
    case "structured":
      return validateStructuredValue(definition, value);
  }
  return invalid("UNSUPPORTED_TYPE", "Ce type de définition n’est pas encore validable.");
}
