<?php
/**
 * Service d'authentification et gestion des utilisateurs
 * Gère la connexion, déconnexion et vérification des permissions
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/database.php';

// =============================================
// CONSTANTES D'AUTHENTIFICATION
// =============================================

/**
 * Rôles disponibles dans le système
 */
define('ROLES_SYSTEME', [
    'admin' => 'Administrateur',
    'directeur' => 'Directeur',
    'enseignant' => 'Enseignant',
    'secretaire' => 'Secrétaire',
    'parent' => 'Parent',
    'eleve' => 'Élève'
]);

/**
 * Permissions par rôle
 */
define('PERMISSIONS_ROLES', [
    'admin' => ['*'], // Toutes les permissions
    'directeur' => [
        'eleves', 'academique', 'personnel', 'finance', 'rapports',
        'utilisateurs', 'parametres'
    ],
    'enseignant' => [
        'eleves', 'academique', 'rapports'
    ],
    'secretaire' => [
        'eleves', 'academique', 'personnel', 'finance', 'rapports'
    ],
    'parent' => [
        'eleves' // Seulement ses propres enfants
    ],
    'eleve' => [
        'profil' // Seulement son propre profil
    ]
]);

// =============================================
// FONCTIONS D'AUTHENTIFICATION
// =============================================

/**
 * Vérifie si un utilisateur est connecté
 *
 * @return bool True si connecté
 */
function est_connecte(): bool
{
    return isset($_SESSION['utilisateur_id']) &&
           isset($_SESSION['utilisateur_role']) &&
           !empty($_SESSION['utilisateur_id']);
}

/**
 * Récupère les informations de l'utilisateur connecté
 *
 * @return array|null Données utilisateur ou null
 */
function get_utilisateur_connecte(): ?array
{
    if (!est_connecte()) {
        return null;
    }

    $sql = "SELECT u.*, p.nom_complet, p.telephone, p.adresse
            FROM utilisateurs u
            LEFT JOIN profils p ON u.id = p.id_utilisateur
            WHERE u.id = ? AND u.statut = 'actif'";

    $result = db_query($sql, [$_SESSION['utilisateur_id']]);

    return $result ? $result[0] : null;
}

/**
 * Vérifie si l'utilisateur a une permission spécifique
 *
 * @param string $permission Permission à vérifier
 * @return bool True si autorisé
 */
function a_permission(string $permission): bool
{
    if (!est_connecte()) {
        return false;
    }

    $role = $_SESSION['utilisateur_role'];

    // Admin a toutes les permissions
    if ($role === 'admin' || in_array('*', PERMISSIONS_ROLES[$role] ?? [])) {
        return true;
    }

    // Vérification spécifique
    return in_array($permission, PERMISSIONS_ROLES[$role] ?? []);
}

/**
 * Connecte un utilisateur avec email et mot de passe
 *
 * @param string $email Email utilisateur
 * @param string $mot_de_passe Mot de passe
 * @return array Résultat avec succès/erreur
 */
function connecter_utilisateur(string $email, string $mot_de_passe): array
{
    // Validation des entrées
    if (empty($email) || empty($mot_de_passe)) {
        return ['succes' => false, 'message' => 'Email et mot de passe requis'];
    }

    // Vérification du format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['succes' => false, 'message' => 'Format d\'email invalide'];
    }

    // Recherche de l'utilisateur
    $sql = "SELECT u.*, p.nom_complet
            FROM utilisateurs u
            LEFT JOIN profils p ON u.id = p.id_utilisateur
            WHERE u.email = ? AND u.statut = 'actif'";

    $utilisateur = db_query($sql, [$email]);

    if (!$utilisateur) {
        return ['succes' => false, 'message' => 'Email ou mot de passe incorrect'];
    }

    $utilisateur = $utilisateur[0];

    // Vérification du mot de passe
    if (!password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        // Enregistrement de la tentative échouée
        enregistrer_tentative_connexion($email, false);
        return ['succes' => false, 'message' => 'Email ou mot de passe incorrect'];
    }

    // Vérification du nombre de tentatives
    if (verifier_tentatives_connexion($email)) {
        return ['succes' => false, 'message' => 'Trop de tentatives de connexion. Réessayez plus tard.'];
    }

    // Connexion réussie
    $_SESSION['utilisateur_id'] = $utilisateur['id'];
    $_SESSION['utilisateur_email'] = $utilisateur['email'];
    $_SESSION['utilisateur_role'] = $utilisateur['role'];
    $_SESSION['utilisateur_nom'] = $utilisateur['nom_complet'] ?? $utilisateur['email'];
    $_SESSION['derniere_activite'] = time();

    // Enregistrement de la connexion réussie
    enregistrer_tentative_connexion($email, true);
    enregistrer_action('connexion', 'Connexion utilisateur', $utilisateur['id']);

    return ['succes' => true, 'message' => 'Connexion réussie'];
}

