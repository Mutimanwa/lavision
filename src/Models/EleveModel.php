<?php
/**
 * Modèle pour la gestion des élèves
 * Contient toutes les fonctions d'accès aux données élèves
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Services/database.php';

// =============================================
// FONCTIONS CRUD ÉLÈVES
// =============================================

/**
 * Récupère un élève par son ID
 *
 * @param int $id_eleve ID de l'élève
 * @return array|null Données de l'élève ou null si non trouvé
 */
function get_eleve_par_id(int $id_eleve): ?array
{
    $sql = "SELECT e.*, u.email, u.role, u.date_creation as date_inscription,
                   c.nom_classe, c.niveau, c.section,
                   p.nom_complet as nom_parent, p.telephone as tel_parent, p.email as email_parent
            FROM eleves e
            LEFT JOIN utilisateurs u ON e.id_utilisateur = u.id
            LEFT JOIN classes c ON e.id_classe = c.id
            LEFT JOIN parents p ON e.id_parent = p.id
            WHERE e.id = ? AND e.statut != 'supprime'";

    $result = db_query($sql, [$id_eleve]);

    return $result ? $result[0] : null;
}

/**
 * Récupère la liste paginée des élèves avec filtres
 *
 * @param array $filtres Filtres à appliquer
 * @param int $page Numéro de page
 * @param int $par_page Nombre d'éléments par page
 * @param string $tri Champ de tri
 * @param string $ordre Ordre de tri (ASC/DESC)
 * @return array Liste des élèves
 */
function get_eleves_pagines(array $filtres, int $page, int $par_page, string $tri, string $ordre): array
{
    $offset = ($page - 1) * $par_page;

    // Construction de la requête avec filtres
    $where = ["statut_etudiant != 'desiste'"];
    $params = [];

    if (!empty($filtres['nom'])) {
        $where[] = "(nom LIKE ? OR post_nom LIKE ? OR prenom LIKE ?)";
        $params[] = '%' . $filtres['nom'] . '%';
        $params[] = '%' . $filtres['nom'] . '%';
        $params[] = '%' . $filtres['nom'] . '%';
    }

    if(!empty($filtres['nationalite'])){
        $where[] = "nationalite = ?";
        $params[] = $filtres['nationalite'];
    }

    if (!empty($filtres['statut'])) {
        $where[] = "statut_etudiant = ?";
        $params[] = $filtres['statut'];
    }

    if (!empty($filtres['genre'])) {
        $where[] = "genre = ?";
        $params[] = $filtres['genre'];
    }

    $where_clause = implode(' AND ', $where);

    // Validation du champ de tri
    $champs_tri_valides = ['nom', 'post_nom', 'prenom', 'date_naissance', 'date_inscription', 'nom_classe'];
    if (!in_array($tri, $champs_tri_valides)) {
        $tri = 'nom';
    }

    // Validation de l'ordre
    $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';

    $sql = "SELECT * 
            FROM eleves 
            WHERE $where_clause
            ORDER BY $tri $ordre
            LIMIT $par_page OFFSET $offset";

    return db_query($sql, $params);
}

/**
 * Compte le nombre total d'élèves selon les filtres
 *
 * @param array $filtres Filtres à appliquer
 * @return int Nombre total d'élèves
 */
function compter_eleves(array $filtres): int
{
    $where = ["statut_etudiant != 'desiste'"];
    $params = [];

    if (!empty($filtres['nom'])) {
        $where[] = "(nom LIKE ? OR post_nom LIKE ? OR prenom LIKE ?)";
        $params[] = '%' . $filtres['nom'] . '%';
        $params[] = '%' . $filtres['nom'] . '%';
        $params[] = '%' . $filtres['nom'] . '%';
    }

     if(!empty($filtres['nationalite'])){
        $where[] = "nationalite = ?";
        $params[] = $filtres['nationalite'];
    }

    if (!empty($filtres['statut_etudiant'])) {
        $where[] = "statut_etudiant = ?";
        $params[] = $filtres['statut_etudiant'];
    }

    if (!empty($filtres['genre'])) {
        $where[] = "genre = ?";
        $params[] = $filtres['genre'];
    }

    $where_clause = implode(' AND ', $where);
    $sql = "SELECT COUNT(*) as total FROM eleves WHERE $where_clause";

    $result = db_query($sql, $params);
    return $result ? (int)$result[0]['total'] : 0;
}

/**
 * Ajoute un nouvel élève
 *
 * @param array $donnees Données de l'élève
 * @return int|bool ID de l'élève créé ou false en cas d'erreur
 */
