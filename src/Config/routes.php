<?php
/**
 * Configuration des routes
 * LaVision - Système de gestion scolaire
 *
 * Ce fichier définit toutes les routes de l'application
 * avec leurs contrôleurs et méthodes associés.
 */

// Définition des routes
$routes = [
    // Routes d'authentification
    'auth' => [
        'login' => ['controller' => 'AuthController', 'method' => 'login', 'auth_required' => false],
        'logout' => ['controller' => 'AuthController', 'method' => 'logout', 'auth_required' => true],
        'forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword', 'auth_required' => false],
        'reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword', 'auth_required' => false],
        'lock-screen' => ['controller' => 'AuthController', 'method' => 'lockScreen', 'auth_required' => true]
    ],

    // Routes du tableau de bord
    'dashboard' => [
        'index' => ['controller' => 'DashboardController', 'method' => 'index', 'auth_required' => true, 'permissions' => []]
    ],

    // Routes des élèves
    'eleves' => [
        'liste' => ['controller' => 'EleveController', 'method' => 'liste', 'auth_required' => true, 'permissions' => ['eleves.view']],
        'detail' => ['controller' => 'EleveController', 'method' => 'detail', 'auth_required' => true, 'permissions' => ['eleves.view']],
        'ajouter' => ['controller' => 'EleveController', 'method' => 'ajouter', 'auth_required' => true, 'permissions' => ['eleves.create']],
        'modifier' => ['controller' => 'EleveController', 'method' => 'modifier', 'auth_required' => true, 'permissions' => ['eleves.edit']],
        'supprimer' => ['controller' => 'EleveController', 'method' => 'supprimer', 'auth_required' => true, 'permissions' => ['eleves.delete']],
        'admission' => ['controller' => 'EleveController', 'method' => 'admission', 'auth_required' => true, 'permissions' => ['eleves.create']],
        'parents' => ['controller' => 'EleveController', 'method' => 'parents', 'auth_required' => true, 'permissions' => ['eleves.view']]
    ],

    // Routes académiques
    'academique' => [
        'classes' => ['controller' => 'AcademiqueController', 'method' => 'classes', 'auth_required' => true, 'permissions' => ['academique.view']],
        'matieres' => ['controller' => 'AcademiqueController', 'method' => 'matieres', 'auth_required' => true, 'permissions' => ['academique.view']],
        'horaires' => ['controller' => 'AcademiqueController', 'method' => 'horaires', 'auth_required' => true, 'permissions' => ['academique.view']],
        'options' => ['controller' => 'AcademiqueController', 'method' => 'options', 'auth_required' => true, 'permissions' => ['academique.manage']],
        'ajouter-classe' => ['controller' => 'AcademiqueController', 'method' => 'ajouterClasse', 'auth_required' => true, 'permissions' => ['academique.create']],
        'modifier-classe' => ['controller' => 'AcademiqueController', 'method' => 'modifierClasse', 'auth_required' => true, 'permissions' => ['academique.edit']],
        'supprimer-classe' => ['controller' => 'AcademiqueController', 'method' => 'supprimerClasse', 'auth_required' => true, 'permissions' => ['academique.delete']],
        'ajouter-matiere' => ['controller' => 'AcademiqueController', 'method' => 'ajouterMatiere', 'auth_required' => true, 'permissions' => ['academique.create']],
        'modifier-matiere' => ['controller' => 'AcademiqueController', 'method' => 'modifierMatiere', 'auth_required' => true, 'permissions' => ['academique.edit']],
        'supprimer-matiere' => ['controller' => 'AcademiqueController', 'method' => 'supprimerMatiere', 'auth_required' => true, 'permissions' => ['academique.delete']],
        'ajouter-horaire' => ['controller' => 'AcademiqueController', 'method' => 'ajouterHoraire', 'auth_required' => true, 'permissions' => ['academique.create']],
        'modifier-horaire' => ['controller' => 'AcademiqueController', 'method' => 'modifierHoraire', 'auth_required' => true, 'permissions' => ['academique.edit']],
        'supprimer-horaire' => ['controller' => 'AcademiqueController', 'method' => 'supprimerHoraire', 'auth_required' => true, 'permissions' => ['academique.delete']]
    ],

    // Routes du personnel
    'personnel' => [
        'professeurs' => ['controller' => 'PersonnelController', 'method' => 'professeurs', 'auth_required' => true, 'permissions' => ['personnel.view']],
        'ajouter-professeur' => ['controller' => 'PersonnelController', 'method' => 'ajouterProfesseur', 'auth_required' => true, 'permissions' => ['personnel.create']],
        'modifier-professeur' => ['controller' => 'PersonnelController', 'method' => 'modifierProfesseur', 'auth_required' => true, 'permissions' => ['personnel.edit']],
        'supprimer-professeur' => ['controller' => 'PersonnelController', 'method' => 'supprimerProfesseur', 'auth_required' => true, 'permissions' => ['personnel.delete']],
        'detail-professeur' => ['controller' => 'PersonnelController', 'method' => 'detailProfesseur', 'auth_required' => true, 'permissions' => ['personnel.view']]
    ],

    // Routes financières
    'finance' => [
        'paiements' => ['controller' => 'FinanceController', 'method' => 'paiements', 'auth_required' => true, 'permissions' => ['finance.view']],
        'ajouter-paiement' => ['controller' => 'FinanceController', 'method' => 'ajouterPaiement', 'auth_required' => true, 'permissions' => ['finance.create']],
        'modifier-paiement' => ['controller' => 'FinanceController', 'method' => 'modifierPaiement', 'auth_required' => true, 'permissions' => ['finance.edit']],
        'supprimer-paiement' => ['controller' => 'FinanceController', 'method' => 'supprimerPaiement', 'auth_required' => true, 'permissions' => ['finance.delete']],
        'detail-paiement' => ['controller' => 'FinanceController', 'method' => 'detailPaiement', 'auth_required' => true, 'permissions' => ['finance.view']],
        'recus' => ['controller' => 'FinanceController', 'method' => 'recus', 'auth_required' => true, 'permissions' => ['finance.view']]
    ],

    // Routes des rapports
    'rapports' => [
        'tableau_bord' => ['controller' => 'RapportsController', 'method' => 'tableauBord', 'auth_required' => true, 'permissions' => ['rapports.view']],
        'general' => ['controller' => 'RapportsController', 'method' => 'rapportGeneral', 'auth_required' => true, 'permissions' => ['rapports.view']],
        'eleves' => ['controller' => 'RapportsController', 'method' => 'rapportEleves', 'auth_required' => true, 'permissions' => ['rapports.view']],
        'academique' => ['controller' => 'RapportsController', 'method' => 'rapportAcademique', 'auth_required' => true, 'permissions' => ['rapports.view']],
        'financier' => ['controller' => 'RapportsController', 'method' => 'rapportFinancier', 'auth_required' => true, 'permissions' => ['rapports.view']],
        'securite' => ['controller' => 'RapportsController', 'method' => 'rapportSecurite', 'auth_required' => true, 'permissions' => ['rapports.view']],
        'export' => ['controller' => 'RapportsController', 'method' => 'export', 'auth_required' => true, 'permissions' => ['rapports.export']]
    ],

    // Routes d'administration
    'administration' => [
        'utilisateurs' => ['controller' => 'AdminController', 'method' => 'utilisateurs', 'auth_required' => true, 'permissions' => ['admin.users']],
        'parametres' => ['controller' => 'AdminController', 'method' => 'parametres', 'auth_required' => true, 'permissions' => ['admin.settings']],
        'annee-scolaire' => ['controller' => 'AdminController', 'method' => 'anneeScolaire', 'auth_required' => true, 'permissions' => ['admin.settings']],
        'logs' => ['controller' => 'AdminController', 'method' => 'logs', 'auth_required' => true, 'permissions' => ['admin.logs']],
        'backup' => ['controller' => 'AdminController', 'method' => 'backup', 'auth_required' => true, 'permissions' => ['admin.backup']],
        'restore' => ['controller' => 'AdminController', 'method' => 'restore', 'auth_required' => true, 'permissions' => ['admin.backup']]
    ],

    // Routes utilisateur
    'utilisateur' => [
        'profile' => ['controller' => 'UserController', 'method' => 'profile', 'auth_required' => true, 'permissions' => []],
        'modifier-profile' => ['controller' => 'UserController', 'method' => 'modifierProfile', 'auth_required' => true, 'permissions' => []],
        'changer-mot-de-passe' => ['controller' => 'UserController', 'method' => 'changerMotDePasse', 'auth_required' => true, 'permissions' => []],
        'preferences' => ['controller' => 'UserController', 'method' => 'preferences', 'auth_required' => true, 'permissions' => []]
    ],

    // Routes API (pour les appels AJAX)
    'api' => [
        'eleves/search' => ['controller' => 'EleveApiController', 'method' => 'search', 'auth_required' => true, 'permissions' => ['eleves.view']],
        'academique/classes' => ['controller' => 'AcademiqueApiController', 'method' => 'classes', 'auth_required' => true, 'permissions' => ['academique.view']],
        'rapports/data' => ['controller' => 'RapportsApiController', 'method' => 'data', 'auth_required' => true, 'permissions' => ['rapports.view']],
        'upload' => ['controller' => 'UploadController', 'method' => 'upload', 'auth_required' => true, 'permissions' => ['upload.allowed']]
    ]
];

