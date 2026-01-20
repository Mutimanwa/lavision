<?php
/**
 * Gestion de l'emploi du temps
 * Planning des cours par classe, matière et professeur
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/matieres.php';
require_once __DIR__ . '/classes.php';

// =============================================
// CONSTANTES POUR L'EMPLOI DU TEMPS
// =============================================

// Jours de la semaine
define('JOUR_LUNDI', 'Lundi');
define('JOUR_MARDI', 'Mardi');
define('JOUR_MERCREDI', 'Mercredi');
define('JOUR_JEUDI', 'Jeudi');
define('JOUR_VENDREDI', 'Vendredi');
define('JOUR_SAMEDI', 'Samedi');

// Types de cours
define('TYPE_COURS', 'cours');
define('TYPE_TD', 'td');
define('TYPE_TP', 'tp');
define('TYPE_EXAMEN', 'examen');

// Périodes de la journée
define('PERIODE_MATIN', 'matin');
define('PERIODE_APREM', 'aprem');
define('PERIODE_JOURNEE', 'journee');

// Statuts
define('EDT_ACTIF', 'actif');
define('EDT_SUSPENDU', 'suspendu');

// Plages horaires standards
define('HEURE_DEBUT_MATIN', '08:00:00');
define('HEURE_FIN_MATIN', '12:00:00');
define('HEURE_DEBUT_APREM', '14:00:00');
define('HEURE_FIN_APREM', '18:00:00');

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'un cours dans l'emploi du temps
 */
