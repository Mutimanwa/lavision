<?php
/**
 * Module d'administration - Gestion des utilisateurs et logs système
 * Sécurité: Rôles, permissions, audit complet
 * Version: 1.0.0
 */

require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/auth.php';

// =============================================
// CONSTANTES ADMINISTRATION
// =============================================

// Rôles système
if(!defined('ROLE_SUPERADMIN')){
define('ROLE_SUPERADMIN', 'superadmin');
}
if(!defined('ROLE_ADMIN')){
define('ROLE_ADMIN', 'admin');
}
if(!defined('ROLE_SECRETAIRE')){
define('ROLE_SECRETAIRE', 'secretaire');
}
if(!defined('ROLE_GESTIONNAIRE')){
define('ROLE_GESTIONNAIRE', 'gestionnaire');
}
if(!defined('ROLE_PROVISEUR')){
define('ROLE_PROVISEUR', 'proviseur');
}
if(!defined('ROLE_PROFESSEUR')){
define('ROLE_PROFESSEUR', 'professeur');
}
// Statuts utilisateur
define('STATUS_ACTIF', 'actif');
define('STATUS_INACTIF', 'inactif');
define('STATUS_SUSPENDU', 'suspendu');
define('STATUS_BLOQUE', 'bloque');

// Types de logs
if(!defined('LOG_INFO')){
define('LOG_INFO', 'info');
}
if(!defined('LOG_WARNING')){
define('LOG_WARNING', 'warning');
}
if(!defined('LOG_ERROR')){
define('LOG_ERROR', 'error');
}
if(!defined('LOG_SECURITY')){
define('LOG_SECURITY', 'security');
}
if(!defined('LOG_AUDIT')){
define('LOG_AUDIT', 'audit');
}

// Catégories de logs
define('LOG_CAT_SYSTEM', 'system');
define('LOG_CAT_AUTH', 'auth');
define('LOG_CAT_USER', 'user');
define('LOG_CAT_SECURITY', 'security');
define('LOG_CAT_DATABASE', 'database');
define('LOG_CAT_APPLICATION', 'application');

// =============================================
// FONCTIONS DE GESTION DES UTILISATEURS
// =============================================

/**
 * Valider les données d'un utilisateur
 */
function admin_valider_utilisateur(array $donnees, bool $nouveau = true): array
{
    $erreurs = [];
    
    // Champs requis pour la création
    if ($nouveau) {
        $champs_requis = ['identifiant', 'email', 'nom', 'prenom', 'role'];
        
        foreach ($champs_requis as $champ) {
            if (empty(trim($donnees[$champ] ?? ''))) {
                $erreurs[$champ] = "Ce champ est requis";
            }
        }
        
        // Vérifier le mot de passe pour les nouveaux utilisateurs
        if (empty($donnees['mot_de_passe'])) {
            $erreurs['mot_de_passe'] = "Le mot de passe est requis";
        }
    }
    
    // Validation de l'identifiant
    if (isset($donnees['identifiant'])) {
        $identifiant = trim($donnees['identifiant']);
        if (strlen($identifiant) < 3) {
            $erreurs['identifiant'] = "L'identifiant doit faire au moins 3 caractères";
        } elseif (strlen($identifiant) > 50) {
            $erreurs['identifiant'] = "L'identifiant ne peut pas dépasser 50 caractères";
        } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $identifiant)) {
            $erreurs['identifiant'] = "L'identifiant ne peut contenir que des lettres, chiffres, tirets et points";
        }
    }
    
    // Validation de l'email
    if (isset($donnees['email'])) {
        $email = trim($donnees['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = "Email invalide";
        } elseif (strlen($email) > 255) {
            $erreurs['email'] = "L'email ne peut pas dépasser 255 caractères";
        }
    }
    
    // Validation du nom
    if (isset($donnees['nom'])) {
        $nom = trim($donnees['nom']);
        if (strlen($nom) < 2) {
            $erreurs['nom'] = "Le nom doit faire au moins 2 caractères";
        } elseif (strlen($nom) > 100) {
            $erreurs['nom'] = "Le nom ne peut pas dépasser 100 caractères";
        }
    }
    
    // Validation du prénom
    if (isset($donnees['prenom'])) {
        $prenom = trim($donnees['prenom']);
        if (strlen($prenom) < 2) {
            $erreurs['prenom'] = "Le prénom doit faire au moins 2 caractères";
        } elseif (strlen($prenom) > 100) {
            $erreurs['prenom'] = "Le prénom ne peut pas dépasser 100 caractères";
        }
    }
    
    // Validation du téléphone
    if (isset($donnees['telephone']) && !empty($donnees['telephone'])) {
        $telephone = trim($donnees['telephone']);
        if (!preg_match('/^[0-9+\s\-\(\)]{8,20}$/', $telephone)) {
            $erreurs['telephone'] = "Numéro de téléphone invalide";
        }
    }
    
    // Validation du rôle
    if (isset($donnees['role'])) {
        $roles_valides = [ROLE_SUPERADMIN, ROLE_ADMIN, ROLE_SECRETAIRE, ROLE_GESTIONNAIRE, ROLE_PROVISEUR];
        if (!in_array($donnees['role'], $roles_valides)) {
            $erreurs['role'] = "Rôle invalide";
        }
    }
    
    // Validation du statut
    if (isset($donnees['statut'])) {
        $statuts_valides = [STATUS_ACTIF, STATUS_INACTIF, STATUS_SUSPENDU, STATUS_BLOQUE];
        if (!in_array($donnees['statut'], $statuts_valides)) {
            $erreurs['statut'] = "Statut invalide";
        }
    }
    
    // Validation des permissions
    if (isset($donnees['permissions']) && !empty($donnees['permissions'])) {
        if (!is_array($donnees['permissions'])) {
            $erreurs['permissions'] = "Les permissions doivent être un tableau";
        } else {
            // Valider chaque permission
            $permissions_valides = [
                PERM_ALL, PERM_VIEW, PERM_CREATE, PERM_EDIT, PERM_DELETE,
                PERM_EXPORT, PERM_IMPORT, PERM_MANAGE_USERS, PERM_MANAGE_SETTINGS
            ];
            
            foreach ($donnees['permissions'] as $perm) {
                if (!in_array($perm, $permissions_valides)) {
                    $erreurs['permissions'] = "Permission invalide détectée";
                    break;
                }
            }
        }
    }
    
    // Validation du mot de passe
    if (isset($donnees['mot_de_passe']) && !empty($donnees['mot_de_passe'])) {
        $password_check = check_password_strength($donnees['mot_de_passe']);
        if (!$password_check['is_strong']) {
            $erreurs['mot_de_passe'] = "Le mot de passe n'est pas assez fort. " . 
                implode(', ', $password_check['messages']);
        }
    }
    
    return $erreurs;
}

/**
 * Récupérer toutes les permissions valides du système
 */
function get_all_valid_permissions(): array
{
    // Les permissions définies dans votre système
    // Adaptez cette liste selon vos besoins réels
    return [
        // Modules/ressources
        'users', 'eleves', 'professeurs', 'classes', 'notes', 'paiements', 
        'settings', 'statistiques', 'absences', 'sanctions', 'emploi_temps',
        'matieres', 'admissions', 'parents',
        
        // Actions/permissions bitwise (si vous les utilisez)
        'view', 'create', 'edit', 'delete', 'export', 'import',
        'manage_users', 'manage_settings',
        
        // Permission spéciale
        '*', // Toutes les permissions
    ];
}

/**
 * Convertir les permissions JSON en tableau
 */
function decode_permissions($permissions_json): array
{
    if (empty($permissions_json) || $permissions_json === '[]' || $permissions_json === 'null') {
        return [];
    }
    
    $permissions = json_decode($permissions_json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($permissions)) {
        return [];
    }
    
    return $permissions;
}


/**
 * Créer un nouvel utilisateur administrateur
 */