/**
 * Déconnecte l'utilisateur actuel
 *
 * @return void
 */
function deconnecter_utilisateur(): void
{
    if (est_connecte()) {
        enregistrer_action('deconnexion', 'Déconnexion utilisateur', $_SESSION['utilisateur_id']);
    }

    // Nettoyage de la session
    session_unset();
    session_destroy();

    // Redémarrage de la session
    session_start();
}

/**
 * Enregistre une tentative de connexion
 *
 * @param string $email Email utilisé
 * @param bool $succes Si la connexion a réussi
 * @return void
 */
function enregistrer_tentative_connexion(string $email, bool $succes): void
{
    $sql = "INSERT INTO tentatives_connexion (email, succes, adresse_ip, user_agent, date_tentative)
            VALUES (?, ?, ?, ?, NOW())";

    db_query($sql, [
        $email,
        $succes ? 1 : 0,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Vérifie si l'utilisateur a dépassé le nombre de tentatives
 *
 * @param string $email Email à vérifier
 * @return bool True si bloqué
 */
function verifier_tentatives_connexion(string $email): bool
{
    $sql = "SELECT COUNT(*) as tentatives
            FROM tentatives_connexion
            WHERE email = ? AND succes = 0 AND date_tentative > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";

    $result = db_query($sql, [$email]);

    return $result && $result[0]['tentatives'] >= MAX_LOGIN_ATTEMPTS;
}

/**
 * Enregistre une action dans le journal d'audit
 *
 * @param string $action Type d'action
 * @param string $description Description détaillée
 * @param int|null $id_utilisateur ID utilisateur (null pour système)
 * @return void
 */
function enregistrer_action(string $action, string $description, ?int $id_utilisateur = null): void
{
    $id_user = $id_utilisateur ?? ($_SESSION['utilisateur_id'] ?? null);

    $sql = "INSERT INTO journal_audit (action, description, id_utilisateur, adresse_ip, user_agent, date_action)
            VALUES (?, ?, ?, ?, ?, NOW())";

    db_query($sql, [
        $action,
        $description,
        $id_user,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Récupère les utilisateurs actifs
 *
 * @return array Liste des utilisateurs
 */
function get_utilisateurs_actifs(): array
{
    $sql = "SELECT u.*, p.nom_complet, p.telephone
            FROM utilisateurs u
            LEFT JOIN profils p ON u.id = p.id_utilisateur
            WHERE u.statut = 'actif'
            ORDER BY u.date_creation DESC";

    return db_query($sql) ?? [];
}

/**
 * Crée un nouvel utilisateur
 *
 * @param array $donnees Données utilisateur
 * @return array Résultat avec ID ou erreur
 */
function creer_utilisateur(array $donnees): array
{
    // Validation des données
    if (empty($donnees['email']) || empty($donnees['mot_de_passe']) || empty($donnees['role'])) {
        return ['succes' => false, 'message' => 'Données incomplètes'];
    }

    // Vérification email unique
    $sql = "SELECT id FROM utilisateurs WHERE email = ?";
    if (db_query($sql, [$donnees['email']])) {
        return ['succes' => false, 'message' => 'Cet email est déjà utilisé'];
    }

    // Hash du mot de passe
    $mot_de_passe_hash = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);

    // Insertion utilisateur
    $sql = "INSERT INTO utilisateurs (email, mot_de_passe, role, statut, date_creation)
            VALUES (?, ?, ?, 'actif', NOW())";

    $result = db_insert($sql, [
        $donnees['email'],
        $mot_de_passe_hash,
        $donnees['role']
    ]);

    if (!$result) {
        return ['succes' => false, 'message' => 'Erreur lors de la création'];
    }

    $id_utilisateur = $result;

    // Création du profil si des données sont fournies
    if (!empty($donnees['nom_complet'])) {
        $sql = "INSERT INTO profils (id_utilisateur, nom_complet, telephone, adresse, date_creation)
                VALUES (?, ?, ?, ?, NOW())";

        db_query($sql, [
            $id_utilisateur,
            $donnees['nom_complet'],
            $donnees['telephone'] ?? null,
            $donnees['adresse'] ?? null
        ]);
    }

    enregistrer_action('creation_utilisateur', 'Création d\'un nouvel utilisateur', $id_utilisateur);

    return ['succes' => true, 'id' => $id_utilisateur, 'message' => 'Utilisateur créé avec succès'];
}

/**
 * Met à jour un utilisateur
 *
 * @param int $id_utilisateur ID utilisateur
 * @param array $donnees Données à mettre à jour
 * @return array Résultat de l'opération
 */
function mettre_a_jour_utilisateur(int $id_utilisateur, array $donnees): array
{
    // Mise à jour du mot de passe si fourni
    if (!empty($donnees['mot_de_passe'])) {
        $mot_de_passe_hash = password_hash($donnees['mot_de_passe'], PASSWORD_DEFAULT);
        $sql = "UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?";
        db_query($sql, [$mot_de_passe_hash, $id_utilisateur]);
    }

    // Mise à jour des autres champs
    $champs_autorises = ['email', 'role', 'statut'];
    $donnees_filtrees = array_intersect_key($donnees, array_flip($champs_autorises));

    if (!empty($donnees_filtrees)) {
        $sql = "UPDATE utilisateurs SET " .
               implode(', ', array_map(fn($champ) => "$champ = ?", array_keys($donnees_filtrees))) .
               " WHERE id = ?";

        $valeurs = array_values($donnees_filtrees);
        $valeurs[] = $id_utilisateur;

        db_query($sql, $valeurs);
    }

    // Mise à jour du profil
    if (isset($donnees['nom_complet']) || isset($donnees['telephone']) || isset($donnees['adresse'])) {
        $sql = "INSERT INTO profils (id_utilisateur, nom_complet, telephone, adresse, date_creation)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                nom_complet = VALUES(nom_complet),
                telephone = VALUES(telephone),
                adresse = VALUES(adresse)";

        db_query($sql, [
            $id_utilisateur,
            $donnees['nom_complet'] ?? null,
            $donnees['telephone'] ?? null,
            $donnees['adresse'] ?? null
        ]);
    }

    enregistrer_action('modification_utilisateur', 'Modification des données utilisateur', $id_utilisateur);

    return ['succes' => true, 'message' => 'Utilisateur mis à jour avec succès'];
}

/**
 * Supprime (désactive) un utilisateur
 *
 * @param int $id_utilisateur ID utilisateur
 * @return array Résultat de l'opération
 */
function supprimer_utilisateur(int $id_utilisateur): array
{
    $sql = "UPDATE utilisateurs SET statut = 'supprime' WHERE id = ?";
    $result = db_query($sql, [$id_utilisateur]);

    if ($result) {
        enregistrer_action('suppression_utilisateur', 'Suppression d\'un utilisateur', $id_utilisateur);
        return ['succes' => true, 'message' => 'Utilisateur supprimé avec succès'];
    }

    return ['succes' => false, 'message' => 'Erreur lors de la suppression'];
}

/**
 * Génère un token de réinitialisation de mot de passe
 *
 * @param string $email Email utilisateur
 * @return array Résultat avec token ou erreur
 */
function generer_token_reinitialisation(string $email): array
{
    // Vérification de l'existence de l'utilisateur
    $sql = "SELECT id FROM utilisateurs WHERE email = ? AND statut = 'actif'";
    $utilisateur = db_query($sql, [$email]);

    if (!$utilisateur) {
        return ['succes' => false, 'message' => 'Email non trouvé'];
    }

    // Génération du token
    $token = bin2hex(random_bytes(32));
    $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Sauvegarde du token
    $sql = "INSERT INTO tokens_reinitialisation (email, token, expiration, date_creation)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE token = VALUES(token), expiration = VALUES(expiration)";

    db_query($sql, [$email, $token, $expiration]);

    return ['succes' => true, 'token' => $token, 'message' => 'Token généré avec succès'];
}

/**
 * Vérifie et utilise un token de réinitialisation
 *
 * @param string $token Token à vérifier
 * @param string $nouveau_mot_de_passe Nouveau mot de passe
 * @return array Résultat de l'opération
 */
function utiliser_token_reinitialisation(string $token, string $nouveau_mot_de_passe): array
{
    // Recherche du token valide
    $sql = "SELECT email FROM tokens_reinitialisation
            WHERE token = ? AND expiration > NOW() AND utilise = 0";

    $result = db_query($sql, [$token]);

    if (!$result) {
        return ['succes' => false, 'message' => 'Token invalide ou expiré'];
    }

    $email = $result[0]['email'];

    // Hash du nouveau mot de passe
    $mot_de_passe_hash = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);

    // Mise à jour du mot de passe
    $sql = "UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?";
    db_query($sql, [$mot_de_passe_hash, $email]);

    // Marquage du token comme utilisé
    $sql = "UPDATE tokens_reinitialisation SET utilise = 1 WHERE token = ?";
    db_query($sql, [$token]);

    enregistrer_action('reinitialisation_mot_de_passe', 'Réinitialisation du mot de passe', null);

    return ['succes' => true, 'message' => 'Mot de passe réinitialisé avec succès'];
}
?>