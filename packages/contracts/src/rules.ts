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
  "section",
  "option",
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

export const RULE_VERSION_STATUSES = [
  "DRAFT",
  "IN_REVIEW",
  "VALIDATED",
  "APPROVED",
  "PUBLISHED",
  "SCHEDULED",
  "ARCHIVED",
] as const;
export const RULE_SIMULATION_STATUSES = ["PASSED", "FAILED"] as const;
export const RULE_DOCUMENT_TYPES = ["bulletin", "proclamation", "invoice", "payslip", "other"] as const;
export const ruleVersionStatusSchema = z.enum(RULE_VERSION_STATUSES);
export const ruleSimulationStatusSchema = z.enum(RULE_SIMULATION_STATUSES);
export const ruleDocumentTypeSchema = z.enum(RULE_DOCUMENT_TYPES);

export const ruleScopeOrderSchema = z
  .array(ruleScopeSchema)
  .min(1)
  .max(RULE_SCOPES.length)
  .superRefine((order, context) => {
    if (order[0] !== "organization") {
      context.addIssue({ code: "custom", message: "La portée organization doit ouvrir l’ordre d’héritage." });
    }
    if (new Set(order).size !== order.length) {
      context.addIssue({ code: "custom", message: "Chaque portée ne peut apparaître qu’une fois." });
    }
  });

export const ruleSetCreateInputSchema = z
  .object({
    organizationId: z.uuid(),
    domain: ruleDomainSchema,
    code: z.string().trim().regex(/^[A-Za-z][A-Za-z0-9_-]{0,63}$/),
    name: z.string().trim().min(1).max(160),
    purpose: z.string().trim().min(1).max(500),
    scopeOrder: ruleScopeOrderSchema,
    definitionKeys: z.array(z.string().min(1).max(160)).min(1).max(100),
  })
  .strict()
  .superRefine((input, context) => {
    if (new Set(input.definitionKeys).size !== input.definitionKeys.length) {
      context.addIssue({ code: "custom", path: ["definitionKeys"], message: "Les définitions doivent être uniques." });
    }
  });
export const ruleSetListQuerySchema = z
  .object({
    organizationId: z.uuid(),
    domain: ruleDomainSchema,
    includeArchived: z.enum(["true", "false"]).optional(),
  })
  .strict();
export const ruleOrganizationQuerySchema = z
  .object({ organizationId: z.uuid() })
  .strict();
export const ruleRevisionsQuerySchema = z
  .object({ organizationId: z.uuid(), limit: z.coerce.number().int().min(1).max(100).default(50) })
  .strict();

export const ruleValueInputSchema = z
  .object({
    key: z.string().min(1).max(160),
    value: z.unknown().optional(),
    notApplicable: z.boolean().default(false),
    reason: z.string().trim().min(1).max(500).optional(),
  })
  .strict()
  .superRefine((input, context) => {
    if (input.notApplicable && !input.reason) {
      context.addIssue({ code: "custom", path: ["reason"], message: "Le non-applicable exige un motif." });
    }
    if (input.notApplicable && input.value !== undefined) {
      context.addIssue({ code: "custom", path: ["value"], message: "Ne fournissez pas de valeur avec notApplicable." });
    }
    if (!input.notApplicable && input.value === undefined) {
      context.addIssue({ code: "custom", path: ["value"], message: "Une valeur ou un non-applicable motivé est requis." });
    }
  });
export const ruleBindingInputSchema = z
  .object({ scopeType: ruleScopeSchema, scopeId: z.uuid() })
  .strict();
export const ruleVersionCreateInputSchema = z
  .object({
    organizationId: z.uuid(),
    effectiveFrom: z.iso.date(),
    effectiveTo: z.iso.date().optional(),
    timezone: z.string().trim().min(1).max(64),
    source: z.string().trim().min(1).max(500),
    reason: z.string().trim().min(1).max(500),
    values: z.array(ruleValueInputSchema).max(200).default([]),
    bindings: z.array(ruleBindingInputSchema).min(1).max(20),
  })
  .strict();
export const ruleVersionUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    effectiveFrom: z.iso.date().optional(),
    effectiveTo: z.iso.date().nullable().optional(),
    timezone: z.string().trim().min(1).max(64).optional(),
    source: z.string().trim().min(1).max(500).optional(),
    reason: z.string().trim().min(1).max(500).optional(),
    values: z.array(ruleValueInputSchema).max(200).optional(),
    bindings: z.array(ruleBindingInputSchema).min(1).max(20).optional(),
  })
  .strict()
  .superRefine((input, context) => {
    if (Object.keys(input).every((key) => key === "organizationId")) {
      context.addIssue({ code: "custom", message: "Au moins une modification est requise." });
    }
  });