function emploi_temps_valider_donnees(array $donnees): array
{
    $erreurs = [];
    
    // Champs obligatoires
    $champs_requis = ['class_id', 'matiere_id', 'professeur_id', 'jour_semaine', 'heure_debut', 'heure_fin', 'annee_id'];
    
    foreach ($champs_requis as $champ) {
        if (empty($donnees[$champ])) {
            $erreurs[$champ] = "Ce champ est requis";
        }
    }
    
    // Vérifier la classe
    if (!empty($donnees['class_id'])) {
        $classe = classe_get_by_id((int)$donnees['class_id']);
        if (!$classe) {
            $erreurs['class_id'] = "Classe non trouvée";
        }
    }
    
    // Vérifier la matière
    if (!empty($donnees['matiere_id'])) {
        $matiere = matiere_get_by_id((int)$donnees['matiere_id']);
        if (!$matiere) {
            $erreurs['matiere_id'] = "Matière non trouvée";
        }
    }
    
    // Vérifier le professeur
    if (!empty($donnees['professeur_id'])) {
        $professeur = db_query_single(
            "SELECT professeur_id FROM professeurs WHERE professeur_id = :professeur_id",
            ['professeur_id' => $donnees['professeur_id']]
        );
        if (!$professeur) {
            $erreurs['professeur_id'] = "Professeur non trouvé";
        }
    }
    
    // Vérifier l'année scolaire
    if (!empty($donnees['annee_id'])) {
        $annee = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE annee_id = :annee_id",
            ['annee_id' => $donnees['annee_id']]
        );
        if (!$annee) {
            $erreurs['annee_id'] = "Année scolaire non trouvée";
        }
    }
    
    // Vérifier le jour de la semaine
    if (!empty($donnees['jour_semaine'])) {
        $jours_valides = [JOUR_LUNDI, JOUR_MARDI, JOUR_MERCREDI, JOUR_JEUDI, JOUR_VENDREDI, JOUR_SAMEDI];
        if (!in_array($donnees['jour_semaine'], $jours_valides)) {
            $erreurs['jour_semaine'] = "Jour de la semaine invalide";
        }
    }
    
    // Vérifier les heures
    if (!empty($donnees['heure_debut']) && !empty($donnees['heure_fin'])) {
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $donnees['heure_debut'])) {
            $erreurs['heure_debut'] = "Format d'heure invalide (HH:MM:SS)";
        }
        
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $donnees['heure_fin'])) {
            $erreurs['heure_fin'] = "Format d'heure invalide (HH:MM:SS)";
        }
        
        // Vérifier que l'heure de fin est après l'heure de début
        if (strtotime($donnees['heure_debut']) >= strtotime($donnees['heure_fin'])) {
            $erreurs['heure_fin'] = "L'heure de fin doit être après l'heure de début";
        }
        
        // Vérifier la durée minimale (au moins 30 minutes)
        $duree = strtotime($donnees['heure_fin']) - strtotime($donnees['heure_debut']);
        if ($duree < 1800) { // 30 minutes en secondes
            $erreurs['heure_fin'] = "La durée minimale d'un cours est de 30 minutes";
        }
        
        // Vérifier la durée maximale (max 4 heures)
        if ($duree > 14400) { // 4 heures en secondes
            $erreurs['heure_fin'] = "La durée maximale d'un cours est de 4 heures";
        }
    }
    
    // Vérifier le type de cours
    if (!empty($donnees['type_cours'])) {
        $types_valides = [TYPE_COURS, TYPE_TD, TYPE_TP, TYPE_EXAMEN];
        if (!in_array($donnees['type_cours'], $types_valides)) {
            $erreurs['type_cours'] = "Type de cours invalide";
        }
    }
    
    // Vérifier la période
    if (!empty($donnees['periode'])) {
        $periodes_valides = [PERIODE_TRIMESTRE1, PERIODE_TRIMESTRE2, PERIODE_TRIMESTRE3];
        if (!in_array($donnees['periode'], $periodes_valides)) {
            $erreurs['periode'] = "Période invalide";
        }
    }
    
    // Vérifier les dates
    if (!empty($donnees['date_debut'])) {
        $date = DateTime::createFromFormat('Y-m-d', $donnees['date_debut']);
        if (!$date || $date->format('Y-m-d') !== $donnees['date_debut']) {
            $erreurs['date_debut'] = "Format de date invalide (YYYY-MM-DD)";
        }
    }
    
    if (!empty($donnees['date_fin'])) {
        $date = DateTime::createFromFormat('Y-m-d', $donnees['date_fin']);
        if (!$date || $date->format('Y-m-d') !== $donnees['date_fin']) {
            $erreurs['date_fin'] = "Format de date invalide (YYYY-MM-DD)";
        }
    }
    
    // Vérifier que date_fin est après date_debut si les deux sont fournies
    if (!empty($donnees['date_debut']) && !empty($donnees['date_fin'])) {
        if (strtotime($donnees['date_fin']) <= strtotime($donnees['date_debut'])) {
            $erreurs['date_fin'] = "La date de fin doit être après la date de début";
        }
    }
    
    // Vérifier la salle
    if (!empty($donnees['salle']) && strlen(trim($donnees['salle'])) > 50) {
        $erreurs['salle'] = "Le nom de la salle ne peut pas dépasser 50 caractères";
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - EMPLOI DU TEMPS
// =============================================

/**
 * Créer un nouveau cours dans l'emploi du temps
 */
function emploi_temps_creer(array $donnees): array
{
    // Vérifier les permissions (admin ou proviseur uniquement)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = emploi_temps_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier les conflits d'horaires
    $conflits = emploi_temps_verifier_conflits($donnees);
    if (!empty($conflits)) {
        return [
            'success' => false,
            'error' => 'Conflit d\'horaire détecté',
            'conflits' => $conflits
        ];
    }
    
    // Vérifier que la matière est enseignée dans cette classe
    $matiere_classe = db_query_single(
        "SELECT 1 FROM emploi_du_temps e
         WHERE e.class_id = :class_id
         AND e.matiere_id = :matiere_id
         AND e.statut = 'actif'
         LIMIT 1",
        ['class_id' => $donnees['class_id'], 'matiere_id' => $donnees['matiere_id']]
    );
    
    // Si la matière n'est pas encore enseignée dans cette classe, vérifier la cohérence
    if (!$matiere_classe) {
        $classe = classe_get_by_id((int)$donnees['class_id']);
        $matiere = matiere_get_by_id((int)$donnees['matiere_id']);
        
        if ($classe && $matiere && $matiere['niveau_id'] && $matiere['niveau_id'] != $classe['niveau_id']) {
            return [
                'success' => false,
                'error' => "Cette matière n'est pas destinée au niveau de cette classe"
            ];
        }
    }
    
    try {
        // Préparer les données pour l'insertion
        $champs = [
            'class_id' => (int)$donnees['class_id'],
            'matiere_id' => (int)$donnees['matiere_id'],
            'professeur_id' => (int)$donnees['professeur_id'],
            'jour_semaine' => $donnees['jour_semaine'],
            'heure_debut' => $donnees['heure_debut'],
            'heure_fin' => $donnees['heure_fin'],
            'salle' => trim($donnees['salle'] ?? ''),
            'type_cours' => $donnees['type_cours'] ?? TYPE_COURS,
            'annee_id' => (int)$donnees['annee_id'],
            'periode' => $donnees['periode'] ?? PERIODE_TRIMESTRE1,
            'date_debut' => $donnees['date_debut'] ?? date('Y-m-d'),
            'date_fin' => $donnees['date_fin'] ?? date('Y-m-d', strtotime('+3 months')),
            'statut' => EDT_ACTIF
        ];
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO emploi_du_temps ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $edt_id = db_last_insert_id();
        
        // Journaliser l'action
        $classe = classe_get_by_id($champs['class_id']);
        $matiere = matiere_get_by_id($champs['matiere_id']);
        $professeur = db_query_single(
            "SELECT nom, prenom FROM professeurs WHERE professeur_id = :professeur_id",
            ['professeur_id' => $champs['professeur_id']]
        );
        
        log_action('Cours créé dans l\'emploi du temps', [
            'edt_id' => $edt_id,
            'class_id' => $champs['class_id'],
            'classe_nom' => $classe ? $classe['nom_complet'] : 'Inconnue',
            'matiere_id' => $champs['matiere_id'],
            'matiere_nom' => $matiere ? $matiere['nom_matiere'] : 'Inconnue',
            'professeur_id' => $champs['professeur_id'],
            'professeur_nom' => $professeur ? $professeur['prenom'] . ' ' . $professeur['nom'] : 'Inconnu',
            'jour_semaine' => $champs['jour_semaine'],
            'heure_debut' => $champs['heure_debut'],
            'heure_fin' => $champs['heure_fin'],
            'salle' => $champs['salle'],
            'periode' => $champs['periode'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'emploi_temps');
        
        return [
            'success' => true,
            'edt_id' => $edt_id,
            'message' => 'Cours créé avec succès dans l\'emploi du temps'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur emploi_temps_creer: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour un cours dans l'emploi du temps
 */
function emploi_temps_modifier(int $edt_id, array $donnees): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le cours existe
    $cours_existant = emploi_temps_get_by_id($edt_id);
    if (!$cours_existant) {
        return ['success' => false, 'error' => 'Cours non trouvé'];
    }
    
    // Valider les données
    $erreurs = emploi_temps_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Ne pas permettre la modification des champs clés (sauf pour admin)
    if (!has_role(ROLE_ADMIN)) {
        $champs_proteges = ['class_id', 'matiere_id', 'annee_id'];
        foreach ($champs_proteges as $champ) {
            if (isset($donnees[$champ]) && $donnees[$champ] != $cours_existant[$champ]) {
                return [
                    'success' => false,
                    'error' => "Vous ne pouvez pas modifier le champ '$champ'"
                ];
            }
        }
    }
    
    // Vérifier les conflits d'horaires (exclure le cours actuel)
    $donnees_conflit = array_merge($donnees, ['exclude_edt_id' => $edt_id]);
    $conflits = emploi_temps_verifier_conflits($donnees_conflit);
    
    if (!empty($conflits)) {
        return [
            'success' => false,
            'error' => 'Conflit d\'horaire détecté',
            'conflits' => $conflits
        ];
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['edt_id' => $edt_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'professeur_id', 'jour_semaine', 'heure_debut', 'heure_fin',
            'salle', 'type_cours', 'periode', 'date_debut', 'date_fin', 'statut'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                
                if (in_array($champ, ['professeur_id', 'jour_semaine', 'type_cours', 'periode', 'statut'])) {
                    $params[$champ] = $donnees[$champ];
                } elseif (in_array($champ, ['heure_debut', 'heure_fin'])) {
                    $params[$champ] = $donnees[$champ];
                } elseif (in_array($champ, ['date_debut', 'date_fin']) && empty($donnees[$champ])) {
                    $params[$champ] = null;
                } else {
                    $params[$champ] = trim($donnees[$champ]);
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Construction de la requête
        $sql = "UPDATE emploi_du_temps SET " . implode(', ', $updates) . " WHERE edt_id = :edt_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Cours modifié dans l\'emploi du temps', [
                'edt_id' => $edt_id,
                'classe_nom' => $cours_existant['classe_nom_complet'],
                'matiere_nom' => $cours_existant['matiere_nom_complet'],
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'emploi_temps');
            
            return [
                'success' => true,
                'message' => 'Cours mis à jour avec succès',
                'edt_id' => $edt_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur emploi_temps_modifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Supprimer un cours de l'emploi du temps
 */
function emploi_temps_supprimer(int $edt_id): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le cours existe
    $cours = emploi_temps_get_by_id($edt_id);
    if (!$cours) {
        return ['success' => false, 'error' => 'Cours non trouvé'];
    }
    
    // Vérifier si le cours est actif
    if ($cours['statut'] === EDT_SUSPENDU) {
        return [
            'success' => false,
            'error' => 'Ce cours est déjà suspendu'
        ];
    }
    
    try {
        // Suspendre le cours plutôt que de le supprimer (pour garder l'historique)
        $sql = "UPDATE emploi_du_temps SET statut = 'suspendu', date_fin = :date_fin WHERE edt_id = :edt_id";
        $success = db_execute($sql, [
            'edt_id' => $edt_id,
            'date_fin' => date('Y-m-d')
        ]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Cours suspendu dans l\'emploi du temps', [
                'edt_id' => $edt_id,
                'classe_nom' => $cours['classe_nom_complet'],
                'matiere_nom' => $cours['matiere_nom_complet'],
                'professeur_nom' => $cours['professeur_nom_complet'],
                'jour_semaine' => $cours['jour_semaine'],
                'heure_debut' => $cours['heure_debut'],
                'heure_fin' => $cours['heure_fin'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'emploi_temps');
            
            return [
                'success' => true,
                'message' => 'Cours suspendu avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        
    } catch (Exception $e) {
        error_log("Erreur emploi_temps_supprimer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Vérifier les conflits d'horaires pour un cours
 */
function emploi_temps_verifier_conflits(array $donnees): array
{
    $conflits = [];
    
    // Préparer les paramètres
    $params = [
        'class_id' => $donnees['class_id'],
        'jour_semaine' => $donnees['jour_semaine'],
        'heure_debut' => $donnees['heure_debut'],
        'heure_fin' => $donnees['heure_fin'],
        'annee_id' => $donnees['annee_id'],
        'periode' => $donnees['periode'] ?? PERIODE_TRIMESTRE1
    ];
    
    // Vérifier les conflits pour la classe
    $sql_classe = "SELECT e.*, m.nom_matiere, p.nom as professeur_nom, p.prenom as professeur_prenom
                   FROM emploi_du_temps e
                   JOIN matieres m ON e.matiere_id = m.matiere_id
                   JOIN professeurs p ON e.professeur_id = p.professeur_id
                   WHERE e.class_id = :class_id
                   AND e.jour_semaine = :jour_semaine
                   AND e.annee_id = :annee_id
                   AND e.periode = :periode
                   AND e.statut = 'actif'
                   AND (
                       (e.heure_debut <= :heure_debut AND e.heure_fin > :heure_debut) OR
                       (e.heure_debut < :heure_fin AND e.heure_fin >= :heure_fin) OR
                       (e.heure_debut >= :heure_debut AND e.heure_fin <= :heure_fin)
                   )";
    
    // Exclure le cours actuel si on modifie
    if (isset($donnees['exclude_edt_id'])) {
        $sql_classe .= " AND e.edt_id != :exclude_edt_id";
        $params['exclude_edt_id'] = $donnees['exclude_edt_id'];
    }
    
    $conflits_classe = db_query($sql_classe, $params);
    
    if (!empty($conflits_classe)) {
        foreach ($conflits_classe as $conflit) {
            $conflits[] = [
                'type' => 'conflit_classe',
                'message' => "Conflit avec le cours de {$conflit['nom_matiere']} ({$conflit['professeur_prenom']} {$conflit['professeur_nom']})",
                'cours_conflit' => $conflit,
                'heure_conflit' => "{$conflit['heure_debut']} - {$conflit['heure_fin']}"
            ];
        }
    }
    
    // Vérifier les conflits pour le professeur
    $sql_professeur = "SELECT e.*, m.nom_matiere, c.libelle as classe_libelle
                       FROM emploi_du_temps e
                       JOIN matieres m ON e.matiere_id = m.matiere_id
                       JOIN classes c ON e.class_id = c.class_id
                       WHERE e.professeur_id = :professeur_id
                       AND e.jour_semaine = :jour_semaine
                       AND e.annee_id = :annee_id
                       AND e.periode = :periode
                       AND e.statut = 'actif'
                       AND (
                           (e.heure_debut <= :heure_debut AND e.heure_fin > :heure_debut) OR
                           (e.heure_debut < :heure_fin AND e.heure_fin >= :heure_fin) OR
                           (e.heure_debut >= :heure_debut AND e.heure_fin <= :heure_fin)
                       )";
    
    $params_prof = array_merge($params, ['professeur_id' => $donnees['professeur_id']]);
    
    if (isset($donnees['exclude_edt_id'])) {
        $sql_professeur .= " AND e.edt_id != :exclude_edt_id";
    }
    
    $conflits_professeur = db_query($sql_professeur, $params_prof);
    
    if (!empty($conflits_professeur)) {
        foreach ($conflits_professeur as $conflit) {
            $conflits[] = [
                'type' => 'conflit_professeur',
                'message' => "Le professeur a déjà un cours de {$conflit['nom_matiere']} avec la classe {$conflit['classe_libelle']}",
                'cours_conflit' => $conflit,
                'heure_conflit' => "{$conflit['heure_debut']} - {$conflit['heure_fin']}"
            ];
        }
    }
    
    // Vérifier les conflits de salle (si une salle est spécifiée)
    if (!empty($donnees['salle'])) {
        $sql_salle = "SELECT e.*, m.nom_matiere, c.libelle as classe_libelle, 
                             p.nom as professeur_nom, p.prenom as professeur_prenom
                      FROM emploi_du_temps e
                      JOIN matieres m ON e.matiere_id = m.matiere_id
                      JOIN classes c ON e.class_id = c.class_id
                      JOIN professeurs p ON e.professeur_id = p.professeur_id
                      WHERE e.salle = :salle
                      AND e.jour_semaine = :jour_semaine
                      AND e.annee_id = :annee_id
                      AND e.periode = :periode
                      AND e.statut = 'actif'
                      AND (
                          (e.heure_debut <= :heure_debut AND e.heure_fin > :heure_debut) OR
                          (e.heure_debut < :heure_fin AND e.heure_fin >= :heure_fin) OR
                          (e.heure_debut >= :heure_debut AND e.heure_fin <= :heure_fin)
                      )";
        
        $params_salle = array_merge($params, ['salle' => $donnees['salle']]);
        
        if (isset($donnees['exclude_edt_id'])) {
            $sql_salle .= " AND e.edt_id != :exclude_edt_id";
        }
        
        $conflits_salle = db_query($sql_salle, $params_salle);
        
        if (!empty($conflits_salle)) {
            foreach ($conflits_salle as $conflit) {
                $conflits[] = [
                    'type' => 'conflit_salle',
                    'message' => "La salle est déjà occupée par le cours de {$conflit['nom_matiere']} ({$conflit['classe_libelle']})",
                    'cours_conflit' => $conflit,
                    'heure_conflit' => "{$conflit['heure_debut']} - {$conflit['heure_fin']}"
                ];
            }
        }
    }
    
    return $conflits;
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir un cours par son ID
 */
function emploi_temps_get_by_id(int $edt_id): ?array
{
    $sql = "SELECT e.*,
                   c.libelle as classe_libelle, c.salle as classe_salle,
                   n.nom_niveau,
                   s.nom_section,
                   m.code_matiere, m.nom_matiere, m.couleur as matiere_couleur,
                   p.nom as professeur_nom, p.prenom as professeur_prenom, p.telephone as professeur_telephone,
                   an.annee_libelle
            FROM emploi_du_temps e
            JOIN classes c ON e.class_id = c.class_id
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections se ON c.section_id = se.section_id
            JOIN matieres m ON e.matiere_id = m.matiere_id
            JOIN professeurs p ON e.professeur_id = p.professeur_id
            JOIN annees_scolaire an ON e.annee_id = an.annee_id
            WHERE e.edt_id = :edt_id";
    
    $cours = db_query_single($sql, ['edt_id' => $edt_id]);
    
    if ($cours) {
        // Ajouter des informations calculées
        $cours['classe_nom_complet'] = $cours['classe_libelle'] . ' - ' . $cours['nom_niveau'];
        if ($cours['nom_section']) {
            $cours['classe_nom_complet'] .= ' ' . $cours['nom_section'];
        }
        
        $cours['matiere_nom_complet'] = $cours['code_matiere'] . ' - ' . $cours['nom_matiere'];
        $cours['professeur_nom_complet'] = $cours['professeur_prenom'] . ' ' . $cours['professeur_nom'];
        
        // Calculer la durée
        $debut = strtotime($cours['heure_debut']);
        $fin = strtotime($cours['heure_fin']);
        $cours['duree_minutes'] = ($fin - $debut) / 60;
        $cours['duree_heures'] = round($cours['duree_minutes'] / 60, 2);
        
        // Déterminer la période de la journée
        $cours['periode_journee'] = emploi_temps_get_periode_journee($cours['heure_debut']);
        
        // Ajouter les statistiques
        $cours['statistiques'] = emploi_temps_get_statistiques_cours($edt_id);
    }
    
    return $cours;
}

/**
 * Obtenir l'emploi du temps d'une classe
 */
function emploi_temps_get_par_classe(int $class_id, int $annee_id, string $periode = null): array
{
    $sql = "SELECT e.*,
                   m.code_matiere, m.nom_matiere, m.couleur as matiere_couleur,
                   p.nom as professeur_nom, p.prenom as professeur_prenom,
                   an.annee_libelle
            FROM emploi_du_temps e
            JOIN matieres m ON e.matiere_id = m.matiere_id
            JOIN professeurs p ON e.professeur_id = p.professeur_id
            JOIN annees_scolaire an ON e.annee_id = an.annee_id
            WHERE e.class_id = :class_id
            AND e.annee_id = :annee_id
            AND e.statut = 'actif'";
    
    $params = ['class_id' => $class_id, 'annee_id' => $annee_id];
    
    if ($periode) {
        $sql .= " AND e.periode = :periode";
        $params['periode'] = $periode;
    }
    
    $sql .= " ORDER BY 
                CASE e.jour_semaine
                    WHEN 'Lundi' THEN 1
                    WHEN 'Mardi' THEN 2
                    WHEN 'Mercredi' THEN 3
                    WHEN 'Jeudi' THEN 4
                    WHEN 'Vendredi' THEN 5
                    WHEN 'Samedi' THEN 6
                    ELSE 7
                END,
                e.heure_debut";
    
    $cours = db_query($sql, $params);
    
    // Organiser par jour de la semaine
    $emploi_par_jour = [
        JOUR_LUNDI => [],
        JOUR_MARDI => [],
        JOUR_MERCREDI => [],
        JOUR_JEUDI => [],
        JOUR_VENDREDI => [],
        JOUR_SAMEDI => []
    ];
    
    foreach ($cours as &$cours_jour) {
        $cours_jour['professeur_nom_complet'] = $cours_jour['professeur_prenom'] . ' ' . $cours_jour['professeur_nom'];
        $cours_jour['matiere_nom_complet'] = $cours_jour['code_matiere'] . ' - ' . $cours_jour['nom_matiere'];
        
        // Calculer la durée
        $debut = strtotime($cours_jour['heure_debut']);
        $fin = strtotime($cours_jour['heure_fin']);
        $cours_jour['duree_minutes'] = ($fin - $debut) / 60;
        
        // Ajouter au jour correspondant
        $jour = $cours_jour['jour_semaine'];
        if (isset($emploi_par_jour[$jour])) {
            $emploi_par_jour[$jour][] = $cours_jour;
        }
    }
    
    // Calculer les statistiques
    $statistiques = emploi_temps_calculer_statistiques_classe($cours);
    
    return [
        'cours' => $emploi_par_jour,
        'statistiques' => $statistiques,
        'classe_id' => $class_id,
        'annee_id' => $annee_id,
        'periode' => $periode
    ];
}

/**
 * Obtenir l'emploi du temps d'un professeur
 */
function emploi_temps_get_par_professeur(int $professeur_id, int $annee_id, string $periode = null): array
{
    $sql = "SELECT e.*,
                   m.code_matiere, m.nom_matiere, m.couleur as matiere_couleur,
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   se.nom_section,
                   an.annee_libelle
            FROM emploi_du_temps e
            JOIN matieres m ON e.matiere_id = m.matiere_id
            JOIN classes c ON e.class_id = c.class_id
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections se ON c.section_id = se.section_id
            JOIN annees_scolaire an ON e.annee_id = an.annee_id
            WHERE e.professeur_id = :professeur_id
            AND e.annee_id = :annee_id
            AND e.statut = 'actif'";
    
    $params = ['professeur_id' => $professeur_id, 'annee_id' => $annee_id];
    
    if ($periode) {
        $sql .= " AND e.periode = :periode";
        $params['periode'] = $periode;
    }
    
    $sql .= " ORDER BY 
                CASE e.jour_semaine
                    WHEN 'Lundi' THEN 1
                    WHEN 'Mardi' THEN 2
                    WHEN 'Mercredi' THEN 3
                    WHEN 'Jeudi' THEN 4
                    WHEN 'Vendredi' THEN 5
                    WHEN 'Samedi' THEN 6
                    ELSE 7
                END,
                e.heure_debut";
    
    $cours = db_query($sql, $params);
    
    // Organiser par jour de la semaine
    $emploi_par_jour = [
        JOUR_LUNDI => [],
        JOUR_MARDI => [],
        JOUR_MERCREDI => [],
        JOUR_JEUDI => [],
        JOUR_VENDREDI => [],
        JOUR_SAMEDI => []
    ];
    
    foreach ($cours as &$cours_jour) {
        $cours_jour['classe_nom_complet'] = $cours_jour['classe_libelle'] . ' - ' . $cours_jour['nom_niveau'];
        if ($cours_jour['nom_section']) {
            $cours_jour['classe_nom_complet'] .= ' ' . $cours_jour['nom_section'];
        }
        
        $cours_jour['matiere_nom_complet'] = $cours_jour['code_matiere'] . ' - ' . $cours_jour['nom_matiere'];
        
        // Calculer la durée
        $debut = strtotime($cours_jour['heure_debut']);
        $fin = strtotime($cours_jour['heure_fin']);
        $cours_jour['duree_minutes'] = ($fin - $debut) / 60;
        
        // Ajouter au jour correspondant
        $jour = $cours_jour['jour_semaine'];
        if (isset($emploi_par_jour[$jour])) {
            $emploi_par_jour[$jour][] = $cours_jour;
        }
    }
    
    // Calculer les statistiques
    $statistiques = emploi_temps_calculer_statistiques_professeur($cours);
    
    return [
        'cours' => $emploi_par_jour,
        'statistiques' => $statistiques,
        'professeur_id' => $professeur_id,
        'annee_id' => $annee_id,
        'periode' => $periode
    ];
}

/**
 * Rechercher des cours avec filtres
 */
function emploi_temps_rechercher(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   e.*,
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   se.nom_section,
                   m.code_matiere, m.nom_matiere,
                   p.nom as professeur_nom, p.prenom as professeur_prenom,
                   an.annee_libelle
            FROM emploi_du_temps e
            JOIN classes c ON e.class_id = c.class_id
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections se ON c.section_id = se.section_id
            JOIN matieres m ON e.matiere_id = m.matiere_id
            JOIN professeurs p ON e.professeur_id = p.professeur_id
            JOIN annees_scolaire an ON e.annee_id = an.annee_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(c.libelle LIKE :{$key} OR m.nom_matiere LIKE :{$key} OR p.nom LIKE :{$key} OR p.prenom LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par classe
    if (!empty($filtres['class_id'])) {
        $sql .= " AND e.class_id = :class_id";
        $params['class_id'] = $filtres['class_id'];
    }
    
    // Filtre par matière
    if (!empty($filtres['matiere_id'])) {
        $sql .= " AND e.matiere_id = :matiere_id";
        $params['matiere_id'] = $filtres['matiere_id'];
    }
    
    // Filtre par professeur
    if (!empty($filtres['professeur_id'])) {
        $sql .= " AND e.professeur_id = :professeur_id";
        $params['professeur_id'] = $filtres['professeur_id'];
    }
    
    // Filtre par année scolaire
    if (!empty($filtres['annee_id'])) {
        $sql .= " AND e.annee_id = :annee_id";
        $params['annee_id'] = $filtres['annee_id'];
    }
    
    // Filtre par période
    if (!empty($filtres['periode'])) {
        $sql .= " AND e.periode = :periode";
        $params['periode'] = $filtres['periode'];
    }
    
    // Filtre par jour de la semaine
    if (!empty($filtres['jour_semaine'])) {
        $sql .= " AND e.jour_semaine = :jour_semaine";
        $params['jour_semaine'] = $filtres['jour_semaine'];
    }
    
    // Filtre par type de cours
    if (!empty($filtres['type_cours'])) {
        $sql .= " AND e.type_cours = :type_cours";
        $params['type_cours'] = $filtres['type_cours'];
    }
    
    // Filtre par salle
    if (!empty($filtres['salle'])) {
        $sql .= " AND e.salle LIKE :salle";
        $params['salle'] = "%{$filtres['salle']}%";
    }
    
    // Filtre par statut
    if (isset($filtres['statut'])) {
        $sql .= " AND e.statut = :statut";
        $params['statut'] = $filtres['statut'];
    } else {
        // Par défaut, ne montrer que les cours actifs
        $sql .= " AND e.statut = 'actif'";
    }
    
    // Filtre par date
    if (!empty($filtres['date_debut'])) {
        $sql .= " AND e.date_debut >= :date_debut";
        $params['date_debut'] = $filtres['date_debut'];
    }
    
    if (!empty($filtres['date_fin'])) {
        $sql .= " AND e.date_fin <= :date_fin";
        $params['date_fin'] = $filtres['date_fin'];
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'e.jour_semaine, e.heure_debut';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY {$order_by} {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $cours = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($cours as &$cours_item) {
            $cours_item['classe_nom_complet'] = $cours_item['classe_libelle'] . ' - ' . $cours_item['nom_niveau'];
            if ($cours_item['nom_section']) {
                $cours_item['classe_nom_complet'] .= ' ' . $cours_item['nom_section'];
            }
            
            $cours_item['matiere_nom_complet'] = $cours_item['code_matiere'] . ' - ' . $cours_item['nom_matiere'];
            $cours_item['professeur_nom_complet'] = $cours_item['professeur_prenom'] . ' ' . $cours_item['professeur_nom'];
            
            // Calculer la durée
            $debut = strtotime($cours_item['heure_debut']);
            $fin = strtotime($cours_item['heure_fin']);
            $cours_item['duree_minutes'] = ($fin - $debut) / 60;
            $cours_item['duree_heures'] = round($cours_item['duree_minutes'] / 60, 2);
            
            // Déterminer la période de la journée
            $cours_item['periode_journee'] = emploi_temps_get_periode_journee($cours_item['heure_debut']);
        }
        
        return [
            'success' => true,
            'cours' => $cours,
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
        error_log("Erreur emploi_temps_rechercher: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

// =============================================
// FONCTIONS DE GÉNÉRATION D'EMPLOI DU TEMPS
// =============================================

/**
 * Générer un emploi du temps automatique pour une classe
 */
function emploi_temps_generer_automatique(int $class_id, int $annee_id, string $periode): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Récupérer la classe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Récupérer les matières de la classe avec leurs heures par semaine
    $matieres = classe_get_matieres($class_id);
    
    if (empty($matieres)) {
        return ['success' => false, 'error' => 'Aucune matière définie pour cette classe'];
    }
    
    // Récupérer les professeurs disponibles pour chaque matière
    $matieres_avec_professeurs = [];
    
    foreach ($matieres as $matiere) {
        $professeurs = matiere_get_professeurs($matiere['matiere_id']);
        
        if (empty($professeurs)) {
            return [
                'success' => false,
                'error' => "Aucun professeur disponible pour la matière: {$matiere['nom_matiere']}"
            ];
        }
        
        $matieres_avec_professeurs[] = [
            'matiere' => $matiere,
            'professeurs' => $professeurs,
            'heures_restantes' => $matiere['heures_semaine']
        ];
    }
    
    // Définir les créneaux horaires standards
    $creneaux = emploi_temps_generer_creneaux_standards();
    
    // Initialiser l'emploi du temps
    $emploi_genere = [
        JOUR_LUNDI => [],
        JOUR_MARDI => [],
        JOUR_MERCREDI => [],
        JOUR_JEUDI => [],
        JOUR_VENDREDI => [],
        JOUR_SAMEDI => []
    ];
    
    // Variables de suivi
    $cours_crees = 0;
    $erreurs = [];
    $assignations = [];
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        // Pour chaque matière, essayer de placer ses heures
        foreach ($matieres_avec_professeurs as $index => $matiere_data) {
            $matiere = $matiere_data['matiere'];
            $heures_a_placer = $matiere_data['heures_restantes'];
            
            // Essayer de placer la matière en blocs de 2 heures (préférable) ou 1 heure
            while ($heures_a_placer > 0) {
                $bloc_heures = min(2, $heures_a_placer); // Préférer des blocs de 2 heures
                
                // Trouver un créneau disponible
                $creneau_trouve = false;
                
                foreach ($emploi_genere as $jour => $cours_du_jour) {
                    if ($creneau_trouve) break;
                    
                    foreach ($creneaux as $creneau) {
                        if ($creneau['duree_heures'] != $bloc_heures) {
                            continue; // Chercher un créneau de la bonne durée
                        }
                        
                        // Vérifier si le créneau est disponible
                        $creneau_libre = true;
                        
                        foreach ($cours_du_jour as $cours_existant) {
                            // Vérifier les conflits d'horaires
                            if (emploi_temps_creneaux_chevauchent(
                                $creneau['heure_debut'], $creneau['heure_fin'],
                                $cours_existant['heure_debut'], $cours_existant['heure_fin']
                            )) {
                                $creneau_libre = false;
                                break;
                            }
                        }
                        
                        if ($creneau_libre) {
                            // Trouver un professeur disponible
                            $professeur_trouve = null;
                            
                            foreach ($matiere_data['professeurs'] as $professeur) {
                                // Vérifier si le professeur est disponible à ce créneau
                                $prof_disponible = emploi_temps_verifier_disponibilite_professeur(
                                    $professeur['professeur_id'],
                                    $jour,
                                    $creneau['heure_debut'],
                                    $creneau['heure_fin'],
                                    $annee_id,
                                    $periode,
                                    $assignations
                                );
                                
                                if ($prof_disponible) {
                                    $professeur_trouve = $professeur;
                                    break;
                                }
                            }
                            
                            if ($professeur_trouve) {
                                // Créer le cours
                                $donnees_cours = [
                                    'class_id' => $class_id,
                                    'matiere_id' => $matiere['matiere_id'],
                                    'professeur_id' => $professeur_trouve['professeur_id'],
                                    'jour_semaine' => $jour,
                                    'heure_debut' => $creneau['heure_debut'],
                                    'heure_fin' => $creneau['heure_fin'],
                                    'salle' => $classe['salle'] ?? 'Salle ' . $classe['libelle'],
                                    'type_cours' => TYPE_COURS,
                                    'annee_id' => $annee_id,
                                    'periode' => $periode,
                                    'date_debut' => date('Y-m-d'),
                                    'date_fin' => date('Y-m-d', strtotime('+3 months'))
                                ];
                                
                                $resultat = emploi_temps_creer($donnees_cours);
                                
                                if ($resultat['success']) {
                                    // Ajouter au planning généré
                                    $emploi_genere[$jour][] = [
                                        'edt_id' => $resultat['edt_id'],
                                        'matiere_nom' => $matiere['nom_matiere'],
                                        'professeur_nom' => $professeur_trouve['nom_complet'],
                                        'heure_debut' => $creneau['heure_debut'],
                                        'heure_fin' => $creneau['heure_fin'],
                                        'salle' => $donnees_cours['salle']
                                    ];
                                    
                                    // Enregistrer l'assignation
                                    $assignations[] = [
                                        'professeur_id' => $professeur_trouve['professeur_id'],
                                        'jour' => $jour,
                                        'heure_debut' => $creneau['heure_debut'],
                                        'heure_fin' => $creneau['heure_fin']
                                    ];
                                    
                                    $cours_crees++;
                                    $heures_a_placer -= $bloc_heures;
                                    $creneau_trouve = true;
                                    break;
                                } else {
                                    $erreurs[] = "Erreur création cours {$matiere['nom_matiere']}: " . 
                                                ($resultat['error'] ?? 'Erreur inconnue');
                                }
                            }
                        }
                    }
                }
                
                // Si on n'a pas trouvé de créneau pour ce bloc, essayer avec 1 heure
                if (!$creneau_trouve && $bloc_heures == 2) {
                    $heures_a_placer = $matiere_data['heures_restantes']; // Réinitialiser
                    continue; // Réessayer avec des blocs de 1 heure
                }
                
                // Si toujours pas trouvé, abandonner pour cette matière
                if (!$creneau_trouve) {
                    $erreurs[] = "Impossible de placer {$heures_a_placer} heure(s) pour {$matiere['nom_matiere']}";
                    break;
                }
            }
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser la génération
        log_action('Emploi du temps généré automatiquement', [
            'class_id' => $class_id,
            'classe_nom' => $classe['nom_complet'],
            'annee_id' => $annee_id,
            'periode' => $periode,
            'cours_crees' => $cours_crees,
            'erreurs' => $erreurs,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'emploi_temps');
        
        return [
            'success' => true,
            'cours_crees' => $cours_crees,
            'emploi_genere' => $emploi_genere,
            'erreurs' => $erreurs,
            'message' => "Génération terminée: {$cours_crees} cours créés"
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur emploi_temps_generer_automatique: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la génération'];
    }
}

/**
 * Générer les créneaux horaires standards
 */
function emploi_temps_generer_creneaux_standards(): array
{
    return [
        ['heure_debut' => '08:00:00', 'heure_fin' => '09:00:00', 'duree_heures' => 1],
        ['heure_debut' => '09:00:00', 'heure_fin' => '10:00:00', 'duree_heures' => 1],
        ['heure_debut' => '10:00:00', 'heure_fin' => '11:00:00', 'duree_heures' => 1],
        ['heure_debut' => '11:00:00', 'heure_fin' => '12:00:00', 'duree_heures' => 1],
        ['heure_debut' => '14:00:00', 'heure_fin' => '15:00:00', 'duree_heures' => 1],
        ['heure_debut' => '15:00:00', 'heure_fin' => '16:00:00', 'duree_heures' => 1],
        ['heure_debut' => '16:00:00', 'heure_fin' => '17:00:00', 'duree_heures' => 1],
        ['heure_debut' => '17:00:00', 'heure_fin' => '18:00:00', 'duree_heures' => 1],
        
        // Créneaux de 2 heures
        ['heure_debut' => '08:00:00', 'heure_fin' => '10:00:00', 'duree_heures' => 2],
        ['heure_debut' => '10:00:00', 'heure_fin' => '12:00:00', 'duree_heures' => 2],
        ['heure_debut' => '14:00:00', 'heure_fin' => '16:00:00', 'duree_heures' => 2],
        ['heure_debut' => '16:00:00', 'heure_fin' => '18:00:00', 'duree_heures' => 2]
    ];
}

/**
 * Vérifier si deux créneaux se chevauchent
 */
function emploi_temps_creneaux_chevauchent(
    string $debut1, string $fin1, 
    string $debut2, string $fin2
): bool {
    $d1 = strtotime($debut1);
    $f1 = strtotime($fin1);
    $d2 = strtotime($debut2);
    $f2 = strtotime($fin2);
    
    return ($d1 < $f2 && $f1 > $d2);
}

/**
 * Vérifier la disponibilité d'un professeur
 */
function emploi_temps_verifier_disponibilite_professeur(
    int $professeur_id, 
    string $jour, 
    string $heure_debut, 
    string $heure_fin,
    int $annee_id,
    string $periode,
    array $assignations = []
): bool {
    // Vérifier dans les assignations en cours
    foreach ($assignations as $assignation) {
        if ($assignation['professeur_id'] == $professeur_id &&
            $assignation['jour'] == $jour &&
            emploi_temps_creneaux_chevauchent(
                $heure_debut, $heure_fin,
                $assignation['heure_debut'], $assignation['heure_fin']
            )) {
            return false;
        }
    }
    
    // Vérifier dans la base de données
    $sql = "SELECT 1 FROM emploi_du_temps e
            WHERE e.professeur_id = :professeur_id
            AND e.jour_semaine = :jour
            AND e.annee_id = :annee_id
            AND e.periode = :periode
            AND e.statut = 'actif'
            AND (
                (e.heure_debut <= :heure_debut AND e.heure_fin > :heure_debut) OR
                (e.heure_debut < :heure_fin AND e.heure_fin >= :heure_fin) OR
                (e.heure_debut >= :heure_debut AND e.heure_fin <= :heure_fin)
            )";
    
    $resultat = db_query_single($sql, [
        'professeur_id' => $professeur_id,
        'jour' => $jour,
        'annee_id' => $annee_id,
        'periode' => $periode,
        'heure_debut' => $heure_debut,
        'heure_fin' => $heure_fin
    ]);
    
    return $resultat === null;
}

/**
 * Obtenir la période de la journée (matin/après-midi)
 */
function emploi_temps_get_periode_journee(string $heure): string
{
    $heure_int = (int)substr($heure, 0, 2);
    
    if ($heure_int < 12) {
        return PERIODE_MATIN;
    } else {
        return PERIODE_APREM;
    }
}

// =============================================
// FONCTIONS DE STATISTIQUES
// =============================================

/**
 * Calculer les statistiques pour une classe
 */
function emploi_temps_calculer_statistiques_classe(array $cours): array
{
    $statistiques = [
        'total_cours' => count($cours),
        'total_heures' => 0,
        'par_jour' => [],
        'par_matiere' => [],
        'par_professeur' => []
    ];
    
    // Initialiser les compteurs par jour
    $jours = [JOUR_LUNDI, JOUR_MARDI, JOUR_MERCREDI, JOUR_JEUDI, JOUR_VENDREDI, JOUR_SAMEDI];
    foreach ($jours as $jour) {
        $statistiques['par_jour'][$jour] = [
            'nombre_cours' => 0,
            'heures' => 0
        ];
    }
    
    // Calculer les totaux
    foreach ($cours as $cours_item) {
        // Durée en heures
        $duree = $cours_item['duree_minutes'] / 60;
        $statistiques['total_heures'] += $duree;
        
        // Par jour
        $jour = $cours_item['jour_semaine'];
        $statistiques['par_jour'][$jour]['nombre_cours']++;
        $statistiques['par_jour'][$jour]['heures'] += $duree;
        
        // Par matière
        $matiere_id = $cours_item['matiere_id'];
        if (!isset($statistiques['par_matiere'][$matiere_id])) {
            $statistiques['par_matiere'][$matiere_id] = [
                'matiere_nom' => $cours_item['nom_matiere'],
                'nombre_cours' => 0,
                'heures' => 0
            ];
        }
        $statistiques['par_matiere'][$matiere_id]['nombre_cours']++;
        $statistiques['par_matiere'][$matiere_id]['heures'] += $duree;
        
        // Par professeur
        $professeur_id = $cours_item['professeur_id'];
        if (!isset($statistiques['par_professeur'][$professeur_id])) {
            $statistiques['par_professeur'][$professeur_id] = [
                'professeur_nom' => $cours_item['professeur_prenom'] . ' ' . $cours_item['professeur_nom'],
                'nombre_cours' => 0,
                'heures' => 0
            ];
        }
        $statistiques['par_professeur'][$professeur_id]['nombre_cours']++;
        $statistiques['par_professeur'][$professeur_id]['heures'] += $duree;
    }
    
    // Arrondir les heures
    $statistiques['total_heures'] = round($statistiques['total_heures'], 2);
    
    foreach ($statistiques['par_jour'] as &$jour) {
        $jour['heures'] = round($jour['heures'], 2);
    }
    
    foreach ($statistiques['par_matiere'] as &$matiere) {
        $matiere['heures'] = round($matiere['heures'], 2);
    }
    
    foreach ($statistiques['par_professeur'] as &$professeur) {
        $professeur['heures'] = round($professeur['heures'], 2);
    }
    
    return $statistiques;
}

/**
 * Calculer les statistiques pour un professeur
 */
function emploi_temps_calculer_statistiques_professeur(array $cours): array
{
    $statistiques = [
        'total_cours' => count($cours),
        'total_heures' => 0,
        'par_jour' => [],
        'par_classe' => [],
        'par_matiere' => []
    ];
    
    // Initialiser les compteurs par jour
    $jours = [JOUR_LUNDI, JOUR_MARDI, JOUR_MERCREDI, JOUR_JEUDI, JOUR_VENDREDI, JOUR_SAMEDI];
    foreach ($jours as $jour) {
        $statistiques['par_jour'][$jour] = [
            'nombre_cours' => 0,
            'heures' => 0
        ];
    }
    
    // Calculer les totaux
    foreach ($cours as $cours_item) {
        // Durée en heures
        $duree = $cours_item['duree_minutes'] / 60;
        $statistiques['total_heures'] += $duree;
        
        // Par jour
        $jour = $cours_item['jour_semaine'];
        $statistiques['par_jour'][$jour]['nombre_cours']++;
        $statistiques['par_jour'][$jour]['heures'] += $duree;
        
        // Par classe
        $class_id = $cours_item['class_id'];
        if (!isset($statistiques['par_classe'][$class_id])) {
            $statistiques['par_classe'][$class_id] = [
                'classe_nom' => $cours_item['classe_nom_complet'],
                'nombre_cours' => 0,
                'heures' => 0
            ];
        }
        $statistiques['par_classe'][$class_id]['nombre_cours']++;
        $statistiques['par_classe'][$class_id]['heures'] += $duree;
        
        // Par matière
        $matiere_id = $cours_item['matiere_id'];
        if (!isset($statistiques['par_matiere'][$matiere_id])) {
            $statistiques['par_matiere'][$matiere_id] = [
                'matiere_nom' => $cours_item['nom_matiere'],
                'nombre_cours' => 0,
                'heures' => 0
            ];
        }
        $statistiques['par_matiere'][$matiere_id]['nombre_cours']++;
        $statistiques['par_matiere'][$matiere_id]['heures'] += $duree;
    }
    
    // Arrondir les heures
    $statistiques['total_heures'] = round($statistiques['total_heures'], 2);
    
    foreach ($statistiques['par_jour'] as &$jour) {
        $jour['heures'] = round($jour['heures'], 2);
    }
    
    foreach ($statistiques['par_classe'] as &$classe) {
        $classe['heures'] = round($classe['heures'], 2);
    }
    
    foreach ($statistiques['par_matiere'] as &$matiere) {
        $matiere['heures'] = round($matiere['heures'], 2);
    }
    
    return $statistiques;
}

/**
 * Obtenir les statistiques d'un cours spécifique
 */
function emploi_temps_get_statistiques_cours(int $edt_id): array
{
    $statistiques = [
        'absences' => 0,
        'notes_enregistrees' => 0,
        'taux_participation' => 0
    ];
    
    // Compter les absences pour ce cours
    $sql_absences = "SELECT COUNT(*) as count
                     FROM absences a
                     JOIN emploi_du_temps e ON a.matiere_id = e.matiere_id
                     WHERE e.edt_id = :edt_id
                     AND a.justifiee = 0";
    
    $result_absences = db_query_single($sql_absences, ['edt_id' => $edt_id]);
    $statistiques['absences'] = $result_absences['count'] ?? 0;
    
    // Compter les notes enregistrées pour ce cours
    $sql_notes = "SELECT COUNT(DISTINCT n.note_id) as count
                  FROM notes n
                  JOIN emploi_du_temps e ON n.matiere_id = e.matiere_id
                  WHERE e.edt_id = :edt_id
                  AND n.est_rectifiee = 0";
    
    $result_notes = db_query_single($sql_notes, ['edt_id' => $edt_id]);
    $statistiques['notes_enregistrees'] = $result_notes['count'] ?? 0;
    
    return $statistiques;
}

// =============================================
// FONCTIONS D'EXPORT ET RAPPORTS
// =============================================

/**
 * Exporter l'emploi du temps d'une classe au format CSV
 */
function emploi_temps_exporter_csv(int $class_id, int $annee_id, string $periode): array
{
    // Vérifier les permissions
    if (!check_access('emploi_temps', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Récupérer l'emploi du temps
    $emploi = emploi_temps_get_par_classe($class_id, $annee_id, $periode);
    
    if (!isset($emploi['cours']) || empty(array_filter($emploi['cours']))) {
        return ['success' => false, 'error' => 'Aucun cours trouvé'];
    }
    
    // Générer le CSV
    $output = fopen('php://temp', 'r+');
    
    // En-têtes
    $headers = ['Jour', 'Heure Début', 'Heure Fin', 'Durée', 'Matière', 'Professeur', 'Salle', 'Type Cours'];
    fputcsv($output, $headers, ';');
    
    // Données
    foreach ($emploi['cours'] as $jour => $cours_du_jour) {
        foreach ($cours_du_jour as $cours) {
            $duree = $cours['duree_minutes'] . ' min';
            
            $row = [
                $jour,
                substr($cours['heure_debut'], 0, 5), // HH:MM
                substr($cours['heure_fin'], 0, 5),   // HH:MM
                $duree,
                $cours['nom_matiere'],
                $cours['professeur_prenom'] . ' ' . $cours['professeur_nom'],
                $cours['salle'] ?? '',
                $cours['type_cours']
            ];
            
            fputcsv($output, $row, ';');
        }
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    // Journaliser l'export
    log_action('Export emploi du temps CSV', [
        'class_id' => $class_id,
        'annee_id' => $annee_id,
        'periode' => $periode,
        'nombre_cours' => array_sum(array_map('count', $emploi['cours'])),
        'par_utilisateur' => $_SESSION['user_id'] ?? null
    ], 'emploi_temps');
    
    return [
        'success' => true,
        'data' => $csv,
        'format' => 'csv',
        'extension' => 'csv',
        'mime_type' => 'text/csv',
        'filename' => 'emploi_temps_' . $class_id . '_' . $periode . '_' . date('Ymd_His') . '.csv'
    ];
}

/**
 * Exporter l'emploi du temps d'un professeur au format PDF
 */
function emploi_temps_exporter_pdf_professeur(int $professeur_id, int $annee_id, string $periode): array
{
    // Vérifier les permissions
    if (!check_access('emploi_temps', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Récupérer l'emploi du temps
    $emploi = emploi_temps_get_par_professeur($professeur_id, $annee_id, $periode);
    
    if (!isset($emploi['cours']) || empty(array_filter($emploi['cours']))) {
        return ['success' => false, 'error' => 'Aucun cours trouvé'];
    }
    
    // Récupérer les informations du professeur
    $professeur = db_query_single(
        "SELECT nom, prenom, telephone, email FROM professeurs WHERE professeur_id = :professeur_id",
        ['professeur_id' => $professeur_id]
    );
    
    // Générer le HTML pour le PDF
    $html = emploi_temps_generer_html_pdf($emploi, $professeur, 'professeur');
    
    // En production, convertir en PDF avec une librairie comme TCPDF
    // Pour l'instant, on retourne le HTML
    $pdf_content = $html;
    
    // Journaliser l'export
    log_action('Export emploi du temps PDF professeur', [
        'professeur_id' => $professeur_id,
        'annee_id' => $annee_id,
        'periode' => $periode,
        'nombre_cours' => array_sum(array_map('count', $emploi['cours'])),
        'par_utilisateur' => $_SESSION['user_id'] ?? null
    ], 'emploi_temps');
    
    return [
        'success' => true,
        'data' => $pdf_content,
        'format' => 'pdf',
        'extension' => 'pdf',
        'mime_type' => 'application/pdf',
        'filename' => 'emploi_temps_prof_' . $professeur_id . '_' . $periode . '_' . date('Ymd_His') . '.pdf'
    ];
}

/**
 * Générer le HTML pour un PDF d'emploi du temps
 */
function emploi_temps_generer_html_pdf(array $emploi, array $info, string $type): string
{
    $titre = $type === 'professeur' ? 
        "Emploi du temps - {$info['prenom']} {$info['nom']}" : 
        "Emploi du temps - Classe";
    
    $html = '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . e($titre) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1, h2, h3 { color: #2c3e50; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .header { text-align: center; margin-bottom: 30px; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #7f8c8d; }
            .info-block { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
            .jour-header { background-color: #3498db; color: white; padding: 10px; margin-top: 20px; }
            .cours-item { margin-bottom: 10px; padding: 10px; border-left: 4px solid #2ecc71; background: #f9f9f9; }
            .heure { font-weight: bold; color: #e74c3c; }
            .matiere { font-weight: bold; }
            .professeur, .classe { color: #7f8c8d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . e(ECOLE_NOM) . '</h1>
            <h2>' . e($titre) . '</h2>
            <p>Période: ' . e(ucfirst(str_replace('trimestre', 'Trimestre ', $emploi['periode']))) . ' | Date d\'édition: ' . date('d/m/Y H:i') . '</p>
        </div>';
    
    // Informations détaillées
    $html .= '
        <div class="info-block">
            <h3>Informations</h3>
            <p><strong>Année scolaire:</strong> ' . e($emploi['annee_id']) . '</p>';
    
    if ($type === 'professeur' && isset($info['telephone'])) {
        $html .= '<p><strong>Téléphone:</strong> ' . e($info['telephone']) . '</p>';
    }
    
    if ($type === 'professeur' && isset($info['email'])) {
        $html .= '<p><strong>Email:</strong> ' . e($info['email']) . '</p>';
    }
    
    $html .= '<p><strong>Nombre total de cours:</strong> ' . e(array_sum(array_map('count', $emploi['cours']))) . '</p>
        </div>';
    
    // Emploi du temps par jour
    foreach ($emploi['cours'] as $jour => $cours_du_jour) {
        if (!empty($cours_du_jour)) {
            $html .= '
                <div class="jour-header">
                    <h3>' . e($jour) . '</h3>
                </div>';
            
            foreach ($cours_du_jour as $cours) {
                $heure_debut = substr($cours['heure_debut'], 0, 5);
                $heure_fin = substr($cours['heure_fin'], 0, 5);
                
                $html .= '
                    <div class="cours-item">
                        <div class="heure">' . e($heure_debut) . ' - ' . e($heure_fin) . '</div>
                        <div class="matiere">' . e($cours['nom_matiere'] ?? $cours['matiere_nom_complet']) . '</div>';
                
                if ($type === 'professeur') {
                    $html .= '<div class="classe">Classe: ' . e($cours['classe_nom_complet']) . '</div>';
                } else {
                    $html .= '<div class="professeur">Professeur: ' . e($cours['professeur_prenom'] . ' ' . $cours['professeur_nom']) . '</div>';
                }
                
                if (!empty($cours['salle'])) {
                    $html .= '<div>Salle: ' . e($cours['salle']) . '</div>';
                }
                
                $html .= '<div>Type: ' . e($cours['type_cours']) . '</div>
                    </div>';
            }
        }
    }
    
    // Statistiques
    if (isset($emploi['statistiques'])) {
        $stats = $emploi['statistiques'];
        $html .= '
            <div class="info-block">
                <h3>Statistiques</h3>
                <p><strong>Heures totales par semaine:</strong> ' . e($stats['total_heures'] ?? 0) . 'h</p>
                <p><strong>Nombre total de cours:</strong> ' . e($stats['total_cours'] ?? 0) . '</p>
            </div>';
    }
    
    $html .= '
        <div class="footer">
            <p>Document généré automatiquement par le système de gestion scolaire</p>
            <p>' . e(ECOLE_NOM) . ' - ' . e(ECOLE_ADRESSE) . ' - ' . e(ECOLE_TELEPHONE) . '</p>
            <p>' . e(ECOLE_DEVISE) . '</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

// =============================================
// FONCTIONS DE VÉRIFICATION ET CONTRÔLE
// =============================================

/**
 * Vérifier l'intégrité de l'emploi du temps (détecter les anomalies)
 */
function emploi_temps_verifier_integrite(int $class_id, int $annee_id, string $periode): array
{
    $anomalies = [];
    
    // Récupérer l'emploi du temps
    $emploi = emploi_temps_get_par_classe($class_id, $annee_id, $periode);
    
    // Vérifier les chevauchements
    $chevauchements = emploi_temps_detecter_chevauchements($emploi['cours']);
    if (!empty($chevauchements)) {
        $anomalies = array_merge($anomalies, $chevauchements);
    }
    
    // Vérifier les créneaux horaires inhabituels
    $creneaux_inhabituels = emploi_temps_detecter_creneaux_inhabituels($emploi['cours']);
    if (!empty($creneaux_inhabituels)) {
        $anomalies = array_merge($anomalies, $creneaux_inhabituels);
    }
    
    // Vérifier les salles non spécifiées
    $salles_manquantes = emploi_temps_detecter_salles_manquantes($emploi['cours']);
    if (!empty($salles_manquantes)) {
        $anomalies = array_merge($anomalies, $salles_manquantes);
    }
    
    // Vérifier la charge horaire des professeurs
    $charge_professeurs = emploi_temps_verifier_charge_professeurs($emploi['cours']);
    if (!empty($charge_professeurs)) {
        $anomalies = array_merge($anomalies, $charge_professeurs);
    }
    
    return [
        'success' => true,
        'class_id' => $class_id,
        'annee_id' => $annee_id,
        'periode' => $periode,
        'anomalies' => $anomalies,
        'nombre_anomalies' => count($anomalies),
        'date_verification' => date('Y-m-d H:i:s')
    ];
}

/**
 * Détecter les chevauchements dans l'emploi du temps
 */
function emploi_temps_detecter_chevauchements(array $cours_par_jour): array
{
    $anomalies = [];
    
    foreach ($cours_par_jour as $jour => $cours_du_jour) {
        // Trier les cours par heure de début
        usort($cours_du_jour, function($a, $b) {
            return strcmp($a['heure_debut'], $b['heure_debut']);
        });
        
        // Vérifier les chevauchements
        for ($i = 0; $i < count($cours_du_jour) - 1; $i++) {
            for ($j = $i + 1; $j < count($cours_du_jour); $j++) {
                $cours1 = $cours_du_jour[$i];
                $cours2 = $cours_du_jour[$j];
                
                if (emploi_temps_creneaux_chevauchent(
                    $cours1['heure_debut'], $cours1['heure_fin'],
                    $cours2['heure_debut'], $cours2['heure_fin']
                )) {
                    $anomalies[] = [
                        'type' => 'chevauchement',
                        'jour' => $jour,
                        'message' => "Chevauchement entre {$cours1['nom_matiere']} et {$cours2['nom_matiere']}",
                        'cours1' => [
                            'matiere' => $cours1['nom_matiere'],
                            'heure' => "{$cours1['heure_debut']} - {$cours1['heure_fin']}",
                            'professeur' => $cours1['professeur_prenom'] . ' ' . $cours1['professeur_nom']
                        ],
                        'cours2' => [
                            'matiere' => $cours2['nom_matiere'],
                            'heure' => "{$cours2['heure_debut']} - {$cours2['heure_fin']}",
                            'professeur' => $cours2['professeur_prenom'] . ' ' . $cours2['professeur_nom']
                        ]
                    ];
                }
            }
        }
    }
    
    return $anomalies;
}

/**
 * Détecter les créneaux horaires inhabituels
 */
function emploi_temps_detecter_creneaux_inhabituels(array $cours_par_jour): array
{
    $anomalies = [];
    
    foreach ($cours_par_jour as $jour => $cours_du_jour) {
        foreach ($cours_du_jour as $cours) {
            $heure_debut = strtotime($cours['heure_debut']);
            $heure_fin = strtotime($cours['heure_fin']);
            $duree = ($heure_fin - $heure_debut) / 3600; // Durée en heures
            
            // Cours très courts (< 30 minutes)
            if ($duree < 0.5) {
                $anomalies[] = [
                    'type' => 'cours_trop_court',
                    'jour' => $jour,
                    'message' => "Cours trop court: {$cours['nom_matiere']} ({$duree}h)",
                    'cours' => $cours,
                    'duree_heures' => $duree
                ];
            }
            
            // Cours très longs (> 4 heures)
            if ($duree > 4) {
                $anomalies[] = [
                    'type' => 'cours_trop_long',
                    'jour' => $jour,
                    'message' => "Cours trop long: {$cours['nom_matiere']} ({$duree}h)",
                    'cours' => $cours,
                    'duree_heures' => $duree
                ];
            }
            
            // Cours en dehors des heures normales
            $heure = (int)date('H', $heure_debut);
            if ($heure < 7 || $heure > 20) {
                $anomalies[] = [
                    'type' => 'heure_inhabituelle',
                    'jour' => $jour,
                    'message' => "Cours en dehors des heures normales: {$cours['nom_matiere']} à {$heure}h",
                    'cours' => $cours,
                    'heure_debut' => date('H:i', $heure_debut)
                ];
            }
        }
    }
    
    return $anomalies;
}

/**
 * Détecter les salles non spécifiées
 */
function emploi_temps_detecter_salles_manquantes(array $cours_par_jour): array
{
    $anomalies = [];
    
    foreach ($cours_par_jour as $jour => $cours_du_jour) {
        foreach ($cours_du_jour as $cours) {
            if (empty($cours['salle'])) {
                $anomalies[] = [
                    'type' => 'salle_manquante',
                    'jour' => $jour,
                    'message' => "Salle non spécifiée pour {$cours['nom_matiere']}",
                    'cours' => $cours
                ];
            }
        }
    }
    
    return $anomalies;
}

/**
 * Vérifier la charge horaire des professeurs
 */
function emploi_temps_verifier_charge_professeurs(array $cours_par_jour): array
{
    $anomalies = [];
    $charge_professeurs = [];
    
    // Calculer la charge horaire par professeur
    foreach ($cours_par_jour as $jour => $cours_du_jour) {
        foreach ($cours_du_jour as $cours) {
            $professeur_id = $cours['professeur_id'];
            $duree = $cours['duree_minutes'] / 60; // Durée en heures
            
            if (!isset($charge_professeurs[$professeur_id])) {
                $charge_professeurs[$professeur_id] = [
                    'nom' => $cours['professeur_prenom'] . ' ' . $cours['professeur_nom'],
                    'total_heures' => 0,
                    'cours' => []
                ];
            }
            
            $charge_professeurs[$professeur_id]['total_heures'] += $duree;
            $charge_professeurs[$professeur_id]['cours'][] = [
                'jour' => $jour,
                'matiere' => $cours['nom_matiere'],
                'duree' => $duree
            ];
        }
    }
    
    // Vérifier les charges excessives
    foreach ($charge_professeurs as $professeur_id => $charge) {
        if ($charge['total_heures'] > 30) { // Plus de 30 heures par semaine
            $anomalies[] = [
                'type' => 'charge_excessive',
                'professeur' => $charge['nom'],
                'message' => "Charge horaire excessive: {$charge['total_heures']}h/semaine",
                'total_heures' => $charge['total_heures'],
                'cours' => $charge['cours']
            ];
        }
    }
    
    return $anomalies;
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Obtenir la liste des jours de la semaine
 */
function emploi_temps_get_jours_semaine(): array
{
    return [
        JOUR_LUNDI => 'Lundi',
        JOUR_MARDI => 'Mardi',
        JOUR_MERCREDI => 'Mercredi',
        JOUR_JEUDI => 'Jeudi',
        JOUR_VENDREDI => 'Vendredi',
        JOUR_SAMEDI => 'Samedi'
    ];
}

/**
 * Obtenir la liste des types de cours
 */
function emploi_temps_get_types_cours(): array
{
    return [
        TYPE_COURS => 'Cours',
        TYPE_TD => 'Travaux Dirigés (TD)',
        TYPE_TP => 'Travaux Pratiques (TP)',
        TYPE_EXAMEN => 'Examen'
    ];
}

/**
 * Formater une heure pour l'affichage
 */
function emploi_temps_format_heure(string $heure): string
{
    return substr($heure, 0, 5); // HH:MM
}

/**
 * Calculer la durée entre deux heures
 */
function emploi_temps_calculer_duree(string $heure_debut, string $heure_fin): string
{
    $debut = strtotime($heure_debut);
    $fin = strtotime($heure_fin);
    $difference = $fin - $debut;
    
    $heures = floor($difference / 3600);
    $minutes = floor(($difference % 3600) / 60);
    
    if ($heures > 0) {
        return "{$heures}h" . ($minutes > 0 ? " {$minutes}min" : "");
    } else {
        return "{$minutes}min";
    }
}

/**
 * Vérifier si un cours est actuellement en cours
 */
function emploi_temps_cours_en_cours(int $edt_id): bool
{
    $cours = emploi_temps_get_by_id($edt_id);
    
    if (!$cours) {
        return false;
    }
    
    $jour_actuel = date('l');
    $jour_cours = $cours['jour_semaine'];
    
    // Convertir le jour en anglais pour la comparaison
    $jours_fr_to_en = [
        'Lundi' => 'Monday',
        'Mardi' => 'Tuesday',
        'Mercredi' => 'Wednesday',
        'Jeudi' => 'Thursday',
        'Vendredi' => 'Friday',
        'Samedi' => 'Saturday'
    ];
    
    if ($jours_fr_to_en[$jour_cours] !== $jour_actuel) {
        return false;
    }
    
    $heure_actuelle = date('H:i:s');
    $heure_debut = $cours['heure_debut'];
    $heure_fin = $cours['heure_fin'];
    
    return ($heure_actuelle >= $heure_debut && $heure_actuelle <= $heure_fin);
}

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Journaliser le chargement du module
log_action('Module emploi du temps chargé', ['version' => '1.0.0']);