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
define('TABLE_EMPLOI_DU_TEMPS', 'emploi_du_temps');
define('TABLE_NIVEAUX', 'niveau');
define('TABLE_SECTIONS', 'sections');
define('TABLE_ANNEES_SCOLAIRES', 'annees_scolaire');
define('TABLE_PROFESSEURS', 'professeurs');
define('TABLE_NOTES', 'notes');
define('TABLE_ADMISSIONS', 'admissions');

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
            SELECT niveau_id as id, nom_niveau, description, ordre_affichage, date_creation
            FROM " . TABLE_NIVEAUX . "
            ORDER BY ordre_affichage ASC, nom_niveau ASC
        ");

        $niveaux = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ajouter le champ 'actif' par défaut (tous les niveaux sont actifs dans le nouveau schéma)
        foreach ($niveaux as &$niveau) {
            $niveau['actif'] = true;
            $niveau['code_niveau'] = $niveau['nom_niveau']; // Pour compatibilité avec l'ancien code
        }

        return $niveaux;

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
            SELECT niveau_id, nom_niveau, description
            FROM " . TABLE_NIVEAUX . "
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
            SET " . implode(', ', $champs) . ", date_modif = NOW()
            WHERE niveau_id = ?
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

/**
 * Ajoute un nouveau niveau académique
 */
