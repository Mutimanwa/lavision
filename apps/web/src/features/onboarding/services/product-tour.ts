import type { DriveStep, Side } from "driver.js";

export const PRODUCT_TOUR_IDS = [
  "dashboard",
  "students",
  "enrollments",
  "classes",
  "attendance",
  "grades",
  "reports",
  "proclamation",
  "modules",
  "settings",
] as const;

export type ProductTourId = (typeof PRODUCT_TOUR_IDS)[number];

const ROUTE_TOURS: Record<string, ProductTourId> = {
  "/": "dashboard",
  "/eleves": "students",
  "/inscriptions": "enrollments",
  "/classes": "classes",
  "/presences": "attendance",
  "/notes": "grades",
  "/bulletins": "reports",
  "/proclamation": "proclamation",
  "/modules": "modules",
  "/parametres": "settings",
};

export function getProductTourId(pathname: string): ProductTourId | null {
  const normalized = pathname === "/" ? "/" : `/${pathname.split("/").filter(Boolean)[0] ?? ""}`;
  return ROUTE_TOURS[normalized] ?? null;
}

function step(
  element: string,
  title: string,
  description: string,
  side: Side = "bottom",
  waitForElement = 0,
): DriveStep {
  return {
    element,
    skipMissingElement: true,
    waitForElement,
    popover: { title, description, side, align: "start" },
  };
}

function pageStep(element: string, title: string, description: string, side: Side = "bottom"): DriveStep {
  return step(element, title, description, side, 900);
}

function commonSteps(): DriveStep[] {
  return [
    {
      popover: {
        title: "Bienvenue dans lavision",
        description: "Cette visite interactive vous montre les zones et les actions de cette page. Utilisez Suivant, Précédent ou les flèches du clavier. Vous pouvez la fermer ou la passer à tout moment.",
        side: "bottom",
        align: "center",
      },
    },
    step(
      '[data-tour="main-navigation"]',
      "Votre menu de navigation",
      "Les modules sont regroupés par responsabilité. Ouvrez une section puis choisissez un module pour accéder à sa page. Les entrées affichées dépendent du rôle de démonstration et de l’activation des modules.",
      "right",
    ),
    step(
      '[data-tour="mobile-menu"]',
      "Le menu sur petit écran",
      "Sur mobile, ce bouton ouvre le menu de navigation. Vous y retrouverez les mêmes modules que sur un écran plus large.",
      "bottom",
    ),
    step(
      '[data-tour="global-search"]',
      "Rechercher un élève",
      "Recherchez un nom ou un matricule depuis n’importe quelle page. Le raccourci Ctrl+K, ou Cmd+K sur Mac, place directement le curseur dans ce champ.",
      "bottom",
    ),
    step(
      '[data-tour="notifications"]',
      "Les notifications utiles",
      "Cette cloche rassemble les dossiers et les notes qui demandent votre attention. Une notification vous conduit vers le module concerné.",
      "bottom",
    ),
    step(
      '[data-tour="profile-menu"]',
      "Votre profil et le centre d’aide",
      "Ouvrez ce menu pour consulter votre espace, changer le rôle de démonstration ou relancer la visite guidée depuis le centre d’aide.",
      "bottom",
    ),
  ];
}

function dashboardSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Vos raccourcis d’action", "Les boutons de l’en-tête permettent d’ouvrir directement la saisie des notes ou une nouvelle inscription, sans passer par le menu."),
    pageStep('[data-tour="first-steps"]', "Reprendre la visite plus tard", "Cette carte permet de commencer, reprendre ou relancer la visite. Votre progression est conservée séparément pour chaque rôle et chaque page.", "top"),
    pageStep('[data-tour="dashboard-stats"]', "Lire les indicateurs", "Ces cartes résument les élèves, les classes, les notes et les résultats disponibles. Elles donnent une lecture rapide, tandis que les modules permettent d’agir sur les dossiers.", "top"),
    pageStep('[data-tour="dashboard-pending"]', "Traiter les priorités", "Chaque ligne vous conduit vers les inscriptions, les notes ou les bulletins à contrôler. Les compteurs vous aident à choisir votre prochaine action.", "left"),
    pageStep(".workspace-metrics", "Les indicateurs de votre rôle", "Cette synthèse est adaptée à votre espace de travail. Elle présente uniquement les informations utiles au rôle de démonstration sélectionné.", "top"),
    pageStep(".workspace-home-grid", "Vos actions prioritaires", "Les liens de cette zone vous conduisent directement vers les tâches de votre rôle. Le panneau latéral rappelle le périmètre de l’aperçu et les modules disponibles.", "top"),
    pageStep(".workspace-modules-card", "Retrouver vos modules", "Cette liste reprend les modules de votre espace et leur état. Utilisez-la pour comprendre la navigation progressive avant d’ouvrir une interface.", "top"),
  ];
}

function studentSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Exporter ou inscrire un élève", "Exporter CSV télécharge la liste filtrée. Nouvelle inscription ouvre le formulaire en plusieurs étapes pour créer un dossier."),
    pageStep('[data-tour="student-directory"] .lv-data-table-header', "Rechercher et filtrer les dossiers", "Utilisez la recherche, le filtre de classe et le filtre de statut pour réduire la liste. Les compteurs et la pagination suivent les résultats affichés.", "bottom"),
    pageStep('[data-tour="student-directory"] .lv-school-table', "Consulter un dossier et ses actions", "Cliquez sur le nom d’un élève pour ouvrir son dossier. Le menu Actions de chaque ligne permet aussi de consulter le dossier ou son bulletin.", "top"),
    pageStep('[data-tour="student-directory"] .table-pagination', "Parcourir les résultats", "Les flèches et les numéros de page déplacent la liste sans supprimer vos filtres. Une sélection de dossiers permet également un export groupé.", "top"),
  ];
}

function enrollmentSteps(): DriveStep[] {
  return [
    pageStep(".form-steps", "Avancer étape par étape", "Le formulaire sépare l’identité, la scolarité, le responsable légal et la confirmation. Vous pouvez revenir à une étape précédente pour corriger une information.", "bottom"),
    pageStep(".form-step-content", "Renseigner les informations", "Les champs marqués d’un astérisque sont obligatoires. Les messages d’erreur s’affichent près du champ à corriger avant de passer à l’étape suivante.", "top"),
    pageStep(".form-footer", "Contrôler puis enregistrer", "Continuer valide l’étape affichée. À la confirmation, le bouton enregistre le dossier et attribue le matricule ; Précédent permet de revenir en arrière.", "top"),
    pageStep(".enrollment-help", "Comprendre la suite du parcours", "Cette aide rappelle comment le dossier alimente le répertoire, les classes, les notes et les bulletins après l’inscription.", "left"),
  ];
}

function classSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Ouvrir une nouvelle classe", "Le bouton Nouvelle classe ouvre un formulaire pour définir le niveau, la salle, la capacité et le professeur principal."),
    pageStep(".class-card-grid", "Consulter les classes", "Chaque carte présente l’effectif, la capacité, le professeur principal et la moyenne disponible. Le lien en bas ouvre les élèves ou les notes selon votre espace.", "top"),
    pageStep(".subjects-card", "Gérer les matières et coefficients", "Le tableau présente les matières utilisées dans les notes et les bulletins. Les rôles autorisés peuvent modifier un coefficient ou ajouter une matière depuis l’en-tête.", "top"),
  ];
}

function attendanceSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Enregistrer ou exporter l’appel", "Enregistrer l’appel conserve les statuts de la classe et de la date affichées. Exporter télécharge les statuts visibles au format CSV."),
    pageStep(".attendance-controls", "Choisir la classe et la date", "Sélectionnez la classe et le jour de l’appel. Tous présents remplit rapidement les statuts, que vous pouvez ensuite corriger individuellement.", "bottom"),
    pageStep(".attendance-list", "Pointer chaque élève", "Choisissez Présent, Retard, Absent ou Excusé pour chaque élève. Les compteurs se mettent à jour avant l’enregistrement définitif.", "top"),
    pageStep(".attendance-sidebar", "Revoir les appels précédents", "L’historique permet de rouvrir une date et de vérifier le taux de présence. Un retard reste visible tout en étant compté comme une présence dans le taux.", "left"),
  ];
}

function gradeSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Enregistrer ou exporter les notes", "Enregistrer les notes met à jour les bulletins et les classements du prototype. Exporter télécharge la grille de la classe, de la matière et de la période sélectionnées."),
    pageStep(".gradebook-selector-card", "Choisir le contexte de saisie", "Sélectionnez la classe, la matière et la période. La barre de progression indique combien de notes sont renseignées ; changer de contexte avec un brouillon demande confirmation.", "bottom"),
    pageStep(".grade-entry-card", "Saisir les notes des élèves", "Saisissez une valeur de 0 à 20 dans chaque cellule. Les valeurs invalides sont signalées et les cellules vides restent des notes non saisies.", "top"),
    pageStep(".gradebook-sidebar", "Contrôler les résultats et changer de matière", "Les statistiques résument la grille en cours. Les raccourcis de matières permettent de changer de saisie tout en conservant le contrôle des brouillons.", "left"),
  ];
}

function reportSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Exporter ou imprimer le bulletin", "Exporter CSV télécharge les résultats de l’élève affiché. Imprimer / PDF ouvre la boîte de dialogue d’impression du navigateur pour produire le document."),
    pageStep(".bulletin-toolbar", "Choisir l’élève et la période", "Sélectionnez une classe, un élève actif et une période. Les flèches permettent de passer au dossier précédent ou suivant sans changer de classe.", "bottom"),
    pageStep("#bulletin", "Lire le bulletin calculé", "Le document reprend les notes, les coefficients, la moyenne, le rang et l’assiduité. Un bulletin incomplet reste provisoire et signale les matières manquantes.", "top"),
    pageStep(".bulletin-footer-actions", "Passer à la suite", "Après vérification, imprimez le bulletin ou ouvrez la proclamation lorsque votre rôle permet de préparer les résultats de la classe.", "top"),
  ];
}

function proclamationSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Exporter, imprimer ou publier", "Les boutons permettent d’exporter le classement et de l’imprimer. La publication est réservée à la Direction et reste bloquée tant que des dossiers sont incomplets."),
    pageStep(".proclamation-toolbar", "Choisir la délibération", "Sélectionnez la classe et la période. Le statut indique si les résultats sont encore provisoires ou ont déjà été publiés.", "bottom"),
    pageStep(".proclamation-stats", "Vérifier la synthèse", "Ces indicateurs donnent le nombre d’élèves évalués, d’admis, d’ajournés et le taux de réussite calculé sur les dossiers complets.", "top"),
    pageStep(".proclamation-document", "Contrôler le procès-verbal", "Le tableau présente le rang, la moyenne, la complétude et la décision de chaque élève. Le menu Actions ouvre le bulletin correspondant pour vérification.", "top"),
  ];
}

function moduleSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Réinitialiser la navigation de démonstration", "Lorsque votre rôle permet de gérer les modules, ce bouton restaure leurs préférences initiales. Cette action concerne la navigation locale, pas les autorisations du serveur."),
    pageStep(".modules-section", "Comprendre les états des modules", "Actif, à configurer, désactivé et planifié décrivent des états différents. Un module planifié est présenté comme une interface future, sans données fictives.", "top"),
    pageStep(".modules-grid", "Ouvrir ou activer une interface", "Ouvrir conduit à l’interface disponible. Les rôles gestionnaires peuvent activer ou désactiver certains modules pour adapter la navigation de démonstration.", "top"),
  ];
}

function settingsSteps(): DriveStep[] {
  return [
    pageStep(".page-actions", "Enregistrer les paramètres", "Le bouton devient actif après une modification. Il enregistre les informations de l’établissement et les paramètres académiques locaux."),
    pageStep(".settings-main", "Configurer l’établissement et les règles", "Les formulaires regroupent les informations affichées sur les documents, la période active et le seuil de réussite. Les règles variables restent configurables plutôt que fixées dans le code.", "top"),
    pageStep(".rule-config", "Préparer les règles avec leur source", "Cette zone API permet de renseigner une valeur, sa date d’effet, sa source et son contexte. L’enregistrement crée un brouillon ; il ne déclenche pas automatiquement la validation ou la publication.", "top"),
    pageStep(".data-card", "Sauvegarder et restaurer les données", "Téléchargez régulièrement une sauvegarde JSON. Après sélection d’un fichier lavision valide, un résumé s’affiche et la restauration ne remplace les données locales qu’après votre confirmation.", "left"),
    pageStep(".danger-card", "Réinitialiser avec précaution", "Cette action restaure le jeu de démonstration et remplace le travail local. Téléchargez une sauvegarde avant de confirmer si vous souhaitez conserver vos données.", "left"),
  ];
}

const PAGE_STEPS: Record<ProductTourId, () => DriveStep[]> = {
  dashboard: dashboardSteps,
  students: studentSteps,
  enrollments: enrollmentSteps,
  classes: classSteps,
  attendance: attendanceSteps,
  grades: gradeSteps,
  reports: reportSteps,
  proclamation: proclamationSteps,
  modules: moduleSteps,
  settings: settingsSteps,
};

export function getProductTourSteps(tourId: ProductTourId): DriveStep[] {
  return [...commonSteps(), ...PAGE_STEPS[tourId]()];
}

export function getProductTourStepCount(tourId: ProductTourId): number {
  return getProductTourSteps(tourId).length;
}
