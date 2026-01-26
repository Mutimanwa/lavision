<?php
/**
 * Fonctions de validation pour le module académique
 * Validation des données d'entrée pour les classes, matières, horaires
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

// =============================================
// VALIDATION DES DONNÉES DE CLASSE
// =============================================

/**
 * Valide les données d'une classe
 */
function valider_donnees_classe(array $donnees): array
{
    $erreurs = [];
    $validees = [];

    // Nettoyage des données
    $donnees = nettoyer_donnees($donnees);

    // Validation du nom de la classe
    $nom_classe = trim($donnees['nom_classe'] ?? '');
    if (empty($nom_classe)) {
        $erreurs['nom_classe'] = "Le nom de la classe est obligatoire.";
    } elseif (strlen($nom_classe) < 2) {
        $erreurs['nom_classe'] = "Le nom de la classe doit contenir au moins 2 caractères.";
    } elseif (strlen($nom_classe) > 50) {
        $erreurs['nom_classe'] = "Le nom de la classe ne peut pas dépasser 50 caractères.";
    } elseif (!preg_match('/^[a-zA-Z0-9\s\-\.]+$/', $nom_classe)) {
        $erreurs['nom_classe'] = "Le nom de la classe contient des caractères invalides.";
    } else {
        $validees['nom_classe'] = $nom_classe;
    }

    // Validation du niveau
    $id_niveau = intval($donnees['id_niveau'] ?? 0);
    if ($id_niveau <= 0) {
        $erreurs['id_niveau'] = "Veuillez sélectionner un niveau.";
    } elseif (!niveau_existe($id_niveau)) {
        $erreurs['id_niveau'] = "Le niveau sélectionné n'existe pas.";
    } else {
        $validees['id_niveau'] = $id_niveau;
    }

    // Validation de la section
    $id_section = intval($donnees['id_section'] ?? 0);
    if ($id_section <= 0) {
        $erreurs['id_section'] = "Veuillez sélectionner une section.";
    } elseif (!section_existe($id_section)) {
        $erreurs['id_section'] = "La section sélectionnée n'existe pas.";
    } else {
        $validees['id_section'] = $id_section;
    }

    // Validation de l'année scolaire
    $id_annee_scolaire = intval($donnees['id_annee_scolaire'] ?? 0);
    if ($id_annee_scolaire <= 0) {
        $erreurs['id_annee_scolaire'] = "Veuillez sélectionner une année scolaire.";
    } elseif (!annee_scolaire_existe($id_annee_scolaire)) {
        $erreurs['id_annee_scolaire'] = "L'année scolaire sélectionnée n'existe pas.";
    } else {
        $validees['id_annee_scolaire'] = $id_annee_scolaire;
    }

    // Validation du professeur principal (optionnel)
    $id_prof_principal = intval($donnees['id_prof_principal'] ?? 0);
    if ($id_prof_principal > 0) {
        if (!professeur_existe($id_prof_principal)) {
            $erreurs['id_prof_principal'] = "Le professeur sélectionné n'existe pas.";
        } else {
            $validees['id_prof_principal'] = $id_prof_principal;
        }
    }

    // Validation de la capacité maximale (optionnel)
    $capacite_max = intval($donnees['capacite_max'] ?? 0);
    if ($capacite_max > 0) {
        if ($capacite_max < 5) {
            $erreurs['capacite_max'] = "La capacité minimale est de 5 élèves.";
        } elseif ($capacite_max > 100) {
            $erreurs['capacite_max'] = "La capacité maximale est de 100 élèves.";
        } else {
            $validees['capacite_max'] = $capacite_max;
        }
    }

    // Validation de la description (optionnel)
    $description = trim($donnees['description'] ?? '');
    if (!empty($description)) {
        if (strlen($description) > 500) {
            $erreurs['description'] = "La description ne peut pas dépasser 500 caractères.";
        } else {
            $validees['description'] = $description;
        }
    }

    // Vérification d'unicité de la classe (nom + niveau + section + année)
    if (empty($erreurs) && isset($validees['nom_classe'], $validees['id_niveau'], $validees['id_section'], $validees['id_annee_scolaire'])) {
        if (classe_existe_deja($validees['nom_classe'], $validees['id_niveau'], $validees['id_section'], $validees['id_annee_scolaire'])) {
            $erreurs['nom_classe'] = "Une classe avec ce nom existe déjà pour ce niveau, cette section et cette année scolaire.";
        }
    }

    return ['erreurs' => $erreurs, 'validees' => $validees];
}

