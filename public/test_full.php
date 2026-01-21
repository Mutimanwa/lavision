<?php
echo "Testing full index.php loading...\n";

// Simuler les variables $_SERVER
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/lavision/app/public/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/lavision/app/public/?page=login';
$_SERVER['QUERY_STRING'] = 'page=login';
$_GET['page'] = 'login';

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définition du fuseau horaire
date_default_timezone_set('Africa/Kinshaca');

// Chargement de la configuration principale
echo "Loading config.php...\n";
require_once __DIR__ . '/../src/Config/config.php';

// Chargement des services de base
echo "Loading database.php...\n";
require_once SERVICES_PATH . '/database.php';
echo "Loading router.php...\n";
require_once SERVICES_PATH . '/router.php';
echo "Loading auth.php...\n";
require_once SERVICES_PATH . '/auth.php';
echo "Loading functions.php...\n";
require_once SERVICES_PATH . '/functions.php';
echo "Loading validation.php...\n";
require_once SERVICES_PATH . '/validation.php';

// Headers de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Protection CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "All includes loaded, testing get_current_page()...\n";
$page_actuelle = get_current_page();
echo "Current page: $page_actuelle\n";

echo "Testing est_connecte()...\n";
$connected = est_connecte();
echo "Connected: " . ($connected ? 'true' : 'false') . "\n";

echo "Test completed successfully\n";
?>