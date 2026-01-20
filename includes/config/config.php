<?php
/**
 * Fichier de configuration du système de gestion scolaire
 * Sécurité: Ne pas modifier directement en production
 * Version: 1.0.0
 */

// =============================================
// CONFIGURATION DE SÉCURITÉ
// =============================================

// Mode de débogage (à désactiver en production)
define('DEBUG_MODE', true);

// Clé de sécurité pour les sessions et chiffrement
define('SECURITY_KEY', 'votre_cle_secrete_unique_et_longue_ici');

// Durée de session en secondes (8 heures)
define('SESSION_LIFETIME', 28800);

// Limite de tentatives de connexion
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// =============================================
// CONFIGURATION DE LA BASE DE DONNÉES
// =============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_academique_ecole');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// =============================================
// CONFIGURATION DE L'APPLICATION
// =============================================

// Informations de l'école
define('ECOLE_NOM', "École Secondaire d'Excellence");
define('ECOLE_ADRESSE', '123 Avenue de l\'Éducation, Kinshasa');
define('ECOLE_TELEPHONE', '+243 81 234 5678');
define('ECOLE_EMAIL', 'contact@ecole-excellence.cd');
define('ECOLE_DEVISE', 'Savoir, Excellence, Discipline');

// Chemins du système
define('ROOT_PATH', dirname(__DIR__, 2));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('BACKUP_PATH', ROOT_PATH . '/backups');
define('LOG_PATH', ROOT_PATH . '/logs');

// URL de base (à ajuster selon l'hébergement)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host . '/lavision/app/public/');

define("ASSETS_URL", BASE_URL . "assets/");
define("CSS_URL", ASSETS_URL . "css/"); 
define("JS_URL", ASSETS_URL ."js/");
define("IMAGES_URL", ASSETS_URL . "img/");
define("LIBS_URL", ASSETS_URL . "libs/");


// =============================================
// CONFIGURATION DES UPLOADS
// =============================================

// Types de fichiers autorisés
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Dossiers d'upload
define('UPLOAD_ELEVES', UPLOADS_PATH . '/eleves/');
define('UPLOAD_PROFESSEURS', UPLOADS_PATH . '/professeurs/');
define('UPLOAD_DOCUMENTS', UPLOADS_PATH . '/documents/');

// =============================================
// CONFIGURATION ACADÉMIQUE
// =============================================

// Seuil de réussite (note minimale)
define('SEUIL_REUSSITE', 10.0);

// Coefficients par type d'évaluation
define('COEF_DEVOIR', 0.3);
define('COEF_EXAMEN', 0.5);
define('COEF_PROJET', 0.2);

// Limites d'absences
define('MAX_ABSENCES_NON_JUSTIFIEES', 10);

// =============================================
// CONFIGURATION FINANCIÈRE
// =============================================

// Devise
define('DEVISE', 'CDF');
define('SYMBOLE_DEVISE', 'FC');

// Délais de paiement (en jours)
define('DELAI_PAIEMENT_INSCRIPTION', 7);
define('DELAI_PAIEMENT_SCOLARITE', 15);

// Taux de pénalité pour retard (% par mois)
define('TAUX_PENALITE', 5);

// =============================================
// CONFIGURATION DE SAUVEGARDE
// =============================================

define('BACKUP_AUTO', true);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
define('BACKUP_RETENTION_DAYS', 30);

// =============================================
// FONCTIONS D'INITIALISATION
// =============================================

/**
 * Initialise l'environnement de l'application
 */
function init_environment(): void
{
    ob_start();
    // Gestion des erreurs selon le mode
    if (DEBUG_MODE) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', LOG_PATH . '/php_errors.log');
    }
    
    // Configuration de la zone horaire
    date_default_timezone_set('Africa/Kinshasa');
    
    // Configuration de la session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    
    // Démarrer la session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Créer les dossiers nécessaires
    // create_required_directories();
}

/**
 * Crée les répertoires nécessaires au système
 */