// =============================================
// VALIDATION DES DONNÉES DE MATIÈRE
// =============================================

/**
 * Valide les données d'une matière
 */
function valider_donnees_matiere(array $donnees): array
{
    $erreurs = [];
    $validees = [];

    // Nettoyage des données
    $donnees = nettoyer_donnees($donnees);

    // Validation du nom de la matière
    $nom_matiere = trim($donnees['nom_matiere'] ?? '');
    if (empty($nom_matiere)) {
        $erreurs['nom_matiere'] = "Le nom de la matière est obligatoire.";
    } elseif (strlen($nom_matiere) < 2) {
        $erreurs['nom_matiere'] = "Le nom de la matière doit contenir au moins 2 caractères.";
    } elseif (strlen($nom_matiere) > 100) {
        $erreurs['nom_matiere'] = "Le nom de la matière ne peut pas dépasser 100 caractères.";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/', $nom_matiere)) {
        $erreurs['nom_matiere'] = "Le nom de la matière contient des caractères invalides.";
    } else {
        $validees['nom_matiere'] = $nom_matiere;
    }

    // Validation du code de la matière
    $code_matiere = trim($donnees['code_matiere'] ?? '');
    if (empty($code_matiere)) {
        $erreurs['code_matiere'] = "Le code de la matière est obligatoire.";
    } elseif (!preg_match('/^[A-Z]{2,10}$/', $code_matiere)) {
        $erreurs['code_matiere'] = "Le code doit contenir entre 2 et 10 lettres majuscules uniquement.";
    } else {
        $validees['code_matiere'] = $code_matiere;
    }

    // Validation du type de matière
    $type_matiere = trim($donnees['type_matiere'] ?? '');
    $types_valides = ['fondamentale', 'optionnelle', 'specialisee'];
    if (empty($type_matiere)) {
        $erreurs['type_matiere'] = "Le type de matière est obligatoire.";
    } elseif (!in_array($type_matiere, $types_valides)) {
        $erreurs['type_matiere'] = "Le type de matière sélectionné n'est pas valide.";
    } else {
        $validees['type_matiere'] = $type_matiere;
    }

    // Validation du niveau
    $id_niveau = intval($donnees['id_niveau'] ?? 0);
    if ($id_niveau <= 0) {
        $erreurs['id_niveau'] = "Veuillez sélectionner un niveau.";
    } elseif (!niveau_existe($id_niveau)) {
        $erreurs['id_niveau'] = "Le niveau sélectionné n'existe pas.";
    } else {
        $validees['id_niveau'] = $id_niveau;
    }

    // Validation du coefficient (optionnel)
    $coefficient = floatval($donnees['coefficient'] ?? 1);
    if ($coefficient < 0.5) {
        $erreurs['coefficient'] = "Le coefficient minimum est de 0.5.";
    } elseif ($coefficient > 10) {
        $erreurs['coefficient'] = "Le coefficient maximum est de 10.";
    } else {
        $validees['coefficient'] = $coefficient;
    }

    // Validation des heures par semaine (optionnel)
    $heures_semaine = intval($donnees['heures_semaine'] ?? 0);
    if ($heures_semaine > 0) {
        if ($heures_semaine < 1) {
            $erreurs['heures_semaine'] = "Le nombre d'heures minimum est de 1.";
        } elseif ($heures_semaine > 40) {
            $erreurs['heures_semaine'] = "Le nombre d'heures maximum est de 40.";
        } else {
            $validees['heures_semaine'] = $heures_semaine;
        }
    }

    // Validation de la description (optionnel)
    $description = trim($donnees['description'] ?? '');
    if (!empty($description)) {
        if (strlen($description) > 1000) {
            $erreurs['description'] = "La description ne peut pas dépasser 1000 caractères.";
        } else {
            $validees['description'] = $description;
        }
    }

    // Vérification d'unicité du code de matière
    if (empty($erreurs) && isset($validees['code_matiere'])) {
        if (code_matiere_existe_deja($validees['code_matiere'])) {
            $erreurs['code_matiere'] = "Ce code de matière existe déjà.";
        }
    }

    return ['erreurs' => $erreurs, 'validees' => $validees];
}

