<?php
/**
 * Modèle pour la gestion du personnel
 * Gère les données des professeurs, administrateurs et autres membres du personnel
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Services/database.php';

// =============================================
// CONSTANTES DE BASE DE DONNÉES
// =============================================

/**
 * Tables utilisées par le module personnel
 */
define('TABLE_PROFESSEURS', 'professeurs');
define('TABLE_USER_ADMINS', 'user_admins');
define('TABLE_PERSONNEL_LOGS', 'logs');

// =============================================
// FONCTIONS DE GESTION DES PROFESSEURS
// =============================================

/**
 * Récupère tous les professeurs avec pagination
 */
function get_professeurs_pagines(int $page = 1, int $par_page = 20, array $filtres = []): array
{
    $pdo = get_db_connection();
    $offset = ($page - 1) * $par_page;

    try {
        $where_conditions = [];
        $params = [];

        // Filtres
        if (!empty($filtres['statut'])) {
            $where_conditions[] = "p.statut = ?";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['specialite'])) {
            $where_conditions[] = "p.specialite LIKE ?";
            $params[] = '%' . $filtres['specialite'] . '%';
        }

        if (!empty($filtres['recherche'])) {
            $where_conditions[] = "(p.nom LIKE ? OR p.post_nom LIKE ? OR p.prenom LIKE ? OR p.matricule_prof LIKE ?)";
            $search_term = '%' . $filtres['recherche'] . '%';
            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        // Requête principale
        $stmt = $pdo->prepare("
            SELECT
                p.professeur_id,
                p.matricule_prof,
                p.nom,
                p.post_nom,
                p.prenom,
                CONCAT(p.nom, ' ', p.post_nom, ' ', p.prenom) as nom_complet,
                p.date_naissance,
                TIMESTAMPDIFF(YEAR, p.date_naissance, CURDATE()) as age,
                p.genre,
                p.telephone,
                p.email,
                p.specialite,
                p.diplome,
                p.date_embauche,
                p.statut,
                p.type_contrat,
                p.salaire_base,
                c.libelle as classe_tuteur,
                COUNT(DISTINCT edt.edt_id) as nombre_cours
            FROM " . TABLE_PROFESSEURS . " p
            LEFT JOIN classes c ON p.professeur_id = c.tuteur_id
            LEFT JOIN emploi_du_temps edt ON p.professeur_id = edt.professeur_id
            {$where_clause}
            GROUP BY p.professeur_id
            ORDER BY p.nom ASC, p.post_nom ASC, p.prenom ASC
            LIMIT ? OFFSET ?
        ");

        $params[] = $par_page;
        $params[] = $offset;

        $stmt->execute($params);
        $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compter le total pour la pagination
        $stmt_count = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM " . TABLE_PROFESSEURS . " p
            {$where_clause}
        ");

        array_pop($params); // Retirer LIMIT
        array_pop($params); // Retirer OFFSET
        $stmt_count->execute($params);
        $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'professeurs' => $professeurs,
            'total' => $total,
            'pages' => ceil($total / $par_page),
            'page_actuelle' => $page
        ];

    } catch (PDOException $e) {
        logError('Erreur récupération professeurs paginés', ['error' => $e->getMessage(), 'filtres' => $filtres]);
        return ['professeurs' => [], 'total' => 0, 'pages' => 0, 'page_actuelle' => $page];
    }
}

/**
 * Récupère un professeur par son ID
 */
function get_professeur_by_id(int $professeur_id): ?array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT
                p.*,
                c.libelle as classe_tuteur,
                u.identifiant,
                u.email as email_admin,
                u.role,
                u.statut as statut_admin
            FROM " . TABLE_PROFESSEURS . " p
            LEFT JOIN classes c ON p.professeur_id = c.tuteur_id
            LEFT JOIN " . TABLE_USER_ADMINS . " u ON p.user_id = u.user_id
            WHERE p.professeur_id = ?
        ");

        $stmt->execute([$professeur_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    } catch (PDOException $e) {
        logError('Erreur récupération professeur par ID', ['error' => $e->getMessage(), 'professeur_id' => $professeur_id]);
        return null;
    }
}

/**
 * Crée un nouveau professeur
 */
