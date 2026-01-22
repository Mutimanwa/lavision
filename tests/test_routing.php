<?php
/**
 * Script de test pour déboguer les problèmes de routage
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test...\n";

// Simuler les variables $_SERVER pour une requête vers la page de login
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/lavision/app/public/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/lavision/app/public/?page=auth/login';
$_SERVER['QUERY_STRING'] = 'page=auth/login';
$_GET['page'] = 'auth/login';

echo "Including config.php...\n";
require_once __DIR__ . '/../src/Config/config.php';
echo "Config.php loaded\n";

echo "Including database.php...\n";
require_once SERVICES_PATH . '/database.php';
echo "Database.php loaded\n";

echo "Including router.php...\n";
require_once SERVICES_PATH . '/router.php';
echo "Router.php loaded\n";

echo "Including auth.php...\n";
require_once SERVICES_PATH . '/auth.php';
echo "Auth.php loaded\n";

echo "Including functions.php...\n";
require_once SERVICES_PATH . '/functions.php';
echo "Functions.php loaded\n";

echo "Including validation.php...\n";
require_once SERVICES_PATH . '/validation.php';
echo "Validation.php loaded\n";

// Simuler la suite de index.php
echo "Setting up session and headers...\n";

// Démarrage de la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définition du fuseau horaire
date_default_timezone_set('Africa/Kinshasa');

// Protection contre les attaques XSS et injection
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Protection CSRF - génération du token si nécessaire
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "Session and headers set up\n";

// Routage principal
echo "Starting routing logic...\n";
$page_actuelle = $_GET['page'] ?? 'dashboard';

echo "Page requested: $page_actuelle\n";

if ($page_actuelle === 'auth/login') {
    echo "Calling auth_login()...\n";
    require_once CONTROLLERS_PATH . '/AuthController.php';
    auth_login();
    echo "auth_login() completed\n";
} else {
    echo "Page not handled in test: $page_actuelle\n";
}

echo "Test completed successfully\n";
?>