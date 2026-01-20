<?php
// Configuration
require_once  __DIR__ . "/../includes/config/config.php";

// Fonctions core
require_once INCLUDES_PATH . '/core/database.php';
require_once INCLUDES_PATH . '/core/auth.php';
require_once INCLUDES_PATH . '/core/functions.php';

// Vérifier l'authentification
$current_page = $_GET['page'] ?? 'dashboard';
$public_pages = ['login', 'register', 'forgot_password', 'reset_password', 'logout','404','403','500'];

if (!in_array($current_page, $public_pages) && !is_logged_in()) {
    redirect('login');
}

// Définir les pages d'authentification
$auth_pages = ['login', 'register', 'forgot_password', 'reset_password','logout'];
$is_auth_page = in_array($current_page, $auth_pages);

// Les pages d'erreur 
$error_pages = ['404', '403','500'];
$is_error_page = in_array($current_page, $error_pages);


if ($is_auth_page) {
    // Utiliser le template d'authentification (sans header/footer normal)
    require_once INCLUDES_PATH . '/templates/auth_template.php';
}else if($is_error_page){
  require_once INCLUDES_PATH . '/templates/error_template.php'; 
} else {
    // Utiliser le template normal avec header/footer
    require_once INCLUDES_PATH . '/templates/header.php';
    
    // Router la requête
    require_once INCLUDES_PATH . '/core/router.php';
    load_page(get_current_page());
    
    require_once INCLUDES_PATH . '/templates/footer.php';
}