function create_required_directories(): void
{
    $directories = [
        UPLOADS_PATH,
        UPLOAD_ELEVES,
        UPLOAD_PROFESSEURS,
        UPLOAD_DOCUMENTS,
        BACKUP_PATH,
        LOG_PATH,
        LOG_PATH . '/actions',
        LOG_PATH . '/errors'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Créer les fichiers .htaccess de protection
    $htaccess_content = "Deny from all\n";
    foreach ([BACKUP_PATH, LOG_PATH] as $protected_dir) {
        file_put_contents($protected_dir . '/.htaccess', $htaccess_content);
    }
}

/**
 * Charge automatiquement les classes nécessaires
 */
function autoload_classes(string $class_name): void
{
    $class_file = INCLUDES_PATH . '/classes/' . str_replace('\\', '/', $class_name) . '.php';
    
    if (file_exists($class_file)) {
        require_once $class_file;
    }
}

spl_autoload_register('autoload_classes');

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Échapper les données pour l'affichage HTML
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Rediriger vers une URL ou une page interne
 * 
 * @param string $destination URL complète ou nom de page interne
 * @param array|int $params Paramètres pour le routing OU code de statut HTTP
 * @param int|null $status_code Code de statut HTTP (optionnel)
 */
function redirect(string $destination, $params = [], ?int $status_code = null): void
{
    // Si c'est déjà une URL complète (commence par http:// ou https://)
    if (strpos($destination, 'http://') === 0 || strpos($destination, 'https://') === 0) {
        $url = $destination;
        $code = is_int($params) ? $params : ($status_code ?? 302);
    }
    // Si c'est une URL relative externe
    elseif (strpos($destination, '/') === 0) {
        $url = BASE_URL . ltrim($destination, '/');
        $code = is_int($params) ? $params : ($status_code ?? 302);
    }
    // Sinon, c'est une page interne pour le routing
    else {
        $url = "index.php?page=" . urlencode($destination);
        if (is_array($params) && !empty($params)) {
            $url .= "&" . http_build_query($params);
        }
        $code = $status_code ?? 302;
    }
    
    header("Location: $url", true, $code);
    exit();
}

/**
 * Générer un token CSRF
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Génère un champ CSRF caché
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}


/**
 * Vérifier un token CSRF
 */
function verify_csrf_token(string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valider une adresse email
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Formater une date
 */
function format_date(string $date, string $format = 'd/m/Y'): string
{
    $datetime = DateTime::createFromFormat('Y-m-d', $date);
    return $datetime ? $datetime->format($format) : $date;
}

/**
 * Formater une date en "il y a X temps"
 */
function time_ago(string $datetime): string
{
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'il y a ' . $diff . ' secondes';
    }elseif ($diff < 3600) {
        return 'il y a ' . floor($diff / 60) . ' minutes';
    } elseif ($diff < 86400) {
        return 'il y a ' . floor($diff / 3600) . ' heures';
    } elseif ($diff < 604800) {
        return 'il y a ' . floor($diff / 86400) . ' jours';
    } elseif ($diff < 2592000) {
        return 'il y a ' . floor($diff / 604800) . ' semaines';
    } elseif ($diff < 31536000) {
        return 'il y a ' . floor($diff / 2592000) . ' mois';
    } else {
        return 'il y a ' . floor($diff / 31536000) . ' ans';
    }
}


/**
 * Journaliser une action
 */
function log_action(string $action, array $details = [], string $categorie = 'system'): void
{
    $log_entry = sprintf(
        "[%s] %s: %s %s\n",
        date('Y-m-d H:i:s'),
        $categorie,
        $action,
        json_encode($details, JSON_UNESCAPED_UNICODE)
    );
    
    $log_file = LOG_PATH . '/actions/' . date('Y-m-d') . '.log';
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// =============================================
// INITIALISATION
// =============================================

// Initialiser l'environnement
init_environment();

// Vérifier les prérequis
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('PHP 7.4.0 ou supérieur est requis.');
}

if (!extension_loaded('pdo_mysql')) {
    die('L\'extension PDO MySQL est requise.');
}