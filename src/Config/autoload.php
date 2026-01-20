<?php
/**
 * Autochargement des classes
 * LaVision - Système de gestion scolaire
 *
 * Ce fichier configure l'autochargement des classes PHP
 * selon la structure modulaire de l'application.
 */

// Fonction d'autochargement personnalisée
spl_autoload_register(function ($class_name) {
    // Définir les mappings des namespaces vers les répertoires
    $mappings = [
        'Controller' => SRC_PATH . '/Controllers/',
        'Model' => SRC_PATH . '/Models/',
        'Service' => SRC_PATH . '/Services/',
        'Middleware' => SRC_PATH . '/Middleware/',
        'Config' => SRC_PATH . '/Config/',
        'Route' => SRC_PATH . '/Routes/',
        'View' => SRC_PATH . '/Views/',
    ];

    // Essayer chaque mapping
    foreach ($mappings as $namespace => $directory) {
        if (strpos($class_name, $namespace) === 0) {
            // Supprimer le namespace du nom de classe
            $relative_class = substr($class_name, strlen($namespace) + 1);

            // Convertir les namespaces en chemins de fichiers
            $file_path = $directory . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file_path)) {
                require_once $file_path;
                return;
            }
        }
    }

    // Autochargement basé sur le nom de classe (pour les classes sans namespace)
    $class_file = SRC_PATH . '/Controllers/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
        return;
    }

    $class_file = SRC_PATH . '/Models/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
        return;
    }

    $class_file = SRC_PATH . '/Services/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
        return;
    }

    // Si la classe n'est pas trouvée, logger l'erreur
    logError("Classe non trouvée: $class_name");
});

// Inclure les fichiers de configuration essentiels
$essential_files = [
    __DIR__ . '/config.php',
    __DIR__ . '/../Services/database.php',
    __DIR__ . '/../Services/router.php',
    __DIR__ . '/../Services/functions.php',
    __DIR__ . '/../Services/auth.php',
    __DIR__ . '/routes.php',
    __DIR__ . '/../Controllers/BaseController.php',
    __DIR__ . '/../Models/BaseModel.php',
];

// Charger les fichiers essentiels
foreach ($essential_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Erreur critique si un fichier essentiel est manquant
        error_log("Fichier essentiel manquant: $file");
        die("Erreur critique: Fichier essentiel manquant - $file");
    }
}

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurer le fuseau horaire
date_default_timezone_set(TIMEZONE ?? 'Europe/Paris');

// Configurer les erreurs selon l'environnement
if (DEBUG_MODE ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Charger les fonctions utilitaires essentielles
require_once SRC_PATH . '/Services/functions.php';

// Configurer la gestion d'erreurs personnalisée
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Ne pas traiter les erreurs supprimées par @
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $error_message = "Erreur PHP [$errno]: $errstr in $errfile on line $errline";

    // Logger l'erreur
    logError($error_message);

    // En mode debug, afficher l'erreur
    if (DEBUG_MODE ?? false) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<strong>Erreur PHP:</strong> $error_message";
        echo "</div>";
    }

    // Continuer l'exécution pour les erreurs non fatales
    return true;
});

// Configurer la gestion des exceptions non capturées
set_exception_handler(function ($exception) {
    $error_message = "Exception non capturée: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();

    // Logger l'exception
    logError($error_message, ['type' => 'EXCEPTION', 'file' => $exception->getFile(), 'line' => $exception->getLine()]);

    // Afficher une page d'erreur
    if (!headers_sent()) {
        http_response_code(500);
    }

    if (DEBUG_MODE ?? false) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h2>Exception non capturée</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>Fichier:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Ligne:</strong> " . $exception->getLine() . "</p>";
        echo "<h3>Trace:</h3>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;'>";
        echo "<h2>Une erreur s'est produite</h2>";
        echo "<p>Veuillez contacter l'administrateur système.</p>";
        echo "<p><a href='" . BASE_URL . "'>Retour à l'accueil</a></p>";
        echo "</div>";
    }

    exit;
});

// Configurer la gestion des erreurs fatales
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = "Erreur fatale: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'];

        // Logger l'erreur fatale
        logError($error_message, ['type' => 'FATAL', 'file' => $error['file'], 'line' => $error['line']]);

        // Afficher une page d'erreur
        if (!headers_sent()) {
            http_response_code(500);
        }

        if (DEBUG_MODE ?? false) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<h2>Erreur fatale</h2>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($error['message']) . "</p>";
            echo "<p><strong>Fichier:</strong> " . htmlspecialchars($error['file']) . "</p>";
            echo "<p><strong>Ligne:</strong> " . $error['line'] . "</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;'>";
            echo "<h2>Une erreur critique s'est produite</h2>";
            echo "<p>Veuillez contacter l'administrateur système.</p>";
            echo "<p><a href='" . BASE_URL . "'>Retour à l'accueil</a></p>";
            echo "</div>";
        }
    }
});

// Fonction pour vérifier les prérequis système
function checkSystemRequirements() {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'MBString Extension' => extension_loaded('mbstring'),
        'JSON Extension' => extension_loaded('json'),
        'Session Extension' => extension_loaded('session'),
        'File Upload Support' => ini_get('file_uploads'),
    ];

    $failed_requirements = [];
    foreach ($requirements as $requirement => $met) {
        if (!$met) {
            $failed_requirements[] = $requirement;
        }
    }

    if (!empty($failed_requirements)) {
        die("Prérequis système non satisfaits: " . implode(', ', $failed_requirements));
    }
}

// Vérifier les prérequis en mode debug
if (DEBUG_MODE ?? false) {
    checkSystemRequirements();
}

// Initialiser les constantes de sécurité CSRF si activé
if (CSRF_PROTECTION ?? true) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Nettoyer les anciennes sessions (optionnel)
if (mt_rand(1, 100) === 1) { // 1% de chance à chaque chargement
    $session_files = glob(session_save_path() . '/sess_*');
    $one_week_ago = time() - (7 * 24 * 60 * 60);

    foreach ($session_files as $file) {
        if (filemtime($file) < $one_week_ago) {
            @unlink($file);
        }
    }
}

// Autochargement terminé
define('AUTOLOAD_COMPLETE', true);