function ajouter_eleve(array $donnees): int|bool
{
    // Démarrage de la transaction
    db_begin_transaction();

    try {
        // Insertion dans la table utilisateurs d'abord
        $sql_user = "INSERT INTO utilisateurs (email, mot_de_passe, role, date_creation)
                     VALUES (?, ?, 'eleve', NOW())";

        $mot_de_passe_hash = password_hash($donnees['mot_de_passe'] ?? 'password123', PASSWORD_DEFAULT);
        $id_utilisateur = db_insert($sql_user, [$donnees['email'], $mot_de_passe_hash]);

        if (!$id_utilisateur) {
            throw new Exception("Erreur lors de la création de l'utilisateur");
        }

        // Insertion dans la table eleves
        $sql_eleve = "INSERT INTO eleves (
            id_utilisateur, nom, post_nom, prenom, date_naissance, lieu_naissance,
            genre, groupe_sanguin, adresse, telephone, email, id_classe, id_parent,
            date_inscription, statut, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())";

        $params = [
            $id_utilisateur,
            $donnees['nom'],
            $donnees['post_nom'],
            $donnees['prenom'],
            $donnees['date_naissance'],
            $donnees['lieu_naissance'] ?? null,
            $donnees['genre'],
            $donnees['groupe_sanguin'] ?? null,
            $donnees['adresse'] ?? null,
            $donnees['telephone'] ?? null,
            $donnees['email'],
            // $donnees['id_classe'] ?? null,
            // $donnees['id_parent'] ?? null,
            $donnees['date_inscription'] ?? date('Y-m-d'),
            $donnees['statut'] ?? 'en_attente'
        ];

        $id_eleve = db_insert($sql_eleve, $params);

        if (!$id_eleve) {
            throw new Exception("Erreur lors de la création de l'élève");
        }

        // Validation de la transaction
        db_commit();

        return $id_eleve;

    } catch (Exception $e) {
        // Annulation de la transaction en cas d'erreur
        db_rollback();
        logError('Erreur ajout élève', ['error' => $e->getMessage(), 'donnees' => $donnees]);
        return false;
    }
}

/**
 * Modifie un élève existant
 *
 * @param int $id_eleve ID de l'élève
 * @param array $donnees Nouvelles données
 * @return bool Succès de la modification
 */