function ajouter_niveau_academique(array $donnees): int|false
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_NIVEAUX . "
            (nom_niveau, description, ordre_affichage, date_creation)
            VALUES (?, ?, ?, NOW())
        ");

        $stmt->execute([
            $donnees['nom_niveau'],
            $donnees['description'] ?? null,
            $donnees['ordre_affichage'] ?? 0
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur ajout niveau académique', [
            'donnees' => $donnees,
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
            SELECT section_id as id, nom_section, description, couleur, date_creation
            FROM " . TABLE_SECTIONS . "
            ORDER BY nom_section ASC
        ");

        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ajouter les champs pour compatibilité avec l'ancien code
        foreach ($sections as &$section) {
            $section['actif'] = true;
            $section['code_section'] = $section['nom_section'];
            $section['ordre_affichage'] = 0; // Valeur par défaut
        }

        return $sections;

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
            SELECT section_id, nom_section, description, couleur
            FROM " . TABLE_SECTIONS . "
            ORDER BY nom_section ASC
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
            SET " . implode(', ', $champs) . ", date_modif = NOW()
            WHERE section_id = ?
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

/**
 * Ajoute une nouvelle section académique
 */
function ajouter_section_academique(array $donnees): int|false
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_SECTIONS . "
            (nom_section, description, couleur, date_creation)
            VALUES (?, ?, ?, NOW())
        ");

        $stmt->execute([
            $donnees['nom_section'],
            $donnees['description'] ?? null,
            $donnees['couleur'] ?? '#007bff'
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur ajout section académique', [
            'donnees' => $donnees,
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
            $conditions[] = "c.niveau_id = ?";
            $valeurs[] = $filtres['niveau'];
        }

        if (!empty($filtres['section'])) {
            $conditions[] = "c.section_id = ?";
            $valeurs[] = $filtres['section'];
        }

        if (!empty($filtres['annee_scolaire'])) {
            $conditions[] = "c.annee_id = ?";
            $valeurs[] = $filtres['annee_scolaire'];
        }

        $where_clause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $stmt = $pdo->prepare("
            SELECT c.*, n.nom_niveau, s.nom_section, a.annee_libelle,
                   p.prenom AS prenom_prof_principal, p.nom AS nom_prof_principal,
                   COUNT(e.eleve_id) AS nombre_eleves
            FROM " . TABLE_CLASSES . " c
            LEFT JOIN " . TABLE_NIVEAUX . " n ON c.niveau_id = n.niveau_id
            LEFT JOIN " . TABLE_SECTIONS . " s ON c.section_id = s.section_id
            LEFT JOIN " . TABLE_ANNEES_SCOLAIRES . " a ON c.annee_id = a.annee_id
            LEFT JOIN " . TABLE_PROFESSEURS . " p ON c.tuteur_id = p.professeur_id
            LEFT JOIN " . TABLE_ADMISSIONS . " adm ON adm.class_id = c.class_id AND adm.statut_admission = 'approuve'
            LEFT JOIN eleves e ON adm.eleve_id = e.eleve_id AND e.statut_etudiant = 'actif'
            $where_clause
            GROUP BY c.class_id
            ORDER BY n.ordre_affichage ASC, c.libelle ASC
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
            $conditions[] = "niveau_id = ?";
            $valeurs[] = $filtres['niveau'];
        }

        if (!empty($filtres['section'])) {
            $conditions[] = "section_id = ?";
            $valeurs[] = $filtres['section'];
        }

        if (!empty($filtres['annee_scolaire'])) {
            $conditions[] = "annee_id = ?";
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
            SELECT c.class_id, c.libelle, n.nom_niveau, s.nom_section
            FROM " . TABLE_CLASSES . " c
            LEFT JOIN " . TABLE_NIVEAUX . " n ON c.niveau_id = n.niveau_id
            LEFT JOIN " . TABLE_SECTIONS . " s ON c.section_id = s.section_id
            WHERE c.statut = 'active'
            ORDER BY n.ordre_affichage ASC, c.libelle ASC
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
            (libelle, niveau_id, section_id, annee_id, tuteur_id,
             capacite_max, salle, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([
            $donnees['libelle'],
            $donnees['niveau_id'],
            $donnees['section_id'] ?? null,
            $donnees['annee_id'],
            $donnees['tuteur_id'] ?? null,
            $donnees['capacite_max'] ?? 30,
            $donnees['salle'] ?? null
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

        if (!empty($filtres['niveau'])) {
            $conditions[] = "m.niveau_id = ?";
            $valeurs[] = $filtres['niveau'];
        }

        $where_clause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

        $stmt = $pdo->prepare("
            SELECT m.*, n.nom_niveau
            FROM " . TABLE_MATIERES . " m
            LEFT JOIN " . TABLE_NIVEAUX . " n ON m.niveau_id = n.niveau_id
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

        if (!empty($filtres['niveau'])) {
            $conditions[] = "niveau_id = ?";
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
            SELECT matiere_id, nom_matiere, code_matiere, coefficient
            FROM " . TABLE_MATIERES . "
            WHERE est_obligatoire = 1
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
            (nom_matiere, code_matiere, description, coefficient, niveau_id,
             couleur, heures_semaine, est_obligatoire, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $donnees['nom_matiere'],
            $donnees['code_matiere'],
            $donnees['description'] ?? null,
            $donnees['coefficient'] ?? 1.00,
            $donnees['niveau_id'] ?? null,
            $donnees['couleur'] ?? '#2ecc71',
            $donnees['heures_semaine'] ?? 4,
            $donnees['est_obligatoire'] ?? true
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
// FONCTIONS DE GESTION DE L'EMPLOI DU TEMPS
// =============================================

/**
 * Récupère les horaires d'une classe pour un jour donné
 */
function get_horaires_classe(int $id_classe, string $jour_semaine): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT edt.*, m.nom_matiere, m.code_matiere,
                   p.prenom AS prenom_professeur, p.nom AS nom_professeur
            FROM " . TABLE_EMPLOI_DU_TEMPS . " edt
            LEFT JOIN " . TABLE_MATIERES . " m ON edt.matiere_id = m.matiere_id
            LEFT JOIN " . TABLE_PROFESSEURS . " p ON edt.professeur_id = p.professeur_id
            WHERE edt.class_id = ? AND edt.jour_semaine = ? AND edt.statut = 'actif'
            ORDER BY edt.heure_debut ASC
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
            INSERT INTO " . TABLE_EMPLOI_DU_TEMPS . "
            (class_id, jour_semaine, heure_debut, heure_fin, matiere_id,
             professeur_id, salle, type_cours, annee_id, periode, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
        ");

        $stmt->execute([
            $donnees['class_id'],
            $donnees['jour_semaine'],
            $donnees['heure_debut'],
            $donnees['heure_fin'],
            $donnees['matiere_id'],
            $donnees['professeur_id'] ?? null,
            $donnees['salle'] ?? null,
            $donnees['type_cours'] ?? 'cours',
            $donnees['annee_id'],
            $donnees['periode'] ?? 'trimestre1'
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
            UPDATE " . TABLE_EMPLOI_DU_TEMPS . "
            SET " . implode(', ', $champs) . ", date_modif = NOW()
            WHERE edt_id = ?
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
            ORDER BY date_debut DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération années scolaires', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Ajoute une nouvelle année scolaire
 */
function ajouter_annee_scolaire(array $donnees): int|false
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_ANNEES_SCOLAIRES . "
            (annee_libelle, date_debut, date_fin, description, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $donnees['annee_libelle'],
            $donnees['date_debut'],
            $donnees['date_fin'],
            $donnees['description'] ?? null,
            $donnees['statut'] ?? 'inactive'
        ]);

        return $pdo->lastInsertId();

    } catch (PDOException $e) {
        logError('Erreur ajout année scolaire', [
            'donnees' => $donnees,
            'error' => $e->getMessage()
        ]);
        return false;
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
            SELECT professeur_id, nom, prenom, specialite
            FROM " . TABLE_PROFESSEURS . "
            WHERE statut = 'actif'
            ORDER BY nom ASC, prenom ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération professeurs disponibles', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Désactive toutes les années scolaires actives
 */
function desactiver_toutes_annees_scolaires(): bool
{
    try {
        $pdo = get_db_connection();

        $stmt = $pdo->prepare("
            UPDATE " . TABLE_ANNEES_SCOLAIRES . "
            SET statut = 'inactive', date_modification = NOW()
            WHERE statut = 'active'
        ");

        return $stmt->execute();

    } catch (PDOException $e) {
        logError('Erreur désactivation années scolaires', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Active une année scolaire spécifique
 */
function activer_annee_scolaire_par_id(int $id): bool
{
    try {
        $pdo = get_db_connection();

        $stmt = $pdo->prepare("
            UPDATE " . TABLE_ANNEES_SCOLAIRES . "
            SET statut = 'inactive'
            WHERE annee_id = :id
        ");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();

    } catch (PDOException $e) {
        logError('Erreur activation année scolaire', ['error' => $e->getMessage(), 'id' => $id]);
        return false;
    }
}

/**
 * Récupère une année scolaire par son ID
 */
function get_annee_scolaire_par_id(int $id): ?array
{
    try {
        $pdo = get_db_connection();

        $stmt = $pdo->prepare("
            SELECT id, annee_libelle, date_debut, date_fin, statut, date_creation
            FROM " . TABLE_ANNEES_SCOLAIRES . "
            WHERE annee_id = :id
        ");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;

    } catch (PDOException $e) {
        logError('Erreur récupération année scolaire par ID', ['error' => $e->getMessage(), 'id' => $id]);
        return null;
    }
}

/**
 * Modifie une année scolaire
 */
function modifier_annee_scolaire(int $id, array $donnees): bool
{
    try {
        $pdo = get_db_connection();

        $stmt = $pdo->prepare("
            UPDATE " . TABLE_ANNEES_SCOLAIRES . "
            SET annee_libelle = :annee_libelle,
                date_debut = :date_debut,
                date_fin = :date_fin
            WHERE annee_id = :id
        ");

        $stmt->bindParam(':annee_libelle', $donnees['annee_libelle'], PDO::PARAM_STR);
        $stmt->bindParam(':date_debut', $donnees['date_debut'], PDO::PARAM_STR);
        $stmt->bindParam(':date_fin', $donnees['date_fin'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();

    } catch (PDOException $e) {
        logError('Erreur modification année scolaire', ['error' => $e->getMessage(), 'id' => $id]);
        return false;
    }
}