function create_professeur(array $data): bool|int
{
    $pdo = get_db_connection();

    try {
        $pdo->beginTransaction();

        // Validation des données
        if (!validate_professeur_data($data)) {
            return false;
        }

        // Insertion du professeur
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_PROFESSEURS . " (
                matricule_prof, nom, post_nom, prenom, date_naissance, lieu_naissance,
                genre, nationalite, telephone, email, adresse, specialite, diplome,
                date_embauche, statut, type_contrat, salaire_base, banque, numero_compte
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['matricule_prof'],
            $data['nom'],
            $data['post_nom'],
            $data['prenom'],
            $data['date_naissance'] ?? null,
            $data['lieu_naissance'] ?? null,
            $data['genre'] ?? 'Autre',
            $data['nationalite'] ?? 'Congolaise',
            $data['telephone'],
            $data['email'] ?? null,
            $data['adresse'] ?? null,
            $data['specialite'] ?? null,
            $data['diplome'] ?? null,
            $data['date_embauche'] ?? date('Y-m-d'),
            $data['statut'] ?? 'actif',
            $data['type_contrat'] ?? 'contractuel',
            $data['salaire_base'] ?? 0,
            $data['banque'] ?? null,
            $data['numero_compte'] ?? null
        ]);

        $professeur_id = $pdo->lastInsertId();

        // Créer un compte utilisateur si demandé
        if (!empty($data['creer_compte'])) {
            $user_id = create_user_account_for_professeur($professeur_id, $data);
            if ($user_id) {
                $stmt_update = $pdo->prepare("UPDATE " . TABLE_PROFESSEURS . " SET user_id = ? WHERE professeur_id = ?");
                $stmt_update->execute([$user_id, $professeur_id]);
            }
        }

        $pdo->commit();
        logAction('Création professeur', "Professeur {$data['nom']} {$data['prenom']} créé", ['professeur_id' => $professeur_id]);
        return $professeur_id;

    } catch (PDOException $e) {
        $pdo->rollBack();
        logError('Erreur création professeur', ['error' => $e->getMessage(), 'data' => $data]);
        return false;
    }
}

/**
 * Met à jour un professeur
 */
function update_professeur(int $professeur_id, array $data): bool
{
    $pdo = get_db_connection();

    try {
        $pdo->beginTransaction();

        // Validation des données
        if (!validate_professeur_data($data, false)) {
            return false;
        }

        // Mise à jour du professeur
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_PROFESSEURS . " SET
                matricule_prof = ?, nom = ?, post_nom = ?, prenom = ?, date_naissance = ?,
                lieu_naissance = ?, genre = ?, nationalite = ?, telephone = ?, email = ?,
                adresse = ?, specialite = ?, diplome = ?, date_embauche = ?, statut = ?,
                type_contrat = ?, salaire_base = ?, banque = ?, numero_compte = ?,
                date_modif = CURRENT_TIMESTAMP
            WHERE professeur_id = ?
        ");

        $stmt->execute([
            $data['matricule_prof'],
            $data['nom'],
            $data['post_nom'],
            $data['prenom'],
            $data['date_naissance'] ?? null,
            $data['lieu_naissance'] ?? null,
            $data['genre'] ?? 'Autre',
            $data['nationalite'] ?? 'Congolaise',
            $data['telephone'],
            $data['email'] ?? null,
            $data['adresse'] ?? null,
            $data['specialite'] ?? null,
            $data['diplome'] ?? null,
            $data['date_embauche'] ?? null,
            $data['statut'] ?? 'actif',
            $data['type_contrat'] ?? 'contractuel',
            $data['salaire_base'] ?? 0,
            $data['banque'] ?? null,
            $data['numero_compte'] ?? null,
            $professeur_id
        ]);

        $pdo->commit();
        logAction('Modification professeur', "Professeur ID {$professeur_id} modifié", ['professeur_id' => $professeur_id]);
        return true;

    } catch (PDOException $e) {
        $pdo->rollBack();
        logError('Erreur modification professeur', ['error' => $e->getMessage(), 'professeur_id' => $professeur_id, 'data' => $data]);
        return false;
    }
}

/**
 * Supprime un professeur (soft delete)
 */