// =============================================
// VALIDATION DES DONNÉES D'HORAIRE
// =============================================

/**
 * Valide les données d'un horaire
 */
function valider_donnees_horaire(array $donnees): array
{
    $erreurs = [];
    $validees = [];

    // Nettoyage des données
    $donnees = nettoyer_donnees($donnees);

    // Validation de la classe
    $id_classe = intval($donnees['id_classe'] ?? 0);
    if ($id_classe <= 0) {
        $erreurs['id_classe'] = "La classe est obligatoire.";
    } elseif (!classe_existe($id_classe)) {
        $erreurs['id_classe'] = "La classe sélectionnée n'existe pas.";
    } else {
        $validees['id_classe'] = $id_classe;
    }

    // Validation du jour de la semaine
    $jour_semaine = intval($donnees['jour_semaine'] ?? 0);
    if ($jour_semaine < 1 || $jour_semaine > 7) {
        $erreurs['jour_semaine'] = "Le jour de la semaine n'est pas valide.";
    } else {
        $validees['jour_semaine'] = $jour_semaine;
    }

    // Validation de l'heure de début
    $heure_debut = trim($donnees['heure_debut'] ?? '');
    if (empty($heure_debut)) {
        $erreurs['heure_debut'] = "L'heure de début est obligatoire.";
    } elseif (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $heure_debut)) {
        $erreurs['heure_debut'] = "Le format de l'heure de début n'est pas valide (HH:MM).";
    } else {
        $validees['heure_debut'] = $heure_debut;
    }

    // Validation de l'heure de fin
    $heure_fin = trim($donnees['heure_fin'] ?? '');
    if (empty($heure_fin)) {
        $erreurs['heure_fin'] = "L'heure de fin est obligatoire.";
    } elseif (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $heure_fin)) {
        $erreurs['heure_fin'] = "Le format de l'heure de fin n'est pas valide (HH:MM).";
    } elseif (isset($validees['heure_debut']) && strtotime($heure_fin) <= strtotime($validees['heure_debut'])) {
        $erreurs['heure_fin'] = "L'heure de fin doit être postérieure à l'heure de début.";
    } else {
        $validees['heure_fin'] = $heure_fin;
    }

    // Validation de la matière
    $id_matiere = intval($donnees['id_matiere'] ?? 0);
    if ($id_matiere <= 0) {
        $erreurs['id_matiere'] = "La matière est obligatoire.";
    } elseif (!matiere_existe($id_matiere)) {
        $erreurs['id_matiere'] = "La matière sélectionnée n'existe pas.";
    } else {
        $validees['id_matiere'] = $id_matiere;
    }

    // Validation du professeur (optionnel)
    $id_professeur = intval($donnees['id_professeur'] ?? 0);
    if ($id_professeur > 0) {
        if (!professeur_existe($id_professeur)) {
            $erreurs['id_professeur'] = "Le professeur sélectionné n'existe pas.";
        } else {
            $validees['id_professeur'] = $id_professeur;
        }
    }

    // Validation de la salle (optionnel)
    $salle = trim($donnees['salle'] ?? '');
    if (!empty($salle)) {
        if (strlen($salle) > 50) {
            $erreurs['salle'] = "Le nom de la salle ne peut pas dépasser 50 caractères.";
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-\.]+$/', $salle)) {
            $erreurs['salle'] = "Le nom de la salle contient des caractères invalides.";
        } else {
            $validees['salle'] = $salle;
        }
    }

    // Vérification des conflits d'horaire
    if (empty($erreurs) && isset($validees['id_classe'], $validees['jour_semaine'], $validees['heure_debut'], $validees['heure_fin'])) {
        if (conflit_horaire_existe($validees['id_classe'], $validees['jour_semaine'], $validees['heure_debut'], $validees['heure_fin'])) {
            $erreurs['heure_debut'] = "Il y a un conflit d'horaire pour cette classe à cette période.";
        }
    }

    return ['erreurs' => $erreurs, 'validees' => $validees];
}

