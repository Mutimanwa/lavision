<?php
/**
 * Système d'authentification et de gestion des sessions
 * Sécurité: Hachage bcrypt, protection CSRF, limite de tentatives
 * Version: 1.0.0
 */

require_once __DIR__ . '/database.php';

// =============================================
// CONSTANTES D'AUTHENTIFICATION
// =============================================

// Durée de session (en secondes)
define('SESSION_TIMEOUT', 8 * 60 * 60); // 8 heures
define('SESSION_INACTIVITY', 30 * 60); // 30 minutes

// Tentatives de connexion
if(!defined('MAX_LOGIN_ATTEMPTS')){
    define('MAX_LOGIN_ATTEMPTS', 5);
}

define('LOCKOUT_DURATION', 15 * 60); // 15 minutes

// Rôles utilisateurs
define('ROLE_SUPERADMIN', 'superadmin');
define('ROLE_ADMIN', 'admin');
define('ROLE_SECRETAIRE', 'secretaire');
define('ROLE_GESTIONNAIRE', 'gestionnaire');
define('ROLE_PROVISEUR', 'proviseur');

// Permissions (masque de bits)
define('PERM_VIEW', 1);
define('PERM_CREATE', 2);
define('PERM_EDIT', 4);
define('PERM_DELETE', 8);
define('PERM_EXPORT', 16);
define('PERM_IMPORT', 32);
define('PERM_MANAGE_USERS', 64);
define('PERM_MANAGE_SETTINGS', 128);
define('PERM_ALL', 255);

// =============================================
// FONCTIONS DE GESTION DE SESSION
// =============================================

/**
 * Initialiser une session sécurisée
 */
function session_init(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration sécurisée des cookies de session
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        // Régénérer l'ID de session périodiquement
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Vérifier l'inactivité
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_INACTIVITY)) {
            session_destroy();
            redirect('login.php?timeout=1');
        }
        
        $_SESSION['last_activity'] = time();
        
        // Vérifier l'adresse IP et l'user-agent
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = get_client_ip();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['ip_address'] !== get_client_ip() || 
                  $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            // Détection de session hijacking
            session_destroy();
            log_action('Session hijacking détecté', [
                'old_ip' => $_SESSION['ip_address'],
                'new_ip' => get_client_ip(),
                'user_agent' => $_SESSION['user_agent']
            ], 'security');
            
            redirect('login.php?hijack=1');
        }
    }
}

/**
 * Détruire la session proprement
 */
function session_destroy_complete(): void
{
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function is_logged_in(): bool
{
    session_init();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Vérifier si l'utilisateur a un rôle spécifique
 */
function has_role(string $role): bool
{
    if (!is_logged_in()) {
        return false;
    }
    
    // Superadmin a tous les rôles
    if ($_SESSION['role'] === ROLE_SUPERADMIN) {
        return true;
    }
    
    return $_SESSION['role'] === $role;
}

/**
 * Vérifier si l'utilisateur a une permission spécifique
 */
function has_permission(int $permission): bool
{
    if (!is_logged_in()) {
        return false;
    }
    
    // Superadmin a toutes les permissions
    if ($_SESSION['role'] === ROLE_SUPERADMIN) {
        return true;
    }
    
    // Vérifier les permissions dans la session
    if (isset($_SESSION['permissions'])) {
        return ($_SESSION['permissions'] & $permission) === $permission;
    }
    
    return false;
}

/**
 * Rediriger vers la page de login si non connecté
 */
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? '/';
        redirect('login.php');
    }
}

/**
 * Rediriger vers le dashboard si déjà connecté
 */
function require_logout(): void
{
    if (is_logged_in()) {
        redirect('dashboard.php');
    }
}

// =============================================
// FONCTIONS D'AUTHENTIFICATION
// =============================================

/**
 * Authentifier un utilisateur
 */