function delete_professeur(int $professeur_id): bool
{
    $pdo = get_db_connection();

    try {
        // Vérifier si le professeur a des cours actifs
        $stmt_check = $pdo->prepare("
            SELECT COUNT(*) as count FROM emploi_du_temps
            WHERE professeur_id = ? AND statut = 'actif'
        ");
        $stmt_check->execute([$professeur_id]);
        $count = $stmt_check->fetch(PDO::FETCH_ASSOC)['count'];

        if ($count > 0) {
            logError('Impossible de supprimer professeur avec cours actifs', ['professeur_id' => $professeur_id]);
            return false;
        }

        // Suppression douce
        $stmt = $pdo->prepare("UPDATE " . TABLE_PROFESSEURS . " SET statut = 'inactif' WHERE professeur_id = ?");
        $stmt->execute([$professeur_id]);

        logAction('Suppression professeur', "Professeur ID {$professeur_id} désactivé", ['professeur_id' => $professeur_id]);
        return true;

    } catch (PDOException $e) {
        logError('Erreur suppression professeur', ['error' => $e->getMessage(), 'professeur_id' => $professeur_id]);
        return false;
    }
}

// =============================================
// FONCTIONS DE GESTION DES ADMINISTRATEURS
// =============================================

/**
 * Récupère tous les administrateurs avec pagination
 */
function get_administrateurs_pagines(int $page = 1, int $par_page = 20, array $filtres = []): array
{
    $pdo = get_db_connection();
    $offset = ($page - 1) * $par_page;

    try {
        $where_conditions = [];
        $params = [];

        // Filtres
        if (!empty($filtres['role'])) {
            $where_conditions[] = "u.role = ?";
            $params[] = $filtres['role'];
        }

        if (!empty($filtres['statut'])) {
            $where_conditions[] = "u.statut = ?";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['recherche'])) {
            $where_conditions[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR u.identifiant LIKE ? OR u.email LIKE ?)";
            $search_term = '%' . $filtres['recherche'] . '%';
            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        // Requête principale
        $stmt = $pdo->prepare("
            SELECT
                u.user_id,
                u.uuid,
                u.identifiant,
                u.email,
                u.nom,
                u.prenom,
                CONCAT(u.nom, ' ', u.prenom) as nom_complet,
                u.telephone,
                u.photo,
                u.role,
                u.permissions,
                u.statut,
                u.dernier_login,
                u.date_creation,
                p.professeur_id,
                p.matricule_prof,
                p.specialite
            FROM " . TABLE_USER_ADMINS . " u
            LEFT JOIN " . TABLE_PROFESSEURS . " p ON u.user_id = p.user_id
            {$where_clause}
            ORDER BY u.nom ASC, u.prenom ASC
            LIMIT ? OFFSET ?
        ");

        $params[] = $par_page;
        $params[] = $offset;

        $stmt->execute($params);
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compter le total pour la pagination
        $stmt_count = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM " . TABLE_USER_ADMINS . " u
            {$where_clause}
        ");

        array_pop($params); // Retirer LIMIT
        array_pop($params); // Retirer OFFSET
        $stmt_count->execute($params);
        $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'administrateurs' => $admins,
            'total' => $total,
            'pages' => ceil($total / $par_page),
            'page_actuelle' => $page
        ];

    } catch (PDOException $e) {
        logError('Erreur récupération administrateurs paginés', ['error' => $e->getMessage(), 'filtres' => $filtres]);
        return ['administrateurs' => [], 'total' => 0, 'pages' => 0, 'page_actuelle' => $page];
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Valide les données d'un professeur
 */
function validate_professeur_data(array $data, bool $is_creation = true): bool
{
    // Validation de base
    if (empty($data['matricule_prof']) || empty($data['nom']) || empty($data['prenom']) || empty($data['telephone'])) {
        return false;
    }

    // Validation du matricule (unique)
    if ($is_creation) {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM " . TABLE_PROFESSEURS . " WHERE matricule_prof = ?");
        $stmt->execute([$data['matricule_prof']]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return false; // Matricule déjà utilisé
        }
    }

    // Validation email si fourni
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return true;
}

/**
 * Crée un compte utilisateur pour un professeur
 */
function create_user_account_for_professeur(int $professeur_id, array $data): ?int
{
    $pdo = get_db_connection();

    try {
        // Générer un identifiant unique
        $identifiant = 'prof_' . $data['matricule_prof'];

        // Mot de passe par défaut (à changer)
        $mot_de_passe = password_hash('Prof123!', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_USER_ADMINS . " (
                identifiant, email, mot_de_passe, nom, prenom, telephone, role, permissions
            ) VALUES (?, ?, ?, ?, ?, ?, 'professeur', '[]')
        ");

        $stmt->execute([
            $identifiant,
            $data['email'] ?? null,
            $mot_de_passe,
            $data['nom'],
            $data['prenom'],
            $data['telephone'],
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur création compte utilisateur professeur', ['error' => $e->getMessage(), 'professeur_id' => $professeur_id]);
        return null;
    }
}

/**
 * Récupère les statistiques du personnel
 */
function get_statistiques_personnel(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT
                (SELECT COUNT(*) FROM " . TABLE_PROFESSEURS . " WHERE statut = 'actif') as professeurs_actifs,
                (SELECT COUNT(*) FROM " . TABLE_PROFESSEURS . " WHERE statut = 'inactif') as professeurs_inactifs,
                (SELECT COUNT(*) FROM " . TABLE_USER_ADMINS . " WHERE statut = 'actif') as admins_actifs,
                (SELECT COUNT(*) FROM " . TABLE_USER_ADMINS . " WHERE role = 'professeur') as comptes_professeurs,
                (SELECT COUNT(*) FROM " . TABLE_PROFESSEURS . " WHERE type_contrat = 'titulaire') as titulaires,
                (SELECT COUNT(*) FROM " . TABLE_PROFESSEURS . " WHERE type_contrat = 'contractuel') as contractuels
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération statistiques personnel', ['error' => $e->getMessage()]);
        return [];
    }
}
?>