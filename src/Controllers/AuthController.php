
<?php
/**
 * Contrôleur d'authentification - Version procédurale
 * LaVision - Système de gestion scolaire
 *
 * Ce contrôleur gère l'authentification des utilisateurs
 * (connexion, déconnexion, récupération de mot de passe)
 */
require_once __DIR__ . '/../Services/functions.php';
/**
 * Affiche le formulaire de connexion
 */
function auth_login() {
    // Si déjà connecté, rediriger vers le dashboard
    if (isset($_SESSION['utilisateur_id'])) {
        redirect('dashboard');
    }

    renderAuth('auth/login', [
        'page_title' => 'Connexion'
    ]);
}

/**
 * Traite la connexion
 */
function auth_processLogin() {
    global $db;

    // Vérifier le token CSRF
    if (!auth_verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $_SESSION['error_message'] = 'Token de sécurité invalide.';
        redirect('login');
    }

    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validation
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = 'Veuillez saisir votre email et mot de passe.';
        redirect('login');
    }

    try {
        // Vérifier les identifiants
        $user = $db->prepare("
            SELECT u.*, r.nom as role_nom, r.permissions
            FROM utilisateurs u
            LEFT JOIN roles r ON u.id_role = r.id
            WHERE u.email = ? AND u.actif = 1
        ");
        $user->execute([$email]);
        $user_data = $user->fetch();

        if (!$user_data || !password_verify($password, $user_data['mot_de_passe'])) {
            logAction('Tentative de connexion échouée', "Email: $email");
            $_SESSION['error_message'] = 'Identifiants incorrects.';
            redirect('login');
        }

        // Connexion réussie
        $_SESSION['utilisateur_id'] = $user_data['id'];
        $_SESSION['utilisateur_nom'] = $user_data['nom'];
        $_SESSION['utilisateur_email'] = $user_data['email'];
        $_SESSION['utilisateur_role'] = $user_data['role_nom'];

        logAction('Connexion réussie', "Utilisateur: {$user_data['nom']} {$user_data['prenom']}");

        // Rediriger vers le dashboard ou l'URL demandée
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
        if (!empty($redirect) && filter_var($redirect, FILTER_VALIDATE_URL)) {
            redirect($redirect);
        } else {
            redirect('dashboard');
        }

    } catch (Exception $e) {
        logAction('Erreur lors de la connexion', $e->getMessage());
        $_SESSION['error_message'] = 'Une erreur s\'est produite lors de la connexion.';
        redirect('login');
    }
}

/**
 * Déconnexion
 */
function auth_logout() {
    logAction('Déconnexion', "Utilisateur: " . (isset($_SESSION['utilisateur_nom']) ? $_SESSION['utilisateur_nom'] : 'unknown'));

    // Détruire la session
    session_destroy();

    // Rediriger vers la page de connexion
    redirect('login');
}

/**
 * Affiche le formulaire d'oubli de mot de passe
 */
function auth_forgotPassword() {
    renderAuth('auth/forgot-password', [
        'page_title' => 'Mot de passe oublié'
    ]);
}

/**
 * Traite la demande de réinitialisation de mot de passe
 */
function auth_processForgotPassword() {
    global $db;

    // Vérifier le token CSRF
    if (!auth_verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $_SESSION['error_message'] = 'Token de sécurité invalide.';
        redirect('forgot-password');
    }

    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Veuillez saisir une adresse email valide.';
        redirect('forgot-password');
    }

    try {
        // Vérifier si l'utilisateur existe
        $user = $db->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE email = ? AND actif = 1");
        $user->execute([$email]);
        $user_data = $user->fetch();

        if ($user_data) {
            // Générer un token de réinitialisation
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Sauvegarder le token
            $stmt = $db->prepare("
                INSERT INTO password_resets (email, token, expires_at, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
            ");
            $stmt->execute([$email, $token, $expires, $token, $expires]);

            // Envoyer l'email (simulation)
            logAction('Demande de réinitialisation de mot de passe', "Email: $email");

            $_SESSION['success_message'] = 'Un email de réinitialisation a été envoyé à votre adresse.';
        } else {
            // Ne pas révéler si l'email existe ou non pour des raisons de sécurité
            $_SESSION['success_message'] = 'Si votre email est enregistré, vous recevrez un lien de réinitialisation.';
        }

        redirect('login');

    } catch (Exception $e) {
        logAction('Erreur réinitialisation mot de passe', $e->getMessage());
        $_SESSION['error_message'] = 'Une erreur s\'est produite.';
        redirect('forgot-password');
    }
}

/**
 * Affiche le formulaire de réinitialisation de mot de passe
 */
function auth_resetPassword() {
    global $db;

    $token = isset($_GET['token']) ? $_GET['token'] : '';

    if (empty($token)) {
        $_SESSION['error_message'] = 'Token de réinitialisation manquant.';
        redirect('login');
    }

    // Vérifier le token
    $reset = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $reset->execute([$token]);
    $reset_data = $reset->fetch();

    if (!$reset_data) {
        $_SESSION['error_message'] = 'Token de réinitialisation invalide ou expiré.';
        redirect('login');
    }

    renderAuth('auth/reset-password', [
        'page_title' => 'Réinitialiser le mot de passe',
        'token' => $token,
        'email' => $reset_data['email']
    ]);
}

/**
 * Traite la réinitialisation de mot de passe
 */
function auth_processResetPassword() {
    global $db;

    // Vérifier le token CSRF
    if (!auth_verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $_SESSION['error_message'] = 'Token de sécurité invalide.';
        redirect('reset-password');
    }

    $token = isset($_POST['token']) ? $_POST['token'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if (empty($token) || empty($password) || empty($password_confirm)) {
        $_SESSION['error_message'] = 'Tous les champs sont obligatoires.';
        redirect('reset-password');
    }

    if ($password !== $password_confirm) {
        $_SESSION['error_message'] = 'Les mots de passe ne correspondent pas.';
        redirect('reset-password');
    }

    if (strlen($password) < 8) {
        $_SESSION['error_message'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        redirect('reset-password');
    }

    try {
        // Vérifier le token
        $reset = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $reset->execute([$token]);
        $reset_data = $reset->fetch();

        if (!$reset_data) {
            $_SESSION['error_message'] = 'Token de réinitialisation invalide ou expiré.';
            redirect('login');
        }

        // Mettre à jour le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $reset_data['email']]);

        // Supprimer le token
        $db->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

        logAction('Mot de passe réinitialisé', "Email: {$reset_data['email']}");

        $_SESSION['success_message'] = 'Votre mot de passe a été réinitialisé avec succès.';
        redirect('login');

    } catch (Exception $e) {
        logAction('Erreur réinitialisation mot de passe', $e->getMessage());
        $_SESSION['error_message'] = 'Une erreur s\'est produite lors de la réinitialisation.';
        redirect('reset-password');
    }
}

/**
 * Affiche l'écran de verrouillage
 */
function auth_lockScreen() {
    if (!isset($_SESSION['utilisateur_id'])) {
        redirect('login');
    }

    renderAuth('auth/lock-screen', [
        'page_title' => 'Écran verrouillé',
        'user_name' => $_SESSION['utilisateur_nom']
    ]);
}

/**
 * Vérifie le token CSRF
 */
function auth_verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
