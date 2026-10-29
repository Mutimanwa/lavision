import type { RoleId } from "@/features/auth/data/roles";

export type ModuleId =
  | "students"
  | "enrollments"
  | "classes"
  | "attendance"
  | "grades"
  | "reports"
  | "proclamation"
  | "settings"
  | "administration"
  | "finance"
  | "cash"
  | "hr"
  | "employee_service"
  | "family_portal"
  | "audit";

export type ModuleSection = "Scolarité" | "Évaluations" | "Administration";
export type ModuleStatus = "active" | "configuring" | "disabled" | "planned";

export interface ModuleDefinition {
  id: ModuleId;
  label: string;
  description: string;
  roles: readonly RoleId[];
  implemented: boolean;
  href?: string;
  section?: ModuleSection;
  core?: boolean;
}

export const MODULE_DEFINITIONS = [
  {
    id: "students",
    label: "Élèves",
    description: "Répertoire, recherche et dossiers administratifs des élèves.",
    roles: ["direction", "school_secretary"],
    implemented: true,
    href: "/eleves/",
    section: "Scolarité",
  },
  {
    id: "enrollments",
    label: "Inscriptions",
    description: "Création guidée d’un dossier et affectation à une classe.",
    roles: ["direction", "school_secretary"],
    implemented: true,
    href: "/inscriptions/",
    section: "Scolarité",
  },
  {
    id: "classes",
    label: "Classes & matières",
    description: "Classes, salles, capacités, matières et coefficients.",
    roles: ["direction", "academic_manager", "school_secretary"],
    implemented: true,
    href: "/classes/",
    section: "Scolarité",
  },
  {
    id: "attendance",
    label: "Présences",
    description: "Appel quotidien, retards, absences et historique.",
    roles: ["direction", "student_affairs", "teacher"],
    implemented: true,
    href: "/presences/",
    section: "Scolarité",
  },
  {
    id: "grades",
    label: "Notes",
    description: "Saisie groupée et contrôle des notes par classe et matière.",
    roles: ["direction", "academic_manager", "teacher"],
    implemented: true,
    href: "/notes/",
    section: "Évaluations",
  },
  {
    id: "reports",
    label: "Bulletins",
    description: "Calcul, contrôle et impression des bulletins scolaires.",
    roles: ["direction", "academic_manager", "teacher"],
    implemented: true,
    href: "/bulletins/",
    section: "Évaluations",
  },
  {
    id: "proclamation",
    label: "Proclamation",
    description: "Classement, contrôle des dossiers et publication des résultats.",
    roles: ["direction", "academic_manager"],
    implemented: true,
    href: "/proclamation/",
    section: "Évaluations",
  },
  {
    id: "settings",
    label: "Paramètres",
    description: "Identité de l’établissement, règles générales et sauvegardes.",
    roles: ["direction"],
    implemented: true,
    href: "/parametres/",
    section: "Administration",
    core: true,
  },
  {
    id: "administration",
    label: "Utilisateurs & accès",
    description: "Comptes, invitations, rôles, portées et sessions.",
    roles: ["technical_admin"],
    implemented: false,
  },
  {
    id: "finance",
    label: "Finance",
    description: "Tarifs, factures, échéanciers, créances et rapports.",
    roles: ["accountant"],
    implemented: false,
  },
  {
    id: "cash",
    label: "Caisse",
    description: "Sessions de caisse, paiements, reçus et clôtures.",
    roles: ["cashier"],
    implemented: false,
  },
  {
    id: "hr",
    label: "Personnel & RH",
    description: "Dossiers employés, contrats, affectations et congés.",
    roles: ["hr_manager"],
    implemented: false,
  },
  {
    id: "employee_service",
    label: "Services employé",
    description: "Profil, documents, congés et bulletins de paie publiés.",
    roles: ["employee"],
    implemented: false,
  },
  {
    id: "family_portal",
    label: "Portails famille & élève",
    description: "Contenus publiés pour les enfants rattachés ou l’élève concerné.",
    roles: ["guardian", "student"],
    implemented: false,
  },
  {
    id: "audit",
    label: "Audit & historique",
    description: "Rapports et journaux en lecture seule selon le mandat.",
    roles: ["auditor"],
    implemented: false,
  },
] as const satisfies readonly ModuleDefinition[];

export type ModulePreferences = Partial<Record<ModuleId, boolean>>;

export function createDefaultModulePreferences(): ModulePreferences {
  return Object.fromEntries(
    MODULE_DEFINITIONS.filter((module) => module.implemented).map((module) => [module.id, true]),
  ) as ModulePreferences;
}
