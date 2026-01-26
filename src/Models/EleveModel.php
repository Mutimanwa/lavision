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
 * @param int $eleve_id ID de l'élève
 * @return array|null Données de l'élève ou null si non trouvé
 */
function get_eleve_par_id(int $eleve_id): ?array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT e.*, adm.class_id, c.libelle as classe_nom,
                   n.nom_niveau, s.nom_section, a.annee_libelle,
                   GROUP_CONCAT(DISTINCT CONCAT(p.prenom, ' ', p.nom) SEPARATOR '; ') as parents_info
            FROM eleves e
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            LEFT JOIN classes c ON adm.class_id = c.class_id
            LEFT JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            LEFT JOIN annees_scolaire a ON c.annee_id = a.annee_id
            LEFT JOIN student_parents sp ON e.eleve_id = sp.eleve_id
            LEFT JOIN parents p ON sp.parent_id = p.parent_id
            WHERE e.eleve_id = ? AND e.statut_etudiant != 'desiste'
            GROUP BY e.eleve_id
        ");

        $stmt->execute([$eleve_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    } catch (PDOException $e) {
        logError('Erreur récupération élève par ID', [
            'eleve_id' => $eleve_id,
            'error' => $e->getMessage()
        ]);
        return null;
    }
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
    $pdo = get_db_connection();

    try {
        $offset = ($page - 1) * $par_page;

        // Construction de la requête avec filtres
        $where = ["e.statut_etudiant != 'desiste'"];
        $params = [];

        if (!empty($filtres['nom'])) {
            $where[] = "(e.nom LIKE ? OR e.post_nom LIKE ? OR e.prenom LIKE ?)";
            $params[] = '%' . $filtres['nom'] . '%';
            $params[] = '%' . $filtres['nom'] . '%';
            $params[] = '%' . $filtres['nom'] . '%';
        }

        if (!empty($filtres['nationalite'])) {
            $where[] = "e.nationalite = ?";
            $params[] = $filtres['nationalite'];
        }

        if (!empty($filtres['statut'])) {
            $where[] = "e.statut_etudiant = ?";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['genre'])) {
            $where[] = "e.genre = ?";
            $params[] = $filtres['genre'];
        }

        if (!empty($filtres['classe'])) {
            $where[] = "adm.class_id = ?";
            $params[] = $filtres['classe'];
        }

        $where_clause = implode(' AND ', $where);

        // Validation du champ de tri
        $champs_tri_valides = ['nom', 'post_nom', 'prenom', 'date_naissance', 'date_inscription', 'libelle'];
        if (!in_array($tri, $champs_tri_valides)) {
            $tri = 'nom';
        }

        // Validation de l'ordre
        $ordre = strtoupper($ordre) === 'DESC' ? 'DESC' : 'ASC';

        $stmt = $pdo->prepare("
            SELECT e.*, adm.class_id, c.libelle as classe_nom,
                   n.nom_niveau, s.nom_section, a.annee_libelle,
                   GROUP_CONCAT(DISTINCT CONCAT(p.prenom, ' ', p.nom) SEPARATOR '; ') as parents_info
            FROM eleves e
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            LEFT JOIN classes c ON adm.class_id = c.class_id
            LEFT JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            LEFT JOIN annees_scolaire a ON c.annee_id = a.annee_id
            LEFT JOIN student_parents sp ON e.eleve_id = sp.eleve_id
            LEFT JOIN parents p ON sp.parent_id = p.parent_id
            WHERE $where_clause
            GROUP BY e.eleve_id
            ORDER BY e.$tri $ordre
            LIMIT ? OFFSET ?
        ");

        $params[] = $par_page;
        $params[] = $offset;

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération élèves paginés', [
            'filtres' => $filtres,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Compte le nombre total d'élèves selon les filtres
 *
 * @param array $filtres Filtres à appliquer
 * @return int Nombre total d'élèves
 */
function compter_eleves(array $filtres): int
{
    $pdo = get_db_connection();

    try {
        $where = ["e.statut_etudiant != 'desiste'"];
        $params = [];

        if (!empty($filtres['nom'])) {
            $where[] = "(e.nom LIKE ? OR e.post_nom LIKE ? OR e.prenom LIKE ?)";
            $params[] = '%' . $filtres['nom'] . '%';
            $params[] = '%' . $filtres['nom'] . '%';
            $params[] = '%' . $filtres['nom'] . '%';
        }

        if (!empty($filtres['nationalite'])) {
            $where[] = "e.nationalite = ?";
            $params[] = $filtres['nationalite'];
        }

        if (!empty($filtres['statut'])) {
            $where[] = "e.statut_etudiant = ?";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['genre'])) {
            $where[] = "e.genre = ?";
            $params[] = $filtres['genre'];
        }

        if (!empty($filtres['classe'])) {
            $where[] = "adm.class_id = ?";
            $params[] = $filtres['classe'];
        }

        $where_clause = implode(' AND ', $where);

        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT e.eleve_id) as total
            FROM eleves e
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            WHERE $where_clause
        ");

        $stmt->execute($params);
        return (int) $stmt->fetchColumn();

    } catch (PDOException $e) {
        logError('Erreur comptage élèves', [
            'filtres' => $filtres,
            'error' => $e->getMessage()
        ]);
        return 0;
    }
}

