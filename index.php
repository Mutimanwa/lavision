<?php
/**
 * Point d'entrée principal de l'application
 * LaVision - Système de gestion scolaire
 *
 * Ce fichier initialise l'application et route les requêtes
 * vers les contrôleurs appropriés.
 */

// Démarrer le chronomètre de performance
$start_time = microtime(true);

// Inclure le fichier d'autochargement
require_once __DIR__ . '/src/Config/autoload.php';

// Vérifier que l'autochargement s'est bien passé
if (!defined('AUTOLOAD_COMPLETE')) {
    die('Erreur critique: Échec de l\'initialisation de l\'application');
}

// Fonction principale de routage
function handleRequest() {
    try {
        // Obtenir l'URI et la méthode HTTP
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        $request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Nettoyer l'URI
        $uri = parse_url($request_uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        // Supprimer le préfixe BASE_URL de l'URI si présent
        $base_path = parse_url(BASE_URL, PHP_URL_PATH);
        if ($base_path && $base_path !== '/' && strpos($uri, $base_path) === 0) {
            $uri = substr($uri, strlen($base_path));
            $uri = $uri ?: '/';
        }

        // Diviser l'URI en segments
        $uri_segments = explode('/', trim($uri, '/'));
        $module = $uri_segments[0] ?? 'dashboard';
        $action = $uri_segments[1] ?? 'index';

        // Gestion spéciale pour la racine
        if ($uri === '/') {
            $module = 'dashboard';
            $action = 'index';
        }

        // Obtenir la configuration des routes
        $routes_config = require SRC_PATH . '/Config/routes.php';
        $routes = $routes_config['routes'] ?? [];
        $default_routes = $routes_config['default_routes'] ?? [];

        // Trouver la route correspondante
        $route = null;
        $route_key = null;

        if (isset($routes[$module][$action])) {
            $route = $routes[$module][$action];
            $route_key = "$module/$action";
        } elseif (isset($default_routes[$module])) {
            $route = $default_routes[$module];
            $route_key = $module;
        } else {
            // Route 404
            $route = $default_routes['404'] ?? [
                'controller' => 'ErrorController',
                'method' => 'notFound',
                'auth_required' => false
            ];
            $route_key = '404';
        }

        // Vérifier l'authentification si requise
        if ($route['auth_required'] ?? true) {
            if (!isset($_SESSION['user_id'])) {
                // Rediriger vers la page de connexion
                header('Location: ' . BASE_URL . '/auth/login?redirect=' . urlencode($request_uri));
                exit;
            }
        }

        // Vérifier les permissions si définies
        if (isset($route['permissions']) && !empty($route['permissions'])) {
            $user_permissions = $_SESSION['user_permissions'] ?? [];
            $a_permission = false;

            foreach ($route['permissions'] as $permission) {
                if (in_array($permission, $user_permissions)) {
                    $a_permission = true;
                    break;
                }
            }

            if (!$a_permission) {
                // Erreur 403 - Accès refusé
                $route = $default_routes['403'] ?? [
                    'controller' => 'ErrorController',
                    'method' => 'forbidden',
                    'auth_required' => false
                ];
            }
        }

        // Instancier le contrôleur
        $controller_name = $route['controller'];
        $method_name = $route['method'];

        if (!class_exists($controller_name)) {
            throw new Exception("Contrôleur '$controller_name' non trouvé");
        }

        $controller = new $controller_name();

        if (!method_exists($controller, $method_name)) {
            throw new Exception("Méthode '$method_name' non trouvée dans le contrôleur '$controller_name'");
        }

        // Journaliser l'accès
        logAccess($route_key, $request_method, $_SESSION['user_id'] ?? null);

        // Appeler la méthode du contrôleur
        $response = $controller->$method_name();

        // Si la méthode retourne une réponse, l'afficher
        if ($response !== null) {
            echo $response;
        }

    } catch (Exception $e) {
        // Logger l'erreur
        logError("Erreur dans handleRequest(): " . $e->getMessage(), ['type' => 'ROUTING_ERROR']);

        // Afficher une erreur 500
        http_response_code(500);

        if (DEBUG_MODE ?? false) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<h2>Erreur de routage</h2>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>Fichier:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
            echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;'>";
            echo "<h2>Une erreur s'est produite</h2>";
            echo "<p>Veuillez contacter l'administrateur système.</p>";
            echo "<p><a href='" . BASE_URL . "'>Retour à l'accueil</a></p>";
            echo "</div>";
        }
    }
}

// Fonction pour journaliser les accès
function logAccess($route, $method, $user_id = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $log_entry = sprintf(
        "[%s] %s - Route: %s, Method: %s, User: %s, IP: %s\n",
        date('Y-m-d H:i:s'),
        session_id(),
        $route,
        $method,
        $user_id ?? 'guest',
        $ip
    );

    // Écrire dans le fichier de log des accès
    $log_file = LOGS_PATH . '/access/' . date('Y-m-d') . '.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Traiter la requête
handleRequest();

// Calculer et logger le temps de réponse
$end_time = microtime(true);
$response_time = round(($end_time - $start_time) * 1000, 2); // en millisecondes

if (DEBUG_MODE ?? false) {
    echo "<!-- Temps de réponse: {$response_time}ms -->";
}

// Logger les performances lentes
if ($response_time > 1000) { // Plus d'1 seconde
    logError("Temps de réponse lent: {$response_time}ms pour " . ($_SERVER['REQUEST_URI'] ?? 'unknown'), ['type' => 'PERFORMANCE']);
}