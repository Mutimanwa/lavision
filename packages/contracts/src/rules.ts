import { z } from "zod";

export const RULE_DOMAINS = [
  "academic",
  "attendance",
  "finance",
  "hr",
  "payroll",
  "documents",
  "calendar",
] as const;
export const RULE_VALUE_TYPES = [
  "boolean",
  "integer",
  "decimal",
  "enum",
  "currency",
  "date",
] as const;
export const RULE_SCOPES = [
  "organization",
  "site",
  "school_year",
  "level",
  "program",
  "class",
  "subject",
] as const;
export const RULE_CATALOG_VERSION = 1;

export const ruleDomainSchema = z.enum(RULE_DOMAINS);
export const ruleValueTypeSchema = z.enum(RULE_VALUE_TYPES);
export const ruleScopeSchema = z.enum(RULE_SCOPES);
export type RuleDomain = z.infer<typeof ruleDomainSchema>;
export type RuleValueType = z.infer<typeof ruleValueTypeSchema>;
export type RuleScope = z.infer<typeof ruleScopeSchema>;

export interface RuleDefinition {
  key: string;
  domain: RuleDomain;
  label: string;
  description: string;
  type: RuleValueType;
  required: boolean;
  scopes: readonly RuleScope[];
  constraints?: {
    min?: number;
    max?: number;
    values?: readonly string[];
  };
}

const scope = {
  organization: ["organization"],
  program: ["organization", "school_year", "program"],
  subject: ["organization", "school_year", "program", "subject"],
  level: ["organization", "school_year", "level"],
  site: ["organization", "site"],
} as const satisfies Record<string, readonly RuleScope[]>;

/**
 * Catalogue produit initial. Les définitions ne portent aucune valeur par défaut :
 * une valeur devient applicable uniquement après configuration et publication.
 */
export const RULE_DEFINITIONS = [
  {
    key: "academic.grading.scale_min",
    domain: "academic",
    label: "Minimum du barème",
    description: "Borne inférieure du barème d’un cours ou programme.",
    type: "decimal",
    required: true,
    scopes: scope.subject,
    constraints: { min: 0, max: 1000000 },
  },
  {
    key: "academic.grading.scale_max",
    domain: "academic",
    label: "Maximum du barème",
    description: "Maximum propre au cours et au programme, sans maximum global imposé.",
    type: "decimal",
    required: true,
    scopes: scope.subject,
    constraints: { min: 0.000001, max: 1000000 },
  },
  {
    key: "academic.grading.input_increment",
    domain: "academic",
    label: "Pas de saisie",
    description: "Incrément autorisé lors de la saisie d’une note.",
    type: "decimal",
    required: false,
    scopes: scope.program,
    constraints: { min: 0.000001, max: 1000 },
  },
  {
    key: "academic.subject.coefficient",
    domain: "academic",
    label: "Coefficient de matière",
    description: "Coefficient distinct du maximum, lorsqu’il est applicable.",
    type: "decimal",
    required: false,
    scopes: scope.subject,
    constraints: { min: 0.000001, max: 1000 },
  },
  {
    key: "academic.evaluation.weight",
    domain: "academic",
    label: "Poids d’évaluation",
    description: "Poids d’une composante d’évaluation, sans valeur implicite.",
    type: "decimal",
    required: false,
    scopes: scope.subject,
    constraints: { min: 0.000001, max: 1000 },
  },
  {
    key: "academic.evaluation.term_schema",
    domain: "academic",
    label: "Schéma d’évaluation",
    description: "Choix explicite entre périodes avec ou sans examen.",
    type: "enum",
    required: true,
    scopes: scope.subject,
    constraints: { values: ["periods_with_exam", "periods_without_exam"] },
  },
  {
    key: "academic.average.missing_policy",
    domain: "academic",
    label: "Traitement des notes manquantes",
    description: "Politique explicite pour une note absente ou non saisie.",
    type: "enum",
    required: true,
    scopes: scope.program,
    constraints: { values: ["exclude_missing", "zero", "non_calculable"] },
  },
  {
    key: "academic.average.provisional_display",
    domain: "academic",
    label: "Affichage provisoire",
    description: "Autorise l’affichage d’une moyenne provisoire sur dossier incomplet.",
    type: "boolean",
    required: false,
    scopes: scope.level,
  },
  {
    key: "academic.rounding.calculation_precision",
    domain: "academic",
    label: "Précision de calcul",
    description: "Nombre de décimales conservées à l’étape configurée.",
    type: "integer",
    required: true,
    scopes: scope.organization,
    constraints: { min: 0, max: 6 },
  },
  {
    key: "academic.rounding.display_precision",
    domain: "academic",
    label: "Précision d’affichage",
    description: "Nombre de décimales affichées sur le document.",
    type: "integer",
    required: true,
    scopes: scope.program,
    constraints: { min: 0, max: 6 },
  },
  {
    key: "academic.rounding.mode",
    domain: "academic",
    label: "Mode d’arrondi",
    description: "Stratégie d’arrondi choisie par l’établissement.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["half_up", "half_even", "truncate"] },
  },
  {
    key: "academic.rounding.stage",
    domain: "academic",
    label: "Étape d’arrondi",
    description: "Étape à laquelle l’arrondi est appliqué.",
    type: "enum",
    required: true,
    scopes: scope.program,
    constraints: { values: ["course", "subject", "period", "final"] },
  },
  {
    key: "academic.rank.tie_policy",
    domain: "academic",
    label: "Classement des ex æquo",
    description: "Règle de numérotation des ex æquo, sans adoption implicite.",
    type: "enum",
    required: true,
    scopes: scope.level,
    constraints: { values: ["competition", "dense", "ordinal"] },
  },
  {
    key: "academic.pass.mark",
    domain: "academic",
    label: "Seuil de réussite",
    description: "Seuil exprimé dans le barème applicable, sans conversion implicite.",
    type: "decimal",
    required: true,
    scopes: scope.program,
    constraints: { min: 0, max: 1000000 },
  },
  {
    key: "academic.publication.incomplete_policy",
    domain: "academic",
    label: "Publication d’un dossier incomplet",
    description: "Comportement explicite pour un dossier incomplet.",
    type: "enum",
    required: true,
    scopes: scope.level,
    constraints: { values: ["provisional", "block"] },
  },
  {
    key: "attendance.capture.mode",
    domain: "attendance",
    label: "Mode d’appel",
    description: "Mode de capture des présences.",
    type: "enum",
    required: true,
    scopes: scope.level,
    constraints: { values: ["daily", "session"] },
  },
  {
    key: "attendance.late.threshold_minutes",
    domain: "attendance",
    label: "Seuil de retard",
    description: "Seuil en minutes, sans conversion automatique en absence.",
    type: "integer",
    required: false,
    scopes: scope.level,
    constraints: { min: 0, max: 1440 },
  },
  {
    key: "finance.currency.code",
    domain: "finance",
    label: "Devise",
    description: "Code ISO de la devise configurée pour l’établissement.",
    type: "currency",
    required: true,
    scopes: scope.organization,
  },
  {
    key: "finance.currency.minor_units",
    domain: "finance",
    label: "Décimales monétaires",
    description: "Nombre de décimales de la devise, sans flottant de calcul.",
    type: "integer",
    required: true,
    scopes: scope.organization,
    constraints: { min: 0, max: 6 },
  },
  {
    key: "finance.late_fee.enabled",
    domain: "finance",
    label: "Pénalités de retard",
    description: "Activation explicite des pénalités, sans taux implicite.",
    type: "boolean",
    required: false,
    scopes: scope.organization,
  },
  {
    key: "finance.cash.opening_required",
    domain: "finance",
    label: "Ouverture de caisse requise",
    description: "Exige une session de caisse avant encaissement.",
    type: "boolean",
    required: false,
    scopes: scope.site,
  },
  {
    key: "finance.document.numbering_reset",
    domain: "finance",
    label: "Réinitialisation de numérotation",
    description: "Rythme de réinitialisation, sans réutilisation de numéro.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["never", "yearly"] },
  },
  {
    key: "hr.leave.negative_balance_policy",
    domain: "hr",
    label: "Solde de congé négatif",
    description: "Politique de dépassement et d’approbation des congés.",
    type: "enum",
    required: false,
    scopes: scope.organization,
    constraints: { values: ["forbid", "allow_with_approval"] },
  },
  {
    key: "payroll.periodicity",
    domain: "payroll",
    label: "Périodicité de paie",
    description: "Périodicité à valider avec le responsable local.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["monthly", "weekly", "biweekly"] },
  },
  {
    key: "payroll.rounding.policy",
    domain: "payroll",
    label: "Arrondi de paie",
    description: "Mode d’arrondi à documenter avec les règles locales.",
    type: "enum",
    required: false,
    scopes: scope.organization,
    constraints: { values: ["half_up", "half_even", "truncate"] },
  },
  {
    key: "document.language",
    domain: "documents",
    label: "Langue du document",
    description: "Langue choisie pour le modèle de document.",
    type: "enum",
    required: true,
    scopes: scope.program,
    constraints: { values: ["fr", "en", "rn"] },
  },
  {
    key: "notification.absence.enabled",
    domain: "documents",
    label: "Notification d’absence",
    description: "Activation explicite de la notification, hors canal et destinataires.",
    type: "boolean",
    required: false,
    scopes: scope.organization,
  },
] as const satisfies readonly RuleDefinition[];

