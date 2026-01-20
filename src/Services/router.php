<?php
/**
 * Routeur principal de l'application LaVision
 * Gère le dispatching des requêtes vers les contrôleurs appropriés
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Config/config.php';

// =============================================
// CONSTANTES DE ROUTAGE
// =============================================

/**
 * Pages publiques accessibles sans authentification
 */
define('PAGES_PUBLIQUES', [
    'login',
    'register',
    'forgot_password',
    'reset_password',
    'logout'
]);

/**
 * Pages d'authentification utilisant un template spécial
 */
define('PAGES_AUTH', [
    'login',
    'register',
    'forgot_password',
    'reset_password',
    'logout'
]);

/**
 * Pages d'erreur utilisant un template spécial
 */
define('PAGES_ERREUR', [
    '404',
    '403',
    '500'
]);

// =============================================
// MAPPING DES ROUTES
// =============================================

/**
 * Tableau associatif des routes disponibles
 * Format: 'route' => 'chemin/vers/le/fichier'
 * Les chemins sont relatifs au dossier Views/
 */
$routes = [
    // Dashboard
    'dashboard' => 'dashboard/index.php',

    // Gestion des élèves
    'eleves' => 'eleves/liste.php',
    'eleves/ajouter' => 'eleves/ajouter.php',
    'eleves/modifier' => 'eleves/modifier.php',
    'eleves/admission' => 'eleves/admission.php',
    'eleves/parents' => 'eleves/parents.php',
    'eleves/detail' => 'eleves/detail.php',

    // Gestion académique
    'academique/options' => 'academique/options.php',
    'academique/classes' => 'academique/classes.php',
    'academique/matieres' => 'academique/matieres.php',
    'academique/horaires' => 'academique/horaires.php',

    // Gestion du personnel
    'personnel/professeurs' => 'personnel/professeurs.php',
    'personnel/personnels' => 'personnel/personnels.php',

    // Cours et examens
    'cours/planification' => 'cours/planification.php',
    'cours/absences' => 'cours/absences.php',
    'cours/examens' => 'cours/examens.php',
    'cours/notes' => 'cours/notes.php',

    // Finance
    'finance/frais-scolaires' => 'finance/frais-scolaires.php',
    'finance/paiements' => 'finance/paiements.php',

    // Rapports
    'rapports/academique' => 'rapports/academique.php',
    'rapports/financier' => 'rapports/financier.php',
    'rapports/personnel' => 'rapports/personnel.php',

    // Administration
    'administration/utilisateurs' => 'administration/utilisateurs/liste.php',
    'administration/utilisateurs/ajout' => 'administration/utilisateurs/ajout.php',
    'administration/utilisateurs/modifier' => 'administration/utilisateurs/modifier.php',
    'administration/utilisateurs/detail' => 'administration/utilisateurs/detail.php',
    'administration/annee-scolaire' => 'administration/annee-scolaire.php',
    'administration/parametres' => 'administration/parametres.php',
    'administration/backup' => 'administration/backup.php',

    // Documentation
    'documentation/getting-started' => 'documentation/getting-started.php',
    'documentation/faq' => 'documentation/faq.php',

    // Pages d'erreur
    '404' => 'errors/404.php',
    '403' => 'errors/403.php',
    '500' => 'errors/500.php'
];

// =============================================
// FONCTIONS DE ROUTAGE
// =============================================

/**
 * Obtient la page actuelle depuis les paramètres GET
 * Nettoie et valide la valeur pour éviter les attaques
 *
 * @return string Nom de la page actuelle
 */
function get_current_page(): string
{
    // Récupération du paramètre page
    $page = $_GET['page'] ?? 'dashboard';

    // Nettoyage de base
    $page = trim($page, '/');
    $page = filter_var($page, FILTER_SANITIZE_STRING);

    // Validation supplémentaire
    if (!is_valid_page_name($page)) {
        return '404';
    }

    return $page;
}

/**
 * Valide le nom d'une page
 * Vérifie que le nom respecte les règles de sécurité
 *
 * @param string $page Nom de la page à valider
 * @return bool True si valide
 */