function modifier_eleve(int $id_eleve, array $donnees): bool
{
    try {
        $sql = "UPDATE eleves SET
                nom = ?, post_nom = ?, prenom = ?, date_naissance = ?,
                lieu_naissance = ?, genre = ?, groupe_sanguin = ?,
                adresse = ?, telephone = ?, email = ?, id_classe = ?,
                id_parent = ?, statut = ?, updated_at = NOW()
                WHERE id = ?";

        $params = [
            $donnees['nom'],
            $donnees['post_nom'],
            $donnees['prenom'],
            $donnees['date_naissance'],
            $donnees['lieu_naissance'] ?? null,
            $donnees['genre'],
            $donnees['groupe_sanguin'] ?? null,
            $donnees['adresse'] ?? null,
            $donnees['telephone'] ?? null,
            $donnees['email'],
            $donnees['id_classe'] ?? null,
            $donnees['id_parent'] ?? null,
            $donnees['statut'] ?? 'actif',
            $id_eleve
        ];

        $result = db_execute($sql, $params);

        // Mise à jour de l'email dans la table utilisateurs si changé
        if (isset($donnees['email'])) {
            $eleve = get_eleve_par_id($id_eleve);
            if ($eleve && $eleve['email'] !== $donnees['email']) {
                db_execute("UPDATE utilisateurs SET email = ? WHERE id = ?",
                          [$donnees['email'], $eleve['id_utilisateur']]);
            }
        }

        return $result !== false;

    } catch (Exception $e) {
        logError('Erreur modification élève', ['id_eleve' => $id_eleve, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Désinscrit un élève (changement de statut)
 *
 * @param int $id_eleve ID de l'élève
 * @return bool Succès de la désinscription
 */
function desinscrire_eleve(int $id_eleve): bool
{
    try {
        $sql = "UPDATE eleves SET statut_etudiant = 'desiste', date_modif = NOW() WHERE id = ?";
        return db_execute($sql, [$id_eleve]) !== false;
    } catch (Exception $e) {
        logError('Erreur désinscription élève', ['id_eleve' => $id_eleve, 'error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Supprime définitivement un élève (utiliser avec précaution)
 *
 * @param int $id_eleve ID de l'élève
 * @return bool Succès de la suppression
 */
function supprimer_eleve_definitivement(int $id_eleve): bool
{
    // Récupération des infos avant suppression
    $eleve = get_eleve_par_id($id_eleve);
    if (!$eleve) {
        return false;
    }

    db_begin_transaction();

    try {
        // Suppression de l'élève
        db_execute("UPDATE eleves SET statut = 'supprime', updated_at = NOW() WHERE id = ?", [$id_eleve]);

        // Suppression de l'utilisateur associé (optionnel, selon la politique)
        // db_execute("DELETE FROM utilisateurs WHERE id = ?", [$eleve['id_utilisateur']]);

        db_commit();
        return true;

    } catch (Exception $e) {
        db_rollback();
        logError('Erreur suppression élève', ['id_eleve' => $id_eleve, 'error' => $e->getMessage()]);
        return false;
    }
}

// =============================================
// FONCTIONS DE RECHERCHE ET FILTRES
// =============================================

/**
 * Recherche des élèves par nom
 *
 * @param string $recherche Terme de recherche
 * @param int $limit Nombre maximum de résultats
 * @return array Liste des élèves trouvés
 */
function rechercher_eleves(string $recherche, int $limit = 10): array
{
    $sql = "SELECT e.id, CONCAT(e.nom, ' ', e.post_nom, ' ', e.prenom) as nom_complet,
                   c.nom_classe, e.statut
            FROM eleves e
            LEFT JOIN classes c ON e.id_classe = c.id
            WHERE e.statut != 'supprime'
            AND (e.nom LIKE ? OR e.post_nom LIKE ? OR e.prenom LIKE ?)
            ORDER BY e.nom, e.post_nom, e.prenom
            LIMIT ?";

    $params = [
        '%' . $recherche . '%',
        '%' . $recherche . '%',
        '%' . $recherche . '%',
        $limit
    ];

    return db_query($sql, $params);
}

/**
 * Récupère les élèves d'une classe spécifique
 *
 * @param int $id_classe ID de la classe
 * @return array Liste des élèves
 */
function get_eleves_par_classe(int $id_classe): array
{
    $sql = "SELECT e.*, CONCAT(e.nom, ' ', e.post_nom, ' ', e.prenom) as nom_complet
            FROM eleves e
            WHERE e.id_classe = ? AND e.statut = 'actif'
            ORDER BY e.nom, e.post_nom, e.prenom";

    return db_query($sql, [$id_classe]);
}

/**
 * Récupère les statistiques des élèves
 *
 * @return array Statistiques
 */
function get_statistiques_eleves(): array
{
    $stats = [];

    // Total par statut
    $sql = "SELECT statut, COUNT(*) as nombre FROM eleves WHERE statut != 'supprime' GROUP BY statut";
    $stats['par_statut'] = db_query($sql);

    // Total par genre
    $sql = "SELECT genre, COUNT(*) as nombre FROM eleves WHERE statut != 'supprime' GROUP BY genre";
    $stats['par_genre'] = db_query($sql);

    // Total par classe
    $sql = "SELECT c.nom_classe, COUNT(e.id) as nombre
            FROM classes c
            LEFT JOIN eleves e ON c.id = e.id_classe AND e.statut = 'actif'
            GROUP BY c.id, c.nom_classe
            ORDER BY c.nom_classe";
    $stats['par_classe'] = db_query($sql);

    return $stats;
}

// =============================================
// FONCTIONS LIÉES AUX DONNÉES ÉLÈVES
// =============================================

/**
 * Récupère les notes d'un élève
 *
 * @param int $id_eleve ID de l'élève
 * @return array Notes de l'élève
 */
function get_notes_eleve(int $id_eleve): array
{
    $sql = "SELECT n.*, m.nom_matiere, m.coefficient, c.nom_classe,
                   CONCAT(p.nom, ' ', p.prenom) as nom_professeur
            FROM notes n
            JOIN matieres m ON n.id_matiere = m.id
            LEFT JOIN classes c ON n.id_classe = c.id
            LEFT JOIN professeurs p ON n.id_professeur = p.id
            WHERE n.id_eleve = ?
            ORDER BY n.date_evaluation DESC, m.nom_matiere";

    return db_query($sql, [$id_eleve]);
}

/**
 * Récupère les présences d'un élève
 *
 * @param int $id_eleve ID de l'élève
 * @return array Présences de l'élève
 */
function get_presences_eleve(int $id_eleve): array
{
    $sql = "SELECT p.*, m.nom_matiere, c.nom_classe,
                   CONCAT(prof.nom, ' ', prof.prenom) as nom_professeur
            FROM presences p
            JOIN matieres m ON p.id_matiere = m.id
            LEFT JOIN classes c ON p.id_classe = c.id
            LEFT JOIN professeurs prof ON p.id_professeur = prof.id
            WHERE p.id_eleve = ?
            ORDER BY p.date_cours DESC";

    return db_query($sql, [$id_eleve]);
}

/**
 * Récupère les paiements d'un élève
 *
 * @param int $id_eleve ID de l'élève
 * @return array Paiements de l'élève
 */
function get_paiements_eleve(int $id_eleve): array
{
    $sql = "SELECT p.*, f.nom_frais, f.montant as montant_frais
            FROM paiements p
            JOIN frais_scolaires f ON p.id_frais = f.id
            WHERE p.id_eleve = ?
            ORDER BY p.date_paiement DESC";

    return db_query($sql, [$id_eleve]);
}

?>