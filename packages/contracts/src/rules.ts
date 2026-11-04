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
  "structured",
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
export const RULE_CATALOG_VERSION = 2;

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
    /** Structure extérieure exigée pour une valeur JSON bornée. */
    structure?: "array" | "object";
  };
}

const scope = {
  organization: ["organization"],
  site: ["organization", "site"],
  schoolYear: ["organization", "school_year"],
  level: ["organization", "school_year", "level"],
  section: ["organization", "school_year", "section"],
  option: ["organization", "school_year", "option"],
  program: ["organization", "school_year", "program"],
  class: ["organization", "school_year", "class"],
  subject: ["organization", "school_year", "program", "subject"],
  levelProgram: ["organization", "school_year", "level", "program"],
  sectionProgram: ["organization", "school_year", "section", "program"],
  siteYear: ["organization", "site", "school_year"],
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
    key: "calendar.period.grading_window",
    domain: "calendar",
    label: "Fenêtre de saisie des notes",
    description: "Bornes de saisie par période, distinctes des dates pédagogiques ; structure détaillée à valider.",
    type: "structured",
    required: false,
    scopes: scope.schoolYear,
    constraints: { structure: "object" },
  },
  {
    key: "calendar.period.publication_window",
    domain: "calendar",
    label: "Fenêtre de publication",
    description: "Bornes de publication par période, sans date implicite ; structure détaillée à valider.",
    type: "structured",
    required: false,
    scopes: scope.schoolYear,
    constraints: { structure: "object" },
  },
  {
    key: "academic.class.capacity_enforcement",
    domain: "academic",
    label: "Contrôle de capacité des classes",
    description: "Choix explicite entre blocage et avertissement lorsque la capacité est dépassée.",
    type: "enum",
    required: false,
    scopes: scope.level,
    constraints: { values: ["block", "warn"] },
  },
  {
    key: "academic.student.unique_matricule_scope",
    domain: "academic",
    label: "Portée d’unicité du matricule",
    description: "Portée dans laquelle le matricule doit être unique, sans identifiant national supposé.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["organization", "site", "school_year"] },
  },
  {
    key: "academic.enrollment.single_primary_class",
    domain: "academic",
    label: "Classe principale unique",
    description: "Exige ou non une seule affectation principale à une date donnée.",
    type: "boolean",
    required: true,
    scopes: scope.organization,
  },
  {
    key: "academic.grading.conversion_mode",
    domain: "academic",
    label: "Conversion entre barèmes",
    description: "Stratégie de conversion choisie explicitement lorsque plusieurs barèmes coexistent.",
    type: "enum",
    required: false,
    scopes: scope.program,
    constraints: { values: ["none", "linear", "scale_to_scale"] },
  },
  {
    key: "academic.average.subject_aggregation",
    domain: "academic",
    label: "Agrégation des évaluations d’une matière",
    description: "Stratégie de synthèse des composantes d’une matière, sans moyenne pondérée implicite.",
    type: "enum",
    required: true,
    scopes: scope.program,
    constraints: { values: ["weighted_mean", "sum_points"] },
  },
  {
    key: "academic.average.report_aggregation",
    domain: "academic",
    label: "Agrégation du bulletin",
    description: "Stratégie de synthèse des matières et périodes, y compris les totaux de points et maxima.",
    type: "enum",
    required: true,
    scopes: scope.levelProgram,
    constraints: { values: ["weighted_mean", "sum_points", "sum_of_periods"] },
  },
  {
    key: "academic.report.grouping_schema",
    domain: "academic",
    label: "Regroupements du bulletin",
    description: "Domaines, sous-domaines, ordre et sous-totaux ; liste structurée sans mise en page imposée.",
    type: "structured",
    required: true,
    scopes: scope.program,
    constraints: { structure: "array" },
  },
  {
    key: "academic.average.exemption_policy",
    domain: "academic",
    label: "Traitement des dispenses",
    description: "Effet d’une dispense sur le dénominateur et la calculabilité, à distinguer d’une note absente.",
    type: "enum",
    required: false,
    scopes: scope.program,
    constraints: { values: ["exclude_from_denominator", "keep_denominator", "non_calculable"] },
  },
  {
    key: "academic.rank.incomplete_policy",
    domain: "academic",
    label: "Classement des dossiers incomplets",
    description: "Exclusion ou rang provisoire explicitement séparé du classement officiel.",
    type: "enum",
    required: true,
    scopes: scope.level,
    constraints: { values: ["exclude", "provisional_after_complete"] },
  },
  {
    key: "academic.appreciation.scale",
    domain: "academic",
    label: "Grille d’appréciations",
    description: "Seuils et libellés ordonnés par programme, sans grille Excellent/Passable imposée.",
    type: "structured",
    required: true,
    scopes: scope.levelProgram,
    constraints: { structure: "array" },
  },
  {
    key: "academic.pass.eliminatory_rules",
    domain: "academic",
    label: "Notes éliminatoires",
    description: "Seuils et effets éliminatoires éventuels ; absence et non-applicabilité restent distinctes.",
    type: "structured",
    required: false,
    scopes: scope.levelProgram,
    constraints: { structure: "array" },
  },
  {
    key: "academic.pass.compensation_policy",
    domain: "academic",
    label: "Compensation entre résultats",
    description: "Stratégie et paramètres de compensation éventuelle, sans compensation implicite.",
    type: "structured",
    required: false,
    scopes: scope.levelProgram,
    constraints: { structure: "object" },
  },
  {
    key: "academic.pass.resit_policy",
    domain: "academic",
    label: "Repêchage et rattrapage",
    description: "Éligibilité, épreuves et effet sur les résultats ; aucune règle de repêchage n’est présumée.",
    type: "structured",
    required: false,
    scopes: scope.levelProgram,
    constraints: { structure: "array" },
  },
  {
    key: "academic.year.aggregation",
    domain: "academic",
    label: "Agrégation annuelle",
    description: "Périodes, examens et poids annuels versionnés, sans poids égal ou 50/50 implicite.",
    type: "structured",
    required: true,
    scopes: scope.levelProgram,
    constraints: { structure: "object" },
  },
  {
    key: "academic.year.decision_policy",
    domain: "academic",
    label: "Décision annuelle",
    description: "Conditions et libellés de passage, redoublement ou délibération par niveau, section et programme.",
    type: "structured",
    required: true,
    scopes: scope.sectionProgram,
    constraints: { structure: "object" },
  },
  {
    key: "academic.national_exam.result_block",
    domain: "academic",
    label: "Bloc d’examen national",
    description: "Paramètres ENAFEP, TENASOSP ou EXETAT applicables au programme, sans combinaison générique.",
    type: "structured",
    required: false,
    scopes: scope.program,
    constraints: { structure: "object" },
  },
  {
    key: "academic.year.midyear_policy",
    domain: "academic",
    label: "Éligibilité en cours d’année",
    description: "Arrivées, départs et périodes réellement présentes ; aucune note antérieure n’est inventée.",
    type: "structured",
    required: true,
    scopes: scope.levelProgram,
    constraints: { structure: "object" },
  },
  {
    key: "academic.decision.attendance_policy",
    domain: "academic",
    label: "Effet de l’assiduité sur la décision",
    description: "Seuils et effets éventuels de l’assiduité, uniquement après configuration et publication.",
    type: "structured",
    required: false,
    scopes: scope.levelProgram,
    constraints: { structure: "object" },
  },
  {
    key: "academic.decision.conduct_policy",
    domain: "academic",
    label: "Effet de la conduite sur la décision",
    description: "Seuils et effets éventuels de la conduite, sans modification automatique du résultat.",
    type: "structured",
    required: false,
    scopes: scope.levelProgram,
    constraints: { structure: "object" },
  },
  {
    key: "academic.council.override_policy",
    domain: "academic",
    label: "Dérogation du conseil",
    description: "Encadrement explicite d’une dérogation administrative, séparée du résultat calculé.",
    type: "enum",
    required: false,
    scopes: scope.organization,
    constraints: { values: ["forbid", "allow_with_reason", "allow_with_approval"] },
  },
  {
    key: "academic.publication.correction_policy",
    domain: "academic",
    label: "Rectification après publication",
    description: "Circuit, mentions et diffusion d’une nouvelle version, sans réécriture du document historique.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "academic.publication.family_visibility",
    domain: "academic",
    label: "Visibilité des résultats par les familles",
    description: "Choix explicite de diffusion des documents publiés ou provisoires.",
    type: "enum",
    required: true,
    scopes: scope.level,
    constraints: { values: ["published_only", "published_and_provisional"] },
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
    key: "attendance.status.catalog",
    domain: "attendance",
    label: "Statuts d’assiduité",
    description: "Statuts autorisés et leurs libellés, sans réduire les absences aux quatre valeurs du prototype.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "attendance.status.counts_absent",
    domain: "attendance",
    label: "Statuts comptés comme absence",
    description: "Incidence de chaque statut sur les totaux d’absence, configurée séparément du libellé.",
    type: "structured",
    required: true,
    scopes: scope.level,
    constraints: { structure: "array" },
  },
  {
    key: "attendance.late.to_absence_policy",
    domain: "attendance",
    label: "Conversion d’un retard en absence",
    description: "Seuil et effet éventuel d’un retard, sans conversion automatique non validée.",
    type: "structured",
    required: false,
    scopes: scope.level,
    constraints: { structure: "object" },
  },
  {
    key: "attendance.justification.required",
    domain: "attendance",
    label: "Justificatifs requis",
    description: "Statuts et situations exigeant un justificatif, avec leur politique de validation.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "attendance.justification.deadline",
    domain: "attendance",
    label: "Délai de justification",
    description: "Durée et unité du délai de justification, sans nombre de jours imposé.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "attendance.alert.thresholds",
    domain: "attendance",
    label: "Seuils d’alerte d’assiduité",
    description: "Seuils, destinataires et périodes d’alerte ; aucun pourcentage n’est présupposé.",
    type: "structured",
    required: false,
    scopes: scope.level,
    constraints: { structure: "array" },
  },
  {
    key: "attendance.correction.window",
    domain: "attendance",
    label: "Fenêtre de correction des appels",
    description: "Durée, unité et circuit d’approbation des corrections après clôture.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "conduct.incident.visibility",
    domain: "attendance",
    label: "Visibilité des incidents de conduite",
    description: "Rôles et portées autorisés pour les incidents de vie scolaire, avec refus par défaut.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "object" },
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
    key: "finance.invoice.debtor_policy",
    domain: "finance",
    label: "Débiteur de la facture",
    description: "Élève, famille ou payeur responsable selon le choix validé de l’établissement.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["student", "guardian", "family", "other_payor"] },
  },
  {
    key: "finance.tariff.applicability",
    domain: "finance",
    label: "Applicabilité des tarifs",
    description: "Priorité des tarifs par année, niveau, programme ou service, sans ordre implicite.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "finance.invoice.proration_policy",
    domain: "finance",
    label: "Prorata de facturation",
    description: "Stratégie et paramètres de prorata en cours de période, à distinguer d’une remise.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "finance.discount.stacking_policy",
    domain: "finance",
    label: "Cumul des remises",
    description: "Cumul, priorité et plafond des remises, sans cumul implicite.",
    type: "enum",
    required: false,
    scopes: scope.organization,
    constraints: { values: ["forbid", "allow_with_cap"] },
  },
  {
    key: "finance.discount.calculation_base",
    domain: "finance",
    label: "Base de calcul des remises",
    description: "Base brute ou nette des remises précédentes, choisie explicitement.",
    type: "enum",
    required: false,
    scopes: scope.organization,
    constraints: { values: ["gross", "net_of_previous_discounts"] },
  },
  {
    key: "finance.discount.approval_thresholds",
    domain: "finance",
    label: "Seuils d’approbation des remises",
    description: "Montants, taux et approbateurs par site, sans seuil financier inventé.",
    type: "structured",
    required: false,
    scopes: scope.site,
    constraints: { structure: "array" },
  },
  {
    key: "finance.late_fee.calculation_base",
    domain: "finance",
    label: "Base des pénalités de retard",
    description: "Base de calcul à utiliser uniquement si les pénalités sont activées et validées.",
    type: "enum",
    required: false,
    scopes: scope.organization,
    constraints: { values: ["outstanding_amount", "invoice_total", "installment_amount"] },
  },
  {
    key: "finance.late_fee.rate_or_amount",
    domain: "finance",
    label: "Taux ou montant de pénalité",
    description: "Type, valeur, unité et plafond éventuel de la pénalité, sans taux par défaut.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "finance.late_fee.grace_period",
    domain: "finance",
    label: "Délai de grâce",
    description: "Durée et unité du délai de grâce, sans durée fiscale ou commerciale supposée.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "finance.payment.allocation_order",
    domain: "finance",
    label: "Affectation des paiements",
    description: "Ordre manuel ou automatique entre enfants, factures et échéances, sous forme de liste bornée.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "finance.payment.overpayment_policy",
    domain: "finance",
    label: "Traitement d’un trop-perçu",
    description: "Choix entre crédit, rejet ou remboursement selon la procédure validée.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["credit", "reject", "refund"] },
  },
  {
    key: "finance.payment.manual_confirmation",
    domain: "finance",
    label: "Confirmation manuelle des paiements",
    description: "Seuils, justificatifs et rôles de confirmation manuelle par site.",
    type: "structured",
    required: false,
    scopes: scope.site,
    constraints: { structure: "object" },
  },
  {
    key: "finance.cash.variance_approval",
    domain: "finance",
    label: "Approbation des écarts de caisse",
    description: "Seuils d’écart et double approbation éventuelle, sans montant arbitraire.",
    type: "structured",
    required: false,
    scopes: scope.site,
    constraints: { structure: "array" },
  },
  {
    key: "finance.document.numbering_scope",
    domain: "finance",
    label: "Portée des séquences financières",
    description: "Portée des numéros de factures, reçus et avoirs, sans réutilisation de numéro.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["organization", "site", "school_year", "document_type"] },
  },
  {
    key: "finance.dunning.schedule",
    domain: "finance",
    label: "Calendrier de relance",
    description: "Échéances, canaux et contenus de relance, sans pénalité ni message implicite.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "array" },
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
    key: "hr.contract.types",
    domain: "hr",
    label: "Types de contrats",
    description: "Types de contrats et champs obligatoires propres à l’établissement, sans liste nationale figée.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "hr.work_schedule.pattern",
    domain: "hr",
    label: "Horaires de travail",
    description: "Jours, horaires et exceptions par site ou fonction, sous forme de structure versionnée.",
    type: "structured",
    required: true,
    scopes: scope.site,
    constraints: { structure: "object" },
  },
  {
    key: "hr.attendance.capture_mode",
    domain: "hr",
    label: "Pointage du personnel",
    description: "Mode de pointage et de correction choisi pour le personnel.",
    type: "enum",
    required: true,
    scopes: scope.site,
    constraints: { values: ["daily", "session", "manual"] },
  },
  {
    key: "hr.leave.catalog",
    domain: "hr",
    label: "Types de congés",
    description: "Types de congés, justificatifs et règles associées propres à l’établissement.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "hr.leave.accrual_policy",
    domain: "hr",
    label: "Acquisition des congés",
    description: "Acquisition, prorata et paramètres par contrat, sans durée légale supposée.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "hr.leave.day_counting",
    domain: "hr",
    label: "Comptage des jours de congé",
    description: "Choix explicite entre jours ouvrés, ouvrables ou calendaires.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["working", "business", "calendar"] },
  },
  {
    key: "hr.leave.carryover_policy",
    domain: "hr",
    label: "Report des congés",
    description: "Report, expiration et limites éventuelles, sans durée de conservation inventée.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "hr.leave.approval_workflow",
    domain: "hr",
    label: "Circuit d’approbation des congés",
    description: "Approbateurs selon la durée et la fonction, avec délégations versionnées.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
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
    key: "payroll.closing_calendar",
    domain: "payroll",
    label: "Calendrier de clôture et règlement",
    description: "Dates et fuseau de clôture et de règlement, sans calendrier de paie implicite.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "payroll.base.calculation",
    domain: "payroll",
    label: "Base de calcul de paie",
    description: "Base mensuelle, horaire ou journalière réellement utilisée et validée localement.",
    type: "enum",
    required: true,
    scopes: scope.organization,
    constraints: { values: ["monthly", "hourly", "daily"] },
  },
  {
    key: "payroll.proration_policy",
    domain: "payroll",
    label: "Prorata de paie",
    description: "Entrées, sorties, absences et temps partiel ; aucune formule de prorata n’est présumée.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "payroll.variable.catalog",
    domain: "payroll",
    label: "Variables de paie",
    description: "Primes, retenues, avances et justificatifs autorisés, sans variable fiscale implicite.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "payroll.contribution.catalog",
    domain: "payroll",
    label: "Cotisations applicables",
    description: "Cotisations, références et dates d’effet validées par le spécialiste local.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "payroll.tax.catalog",
    domain: "payroll",
    label: "Impôts applicables",
    description: "Impôts, références et dates d’effet validés par le spécialiste local ; aucun taux par défaut.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "payroll.rounding.precision",
    domain: "payroll",
    label: "Précision de paie",
    description: "Nombre de décimales conservées à l’étape de paie configurée.",
    type: "integer",
    required: false,
    scopes: scope.organization,
    constraints: { min: 0, max: 6 },
  },
  {
    key: "payroll.rounding.stage",
    domain: "payroll",
    label: "Étape d’arrondi de paie",
    description: "Étape à laquelle l’arrondi de paie est appliqué, sans arrondi implicite.",
    type: "enum",
    required: false,
    scopes: scope.organization,
    constraints: { values: ["line", "subtotal", "final"] },
  },
  {
    key: "payroll.validation.workflow",
    domain: "payroll",
    label: "Circuit de validation de paie",
    description: "Préparation, contrôle, validation et règlement avec les rôles réellement habilités.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "array" },
  },
  {
    key: "payroll.correction_policy",
    domain: "payroll",
    label: "Régularisation de paie",
    description: "Régularisation sans réécriture du bulletin historique, avec circuit et mentions validés.",
    type: "structured",
    required: true,
    scopes: scope.organization,
    constraints: { structure: "object" },
  },
  {
    key: "payroll.payment.export_format",
    domain: "payroll",
    label: "Format d’export du règlement",
    description: "Format du destinataire et correspondance des champs, sans format bancaire supposé.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
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
    key: "document.required_fields",
    domain: "documents",
    label: "Champs obligatoires du document",
    description: "Champs requis par modèle, sans considérer le prototype comme un formulaire officiel.",
    type: "structured",
    required: true,
    scopes: scope.program,
    constraints: { structure: "array" },
  },
  {
    key: "document.template.identity",
    domain: "documents",
    label: "Identité du modèle",
    description: "Intitulé, code, édition et programme du modèle, avec version et source explicites.",
    type: "structured",
    required: true,
    scopes: scope.program,
    constraints: { structure: "object" },
  },
  {
    key: "document.publication.workflow",
    domain: "documents",
    label: "Circuit de publication du document",
    description: "Préparateurs, validateurs, signataires et délégations par type de document.",
    type: "structured",
    required: true,
    scopes: scope.program,
    constraints: { structure: "array" },
  },
  {
    key: "document.authenticity.mode",
    domain: "documents",
    label: "Authenticité du document",
    description: "Identifiant ou QR éventuel, sans exposer de donnée publique sensible.",
    type: "enum",
    required: false,
    scopes: scope.program,
    constraints: { values: ["none", "identifier", "qr"] },
  },
  {
    key: "notification.dunning.enabled",
    domain: "documents",
    label: "Notification de relance",
    description: "Activation explicite des relances, hors canal, contenu et destinataires.",
    type: "boolean",
    required: false,
    scopes: scope.organization,
  },
  {
    key: "notification.retry_policy",
    domain: "documents",
    label: "Politique de nouvelle tentative",
    description: "Tentatives et délais techniques par canal, sans délai réseau présenté comme règle métier.",
    type: "structured",
    required: false,
    scopes: scope.organization,
    constraints: { structure: "object" },
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
        structure: z.enum(["array", "object"]).optional(),
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