function is_valid_page_name(string $page): bool
{
    // Le nom ne doit contenir que des lettres, chiffres, tirets et slashes
    return preg_match('/^[a-zA-Z0-9\-\/]+$/', $page) === 1;
}

/**
 * Charge et affiche la page demandée
 * Gère les erreurs si la page n'existe pas
 *
 * @param string $page Nom de la page à charger
 * @return bool True si la page a été chargée, false sinon
 */
function load_page(string $page): bool
{
    global $routes;

    // Vérifier si la route existe
    if (!isset($routes[$page])) {
        // Page non trouvée, charger la page 404
        load_error_page('404');
        return false;
    }

    $file_path = VIEWS_PATH . '/' . $routes[$page];

    // Vérifier si le fichier existe
    if (!file_exists($file_path)) {
        logError('Fichier vue manquant', [
            'page' => $page,
            'path' => $file_path
        ]);

        // Charger la page 500 en cas d'erreur
        load_error_page('500');
        return false;
    }

    try {
        // Journaliser l'accès à la page
        logAction('Page chargée', 'Chargement de la page',['page' => $page]);

        // Inclure le fichier de la vue
        require_once $file_path;
        return true;

    } catch (Exception $e) {
        logError('Erreur chargement page', [
            'page' => $page,
            'error' => $e->getMessage()
        ]);

        load_error_page('500');
        return false;
    }
}

/**
 * Charge une page d'erreur spécifique
 *
 * @param string $error_code Code d'erreur (404, 403, 500)
 */
function load_error_page(string $error_code): void
{
    $error_file = VIEWS_PATH . '/errors/' . $error_code . '.php';

    if (file_exists($error_file)) {
        require_once $error_file;
    } else {
        // Page d'erreur par défaut si le fichier n'existe pas
        echo "<h1>Erreur $error_code</h1><p>Une erreur inattendue s'est produite.</p>";
    }
}

/**
 * Vérifie si une page nécessite une authentification
 *
 * @param string $page Nom de la page
 * @return bool True si authentification requise
 */
function page_requires_auth(string $page): bool
{
    return !in_array($page, PAGES_PUBLIQUES);
}

/**
 * Vérifie si une page utilise le template d'authentification
 *
 * @param string $page Nom de la page
 * @return bool True si template auth
 */
function is_auth_page(string $page): bool
{
    return in_array($page, PAGES_AUTH);
}

/**
 * Vérifie si une page est une page d'erreur
 *
 * @param string $page Nom de la page
 * @return bool True si page d'erreur
 */
function is_error_page(string $page): bool
{
    return in_array($page, PAGES_ERREUR);
}

/**
 * Redirige vers une page spécifique
 * Utilise les headers HTTP pour une redirection propre
 *
 * @param string $page Nom de la page de destination
 * @param array $params Paramètres GET optionnels
 */
function redirect(string $page, array $params = []): void
{
    $url = BASE_URL;

    if (!empty($page)) {
        $url .= '?page=' . urlencode($page);
    }

    if (!empty($params)) {
        $query_string = http_build_query($params);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $query_string;
    }

    header("Location: $url");
    exit;
}

/**
 * Génère une URL pour une page spécifique
 * Utile pour créer des liens dans les vues
 *
 * @param string $page Nom de la page
 * @param array $params Paramètres GET optionnels
 * @return string URL complète
 */
function url(string $page = '', array $params = []): string
{
    $url = BASE_URL;

    if (!empty($page)) {
        $url .= '?page=' . urlencode($page);
    }

    if (!empty($params)) {
        $query_string = http_build_query($params);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $query_string;
    }

    return $url;
}

/**
 * Obtient la liste de toutes les routes disponibles
 *
 * @return array Liste des routes
 */
function get_available_routes(): array
{
    global $routes;
    return array_keys($routes);
}

/**
 * Vérifie si une route existe
 *
 * @param string $route Nom de la route
 * @return bool True si la route existe
 */
function route_exists(string $route): bool
{
    global $routes;
    return isset($routes[$route]);
}

?>