// =============================================
// FONCTIONS UTILITAIRES DE VALIDATION
// =============================================

/**
 * Fonctions utilitaires pour la validation académique
 */
function niveau_existe(int $id_niveau): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM niveaux WHERE id = ?");
    $stmt->execute([$id_niveau]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si une section existe
 */
function section_existe(int $id_section): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sections_academiques WHERE id = ?");
    $stmt->execute([$id_section]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si une année scolaire existe
 */
function annee_scolaire_existe(int $id_annee): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM annees_scolaires WHERE id = ?");
    $stmt->execute([$id_annee]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si un professeur existe
 */
function professeur_existe(int $id_professeur): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM professeurs WHERE id = ? AND actif = 1");
    $stmt->execute([$id_professeur]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si une matière existe
 */
function matiere_existe(int $id_matiere): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE id = ? AND actif = 1");
    $stmt->execute([$id_matiere]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si une classe existe déjà (unicité)
 */
function classe_existe_deja(string $nom_classe, int $id_niveau, int $id_section, int $id_annee): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM classes
        WHERE nom_classe = ? AND id_niveau = ? AND id_section = ? AND id_annee_scolaire = ? AND actif = 1
    ");
    $stmt->execute([$nom_classe, $id_niveau, $id_section, $id_annee]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si un code de matière existe déjà
 */
function code_matiere_existe_deja(string $code_matiere): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM matieres WHERE code_matiere = ? AND actif = 1");
    $stmt->execute([$code_matiere]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie s'il y a un conflit d'horaire
 */
function conflit_horaire_existe(int $id_classe, int $jour_semaine, string $heure_debut, string $heure_fin): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM horaires
        WHERE id_classe = ? AND jour_semaine = ? AND actif = 1
        AND (
            (heure_debut <= ? AND heure_fin > ?) OR
            (heure_debut < ? AND heure_fin >= ?) OR
            (heure_debut >= ? AND heure_fin <= ?)
        )
    ");
    $stmt->execute([
        $id_classe, $jour_semaine,
        $heure_debut, $heure_debut,
        $heure_fin, $heure_fin,
        $heure_debut, $heure_fin
    ]);
    return $stmt->fetchColumn() > 0;
}

// =============================================
// VALIDATION DES DONNÉES DE NIVEAU ACADÉMIQUE
// =============================================

/**
 * Valide les données d'un niveau académique
 */
function valider_donnees_niveau(array $donnees): array
{
    $erreurs = [];
    $validees = [];

    // Nettoyage des données
    $donnees = nettoyer_donnees($donnees);

    // Validation du nom du niveau
    $nom_niveau = trim($donnees['nom_niveau'] ?? '');
    if (empty($nom_niveau)) {
        $erreurs['nom_niveau'] = "Le nom du niveau est obligatoire.";
    } elseif (strlen($nom_niveau) < 2) {
        $erreurs['nom_niveau'] = "Le nom du niveau doit contenir au moins 2 caractères.";
    } elseif (strlen($nom_niveau) > 50) {
        $erreurs['nom_niveau'] = "Le nom du niveau ne peut pas dépasser 50 caractères.";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ0-9\s\-\.]+$/', $nom_niveau)) {
        $erreurs['nom_niveau'] = "Le nom du niveau contient des caractères invalides.";
    } else {
        $validees['nom_niveau'] = $nom_niveau;
    }

    // Validation de l'ordre d'affichage (optionnel)
    $ordre_affichage = intval($donnees['ordre_affichage'] ?? 0);
    if ($ordre_affichage < 0) {
        $erreurs['ordre_affichage'] = "L'ordre d'affichage ne peut pas être négatif.";
    } elseif ($ordre_affichage > 999) {
        $erreurs['ordre_affichage'] = "L'ordre d'affichage ne peut pas dépasser 999.";
    } else {
        $validees['ordre_affichage'] = $ordre_affichage;
    }

    // Validation de la description (optionnel)
    $description = trim($donnees['description'] ?? '');
    if (!empty($description)) {
        if (strlen($description) > 500) {
            $erreurs['description'] = "La description ne peut pas dépasser 500 caractères.";
        } else {
            $validees['description'] = $description;
        }
    }

    // Vérification d'unicité du nom de niveau
    if (empty($erreurs) && isset($validees['nom_niveau'])) {
        if (nom_niveau_existe_deja($validees['nom_niveau'])) {
            $erreurs['nom_niveau'] = "Ce nom de niveau existe déjà.";
        }
    }

    return ['erreurs' => $erreurs, 'validees' => $validees];
}

// =============================================
// VALIDATION DES DONNÉES DE SECTION ACADÉMIQUE
// =============================================

/**
 * Valide les données d'une section académique
 */
function valider_donnees_section(array $donnees): array
{
    $erreurs = [];
    $validees = [];

    // Nettoyage des données
    $donnees = nettoyer_donnees($donnees);

    // Validation du nom de la section
    $nom_section = trim($donnees['nom_section'] ?? '');
    if (empty($nom_section)) {
        $erreurs['nom_section'] = "Le nom de la section est obligatoire.";
    } elseif (strlen($nom_section) < 2) {
        $erreurs['nom_section'] = "Le nom de la section doit contenir au moins 2 caractères.";
    } elseif (strlen($nom_section) > 50) {
        $erreurs['nom_section'] = "Le nom de la section ne peut pas dépasser 50 caractères.";
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\.]+$/', $nom_section)) {
        $erreurs['nom_section'] = "Le nom de la section contient des caractères invalides.";
    } else {
        $validees['nom_section'] = $nom_section;
    }

    // Validation de la couleur (optionnel)
    $couleur = trim($donnees['couleur'] ?? '');
    if (!empty($couleur)) {
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $couleur)) {
            $erreurs['couleur'] = "Le format de la couleur n'est pas valide (ex: #FF0000).";
        } else {
            $validees['couleur'] = $couleur;
        }
    }

    // Validation de la description (optionnel)
    $description = trim($donnees['description'] ?? '');
    if (!empty($description)) {
        if (strlen($description) > 500) {
            $erreurs['description'] = "La description ne peut pas dépasser 500 caractères.";
        } else {
            $validees['description'] = $description;
        }
    }

    // Vérification d'unicité du nom de section
    if (empty($erreurs) && isset($validees['nom_section'])) {
        if (nom_section_existe_deja($validees['nom_section'])) {
            $erreurs['nom_section'] = "Ce nom de section existe déjà.";
        }
    }

    return ['erreurs' => $erreurs, 'validees' => $validees];
}

// =============================================
// VALIDATION DES DONNÉES D'ANNÉE SCOLAIRE
// =============================================

/**
 * Valide les données d'une année scolaire
 */
function valider_donnees_annee_scolaire(array $donnees): array
{
    $erreurs = [];
    $validees = [];

    // Nettoyage des données
    $donnees = nettoyer_donnees($donnees);

    // Validation du libellé de l'année scolaire
    $annee_libelle = trim($donnees['annee_libelle'] ?? '');
    if (empty($annee_libelle)) {
        $erreurs['annee_libelle'] = "Le libellé de l'année scolaire est obligatoire.";
    } elseif (strlen($annee_libelle) < 4) {
        $erreurs['annee_libelle'] = "Le libellé de l'année scolaire doit contenir au moins 4 caractères.";
    } elseif (strlen($annee_libelle) > 50) {
        $erreurs['annee_libelle'] = "Le libellé de l'année scolaire ne peut pas dépasser 50 caractères.";
    } elseif (!preg_match('/^[a-zA-Z0-9\s\-\.\/]+$/', $annee_libelle)) {
        $erreurs['annee_libelle'] = "Le libellé de l'année scolaire contient des caractères invalides.";
    } else {
        $validees['annee_libelle'] = $annee_libelle;
    }

    // Validation de la date de début
    $date_debut = trim($donnees['date_debut'] ?? '');
    if (empty($date_debut)) {
        $erreurs['date_debut'] = "La date de début est obligatoire.";
    } elseif (!strtotime($date_debut)) {
        $erreurs['date_debut'] = "Le format de la date de début n'est pas valide.";
    } else {
        $validees['date_debut'] = $date_debut;
    }

    // Validation de la date de fin
    $date_fin = trim($donnees['date_fin'] ?? '');
    if (empty($date_fin)) {
        $erreurs['date_fin'] = "La date de fin est obligatoire.";
    } elseif (!strtotime($date_fin)) {
        $erreurs['date_fin'] = "Le format de la date de fin n'est pas valide.";
    } elseif (isset($validees['date_debut']) && strtotime($date_fin) <= strtotime($validees['date_debut'])) {
        $erreurs['date_fin'] = "La date de fin doit être postérieure à la date de début.";
    } else {
        $validees['date_fin'] = $date_fin;
    }

    // Validation du statut (optionnel)
    $statut = trim($donnees['statut'] ?? 'inactive');
    $statuts_valides = ['active', 'inactive', 'archive'];
    if (!in_array($statut, $statuts_valides)) {
        $erreurs['statut'] = "Le statut sélectionné n'est pas valide.";
    } else {
        $validees['statut'] = $statut;
    }

    // Validation de la description (optionnel)
    $description = trim($donnees['description'] ?? '');
    if (!empty($description)) {
        if (strlen($description) > 1000) {
            $erreurs['description'] = "La description ne peut pas dépasser 1000 caractères.";
        } else {
            $validees['description'] = $description;
        }
    }

    // Vérification d'unicité du libellé d'année scolaire
    if (empty($erreurs) && isset($validees['annee_libelle'])) {
        if (libelle_annee_existe_deja($validees['annee_libelle'])) {
            $erreurs['annee_libelle'] = "Ce libellé d'année scolaire existe déjà.";
        }
    }

    return ['erreurs' => $erreurs, 'validees' => $validees];
}

// =============================================
// NOUVELLES FONCTIONS UTILITAIRES DE VALIDATION
// =============================================

/**
 * Vérifie si un nom de niveau existe déjà
 */
function nom_niveau_existe_deja(string $nom_niveau): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TABLE_NIVEAUX . " WHERE nom_niveau = ?");
    $stmt->execute([$nom_niveau]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si un nom de section existe déjà
 */
function nom_section_existe_deja(string $nom_section): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TABLE_SECTIONS . " WHERE nom_section = ?");
    $stmt->execute([$nom_section]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Vérifie si un libellé d'année scolaire existe déjà
 */
function libelle_annee_existe_deja(string $annee_libelle): bool
{
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . TABLE_ANNEES_SCOLAIRES . " WHERE annee_libelle = ?");
    $stmt->execute([$annee_libelle]);
    return $stmt->fetchColumn() > 0;
}