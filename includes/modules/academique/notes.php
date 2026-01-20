<?php
/**
 * Gestion des notes et évaluations
 * Système complet de saisie, calcul et statistiques
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/matieres.php';
require_once __DIR__ . '/../eleves/eleves.php';

// =============================================
// CONSTANTES POUR LES NOTES
// =============================================

// Types d'évaluation
define('TYPE_DEVOIR', 'Devoir');
define('TYPE_EXAMEN', 'Examen');
define('TYPE_PROJET', 'Projet');
define('TYPE_PARTICIPATION', 'Participation');
define('TYPE_COMPOSITION', 'Composition');

// Périodes académiques
define('PERIODE_TRIMESTRE1', 'trimestre1');
define('PERIODE_TRIMESTRE2', 'trimestre2');
define('PERIODE_TRIMESTRE3', 'trimestre3');

// Statuts de validation
define('NOTE_VALIDEE', 'validee');
define('NOTE_EN_ATTENTE', 'en_attente');
define('NOTE_RECTIFIEE', 'rectifiee');

// Seuils de réussite
define('SEUIL_REUSSITE', 10.0);
define('SEUIL_MENTION_AB', 12.0); // Assez Bien
define('SEUIL_MENTION_B', 14.0);  // Bien
define('SEUIL_MENTION_TB', 16.0); // Très Bien

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'une note
 */