export const ruleVersionActionInputSchema = z
  .object({ organizationId: z.uuid(), reason: z.string().trim().min(1).max(500) })
  .strict();
export const ruleVersionDuplicateInputSchema = z
  .object({
    organizationId: z.uuid(),
    targetSetId: z.uuid().optional(),
    effectiveFrom: z.iso.date(),
    effectiveTo: z.iso.date().optional(),
    timezone: z.string().trim().min(1).max(64),
    source: z.string().trim().min(1).max(500),
    reason: z.string().trim().min(1).max(500),
  })
  .strict();

export const ruleResolutionScopeSchema = z
  .object({
    siteId: z.uuid().optional(),
    schoolYearId: z.uuid().optional(),
    levelId: z.uuid().optional(),
    sectionId: z.uuid().optional(),
    optionId: z.uuid().optional(),
    programId: z.uuid().optional(),
    classId: z.uuid().optional(),
    subjectId: z.uuid().optional(),
  })
  .strict();
export const ruleResolveInputSchema = z
  .object({
    organizationId: z.uuid(),
    ruleSetId: z.uuid(),
    asOf: z.iso.date(),
    scope: ruleResolutionScopeSchema,
  })
  .strict();
export const ruleSimulationCaseSchema = z
  .object({
    name: z.string().trim().min(1).max(120),
    kind: z.enum(["normal", "boundary", "incomplete"]).optional(),
    asOf: z.iso.date(),
    scope: ruleResolutionScopeSchema,
    input: z.unknown().optional(),
  })
  .strict();
export const ruleSimulationInputSchema = z
  .object({
    organizationId: z.uuid(),
    comparisonVersionId: z.uuid().optional(),
    cases: z.array(ruleSimulationCaseSchema).min(1).max(20),
  })
  .strict()
  .superRefine((input, context) => {
    if (new Set(input.cases.map((entry) => entry.name)).size !== input.cases.length) {
      context.addIssue({ code: "custom", path: ["cases"], message: "Les noms de cas doivent être uniques." });
    }
  });
export const ruleSnapshotInputSchema = z
  .object({
    organizationId: z.uuid(),
    documentType: ruleDocumentTypeSchema,
    documentId: z.uuid(),
    ruleSetVersionId: z.uuid(),
    asOf: z.iso.date(),
    scope: ruleResolutionScopeSchema,
  })
  .strict();

export const ruleSetResponseSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    domain: ruleDomainSchema,
    code: z.string(),
    name: z.string(),
    purpose: z.string(),
    scopeOrder: z.array(ruleScopeSchema),
    definitionKeys: z.array(z.string()),
    createdById: z.uuid(),
    createdAt: z.iso.datetime(),
    updatedAt: z.iso.datetime(),
    archivedAt: z.iso.datetime().nullable(),
  })
  .strict();
export const ruleValueResponseSchema = z
  .object({
    key: z.string(),
    value: z.unknown().nullable(),
    notApplicable: z.boolean(),
    reason: z.string().nullable(),
  })
  .strict();
export const ruleBindingResponseSchema = ruleBindingInputSchema;
export const ruleVersionResponseSchema = z
  .object({
    id: z.uuid(),
    ruleSetId: z.uuid(),
    version: z.number().int(),
    status: ruleVersionStatusSchema,
    effectiveFrom: z.iso.date(),
    effectiveTo: z.iso.date().nullable(),
    effectiveTimezone: z.string(),
    source: z.string(),
    reason: z.string(),
    contentHash: z.string().nullable(),
    createdById: z.uuid(),
    reviewedById: z.uuid().nullable(),
    approvedById: z.uuid().nullable(),
    publishedById: z.uuid().nullable(),
    publicationSimulationId: z.uuid().nullable(),
    submittedAt: z.iso.datetime().nullable(),
    reviewedAt: z.iso.datetime().nullable(),
    approvedAt: z.iso.datetime().nullable(),
    publishedAt: z.iso.datetime().nullable(),
    createdAt: z.iso.datetime(),
    updatedAt: z.iso.datetime(),
    archivedAt: z.iso.datetime().nullable(),
    values: z.array(ruleValueResponseSchema),
    bindings: z.array(ruleBindingResponseSchema),
  })
  .strict();
export const ruleSetsResponseSchema = z
  .object({ sets: z.array(ruleSetResponseSchema) })
  .strict();
