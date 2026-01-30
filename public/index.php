<?php
/**
 * Point d'entrée principal de l'application LaVision
 * Gère le routage et l'initialisation du système
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 */

// =============================================
// INITIALISATION DU SYSTÈME
// =============================================

// Démarrage de la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définition du fuseau horaire
date_default_timezone_set('Africa/Kinshasa');

// =============================================
// CHARGEMENT DES CONFIGURATIONS ET SERVICES
// =============================================

// Chargement de la configuration principale
require_once __DIR__ . '/../src/Config/config.php';

// Chargement des services de base
require_once SERVICES_PATH . '/database.php';
require_once SERVICES_PATH . '/router.php';
require_once SERVICES_PATH . '/auth.php';
require_once SERVICES_PATH . '/functions.php';
require_once SERVICES_PATH . '/validation.php';

// =============================================
// GESTION DES ERREURS ET LOGGING
// =============================================

// Configuration des erreurs selon le mode debug
if (DEBUG_MODE) {
    // Affichage des erreurs en développement
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Masquage des erreurs en production
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// =============================================
// SÉCURITÉ DE BASE
// =============================================

// Protection contre les attaques XSS et injection
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Protection CSRF - génération du token si nécessaire
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// =============================================
// ROUTAGE ET TRAITEMENT DES REQUÊTES
// =============================================

// Récupération de la page demandée
$page_actuelle = get_current_page();

// Vérification de l'authentification
$pages_publiques = ['login', 'register', 'forgot-password', 'reset-password', 'logout'];
$pages_auth = ['login', 'register', 'forgot-password', 'reset-password', 'logout'];
$pages_erreur = ['404', '403', '500'];

if (!in_array($page_actuelle, $pages_publiques) && !est_connecte()) {
    // Redirection vers la page de connexion
    redirect('login');
    exit;
}

// =============================================
// CHARGEMENT DES CONTRÔLEURS SELON LA PAGE
// =============================================

// Contrôleurs d'authentification
if (in_array($page_actuelle, ['login', 'logout', 'forgot-password', 'reset-password', 'lock-screen'])) {
    require_once CONTROLLERS_PATH . '/AuthController.php';

    switch ($page_actuelle) {
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                auth_processLogin();
            } else {
                auth_login();
            }
            break;
        case 'logout':
            auth_logout();
            break;
        case 'forgot-password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                auth_processForgotPassword();
            } else {
                auth_forgotPassword();
            }
            break;
        case 'reset-password':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                auth_processResetPassword();
            } else {
                auth_resetPassword();
            }
            break;
        case 'lock-screen':
            auth_lockScreen();
            break;
    }
    exit;
}

// Contrôleur du dashboard
if ($page_actuelle === 'dashboard') {
    require_once CONTROLLERS_PATH . '/DashboardController.php';
    dashboard_index();
    exit;
}

// Contrôleurs d'erreur
if (in_array($page_actuelle, ['404', '403', '500'])) {
    require_once CONTROLLERS_PATH . '/ErrorController.php';

    switch ($page_actuelle) {
        case '404':
            error_notFound();
            break;
        case '403':
            error_forbidden();
            break;
        case '500':
            error_serverError();
            break;
    }
    exit;
}

// Contrôleurs pour les élèves
if (strpos($page_actuelle, 'eleves') === 0) {
    require_once CONTROLLERS_PATH . '/EleveController.php';

    switch ($page_actuelle) {
        case 'eleves':
            afficher_liste_eleves();
            break;
        case 'eleves/ajouter':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                traiter_ajout_eleve();
            } else {
                afficher_formulaire_ajout_eleve();
            }
            break;
        case 'eleves/modifier':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                traiter_modification_eleve();
            } else {
                afficher_formulaire_modification_eleve();
            }
            break;
        case 'eleves/admission':
            afficher_admission_eleves();
            break;
        case 'eleves/parents':
            afficher_gestion_parents();
            break;
        case 'eleves/detail':
            afficher_details_eleve();
            break;
        default:
            load_page($page_actuelle);
            break;
    }
    exit;
}