export const ruleDefinitionSchema = z
  .object({
    key: z.string(),
    domain: ruleDomainSchema,
    label: z.string(),
    description: z.string(),
    type: ruleValueTypeSchema,
    required: z.boolean(),
    scopes: z.array(ruleScopeSchema),
    constraints: z
      .object({
        min: z.number().optional(),
        max: z.number().optional(),
        values: z.array(z.string()).optional(),
      })
      .strict()
      .nullable(),
  })
  .strict();
export const ruleCatalogQuerySchema = z
  .object({
    organizationId: z.uuid(),
    domain: ruleDomainSchema.optional(),
    search: z.string().trim().max(100).optional(),
  })
  .strict();
export const ruleCatalogResponseSchema = z
  .object({
    catalogVersion: z.number().int(),
    definitions: z.array(ruleDefinitionSchema),
  })
  .strict();
export const ruleValidationInputSchema = z
  .object({ organizationId: z.uuid(), key: z.string().min(1).max(160), value: z.unknown() })
  .strict();
export const ruleValidationIssueSchema = z
  .object({ path: z.string(), code: z.string(), message: z.string() })
  .strict();
export const ruleValidationResponseSchema = z
  .object({
    valid: z.boolean(),
    key: z.string(),
    normalizedValue: z.unknown().optional(),
    issues: z.array(ruleValidationIssueSchema),
  })
  .strict();

export type RuleDefinitionResponse = z.infer<typeof ruleDefinitionSchema>;
export type RuleCatalogQuery = z.infer<typeof ruleCatalogQuerySchema>;
export type RuleValidationInput = z.infer<typeof ruleValidationInputSchema>;
export type RuleValidationIssue = z.infer<typeof ruleValidationIssueSchema>;
export type RuleValidationResponse = z.infer<typeof ruleValidationResponseSchema>;