/**
 * Ajoute un nouvel élève
 *
 * @param array $donnees Données de l'élève
 * @return int|bool ID de l'élève créé ou false en cas d'erreur
 */
function ajouter_eleve(array $donnees): int|bool
{
    $pdo = get_db_connection();

    try {
        // Générer un matricule unique
        $matricule = generer_matricule_eleve();

        $stmt = $pdo->prepare("
            INSERT INTO eleves (
                matricule, nom, post_nom, prenom, date_naissance, lieu_naissance,
                genre, nationalite, telephone, email, adresse, photo,
                groupe_sanguin, allergies, statut_etudiant, date_inscription, date_creation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $matricule,
            $donnees['nom'],
            $donnees['post_nom'],
            $donnees['prenom'],
            $donnees['date_naissance'],
            $donnees['lieu_naissance'] ?? null,
            $donnees['genre'] ?? 'Autre',
            $donnees['nationalite'] ?? 'Congolaise',
            $donnees['telephone'] ?? null,
            $donnees['email'] ?? null,
            $donnees['adresse'] ?? null,
            $donnees['photo'] ?? null,
            $donnees['groupe_sanguin'] ?? null,
            $donnees['allergies'] ?? null,
            $donnees['statut_etudiant'] ?? 'en_attente',
            $donnees['date_inscription'] ?? date('Y-m-d')
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur ajout élève', [
            'error' => $e->getMessage(),
            'donnees' => $donnees
        ]);
        return false;
    }
}

/**
 * Modifie un élève existant
 *
 * @param int $eleve_id ID de l'élève
 * @param array $donnees Nouvelles données
 * @return bool Succès de la modification
 */