// Contrôleurs pour l'académie
elseif (strpos($page_actuelle, 'academique') === 0) {
    require_once CONTROLLERS_PATH . '/AcademiqueController.php';

    switch ($page_actuelle) {
        case 'academique/options':
            afficher_options_academiques();
            break;
        case 'academique/classes':
            afficher_gestion_classes();
            break;
        case 'academique/classes/ajouter':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                traiter_ajout_classe();
            } else {
                afficher_formulaire_ajout_classe();
            }
            break;
        case 'academique/matieres':
            afficher_gestion_matieres();
            break;
        case 'academique/matieres/ajouter':
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                traiter_ajout_matiere();
            }else{
                afficher_formulaire_ajout_matiere();
            }
            break;
            case 'academique/horaires':
                afficher_gestion_horaires();
            break;
            case 'academique/horaires/sauvegarder':
                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    traiter_sauvegarde_horaires();
                }else{
                    afficher_gestion_horaires();
                }
            break;
            default:
            load_page($page_actuelle);
            break;
    }   
    exit;
}

// Contrôleurs pour le personnel
elseif (strpos($page_actuelle, 'personnel') === 0) {
    require_once CONTROLLERS_PATH . '/PersonnelController.php';

    switch ($page_actuelle) {
        case 'personnel/professeurs':
            afficher_professeurs();
            break;
        case 'personnel/professeurs/ajouter':
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                afficher_formulaire_professeur();
            }else{
                afficher_formulaire_professeur();
            }
            break;
        case 'personnel/personnels':
            afficher_administrateurs();
            break;
         case 'personnel/personnels/ajouter':
            if($_SERVER['REQUEST_METHOD']=== "POST"){
                traiter_formulaire_administrateur();
            }else{
                afficher_formulaire_administrateur();
            }
            break;

        default:
            load_page($page_actuelle);
            break;
    }
    exit;
}

// Contrôleurs pour les cours et examens
elseif (strpos($page_actuelle, 'cours') === 0) {
    require_once CONTROLLERS_PATH . '/CoursController.php';

    switch ($page_actuelle) {
        case 'cours/planification':
            afficher_planification_cours();
            break;
        case 'cours/absences':
            afficher_gestion_absences();
            break;
        case 'cours/examens':
            afficher_gestion_examens();
            break;
        case 'cours/notes':
            afficher_gestion_notes();
            break;
        default:
            load_page($page_actuelle);
            break;
    }
    exit;
}

// Contrôleurs pour la finance
elseif (strpos($page_actuelle, 'finance') === 0) {
    require_once CONTROLLERS_PATH . '/FinanceController.php';

    switch ($page_actuelle) {
        case 'finance/frais-scolaires':
            afficher_gestion_frais_scolaires();
            break;
        case 'finance/paiements':
            afficher_gestion_paiements();
            break;
        default:
            load_page($page_actuelle);
            break;
    }
    exit;
}

// Contrôleurs pour les rapports
elseif (strpos($page_actuelle, 'rapports') === 0) {
    require_once CONTROLLERS_PATH . '/RapportsController.php';

    switch ($page_actuelle) {
        case 'rapports/academique':
            afficher_rapports_academiques();
            break;
        case 'rapports/financier':
            afficher_rapports_financiers();
            break;
        case 'rapports/personnel':
            afficher_rapports_personnel();
            break;
        default:
            load_page($page_actuelle);
            break;
    }
    exit;
}

// Contrôleurs pour l'administration
elseif (strpos($page_actuelle, 'administration') === 0) {
    require_once CONTROLLERS_PATH . '/AdminController.php';

    switch ($page_actuelle) {
        case 'administration/utilisateurs':
            admin_utilisateurs();
            break;
        case 'administration/parametres':
            admin_parametres();
            break;
        case 'administration/annee-scolaire':
            admin_anneeScolaire();
            break;
        case 'administration/logs':
            admin_logs();
            break;
        case 'administration/backup':
            admin_backup();
            break;
        case 'administration/restore':
            admin_restore();
            break;
        default:
            load_page($page_actuelle);
            break;
    }
    exit;
}

// =============================================
// CHARGEMENT DES TEMPLATES ET PAGES STANDARD
// =============================================

// Pages d'authentification
if (is_auth_page($page_actuelle)) {
    require_once VIEWS_PATH . '/templates/auth_template.php';
    exit;
}

// Pages d'erreur
if (is_error_page($page_actuelle)) {
    require_once VIEWS_PATH . '/templates/error_template.php';
    exit;
}

// Pages normales avec template complet
require_once VIEWS_PATH . '/templates/header.php';

// Chargement de la page demandée
if (!load_page($page_actuelle)) {
    // Page non trouvée
    load_error_page('404');
}

require_once VIEWS_PATH . '/templates/footer.php';