// Routes par défaut
$default_routes = [
    'home' => ['controller' => 'DashboardController', 'method' => 'index', 'auth_required' => true],
    '404' => ['controller' => 'ErrorController', 'method' => 'notFound', 'auth_required' => false],
    '403' => ['controller' => 'ErrorController', 'method' => 'forbidden', 'auth_required' => false],
    '500' => ['controller' => 'ErrorController', 'method' => 'serverError', 'auth_required' => false]
];

// Fonction pour obtenir une route
// function getRoute($module, $action = 'index') {
//     global $routes, $default_routes;

//     if (isset($routes[$module][$action])) {
//         return $routes[$module][$action];
//     }

//     // Retourner la route par défaut si elle existe
//     if (isset($default_routes[$module])) {
//         return $default_routes[$module];
//     }

//     // Retourner null si la route n'existe pas
//     return null;
// }

// Fonction pour vérifier si une route nécessite une authentification
// function routeRequiresAuth($module, $action = 'index') {
//     $route = getRoute($module, $action);
//     return $route ? ($route['auth_required'] ?? true) : true;
// }

// Fonction pour obtenir les permissions requises pour une route
// function getRoutePermissions($module, $action = 'index') {
//     $route = getRoute($module, $action);
//     return $route ? ($route['permissions'] ?? []) : [];
// }

// Fonction pour vérifier si l'utilisateur a accès à une route
// function hasRouteAccess($module, $action = 'index') {
//     $route = getRoute($module, $action);

//     if (!$route) {
//         return false;
//     }

//     // Vérifier l'authentification
//     if ($route['auth_required'] && !isset($_SESSION['user_id'])) {
//         return false;
//     }

//     // Vérifier les permissions
//     if (!empty($route['permissions'])) {
//         $user_permissions = $_SESSION['user_permissions'] ?? [];
//         foreach ($route['permissions'] as $permission) {
//             if (!in_array($permission, $user_permissions)) {
//                 return false;
//             }
//         }
//     }

//     return true;
// }

// Retourner la configuration des routes
return [
    'routes' => $routes,
    'default_routes' => $default_routes
];