function modifier_eleve(int $eleve_id, array $donnees): bool
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            UPDATE eleves SET
                nom = ?, post_nom = ?, prenom = ?, date_naissance = ?,
                lieu_naissance = ?, genre = ?, nationalite = ?,
                telephone = ?, email = ?, adresse = ?, photo = ?,
                groupe_sanguin = ?, allergies = ?, statut_etudiant = ?,
                date_modif = NOW()
            WHERE eleve_id = ?
        ");

        $stmt->execute([
            $donnees['nom'],
            $donnees['post_nom'],
            $donnees['prenom'],
            $donnees['date_naissance'],
            $donnees['lieu_naissance'] ?? null,
            $donnees['genre'] ?? 'Autre',
            $donnees['nationalite'] ?? 'Congolaise',
            $donnees['telephone'] ?? null,
            $donnees['email'] ?? null,
            $donnees['adresse'] ?? null,
            $donnees['photo'] ?? null,
            $donnees['groupe_sanguin'] ?? null,
            $donnees['allergies'] ?? null,
            $donnees['statut_etudiant'] ?? 'actif',
            $eleve_id
        ]);

        return true;

    } catch (PDOException $e) {
        logError('Erreur modification élève', [
            'eleve_id' => $eleve_id,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Désinscrit un élève (changement de statut)
 *
 * @param int $eleve_id ID de l'élève
 * @return bool Succès de la désinscription
 */
function desinscrire_eleve(int $eleve_id): bool
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("UPDATE eleves SET statut_etudiant = 'desiste', date_modif = NOW() WHERE eleve_id = ?");
        return $stmt->execute([$eleve_id]);
    } catch (PDOException $e) {
        logError('Erreur désinscription élève', [
            'eleve_id' => $eleve_id,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Supprime définitivement un élève (utiliser avec précaution)
 *
 * @param int $eleve_id ID de l'élève
 * @return bool Succès de la suppression
 */
function supprimer_eleve_definitivement(int $eleve_id): bool
{
    $pdo = get_db_connection();

    try {
        // Au lieu de supprimer, on marque comme supprimé pour conserver l'historique
        $stmt = $pdo->prepare("UPDATE eleves SET statut_etudiant = 'desiste', date_modif = NOW() WHERE eleve_id = ?");
        return $stmt->execute([$eleve_id]);
    } catch (PDOException $e) {
        logError('Erreur suppression élève', [
            'eleve_id' => $eleve_id,
            'error' => $e->getMessage()
        ]);
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
 * @param int $class_id ID de la classe
 * @return array Liste des élèves
 */
function get_eleves_par_classe(int $class_id): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT e.*, CONCAT(e.nom, ' ', e.post_nom, ' ', e.prenom) as nom_complet,
                   adm.date_admission, adm.statut_admission
            FROM eleves e
            JOIN admissions adm ON e.eleve_id = adm.eleve_id
            WHERE adm.class_id = ? AND adm.statut_admission = 'approuve'
            AND e.statut_etudiant = 'actif'
            ORDER BY e.nom, e.post_nom, e.prenom
        ");

        $stmt->execute([$class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération élèves par classe', [
            'class_id' => $class_id,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Récupère les statistiques des élèves
 *
 * @return array Statistiques
 */
function get_statistiques_eleves(): array
{
    $pdo = get_db_connection();

    try {
        $stats = [];

        // Total par statut
        $stmt = $pdo->query("SELECT statut_etudiant, COUNT(*) as nombre FROM eleves GROUP BY statut_etudiant");
        $stats['par_statut'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total par genre
        $stmt = $pdo->query("SELECT genre, COUNT(*) as nombre FROM eleves GROUP BY genre");
        $stats['par_genre'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total par classe
        $stmt = $pdo->prepare("
            SELECT c.libelle as classe_nom, COUNT(adm.eleve_id) as nombre
            FROM classes c
            LEFT JOIN admissions adm ON c.class_id = adm.class_id AND adm.statut_admission = 'approuve'
            LEFT JOIN eleves e ON adm.eleve_id = e.eleve_id AND e.statut_etudiant = 'actif'
            GROUP BY c.class_id, c.libelle
            ORDER BY c.libelle
        ");
        $stmt->execute();
        $stats['par_classe'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;

    } catch (PDOException $e) {
        logError('Erreur récupération statistiques élèves', ['error' => $e->getMessage()]);
        return [];
    }
}

// =============================================
// FONCTIONS LIÉES AUX DONNÉES ÉLÈVES
// =============================================

/**
 * Récupère les notes d'un élève
 *
 * @param int $eleve_id ID de l'élève
 * @return array Notes de l'élève
 */
function get_notes_eleve(int $eleve_id): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT n.*, m.nom_matiere, m.coefficient,
                   CONCAT(p.prenom, ' ', p.nom) as nom_professeur,
                   a.annee_libelle, n.periode
            FROM notes n
            JOIN matieres m ON n.matiere_id = m.matiere_id
            LEFT JOIN professeurs p ON n.professeur_id = p.professeur_id
            LEFT JOIN annees_scolaire a ON n.annee_id = a.annee_id
            WHERE n.eleve_id = ?
            ORDER BY n.date_evaluation DESC, m.nom_matiere
        ");

        $stmt->execute([$eleve_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération notes élève', [
            'eleve_id' => $eleve_id,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Récupère les absences d'un élève
 *
 * @param int $eleve_id ID de l'élève
 * @return array Absences de l'élève
 */
function get_absences_eleve(int $eleve_id): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT a.*, m.nom_matiere, a.periode_journee, a.justifiee, a.motif,
                   CONCAT(u.prenom, ' ', u.nom) as enregistreur
            FROM absences a
            LEFT JOIN matieres m ON a.matiere_id = m.matiere_id
            LEFT JOIN user_admins u ON a.enregistre_par = u.user_id
            WHERE a.eleve_id = ?
            ORDER BY a.date_absence DESC
        ");

        $stmt->execute([$eleve_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération absences élève', [
            'eleve_id' => $eleve_id,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Récupère les paiements d'un élève
 *
 * @param int $eleve_id ID de l'élève
 * @return array Paiements de l'élève
 */
function get_paiements_eleve(int $eleve_id): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT p.*, a.annee_libelle, CONCAT(u.prenom, ' ', u.nom) as caissier
            FROM paiements p
            LEFT JOIN annees_scolaire a ON p.annee_id = a.annee_id
            LEFT JOIN user_admins u ON p.caissier_id = u.user_id
            WHERE p.eleve_id = ?
            ORDER BY p.date_creation DESC
        ");

        $stmt->execute([$eleve_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération paiements élève', [
            'eleve_id' => $eleve_id,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Génère un matricule unique pour un élève
 *
 * @return string Matricule généré
 */
function generer_matricule_eleve(): string
{
    $pdo = get_db_connection();

    do {
        // Format: ELEVE + Année + Numéro séquentiel (ex: ELEVE20250001)
        $annee = date('Y');
        $numero = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $matricule = 'ELEVE' . $annee . $numero;

        // Vérifier si le matricule existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM eleves WHERE matricule = ?");
        $stmt->execute([$matricule]);
        $exists = $stmt->fetchColumn();

    } while ($exists > 0);

    return $matricule;
}

?>