export type RoleId =
  | "technical_admin"
  | "direction"
  | "academic_manager"
  | "school_secretary"
  | "student_affairs"
  | "teacher"
  | "accountant"
  | "cashier"
  | "hr_manager"
  | "employee"
  | "guardian"
  | "student"
  | "auditor";

export interface RoleDefinition {
  id: RoleId;
  label: string;
  description: string;
}

/**
 * Functional role catalogue proposed for the future server-side RBAC.
 * A role grants no access without its permissions and resource scopes.
 */
export const ROLE_DEFINITIONS = [
  {
    id: "technical_admin",
    label: "Administrateur technique",
    description: "Gère les comptes, les intégrations et la supervision des établissements qui lui sont affectés, sans accès métier implicite.",
  },
  {
    id: "direction",
    label: "Direction",
    description: "Pilote l’établissement et effectue les validations qui lui sont déléguées, notamment la publication des résultats.",
  },
  {
    id: "academic_manager",
    label: "Responsable pédagogique",
    description: "Coordonne les programmes, les règles académiques et la validation des notes dans sa portée.",
  },
  {
    id: "school_secretary",
    label: "Secrétariat / scolarité",
    description: "Gère les admissions, les inscriptions et les dossiers administratifs des élèves.",
  },
  {
    id: "student_affairs",
    label: "Vie scolaire",
    description: "Suit l’assiduité et la discipline selon les classes et les périodes qui lui sont confiées.",
  },
  {
    id: "teacher",
    label: "Enseignant",
    description: "Saisit les appels et les notes pour ses seules classes et matières assignées.",
  },
  {
    id: "accountant",
    label: "Comptable",
    description: "Gère les tarifs, les factures, les créances et les rapports financiers, sans accès implicite à la paie.",
  },
  {
    id: "cashier",
    label: "Caissier",
    description: "Enregistre les encaissements et délivre les reçus dans ses sessions de caisse autorisées.",
  },
  {
    id: "hr_manager",
    label: "Responsable RH",
    description: "Gère les dossiers du personnel, les contrats et les congés ; la paie exige des permissions dédiées.",
  },
  {
    id: "employee",
    label: "Employé",
    description: "Consulte uniquement ses propres informations et documents professionnels publiés.",
  },
  {
    id: "guardian",
    label: "Parent / responsable",
    description: "Consulte les informations publiées et la situation financière des seuls enfants qui lui sont légalement rattachés.",
  },
  {
    id: "student",
    label: "Élève",
    description: "Consulte uniquement ses propres contenus et résultats publiés.",
  },
  {
    id: "auditor",
    label: "Auditeur / lecture seule",
    description: "Consulte les rapports et journaux couverts par son mandat, sans modifier les données.",
  },
] as const satisfies readonly RoleDefinition[];
