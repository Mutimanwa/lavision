<?php
/**
 * Modèle pour la gestion académique
 * Gère les données des classes, matières, horaires et autres éléments académiques
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */
require_once __DIR__ ."/../Config/Config.php";
require_once SERVICES_PATH . '/database.php';

// =============================================
// CONSTANTES DE BASE DE DONNÉES
// =============================================

/**
 * Tables utilisées par le module académique
 */
define('TABLE_CLASSES', 'classes');
define('TABLE_MATIERES', 'matieres');
define('TABLE_HORAIRES', 'horaires');
define('TABLE_NIVEAUX', 'niveaux_academiques');
define('TABLE_SECTIONS', 'sections_academiques');
define('TABLE_ANNEES_SCOLAIRES', 'annees_scolaire');

// =============================================
// FONCTIONS DE GESTION DES NIVEAUX ACADÉMIQUES
// =============================================

/**
 * Récupère tous les niveaux académiques
 */
function get_niveaux_academiques(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT id, nom_niveau, code_niveau, actif, ordre_affichage
            FROM " . TABLE_NIVEAUX . "
            ORDER BY ordre_affichage ASC, nom_niveau ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération niveaux académiques', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Récupère les niveaux actifs
 */
function get_niveaux_actifs(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT id, nom_niveau, code_niveau
            FROM " . TABLE_NIVEAUX . "
            WHERE actif = 1
            ORDER BY ordre_affichage ASC, nom_niveau ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération niveaux actifs', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Modifie un niveau académique
 */
function modifier_niveau_academique(int $id, array $donnees): bool
{
    $pdo = get_db_connection();

    try {
        $champs = [];
        $valeurs = [];

        foreach ($donnees as $champ => $valeur) {
            $champs[] = "$champ = ?";
            $valeurs[] = $valeur;
        }

        $valeurs[] = $id;

        $stmt = $pdo->prepare("
            UPDATE " . TABLE_NIVEAUX . "
            SET " . implode(', ', $champs) . ", date_modification = NOW()
            WHERE id = ?
        ");

        return $stmt->execute($valeurs);

    } catch (PDOException $e) {
        logError('Erreur modification niveau académique', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// =============================================
// FONCTIONS DE GESTION DES SECTIONS ACADÉMIQUES
// =============================================

/**
 * Récupère toutes les sections académiques
 */
function get_sections_academiques(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT id, nom_section, code_section, actif, ordre_affichage
            FROM " . TABLE_SECTIONS . "
            ORDER BY ordre_affichage ASC, nom_section ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération sections académiques', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Récupère les sections actives
 */
function get_sections_actives(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT id, nom_section, code_section
            FROM " . TABLE_SECTIONS . "
            WHERE actif = 1
            ORDER BY ordre_affichage ASC, nom_section ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération sections actives', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Modifie une section académique
 */
function modifier_section_academique(int $id, array $donnees): bool
{
    $pdo = get_db_connection();

    try {
        $champs = [];
        $valeurs = [];

        foreach ($donnees as $champ => $valeur) {
            $champs[] = "$champ = ?";
            $valeurs[] = $valeur;
        }

        $valeurs[] = $id;

        $stmt = $pdo->prepare("
            UPDATE " . TABLE_SECTIONS . "
            SET " . implode(', ', $champs) . ", date_modification = NOW()
            WHERE id = ?
        ");

        return $stmt->execute($valeurs);

    } catch (PDOException $e) {
        logError('Erreur modification section académique', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// =============================================
// FONCTIONS DE GESTION DES CLASSES
// =============================================

/**
 * Récupère les classes avec pagination et filtres
 */
function get_classes_pagines(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $pdo = get_db_connection();

    try {
        $offset = ($page - 1) * $par_page;

        // Construction de la requête avec filtres
        $conditions = [];
        $valeurs = [];

        if (!empty($filtres['niveau'])) {
            $conditions[] = "c.id_niveau = ?";
            $valeurs[] = $filtres['niveau'];
        }

        if (!empty($filtres['section'])) {
            $conditions[] = "c.id_section = ?";
            $valeurs[] = $filtres['section'];
        }

        if (!empty($filtres['annee_scolaire'])) {
            $conditions[] = "c.id_annee_scolaire = ?";
            $valeurs[] = $filtres['annee_scolaire'];
        }

        $where_clause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $stmt = $pdo->prepare("
            SELECT c.*, n.nom_niveau, s.nom_section, a.annee_debut, a.annee_fin,
                   p.nom AS nom_prof_principal, p.prenoms AS prenoms_prof_principal,
                   COUNT(e.id) AS nombre_eleves
            FROM " . TABLE_CLASSES . " c
            LEFT JOIN " . TABLE_NIVEAUX . " n ON c.id_niveau = n.id
            LEFT JOIN " . TABLE_SECTIONS . " s ON c.id_section = s.id
            LEFT JOIN " . TABLE_ANNEES_SCOLAIRES . " a ON c.id_annee_scolaire = a.id
            LEFT JOIN professeurs p ON c.id_prof_principal = p.id
            LEFT JOIN eleves e ON e.id_classe = c.id AND e.actif = 1
            $where_clause
            GROUP BY c.id
            ORDER BY n.ordre_affichage ASC, c.nom_classe ASC
            LIMIT ? OFFSET ?
        ");

        $valeurs[] = $par_page;
        $valeurs[] = $offset;

        $stmt->execute($valeurs);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération classes paginées', [
            'filtres' => $filtres,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Compte le nombre total de classes selon les filtres
 */
function compter_classes(array $filtres = []): int
{
    $pdo = get_db_connection();

    try {
        $conditions = [];
        $valeurs = [];

        if (!empty($filtres['niveau'])) {
            $conditions[] = "id_niveau = ?";
            $valeurs[] = $filtres['niveau'];
        }

        if (!empty($filtres['section'])) {
            $conditions[] = "id_section = ?";
            $valeurs[] = $filtres['section'];
        }

        if (!empty($filtres['annee_scolaire'])) {
            $conditions[] = "id_annee_scolaire = ?";
            $valeurs[] = $filtres['annee_scolaire'];
        }

        $where_clause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TABLE_CLASSES . " $where_clause");
        $stmt->execute($valeurs);

        return (int) $stmt->fetchColumn();

    } catch (PDOException $e) {
        logError('Erreur comptage classes', [
            'filtres' => $filtres,
            'error' => $e->getMessage()
        ]);
        return 0;
    }
}

/**
 * Récupère les classes actives
 */
function get_classes_actives(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT c.id, c.nom_classe, n.nom_niveau, s.nom_section
            FROM " . TABLE_CLASSES . " c
            LEFT JOIN " . TABLE_NIVEAUX . " n ON c.id_niveau = n.id
            LEFT JOIN " . TABLE_SECTIONS . " s ON c.id_section = s.id
            WHERE c.actif = 1
            ORDER BY n.ordre_affichage ASC, c.nom_classe ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération classes actives', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Ajoute une nouvelle classe
 */
function ajouter_classe(array $donnees): int|false
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_CLASSES . "
            (nom_classe, id_niveau, id_section, id_annee_scolaire, id_prof_principal,
             capacite_max, description, actif, date_creation, date_modification)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");

        $stmt->execute([
            $donnees['nom_classe'],
            $donnees['id_niveau'],
            $donnees['id_section'],
            $donnees['id_annee_scolaire'],
            $donnees['id_prof_principal'] ?? null,
            $donnees['capacite_max'] ?? null,
            $donnees['description'] ?? null
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur ajout classe', [
            'donnees' => $donnees,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// =============================================
// FONCTIONS DE GESTION DES MATIÈRES
// =============================================

/**
 * Récupère les matières avec pagination et filtres
 */
function get_matieres_pagines(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $pdo = get_db_connection();

    try {
        $offset = ($page - 1) * $par_page;

        // Construction de la requête avec filtres
        $conditions = [];
        $valeurs = [];

        if (!empty($filtres['type'])) {
            $conditions[] = "type_matiere = ?";
            $valeurs[] = $filtres['type'];
        }

        if (!empty($filtres['niveau'])) {
            $conditions[] = "id_niveau = ?";
            $valeurs[] = $filtres['niveau'];
        }

        $where_clause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $stmt = $pdo->prepare("
            SELECT m.*, n.nom_niveau
            FROM " . TABLE_MATIERES . " m
            LEFT JOIN " . TABLE_NIVEAUX . " n ON m.id_niveau = n.id
            $where_clause
            ORDER BY m.nom_matiere ASC
            LIMIT ? OFFSET ?
        ");

        $valeurs[] = $par_page;
        $valeurs[] = $offset;

        $stmt->execute($valeurs);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération matières paginées', [
            'filtres' => $filtres,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Compte le nombre total de matières selon les filtres
 */
function compter_matieres(array $filtres = []): int
{
    $pdo = get_db_connection();

    try {
        $conditions = [];
        $valeurs = [];

        if (!empty($filtres['type'])) {
            $conditions[] = "type_matiere = ?";
            $valeurs[] = $filtres['type'];
        }

        if (!empty($filtres['niveau'])) {
            $conditions[] = "id_niveau = ?";
            $valeurs[] = $filtres['niveau'];
        }

        $where_clause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TABLE_MATIERES . " $where_clause");
        $stmt->execute($valeurs);

        return (int) $stmt->fetchColumn();

    } catch (PDOException $e) {
        logError('Erreur comptage matières', [
            'filtres' => $filtres,
            'error' => $e->getMessage()
        ]);
        return 0;
    }
}

/**
 * Récupère les matières actives
 */
function get_matieres_actives(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT id, nom_matiere, code_matiere, coefficient
            FROM " . TABLE_MATIERES . "
            WHERE actif = 1
            ORDER BY nom_matiere ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération matières actives', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Ajoute une nouvelle matière
 */
function ajouter_matiere(array $donnees): int|false
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_MATIERES . "
            (nom_matiere, code_matiere, type_matiere, id_niveau, coefficient,
             description, heures_semaine, actif, date_creation, date_modification)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");

        $stmt->execute([
            $donnees['nom_matiere'],
            $donnees['code_matiere'],
            $donnees['type_matiere'],
            $donnees['id_niveau'],
            $donnees['coefficient'] ?? 1,
            $donnees['description'] ?? null,
            $donnees['heures_semaine'] ?? null
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur ajout matière', [
            'donnees' => $donnees,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// =============================================
// FONCTIONS DE GESTION DES HORAIRES
// =============================================

/**
 * Récupère les horaires d'une classe pour un jour donné
 */
function get_horaires_classe(int $id_classe, int $jour_semaine): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT h.*, m.nom_matiere, m.code_matiere,
                   p.nom AS nom_professeur, p.prenoms AS prenoms_professeur
            FROM " . TABLE_HORAIRES . " h
            LEFT JOIN " . TABLE_MATIERES . " m ON h.id_matiere = m.id
            LEFT JOIN professeurs p ON h.id_professeur = p.id
            WHERE h.id_classe = ? AND h.jour_semaine = ? AND h.actif = 1
            ORDER BY h.heure_debut ASC
        ");

        $stmt->execute([$id_classe, $jour_semaine]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération horaires classe', [
            'id_classe' => $id_classe,
            'jour_semaine' => $jour_semaine,
            'error' => $e->getMessage()
        ]);
        return [];
    }
}

/**
 * Ajoute un nouvel horaire
 */
function ajouter_horaire(array $donnees): int|false
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_HORAIRES . "
            (id_classe, jour_semaine, heure_debut, heure_fin, id_matiere,
             id_professeur, salle, actif, date_creation, date_modification)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");

        $stmt->execute([
            $donnees['id_classe'],
            $donnees['jour_semaine'],
            $donnees['heure_debut'],
            $donnees['heure_fin'],
            $donnees['id_matiere'],
            $donnees['id_professeur'] ?? null,
            $donnees['salle'] ?? null
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur ajout horaire', [
            'donnees' => $donnees,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Modifie un horaire existant
 */
function modifier_horaire(int $id, array $donnees): bool
{
    $pdo = get_db_connection();

    try {
        $champs = [];
        $valeurs = [];

        foreach ($donnees as $champ => $valeur) {
            $champs[] = "$champ = ?";
            $valeurs[] = $valeur;
        }

        $valeurs[] = $id;

        $stmt = $pdo->prepare("
            UPDATE " . TABLE_HORAIRES . "
            SET " . implode(', ', $champs) . ", date_modification = NOW()
            WHERE id = ?
        ");

        return $stmt->execute($valeurs);

    } catch (PDOException $e) {
        logError('Erreur modification horaire', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Récupère l'année scolaire active
 */
function get_annee_scolaire_active(): array|null
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM " . TABLE_ANNEES_SCOLAIRES . "
            WHERE statut = 'active'
            ORDER BY date_debut DESC
            LIMIT 1
        ");
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    } catch (PDOException $e) {
        logError('Erreur récupération année scolaire active', ['error' => $e->getMessage()]);
        return null;
    }
}

/**
 * Récupère toutes les années scolaires
 */
function get_annees_scolaires(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT * FROM " . TABLE_ANNEES_SCOLAIRES . "
            ORDER BY annee_debut DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération années scolaires', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Récupère les professeurs disponibles
 */
function get_professeurs_disponibles(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT id, nom, prenoms, specialite
            FROM professeurs
            WHERE actif = 1
            ORDER BY nom ASC, prenoms ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération professeurs disponibles', ['error' => $e->getMessage()]);
        return [];
    }
}