function auth_login(string $identifiant, string $mot_de_passe, bool $remember = false): array
{
    // Vérifier les tentatives de connexion
    $login_attempts = check_login_attempts(get_client_ip());
    if ($login_attempts['blocked']) {
        return [
            'success' => false,
            'error' => 'Trop de tentatives. Veuillez réessayer dans ' . $login_attempts['remaining'] . ' secondes.'
        ];
    }
    
    // Valider les entrées
    $errors = validate_login_input($identifiant, $mot_de_passe);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        // Rechercher l'utilisateur
        $user = db_query_single(
            "SELECT *
             FROM user_admins 
             WHERE identifiant = :identifiant 
             AND deleted_at IS NULL",
            ['identifiant' => $identifiant]
        );
        
        // Vérifier si l'utilisateur existe
        if (!$user) {
            log_login_attempt(get_client_ip(), $identifiant, false, 'Utilisateur inexistant');
            return [
                'success' => false,
                'error' => 'Identifiant ou mot de passe incorrect'
            ];
        }
        
        // Vérifier le statut du compte
        if ($user['statut'] !== 'actif') {
            log_login_attempt(get_client_ip(), $identifiant, false, 'Compte ' . $user['statut']);
            return [
                'success' => false,
                'error' => 'Votre compte est ' . $user['statut']
            ];
        }
        
        // Vérifier le mot de passe
        if (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
            log_login_attempt(get_client_ip(), $identifiant, false, 'Mot de passe incorrect');
            return [
                'success' => false,
                'error' => 'Identifiant ou mot de passe incorrect'
            ];
        }
        
        // Vérifier si le mot de passe doit être réinitialisé
        if (password_needs_rehash($user['mot_de_passe'], PASSWORD_BCRYPT)) {
            $new_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
            db_execute(
                "UPDATE user_admins SET mot_de_passe = :hash WHERE user_id = :id",
                ['hash' => $new_hash, 'id' => $user['user_id']]
            );
        }
        
        // Créer la session
        session_init();
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['identifiant'] = $user['identifiant'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['telephone'] = $user['telephone'];
        $_SESSION['photo'] = $user['photo'];
        $_SESSION['photo'] = $user['photo'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['statut'] = $user['statut'];
        $_SESSION['date_creation'] = $user['date_creation'];
        $_SESSION['permissions'] = $user['permissions'] ? json_decode($user['permissions'], true) : 0;
        $_SESSION['login_time'] = time();
        
        // Mettre à jour le dernier login
        db_execute(
            "UPDATE user_admins 
             SET dernier_login = NOW(), 
                 statut = 'actif' 
             WHERE user_id = :id",
            ['id' => $user['user_id']]
        );
        
        // Journaliser la connexion réussie
        log_login_attempt(get_client_ip(), $identifiant, true, 'Connexion réussie');
        log_action('Connexion', [
            'user_id' => $user['user_id'],
            'role' => $user['role']
        ], 'auth');
        
        // Gestion du "Se souvenir de moi"
        if ($remember) {
            create_remember_token($user['user_id']);
        }
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['user_id'],
                'nom_complet' => $user['prenom'] . ' ' . $user['nom'],
                'role' => $user['role']
            ],
            'message' => 'Connexion réussie'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur auth_login: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Erreur interne. Veuillez réessayer.'
        ];
    }
}

/**
 * Déconnecter l'utilisateur
 */