export const ruleSetDetailResponseSchema = z
  .object({ set: ruleSetResponseSchema, versions: z.array(ruleVersionResponseSchema) })
  .strict();

export const ruleResolutionSourceSchema = z
  .object({
    versionId: z.uuid(),
    version: z.number().int(),
    scopeType: ruleScopeSchema,
    scopeId: z.uuid(),
    inherited: z.boolean(),
  })
  .strict();
export const ruleResolvedValueSchema = z
  .object({
    key: z.string(),
    value: z.unknown().nullable(),
    notApplicable: z.boolean(),
    reason: z.string().nullable(),
    source: ruleResolutionSourceSchema,
  })
  .strict();
export const ruleResolutionIssueSchema = z
  .object({ key: z.string(), code: z.string(), message: z.string() })
  .strict();
export const ruleResolutionResponseSchema = z
  .object({
    ruleSetId: z.uuid(),
    asOf: z.iso.date(),
    status: z.enum(["RESOLVED", "INCOMPLETE", "CONFLICT"]),
    values: z.array(ruleResolvedValueSchema),
    missing: z.array(ruleResolutionIssueSchema),
    unconfigured: z.array(z.string()),
    conflicts: z.array(ruleResolutionIssueSchema),
    warnings: z.array(z.string()),
  })
  .strict();
export const ruleSimulationResponseSchema = z
  .object({
    id: z.uuid(),
    ruleSetVersionId: z.uuid(),
    status: ruleSimulationStatusSchema,
    inputHash: z.string(),
    contentHash: z.string(),
    report: z.unknown(),
    createdById: z.uuid(),
    createdAt: z.iso.datetime(),
  })
  .strict();
export const ruleSnapshotResponseSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    documentType: ruleDocumentTypeSchema,
    documentId: z.uuid(),
    ruleSetVersionId: z.uuid(),
    resolved: z.unknown(),
    contentHash: z.string(),
    createdById: z.uuid(),
    createdAt: z.iso.datetime(),
  })
  .strict();
export const ruleDiffResponseSchema = z
  .object({
    fromVersionId: z.uuid(),
    toVersionId: z.uuid(),
    changes: z.array(
      z.object({
        key: z.string(),
        change: z.enum(["added", "removed", "changed", "unchanged"]),
        before: z.unknown().nullable(),
        after: z.unknown().nullable(),
      }).strict(),
    ),
    bindingsChanged: z.boolean(),
    effectChanged: z.boolean(),
  })
  .strict();
export const ruleRevisionResponseSchema = z
  .object({
    id: z.uuid(),
    action: z.string(),
    reason: z.string().nullable(),
    actorUserId: z.uuid(),
    requestId: z.uuid(),
    before: z.unknown().nullable(),
    after: z.unknown().nullable(),
    createdAt: z.iso.datetime(),
  })
  .strict();
export const ruleRevisionsResponseSchema = z
  .object({ revisions: z.array(ruleRevisionResponseSchema) })
  .strict();

export type RuleDefinitionResponse = z.infer<typeof ruleDefinitionSchema>;
export type RuleCatalogQuery = z.infer<typeof ruleCatalogQuerySchema>;
export type RuleValidationInput = z.infer<typeof ruleValidationInputSchema>;
export type RuleValidationIssue = z.infer<typeof ruleValidationIssueSchema>;
export type RuleValidationResponse = z.infer<typeof ruleValidationResponseSchema>;
export type RuleSetCreateInput = z.infer<typeof ruleSetCreateInputSchema>;
export type RuleSetListQuery = z.infer<typeof ruleSetListQuerySchema>;
export type RuleValueInput = z.infer<typeof ruleValueInputSchema>;
export type RuleBindingInput = z.infer<typeof ruleBindingInputSchema>;
export type RuleVersionCreateInput = z.infer<typeof ruleVersionCreateInputSchema>;
export type RuleVersionUpdateInput = z.infer<typeof ruleVersionUpdateInputSchema>;
export type RuleVersionActionInput = z.infer<typeof ruleVersionActionInputSchema>;
export type RuleVersionDuplicateInput = z.infer<typeof ruleVersionDuplicateInputSchema>;
export type RuleResolutionScope = z.infer<typeof ruleResolutionScopeSchema>;
export type RuleResolveInput = z.infer<typeof ruleResolveInputSchema>;
export type RuleSimulationInput = z.infer<typeof ruleSimulationInputSchema>;
export type RuleSnapshotInput = z.infer<typeof ruleSnapshotInputSchema>;
