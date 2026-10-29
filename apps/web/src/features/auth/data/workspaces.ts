import type { RoleId } from "./roles";

export interface WorkspaceAction {
  label: string;
  href: string;
}

export interface WorkspaceTask {
  title: string;
  description: string;
  href: string;
}

export interface RoleWorkspace {
  role: RoleId;
  spaceLabel: string;
  title: string;
  description: string;
  scopeLabel: string;
  profileName: string;
  primaryAction: WorkspaceAction;
  tasks: WorkspaceTask[];
  guideIntroduction: string;
  firstTask: string;
}

export const ROLE_WORKSPACES: Record<RoleId, RoleWorkspace> = {
  technical_admin: {
    role: "technical_admin",
    spaceLabel: "Console technique",
    title: "Administrez les accès sans exposer les données métier",
    description: "Supervisez les modules, les rôles et les environnements confiés à votre équipe technique.",
    scopeLabel: "Établissements gérés · aperçu local",
    profileName: "Administration technique",
    primaryAction: { label: "Gérer les modules", href: "/modules/" },
    tasks: [
      { title: "Vérifier les modules disponibles", description: "Contrôlez les états d’activation et les prérequis du prototype.", href: "/modules/" },
      { title: "Préparer les habilitations", description: "Le catalogue des 13 rôles est prêt pour la future gestion des comptes.", href: "/modules/" },
    ],
    guideIntroduction: "Cette console sépare volontairement l’administration technique des contenus scolaires, financiers et RH.",
    firstTask: "Ouvrez le catalogue des modules et repérez la différence entre actif, à configurer et désactivé.",
  },
  direction: {
    role: "direction",
    spaceLabel: "Espace Direction",
    title: "Pilotez votre établissement",
    description: "Suivez les indicateurs, les validations et les décisions qui demandent votre attention.",
    scopeLabel: "Établissement · toutes les zones autorisées",
    profileName: "Direction",
    primaryAction: { label: "Voir les validations", href: "/proclamation/" },
    tasks: [
      { title: "Contrôler les dossiers", description: "Vérifiez les inscriptions et les bulletins incomplets.", href: "/bulletins/" },
      { title: "Préparer la proclamation", description: "Examinez le classement avant toute publication officielle.", href: "/proclamation/" },
    ],
    guideIntroduction: "Votre accueil rassemble les indicateurs et les validations utiles au pilotage de l’établissement.",
    firstTask: "Consultez les actions en attente, puis ouvrez la proclamation sans publier tant que les contrôles ne sont pas terminés.",
  },
  academic_manager: {
    role: "academic_manager",
    spaceLabel: "Espace pédagogique",
    title: "Coordonnez la structure et la qualité des évaluations",
    description: "Suivez les classes, les matières et la complétude des notes avant les délibérations.",
    scopeLabel: "Périmètre pédagogique · aperçu local",
    profileName: "Responsable pédagogique",
    primaryAction: { label: "Contrôler les notes", href: "/notes/" },
    tasks: [
      { title: "Vérifier les programmes", description: "Contrôlez les classes, les matières et leurs coefficients.", href: "/classes/" },
      { title: "Contrôler les bulletins", description: "Repérez les dossiers incomplets avant la délibération.", href: "/bulletins/" },
      { title: "Préparer la proclamation", description: "Vérifiez les résultats avant validation par la direction.", href: "/proclamation/" },
    ],
    guideIntroduction: "Votre espace privilégie les programmes, les validations de notes et la préparation des résultats.",
    firstTask: "Ouvrez la saisie des notes et vérifiez la complétude d’une classe avant de consulter son bulletin.",
  },
  school_secretary: {
    role: "school_secretary",
    spaceLabel: "Espace scolarité",
    title: "Gardez chaque dossier élève à jour",
    description: "Traitez les inscriptions, les affectations et les informations administratives des élèves.",
    scopeLabel: "Scolarité · dossiers administratifs",
    profileName: "Secrétariat scolaire",
    primaryAction: { label: "Nouvelle inscription", href: "/inscriptions/" },
    tasks: [
      { title: "Traiter les dossiers en attente", description: "Vérifiez les informations avant de confirmer les inscriptions.", href: "/eleves/" },
      { title: "Vérifier les affectations", description: "Contrôlez les classes et les capacités disponibles.", href: "/classes/" },
    ],
    guideIntroduction: "Votre navigation se concentre sur les élèves, les inscriptions et les affectations administratives.",
    firstTask: "Ouvrez le répertoire des élèves, recherchez un dossier puis vérifiez sa classe et son statut.",
  },
  student_affairs: {
    role: "student_affairs",
    spaceLabel: "Espace vie scolaire",
    title: "Suivez l’assiduité au bon moment",
    description: "Contrôlez les appels, les absences et les retards dans votre périmètre de suivi.",
    scopeLabel: "Assiduité · classes suivies",
    profileName: "Vie scolaire",
    primaryAction: { label: "Ouvrir les appels", href: "/presences/" },
    tasks: [
      { title: "Contrôler l’appel du jour", description: "Vérifiez les présences et les élèves non encore pointés.", href: "/presences/" },
      { title: "Examiner l’historique", description: "Repérez les absences et les retards enregistrés.", href: "/presences/" },
    ],
    guideIntroduction: "Votre espace met en avant les appels et les informations d’assiduité, sans exposer les finances ou la paie.",
    firstTask: "Choisissez une classe dans le module Présences et vérifiez les statuts avant d’enregistrer l’appel.",
  },
  teacher: {
    role: "teacher",
    spaceLabel: "Espace enseignant",
    title: "Retrouvez vos appels et vos évaluations",
    description: "Accédez rapidement aux classes, aux notes et aux bulletins utiles à votre activité.",
    scopeLabel: "Affectations de démonstration · aperçu non sécurisé",
    profileName: "Espoir Ndayisaba",
    primaryAction: { label: "Saisir les notes", href: "/notes/" },
    tasks: [
      { title: "Faire l’appel", description: "Enregistrez les présences de la classe et de la date choisies.", href: "/presences/" },
      { title: "Compléter les notes", description: "Vérifiez les valeurs attendues pour la période active.", href: "/notes/" },
      { title: "Consulter les bulletins", description: "Contrôlez les résultats disponibles avant leur publication.", href: "/bulletins/" },
    ],
    guideIntroduction: "Votre espace rassemble uniquement les parcours utiles à l’appel, aux notes et au suivi pédagogique.",
    firstTask: "Ouvrez une classe dans Notes, vérifiez les valeurs manquantes puis enregistrez le brouillon.",
  },
  accountant: {
    role: "accountant",
    spaceLabel: "Espace comptabilité",
    title: "Préparez un pilotage financier lisible",
    description: "Retrouvez le parcours d’activation des tarifs, factures, créances et rapports financiers.",
    scopeLabel: "Finance · module planifié",
    profileName: "Comptabilité",
    primaryAction: { label: "Voir le module finance", href: "/modules/" },
    tasks: [
      { title: "Vérifier les prérequis financiers", description: "Le module de facturation reste planifié et n’affiche aucune donnée fictive.", href: "/modules/" },
      { title: "Comprendre l’activation progressive", description: "Repérez les états et les responsables de configuration.", href: "/modules/" },
    ],
    guideIntroduction: "Cet accueil prépare l’espace finance sans simuler des factures ou des montants qui ne sont pas encore gérés.",
    firstTask: "Repérez le module Finance dans le catalogue et consultez son état de disponibilité.",
  },
  cashier: {
    role: "cashier",
    spaceLabel: "Espace caisse",
    title: "Retrouvez le futur parcours d’encaissement",
    description: "Préparez le parcours session de caisse, paiement, reçu et clôture.",
    scopeLabel: "Caisse affectée · module planifié",
    profileName: "Caisse",
    primaryAction: { label: "Voir le module caisse", href: "/modules/" },
    tasks: [
      { title: "Vérifier la disponibilité de la caisse", description: "Aucune session réelle n’est ouverte dans le prototype actuel.", href: "/modules/" },
      { title: "Découvrir le cycle d’activation", description: "La future caisse exigera une affectation et une session ouverte.", href: "/modules/" },
    ],
    guideIntroduction: "Votre futur espace de caisse restera séparé des tarifs et des règles financières.",
    firstTask: "Ouvrez le catalogue et repérez les prérequis du futur module Caisse.",
  },
  hr_manager: {
    role: "hr_manager",
    spaceLabel: "Espace ressources humaines",
    title: "Préparez un espace personnel cloisonné",
    description: "Retrouvez le parcours prévu pour les dossiers, contrats, congés et la préparation RH.",
    scopeLabel: "Personnel · module planifié",
    profileName: "Ressources humaines",
    primaryAction: { label: "Voir le module RH", href: "/modules/" },
    tasks: [
      { title: "Vérifier les prérequis RH", description: "Les dossiers du personnel ne sont pas encore intégrés au prototype.", href: "/modules/" },
      { title: "Préparer la séparation de la paie", description: "La préparation et la validation nécessiteront des permissions dédiées.", href: "/modules/" },
    ],
    guideIntroduction: "Cet espace prépare les parcours RH sans créer de dossiers employés ou de règles de paie fictives.",
    firstTask: "Consultez le module Personnel et repérez sa distinction avec les permissions de paie.",
  },
  employee: {
    role: "employee",
    spaceLabel: "Mon espace employé",
    title: "Vos documents professionnels au même endroit",
    description: "Découvrez le futur self-service consacré à votre profil, vos congés et vos documents publiés.",
    scopeLabel: "Dossier personnel uniquement",
    profileName: "Employé démonstration",
    primaryAction: { label: "Voir mes futurs services", href: "/modules/" },
    tasks: [
      { title: "Découvrir le self-service", description: "Le futur espace ne donnera accès qu’à vos propres documents.", href: "/modules/" },
      { title: "Comprendre la publication", description: "Un bulletin ou document ne sera visible qu’après publication autorisée.", href: "/modules/" },
    ],
    guideIntroduction: "Votre espace personnel restera distinct de l’administration RH et des dossiers des collègues.",
    firstTask: "Parcourez les services planifiés et repérez ceux qui concernent votre dossier personnel.",
  },
  guardian: {
    role: "guardian",
    spaceLabel: "Portail du responsable",
    title: "Suivez uniquement les enfants qui vous sont rattachés",
    description: "Découvrez un portail simple pour les publications, l’assiduité autorisée et les informations du foyer.",
    scopeLabel: "Enfants rattachés · contenus publiés",
    profileName: "Parent / responsable",
    primaryAction: { label: "Découvrir le portail", href: "/modules/" },
    tasks: [
      { title: "Vérifier le principe de rattachement", description: "Le futur portail exigera un lien validé avec chaque enfant.", href: "/modules/" },
      { title: "Comprendre les publications", description: "Aucun brouillon de bulletin ne sera visible depuis le portail.", href: "/modules/" },
    ],
    guideIntroduction: "Le portail privilégiera une lecture simple et ne montrera que les informations publiées pour vos enfants.",
    firstTask: "Repérez le futur portail famille et son prérequis de rattachement vérifié.",
  },
  student: {
    role: "student",
    spaceLabel: "Portail de l’élève",
    title: "Retrouvez vos informations publiées",
    description: "Découvrez votre futur espace pour les classes, ressources et résultats destinés aux élèves.",
    scopeLabel: "Dossier personnel · contenus publiés",
    profileName: "Élève démonstration",
    primaryAction: { label: "Découvrir mon portail", href: "/modules/" },
    tasks: [
      { title: "Découvrir les contenus du portail", description: "Le futur espace regroupera uniquement vos informations publiées.", href: "/modules/" },
      { title: "Comprendre la visibilité des notes", description: "Les brouillons et les dossiers d’autres élèves resteront inaccessibles.", href: "/modules/" },
    ],
    guideIntroduction: "Votre portail sera volontairement épuré et limité à votre propre parcours scolaire.",
    firstTask: "Ouvrez le catalogue et repérez les futurs services du portail élève.",
  },
  auditor: {
    role: "auditor",
    spaceLabel: "Espace audit",
    title: "Contrôlez sans modifier",
    description: "Préparez un accès en lecture seule aux rapports et aux historiques couverts par votre mandat.",
    scopeLabel: "Mandat de lecture seule",
    profileName: "Auditeur démonstration",
    primaryAction: { label: "Voir le périmètre d’audit", href: "/modules/" },
    tasks: [
      { title: "Vérifier le principe de lecture seule", description: "Aucune action de création ou de modification n’est proposée dans cet espace.", href: "/modules/" },
      { title: "Préparer les contrôles d’export", description: "L’export nécessitera une permission distincte de la consultation.", href: "/modules/" },
    ],
    guideIntroduction: "Votre espace est conçu pour consulter un périmètre précis, sans modifier les données ni exporter par défaut.",
    firstTask: "Consultez le module Audit et vérifiez qu’aucune opération d’écriture n’est proposée.",
  },
};
