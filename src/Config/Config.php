<?php
/**
 * Fichier de configuration principal de l'application LaVision
 * Ce fichier contient toutes les constantes et paramètres système
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

// =============================================
// CONFIGURATION DE SÉCURITÉ
// =============================================

/**
 * Mode de débogage - À désactiver en production pour des raisons de sécurité
 */
define('DEBUG_MODE', true);

/**
 * Clé de sécurité unique pour les sessions et le chiffrement
 * IMPORTANT: Changez cette valeur en production
 */
define('SECURITY_KEY', 'votre_cle_secrete_unique_et_longue_ici_2026');

/**
 * Durée de vie des sessions en secondes (8 heures)
 */
define('SESSION_LIFETIME', 28800);

/**
 * Nombre maximum de tentatives de connexion avant blocage
 */
define('MAX_LOGIN_ATTEMPTS', 5);

/**
 * Durée de blocage après tentatives échouées (15 minutes)
 */
define('LOGIN_LOCKOUT_TIME', 900);

/**
 * Activation de la protection CSRF
 */
define('CSRF_PROTECTION', true);

/**
 * Durée de validité des tokens CSRF (1 heure)
 */
define('CSRF_TOKEN_LIFETIME', 3600);

// =============================================
// CONFIGURATION DE LA BASE DE DONNÉES
// =============================================

/**
 * Paramètres de connexion à la base de données MySQL
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_academique_ecole');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// =============================================
// CONFIGURATION DE L'APPLICATION
// =============================================

/**
 * Informations générales de l'établissement scolaire
 */
define('ECOLE_NOM', "École Secondaire d'Excellence");
define('ECOLE_ADRESSE', '123 Avenue de l\'Éducation, Kinshasa');
define('ECOLE_TELEPHONE', '+243 81 234 5678');
define('ECOLE_EMAIL', 'contact@ecole-excellence.cd');
define('ECOLE_DEVISE', 'Savoir, Excellence, Discipline');
define("APP_VERSION", "1.0.1");

// =============================================
// CONFIGURATION DES CHEMINS
// =============================================

/**
 * Chemins absolus du système de fichiers
 * Ces constantes facilitent la navigation dans l'application
 */
define('ROOT_PATH', dirname(__DIR__, 2));
define('SRC_PATH', ROOT_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/Config');
define('CONTROLLERS_PATH', SRC_PATH . '/Controllers');
define('MODELS_PATH', SRC_PATH . '/Models');
define('VIEWS_PATH', SRC_PATH . '/Views');
define('SERVICES_PATH', SRC_PATH . '/Services');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMG_PATH', ASSETS_PATH . '/img');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('BACKUPS_PATH', ROOT_PATH . '/backups');
define('TEMPLATES_PATH', VIEWS_PATH . '/templates');

// =============================================
// CONFIGURATION DES URL
// =============================================

/**
 * Fuseau horaire de l'application
 */
define('TIMEZONE', 'Africa/Kinshasa');

/**
 * URL de base de l'application
 * Détectée automatiquement ou configurable manuellement
 */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$script = $_SERVER['SCRIPT_NAME'] ?? ''; // ex : /lavision/a/app/public/index.php

// On retire tout après /public
$base_path = preg_replace('#/public(/.*)?$#', '/public', $script);

// On retire /index.php si présent
$base_path = str_replace('/index.php', '', $base_path);

$base_url = $protocol . '://' . $host . $base_path;

define('BASE_URL', $base_url);

/**
 * URLs spécifiques pour les assets
 */
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'img/');
define('LIBS_URL', ASSETS_URL . 'libs/');

// =============================================
// CONFIGURATION DES MODULES
// =============================================

/**
 * Liste des modules actifs de l'application
 * Permet d'activer/désactiver des fonctionnalités
 */
define('MODULES_ACTIFS', [
    'eleves' => true,
    'academique' => true,
    'personnel' => true,
    'cours_examens' => true,
    'finance' => true,
    'rapports' => true,
    'administration' => true,
    'documentation' => true
]);

// =============================================
// CONFIGURATION DE PERFORMANCE
// =============================================

/**
 * Cache des requêtes SQL (en secondes)
 */
define('CACHE_DURATION', 300); // 5 minutes

/**
 * Taille maximale des fichiers uploadés (en octets)
 */
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB

// =============================================
// CONFIGURATION DES EMAILS
// =============================================

/**
 * Paramètres SMTP pour l'envoi d'emails
 */
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre_email@gmail.com');
define('SMTP_PASS', 'votre_mot_de_passe');
define('SMTP_ENCRYPTION', 'tls');

// =============================================
// CONFIGURATION DES LOGS
// =============================================

/**
 * Niveaux de log disponibles
 */
define('LOG_LEVELS', [
    'DEBUG' => 0,
    'INFO' => 1,
    'WARNING' => 2,
    'ERROR' => 3,
    'CRITICAL' => 4
]);

/**
 * Niveau de log actuel
 */
define('CURRENT_LOG_LEVEL', LOG_LEVELS['INFO']);

// =============================================
// CONFIGURATION DES ROLES UTILISATEURS
// =============================================

/**
 * Rôles disponibles dans le système
 */
define('ROLES', [
    'superadmin' => 'Super Administrateur',
    'admin' => 'Administrateur',
    'secretaire' => 'Secrétaire',
    'professeur' => 'Professeur',
    'eleve' => 'Élève',
    'parent' => 'Parent'
]);

/**
 * Permissions par défaut pour chaque rôle
 */
define('PERMISSIONS_PAR_DEFAUT', [
    'superadmin' => ['*'], // Toutes les permissions
    'admin' => [
        'eleves_gerer',
        'academique_gerer',
        'personnel_gerer',
        'finance_consulter',
        'rapports_consulter',
        'administration_gerer'
    ],
    'secretaire' => [
        'eleves_consulter',
        'eleves_modifier',
        'academique_consulter',
        'finance_gerer',
        'rapports_consulter'
    ],
    'professeur' => [
        'eleves_consulter',
        'academique_consulter',
        'notes_gerer',
        'presences_gerer'
    ],
    'eleve' => [
        'profil_consulter',
        'notes_consulter',
        'emploi_du_temps_consulter'
    ],
    'parent' => [
        'enfants_consulter',
        'notes_consulter',
        'paiements_consulter'
    ]
]);

?>