function auth_logout(): void
{
    if (is_logged_in()) {
        $user_id = $_SESSION['user_id'] ?? null;
        
        // Journaliser la déconnexion
        log_action('Déconnexion', ['user_id' => $user_id], 'auth');
        
        // Supprimer le token "remember me"
        if (isset($_COOKIE['remember_token'])) {
            delete_remember_token($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    session_destroy_complete();
}

/**
 * Valider les entrées de login
 */
function validate_login_input(string $identifiant, string $mot_de_passe): array
{
    $errors = [];
    
    // Vérifier l'identifiant
    if (empty(trim($identifiant))) {
        $errors['identifiant'] = 'L\'identifiant est requis';
    } elseif (strlen($identifiant) < 3) {
        $errors['identifiant'] = 'L\'identifiant doit faire au moins 3 caractères';
    } elseif (strlen($identifiant) > 50) {
        $errors['identifiant'] = 'L\'identifiant ne peut pas dépasser 50 caractères';
    }
    
    // Vérifier le mot de passe
    if (empty($mot_de_passe)) {
        $errors['mot_de_passe'] = 'Le mot de passe est requis';
    } elseif (strlen($mot_de_passe) < 6) {
        $errors['mot_de_passe'] = 'Le mot de passe doit faire au moins 6 caractères';
    }
    
    return $errors;
}

// =============================================
// GESTION DES MOTS DE PASSE
// =============================================

/**
 * Hacher un mot de passe
 */
function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Vérifier la force d'un mot de passe
 */
function check_password_strength(string $password): array
{
    $strength = 0;
    $messages = [];
    
    // Longueur minimale
    if (strlen($password) >= 8) {
        $strength++;
    } else {
        $messages[] = 'Minimum 8 caractères';
    }
    
    // Contient des lettres minuscules
    if (preg_match('/[a-z]/', $password)) {
        $strength++;
    } else {
        $messages[] = 'Au moins une lettre minuscule';
    }
    
    // Contient des lettres majuscules
    if (preg_match('/[A-Z]/', $password)) {
        $strength++;
    } else {
        $messages[] = 'Au moins une lettre majuscule';
    }
    
    // Contient des chiffres
    if (preg_match('/[0-9]/', $password)) {
        $strength++;
    } else {
        $messages[] = 'Au moins un chiffre';
    }
    
    // Contient des caractères spéciaux
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        $strength++;
    } else {
        $messages[] = 'Au moins un caractère spécial';
    }
    
    // Évaluer la force
    if ($strength >= 4) {
        $level = 'fort';
    } elseif ($strength >= 3) {
        $level = 'moyen';
    } else {
        $level = 'faible';
    }
    
    return [
        'strength' => $strength,
        'level' => $level,
        'messages' => $messages,
        'is_strong' => $strength >= 4
    ];
}

/**
 * Générer un mot de passe aléatoire
 */
function generate_random_password(int $length = 12): string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Réinitialiser le mot de passe d'un utilisateur
 */
function reset_password(string $email): array
{
    // Rechercher l'utilisateur
    $user = db_query_single(
        "SELECT user_id, email, nom, prenom 
         FROM user_admins 
         WHERE email = :email 
         AND deleted_at IS NULL",
        ['email' => $email]
    );
    
    if (!$user) {
        return [
            'success' => false,
            'error' => 'Aucun compte trouvé avec cet email'
        ];
    }
    
    try {
        // Générer un token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Stocker le token dans la base
        db_execute(
            "INSERT INTO password_resets (email, token, expires_at) 
             VALUES (:email, :token, :expires)
             ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires",
            ['email' => $email, 'token' => $token, 'expires' => $expires]
        );
        
        // Envoyer l'email (simulé ici)
        $reset_link = BASE_URL . "reset_password.php?token=" . urlencode($token);
        
        // Dans un vrai système, on enverrait un email
        // mail($email, "Réinitialisation de mot de passe", "Lien: $reset_link");
        
        log_action('Demande réinitialisation mot de passe', [
            'user_id' => $user['user_id'],
            'email' => $email
        ], 'auth');
        
        return [
            'success' => true,
            'message' => 'Instructions envoyées par email',
            'token' => DEBUG_MODE ? $token : null, // À retirer en production
            'link' => DEBUG_MODE ? $reset_link : null
        ];
        
    } catch (Exception $e) {
        error_log("Erreur reset_password: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Erreur lors de la réinitialisation'
        ];
    }
}

// =============================================
// PROTECTION CONTRE LES ATTAQUES
// =============================================

/**
 * Vérifier les tentatives de connexion
 */
function check_login_attempts(string $ip): array
{
    $lockout_key = "login_lockout_" . md5($ip);
    $attempts_key = "login_attempts_" . md5($ip);
    
    // Vérifier si l'IP est bloquée
    if (isset($_SESSION[$lockout_key])) {
        $remaining = $_SESSION[$lockout_key] - time();
        if ($remaining > 0) {
            return [
                'blocked' => true,
                'remaining' => $remaining,
                'attempts' => $_SESSION[$attempts_key] ?? 0
            ];
        } else {
            // Débloquer après expiration
            unset($_SESSION[$lockout_key]);
            unset($_SESSION[$attempts_key]);
        }
    }
    
    return [
        'blocked' => false,
        'remaining' => 0,
        'attempts' => $_SESSION[$attempts_key] ?? 0
    ];
}

/**
 * Journaliser une tentative de connexion
 */
function log_login_attempt(string $ip, string $identifiant, bool $success, string $reason = ''): void
{
    $attempts_key = "login_attempts_" . md5($ip);
    $lockout_key = "login_lockout_" . md5($ip);
    
    if ($success) {
        // Réinitialiser les tentatives en cas de succès
        unset($_SESSION[$attempts_key]);
        unset($_SESSION[$lockout_key]);
    } else {
        // Incrémenter les tentatives échouées
        $attempts = ($_SESSION[$attempts_key] ?? 0) + 1;
        $_SESSION[$attempts_key] = $attempts;
        
        // Bloquer après MAX_LOGIN_ATTEMPTS
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $_SESSION[$lockout_key] = time() + LOCKOUT_DURATION;
            $reason .= " (Compte bloqué pour " . LOCKOUT_DURATION . " secondes)";
        }
    }
    
    // Journaliser dans la base de données
    db_execute(
        "INSERT INTO login_attempts (ip_address, identifiant, success, reason, user_agent) 
         VALUES (:ip, :identifiant, :success, :reason, :user_agent)",
        [
            'ip' => $ip,
            'identifiant' => $identifiant,
            'success' => $success ? 1 : 0,
            'reason' => $reason,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]
    );
    
    // Journaliser localement
    $level = $success ? 'info' : ($attempts >= MAX_LOGIN_ATTEMPTS ? 'warning' : 'info');
    log_action('Tentative de connexion', [
        'ip' => $ip,
        'identifiant' => $identifiant,
        'success' => $success,
        'reason' => $reason,
        'attempts' => $attempts
    ], 'security');
}

/**
 * Obtenir l'adresse IP du client
 */
function get_client_ip(): string
{
    $ip_keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];
    
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
            $ip_list = explode(',', $_SERVER[$key]);
            $ip = trim($ip_list[0]);
            
            // Valider l'adresse IP
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// =============================================
// FONCTIONS "SE SOUVENIR DE MOI"
// =============================================

/**
 * Créer un token "remember me"
 */
function create_remember_token(int $user_id): void
{
    // Générer un token unique
    $token = bin2hex(random_bytes(32));
    $selector = bin2hex(random_bytes(16));
    $hashed_token = hash('sha256', $token);
    
    // Date d'expiration (30 jours)
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Stocker dans la base
    db_execute(
        "INSERT INTO remember_tokens (user_id, selector, hashed_token, expires_at) 
         VALUES (:user_id, :selector, :hashed_token, :expires)",
        [
            'user_id' => $user_id,
            'selector' => $selector,
            'hashed_token' => $hashed_token,
            'expires' => $expires
        ]
    );
    
    // Stocker dans un cookie sécurisé
    $cookie_value = $selector . ':' . $token;
    setcookie(
        'remember_token',
        $cookie_value,
        [
            'expires' => strtotime('+30 days'),
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Vérifier le token "remember me"
 */
function check_remember_token(): bool
{
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $cookie_value = $_COOKIE['remember_token'];
    list($selector, $token) = explode(':', $cookie_value);
    
    if (empty($selector) || empty($token)) {
        return false;
    }
    
    // Rechercher le token
    $token_data = db_query_single(
        "SELECT rt.*, u.user_id, u.identifiant, u.email, u.nom, u.prenom, u.role, u.permissions
         FROM remember_tokens rt
         JOIN user_admins u ON rt.user_id = u.user_id
         WHERE rt.selector = :selector 
         AND u.deleted_at IS NULL
         AND u.statut = 'actif'",
        ['selector' => $selector]
    );
    
    if (!$token_data) {
        return false;
    }
    
    // Vérifier l'expiration
    if (strtotime($token_data['expires_at']) < time()) {
        delete_remember_token($selector);
        return false;
    }
    
    // Vérifier le token
    $hashed_token = hash('sha256', $token);
    if (!hash_equals($token_data['hashed_token'], $hashed_token)) {
        delete_remember_token($selector);
        return false;
    }
    
    // Connexion automatique
    session_init();
    
    $_SESSION['user_id'] = $token_data['user_id'];
    $_SESSION['identifiant'] = $token_data['identifiant'];
    $_SESSION['email'] = $token_data['email'];
    $_SESSION['nom'] = $token_data['nom'];
    $_SESSION['prenom'] = $token_data['prenom'];
    $_SESSION['role'] = $token_data['role'];
    $_SESSION['permissions'] = $token_data['permissions'] ? json_decode($token_data['permissions'], true) : 0;
    $_SESSION['login_time'] = time();
    $_SESSION['remember_me'] = true;
    
    // Renouveler le token
    delete_remember_token($selector);
    create_remember_token($token_data['user_id']);
    
    log_action('Connexion automatique via remember token', [
        'user_id' => $token_data['user_id']
    ], 'auth');
    
    return true;
}

/**
 * Supprimer un token "remember me"
 */
function delete_remember_token(string $selector): void
{
    db_execute(
        "DELETE FROM remember_tokens WHERE selector = :selector",
        ['selector' => $selector]
    );
}

// =============================================
// FONCTIONS D'AUTORISATION
// =============================================

/**
 * Vérifier l'accès à une ressource
 */
function check_access(string $resource, string $action = 'view'): bool
{
    if (!is_logged_in()) {
        return false;
    }
    
    // Superadmin a accès à tout
    if ($_SESSION['role'] === ROLE_SUPERADMIN) {
        return true;
    }
    
    // Définir les permissions par rôle et ressource
    $role_permissions = [
        ROLE_SUPERADMIN => [
            'users' => PERM_ALL,
            'eleves' => PERM_ALL,
            'professeurs' => PERM_ALL,
            'classes' => PERM_ALL,
            'notes' => PERM_ALL,
            'paiements' => PERM_ALL,
            'settings' => PERM_ALL
        ],
        ROLE_ADMIN => [
            'users' => PERM_ALL,
            'eleves' => PERM_ALL,
            'professeurs' => PERM_ALL,
            'classes' => PERM_ALL,
            'notes' => PERM_ALL,
            'paiements' => PERM_ALL,
            'settings' => PERM_MANAGE_SETTINGS
        ],
        ROLE_SECRETAIRE => [
            'eleves' => PERM_VIEW | PERM_CREATE | PERM_EDIT,
            'professeurs' => PERM_VIEW,
            'classes' => PERM_VIEW,
            'notes' => PERM_VIEW,
            'paiements' => PERM_VIEW | PERM_CREATE | PERM_EDIT
        ],
        ROLE_GESTIONNAIRE => [
            'paiements' => PERM_ALL,
            'eleves' => PERM_VIEW
        ],
        ROLE_PROVISEUR => [
            'eleves' => PERM_ALL,
            'professeurs' => PERM_ALL,
            'classes' => PERM_ALL,
            'notes' => PERM_ALL,
            'statistiques' => PERM_ALL
        ]
    ];
    
    // Vérifier si le rôle a des permissions définies
    if (!isset($role_permissions[$_SESSION['role']])) {
        return false;
    }
    
    $role_perm = $role_permissions[$_SESSION['role']];
    
    // Vérifier si la ressource est autorisée
    if (!isset($role_perm[$resource])) {
        return false;
    }
    
    // Convertir l'action en permission
    $action_map = [
        'view' => PERM_VIEW,
        'create' => PERM_CREATE,
        'edit' => PERM_EDIT,
        'delete' => PERM_DELETE,
        'export' => PERM_EXPORT,
        'import' => PERM_IMPORT
    ];
    
    $required_perm = $action_map[$action] ?? PERM_VIEW;
    
    // Vérifier la permission
    return ($role_perm[$resource] & $required_perm) === $required_perm;
}

/**
 * Rediriger si l'accès est refusé
 */
function require_access(string $resource, string $action = 'view'): void
{
    if (!check_access($resource, $action)) {
        $_SESSION['error'] = 'Accès non autorisé';
        redirect('403.php');
    }
}

// =============================================
// FONCTIONS D'UTILISATEUR
// =============================================

/**
 * Obtenir les informations de l'utilisateur courant
 */
function auth_get_current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    
    try {
        // Récupérer plus d'informations depuis la base
        $user_data = db_query_single(
            "SELECT user_id, identifiant, email, nom, prenom, role, permissions, 
                    telephone, photo, statut, date_creation, dernier_login
             FROM user_admins 
             WHERE user_id = :user_id 
             AND deleted_at IS NULL",
            ['user_id' => $_SESSION['user_id']]
        );
        
        if (!$user_data) {
            return null;
        }
        
        return [
            'id' => $user_data['user_id'],
            'identifiant' => $user_data['identifiant'],
            'email' => $user_data['email'],
            'nom' => $user_data['nom'],
            'prenom' => $user_data['prenom'],
            'nom_complet' => $user_data['prenom'] . ' ' . $user_data['nom'],
            'role' => $user_data['role'],
            'permissions' => json_decode($user_data['permissions'] ?? '[]', true),
            'telephone' => $user_data['telephone'],
            'photo' => $user_data['photo'] ?: 'user-avatar.png',
            'statut' => $user_data['statut'],
            'date_creation' => $user_data['date_creation'],
            'dernier_login' => $user_data['dernier_login'],
            'login_time' => $_SESSION['login_time'] ?? null
        ];
        
    } catch (Exception $e) {
        error_log("Erreur auth_get_current_user: " . $e->getMessage());
        return null;
    }
}
/**
 * Mettre à jour le profil utilisateur
 */
function update_user_profile(int $user_id, array $data): array
{
    $errors = validate_profile_data($data);
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        // Vérifier que l'utilisateur existe
        $user = db_query_single(
            "SELECT user_id FROM user_admins WHERE user_id = :id AND deleted_at IS NULL",
            ['id' => $user_id]
        );
        
        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur non trouvé'];
        }
        
        // Préparer la mise à jour
        $updates = [];
        $params = ['id' => $user_id];
        
        if (isset($data['email'])) {
            // Vérifier l'unicité de l'email
            if (db_value_exists('user_admins', 'email', $data['email'], $user_id)) {
                return ['success' => false, 'error' => 'Cet email est déjà utilisé'];
            }
            $updates[] = "email = :email";
            $params['email'] = $data['email'];
        }
        
        if (isset($data['telephone'])) {
            $updates[] = "telephone = :telephone";
            $params['telephone'] = $data['telephone'];
        }
        
        if (isset($data['nom'])) {
            $updates[] = "nom = :nom";
            $params['nom'] = $data['nom'];
        }
        
        if (isset($data['prenom'])) {
            $updates[] = "prenom = :prenom";
            $params['prenom'] = $data['prenom'];
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Exécuter la mise à jour
        $sql = "UPDATE user_admins SET " . implode(', ', $updates) . " WHERE user_id = :id";
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Mettre à jour la session si c'est l'utilisateur courant
            if ($user_id == ($_SESSION['user_id'] ?? 0)) {
                foreach (['email', 'nom', 'prenom'] as $field) {
                    if (isset($data[$field])) {
                        $_SESSION[$field] = $data[$field];
                    }
                }
            }
            
            log_action('Profil utilisateur mis à jour', [
                'user_id' => $user_id,
                'fields' => array_keys($data)
            ], 'auth');
            
            return ['success' => true, 'message' => 'Profil mis à jour avec succès'];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur update_user_profile: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Valider les données du profil
 */
function validate_profile_data(array $data): array
{
    $errors = [];
    
    if (isset($data['email']) && !empty($data['email'])) {
        if (!is_valid_email($data['email'])) {
            $errors['email'] = 'Email invalide';
        }
    }
    
    if (isset($data['telephone']) && !empty($data['telephone'])) {
        if (!preg_match('/^[0-9+\s\-\(\)]{8,20}$/', $data['telephone'])) {
            $errors['telephone'] = 'Numéro de téléphone invalide';
        }
    }
    
    if (isset($data['nom']) && empty(trim($data['nom']))) {
        $errors['nom'] = 'Le nom est requis';
    }
    
    if (isset($data['prenom']) && empty(trim($data['prenom']))) {
        $errors['prenom'] = 'Le prénom est requis';
    }
    
    return $errors;
}

/**
 * Changer le mot de passe d'un utilisateur
 */
function change_user_password(int $user_id, string $current_password, string $new_password, string $confirm_password): array
{
    // Validation
    if (empty($current_password)) {
        return ['success' => false, 'error' => 'Mot de passe actuel requis'];
    }
    
    if (empty($new_password) || empty($confirm_password)) {
        return ['success' => false, 'error' => 'Nouveau mot de passe requis'];
    }
    
    if ($new_password !== $confirm_password) {
        return ['success' => false, 'error' => 'Les mots de passe ne correspondent pas'];
    }
    
    // Vérifier la force du mot de passe
    $strength = check_password_strength($new_password);
    if (!$strength['is_strong']) {
        return ['success' => false, 'error' => 'Le mot de passe est trop faible'];
    }
    
    try {
        // Récupérer l'utilisateur
        $user = db_query_single(
            "SELECT user_id, mot_de_passe FROM user_admins WHERE user_id = :id",
            ['id' => $user_id]
        );
        
        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur non trouvé'];
        }
        
        // Vérifier le mot de passe actuel
        if (!password_verify($current_password, $user['mot_de_passe'])) {
            return ['success' => false, 'error' => 'Mot de passe actuel incorrect'];
        }
        
        // Hacher le nouveau mot de passe
        $new_hash = hash_password($new_password);
        
        // Mettre à jour dans la base
        db_execute(
            "UPDATE user_admins SET mot_de_passe = :password WHERE user_id = :id",
            ['password' => $new_hash, 'id' => $user_id]
        );
        
        // Journaliser l'action
        log_action('Mot de passe changé', ['user_id' => $user_id], 'auth');
        
        return [
            'success' => true,
            'message' => 'Mot de passe changé avec succès'
        ];
        
    } catch (Exception $e) {
        log_action("Erreur de modification de mot de passe", ['error' => "" . $e->getMessage()]);
        error_log("Erreur change_user_password: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Uploader une photo de profil
 */
function upload_profile_photo(int $user_id): array
{
    // Vérifier si un fichier a été uploadé
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Aucun fichier uploadé ou erreur d\'upload'];
    }
    
    $file = $_FILES['photo'];
    
    // Vérifier la taille
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Le fichier est trop volumineux (max 5MB)'];
    }
    
    // Vérifier le type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }
    
    try {
        // Créer le dossier si nécessaire
        $upload_dir = UPLOADS_PATH . '/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Générer un nom de fichier unique
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
        $destination = $upload_dir . $new_filename;
        
        // Déplacer le fichier uploadé
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Supprimer l'ancienne photo si elle existe
            $old_photo = db_query_single(
                "SELECT photo FROM user_admins WHERE user_id = :id",
                ['id' => $user_id]
            )['photo'] ?? '';
            
            if ($old_photo && file_exists($upload_dir . $old_photo)) {
                unlink($upload_dir . $old_photo);
            }
            
            // Mettre à jour dans la base
            db_execute(
                "UPDATE user_admins SET photo = :photo WHERE user_id = :id",
                ['photo' => $new_filename, 'id' => $user_id]
            );
            
            // Journaliser l'action
            log_action('Photo de profil changée', ['user_id' => $user_id, 'file' => $new_filename], 'auth');
            
            return [
                'success' => true,
                'message' => 'Photo mise à jour avec succès',
                'photo_url' => '/public/uploads/profiles/' . $new_filename
            ];
        } else {
            return ['success' => false, 'error' => 'Erreur lors du déplacement du fichier'];
        }
        
    } catch (Exception $e) {
        error_log("Erreur upload_profile_photo: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Obtenir les statistiques d'un utilisateur
 */
function get_user_statistics(int $user_id): array
{
    try {
        // Compter les élèves gérés (selon le rôle)
        $eleves_count = db_query_single(
            "SELECT COUNT(*) as count FROM eleves WHERE statut_etudiant = 'actif'"
        )['count'] ?? 0;
        
        // Compter les classes
        $classes_count = db_query_single(
            "SELECT COUNT(*) as count FROM classes WHERE statut = 'active'"
        )['count'] ?? 0;
        
        // Compter les professeurs
        $professeurs_count = db_query_single(
            "SELECT COUNT(*) as count FROM professeurs WHERE statut = 'actif'"
        )['count'] ?? 0;
        
        // Compter les actions du mois (logs)
        $actions_count = db_query_single(
            "SELECT COUNT(*) as count FROM logs 
             WHERE user_id = :user_id 
             AND DATE(date_action) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
            ['user_id' => $user_id]
        )['count'] ?? 0;
        
        return [
            'eleves_count' => $eleves_count,
            'classes_count' => $classes_count,
            'professeurs_count' => $professeurs_count,
            'actions_count' => $actions_count
        ];
        
    } catch (Exception $e) {
        error_log("Erreur get_user_statistics: " . $e->getMessage());
        return [
            'eleves_count' => 0,
            'classes_count' => 0,
            'professeurs_count' => 0,
            'actions_count' => 0
        ];
    }
}

/**
 * Obtenir l'URL d'une photo de profil
 */
function get_profile_photo_url(?string $filename): string
{
    if (!$filename) {
        return IMAGES_URL . 'icons/user-avatar.png';
    }
    
    // Vérifier si c'est une URL complète ou un chemin local
    if (strpos($filename, 'http') === 0) {
        return $filename;
    }
    
    return BASE_URL . 'uploads/profiles/' . $filename;
}

/**
 * Obtenir les activités récentes d'un utilisateur
 */
function get_recent_activities(int $user_id, int $limit = 5): array
{
    try {
        $activities = db_query(
            "SELECT action, action_date, details, ip_address
             FROM user_logs 
             WHERE user_id = :user_id 
             ORDER BY action_date DESC 
             LIMIT :limit",
            ['user_id' => $user_id, 'limit' => $limit]
        );
        
        $formatted = [];
        foreach ($activities as $activity) {
            $details = json_decode($activity['details'] ?? '{}', true);
            
            // Déterminer le type d'action
            $type = 'view';
            if (strpos($activity['action'], 'login') !== false) $type = 'login';
            elseif (strpos($activity['action'], 'logout') !== false) $type = 'logout';
            elseif (strpos($activity['action'], 'create') !== false || strpos($activity['action'], 'ajout') !== false) $type = 'create';
            elseif (strpos($activity['action'], 'update') !== false || strpos($activity['action'], 'modif') !== false) $type = 'update';
            elseif (strpos($activity['action'], 'delete') !== false || strpos($activity['action'], 'supprim') !== false) $type = 'delete';
            
            $formatted[] = [
                'type' => $type,
                'title' => ucfirst($activity['action']),
                'description' => $details['description'] ?? $activity['action'],
                'time_ago' => time_ago($activity['action_date'])
            ];
        }
        
        return $formatted;
        
    } catch (Exception $e) {
        error_log("Erreur get_recent_activities: " . $e->getMessage());
        return [];
    }
}


// =============================================
// INITIALISATION
// =============================================

// Initialiser la session
session_init();

// Vérifier le token "remember me" si non connecté
if (!is_logged_in() && isset($_COOKIE['remember_token'])) {
    check_remember_token();
}

// Journaliser le chargement du module
log_action('Module auth chargé', ['version' => '1.0.0']);