function note_valider_donnees(array $donnees): array
{
    $erreurs = [];
    
    // Champs obligatoires
    $champs_requis = ['eleve_id', 'matiere_id', 'type_evaluation', 'note', 'date_evaluation', 'annee_id', 'periode'];
    
    foreach ($champs_requis as $champ) {
        if (empty($donnees[$champ])) {
            $erreurs[$champ] = "Ce champ est requis";
        }
    }
    
    // Vérifier que l'élève existe
    if (!empty($donnees['eleve_id'])) {
        $eleve = eleve_get_by_id((int)$donnees['eleve_id']);
        if (!$eleve) {
            $erreurs['eleve_id'] = "Élève non trouvé";
        } elseif ($eleve['statut_etudiant'] !== ELEVE_ACTIF) {
            $erreurs['eleve_id'] = "Élève non actif";
        }
    }
    
    // Vérifier que la matière existe
    if (!empty($donnees['matiere_id'])) {
        $matiere = matiere_get_by_id((int)$donnees['matiere_id']);
        if (!$matiere) {
            $erreurs['matiere_id'] = "Matière non trouvée";
        }
    }
    
    // Vérifier que l'année scolaire existe
    if (!empty($donnees['annee_id'])) {
        $annee = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE annee_id = :annee_id",
            ['annee_id' => $donnees['annee_id']]
        );
        if (!$annee) {
            $erreurs['annee_id'] = "Année scolaire non trouvée";
        }
    }
    
    // Vérifier le type d'évaluation
    if (!empty($donnees['type_evaluation'])) {
        $types_valides = [TYPE_DEVOIR, TYPE_EXAMEN, TYPE_PROJET, TYPE_PARTICIPATION, TYPE_COMPOSITION];
        if (!in_array($donnees['type_evaluation'], $types_valides)) {
            $erreurs['type_evaluation'] = "Type d'évaluation invalide";
        }
    }
    
    // Vérifier la période
    if (!empty($donnees['periode'])) {
        $periodes_valides = [PERIODE_TRIMESTRE1, PERIODE_TRIMESTRE2, PERIODE_TRIMESTRE3];
        if (!in_array($donnees['periode'], $periodes_valides)) {
            $erreurs['periode'] = "Période invalide";
        }
    }
    
    // Vérifier la note
    if (isset($donnees['note'])) {
        $note = floatval($donnees['note']);
        if ($note < 0 || $note > 20) {
            $erreurs['note'] = "La note doit être entre 0 et 20";
        } elseif (!is_numeric($donnees['note'])) {
            $erreurs['note'] = "La note doit être un nombre";
        }
    }
    
    // Vérifier le coefficient
    if (isset($donnees['coefficient'])) {
        $coeff = floatval($donnees['coefficient']);
        if ($coeff < 0.1 || $coeff > 5.0) {
            $erreurs['coefficient'] = "Le coefficient doit être entre 0.1 et 5.0";
        }
    }
    
    // Vérifier la date d'évaluation
    if (!empty($donnees['date_evaluation'])) {
        $date = DateTime::createFromFormat('Y-m-d', $donnees['date_evaluation']);
        if (!$date || $date->format('Y-m-d') !== $donnees['date_evaluation']) {
            $erreurs['date_evaluation'] = "Format de date invalide (YYYY-MM-DD)";
        } else {
            // Ne pas permettre les dates futures
            if ($date > new DateTime()) {
                $erreurs['date_evaluation'] = "La date d'évaluation ne peut pas être dans le futur";
            }
            
            // Vérifier que la date est dans l'année scolaire
            if (!empty($donnees['annee_id'])) {
                $annee = db_query_single(
                    "SELECT date_debut, date_fin FROM annees_scolaire WHERE annee_id = :annee_id",
                    ['annee_id' => $donnees['annee_id']]
                );
                if ($annee) {
                    if ($date < new DateTime($annee['date_debut']) || $date > new DateTime($annee['date_fin'])) {
                        $erreurs['date_evaluation'] = "La date n'est pas dans l'année scolaire";
                    }
                }
            }
        }
    }
    
    // Vérifier le professeur (si fourni)
    if (!empty($donnees['professeur_id'])) {
        $professeur = db_query_single(
            "SELECT professeur_id FROM professeurs WHERE professeur_id = :professeur_id",
            ['professeur_id' => $donnees['professeur_id']]
        );
        if (!$professeur) {
            $erreurs['professeur_id'] = "Professeur non trouvé";
        }
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - NOTES
// =============================================

/**
 * Créer une nouvelle note
 */
function note_creer(array $donnees): array
{
    // Vérifier les permissions (professeur, admin ou proviseur)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        // Vérifier si l'utilisateur est un professeur de cette matière
        $est_professeur = note_verifier_professeur_matiere(
            $_SESSION['user_id'] ?? 0,
            $donnees['matiere_id'] ?? 0,
            $donnees['eleve_id'] ?? 0
        );
        
        if (!$est_professeur) {
            return ['success' => false, 'error' => 'Permission refusée'];
        }
    }
    
    // Valider les données
    $erreurs = note_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité (même élève, matière, type, date et période)
    $note_existante = db_query_single(
        "SELECT note_id FROM notes 
         WHERE eleve_id = :eleve_id 
         AND matiere_id = :matiere_id 
         AND type_evaluation = :type_evaluation 
         AND date_evaluation = :date_evaluation 
         AND periode = :periode 
         AND annee_id = :annee_id",
        [
            'eleve_id' => $donnees['eleve_id'],
            'matiere_id' => $donnees['matiere_id'],
            'type_evaluation' => $donnees['type_evaluation'],
            'date_evaluation' => $donnees['date_evaluation'],
            'periode' => $donnees['periode'],
            'annee_id' => $donnees['annee_id']
        ]
    );
    
    if ($note_existante) {
        return [
            'success' => false,
            'error' => "Une note existe déjà pour cette évaluation"
        ];
    }
    
    // Vérifier que l'élève est bien inscrit dans une classe pour cette année
    $admission = admission_get_actuelle((int)$donnees['eleve_id']);
    if (!$admission) {
        return [
            'success' => false,
            'error' => "L'élève n'est pas admis dans une classe pour cette année"
        ];
    }
    
    // Vérifier que la matière est enseignée dans la classe de l'élève
    $matiere_classe = db_query_single(
        "SELECT 1 FROM emploi_du_temps e
         WHERE e.class_id = :class_id
         AND e.matiere_id = :matiere_id
         AND e.statut = 'actif'
         LIMIT 1",
        [
            'class_id' => $admission['class_id'],
            'matiere_id' => $donnees['matiere_id']
        ]
    );
    
    if (!$matiere_classe) {
        return [
            'success' => false,
            'error' => "Cette matière n'est pas enseignée dans la classe de l'élève"
        ];
    }
    
    try {
        // Préparer les données pour l'insertion
        $champs = [
            'eleve_id' => (int)$donnees['eleve_id'],
            'matiere_id' => (int)$donnees['matiere_id'],
            'type_evaluation' => $donnees['type_evaluation'],
            'note' => floatval($donnees['note']),
            'coefficient' => isset($donnees['coefficient']) ? floatval($donnees['coefficient']) : 1.0,
            'date_evaluation' => $donnees['date_evaluation'],
            'annee_id' => (int)$donnees['annee_id'],
            'periode' => $donnees['periode'],
            'professeur_id' => !empty($donnees['professeur_id']) ? (int)$donnees['professeur_id'] : null,
            'remarques' => trim($donnees['remarques'] ?? ''),
            'est_rectifiee' => 0,
            'modifie_par' => $_SESSION['user_id'] ?? null
        ];
        
        // Si pas de professeur spécifié, utiliser l'utilisateur courant si c'est un professeur
        if (!$champs['professeur_id']) {
            $professeur = db_query_single(
                "SELECT professeur_id FROM professeurs WHERE user_id = :user_id",
                ['user_id' => $_SESSION['user_id'] ?? 0]
            );
            if ($professeur) {
                $champs['professeur_id'] = $professeur['professeur_id'];
            }
        }
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO notes ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $note_id = db_last_insert_id();
        
        // Journaliser l'action
        $eleve = eleve_get_by_id($champs['eleve_id']);
        $matiere = matiere_get_by_id($champs['matiere_id']);
        
        log_action('Note créée', [
            'note_id' => $note_id,
            'eleve_id' => $champs['eleve_id'],
            'eleve_nom' => $eleve ? $eleve['nom_complet'] : 'Inconnu',
            'matiere_id' => $champs['matiere_id'],
            'matiere_nom' => $matiere ? $matiere['nom_matiere'] : 'Inconnue',
            'type_evaluation' => $champs['type_evaluation'],
            'note' => $champs['note'],
            'periode' => $champs['periode'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'notes');
        
        // Recalculer la moyenne de l'élève pour cette matière et période
        note_recalculer_moyenne($champs['eleve_id'], $champs['matiere_id'], $champs['periode'], $champs['annee_id']);
        
        return [
            'success' => true,
            'note_id' => $note_id,
            'message' => 'Note créée avec succès'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur note_creer: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour une note existante
 */
function note_modifier(int $note_id, array $donnees): array
{
    // Vérifier que la note existe
    $note_existante = note_get_by_id($note_id);
    if (!$note_existante) {
        return ['success' => false, 'error' => 'Note non trouvée'];
    }
    
    // Vérifier les permissions
    $peut_modifier = note_verifier_permission_modification($note_existante);
    if (!$peut_modifier) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = note_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Ne pas permettre la modification des champs clés (sauf pour admin)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        $champs_proteges = ['eleve_id', 'matiere_id', 'type_evaluation', 'date_evaluation', 'periode', 'annee_id'];
        foreach ($champs_proteges as $champ) {
            if (isset($donnees[$champ]) && $donnees[$champ] != $note_existante[$champ]) {
                return [
                    'success' => false,
                    'error' => "Vous ne pouvez pas modifier le champ '$champ'"
                ];
            }
        }
    }
    
    // Si c'est une rectification, créer une nouvelle note rectifiée
    if (isset($donnees['note']) && floatval($donnees['note']) != floatval($note_existante['note'])) {
        return note_rectifier($note_id, $donnees);
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['note_id' => $note_id];
        
        // Champs modifiables
        $champs_modifiables = ['coefficient', 'remarques', 'professeur_id'];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                
                if ($champ === 'coefficient') {
                    $params[$champ] = floatval($donnees[$champ]);
                } elseif ($champ === 'professeur_id' && empty($donnees[$champ])) {
                    $params[$champ] = null;
                } else {
                    $params[$champ] = trim($donnees[$champ]);
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Ajouter le modificateur
        $updates[] = "modifie_par = :modifie_par";
        $params['modifie_par'] = $_SESSION['user_id'] ?? null;
        
        // Construction de la requête
        $sql = "UPDATE notes SET " . implode(', ', $updates) . " WHERE note_id = :note_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Note modifiée', [
                'note_id' => $note_id,
                'eleve_id' => $note_existante['eleve_id'],
                'matiere_id' => $note_existante['matiere_id'],
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'notes');
            
            return [
                'success' => true,
                'message' => 'Note mise à jour avec succès',
                'note_id' => $note_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur note_modifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Rectifier une note (créer une nouvelle version)
 */
function note_rectifier(int $note_id, array $donnees): array
{
    // Vérifier que la note existe
    $note_existante = note_get_by_id($note_id);
    if (!$note_existante) {
        return ['success' => false, 'error' => 'Note non trouvée'];
    }
    
    // Vérifier les permissions (admin ou proviseur uniquement)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Seuls les administrateurs peuvent rectifier des notes'];
    }
    
    // Vérifier que la nouvelle note est différente
    $nouvelle_note = floatval($donnees['note'] ?? $note_existante['note']);
    if ($nouvelle_note == floatval($note_existante['note'])) {
        return ['success' => false, 'error' => 'La nouvelle note est identique à l\'ancienne'];
    }
    
    // Vérifier la validité de la nouvelle note
    if ($nouvelle_note < 0 || $nouvelle_note > 20) {
        return ['success' => false, 'error' => 'La note doit être entre 0 et 20'];
    }
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        // Marquer l'ancienne note comme rectifiée
        $sql_ancienne = "UPDATE notes 
                        SET est_rectifiee = 1, 
                            note_rectifiee = :nouvelle_note,
                            modifie_par = :modifie_par,
                            date_modif = NOW()
                        WHERE note_id = :note_id";
        
        $success_ancienne = db_execute($sql_ancienne, [
            'nouvelle_note' => $nouvelle_note,
            'modifie_par' => $_SESSION['user_id'] ?? null,
            'note_id' => $note_id
        ]);
        
        if (!$success_ancienne) {
            throw new Exception("Échec de la mise à jour de l'ancienne note");
        }
        
        // Créer une nouvelle note avec la valeur rectifiée
        $nouvelle_donnees = [
            'eleve_id' => $note_existante['eleve_id'],
            'matiere_id' => $note_existante['matiere_id'],
            'type_evaluation' => $note_existante['type_evaluation'],
            'note' => $nouvelle_note,
            'coefficient' => $donnees['coefficient'] ?? $note_existante['coefficient'],
            'date_evaluation' => $note_existante['date_evaluation'],
            'annee_id' => $note_existante['annee_id'],
            'periode' => $note_existante['periode'],
            'professeur_id' => $note_existante['professeur_id'],
            'remarques' => trim(($donnees['remarques'] ?? '') . " [Rectification de la note #{$note_id}: {$note_existante['note']} -> {$nouvelle_note}]"),
            'est_rectifiee' => 0,
            'modifie_par' => $_SESSION['user_id'] ?? null
        ];
        
        $resultat_nouvelle = note_creer($nouvelle_donnees);
        
        if (!$resultat_nouvelle['success']) {
            throw new Exception("Échec de la création de la nouvelle note: " . 
                ($resultat_nouvelle['error'] ?? ''));
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        $eleve = eleve_get_by_id($note_existante['eleve_id']);
        $matiere = matiere_get_by_id($note_existante['matiere_id']);
        
        log_action('Note rectifiée', [
            'ancienne_note_id' => $note_id,
            'nouvelle_note_id' => $resultat_nouvelle['note_id'],
            'eleve_id' => $note_existante['eleve_id'],
            'eleve_nom' => $eleve ? $eleve['nom_complet'] : 'Inconnu',
            'matiere_id' => $note_existante['matiere_id'],
            'matiere_nom' => $matiere ? $matiere['nom_matiere'] : 'Inconnue',
            'ancienne_note' => $note_existante['note'],
            'nouvelle_note' => $nouvelle_note,
            'difference' => $nouvelle_note - floatval($note_existante['note']),
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'notes');
        
        return [
            'success' => true,
            'message' => 'Note rectifiée avec succès',
            'ancienne_note_id' => $note_id,
            'nouvelle_note_id' => $resultat_nouvelle['note_id'],
            'ancienne_note' => $note_existante['note'],
            'nouvelle_note' => $nouvelle_note
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur note_rectifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la rectification'];
    }
}

/**
 * Supprimer une note
 */
function note_supprimer(int $note_id): array
{
    // Vérifier que la note existe
    $note = note_get_by_id($note_id);
    if (!$note) {
        return ['success' => false, 'error' => 'Note non trouvée'];
    }
    
    // Vérifier les permissions (admin ou proviseur uniquement)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Empêcher la suppression de notes rectifiées
    if ($note['est_rectifiee']) {
        return [
            'success' => false,
            'error' => 'Impossible de supprimer une note rectifiée'
        ];
    }
    
    try {
        // Sauvegarder les informations avant suppression
        $eleve_id = $note['eleve_id'];
        $matiere_id = $note['matiere_id'];
        $periode = $note['periode'];
        $annee_id = $note['annee_id'];
        
        // Supprimer la note
        $sql = "DELETE FROM notes WHERE note_id = :note_id";
        $success = db_execute($sql, ['note_id' => $note_id]);
        
        if ($success) {
            // Journaliser l'action
            $eleve = eleve_get_by_id($eleve_id);
            $matiere = matiere_get_by_id($matiere_id);
            
            log_action('Note supprimée', [
                'note_id' => $note_id,
                'eleve_id' => $eleve_id,
                'eleve_nom' => $eleve ? $eleve['nom_complet'] : 'Inconnu',
                'matiere_id' => $matiere_id,
                'matiere_nom' => $matiere ? $matiere['nom_matiere'] : 'Inconnue',
                'type_evaluation' => $note['type_evaluation'],
                'note' => $note['note'],
                'periode' => $periode,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'notes');
            
            // Recalculer la moyenne
            note_recalculer_moyenne($eleve_id, $matiere_id, $periode, $annee_id);
            
            return [
                'success' => true,
                'message' => 'Note supprimée avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        
    } catch (Exception $e) {
        error_log("Erreur note_supprimer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE VÉRIFICATION DE PERMISSIONS
// =============================================

/**
 * Vérifier si un utilisateur est professeur d'une matière pour un élève
 */
function note_verifier_professeur_matiere(int $user_id, int $matiere_id, int $eleve_id): bool
{
    // Récupérer le professeur associé à l'utilisateur
    $professeur = db_query_single(
        "SELECT professeur_id FROM professeurs WHERE user_id = :user_id",
        ['user_id' => $user_id]
    );
    
    if (!$professeur) {
        return false;
    }
    
    // Récupérer la classe de l'élève
    $admission = admission_get_actuelle($eleve_id);
    if (!$admission) {
        return false;
    }
    
    // Vérifier si ce professeur enseigne cette matière dans cette classe
    $resultat = db_query_single(
        "SELECT 1 FROM emploi_du_temps e
         WHERE e.professeur_id = :professeur_id
         AND e.matiere_id = :matiere_id
         AND e.class_id = :class_id
         AND e.statut = 'actif'
         LIMIT 1",
        [
            'professeur_id' => $professeur['professeur_id'],
            'matiere_id' => $matiere_id,
            'class_id' => $admission['class_id']
        ]
    );
    
    return $resultat !== null;
}

/**
 * Vérifier les permissions de modification d'une note
 */
function note_verifier_permission_modification(array $note): bool
{
    // Admin et proviseur peuvent tout modifier
    if (has_role(ROLE_ADMIN) || has_role(ROLE_PROVISEUR)) {
        return true;
    }
    
    // Vérifier si l'utilisateur est le professeur qui a créé la note
    $professeur_utilisateur = db_query_single(
        "SELECT professeur_id FROM professeurs WHERE user_id = :user_id",
        ['user_id' => $_SESSION['user_id'] ?? 0]
    );
    
    if ($professeur_utilisateur && $note['professeur_id'] == $professeur_utilisateur['professeur_id']) {
        // Vérifier que la note n'est pas trop ancienne (max 7 jours)
        $date_note = new DateTime($note['date_creation']);
        $date_actuelle = new DateTime();
        $difference = $date_actuelle->diff($date_note)->days;
        
        if ($difference <= 7) {
            return true;
        }
    }
    
    return false;
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir une note par son ID
 */
function note_get_by_id(int $note_id): ?array
{
    $sql = "SELECT n.*,
                   e.matricule, e.nom as eleve_nom, e.prenom as eleve_prenom, e.post_nom as eleve_post_nom,
                   m.code_matiere, m.nom_matiere, m.coefficient as matiere_coefficient,
                   p.nom as professeur_nom, p.prenom as professeur_prenom,
                   u.nom as modificateur_nom, u.prenom as modificateur_prenom,
                   an.annee_libelle
            FROM notes n
            JOIN eleves e ON n.eleve_id = e.eleve_id
            JOIN matieres m ON n.matiere_id = m.matiere_id
            LEFT JOIN professeurs p ON n.professeur_id = p.professeur_id
            LEFT JOIN user_admins u ON n.modifie_par = u.user_id
            JOIN annees_scolaire an ON n.annee_id = an.annee_id
            WHERE n.note_id = :note_id";
    
    $note = db_query_single($sql, ['note_id' => $note_id]);
    
    if ($note) {
        // Ajouter des informations calculées
        $note['eleve_nom_complet'] = $note['eleve_prenom'] . ' ' . $note['eleve_nom'] . ' ' . $note['eleve_post_nom'];
        $note['professeur_nom_complet'] = $note['professeur_nom'] ? 
            $note['professeur_prenom'] . ' ' . $note['professeur_nom'] : 'Non spécifié';
        $note['modificateur_nom_complet'] = $note['modificateur_nom'] ? 
            $note['modificateur_prenom'] . ' ' . $note['modificateur_nom'] : null;
        
        // Calculer la note pondérée
        $note['note_ponderee'] = $note['note'] * $note['coefficient'];
        
        // Déterminer le statut (réussi/échoué)
        $note['reussi'] = $note['note'] >= SEUIL_REUSSITE;
        
        // Ajouter l'historique des rectifications
        if ($note['est_rectifiee']) {
            $note['historique_rectifications'] = note_get_historique_rectifications($note_id);
        }
    }
    
    return $note;
}

/**
 * Obtenir l'historique des rectifications d'une note
 */
function note_get_historique_rectifications(int $note_id): array
{
    $sql = "SELECT n.*,
                   e.prenom as eleve_prenom, e.nom as eleve_nom,
                   m.nom_matiere,
                   u.nom as modificateur_nom, u.prenom as modificateur_prenom
            FROM notes n
            JOIN eleves e ON n.eleve_id = e.eleve_id
            JOIN matieres m ON n.matiere_id = m.matiere_id
            LEFT JOIN user_admins u ON n.modifie_par = u.user_id
            WHERE n.est_rectifiee = 1
            AND n.note_rectifiee IS NOT NULL
            AND (
                n.note_id = :note_id
                OR EXISTS (
                    SELECT 1 FROM notes n2 
                    WHERE n2.est_rectifiee = 1 
                    AND n2.note_id = :note_id2
                    AND n2.eleve_id = n.eleve_id
                    AND n2.matiere_id = n.matiere_id
                    AND n2.type_evaluation = n.type_evaluation
                    AND n2.date_evaluation = n.date_evaluation
                    AND n2.periode = n.periode
                )
            )
            ORDER BY n.date_creation DESC";
    
    $historique = db_query($sql, ['note_id' => $note_id, 'note_id2' => $note_id]);
    
    // Ajouter des informations supplémentaires
    foreach ($historique as &$item) {
        $item['eleve_nom_complet'] = $item['eleve_prenom'] . ' ' . $item['eleve_nom'];
        $item['modificateur_nom_complet'] = $item['modificateur_nom'] ? 
            $item['modificateur_prenom'] . ' ' . $item['modificateur_nom'] : 'Système';
    }
    
    return $historique;
}

/**
 * Rechercher des notes avec filtres
 */
function note_rechercher(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   n.*,
                   e.matricule, e.nom as eleve_nom, e.prenom as eleve_prenom, e.post_nom,
                   m.code_matiere, m.nom_matiere,
                   p.nom as professeur_nom, p.prenom as professeur_prenom,
                   an.annee_libelle,
                   c.libelle as classe_libelle
            FROM notes n
            JOIN eleves e ON n.eleve_id = e.eleve_id
            JOIN matieres m ON n.matiere_id = m.matiere_id
            LEFT JOIN professeurs p ON n.professeur_id = p.professeur_id
            JOIN annees_scolaire an ON n.annee_id = an.annee_id
            LEFT JOIN admissions a ON n.eleve_id = a.eleve_id AND a.statut_admission = 'approuve' AND n.annee_id = a.annee_id
            LEFT JOIN classes c ON a.class_id = c.class_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(e.nom LIKE :{$key} OR e.prenom LIKE :{$key} OR e.matricule LIKE :{$key} OR m.nom_matiere LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par élève
    if (!empty($filtres['eleve_id'])) {
        $sql .= " AND n.eleve_id = :eleve_id";
        $params['eleve_id'] = $filtres['eleve_id'];
    }
    
    // Filtre par matière
    if (!empty($filtres['matiere_id'])) {
        $sql .= " AND n.matiere_id = :matiere_id";
        $params['matiere_id'] = $filtres['matiere_id'];
    }
    
    // Filtre par classe
    if (!empty($filtres['class_id'])) {
        $sql .= " AND a.class_id = :class_id";
        $params['class_id'] = $filtres['class_id'];
    }
    
    // Filtre par année scolaire
    if (!empty($filtres['annee_id'])) {
        $sql .= " AND n.annee_id = :annee_id";
        $params['annee_id'] = $filtres['annee_id'];
    }
    
    // Filtre par période
    if (!empty($filtres['periode'])) {
        $sql .= " AND n.periode = :periode";
        $params['periode'] = $filtres['periode'];
    }
    
    // Filtre par type d'évaluation
    if (!empty($filtres['type_evaluation'])) {
        $sql .= " AND n.type_evaluation = :type_evaluation";
        $params['type_evaluation'] = $filtres['type_evaluation'];
    }
    
    // Filtre par professeur
    if (!empty($filtres['professeur_id'])) {
        $sql .= " AND n.professeur_id = :professeur_id";
        $params['professeur_id'] = $filtres['professeur_id'];
    }
    
    // Filtre par date
    if (!empty($filtres['date_debut'])) {
        $sql .= " AND n.date_evaluation >= :date_debut";
        $params['date_debut'] = $filtres['date_debut'];
    }
    
    if (!empty($filtres['date_fin'])) {
        $sql .= " AND n.date_evaluation <= :date_fin";
        $params['date_fin'] = $filtres['date_fin'];
    }
    
    // Filtre par note minimum
    if (isset($filtres['note_min'])) {
        $sql .= " AND n.note >= :note_min";
        $params['note_min'] = floatval($filtres['note_min']);
    }
    
    // Filtre par note maximum
    if (isset($filtres['note_max'])) {
        $sql .= " AND n.note <= :note_max";
        $params['note_max'] = floatval($filtres['note_max']);
    }
    
    // Filtre par statut (réussi/échoué)
    if (isset($filtres['reussi'])) {
        if ($filtres['reussi']) {
            $sql .= " AND n.note >= " . SEUIL_REUSSITE;
        } else {
            $sql .= " AND n.note < " . SEUIL_REUSSITE;
        }
    }
    
    // Exclure les notes rectifiées (sauf demande explicite)
    if (!isset($filtres['inclure_rectifiees']) || !$filtres['inclure_rectifiees']) {
        $sql .= " AND n.est_rectifiee = 0";
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'n.date_evaluation';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'DESC';
    $sql .= " ORDER BY {$order_by} {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $notes = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($notes as &$note) {
            $note['eleve_nom_complet'] = $note['eleve_prenom'] . ' ' . $note['eleve_nom'] . ' ' . $note['post_nom'];
            $note['professeur_nom_complet'] = $note['professeur_nom'] ? 
                $note['professeur_prenom'] . ' ' . $note['professeur_nom'] : 'Non spécifié';
            $note['note_ponderee'] = $note['note'] * $note['coefficient'];
            $note['reussi'] = $note['note'] >= SEUIL_REUSSITE;
            
            // Déterminer la mention (si c'est un examen)
            if ($note['type_evaluation'] === TYPE_EXAMEN || $note['type_evaluation'] === TYPE_COMPOSITION) {
                $note['mention'] = note_get_mention($note['note']);
            }
        }
        
        return [
            'success' => true,
            'notes' => $notes,
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
        error_log("Erreur note_rechercher: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

/**
 * Obtenir les notes d'un élève pour une matière et période
 */
function note_get_par_eleve_matiere_periode(int $eleve_id, int $matiere_id, string $periode, int $annee_id): array
{
    $sql = "SELECT n.*,
                   p.nom as professeur_nom, p.prenom as professeur_prenom
            FROM notes n
            LEFT JOIN professeurs p ON n.professeur_id = p.professeur_id
            WHERE n.eleve_id = :eleve_id
            AND n.matiere_id = :matiere_id
            AND n.periode = :periode
            AND n.annee_id = :annee_id
            AND n.est_rectifiee = 0
            ORDER BY n.type_evaluation, n.date_evaluation";
    
    $notes = db_query($sql, [
        'eleve_id' => $eleve_id,
        'matiere_id' => $matiere_id,
        'periode' => $periode,
        'annee_id' => $annee_id
    ]);
    
    // Ajouter des informations supplémentaires
    foreach ($notes as &$note) {
        $note['professeur_nom_complet'] = $note['professeur_nom'] ? 
            $note['professeur_prenom'] . ' ' . $note['professeur_nom'] : 'Non spécifié';
        $note['note_ponderee'] = $note['note'] * $note['coefficient'];
    }
    
    return $notes;
}

/**
 * Obtenir toutes les notes d'un élève pour une période
 */
function note_get_par_eleve_periode(int $eleve_id, string $periode, int $annee_id): array
{
    $sql = "SELECT n.*,
                   m.code_matiere, m.nom_matiere, m.coefficient as matiere_coefficient,
                   p.nom as professeur_nom, p.prenom as professeur_prenom
            FROM notes n
            JOIN matieres m ON n.matiere_id = m.matiere_id
            LEFT JOIN professeurs p ON n.professeur_id = p.professeur_id
            WHERE n.eleve_id = :eleve_id
            AND n.periode = :periode
            AND n.annee_id = :annee_id
            AND n.est_rectifiee = 0
            ORDER BY m.nom_matiere, n.type_evaluation";
    
    $notes = db_query($sql, [
        'eleve_id' => $eleve_id,
        'periode' => $periode,
        'annee_id' => $annee_id
    ]);
    
    // Grouper par matière
    $notes_par_matiere = [];
    foreach ($notes as $note) {
        $matiere_id = $note['matiere_id'];
        if (!isset($notes_par_matiere[$matiere_id])) {
            $notes_par_matiere[$matiere_id] = [
                'matiere' => [
                    'code_matiere' => $note['code_matiere'],
                    'nom_matiere' => $note['nom_matiere'],
                    'coefficient' => $note['matiere_coefficient']
                ],
                'notes' => []
            ];
        }
        $notes_par_matiere[$matiere_id]['notes'][] = $note;
    }
    
    // Calculer les moyennes pour chaque matière
    foreach ($notes_par_matiere as $matiere_id => &$donnees) {
        $donnees['moyenne'] = note_calculer_moyenne_matiere($eleve_id, $matiere_id, $periode, $annee_id);
    }
    
    return array_values($notes_par_matiere);
}

// =============================================
// FONCTIONS DE CALCUL ET MOYENNES
// =============================================

/**
 * Calculer la moyenne d'un élève pour une matière et période
 */
function note_calculer_moyenne_matiere(int $eleve_id, int $matiere_id, string $periode, int $annee_id): array
{
    // Récupérer toutes les notes de l'élève pour cette matière et période
    $notes = note_get_par_eleve_matiere_periode($eleve_id, $matiere_id, $periode, $annee_id);
    
    if (empty($notes)) {
        return [
            'moyenne' => null,
            'total_points' => 0,
            'total_coefficients' => 0,
            'nombre_notes' => 0,
            'calcul_possible' => false,
            'message' => 'Aucune note disponible'
        ];
    }
    
    // Calculer la moyenne pondérée
    $total_points = 0;
    $total_coefficients = 0;
    $notes_calculees = [];
    
    foreach ($notes as $note) {
        $points = $note['note'] * $note['coefficient'];
        $total_points += $points;
        $total_coefficients += $note['coefficient'];
        
        $notes_calculees[] = [
            'type' => $note['type_evaluation'],
            'note' => $note['note'],
            'coefficient' => $note['coefficient'],
            'points' => $points
        ];
    }
    
    if ($total_coefficients > 0) {
        $moyenne = $total_points / $total_coefficients;
        $reussi = $moyenne >= SEUIL_REUSSITE;
        
        return [
            'moyenne' => round($moyenne, 2),
            'total_points' => round($total_points, 2),
            'total_coefficients' => $total_coefficients,
            'nombre_notes' => count($notes),
            'reussi' => $reussi,
            'notes_calculees' => $notes_calculees,
            'calcul_possible' => true,
            'mention' => note_get_mention($moyenne)
        ];
    }
    
    return [
        'moyenne' => null,
        'total_points' => 0,
        'total_coefficients' => 0,
        'nombre_notes' => count($notes),
        'calcul_possible' => false,
        'message' => 'Coefficients totaux nuls'
    ];
}

/**
 * Recalculer la moyenne d'un élève pour une matière et période
 */
function note_recalculer_moyenne(int $eleve_id, int $matiere_id, string $periode, int $annee_id): array
{
    $moyenne = note_calculer_moyenne_matiere($eleve_id, $matiere_id, $periode, $annee_id);
    
    // Journaliser le recalcul
    log_action('Moyenne recalculée', [
        'eleve_id' => $eleve_id,
        'matiere_id' => $matiere_id,
        'periode' => $periode,
        'annee_id' => $annee_id,
        'moyenne' => $moyenne['moyenne'] ?? null,
        'nombre_notes' => $moyenne['nombre_notes'] ?? 0,
        'par_utilisateur' => $_SESSION['user_id'] ?? 'système'
    ], 'notes');
    
    return $moyenne;
}

/**
 * Calculer la moyenne générale d'un élève pour une période
 */
function note_calculer_moyenne_generale(int $eleve_id, string $periode, int $annee_id): array
{
    // Récupérer toutes les matières de l'élève pour cette période
    $notes_par_matiere = note_get_par_eleve_periode($eleve_id, $periode, $annee_id);
    
    if (empty($notes_par_matiere)) {
        return [
            'moyenne_generale' => null,
            'total_matieres' => 0,
            'matieres_calculees' => 0,
            'calcul_possible' => false,
            'message' => 'Aucune matière avec notes'
        ];
    }
    
    // Calculer la moyenne générale pondérée par les coefficients des matières
    $total_points = 0;
    $total_coefficients = 0;
    $matieres_calculees = [];
    $matieres_reussies = 0;
    
    foreach ($notes_par_matiere as $donnees_matiere) {
        $moyenne_matiere = $donnees_matiere['moyenne'];
        
        if ($moyenne_matiere['calcul_possible'] && $moyenne_matiere['moyenne'] !== null) {
            $coefficient_matiere = $donnees_matiere['matiere']['coefficient'];
            $points = $moyenne_matiere['moyenne'] * $coefficient_matiere;
            
            $total_points += $points;
            $total_coefficients += $coefficient_matiere;
            
            if ($moyenne_matiere['reussi']) {
                $matieres_reussies++;
            }
            
            $matieres_calculees[] = [
                'matiere' => $donnees_matiere['matiere']['nom_matiere'],
                'coefficient_matiere' => $coefficient_matiere,
                'moyenne_matiere' => $moyenne_matiere['moyenne'],
                'points' => $points,
                'reussi' => $moyenne_matiere['reussi'],
                'nombre_notes' => $moyenne_matiere['nombre_notes']
            ];
        }
    }
    
    if ($total_coefficients > 0) {
        $moyenne_generale = $total_points / $total_coefficients;
        
        return [
            'moyenne_generale' => round($moyenne_generale, 2),
            'total_points' => round($total_points, 2),
            'total_coefficients' => $total_coefficients,
            'total_matieres' => count($notes_par_matiere),
            'matieres_calculees' => count($matieres_calculees),
            'matieres_reussies' => $matieres_reussies,
            'matieres_echouees' => count($matieres_calculees) - $matieres_reussies,
            'taux_reussite' => count($matieres_calculees) > 0 ? 
                round(($matieres_reussies / count($matieres_calculees)) * 100, 1) : 0,
            'matieres_calculees_details' => $matieres_calculees,
            'calcul_possible' => true,
            'mention_generale' => note_get_mention($moyenne_generale)
        ];
    }
    
    return [
        'moyenne_generale' => null,
        'total_matieres' => count($notes_par_matiere),
        'matieres_calculees' => 0,
        'calcul_possible' => false,
        'message' => 'Aucune matière avec moyenne calculable'
    ];
}

/**
 * Obtenir la mention correspondant à une note
 */
function note_get_mention(float $note): string
{
    if ($note >= SEUIL_MENTION_TB) {
        return 'Très Bien';
    } elseif ($note >= SEUIL_MENTION_B) {
        return 'Bien';
    } elseif ($note >= SEUIL_MENTION_AB) {
        return 'Assez Bien';
    } elseif ($note >= SEUIL_REUSSITE) {
        return 'Passable';
    } else {
        return 'Insuffisant';
    }
}

// =============================================
// FONCTIONS DE BULLETIN ET RAPPORTS
// =============================================

/**
 * Générer le bulletin d'un élève
 */
function note_generer_bulletin(int $eleve_id, string $periode, int $annee_id): array
{
    // Récupérer les informations de l'élève
    $eleve = eleve_get_by_id($eleve_id);
    if (!$eleve) {
        return ['success' => false, 'error' => 'Élève non trouvé'];
    }
    
    // Récupérer l'admission actuelle
    $admission = admission_get_actuelle($eleve_id);
    if (!$admission) {
        return ['success' => false, 'error' => 'L\'élève n\'est pas admis dans une classe'];
    }
    
    // Récupérer les informations de la classe
    $classe = classe_get_by_id($admission['class_id']);
    
    // Récupérer les notes par matière
    $notes_par_matiere = note_get_par_eleve_periode($eleve_id, $periode, $annee_id);
    
    // Calculer la moyenne générale
    $moyenne_generale = note_calculer_moyenne_generale($eleve_id, $periode, $annee_id);
    
    // Récupérer les absences pour cette période
    $absences = note_get_absences_eleve_periode($eleve_id, $periode, $annee_id);
    
    // Récupérer les appréciations
    $appreciations = note_get_appreciations($eleve_id, $periode, $annee_id);
    
    // Récupérer les informations de l'année scolaire
    $annee = db_query_single(
        "SELECT * FROM annees_scolaire WHERE annee_id = :annee_id",
        ['annee_id' => $annee_id]
    );
    
    // Calculer le rang de l'élève dans la classe
    $rang = note_calculer_rang_classe($eleve_id, $periode, $annee_id);
    
    return [
        'success' => true,
        'bulletin' => [
            'eleve' => [
                'id' => $eleve['eleve_id'],
                'matricule' => $eleve['matricule'],
                'nom_complet' => $eleve['nom_complet'],
                'date_naissance' => $eleve['date_naissance'],
                'age' => $eleve['age'] ?? null
            ],
            'classe' => [
                'id' => $classe['class_id'] ?? null,
                'libelle' => $classe['nom_complet'] ?? null,
                'niveau' => $classe['nom_niveau'] ?? null,
                'section' => $classe['nom_section'] ?? null,
                'tuteur' => $classe['tuteur_nom_complet'] ?? null
            ],
            'periode' => [
                'code' => $periode,
                'libelle' => ucfirst(str_replace('trimestre', 'Trimestre ', $periode))
            ],
            'annee_scolaire' => $annee['annee_libelle'] ?? null,
            'matieres' => $notes_par_matiere,
            'moyenne_generale' => $moyenne_generale,
            'absences' => $absences,
            'appreciations' => $appreciations,
            'rang_classe' => $rang,
            'date_edition' => date('Y-m-d H:i:s'),
            'date_impression' => date('d/m/Y à H:i'),
            'etablissement' => [
                'nom' => ECOLE_NOM,
                'adresse' => ECOLE_ADRESSE,
                'telephone' => ECOLE_TELEPHONE,
                'devise' => ECOLE_DEVISE
            ]
        ]
    ];
}

/**
 * Récupérer les absences d'un élève pour une période
 */
function note_get_absences_eleve_periode(int $eleve_id, string $periode, int $annee_id): array
{
    // Déterminer les dates de la période
    $dates_periode = note_get_dates_periode($periode, $annee_id);
    
    $sql = "SELECT a.*,
                   m.nom_matiere,
                   u.nom as enregistreur_nom, u.prenom as enregistreur_prenom
            FROM absences a
            LEFT JOIN matieres m ON a.matiere_id = m.matiere_id
            LEFT JOIN user_admins u ON a.enregistre_par = u.user_id
            WHERE a.eleve_id = :eleve_id
            AND a.date_absence >= :date_debut
            AND a.date_absence <= :date_fin
            ORDER BY a.date_absence DESC";
    
    $absences = db_query($sql, [
        'eleve_id' => $eleve_id,
        'date_debut' => $dates_periode['date_debut'],
        'date_fin' => $dates_periode['date_fin']
    ]);
    
    // Compter les totaux
    $total_absences = count($absences);
    $absences_justifiees = 0;
    $absences_non_justifiees = 0;
    
    foreach ($absences as $absence) {
        if ($absence['justifiee']) {
            $absences_justifiees++;
        } else {
            $absences_non_justifiees++;
        }
    }
    
    return [
        'absences' => $absences,
        'statistiques' => [
            'total' => $total_absences,
            'justifiees' => $absences_justifiees,
            'non_justifiees' => $absences_non_justifiees,
            'taux_justification' => $total_absences > 0 ? 
                round(($absences_justifiees / $total_absences) * 100, 1) : 0
        ]
    ];
}

/**
 * Récupérer les appréciations d'un élève
 */
function note_get_appreciations(int $eleve_id, string $periode, int $annee_id): array
{
    // Dans un système complet, on aurait une table d'appréciations
    // Pour l'instant, on retourne des appréciations factices basées sur les notes
    
    $appreciations = [];
    
    // Appréciation du professeur principal
    $appreciations['professeur_principal'] = [
        'auteur' => 'Professeur Principal',
        'date' => date('Y-m-d'),
        'contenu' => 'Appréciation générale basée sur les résultats et le comportement.',
        'type' => 'general'
    ];
    
    // Appréciation du proviseur
    $appreciations['proviseur'] = [
        'auteur' => 'Proviseur',
        'date' => date('Y-m-d'),
        'contenu' => 'Appréciation de la direction.',
        'type' => 'direction'
    ];
    
    return $appreciations;
}

/**
 * Calculer le rang d'un élève dans sa classe
 */
function note_calculer_rang_classe(int $eleve_id, string $periode, int $annee_id): ?array
{
    // Récupérer la classe de l'élève
    $admission = admission_get_actuelle($eleve_id);
    if (!$admission) {
        return null;
    }
    
    // Récupérer tous les élèves de la classe
    $eleves_classe = classe_get_eleves($admission['class_id']);
    
    if (count($eleves_classe) < 2) {
        return null; // Pas de rang s'il n'y a qu'un élève
    }
    
    $classement = [];
    
    foreach ($eleves_classe as $eleve) {
        $moyenne_generale = note_calculer_moyenne_generale($eleve['eleve_id'], $periode, $annee_id);
        
        if ($moyenne_generale['calcul_possible']) {
            $classement[] = [
                'eleve_id' => $eleve['eleve_id'],
                'nom_complet' => $eleve['nom_complet'],
                'moyenne' => $moyenne_generale['moyenne_generale'],
                'matieres_reussies' => $moyenne_generale['matieres_reussies']
            ];
        }
    }
    
    // Trier par moyenne décroissante
    usort($classement, function($a, $b) {
        if ($a['moyenne'] == $b['moyenne']) {
            return $b['matieres_reussies'] - $a['matieres_reussies'];
        }
        return $b['moyenne'] <=> $a['moyenne'];
    });
    
    // Trouver le rang de l'élève
    $rang = null;
    foreach ($classement as $index => $eleve_classement) {
        if ($eleve_classement['eleve_id'] == $eleve_id) {
            $rang = $index + 1;
            break;
        }
    }
    
    if ($rang === null) {
        return null;
    }
    
    return [
        'rang' => $rang,
        'total_eleves' => count($classement),
        'pourcentage' => round(($rang / count($classement)) * 100, 1),
        'classement_complet' => $classement
    ];
}

/**
 * Obtenir les dates d'une période académique
 */
function note_get_dates_periode(string $periode, int $annee_id): array
{
    // Récupérer les dates de l'année scolaire
    $annee = db_query_single(
        "SELECT date_debut, date_fin FROM annees_scolaire WHERE annee_id = :annee_id",
        ['annee_id' => $annee_id]
    );
    
    if (!$annee) {
        return [
            'date_debut' => date('Y-m-d'),
            'date_fin' => date('Y-m-d')
        ];
    }
    
    $date_debut_annee = new DateTime($annee['date_debut']);
    $date_fin_annee = new DateTime($annee['date_fin']);
    
    // Calculer les trimestres (approximatif)
    $interval = $date_debut_annee->diff($date_fin_annee);
    $jours_total = $interval->days;
    $jours_par_trimestre = floor($jours_total / 3);
    
    switch ($periode) {
        case PERIODE_TRIMESTRE1:
            $date_debut = $date_debut_annee;
            $date_fin = clone $date_debut_annee;
            $date_fin->add(new DateInterval("P{$jours_par_trimestre}D"));
            break;
            
        case PERIODE_TRIMESTRE2:
            $date_debut = clone $date_debut_annee;
            $date_debut->add(new DateInterval("P{$jours_par_trimestre}D"));
            $date_fin = clone $date_debut;
            $date_fin->add(new DateInterval("P{$jours_par_trimestre}D"));
            break;
            
        case PERIODE_TRIMESTRE3:
            $date_debut = clone $date_debut_annee;
            $date_debut->add(new DateInterval("P" . ($jours_par_trimestre * 2) . "D"));
            $date_fin = $date_fin_annee;
            break;
            
        default:
            $date_debut = $date_debut_annee;
            $date_fin = $date_fin_annee;
    }
    
    return [
        'date_debut' => $date_debut->format('Y-m-d'),
        'date_fin' => $date_fin->format('Y-m-d')
    ];
}

// =============================================
// FONCTIONS DE SAISIE EN MASSE
// =============================================

/**
 * Saisir des notes en masse pour une classe
 */
function note_saisir_masse(int $class_id, int $matiere_id, string $type_evaluation, array $donnees): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        // Vérifier si l'utilisateur est professeur de cette matière dans cette classe
        $professeur = db_query_single(
            "SELECT professeur_id FROM professeurs WHERE user_id = :user_id",
            ['user_id' => $_SESSION['user_id'] ?? 0]
        );
        
        if (!$professeur) {
            return ['success' => false, 'error' => 'Permission refusée'];
        }
        
        $est_professeur_classe = db_query_single(
            "SELECT 1 FROM emploi_du_temps e
             WHERE e.professeur_id = :professeur_id
             AND e.matiere_id = :matiere_id
             AND e.class_id = :class_id
             AND e.statut = 'actif'
             LIMIT 1",
            [
                'professeur_id' => $professeur['professeur_id'],
                'matiere_id' => $matiere_id,
                'class_id' => $class_id
            ]
        );
        
        if (!$est_professeur_classe) {
            return ['success' => false, 'error' => 'Permission refusée'];
        }
    }
    
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Vérifier que la matière existe
    $matiere = matiere_get_by_id($matiere_id);
    if (!$matiere) {
        return ['success' => false, 'error' => 'Matière non trouvée'];
    }
    
    // Vérifier que la matière est enseignée dans cette classe
    $matiere_classe = db_query_single(
        "SELECT 1 FROM emploi_du_temps e
         WHERE e.class_id = :class_id
         AND e.matiere_id = :matiere_id
         AND e.statut = 'actif'
         LIMIT 1",
        ['class_id' => $class_id, 'matiere_id' => $matiere_id]
    );
    
    if (!$matiere_classe) {
        return [
            'success' => false,
            'error' => "Cette matière n'est pas enseignée dans cette classe"
        ];
    }
    
    // Valider les données de base
    if (empty($donnees['date_evaluation']) || empty($donnees['periode']) || empty($donnees['annee_id'])) {
        return ['success' => false, 'error' => 'Données de base manquantes'];
    }
    
    $resultats = [
        'succes' => 0,
        'echecs' => 0,
        'details' => [],
        'erreurs' => []
    ];
    
    // Récupérer le professeur courant
    $professeur_id = null;
    $professeur = db_query_single(
        "SELECT professeur_id FROM professeurs WHERE user_id = :user_id",
        ['user_id' => $_SESSION['user_id'] ?? 0]
    );
    
    if ($professeur) {
        $professeur_id = $professeur['professeur_id'];
    }
    
    // Démarrer la transaction
    db_begin_transaction();
    
    try {
        // Récupérer tous les élèves de la classe
        $eleves = classe_get_eleves($class_id);
        
        foreach ($eleves as $eleve) {
            // Vérifier si une note est fournie pour cet élève
            $note_value = $donnees['notes'][$eleve['eleve_id']] ?? null;
            
            if ($note_value === null || $note_value === '') {
                // Pas de note pour cet élève, on passe
                continue;
            }
            
            // Préparer les données de la note
            $donnees_note = [
                'eleve_id' => $eleve['eleve_id'],
                'matiere_id' => $matiere_id,
                'type_evaluation' => $type_evaluation,
                'note' => floatval($note_value),
                'coefficient' => $donnees['coefficient'] ?? 1.0,
                'date_evaluation' => $donnees['date_evaluation'],
                'annee_id' => $donnees['annee_id'],
                'periode' => $donnees['periode'],
                'professeur_id' => $professeur_id,
                'remarques' => $donnees['remarques'] ?? ''
            ];
            
            // Créer la note
            $resultat = note_creer($donnees_note);
            
            if ($resultat['success']) {
                $resultats['succes']++;
                $resultats['details'][] = [
                    'eleve_id' => $eleve['eleve_id'],
                    'eleve_nom' => $eleve['nom_complet'],
                    'note' => $note_value,
                    'note_id' => $resultat['note_id'],
                    'message' => 'Succès'
                ];
            } else {
                $resultats['echecs']++;
                $resultats['erreurs'][] = [
                    'eleve_id' => $eleve['eleve_id'],
                    'eleve_nom' => $eleve['nom_complet'],
                    'error' => $resultat['error'] ?? 'Erreur inconnue'
                ];
            }
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser la saisie en masse
        log_action('Notes saisies en masse', [
            'class_id' => $class_id,
            'classe_nom' => $classe['nom_complet'],
            'matiere_id' => $matiere_id,
            'matiere_nom' => $matiere['nom_matiere'],
            'type_evaluation' => $type_evaluation,
            'nombre_notes' => count($eleves),
            'succes' => $resultats['succes'],
            'echecs' => $resultats['echecs'],
            'date_evaluation' => $donnees['date_evaluation'],
            'periode' => $donnees['periode'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'notes');
        
        return [
            'success' => true,
            'resultats' => $resultats,
            'message' => "Saisie terminée: {$resultats['succes']} notes créées, {$resultats['echecs']} échecs"
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur note_saisir_masse: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la saisie en masse'];
    }
}

// =============================================
// FONCTIONS DE STATISTIQUES
// =============================================

/**
 * Obtenir les statistiques des notes pour une classe
 */
function note_get_statistiques_classe(int $class_id, string $periode, int $annee_id): array
{
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Récupérer tous les élèves de la classe
    $eleves = classe_get_eleves($class_id);
    
    $statistiques = [
        'classe' => [
            'id' => $classe['class_id'],
            'libelle' => $classe['nom_complet'],
            'niveau' => $classe['nom_niveau'],
            'section' => $classe['nom_section'],
            'nombre_eleves' => count($eleves)
        ],
        'periode' => $periode,
        'annee_id' => $annee_id,
        'matieres' => [],
        'moyennes_eleves' => [],
        'generales' => []
    ];
    
    // Récupérer les matières de la classe
    $matieres = classe_get_matieres($class_id);
    
    foreach ($matieres as $matiere) {
        // Calculer les statistiques pour chaque matière
        $stats_matiere = note_calculer_statistiques_matiere_classe($class_id, $matiere['matiere_id'], $periode, $annee_id);
        
        $statistiques['matieres'][] = [
            'matiere' => $matiere,
            'statistiques' => $stats_matiere
        ];
    }
    
    // Calculer les moyennes générales pour chaque élève
    foreach ($eleves as $eleve) {
        $moyenne_generale = note_calculer_moyenne_generale($eleve['eleve_id'], $periode, $annee_id);
        
        if ($moyenne_generale['calcul_possible']) {
            $statistiques['moyennes_eleves'][] = [
                'eleve' => [
                    'id' => $eleve['eleve_id'],
                    'nom_complet' => $eleve['nom_complet'],
                    'matricule' => $eleve['matricule']
                ],
                'moyenne_generale' => $moyenne_generale['moyenne_generale'],
                'matieres_reussies' => $moyenne_generale['matieres_reussies'],
                'mention' => $moyenne_generale['mention_generale']
            ];
        }
    }
    
    // Trier les élèves par moyenne décroissante
    usort($statistiques['moyennes_eleves'], function($a, $b) {
        return $b['moyenne_generale'] <=> $a['moyenne_generale'];
    });
    
    // Calculer les statistiques générales de la classe
    $moyennes = array_column($statistiques['moyennes_eleves'], 'moyenne_generale');
    
    if (!empty($moyennes)) {
        $statistiques['generales'] = [
            'moyenne_classe' => round(array_sum($moyennes) / count($moyennes), 2),
            'meilleure_moyenne' => max($moyennes),
            'plus_basse_moyenne' => min($moyennes),
            'ecart_type' => note_calculer_ecart_type($moyennes),
            'nombre_eleves_avec_moyenne' => count($moyennes),
            'repartition_mentions' => note_calculer_repartition_mentions($statistiques['moyennes_eleves'])
        ];
    }
    
    return [
        'success' => true,
        'statistiques' => $statistiques,
        'date_calcul' => date('Y-m-d H:i:s')
    ];
}

/**
 * Calculer les statistiques d'une matière pour une classe
 */
function note_calculer_statistiques_matiere_classe(int $class_id, int $matiere_id, string $periode, int $annee_id): array
{
    // Récupérer tous les élèves de la classe
    $eleves = classe_get_eleves($class_id);
    
    $notes = [];
    $moyennes = [];
    
    foreach ($eleves as $eleve) {
        $moyenne_matiere = note_calculer_moyenne_matiere($eleve['eleve_id'], $matiere_id, $periode, $annee_id);
        
        if ($moyenne_matiere['calcul_possible'] && $moyenne_matiere['moyenne'] !== null) {
            $notes[] = [
                'eleve_id' => $eleve['eleve_id'],
                'eleve_nom' => $eleve['nom_complet'],
                'moyenne' => $moyenne_matiere['moyenne'],
                'reussi' => $moyenne_matiere['reussi']
            ];
            
            $moyennes[] = $moyenne_matiere['moyenne'];
        }
    }
    
    if (empty($moyennes)) {
        return [
            'calcul_possible' => false,
            'message' => 'Aucune note disponible'
        ];
    }
    
    // Calculer les statistiques
    $moyenne_classe = round(array_sum($moyennes) / count($moyennes), 2);
    $meilleure_note = max($moyennes);
    $plus_basse_note = min($moyennes);
    
    // Compter les réussites
    $reussites = array_filter($notes, function($note) {
        return $note['reussi'];
    });
    
    // Trier les élèves par moyenne décroissante
    usort($notes, function($a, $b) {
        return $b['moyenne'] <=> $a['moyenne'];
    });
    
    return [
        'calcul_possible' => true,
        'nombre_eleves_avec_notes' => count($notes),
        'nombre_eleves_total' => count($eleves),
        'moyenne_classe' => $moyenne_classe,
        'meilleure_note' => $meilleure_note,
        'plus_basse_note' => $plus_basse_note,
        'nombre_reussites' => count($reussites),
        'nombre_echecs' => count($notes) - count($reussites),
        'taux_reussite' => round((count($reussites) / count($notes)) * 100, 1),
        'ecart_type' => note_calculer_ecart_type($moyennes),
        'repartition_notes' => note_calculer_repartition_notes($moyennes),
        'classement_eleves' => $notes
    ];
}

/**
 * Calculer l'écart-type d'un ensemble de notes
 */
function note_calculer_ecart_type(array $notes): float
{
    if (count($notes) < 2) {
        return 0;
    }
    
    $moyenne = array_sum($notes) / count($notes);
    $somme_carres = 0;
    
    foreach ($notes as $note) {
        $somme_carres += pow($note - $moyenne, 2);
    }
    
    $variance = $somme_carres / (count($notes) - 1);
    return round(sqrt($variance), 2);
}

/**
 * Calculer la répartition des notes par intervalles
 */
function note_calculer_repartition_notes(array $notes): array
{
    $intervalles = [
        [0, 5, '0-5'],
        [5, 10, '5-10'],
        [10, 12, '10-12'],
        [12, 14, '12-14'],
        [14, 16, '14-16'],
        [16, 18, '16-18'],
        [18, 20, '18-20']
    ];
    
    $repartition = [];
    
    foreach ($intervalles as $intervalle) {
        $count = count(array_filter($notes, function($note) use ($intervalle) {
            return $note >= $intervalle[0] && $note < $intervalle[1];
        }));
        
        // Pour le dernier intervalle, inclure la note 20
        if ($intervalle[1] == 20) {
            $count = count(array_filter($notes, function($note) use ($intervalle) {
                return $note >= $intervalle[0] && $note <= $intervalle[1];
            }));
        }
        
        $pourcentage = count($notes) > 0 ? round(($count / count($notes)) * 100, 1) : 0;
        
        $repartition[] = [
            'intervalle' => $intervalle[2],
            'min' => $intervalle[0],
            'max' => $intervalle[1],
            'nombre' => $count,
            'pourcentage' => $pourcentage
        ];
    }
    
    return $repartition;
}

/**
 * Calculer la répartition des mentions
 */
function note_calculer_repartition_mentions(array $moyennes_eleves): array
{
    $mentions = [
        'Très Bien' => 0,
        'Bien' => 0,
        'Assez Bien' => 0,
        'Passable' => 0,
        'Insuffisant' => 0
    ];
    
    foreach ($moyennes_eleves as $eleve) {
        $mention = note_get_mention($eleve['moyenne_generale']);
        $mentions[$mention]++;
    }
    
    $resultat = [];
    $total = count($moyennes_eleves);
    
    foreach ($mentions as $mention => $count) {
        $pourcentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
        
        $resultat[] = [
            'mention' => $mention,
            'nombre' => $count,
            'pourcentage' => $pourcentage
        ];
    }
    
    return $resultat;
}

// =============================================
// FONCTIONS D'EXPORT ET RAPPORTS
// =============================================

/**
 * Exporter les notes au format CSV
 */
function note_exporter_csv(array $filtres = []): array
{
    // Vérifier les permissions
    if (!check_access('notes', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        // Récupérer toutes les notes selon les filtres
        $resultat = note_rechercher($filtres, 1, 10000);
        
        if (!$resultat['success']) {
            return $resultat;
        }
        
        $notes = $resultat['notes'];
        
        // Générer le CSV
        $output = fopen('php://temp', 'r+');
        
        // En-têtes
        $headers = [
            'Matricule', 'Élève', 'Classe', 'Matière', 'Type d\'évaluation',
            'Note', 'Coefficient', 'Note pondérée', 'Date évaluation',
            'Période', 'Année scolaire', 'Professeur', 'Réussi', 'Remarques'
        ];
        
        fputcsv($output, $headers, ';');
        
        // Données
        foreach ($notes as $note) {
            $row = [
                $note['matricule'],
                $note['eleve_nom_complet'],
                $note['classe_libelle'] ?? 'Non spécifié',
                $note['nom_matiere'],
                $note['type_evaluation'],
                $note['note'],
                $note['coefficient'],
                $note['note_ponderee'],
                format_date($note['date_evaluation']),
                ucfirst(str_replace('trimestre', 'Trimestre ', $note['periode'])),
                $note['annee_libelle'],
                $note['professeur_nom_complet'],
                $note['reussi'] ? 'Oui' : 'Non',
                $note['remarques'] ?? ''
            ];
            
            fputcsv($output, $row, ';');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        // Journaliser l'export
        log_action('Export notes CSV', [
            'nombre' => count($notes),
            'filtres' => $filtres,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'notes');
        
        return [
            'success' => true,
            'data' => $csv,
            'format' => 'csv',
            'extension' => 'csv',
            'mime_type' => 'text/csv',
            'filename' => 'notes_' . date('Ymd_His') . '.csv',
            'count' => count($notes)
        ];
        
    } catch (Exception $e) {
        error_log("Erreur note_exporter_csv: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'export'];
    }
}

/**
 * Générer un rapport détaillé des notes
 */
function note_generer_rapport(int $class_id, string $periode, int $annee_id, string $format = 'html'): array
{
    // Vérifier les permissions
    if (!check_access('notes', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Récupérer les statistiques de la classe
    $statistiques = note_get_statistiques_classe($class_id, $periode, $annee_id);
    
    if (!$statistiques['success']) {
        return $statistiques;
    }
    
    // Générer le rapport selon le format
    switch (strtolower($format)) {
        case 'html':
            $rapport = note_generer_rapport_html($statistiques['statistiques']);
            $extension = 'html';
            $mime_type = 'text/html';
            break;
            
        case 'pdf':
            $rapport = note_generer_rapport_pdf($statistiques['statistiques']);
            $extension = 'pdf';
            $mime_type = 'application/pdf';
            break;
            
        default:
            return ['success' => false, 'error' => 'Format non supporté'];
    }
    
    // Journaliser la génération du rapport
    log_action('Rapport notes généré', [
        'class_id' => $class_id,
        'periode' => $periode,
        'annee_id' => $annee_id,
        'format' => $format,
        'par_utilisateur' => $_SESSION['user_id'] ?? null
    ], 'notes');
    
    return [
        'success' => true,
        'data' => $rapport,
        'format' => $format,
        'extension' => $extension,
        'mime_type' => $mime_type,
        'filename' => 'rapport_notes_' . $class_id . '_' . $periode . '_' . date('Ymd_His') . '.' . $extension
    ];
}

/**
 * Générer un rapport HTML
 */
function note_generer_rapport_html(array $statistiques): string
{
    $html = '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Rapport des notes - ' . e($statistiques['classe']['libelle']) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1, h2, h3 { color: #2c3e50; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .success { color: #27ae60; }
            .danger { color: #e74c3c; }
            .warning { color: #f39c12; }
            .info { background-color: #3498db; color: white; }
            .header { text-align: center; margin-bottom: 30px; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #7f8c8d; }
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
            .stat-card { background: #f8f9fa; border-left: 4px solid #3498db; padding: 15px; border-radius: 4px; }
            .stat-value { font-size: 24px; font-weight: bold; }
            .stat-label { color: #7f8c8d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . e(ECOLE_NOM) . '</h1>
            <h2>Rapport des notes - ' . e($statistiques['classe']['libelle']) . '</h2>
            <p>Période: ' . e(ucfirst(str_replace('trimestre', 'Trimestre ', $statistiques['periode']))) . ' | Date de génération: ' . date('d/m/Y H:i') . '</p>
        </div>';
    
    // Statistiques générales
    if (!empty($statistiques['generales'])) {
        $html .= '
        <h3>Statistiques Générales de la Classe</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">' . e($statistiques['generales']['moyenne_classe']) . '/20</div>
                <div class="stat-label">Moyenne de classe</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . e($statistiques['generales']['meilleure_moyenne']) . '/20</div>
                <div class="stat-label">Meilleure moyenne</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . e($statistiques['classe']['nombre_eleves']) . '</div>
                <div class="stat-label">Nombre d\'élèves</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . e($statistiques['generales']['ecart_type']) . '</div>
                <div class="stat-label">Écart-type</div>
            </div>
        </div>';
    }
    
    // Classement des élèves
    if (!empty($statistiques['moyennes_eleves'])) {
        $html .= '
        <h3>Classement des Élèves</h3>
        <table>
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Élève</th>
                    <th>Matricule</th>
                    <th>Moyenne Générale</th>
                    <th>Mat. Réussies</th>
                    <th>Mention</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($statistiques['moyennes_eleves'] as $index => $eleve) {
            $classe_reussi = $eleve['moyenne_generale'] >= SEUIL_REUSSITE ? 'success' : 'danger';
            
            $html .= '
                <tr>
                    <td>' . ($index + 1) . '</td>
                    <td>' . e($eleve['eleve']['nom_complet']) . '</td>
                    <td>' . e($eleve['eleve']['matricule']) . '</td>
                    <td class="' . $classe_reussi . '">' . e($eleve['moyenne_generale']) . '/20</td>
                    <td>' . e($eleve['matieres_reussies']) . '</td>
                    <td>' . e($eleve['mention']) . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>';
    }
    
    // Statistiques par matière
    if (!empty($statistiques['matieres'])) {
        $html .= '
        <h3>Statistiques par Matière</h3>
        <table>
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Moyenne Classe</th>
                    <th>Meilleure Note</th>
                    <th>+ Basse Note</th>
                    <th>Taux Réussite</th>
                    <th>Écart-type</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($statistiques['matieres'] as $matiere_stat) {
            $stats = $matiere_stat['statistiques'];
            
            if ($stats['calcul_possible']) {
                $classe_taux = $stats['taux_reussite'] >= 70 ? 'success' : ($stats['taux_reussite'] >= 50 ? 'warning' : 'danger');
                
                $html .= '
                    <tr>
                        <td>' . e($matiere_stat['matiere']['nom_matiere']) . '</td>
                        <td>' . e($stats['moyenne_classe']) . '/20</td>
                        <td>' . e($stats['meilleure_note']) . '/20</td>
                        <td>' . e($stats['plus_basse_note']) . '/20</td>
                        <td class="' . $classe_taux . '">' . e($stats['taux_reussite']) . '%</td>
                        <td>' . e($stats['ecart_type']) . '</td>
                    </tr>';
            }
        }
        
        $html .= '
            </tbody>
        </table>';
    }
    
    // Distribution des mentions
    if (!empty($statistiques['generales']['repartition_mentions'])) {
        $html .= '
        <h3>Distribution des Mentions</h3>
        <table>
            <thead>
                <tr>
                    <th>Mention</th>
                    <th>Nombre d\'Élèves</th>
                    <th>Pourcentage</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($statistiques['generales']['repartition_mentions'] as $mention) {
            $html .= '
                <tr>
                    <td>' . e($mention['mention']) . '</td>
                    <td>' . e($mention['nombre']) . '</td>
                    <td>' . e($mention['pourcentage']) . '%</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>';
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

/**
 * Générer un rapport PDF (placeholder - à implémenter avec une librairie PDF)
 */
function note_generer_rapport_pdf(array $statistiques): string
{
    // En production, utiliser une librairie comme TCPDF ou Dompdf
    // Pour l'instant, on retourne le HTML
    return note_generer_rapport_html($statistiques);
}

// =============================================
// FONCTIONS D'AUDIT ET CONTRÔLE
// =============================================

/**
 * Vérifier l'intégrité des notes (détecter les anomalies)
 */
function note_verifier_integrite(int $class_id, string $periode, int $annee_id): array
{
    $anomalies = [];
    
    // Récupérer tous les élèves de la classe
    $eleves = classe_get_eleves($class_id);
    
    foreach ($eleves as $eleve) {
        // Vérifier pour chaque matière
        $matieres = classe_get_matieres($class_id);
        
        foreach ($matieres as $matiere) {
            // Récupérer les notes de l'élève pour cette matière
            $notes = note_get_par_eleve_matiere_periode($eleve['eleve_id'], $matiere['matiere_id'], $periode, $annee_id);
            
            // Vérifier les anomalies
            $anomalies_matiere = note_detecter_anomalies($notes, $eleve, $matiere);
            
            if (!empty($anomalies_matiere)) {
                $anomalies[] = [
                    'eleve' => $eleve['nom_complet'],
                    'matiere' => $matiere['nom_matiere'],
                    'anomalies' => $anomalies_matiere
                ];
            }
        }
    }
    
    return [
        'success' => true,
        'class_id' => $class_id,
        'periode' => $periode,
        'annee_id' => $annee_id,
        'nombre_eleves' => count($eleves),
        'anomalies' => $anomalies,
        'nombre_anomalies' => count($anomalies),
        'date_verification' => date('Y-m-d H:i:s')
    ];
}

/**
 * Détecter les anomalies dans les notes d'un élève
 */
function note_detecter_anomalies(array $notes, array $eleve, array $matiere): array
{
    $anomalies = [];
    
    if (empty($notes)) {
        // Pas de notes - pas nécessairement une anomalie
        return $anomalies;
    }
    
    // 1. Vérifier les notes hors plage
    foreach ($notes as $note) {
        if ($note['note'] < 0 || $note['note'] > 20) {
            $anomalies[] = [
                'type' => 'note_hors_plage',
                'message' => "Note hors plage (0-20): {$note['note']}",
                'note_id' => $note['note_id'],
                'type_evaluation' => $note['type_evaluation']
            ];
        }
    }
    
    // 2. Vérifier les coefficients inhabituels
    foreach ($notes as $note) {
        if ($note['coefficient'] < 0.1 || $note['coefficient'] > 5) {
            $anomalies[] = [
                'type' => 'coefficient_anormal',
                'message' => "Coefficient anormal: {$note['coefficient']}",
                'note_id' => $note['note_id'],
                'valeur' => $note['coefficient']
            ];
        }
    }
    
    // 3. Vérifier les dates d'évaluation dans le futur
    $date_actuelle = new DateTime();
    foreach ($notes as $note) {
        $date_evaluation = new DateTime($note['date_evaluation']);
        if ($date_evaluation > $date_actuelle) {
            $anomalies[] = [
                'type' => 'date_future',
                'message' => "Date d'évaluation dans le futur: " . $date_evaluation->format('d/m/Y'),
                'note_id' => $note['note_id'],
                'date_evaluation' => $note['date_evaluation']
            ];
        }
    }
    
    // 4. Vérifier les doublons (même type, même date)
    $types_dates = [];
    foreach ($notes as $note) {
        $cle = $note['type_evaluation'] . '_' . $note['date_evaluation'];
        
        if (isset($types_dates[$cle])) {
            $anomalies[] = [
                'type' => 'doublon_possible',
                'message' => "Plusieurs notes du même type le même jour",
                'note_ids' => [$types_dates[$cle], $note['note_id']],
                'type_evaluation' => $note['type_evaluation'],
                'date_evaluation' => $note['date_evaluation']
            ];
        } else {
            $types_dates[$cle] = $note['note_id'];
        }
    }
    
    // 5. Vérifier les écarts extrêmes entre notes
    if (count($notes) >= 2) {
        $valeurs = array_column($notes, 'note');
        $ecart = max($valeurs) - min($valeurs);
        
        if ($ecart > 10) {
            $anomalies[] = [
                'type' => 'ecart_extreme',
                'message' => "Écart extrême entre notes: {$ecart} points",
                'note_min' => min($valeurs),
                'note_max' => max($valeurs),
                'ecart' => $ecart
            ];
        }
    }
    
    return $anomalies;
}

/**
 * Obtenir l'historique des modifications d'une note
 */
function note_get_historique_modifications(int $note_id): array
{
    // Cette fonction serait complète avec une table d'audit des modifications
    // Pour l'instant, on retourne l'historique des rectifications
    
    return note_get_historique_rectifications($note_id);
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Formater une note pour l'affichage
 */
function note_format(float $note, int $decimales = 2): string
{
    return number_format($note, $decimales, ',', ' ');
}

/**
 * Vérifier si une période est encore modifiable
 */
function note_periode_est_modifiable(string $periode, int $annee_id): bool
{
    // Récupérer les dates de la période
    $dates_periode = note_get_dates_periode($periode, $annee_id);
    
    // Calculer la date limite (1 mois après la fin de la période)
    $date_fin_periode = new DateTime($dates_periode['date_fin']);
    $date_limite = clone $date_fin_periode;
    $date_limite->add(new DateInterval('P1M'));
    
    $date_actuelle = new DateTime();
    
    return $date_actuelle <= $date_limite;
}

/**
 * Obtenir le coefficient par défaut pour un type d'évaluation
 */
function note_get_coefficient_par_defaut(string $type_evaluation): float
{
    $coefficients = [
        TYPE_DEVOIR => COEF_DEVOIR,
        TYPE_EXAMEN => COEF_EXAMEN,
        TYPE_PROJET => COEF_PROJET,
        TYPE_PARTICIPATION => 0.1,
        TYPE_COMPOSITION => COEF_EXAMEN
    ];
    
    return $coefficients[$type_evaluation] ?? 1.0;
}

/**
 * Obtenir la liste des types d'évaluation
 */
function note_get_types_evaluation(): array
{
    return [
        TYPE_DEVOIR => 'Devoir',
        TYPE_EXAMEN => 'Examen',
        TYPE_PROJET => 'Projet',
        TYPE_PARTICIPATION => 'Participation',
        TYPE_COMPOSITION => 'Composition'
    ];
}

/**
 * Obtenir la liste des périodes académiques
 */
function note_get_periodes(): array
{
    return [
        PERIODE_TRIMESTRE1 => 'Premier trimestre',
        PERIODE_TRIMESTRE2 => 'Deuxième trimestre',
        PERIODE_TRIMESTRE3 => 'Troisième trimestre'
    ];
}

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Journaliser le chargement du module
log_action('Module notes chargé', ['version' => '1.0.0']);