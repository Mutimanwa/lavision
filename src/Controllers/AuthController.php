<?php
/**
 * Contrôleur d'authentification
 * LaVision - Système de gestion scolaire
 *
 * Ce contrôleur gère l'authentification des utilisateurs
 * (connexion, déconnexion, récupération de mot de passe)
 */

class AuthController extends BaseController {
    /**
     * Affiche le formulaire de connexion
     */
    public function login() {
        // Si déjà connecté, rediriger vers le dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirectToRoute('dashboard', 'index');
        }

        $this->renderAuth('auth/login', [
            'page_title' => 'Connexion'
        ]);
    }

    /**
     * Traite la connexion
     */
    public function processLogin() {
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            $this->setFlashMessage('error', 'Token de sécurité invalide.');
            $this->redirectToRoute('auth', 'login');
        }

        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Validation
        if (empty($email) || empty($password)) {
            $this->setFlashMessage('error', 'Veuillez saisir votre email et mot de passe.');
            $this->redirectToRoute('auth', 'login');
        }

        try {
            // Vérifier les identifiants
            $user = $this->db->prepare("
                SELECT u.*, r.nom as role_nom, r.permissions
                FROM utilisateurs u
                LEFT JOIN roles r ON u.id_role = r.id
                WHERE u.email = ? AND u.actif = 1
            ");
            $user->execute([$email]);
            $user_data = $user->fetch();

            if (!$user_data || !password_verify($password, $user_data['mot_de_passe'])) {
                $this->logAction('Tentative de connexion échouée', "Email: $email");
                $this->setFlashMessage('error', 'Identifiants incorrects.');
                $this->redirectToRoute('auth', 'login');
            }

            // Connexion réussie
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user_nom'] = $user_data['nom'];
            $_SESSION['user_prenom'] = $user_data['prenom'];
            $_SESSION['user_email'] = $user_data['email'];
            $_SESSION['user_role'] = $user_data['role_nom'];
            $_SESSION['user_permissions'] = json_decode($user_data['permissions'], true) ? json_decode($user_data['permissions'], true) : [];

            $this->logAction('Connexion réussie', "Utilisateur: {$user_data['nom']} {$user_data['prenom']}");

            // Rediriger vers le dashboard ou l'URL demandée
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
            if (!empty($redirect) && filter_var($redirect, FILTER_VALIDATE_URL)) {
                $this->redirect($redirect);
            } else {
                $this->redirectToRoute('dashboard', 'index');
            }

        } catch (Exception $e) {
            $this->logAction('Erreur lors de la connexion', $e->getMessage());
            $this->setFlashMessage('error', 'Une erreur s\'est produite lors de la connexion.');
            $this->redirectToRoute('auth', 'login');
        }
    }

    /**
     * Déconnexion
     */
    public function logout() {
        $this->logAction('Déconnexion', "Utilisateur: " . (isset($_SESSION['user_nom']) ? $_SESSION['user_nom'] : 'unknown') . " " . (isset($_SESSION['user_prenom']) ? $_SESSION['user_prenom'] : ''));

        // Détruire la session
        session_destroy();

        // Rediriger vers la page de connexion
        $this->redirectToRoute('auth', 'login');
    }

    /**
     * Affiche le formulaire d'oubli de mot de passe
     */
    public function forgotPassword() {
        $this->renderAuth('auth/forgot_password', [
            'page_title' => 'Mot de passe oublié'
        ]);
    }

    /**
     * Traite la demande de réinitialisation de mot de passe
     */
    public function processForgotPassword() {
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            $this->setFlashMessage('error', 'Token de sécurité invalide.');
            $this->redirectToRoute('auth', 'forgot-password');
        }

        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlashMessage('error', 'Veuillez saisir une adresse email valide.');
            $this->redirectToRoute('auth', 'forgot-password');
        }

        try {
            // Vérifier si l'utilisateur existe
            $user = $this->db->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE email = ? AND actif = 1");
            $user->execute([$email]);
            $user_data = $user->fetch();

            if ($user_data) {
                // Générer un token de réinitialisation
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Sauvegarder le token
                $stmt = $this->db->prepare("
                    INSERT INTO password_resets (email, token, expires_at, created_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
                ");
                $stmt->execute([$email, $token, $expires, $token, $expires]);

                // Envoyer l'email (simulation)
                $this->logAction('Demande de réinitialisation de mot de passe', "Email: $email");

                $this->setFlashMessage('success', 'Un email de réinitialisation a été envoyé à votre adresse.');
            } else {
                // Ne pas révéler si l'email existe ou non pour des raisons de sécurité
                $this->setFlashMessage('success', 'Si votre email est enregistré, vous recevrez un lien de réinitialisation.');
            }

            $this->redirectToRoute('auth', 'login');

        } catch (Exception $e) {
            $this->logAction('Erreur réinitialisation mot de passe', $e->getMessage());
            $this->setFlashMessage('error', 'Une erreur s\'est produite.');
            $this->redirectToRoute('auth', 'forgot-password');
        }
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe
     */
    public function resetPassword() {
        $token = isset($_GET['token']) ? $_GET['token'] : '';

        if (empty($token)) {
            $this->setFlashMessage('error', 'Token de réinitialisation manquant.');
            $this->redirectToRoute('auth', 'login');
        }

        // Vérifier le token
        $reset = $this->db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $reset->execute([$token]);
        $reset_data = $reset->fetch();

        if (!$reset_data) {
            $this->setFlashMessage('error', 'Token de réinitialisation invalide ou expiré.');
            $this->redirectToRoute('auth', 'login');
        }

        $this->renderAuth('auth/reset_password', [
            'page_title' => 'Réinitialiser le mot de passe',
            'token' => $token,
            'email' => $reset_data['email']
        ]);
    }

    /**
     * Traite la réinitialisation de mot de passe
     */
    public function processResetPassword() {
        // Vérifier le token CSRF
        if (!$this->verifyCsrfToken(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            $this->setFlashMessage('error', 'Token de sécurité invalide.');
            $this->redirectToRoute('auth', 'reset-password', [], '', ['token' => isset($_POST['token']) ? $_POST['token'] : '']);
        }

        $token = isset($_POST['token']) ? $_POST['token'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

        if (empty($token) || empty($password) || empty($password_confirm)) {
            $this->setFlashMessage('error', 'Tous les champs sont obligatoires.');
            $this->redirectToRoute('auth', 'reset-password', [], '', ['token' => $token]);
        }

        if ($password !== $password_confirm) {
            $this->setFlashMessage('error', 'Les mots de passe ne correspondent pas.');
            $this->redirectToRoute('auth', 'reset-password', [], '', ['token' => $token]);
        }

        if (strlen($password) < 8) {
            $this->setFlashMessage('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            $this->redirectToRoute('auth', 'reset-password', [], '', ['token' => $token]);
        }

        try {
            // Vérifier le token
            $reset = $this->db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
            $reset->execute([$token]);
            $reset_data = $reset->fetch();

            if (!$reset_data) {
                $this->setFlashMessage('error', 'Token de réinitialisation invalide ou expiré.');
                $this->redirectToRoute('auth', 'login');
            }

            // Mettre à jour le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $reset_data['email']]);

            // Supprimer le token
            $this->db->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

            $this->logAction('Mot de passe réinitialisé', "Email: {$reset_data['email']}");

            $this->setFlashMessage('success', 'Votre mot de passe a été réinitialisé avec succès.');
            $this->redirectToRoute('auth', 'login');

        } catch (Exception $e) {
            $this->logAction('Erreur réinitialisation mot de passe', $e->getMessage());
            $this->setFlashMessage('error', 'Une erreur s\'est produite lors de la réinitialisation.');
            $this->redirectToRoute('auth', 'reset-password', [], '', ['token' => $token]);
        }
    }

    /**
     * Affiche l'écran de verrouillage
     */
    public function lockScreen() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirectToRoute('auth', 'login');
        }

        $this->renderAuth('auth/lock_screen', [
            'page_title' => 'Écran verrouillé',
            'user_name' => $_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom']
        ]);
    }

    /**
     * Vérifie le token CSRF
     */
    private function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