function admin_creer_utilisateur(array $donnees): array
{
    // Vérifier les permissions (superadmin ou admin uniquement)
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Empêcher les non-superadmin de créer des superadmin
    if (!has_role(ROLE_SUPERADMIN) && $donnees['role'] === ROLE_SUPERADMIN) {
        return ['success' => false, 'error' => 'Seuls les superadmins peuvent créer des superadmins'];
    }
    
    // Valider les données
    $erreurs = admin_valider_utilisateur($donnees, true);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité de l'identifiant
    $identifiant_existant = db_query_single(
        "SELECT user_id FROM user_admins WHERE identifiant = :identifiant AND deleted_at IS NULL",
        ['identifiant' => trim($donnees['identifiant'])]
    );
    
    if ($identifiant_existant) {
        return [
            'success' => false,
            'error' => "Cet identifiant est déjà utilisé"
        ];
    }
    
    // Vérifier l'unicité de l'email
    $email_existant = db_query_single(
        "SELECT user_id FROM user_admins WHERE email = :email AND deleted_at IS NULL",
        ['email' => trim($donnees['email'])]
    );
    
    if ($email_existant) {
        return [
            'success' => false,
            'error' => "Cet email est déjà utilisé"
        ];
    }
    
    try {
        // Préparer les données pour l'insertion
        $champs = [
            'uuid' => bin2hex(random_bytes(18)),
            'identifiant' => trim($donnees['identifiant']),
            'email' => trim($donnees['email']),
            'mot_de_passe' => hash_password($donnees['mot_de_passe']),
            'nom' => trim($donnees['nom']),
            'prenom' => trim($donnees['prenom']),
            'telephone' => isset($donnees['telephone']) ? trim($donnees['telephone']) : null,
            'role' => $donnees['role'],
            'permissions' => isset($donnees['permissions']) ? 
                json_encode(array_unique($donnees['permissions'])) : null,
            'statut' => $donnees['statut'] ?? STATUS_ACTIF
        ];
        
        // Si c'est un professeur, lier à la table professeurs
        if ($donnees['role'] === 'professeur' && !empty($donnees['professeur_id'])) {
            // Vérifier que le professeur existe et n'a pas déjà de compte
            $professeur = db_query_single(
                "SELECT professeur_id, user_id FROM professeurs WHERE professeur_id = :professeur_id",
                ['professeur_id' => $donnees['professeur_id']]
            );
            
            if (!$professeur) {
                return ['success' => false, 'error' => 'Professeur non trouvé'];
            }
            
            if ($professeur['user_id']) {
                return ['success' => false, 'error' => 'Ce professeur a déjà un compte utilisateur'];
            }
            
            $champs['professeur_id'] = $donnees['professeur_id'];
        }
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO user_admins ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $user_id = db_last_insert_id();
        
        // Si c'est un professeur, mettre à jour la table professeurs
        if ($donnees['role'] === 'professeur' && !empty($donnees['professeur_id'])) {
            db_execute(
                "UPDATE professeurs SET user_id = :user_id WHERE professeur_id = :professeur_id",
                ['user_id' => $user_id, 'professeur_id' => $donnees['professeur_id']]
            );
        }
        
        // Journaliser l'action
        log_action('Utilisateur créé', [
            'user_id' => $user_id,
            'identifiant' => $champs['identifiant'],
            'email' => $champs['email'],
            'role' => $champs['role'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], LOG_CAT_USER);
        
        return [
            'success' => true,
            'user_id' => $user_id,
            'message' => 'Utilisateur créé avec succès'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur admin_creer_utilisateur: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour un utilisateur
 */
function admin_modifier_utilisateur(int $user_id, array $donnees): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        // Un utilisateur peut modifier son propre profil (sauf certains champs)
        if ($user_id != ($_SESSION['user_id'] ?? 0)) {
            return ['success' => false, 'error' => 'Permission refusée'];
        }
    }
    
    // Vérifier que l'utilisateur existe
    $utilisateur_existant = admin_get_utilisateur_byId($user_id);
    if (!$utilisateur_existant) {
        return ['success' => false, 'error' => 'Utilisateur non trouvé'];
    }
    
    // Empêcher les modifications de superadmin par des non-superadmin
    if (!has_role(ROLE_SUPERADMIN) && $utilisateur_existant['role'] === ROLE_SUPERADMIN) {
        return ['success' => false, 'error' => 'Impossible de modifier un superadmin'];
    }
    
    // Empêcher les non-superadmin de créer des superadmin
    if (isset($donnees['role']) && $donnees['role'] === ROLE_SUPERADMIN && !has_role(ROLE_SUPERADMIN)) {
        return ['success' => false, 'error' => 'Seuls les superadmins peuvent attribuer le rôle superadmin'];
    }
    
    // Valider les données
    $erreurs = admin_valider_utilisateur($donnees, false);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité de l'identifiant (si modifié)
    if (isset($donnees['identifiant']) && $donnees['identifiant'] !== $utilisateur_existant['identifiant']) {
        $identifiant_existant = db_query_single(
            "SELECT user_id FROM user_admins 
             WHERE identifiant = :identifiant 
             AND user_id != :user_id 
             AND deleted_at IS NULL",
            ['identifiant' => trim($donnees['identifiant']), 'user_id' => $user_id]
        );
        
        if ($identifiant_existant) {
            return [
                'success' => false,
                'error' => "Cet identifiant est déjà utilisé"
            ];
        }
    }
    
    // Vérifier l'unicité de l'email (si modifié)
    if (isset($donnees['email']) && $donnees['email'] !== $utilisateur_existant['email']) {
        $email_existant = db_query_single(
            "SELECT user_id FROM user_admins 
             WHERE email = :email 
             AND user_id != :user_id 
             AND deleted_at IS NULL",
            ['email' => trim($donnees['email']), 'user_id' => $user_id]
        );
        
        if ($email_existant) {
            return [
                'success' => false,
                'error' => "Cet email est déjà utilisé"
            ];
        }
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['user_id' => $user_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'identifiant', 'email', 'nom', 'prenom', 'telephone',
            'role', 'permissions', 'statut'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                
                if ($champ === 'permissions') {
                    // Pour MySQL JSON, un tableau vide doit être encodé en '[]' ou être NULL
                    if (empty($donnees[$champ])) {
                        $params[$champ] = '[]'; // JSON array vide
                    } else {
                        $params[$champ] = json_encode(array_unique($donnees[$champ]));
                    }
                } elseif (in_array($champ, ['identifiant', 'email', 'nom', 'prenom', 'telephone'])) {
                    $params[$champ] = trim($donnees[$champ]);
                } else {
                    $params[$champ] = $donnees[$champ];
                }
            }
        }
        
        // Gestion du mot de passe (si fourni)
        if (!empty($donnees['mot_de_passe'])) {
            $updates[] = "mot_de_passe = :mot_de_passe";
            $params['mot_de_passe'] = hash_password($donnees['mot_de_passe']);
            
            // Forcer la déconnexion si on change le mot de passe d'un autre utilisateur
            if ($user_id != ($_SESSION['user_id'] ?? 0)) {
                $updates[] = "dernier_login = NULL";
                
                // Invalider les sessions actives
                db_execute(
                    "DELETE FROM user_sessions WHERE user_id = :user_id",
                    ['user_id' => $user_id]
                );
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Ajouter la date de modification
        // $updates[] = "updated_by = :updated_by";
        // $params['updated_by'] = $_SESSION['user_id'] ?? null;
        
        // Construction de la requête
        $sql = "UPDATE user_admins SET " . implode(', ', $updates) . " WHERE user_id = :user_id";
        
        // DEBUG: Afficher la requête SQL et les paramètres
        error_log("SQL UPDATE: " . $sql);
        error_log("Params: " . print_r($params, true));
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            if (isset($donnees['mot_de_passe'])) {
                $changements['mot_de_passe'] = '******';
            }
            
            log_action('Utilisateur modifié', [
                'user_id' => $user_id,
                'identifiant' => $utilisateur_existant['identifiant'],
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], LOG_CAT_USER);
            
            // Mettre à jour la session si c'est l'utilisateur courant
            if ($user_id == ($_SESSION['user_id'] ?? 0)) {
                if (isset($donnees['nom'])) $_SESSION['nom'] = $donnees['nom'];
                if (isset($donnees['prenom'])) $_SESSION['prenom'] = $donnees['prenom'];
                if (isset($donnees['email'])) $_SESSION['email'] = $donnees['email'];
                if (isset($donnees['role'])) $_SESSION['role'] = $donnees['role'];
                if (isset($donnees['permissions'])) $_SESSION['permissions'] = $donnees['permissions'];
            }
            
            return [
                'success' => true,
                'message' => 'Utilisateur mis à jour avec succès',
                'user_id' => $user_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur admin_modifier_utilisateur: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne: ' . $e->getMessage()];
    }
}

/**
 * Supprimer un utilisateur (soft delete)
 */
function admin_supprimer_utilisateur(int $user_id, string $motif = ''): array
{
    // Vérifier les permissions (superadmin uniquement pour la suppression)
    if (!has_role(ROLE_SUPERADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée - Superadmin requis'];
    }
    
    // Vérifier que l'utilisateur existe
    $utilisateur = admin_get_utilisateur($user_id);
    if (!$utilisateur) {
        return ['success' => false, 'error' => 'Utilisateur non trouvé'];
    }
    
    // Empêcher l'auto-suppression
    if ($user_id == ($_SESSION['user_id'] ?? 0)) {
        return ['success' => false, 'error' => 'Impossible de supprimer votre propre compte'];
    }
    
    // Empêcher la suppression du dernier superadmin
    if ($utilisateur['role'] === ROLE_SUPERADMIN) {
        $nombre_superadmins = db_query_single(
            "SELECT COUNT(*) as count FROM user_admins 
             WHERE role = :role AND deleted_at IS NULL",
            ['role' => ROLE_SUPERADMIN]
        );
        
        if ($nombre_superadmins && $nombre_superadmins['count'] <= 1) {
            return [
                'success' => false,
                'error' => 'Impossible de supprimer le dernier superadmin'
            ];
        }
    }
    
    try {
        // Soft delete - marquer comme supprimé
        $sql = "UPDATE user_admins 
                SET deleted_at = NOW(), 
                    deleted_by = :deleted_by,
                    statut = :statut
                WHERE user_id = :user_id";
        
        $success = db_execute($sql, [
            'user_id' => $user_id,
            'deleted_by' => $_SESSION['user_id'] ?? null,
            'statut' => STATUS_INACTIF
        ]);
        
        if ($success) {
            // Invalider les sessions actives
            db_execute(
                "DELETE FROM user_sessions WHERE user_id = :user_id",
                ['user_id' => $user_id]
            );
            
            // Supprimer les tokens "remember me"
            db_execute(
                "DELETE FROM remember_tokens WHERE user_id = :user_id",
                ['user_id' => $user_id]
            );
            
            // Journaliser l'action
            log_action('Utilisateur supprimé', [
                'user_id' => $user_id,
                'identifiant' => $utilisateur['identifiant'],
                'email' => $utilisateur['email'],
                'role' => $utilisateur['role'],
                'motif' => $motif,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], LOG_CAT_USER);
            
            return [
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        
    } catch (Exception $e) {
        error_log("Erreur admin_supprimer_utilisateur: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Réactiver un utilisateur supprimé
 */
function admin_reactiver_utilisateur(int $user_id): array
{
    // Vérifier les permissions (admin ou superadmin)
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'utilisateur existe et est supprimé
    $utilisateur = db_query_single(
        "SELECT user_id, identifiant, email, role 
         FROM user_admins 
         WHERE user_id = :user_id 
         AND deleted_at IS NOT NULL",
        ['user_id' => $user_id]
    );
    
    if (!$utilisateur) {
        return ['success' => false, 'error' => 'Utilisateur non trouvé ou déjà actif'];
    }
    
    try {
        // Réactiver l'utilisateur
        $sql = "UPDATE user_admins 
                SET deleted_at = NULL, 
                    deleted_by = NULL,
                    statut = :statut,
                    updated_by = :updated_by
                WHERE user_id = :user_id";
        
        $success = db_execute($sql, [
            'user_id' => $user_id,
            'statut' => STATUS_ACTIF,
            'updated_by' => $_SESSION['user_id'] ?? null
        ]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Utilisateur réactivé', [
                'user_id' => $user_id,
                'identifiant' => $utilisateur['identifiant'],
                'email' => $utilisateur['email'],
                'role' => $utilisateur['role'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], LOG_CAT_USER);
            
            return [
                'success' => true,
                'message' => 'Utilisateur réactivé avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la réactivation'];
        
    } catch (Exception $e) {
        error_log("Erreur admin_reactiver_utilisateur: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Changer le statut d'un utilisateur
 */
function admin_changer_statut_utilisateur(int $user_id, string $nouveau_statut, string $motif = ''): array
{
    // Vérifier les permissions (admin ou superadmin)
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'utilisateur existe
    $utilisateur = admin_get_utilisateur($user_id);
    if (!$utilisateur) {
        return ['success' => false, 'error' => 'Utilisateur non trouvé'];
    }
    
    // Empêcher les modifications de superadmin par des non-superadmin
    if (!has_role(ROLE_SUPERADMIN) && $utilisateur['role'] === ROLE_SUPERADMIN) {
        return ['success' => false, 'error' => 'Impossible de modifier un superadmin'];
    }
    
    // Vérifier le statut
    $statuts_valides = [STATUS_ACTIF, STATUS_INACTIF, STATUS_SUSPENDU, STATUS_BLOQUE];
    if (!in_array($nouveau_statut, $statuts_valides)) {
        return ['success' => false, 'error' => 'Statut invalide'];
    }
    
    // Empêcher de bloquer/désactiver le dernier superadmin
    if ($utilisateur['role'] === ROLE_SUPERADMIN && 
        in_array($nouveau_statut, [STATUS_INACTIF, STATUS_SUSPENDU, STATUS_BLOQUE])) {
        
        $nombre_superadmins_actifs = db_query_single(
            "SELECT COUNT(*) as count FROM user_admins 
             WHERE role = :role 
             AND statut = :statut_actif
             AND deleted_at IS NULL",
            ['role' => ROLE_SUPERADMIN, 'statut_actif' => STATUS_ACTIF]
        );
        
        if ($nombre_superadmins_actifs && $nombre_superadmins_actifs['count'] <= 1) {
            return [
                'success' => false,
                'error' => 'Impossible de désactiver le dernier superadmin actif'
            ];
        }
    }
    
    try {
        // Changer le statut
        $sql = "UPDATE user_admins 
                SET statut = :statut,
                    updated_by = :updated_by
                WHERE user_id = :user_id";
        
        $success = db_execute($sql, [
            'user_id' => $user_id,
            'statut' => $nouveau_statut,
            'updated_by' => $_SESSION['user_id'] ?? null
        ]);
        
        if ($success) {
            // Si on suspend ou bloque, invalider les sessions
            if (in_array($nouveau_statut, [STATUS_SUSPENDU, STATUS_BLOQUE])) {
                db_execute(
                    "DELETE FROM user_sessions WHERE user_id = :user_id",
                    ['user_id' => $user_id]
                );
                
                // Si l'utilisateur est actuellement connecté, le déconnecter
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
                    session_destroy_complete();
                }
            }
            
            // Journaliser l'action
            log_action('Statut utilisateur changé', [
                'user_id' => $user_id,
                'identifiant' => $utilisateur['identifiant'],
                'ancien_statut' => $utilisateur['statut'],
                'nouveau_statut' => $nouveau_statut,
                'motif' => $motif,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], LOG_CAT_USER);
            
            return [
                'success' => true,
                'message' => "Statut changé à '{$nouveau_statut}' avec succès",
                'user_id' => $user_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors du changement de statut'];
        
    } catch (Exception $e) {
        error_log("Erreur admin_changer_statut_utilisateur: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Réinitialiser le mot de passe d'un utilisateur
 */
function admin_reinitialiser_mot_de_passe(int $user_id, string $nouveau_mot_de_passe = ''): array
{
    // Vérifier les permissions (admin ou superadmin)
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        // Un utilisateur peut réinitialiser son propre mot de passe
        if ($user_id != ($_SESSION['user_id'] ?? 0)) {
            return ['success' => false, 'error' => 'Permission refusée'];
        }
    }
    
    // Vérifier que l'utilisateur existe
    $utilisateur = admin_get_utilisateur($user_id);
    if (!$utilisateur) {
        return ['success' => false, 'error' => 'Utilisateur non trouvé'];
    }
    
    // Empêcher les modifications de superadmin par des non-superadmin
    if (!has_role(ROLE_SUPERADMIN) && $utilisateur['role'] === ROLE_SUPERADMIN) {
        return ['success' => false, 'error' => 'Impossible de modifier un superadmin'];
    }
    
    // Générer un mot de passe aléatoire si non fourni
    if (empty($nouveau_mot_de_passe)) {
        $nouveau_mot_de_passe = generate_random_password(12);
    }
    
    // Vérifier la force du mot de passe
    $password_check = check_password_strength($nouveau_mot_de_passe);
    if (!$password_check['is_strong']) {
        return [
            'success' => false,
            'error' => "Le mot de passe n'est pas assez fort. " . 
                implode(', ', $password_check['messages'])
        ];
    }
    
    try {
        // Mettre à jour le mot de passe
        $sql = "UPDATE user_admins 
                SET mot_de_passe = :mot_de_passe,
                    updated_by = :updated_by,
                    dernier_login = NULL
                WHERE user_id = :user_id";
        
        $success = db_execute($sql, [
            'user_id' => $user_id,
            'mot_de_passe' => hash_password($nouveau_mot_de_passe),
            'updated_by' => $_SESSION['user_id'] ?? null
        ]);
        
        if ($success) {
            // Invalider toutes les sessions actives
            db_execute(
                "DELETE FROM user_sessions WHERE user_id = :user_id",
                ['user_id' => $user_id]
            );
            
            // Supprimer les tokens "remember me"
            db_execute(
                "DELETE FROM remember_tokens WHERE user_id = :user_id",
                ['user_id' => $user_id]
            );
            
            // Journaliser l'action
            log_action('Mot de passe réinitialisé', [
                'user_id' => $user_id,
                'identifiant' => $utilisateur['identifiant'],
                'force_mot_de_passe' => $password_check['level'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], LOG_CAT_USER);
            
            return [
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
                'nouveau_mot_de_passe' => $nouveau_mot_de_passe, // À envoyer par email en production
                'user_id' => $user_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la réinitialisation'];
        
    } catch (Exception $e) {
        error_log("Erreur admin_reinitialiser_mot_de_passe: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir un utilisateur par son UUID
 */
function admin_get_utilisateur(string $uuid): ?array
{
    $sql = "SELECT *
            FROM user_admins 
            WHERE uuid = :uuid 
            AND deleted_at IS NULL";
    
    $utilisateur = db_query_single($sql, ['uuid' => $uuid]);
    
    
    if ($utilisateur) {
        $user_id = $utilisateur['user_id'];
        // Ajouter des informations calculées
        $utilisateur['nom_complet'] = $utilisateur['prenom'] . ' ' . $utilisateur['nom'];
        
        // Décoder les permissions
        if (!empty($utilisateur['permissions'])) {
            $utilisateur['permissions_array'] = json_decode($utilisateur['permissions'], true);
        } else {
            $utilisateur['permissions_array'] = [];
        }
        
        // Ajouter les informations de session
        $utilisateur['sessions_actives'] = admin_get_sessions_utilisateur($user_id);
        
        // Ajouter les statistiques
        $utilisateur['statistiques'] = admin_get_statistiques_utilisateur($user_id);
        
        // Si c'est un professeur, ajouter les informations du professeur
        if ($utilisateur['role'] === 'professeur') {
            $professeur = db_query_single(
                "SELECT p.* FROM professeurs p WHERE p.user_id = :user_id",
                ['user_id' => $user_id]
            );
            $utilisateur['professeur'] = $professeur;
        }
    }
    
    return $utilisateur;
}

/**
 * Obtenir un utilisateur par son ID
 * @param int $user_id
 * @return array|null
 */
function admin_get_utilisateur_byId(int $user_id){
        $sql = "SELECT *
            FROM user_admins 
            WHERE user_id = :user_id 
            AND deleted_at IS NULL";
    
    $utilisateur = db_query_single($sql, ['user_id' => $user_id]);

    return $utilisateur;
}
/**
 * Obtenir un utilisateur par son identifiant
 */
function admin_get_utilisateur_par_identifiant(string $identifiant): ?array
{
    $sql = "SELECT u.* FROM user_admins u 
            WHERE u.identifiant = :identifiant 
            AND u.deleted_at IS NULL";
    
    return db_query_single($sql, ['identifiant' => $identifiant]);
}

/**
 * Obtenir un utilisateur par son email
 */
function admin_get_utilisateur_par_email(string $email): ?array
{
    $sql = "SELECT u.* FROM user_admins u 
            WHERE u.email = :email 
            AND u.deleted_at IS NULL";
    
    return db_query_single($sql, ['email' => $email]);
}

/**
 * Obtenir un utilisateur par son 
 */
function admin_get_utilisateur_par_statut(string $statut = 'actif'): ?array
{
    $sql = "SELECT u.* FROM user_admins u 
            WHERE u.statut = :statut 
            AND u.deleted_at IS NULL";
    
    return db_query($sql, ['statut' => $statut]);
}
/**
 * Rechercher des utilisateurs avec filtres
 */
function admin_rechercher_utilisateurs(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   u.user_id, u.identifiant, u.email, u.nom, u.prenom, 
                   u.role, u.statut, u.dernier_login, u.date_creation,
                   uc.nom as created_by_nom, uc.prenom as created_by_prenom
            FROM user_admins u
            LEFT JOIN user_admins uc ON u.created_by = uc.user_id
            WHERE u.deleted_at IS NULL";
    
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(u.identifiant LIKE :{$key} OR u.email LIKE :{$key} OR u.nom LIKE :{$key} OR u.prenom LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par rôle
    if (!empty($filtres['role'])) {
        $sql .= " AND u.role = :role";
        $params['role'] = $filtres['role'];
    }
    
    // Filtre par statut
    if (!empty($filtres['statut'])) {
        $sql .= " AND u.statut = :statut";
        $params['statut'] = $filtres['statut'];
    }
    
    // Filtre par date de création
    if (!empty($filtres['date_debut'])) {
        $sql .= " AND u.date_creation >= :date_debut";
        $params['date_debut'] = $filtres['date_debut'];
    }
    
    if (!empty($filtres['date_fin'])) {
        $sql .= " AND u.date_creation <= :date_fin";
        $params['date_fin'] = $filtres['date_fin'];
    }
    
    // Filtrer les superadmin (sauf pour les superadmin)
    if (!has_role(ROLE_SUPERADMIN)) {
        $sql .= " AND u.role != '" . ROLE_SUPERADMIN . "'";
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'u.date_creation';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'DESC';
    $sql .= " ORDER BY {$order_by} {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $utilisateurs = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($utilisateurs as &$utilisateur) {
            $utilisateur['nom_complet'] = $utilisateur['prenom'] . ' ' . $utilisateur['nom'];
            $utilisateur['created_by_nom_complet'] = $utilisateur['created_by_nom'] ? 
                $utilisateur['created_by_prenom'] . ' ' . $utilisateur['created_by_nom'] : 'Système';
            
            // Calculer l'inactivité
            if ($utilisateur['dernier_login']) {
                $dernier_login = new DateTime($utilisateur['dernier_login']);
                $maintenant = new DateTime();
                $interval = $maintenant->diff($dernier_login);
                $utilisateur['jours_inactivite'] = $interval->days;
            } else {
                $utilisateur['jours_inactivite'] = null;
            }
        }
        
        return [
            'success' => true,
            'utilisateurs' => $utilisateurs,
            'pagination' => [
                'page' => $page,
                'par_page' => $par_page,
                'total' => $total,
                'total_pages' => $total_pages,
                'has_prev' => $page > 1,
                'has_next' => $page < $total_pages
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erreur admin_rechercher_utilisateurs: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

/**
 * Obtenir les sessions actives d'un utilisateur
 */
function admin_get_sessions_utilisateur(int $user_id): array
{
    $sql = "SELECT session_id, ip_address, user_agent, last_activity, expires_at
            FROM user_sessions
            WHERE user_id = :user_id
            AND expires_at > NOW()
            ORDER BY last_activity DESC";
    
    $sessions = db_query($sql, ['user_id' => $user_id]);
    
    // Ajouter des informations supplémentaires
    foreach ($sessions as &$session) {
        $session['actif'] = (time() - $session['last_activity']) < 1800; // 30 minutes
        $session['derniere_activite'] = date('Y-m-d H:i:s', $session['last_activity']);
        
        // Détecter le navigateur
        $user_agent = $session['user_agent'];
        if (strpos($user_agent, 'Chrome') !== false) {
            $session['navigateur'] = 'Chrome';
        } elseif (strpos($user_agent, 'Firefox') !== false) {
            $session['navigateur'] = 'Firefox';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $session['navigateur'] = 'Safari';
        } elseif (strpos($user_agent, 'Edge') !== false) {
            $session['navigateur'] = 'Edge';
        } else {
            $session['navigateur'] = 'Autre';
        }
    }
    
    return $sessions;
}

/**
 * Obtenir les statistiques d'un utilisateur
 */
function admin_get_statistiques_utilisateur(int $user_id): array
{
    $statistiques = [
        'connexions' => [],
        'actions' => []
    ];
    
    // Nombre de connexions réussies (30 derniers jours)
    $sql_connexions = "SELECT COUNT(*) as count, DATE(dernier_login) as date
                       FROM user_admins
                       WHERE user_id = :user_id
                       AND dernier_login IS NOT NULL
                       AND dernier_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                       GROUP BY DATE(dernier_login)
                       ORDER BY date DESC";
    
    $connexions = db_query($sql_connexions, ['user_id' => $user_id]);
    $statistiques['connexions'] = $connexions;
    
    // Actions récentes (dans les logs)
    $sql_actions = "SELECT action, categorie, date_action, details
                    FROM logs
                    WHERE user_id = :user_id
                    AND date_action >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ORDER BY date_action DESC
                    LIMIT 50";
    
    $actions = db_query($sql_actions, ['user_id' => $user_id]);
    $statistiques['actions'] = $actions;
    
    // Nombre total d'actions
    $total_actions = db_query_single(
        "SELECT COUNT(*) as count FROM logs WHERE user_id = :user_id",
        ['user_id' => $user_id]
    );
    $statistiques['total_actions'] = $total_actions['count'] ?? 0;
    
    // Première connexion
    $premiere_connexion = db_query_single(
        "SELECT MIN(dernier_login) as premiere_connexion 
         FROM user_admins 
         WHERE user_id = :user_id",
        ['user_id' => $user_id]
    );
    $statistiques['premiere_connexion'] = $premiere_connexion['premiere_connexion'] ?? null;
    
    return $statistiques;
}

/**
 * Obtenir les utilisateurs actuellement connectés
 */
function admin_get_utilisateurs_connectes(): array
{
    $sql = "SELECT DISTINCT u.user_id, u.identifiant, u.nom, u.prenom, u.role,
                   MAX(s.last_activity) as derniere_activite,
                   COUNT(s.session_id) as sessions_actives
            FROM user_sessions s
            JOIN user_admins u ON s.user_id = u.user_id
            WHERE s.expires_at > NOW()
            AND u.deleted_at IS NULL
            GROUP BY u.user_id, u.identifiant, u.nom, u.prenom, u.role
            HAVING MAX(s.last_activity) > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 MINUTE))
            ORDER BY derniere_activite DESC";
    
    $utilisateurs = db_query($sql);
    
    // Ajouter des informations supplémentaires
    foreach ($utilisateurs as &$utilisateur) {
        $utilisateur['nom_complet'] = $utilisateur['prenom'] . ' ' . $utilisateur['nom'];
        $utilisateur['derniere_activite_formate'] = date('Y-m-d H:i:s', $utilisateur['derniere_activite']);
        
        // Calculer le temps d'inactivité en minutes
        $inactivite = time() - $utilisateur['derniere_activite'];
        $utilisateur['inactivite_minutes'] = floor($inactivite / 60);
    }
    
    return [
        'success' => true,
        'utilisateurs_connectes' => $utilisateurs,
        'total' => count($utilisateurs),
        'date_releve' => date('Y-m-d H:i:s')
    ];
}

/**
 * Obtenir la liste des utilisateurs
 */
function admin_get_all_utilisateurs(): array
{
    $sql = "SELECT * FROM user_admins WHERE deleted_at IS NULL ORDER BY date_creation DESC";
    
    return db_query($sql);

}
// =============================================
// FONCTIONS DE GESTION DES RÔLES ET PERMISSIONS
// =============================================

/**
 * Obtenir la liste des rôles disponibles
 */
function admin_get_roles(): array
{
    return [
        ROLE_SUPERADMIN => [
            'nom' => 'Super Administrateur',
            'description' => 'Accès complet à toutes les fonctionnalités du système',
            'permissions' => [PERM_ALL],
            'creatable_by' => [ROLE_SUPERADMIN]
        ],
        ROLE_ADMIN => [
            'nom' => 'Administrateur',
            'description' => 'Gestion complète du système (sauf superadmin)',
            'permissions' => [
                PERM_VIEW, PERM_CREATE, PERM_EDIT, PERM_DELETE,
                PERM_EXPORT, PERM_IMPORT, PERM_MANAGE_USERS, PERM_MANAGE_SETTINGS
            ],
            'creatable_by' => [ROLE_SUPERADMIN, ROLE_ADMIN]
        ],
        ROLE_PROVISEUR => [
            'nom' => 'Proviseur/Directeur',
            'description' => 'Gestion académique et suivi des performances',
            'permissions' => [
                PERM_VIEW, PERM_CREATE, PERM_EDIT, PERM_EXPORT,
                PERM_MANAGE_SETTINGS
            ],
            'creatable_by' => [ROLE_SUPERADMIN, ROLE_ADMIN]
        ],
        ROLE_SECRETAIRE => [
            'nom' => 'Secrétaire',
            'description' => 'Gestion des élèves, inscriptions et documents',
            'permissions' => [
                PERM_VIEW, PERM_CREATE, PERM_EDIT, PERM_EXPORT
            ],
            'creatable_by' => [ROLE_SUPERADMIN, ROLE_ADMIN, ROLE_PROVISEUR]
        ],
        ROLE_GESTIONNAIRE => [
            'nom' => 'Gestionnaire Financier',
            'description' => 'Gestion des paiements et finances',
            'permissions' => [
                PERM_VIEW, PERM_CREATE, PERM_EDIT, PERM_EXPORT
            ],
            'creatable_by' => [ROLE_SUPERADMIN, ROLE_ADMIN]
        ],
        ROLE_PROFESSEUR => [
            'nom' => 'Professeur',
            'description' => 'Saisie des notes et gestion des cours',
            'permissions' => [
                PERM_VIEW, PERM_CREATE, PERM_EDIT
            ],
            'creatable_by' => [ROLE_SUPERADMIN, ROLE_ADMIN, ROLE_PROVISEUR]
        ]
    ];
}

/**
 * Obtenir les permissions disponibles
 */
function admin_get_permissions(): array
{
    return [
        PERM_VIEW => [
            'code' => PERM_VIEW,
            'nom' => 'Voir',
            'description' => 'Permission de consulter les données'
        ],
        PERM_CREATE => [
            'code' => PERM_CREATE,
            'nom' => 'Créer',
            'description' => 'Permission de créer de nouvelles données'
        ],
        PERM_EDIT => [
            'code' => PERM_EDIT,
            'nom' => 'Modifier',
            'description' => 'Permission de modifier les données existantes'
        ],
        PERM_DELETE => [
            'code' => PERM_DELETE,
            'nom' => 'Supprimer',
            'description' => 'Permission de supprimer des données'
        ],
        PERM_EXPORT => [
            'code' => PERM_EXPORT,
            'nom' => 'Exporter',
            'description' => 'Permission d\'exporter des données'
        ],
        PERM_IMPORT => [
            'code' => PERM_IMPORT,
            'nom' => 'Importer',
            'description' => 'Permission d\'importer des données'
        ],
        PERM_MANAGE_USERS => [
            'code' => PERM_MANAGE_USERS,
            'nom' => 'Gérer les utilisateurs',
            'description' => 'Permission de gérer les comptes utilisateurs'
        ],
        PERM_MANAGE_SETTINGS => [
            'code' => PERM_MANAGE_SETTINGS,
            'nom' => 'Gérer les paramètres',
            'description' => 'Permission de modifier les paramètres système'
        ]
    ];
}

/**
 * Vérifier si un utilisateur peut créer un rôle
 */
function admin_can_create_role(string $role_a_creer): bool
{
    $roles = admin_get_roles();
    
    if (!isset($roles[$role_a_creer])) {
        return false;
    }
    
    $role_info = $roles[$role_a_creer];
    $user_role = $_SESSION['role'] ?? '';
    
    return in_array($user_role, $role_info['creatable_by']);
}

/**
 * Obtenir les statistiques par rôle
 */
function admin_get_statistiques_roles(): array
{
    $sql = "SELECT role, statut, COUNT(*) as nombre
            FROM user_admins
            WHERE deleted_at IS NULL
            GROUP BY role, statut
            ORDER BY role, statut";
    
    $resultats = db_query($sql);
    
    $statistiques = [];
    $total = 0;
    
    foreach ($resultats as $resultat) {
        $role = $resultat['role'];
        $statut = $resultat['statut'];
        
        if (!isset($statistiques[$role])) {
            $statistiques[$role] = [
                'total' => 0,
                'par_statut' => []
            ];
        }
        
        $statistiques[$role]['total'] += $resultat['nombre'];
        $statistiques[$role]['par_statut'][$statut] = $resultat['nombre'];
        $total += $resultat['nombre'];
    }
    
    return [
        'success' => true,
        'statistiques' => $statistiques,
        'total_utilisateurs' => $total,
        'date_calcul' => date('Y-m-d H:i:s')
    ];
}

// =============================================
// FONCTIONS DE GESTION DES LOGS
// =============================================

/**
 * Journaliser une action dans la base de données
 */
function admin_log_action(
    string $action, 
    array $details = [], 
    string $categorie = LOG_CAT_SYSTEM,
    string $niveau = LOG_INFO,
    ?int $user_id = null
): bool {
    try {
        $sql = "INSERT INTO logs (user_id, niveau_log, categorie, action, details, ip_address, user_agent)
                VALUES (:user_id, :niveau, :categorie, :action, :details, :ip_address, :user_agent)";
        
        $params = [
            'user_id' => $user_id ?? ($_SESSION['user_id'] ?? null),
            'niveau' => $niveau,
            'categorie' => $categorie,
            'action' => $action,
            'details' => !empty($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        return db_execute($sql, $params);
        
    } catch (Exception $e) {
        error_log("Erreur admin_log_action: " . $e->getMessage());
        return false;
    }
}

/**
 * Rechercher dans les logs
 */
function admin_rechercher_logs(array $filtres = [], int $page = 1, int $par_page = 50): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   l.*,
                   u.identifiant, u.nom as user_nom, u.prenom as user_prenom
            FROM logs l
            LEFT JOIN user_admins u ON l.user_id = u.user_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(l.action LIKE :{$key} OR l.details LIKE :{$key} OR u.identifiant LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par utilisateur
    if (!empty($filtres['user_id'])) {
        $sql .= " AND l.user_id = :user_id";
        $params['user_id'] = $filtres['user_id'];
    }
    
    // Filtre par catégorie
    if (!empty($filtres['categorie'])) {
        $sql .= " AND l.categorie = :categorie";
        $params['categorie'] = $filtres['categorie'];
    }
    
    // Filtre par niveau
    if (!empty($filtres['niveau_log'])) {
        $sql .= " AND l.niveau_log = :niveau_log";
        $params['niveau_log'] = $filtres['niveau_log'];
    }
    
    // Filtre par date
    if (!empty($filtres['date_debut'])) {
        $sql .= " AND l.date_action >= :date_debut";
        $params['date_debut'] = $filtres['date_debut'];
    }
    
    if (!empty($filtres['date_fin'])) {
        $sql .= " AND l.date_action <= :date_fin";
        $params['date_fin'] = $filtres['date_fin'];
    }
    
    // Filtre par IP
    if (!empty($filtres['ip_address'])) {
        $sql .= " AND l.ip_address LIKE :ip_address";
        $params['ip_address'] = "%{$filtres['ip_address']}%";
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'l.date_action';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'ASC' ? 'ASC' : 'DESC';
    $sql .= " ORDER BY {$order_by} {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $logs = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($logs as &$log) {
            $log['user_nom_complet'] = $log['user_nom'] ? 
                $log['user_prenom'] . ' ' . $log['user_nom'] : 'Système';
            
            // Décoder les détails JSON
            if (!empty($log['details'])) {
                $log['details_array'] = json_decode($log['details'], true);
            }
            
            // Formater la date
            $log['date_action_formate'] = date('d/m/Y H:i:s', strtotime($log['date_action']));
        }
        
        return [
            'success' => true,
            'logs' => $logs,
            'pagination' => [
                'page' => $page,
                'par_page' => $par_page,
                'total' => $total,
                'total_pages' => $total_pages,
                'has_prev' => $page > 1,
                'has_next' => $page < $total_pages
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erreur admin_rechercher_logs: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

/**
 * Obtenir les statistiques des logs
 */
function admin_get_statistiques_logs(string $periode = '7days'): array
{
    // Déterminer la période
    switch ($periode) {
        case 'today':
            $interval = '1 DAY';
            break;
        case '7days':
            $interval = '7 DAY';
            break;
        case '30days':
            $interval = '30 DAY';
            break;
        case '90days':
            $interval = '90 DAY';
            break;
        default:
            $interval = '7 DAY';
    }
    
    $statistiques = [];
    
    // Logs par catégorie
    $sql_categories = "SELECT categorie, COUNT(*) as nombre
                       FROM logs
                       WHERE date_action >= DATE_SUB(NOW(), INTERVAL {$interval})
                       GROUP BY categorie
                       ORDER BY nombre DESC";
    
    $categories = db_query($sql_categories);
    $statistiques['par_categorie'] = $categories;
    
    // Logs par niveau
    $sql_niveaux = "SELECT niveau_log, COUNT(*) as nombre
                    FROM logs
                    WHERE date_action >= DATE_SUB(NOW(), INTERVAL {$interval})
                    GROUP BY niveau_log
                    ORDER BY 
                        CASE niveau_log
                            WHEN 'error' THEN 1
                            WHEN 'warning' THEN 2
                            WHEN 'security' THEN 3
                            WHEN 'info' THEN 4
                            ELSE 5
                        END";
    
    $niveaux = db_query($sql_niveaux);
    $statistiques['par_niveau'] = $niveaux;
    
    // Logs par utilisateur (top 10)
    $sql_utilisateurs = "SELECT u.identifiant, u.nom, u.prenom, COUNT(l.log_id) as nombre
                         FROM logs l
                         LEFT JOIN user_admins u ON l.user_id = u.user_id
                         WHERE l.date_action >= DATE_SUB(NOW(), INTERVAL {$interval})
                         GROUP BY l.user_id, u.identifiant, u.nom, u.prenom
                         ORDER BY nombre DESC
                         LIMIT 10";
    
    $utilisateurs = db_query($sql_utilisateurs);
    $statistiques['par_utilisateur'] = $utilisateurs;
    
    // Logs par jour (pour graphique)
    $sql_par_jour = "SELECT DATE(date_action) as date, COUNT(*) as nombre
                     FROM logs
                     WHERE date_action >= DATE_SUB(NOW(), INTERVAL {$interval})
                     GROUP BY DATE(date_action)
                     ORDER BY date";
    
    $par_jour = db_query($sql_par_jour);
    $statistiques['par_jour'] = $par_jour;
    
    // Actions les plus fréquentes
    $sql_actions = "SELECT action, COUNT(*) as nombre
                    FROM logs
                    WHERE date_action >= DATE_SUB(NOW(), INTERVAL {$interval})
                    GROUP BY action
                    ORDER BY nombre DESC
                    LIMIT 15";
    
    $actions = db_query($sql_actions);
    $statistiques['actions_frequentes'] = $actions;
    
    // Total des logs
    $total_logs = db_query_single(
        "SELECT COUNT(*) as total FROM logs WHERE date_action >= DATE_SUB(NOW(), INTERVAL {$interval})"
    );
    $statistiques['total_logs'] = $total_logs['total'] ?? 0;
    
    return [
        'success' => true,
        'statistiques' => $statistiques,
        'periode' => $periode,
        'date_debut' => date('Y-m-d', strtotime("-$interval")),
        'date_fin' => date('Y-m-d'),
        'date_calcul' => date('Y-m-d H:i:s')
    ];
}

/**
 * Nettoyer les anciens logs
 */
function admin_nettoyer_logs(int $jours_a_conserver = 90): array
{
    // Vérifier les permissions (admin ou superadmin uniquement)
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    if ($jours_a_conserver < 7) {
        return ['success' => false, 'error' => 'Minimum 7 jours de conservation'];
    }
    
    if ($jours_a_conserver > 365) {
        return ['success' => false, 'error' => 'Maximum 365 jours de conservation'];
    }
    
    try {
        // Calculer la date de coupure
        $date_coupure = date('Y-m-d H:i:s', strtotime("-$jours_a_conserver days"));
        
        // Compter le nombre de logs à supprimer
        $sql_count = "SELECT COUNT(*) as nombre FROM logs WHERE date_action < :date_coupure";
        $result_count = db_query_single($sql_count, ['date_coupure' => $date_coupure]);
        $nombre_a_supprimer = $result_count['nombre'] ?? 0;
        
        if ($nombre_a_supprimer === 0) {
            return [
                'success' => true,
                'message' => 'Aucun log à nettoyer',
                'logs_supprimes' => 0
            ];
        }
        
        // Supprimer les logs
        $sql_delete = "DELETE FROM logs WHERE date_action < :date_coupure";
        $success = db_execute($sql_delete, ['date_coupure' => $date_coupure]);
        
        if ($success) {
            // Journaliser le nettoyage
            admin_log_action(
                'Nettoyage des logs',
                [
                    'jours_conserves' => $jours_a_conserver,
                    'logs_supprimes' => $nombre_a_supprimer,
                    'date_coupure' => $date_coupure
                ],
                LOG_CAT_SYSTEM,
                LOG_INFO,
                $_SESSION['user_id'] ?? null
            );
            
            return [
                'success' => true,
                'message' => "Nettoyage terminé: {$nombre_a_supprimer} logs supprimés",
                'logs_supprimes' => $nombre_a_supprimer,
                'date_coupure' => $date_coupure,
                'jours_conserves' => $jours_a_conserver
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors du nettoyage des logs'];
        
    } catch (Exception $e) {
        error_log("Erreur admin_nettoyer_logs: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Exporter les logs au format CSV
 */
function admin_exporter_logs_csv(array $filtres = []): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        // Récupérer les logs (sans pagination)
        $filtres_export = $filtres;
        unset($filtres_export['page'], $filtres_export['par_page']);
        
        $resultat = admin_rechercher_logs($filtres_export, 1, 10000);
        
        if (!$resultat['success']) {
            return $resultat;
        }
        
        $logs = $resultat['logs'];
        
        // Générer le CSV
        $output = fopen('php://temp', 'r+');
        
        // En-têtes
        $headers = ['Date', 'Utilisateur', 'Catégorie', 'Niveau', 'Action', 'Détails', 'IP', 'User Agent'];
        fputcsv($output, $headers, ';');
        
        // Données
        foreach ($logs as $log) {
            // Nettoyer les détails pour le CSV
            $details = '';
            if (!empty($log['details_array'])) {
                $details = json_encode($log['details_array'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $details = str_replace(["\r", "\n"], ' ', $details); // Supprimer les sauts de ligne
                $details = substr($details, 0, 500); // Limiter la longueur
            }
            
            // Nettoyer le user agent
            $user_agent = substr($log['user_agent'] ?? '', 0, 200);
            
            $row = [
                $log['date_action_formate'],
                $log['user_nom_complet'],
                $log['categorie'],
                $log['niveau_log'],
                $log['action'],
                $details,
                $log['ip_address'] ?? '',
                $user_agent
            ];
            
            fputcsv($output, $row, ';');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        // Journaliser l'export
        admin_log_action(
            'Export des logs CSV',
            [
                'filtres' => $filtres,
                'nombre_logs' => count($logs)
            ],
            LOG_CAT_SYSTEM,
            LOG_INFO,
            $_SESSION['user_id'] ?? null
        );
        
        return [
            'success' => true,
            'data' => $csv,
            'format' => 'csv',
            'extension' => 'csv',
            'mime_type' => 'text/csv',
            'filename' => 'logs_' . date('Ymd_His') . '.csv',
            'count' => count($logs)
        ];
        
    } catch (Exception $e) {
        error_log("Erreur admin_exporter_logs_csv: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'export'];
    }
}

// =============================================
// FONCTIONS DE SURVEILLANCE DE SÉCURITÉ
// =============================================

/**
 * Surveiller les tentatives de connexion suspectes
 */
function admin_surveiller_tentatives_connexion(int $heures = 24): array
{
    $alertes = [];
    
    // Tentatives échouées par IP (plus de 5 en 1 heure)
    $sql_tentatives_ip = "SELECT ip_address, COUNT(*) as tentatives,
                                 MIN(date_action) as premiere_tentative,
                                 MAX(date_action) as derniere_tentative
                          FROM logs
                          WHERE categorie = 'security'
                          AND action LIKE '%Tentative de connexion%'
                          AND niveau_log = 'warning'
                          AND date_action >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                          GROUP BY ip_address
                          HAVING COUNT(*) > 5
                          ORDER BY tentatives DESC";
    
    $tentatives_ip = db_query($sql_tentatives_ip);
    
    if (!empty($tentatives_ip)) {
        foreach ($tentatives_ip as $tentative) {
            $alertes[] = [
                'type' => 'tentatives_ip',
                'severite' => 'haute',
                'message' => "IP {$tentative['ip_address']}: {$tentative['tentatives']} tentatives échouées en 1h",
                'details' => $tentative
            ];
        }
    }
    
    // Comptes avec multiples échecs
    $sql_comptes_echoues = "SELECT details->>'$.identifiant' as identifiant, COUNT(*) as echecs
                            FROM logs
                            WHERE categorie = 'security'
                            AND action = 'Tentative de connexion échouée'
                            AND date_action >= DATE_SUB(NOW(), INTERVAL {$heures} HOUR)
                            GROUP BY details->>'$.identifiant'
                            HAVING COUNT(*) >= 3
                            ORDER BY echecs DESC";
    
    $comptes_echoues = db_query($sql_comptes_echoues);
    
    if (!empty($comptes_echoues)) {
        foreach ($comptes_echoues as $compte) {
            $alertes[] = [
                'type' => 'compte_echoues',
                'severite' => 'moyenne',
                'message' => "Compte {$compte['identifiant']}: {$compte['echecs']} échecs en {$heures}h",
                'details' => $compte
            ];
        }
    }
    
    // Connexions depuis des IP inhabituelles
    $sql_ip_inhabituelles = "SELECT l.ip_address, u.identifiant, COUNT(*) as connexions
                             FROM logs l
                             JOIN user_admins u ON l.user_id = u.user_id
                             WHERE l.categorie = 'auth'
                             AND l.action = 'Connexion réussie'
                             AND l.date_action >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                             AND u.dernier_login IS NOT NULL
                             AND DATEDIFF(NOW(), u.dernier_login) > 7
                             GROUP BY l.ip_address, u.identifiant
                             HAVING COUNT(*) > 0";
    
    $ip_inhabituelles = db_query($sql_ip_inhabituelles);
    
    if (!empty($ip_inhabituelles)) {
        foreach ($ip_inhabituelles as $ip) {
            $alertes[] = [
                'type' => 'ip_inhabituelle',
                'severite' => 'basse',
                'message' => "Connexion inhabituelle pour {$ip['identifiant']} depuis {$ip['ip_address']}",
                'details' => $ip
            ];
        }
    }
    
    // Activité suspecte (actions multiples en peu de temps)
    $sql_activite_suspecte = "SELECT user_id, COUNT(*) as actions,
                                     MIN(date_action) as debut,
                                     MAX(date_action) as fin
                              FROM logs
                              WHERE date_action >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                              AND user_id IS NOT NULL
                              GROUP BY user_id
                              HAVING COUNT(*) > 50
                              ORDER BY actions DESC";
    
    $activite_suspecte = db_query($sql_activite_suspecte);
    
    if (!empty($activite_suspecte)) {
        foreach ($activite_suspecte as $activite) {
            $utilisateur = admin_get_utilisateur($activite['user_id']);
            $nom_complet = $utilisateur ? $utilisateur['nom_complet'] : 'Utilisateur ' . $activite['user_id'];
            
            $alertes[] = [
                'type' => 'activite_suspecte',
                'severite' => 'moyenne',
                'message' => "Activité suspecte: {$activite['actions']} actions en 5min par {$nom_complet}",
                'details' => $activite
            ];
        }
    }
    
    return [
        'success' => true,
        'alertes' => $alertes,
        'total_alertes' => count($alertes),
        'periode_analyse' => "{$heures} heures",
        'date_analyse' => date('Y-m-d H:i:s')
    ];
}

/**
 * Générer un rapport de sécurité
 */
function admin_generer_rapport_securite(string $periode = '7days'): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Statistiques de sécurité
    $stats_securite = [];
    
    // Tentatives de connexion
    $sql_tentatives = "SELECT 
                        SUM(CASE WHEN niveau_log = 'info' THEN 1 ELSE 0 END) as reussies,
                        SUM(CASE WHEN niveau_log = 'warning' THEN 1 ELSE 0 END) as echouees
                       FROM logs
                       WHERE categorie = 'security'
                       AND date_action >= DATE_SUB(NOW(), INTERVAL {$periode})";
    
    $tentatives = db_query_single($sql_tentatives);
    $stats_securite['tentatives_connexion'] = $tentatives;
    
    // Comptes bloqués/suspendus
    $sql_comptes = "SELECT statut, COUNT(*) as nombre
                    FROM user_admins
                    WHERE deleted_at IS NULL
                    AND statut IN ('suspendu', 'bloque')
                    GROUP BY statut";
    
    $comptes = db_query($sql_comptes);
    $stats_securite['comptes_problematiques'] = $comptes;
    
    // Sessions actives
    $sessions_actives = db_query_single(
        "SELECT COUNT(*) as nombre FROM user_sessions WHERE expires_at > NOW()"
    );
    $stats_securite['sessions_actives'] = $sessions_actives['nombre'] ?? 0;
    
    // Logs d'erreur
    $sql_erreurs = "SELECT COUNT(*) as nombre
                    FROM logs
                    WHERE niveau_log = 'error'
                    AND date_action >= DATE_SUB(NOW(), INTERVAL {$periode})";
    
    $erreurs = db_query_single($sql_erreurs);
    $stats_securite['erreurs_systeme'] = $erreurs['nombre'] ?? 0;
    
    // Alertes de sécurité
    $alertes = admin_surveiller_tentatives_connexion(24);
    $stats_securite['alertes_securite'] = $alertes['alertes'] ?? [];
    
    // Recommandations
    $recommandations = [];
    
    if ($stats_securite['tentatives_connexion']['echouees'] > 20) {
        $recommandations[] = "Nombre élevé de tentatives échouées. Vérifier les logs de sécurité.";
    }
    
    $total_comptes_problematiques = array_sum(array_column($comptes, 'nombre'));
    if ($total_comptes_problematiques > 5) {
        $recommandations[] = "Plusieurs comptes sont suspendus ou bloqués. Examiner les causes.";
    }
    
    if ($stats_securite['erreurs_systeme'] > 50) {
        $recommandations[] = "Nombre élevé d'erreurs système. Vérifier la stabilité de l'application.";
    }
    
    if (!empty($alertes['alertes'])) {
        $recommandations[] = "Alertes de sécurité détectées. Examiner les tentatives suspectes.";
    }
    
    // Générer le rapport HTML
    $rapport_html = admin_generer_rapport_securite_html($stats_securite, $recommandations, $periode);
    
    return [
        'success' => true,
        'rapport' => [
            'statistiques' => $stats_securite,
            'recommandations' => $recommandations,
            'periode' => $periode,
            'date_generation' => date('Y-m-d H:i:s'),
            'html' => $rapport_html
        ]
    ];
}

/**
 * Générer le HTML du rapport de sécurité
 */
function admin_generer_rapport_securite_html(array $stats, array $recommandations, string $periode): string
{
    $html = '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Rapport de Sécurité</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1, h2, h3 { color: #2c3e50; }
            .header { text-align: center; margin-bottom: 30px; }
            .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
            .stat-card { background: #f8f9fa; padding: 15px; border-radius: 4px; text-align: center; }
            .stat-value { font-size: 24px; font-weight: bold; }
            .stat-label { color: #7f8c8d; font-size: 14px; }
            .alerte { padding: 10px; margin-bottom: 10px; border-left: 4px solid; border-radius: 4px; }
            .alerte-haute { border-color: #e74c3c; background: #fddede; }
            .alerte-moyenne { border-color: #f39c12; background: #fef5e7; }
            .alerte-basse { border-color: #3498db; background: #e8f4fc; }
            .recommandation { padding: 10px; background: #e8f6f3; border-left: 4px solid #1abc9c; margin-bottom: 10px; }
            .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .table th { background-color: #f2f2f2; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #7f8c8d; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . e(ECOLE_NOM) . '</h1>
            <h2>Rapport de Sécurité</h2>
            <p>Période: ' . e($periode) . ' | Date de génération: ' . date('d/m/Y H:i') . '</p>
        </div>';
    
    // Statistiques principales
    $html .= '
        <div class="section">
            <h3>Statistiques de Sécurité</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">' . e($stats['tentatives_connexion']['reussies'] ?? 0) . '</div>
                    <div class="stat-label">Connexions réussies</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">' . e($stats['tentatives_connexion']['echouees'] ?? 0) . '</div>
                    <div class="stat-label">Tentatives échouées</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">' . e($stats['sessions_actives'] ?? 0) . '</div>
                    <div class="stat-label">Sessions actives</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">' . e($stats['erreurs_systeme'] ?? 0) . '</div>
                    <div class="stat-label">Erreurs système</div>
                </div>
            </div>
        </div>';
    
    // Alertes de sécurité
    if (!empty($stats['alertes_securite'])) {
        $html .= '
            <div class="section">
                <h3>Alertes de Sécurité</h3>';
        
        foreach ($stats['alertes_securite'] as $alerte) {
            $classe = 'alerte-' . $alerte['severite'];
            $html .= '
                <div class="alerte ' . $classe . '">
                    <strong>' . e(ucfirst($alerte['severite'])) . ':</strong> ' . e($alerte['message']) . '
                </div>';
        }
        
        $html .= '
            </div>';
    }
    
    // Comptes problématiques
    if (!empty($stats['comptes_problematiques'])) {
        $html .= '
            <div class="section">
                <h3>Comptes à Surveiller</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Statut</th>
                            <th>Nombre</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($stats['comptes_problematiques'] as $compte) {
            $html .= '
                        <tr>
                            <td>' . e(ucfirst($compte['statut'])) . '</td>
                            <td>' . e($compte['nombre']) . '</td>
                        </tr>';
        }
        
        $html .= '
                    </tbody>
                </table>
            </div>';
    }
    
    // Recommandations
    if (!empty($recommandations)) {
        $html .= '
            <div class="section">
                <h3>Recommandations</h3>';
        
        foreach ($recommandations as $recommandation) {
            $html .= '
                <div class="recommandation">' . e($recommandation) . '</div>';
        }
        
        $html .= '
            </div>';
    }
    
    $html .= '
        <div class="footer">
            <p>Document généré automatiquement par le système de gestion scolaire</p>
            <p>' . e(ECOLE_NOM) . ' - ' . e(ECOLE_ADRESSE) . '</p>
            <p>Rapport confidentiel - À usage interne uniquement</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

// =============================================
// FONCTIONS DE GESTION DES PARAMÈTRES SYSTÈME
// =============================================

/**
 * Obtenir un paramètre système
 */
function admin_get_parametre(string $cle, $valeur_par_defaut = null)
{
    $sql = "SELECT valeur, type FROM parametres WHERE cle = :cle";
    $parametre = db_query_single($sql, ['cle' => $cle]);
    
    if (!$parametre) {
        return $valeur_par_defaut;
    }
    
    // Convertir selon le type
    switch ($parametre['type']) {
        case 'integer':
            return intval($parametre['valeur']);
        case 'boolean':
            return filter_var($parametre['valeur'], FILTER_VALIDATE_BOOLEAN);
        case 'json':
            return json_decode($parametre['valeur'], true);
        case 'array':
            return explode(',', $parametre['valeur']);
        default:
            return $parametre['valeur'];
    }
}

/**
 * Définir un paramètre système
 */
function admin_set_parametre(string $cle, $valeur, string $type = 'string', string $categorie = 'general', string $description = ''): bool
{
    // Vérifier les permissions
    if (!has_role(ROLE_SUPERADMIN) && !has_role(ROLE_ADMIN)) {
        return false;
    }
    
    // Convertir la valeur selon le type
    $valeur_stockee = '';
    
    switch ($type) {
        case 'integer':
            $valeur_stockee = strval(intval($valeur));
            break;
        case 'boolean':
            $valeur_stockee = $valeur ? '1' : '0';
            break;
        case 'json':
            $valeur_stockee = json_encode($valeur, JSON_UNESCAPED_UNICODE);
            break;
        case 'array':
            $valeur_stockee = is_array($valeur) ? implode(',', $valeur) : strval($valeur);
            break;
        default:
            $valeur_stockee = strval($valeur);
    }
    
    try {
        // Vérifier si le paramètre existe
        $existe = db_query_single("SELECT parametre_id FROM parametres WHERE cle = :cle", ['cle' => $cle]);
        
        if ($existe) {
            // Mettre à jour
            $sql = "UPDATE parametres 
                    SET valeur = :valeur, type = :type, categorie = :categorie, 
                        description = :description, date_modif = NOW()
                    WHERE cle = :cle";
        } else {
            // Créer
            $sql = "INSERT INTO parametres (cle, valeur, type, categorie, description)
                    VALUES (:cle, :valeur, :type, :categorie, :description)";
        }
        
        $params = [
            'cle' => $cle,
            'valeur' => $valeur_stockee,
            'type' => $type,
            'categorie' => $categorie,
            'description' => $description
        ];
        
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser
            admin_log_action(
                'Paramètre système modifié',
                ['cle' => $cle, 'type' => $type, 'categorie' => $categorie],
                LOG_CAT_SYSTEM,
                LOG_INFO,
                $_SESSION['user_id'] ?? null
            );
        }
        
        return $success;
        
    } catch (Exception $e) {
        error_log("Erreur admin_set_parametre: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtenir tous les paramètres d'une catégorie
 */
function admin_get_parametres_categorie(string $categorie): array
{
    $sql = "SELECT * FROM parametres WHERE categorie = :categorie ORDER BY cle";
    $parametres = db_query($sql, ['categorie' => $categorie]);
    
    // Convertir les valeurs selon leur type
    foreach ($parametres as &$parametre) {
        switch ($parametre['type']) {
            case 'integer':
                $parametre['valeur'] = intval($parametre['valeur']);
                break;
            case 'boolean':
                $parametre['valeur'] = filter_var($parametre['valeur'], FILTER_VALIDATE_BOOLEAN);
                break;
            case 'json':
                $parametre['valeur'] = json_decode($parametre['valeur'], true);
                break;
            case 'array':
                $parametre['valeur'] = explode(',', $parametre['valeur']);
                break;
        }
    }
    
    return $parametres;
}

/**
 * Obtenir les statistiques système
 */
function admin_get_statistiques_systeme(): array
{
    $stats = [];
    
    // Nombre d'utilisateurs
    $sql_utilisateurs = "SELECT COUNT(*) as total FROM user_admins WHERE deleted_at IS NULL";
    $utilisateurs = db_query_single($sql_utilisateurs);
    $stats['utilisateurs'] = $utilisateurs['total'] ?? 0;
    
    // Nombre d'élèves
    $sql_eleves = "SELECT COUNT(*) as total FROM eleves WHERE statut_etudiant = 'actif'";
    $eleves = db_query_single($sql_eleves);
    $stats['eleves'] = $eleves['total'] ?? 0;
    
    // Nombre de professeurs
    $sql_professeurs = "SELECT COUNT(*) as total FROM professeurs WHERE statut = 'actif'";
    $professeurs = db_query_single($sql_professeurs);
    $stats['professeurs'] = $professeurs['total'] ?? 0;
    
    // Nombre de classes
    $sql_classes = "SELECT COUNT(*) as total FROM classes WHERE statut = 'active'";
    $classes = db_query_single($sql_classes);
    $stats['classes'] = $classes['total'] ?? 0;
    
    // Nombre de matières
    $sql_matieres = "SELECT COUNT(*) as total FROM matieres";
    $matieres = db_query_single($sql_matieres);
    $stats['matieres'] = $matieres['total'] ?? 0;
    
    // Nombre de notes
    $sql_notes = "SELECT COUNT(*) as total FROM notes WHERE est_rectifiee = 0";
    $notes = db_query_single($sql_notes);
    $stats['notes'] = $notes['total'] ?? 0;
    
    // Taille de la base de données (estimation)
    $sql_taille = "SELECT SUM(data_length + index_length) as taille_bytes 
                   FROM information_schema.tables 
                   WHERE table_schema = :database";
    $taille = db_query_single($sql_taille, ['database' => DB_NAME]);
    $stats['taille_base_mo'] = round(($taille['taille_bytes'] ?? 0) / 1048576, 2);
    
    // Espace disque (simulation)
    $stats['espace_disque'] = [
        'total' => '100 GB',
        'utilise' => round($stats['taille_base_mo'] / 1024, 2) . ' GB',
        'libre' => '95.5 GB'
    ];
    
    // Performance (simulation)
    $stats['performance'] = [
        'temps_reponse' => '0.15s',
        'requetes_heure' => '1250',
        'uptime' => '99.8%'
    ];
    
    // Sauvegardes
    $sql_backups = "SELECT COUNT(*) as total FROM backup_logs WHERE statut = 'success'";
    $backups = db_query_single($sql_backups);
    $stats['sauvegardes'] = $backups['total'] ?? 0;
    
    return [
        'success' => true,
        'statistiques' => $stats,
        'date_calcul' => date('Y-m-d H:i:s')
    ];
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Obtenir l'adresse IP du client
 */
function admin_get_client_ip(): string
{
    return get_client_ip();
}

/**
 * Formater une taille en octets lisible
 */
function admin_format_taille(int $octets): string
{
    $unites = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $octets >= 1024 && $i < count($unites) - 1; $i++) {
        $octets /= 1024;
    }
    
    return round($octets, 2) . ' ' . $unites[$i];
}

/**
 * Vérifier la santé du système
 */
function admin_verifier_sante_systeme(): array
{
    $sante = [
        'database' => 'healthy',
        'disk' => 'healthy',
        'memory' => 'healthy',
        'security' => 'healthy',
        'backup' => 'healthy'
    ];
    
    $messages = [];
    $recommandations = [];
    
    try {
        // Vérifier la connexion à la base de données
        $db_check = db_query_single("SELECT 1 as check_value");
        if (!$db_check) {
            $sante['database'] = 'critical';
            $messages[] = 'La connexion à la base de données a échoué';
        }
        
        // Vérifier l'espace disque (simulation)
        $free_space = disk_free_space(__DIR__);
        $total_space = disk_total_space(__DIR__);
        $percent_used = (($total_space - $free_space) / $total_space) * 100;
        
        if ($percent_used > 90) {
            $sante['disk'] = 'critical';
            $messages[] = 'Espace disque presque plein (' . round($percent_used, 1) . '%)';
            $recommandations[] = 'Nettoyer les fichiers temporaires ou augmenter l\'espace disque';
        } elseif ($percent_used > 80) {
            $sante['disk'] = 'warning';
            $messages[] = 'Espace disque limité (' . round($percent_used, 1) . '%)';
        }
        
        // Vérifier la mémoire (simulation)
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        
        // Convertir la limite en octets
        $limit_bytes = admin_parse_memory_limit($memory_limit);
        $percent_memory = ($memory_usage / $limit_bytes) * 100;
        
        if ($percent_memory > 90) {
            $sante['memory'] = 'critical';
            $messages[] = 'Utilisation mémoire élevée (' . round($percent_memory, 1) . '%)';
            $recommandations[] = 'Optimiser le code ou augmenter la mémoire PHP';
        } elseif ($percent_memory > 80) {
            $sante['memory'] = 'warning';
            $messages[] = 'Utilisation mémoire importante (' . round($percent_memory, 1) . '%)';
        }
        
        // Vérifier la sécurité (tentatives récentes)
        $alertes_securite = admin_surveiller_tentatives_connexion(1);
        if (!empty($alertes_securite['alertes'])) {
            $sante['security'] = 'warning';
            $messages[] = 'Alertes de sécurité détectées';
            $recommandations[] = 'Examiner les tentatives de connexion suspectes';
        }
        
        // Vérifier les sauvegardes
        $dernier_backup = db_query_single(
            "SELECT MAX(date_execution) as dernier FROM backup_logs WHERE statut = 'success'"
        );
        
        if ($dernier_backup && $dernier_backup['dernier']) {
            $dernier = new DateTime($dernier_backup['dernier']);
            $maintenant = new DateTime();
            $interval = $maintenant->diff($dernier);
            
            if ($interval->days > 2) {
                $sante['backup'] = 'warning';
                $messages[] = 'Dernière sauvegarde il y a ' . $interval->days . ' jours';
                $recommandations[] = 'Vérifier la configuration des sauvegardes automatiques';
            }
        } else {
            $sante['backup'] = 'critical';
            $messages[] = 'Aucune sauvegarde trouvée';
            $recommandations[] = 'Configurer les sauvegardes automatiques immédiatement';
        }
        
    } catch (Exception $e) {
        error_log("Erreur admin_verifier_sante_systeme: " . $e->getMessage());
        $sante['database'] = 'critical';
        $messages[] = 'Erreur lors de la vérification: ' . $e->getMessage();
    }
    
    // Calculer le score global
    $scores = [
        'healthy' => 100,
        'warning' => 50,
        'critical' => 0
    ];
    
    $score_total = 0;
    foreach ($sante as $composant => $etat) {
        $score_total += $scores[$etat];
    }
    
    $score_global = round($score_total / count($sante));
    
    return [
        'success' => true,
        'sante' => $sante,
        'score_global' => $score_global,
        'messages' => $messages,
        'recommandations' => $recommandations,
        'date_verification' => date('Y-m-d H:i:s')
    ];
}

/**
 * Parser une limite mémoire PHP en octets
 */
function admin_parse_memory_limit(string $limit): int
{
    $value = (int)$limit;
    $unit = strtolower(substr($limit, -1));
    
    switch ($unit) {
        case 'g':
            return $value * 1024 * 1024 * 1024;
        case 'm':
            return $value * 1024 * 1024;
        case 'k':
            return $value * 1024;
        default:
            return $value;
    }
}

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Redéfinir la fonction log_action pour utiliser notre système amélioré
if (!function_exists('log_action')) {
    function log_action(string $action, array $details = [], string $categorie = LOG_CAT_SYSTEM): void
    {
        admin_log_action($action, $details, $categorie, LOG_INFO, $_SESSION['user_id'] ?? null);
    }
}

// Journaliser le chargement du module
admin_log_action('Module administration chargé', ['version' => '1.0.0'], LOG_CAT_SYSTEM, LOG_INFO);