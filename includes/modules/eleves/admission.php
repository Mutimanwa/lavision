<?php
/**
 * Gestion des admissions scolaires
 * Processus d'admission, approbation, affectation aux classes
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/eleves.php';

// =============================================
// CONSTANTES D'ADMISSION
// =============================================

// Statuts d'admission
define('ADMISSION_EN_ATTENTE', 'en_attente');
define('ADMISSION_APPROUVE', 'approuve');
define('ADMISSION_REJETE', 'rejete');
define('ADMISSION_LISTE_ATTENTE', 'liste_attente');

// Types de décision
define('DECISION_AUTOMATIQUE', 'automatique');
define('DECISION_MANUEL', 'manuel');
define('DECISION_COMITE', 'comite');

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'une admission
 */
function valider_donnees_admission(array $donnees): array
{
    $erreurs = [];
    
    // Champs obligatoires
    $champs_requis = ['eleve_id', 'class_id', 'annee_id'];
    
    foreach ($champs_requis as $champ) {
        if (empty($donnees[$champ])) {
            $erreurs[$champ] = "Ce champ est requis";
        } elseif (!is_numeric($donnees[$champ])) {
            $erreurs[$champ] = "Valeur numérique requise";
        }
    }
    
    // Vérifier que l'élève existe et est éligible
    if (!empty($donnees['eleve_id'])) {
        $eleve = eleve_get_by_id((int)$donnees['eleve_id']);
        if (!$eleve) {
            $erreurs['eleve_id'] = "Élève non trouvé";
        } elseif ($eleve['statut_etudiant'] === ELEVE_DESISTE) {
            $erreurs['eleve_id'] = "Élève désisté, non éligible à l'admission";
        }
    }
    
    // Vérifier que la classe existe et a de la capacité
    if (!empty($donnees['class_id'])) {
        $classe = db_query_single(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM admissions a 
                     WHERE a.class_id = c.class_id 
                     AND a.statut_admission = :statut_approuve) as nombre_eleves
             FROM classes c 
             WHERE c.class_id = :class_id",
            ['class_id' => $donnees['class_id'], 'statut_approuve' => ADMISSION_APPROUVE]
        );
        
        if (!$classe) {
            $erreurs['class_id'] = "Classe non trouvée";
        } elseif ($classe['capacite_max'] > 0 && $classe['nombre_eleves'] >= $classe['capacite_max']) {
            $erreurs['class_id'] = "Classe au complet (capacité: {$classe['capacite_max']})";
        }
    }
    
    // Vérifier que l'année scolaire existe et est active
    if (!empty($donnees['annee_id'])) {
        $annee = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE annee_id = :annee_id AND statut = 'active'",
            ['annee_id' => $donnees['annee_id']]
        );
        
        if (!$annee) {
            $erreurs['annee_id'] = "Année scolaire inactive ou non trouvée";
        }
    }
    
    // Vérifier les frais si fournis
    if (isset($donnees['frais_inscription']) && $donnees['frais_inscription'] < 0) {
        $erreurs['frais_inscription'] = "Les frais ne peuvent pas être négatifs";
    }
    
    // Vérifier la date limite de paiement
    if (!empty($donnees['date_limite_paiement'])) {
        $date_limite = DateTime::createFromFormat('Y-m-d', $donnees['date_limite_paiement']);
        if (!$date_limite || $date_limite->format('Y-m-d') !== $donnees['date_limite_paiement']) {
            $erreurs['date_limite_paiement'] = "Format de date invalide (YYYY-MM-DD)";
        } elseif ($date_limite < new DateTime()) {
            $erreurs['date_limite_paiement'] = "La date limite ne peut pas être dans le passé";
        }
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - ADMISSIONS
// =============================================

/**
 * Créer une nouvelle admission
 */
function admission_creer(array $donnees): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'create')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = valider_donnees_admission($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Démarrer la transaction
    db_begin_transaction();
    
    try {
        // Vérifier si l'élève a déjà une admission active pour cette année
        $admission_existante = db_query_single(
            "SELECT admission_id, statut_admission 
             FROM admissions 
             WHERE eleve_id = :eleve_id 
             AND annee_id = :annee_id
             AND statut_admission IN (:en_attente, :approuve)",
            [
                'eleve_id' => $donnees['eleve_id'],
                'annee_id' => $donnees['annee_id'],
                'en_attente' => ADMISSION_EN_ATTENTE,
                'approuve' => ADMISSION_APPROUVE
            ]
        );
        
        if ($admission_existante) {
            return [
                'success' => false,
                'error' => "Cet élève a déjà une admission {$admission_existante['statut_admission']} pour cette année"
            ];
        }
        
        // Vérifier la capacité de la classe
        $capacite = verifier_capacite_classe((int)$donnees['class_id']);
        if (!$capacite['disponible']) {
            return [
                'success' => false,
                'error' => "Classe au complet. Places disponibles: {$capacite['places_disponibles']}"
            ];
        }
        
        // Préparer les données pour l'insertion
        $champs = [
            'eleve_id' => (int)$donnees['eleve_id'],
            'class_id' => (int)$donnees['class_id'],
            'annee_id' => (int)$donnees['annee_id'],
            'admis_par' => $_SESSION['user_id'] ?? null,
            'statut_admission' => $donnees['statut_admission'] ?? ADMISSION_EN_ATTENTE,
            'frais_inscription' => $donnees['frais_inscription'] ?? 0,
            'frais_payes' => $donnees['frais_payes'] ?? 0,
            'date_limite_paiement' => $donnees['date_limite_paiement'] ?? null,
            'notes_entretien' => trim($donnees['notes_entretien'] ?? ''),
            'decision_comite' => trim($donnees['decision_comite'] ?? ''),
            'date_decision' => $donnees['date_decision'] ?? null
        ];
        
        // Si le statut est approuvé, vérifier les frais
        if ($champs['statut_admission'] === ADMISSION_APPROUVE) {
            if (empty($champs['date_decision'])) {
                $champs['date_decision'] = date('Y-m-d');
            }
            
            // Mettre à jour le statut de l'élève
            $success = db_execute(
                "UPDATE eleves SET statut_etudiant = :statut WHERE eleve_id = :eleve_id",
                ['statut' => ELEVE_ACTIF, 'eleve_id' => $champs['eleve_id']]
            );
            
            if (!$success) {
                throw new Exception("Erreur lors de la mise à jour du statut de l'élève");
            }
        }
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO admissions ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $admission_id = db_last_insert_id();
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        $eleve = eleve_get_by_id($champs['eleve_id']);
        $classe = db_query_single("SELECT libelle FROM classes WHERE class_id = :class_id", 
            ['class_id' => $champs['class_id']]);
        
        log_action('Admission créée', [
            'admission_id' => $admission_id,
            'eleve_id' => $champs['eleve_id'],
            'eleve_nom' => $eleve ? $eleve['nom_complet'] : 'Inconnu',
            'class_id' => $champs['class_id'],
            'classe' => $classe ? $classe['libelle'] : 'Inconnue',
            'statut' => $champs['statut_admission'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'admissions');
        
        return [
            'success' => true,
            'admission_id' => $admission_id,
            'message' => 'Admission créée avec succès'
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur admission_creer: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour une admission existante
 */
function admission_modifier(int $admission_id, array $donnees): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'admission existe
    $admission_existante = admission_get_by_id($admission_id);
    if (!$admission_existante) {
        return ['success' => false, 'error' => 'Admission non trouvée'];
    }
    
    // Valider les données
    $erreurs = valider_donnees_admission($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Ne pas permettre la modification d'une admission approuvée
    if ($admission_existante['statut_admission'] === ADMISSION_APPROUVE) {
        // Seuls certains champs peuvent être modifiés
        $champs_modifiables = ['notes_entretien', 'decision_comite', 'date_limite_paiement'];
        $donnees = array_intersect_key($donnees, array_flip($champs_modifiables));
        
        if (empty($donnees)) {
            return ['success' => false, 'error' => 'Admission approuvée, modification limitée'];
        }
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['admission_id' => $admission_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'class_id', 'frais_inscription', 'frais_payes', 'date_limite_paiement',
            'notes_entretien', 'decision_comite', 'date_decision', 'statut_admission'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                $params[$champ] = $donnees[$champ];
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Si le statut change vers "approuvé"
        if (isset($donnees['statut_admission']) && $donnees['statut_admission'] === ADMISSION_APPROUVE) {
            // Vérifier la capacité de la classe
            $class_id = $donnees['class_id'] ?? $admission_existante['class_id'];
            $capacite = verifier_capacite_classe((int)$class_id, $admission_id);
            
            if (!$capacite['disponible']) {
                return [
                    'success' => false,
                    'error' => "Classe au complet. Places disponibles: {$capacite['places_disponibles']}"
                ];
            }
            
            // Ajouter la date de décision si non fournie
            if (!isset($donnees['date_decision'])) {
                $updates[] = "date_decision = :date_decision";
                $params['date_decision'] = date('Y-m-d');
            }
            
            // Mettre à jour le statut de l'élève
            db_execute(
                "UPDATE eleves SET statut_etudiant = :statut WHERE eleve_id = :eleve_id",
                ['statut' => ELEVE_ACTIF, 'eleve_id' => $admission_existante['eleve_id']]
            );
        }
        
        // Si le statut change vers "rejeté" ou "liste d'attente"
        if (isset($donnees['statut_admission']) && 
            in_array($donnees['statut_admission'], [ADMISSION_REJETE, ADMISSION_LISTE_ATTENTE])) {
            
            // Remettre l'élève en attente s'il n'était pas actif
            if ($admission_existante['statut_admission'] === ADMISSION_APPROUVE) {
                db_execute(
                    "UPDATE eleves SET statut_etudiant = :statut WHERE eleve_id = :eleve_id",
                    ['statut' => ELEVE_EN_ATTENTE, 'eleve_id' => $admission_existante['eleve_id']]
                );
            }
        }
        
        // Construction de la requête
        $sql = "UPDATE admissions SET " . implode(', ', $updates) . " WHERE admission_id = :admission_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Admission modifiée', [
                'admission_id' => $admission_id,
                'eleve_id' => $admission_existante['eleve_id'],
                'changements' => $changements,
                'ancien_statut' => $admission_existante['statut_admission'],
                'nouveau_statut' => $donnees['statut_admission'] ?? $admission_existante['statut_admission'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'admissions');
            
            return [
                'success' => true,
                'message' => 'Admission mise à jour avec succès',
                'admission_id' => $admission_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur admission_modifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Supprimer une admission
 */
function admission_supprimer(int $admission_id): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'delete')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'admission existe
    $admission = admission_get_by_id($admission_id);
    if (!$admission) {
        return ['success' => false, 'error' => 'Admission non trouvée'];
    }
    
    // Ne pas permettre la suppression d'une admission approuvée
    if ($admission['statut_admission'] === ADMISSION_APPROUVE) {
        return [
            'success' => false,
            'error' => 'Impossible de supprimer une admission approuvée. Utilisez "rejeter" à la place.'
        ];
    }
    
    try {
        // Supprimer l'admission
        $sql = "DELETE FROM admissions WHERE admission_id = :admission_id";
        $success = db_execute($sql, ['admission_id' => $admission_id]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Admission supprimée', [
                'admission_id' => $admission_id,
                'eleve_id' => $admission['eleve_id'],
                'statut' => $admission['statut_admission'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'admissions');
            
            return [
                'success' => true,
                'message' => 'Admission supprimée avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        
    } catch (Exception $e) {
        error_log("Erreur admission_supprimer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir une admission par son ID
 */
function admission_get_by_id(int $admission_id): ?array
{
    $sql = "SELECT a.*, 
                   e.matricule, e.nom, e.prenom, e.post_nom,
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   s.nom_section,
                   an.annee_libelle,
                   u.nom as admin_nom, u.prenom as admin_prenom
            FROM admissions a
            JOIN eleves e ON a.eleve_id = e.eleve_id
            JOIN classes c ON a.class_id = c.class_id
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            JOIN annees_scolaire an ON a.annee_id = an.annee_id
            LEFT JOIN user_admins u ON a.admis_par = u.user_id
            WHERE a.admission_id = :admission_id";
    
    $admission = db_query_single($sql, ['admission_id' => $admission_id]);
    
    if ($admission) {
        // Ajouter les informations calculées
        $admission['eleve_nom_complet'] = $admission['prenom'] . ' ' . $admission['nom'] . ' ' . $admission['post_nom'];
        $admission['admin_nom_complet'] = $admission['admin_prenom'] . ' ' . $admission['admin_nom'];
        $admission['frais_restants'] = $admission['frais_inscription'] - $admission['frais_payes'];
        
        // Ajouter le statut de paiement
        if ($admission['frais_restants'] <= 0) {
            $admission['statut_paiement'] = 'paye';
        } elseif ($admission['frais_payes'] > 0) {
            $admission['statut_paiement'] = 'partiel';
        } else {
            $admission['statut_paiement'] = 'impaye';
        }
        
        // Vérifier si la date limite est dépassée
        if ($admission['date_limite_paiement']) {
            $date_limite = new DateTime($admission['date_limite_paiement']);
            $aujourdhui = new DateTime();
            $admission['date_limite_depassee'] = $date_limite < $aujourdhui;
            $admission['jours_retard'] = $date_limite->diff($aujourdhui)->days;
        }
    }
    
    return $admission;
}

/**
 * Obtenir l'admission actuelle d'un élève
 */
function admission_get_actuelle(int $eleve_id): ?array
{
    $sql = "SELECT a.*, 
                   c.libelle as classe_libelle,
                   an.annee_libelle
            FROM admissions a
            JOIN classes c ON a.class_id = c.class_id
            JOIN annees_scolaire an ON a.annee_id = an.annee_id
            WHERE a.eleve_id = :eleve_id 
            AND a.statut_admission = :statut_approuve
            AND an.statut = 'active'
            ORDER BY a.date_admission DESC 
            LIMIT 1";
    
    return db_query_single($sql, [
        'eleve_id' => $eleve_id,
        'statut_approuve' => ADMISSION_APPROUVE
    ]);
}

/**
 * Obtenir l'historique des admissions d'un élève
 */
function admission_get_historique(int $eleve_id): array
{
    $sql = "SELECT a.*, 
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   s.nom_section,
                   an.annee_libelle,
                   u.nom as admin_nom, u.prenom as admin_prenom
            FROM admissions a
            JOIN classes c ON a.class_id = c.class_id
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            JOIN annees_scolaire an ON a.annee_id = an.annee_id
            LEFT JOIN user_admins u ON a.admis_par = u.user_id
            WHERE a.eleve_id = :eleve_id
            ORDER BY a.date_admission DESC";
    
    $admissions = db_query($sql, ['eleve_id' => $eleve_id]);
    
    // Ajouter des informations supplémentaires
    foreach ($admissions as &$admission) {
        $admission['eleve_nom_complet'] = $admission['prenom'] . ' ' . $admission['nom'] . ' ' . $admission['post_nom'];
        $admission['admin_nom_complet'] = $admission['admin_prenom'] . ' ' . $admission['admin_nom'];
        $admission['frais_restants'] = $admission['frais_inscription'] - $admission['frais_payes'];
    }
    
    return $admissions;
}

/**
 * Rechercher des admissions avec filtres
 */
function admission_rechercher(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   a.*,
                   e.matricule, e.nom, e.prenom, e.post_nom,
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   an.annee_libelle
            FROM admissions a
            JOIN eleves e ON a.eleve_id = e.eleve_id
            JOIN classes c ON a.class_id = c.class_id
            JOIN niveau n ON c.niveau_id = n.niveau_id
            JOIN annees_scolaire an ON a.annee_id = an.annee_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(e.nom LIKE :{$key} OR e.prenom LIKE :{$key} OR e.matricule LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par statut
    if (!empty($filtres['statut'])) {
        $sql .= " AND a.statut_admission = :statut";
        $params['statut'] = $filtres['statut'];
    }
    
    // Filtre par année scolaire
    if (!empty($filtres['annee_id'])) {
        $sql .= " AND a.annee_id = :annee_id";
        $params['annee_id'] = $filtres['annee_id'];
    }
    
    // Filtre par classe
    if (!empty($filtres['class_id'])) {
        $sql .= " AND a.class_id = :class_id";
        $params['class_id'] = $filtres['class_id'];
    }
    
    // Filtre par niveau
    if (!empty($filtres['niveau_id'])) {
        $sql .= " AND c.niveau_id = :niveau_id";
        $params['niveau_id'] = $filtres['niveau_id'];
    }
    
    // Filtre par date
    if (!empty($filtres['date_debut'])) {
        $sql .= " AND DATE(a.date_admission) >= :date_debut";
        $params['date_debut'] = $filtres['date_debut'];
    }
    
    if (!empty($filtres['date_fin'])) {
        $sql .= " AND DATE(a.date_admission) <= :date_fin";
        $params['date_fin'] = $filtres['date_fin'];
    }
    
    // Filtre par statut de paiement
    if (!empty($filtres['statut_paiement'])) {
        switch ($filtres['statut_paiement']) {
            case 'paye':
                $sql .= " AND a.frais_payes >= a.frais_inscription";
                break;
            case 'partiel':
                $sql .= " AND a.frais_payes > 0 AND a.frais_payes < a.frais_inscription";
                break;
            case 'impaye':
                $sql .= " AND (a.frais_payes = 0 OR a.frais_payes IS NULL)";
                break;
        }
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'a.date_admission';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'DESC';
    $sql .= " ORDER BY {$order_by} {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $admissions = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($admissions as &$admission) {
            $admission['eleve_nom_complet'] = $admission['prenom'] . ' ' . $admission['nom'] . ' ' . $admission['post_nom'];
            $admission['frais_restants'] = $admission['frais_inscription'] - $admission['frais_payes'];
            
            // Statut de paiement
            if ($admission['frais_restants'] <= 0) {
                $admission['statut_paiement'] = 'paye';
            } elseif ($admission['frais_payes'] > 0) {
                $admission['statut_paiement'] = 'partiel';
            } else {
                $admission['statut_paiement'] = 'impaye';
            }
        }
        
        return [
            'success' => true,
            'admissions' => $admissions,
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
        error_log("Erreur admission_rechercher: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

// =============================================
// FONCTIONS DE GESTION DES DÉCISIONS
// =============================================

/**
 * Approuver une admission
 */
function admission_approuver(int $admission_id, string $decision_comite = '', string $type_decision = DECISION_MANUEL): array
{
    // Vérifier les permissions (nécessite admin ou proviseur)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'admission existe
    $admission = admission_get_by_id($admission_id);
    if (!$admission) {
        return ['success' => false, 'error' => 'Admission non trouvée'];
    }
    
    // Vérifier que l'admission n'est pas déjà approuvée
    if ($admission['statut_admission'] === ADMISSION_APPROUVE) {
        return ['success' => false, 'error' => 'Admission déjà approuvée'];
    }
    
    // Vérifier la capacité de la classe
    $capacite = verifier_capacite_classe((int)$admission['class_id'], $admission_id);
    if (!$capacite['disponible']) {
        return [
            'success' => false,
            'error' => "Classe au complet. Places disponibles: {$capacite['places_disponibles']}"
        ];
    }
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        // Mettre à jour l'admission
        $sql = "UPDATE admissions 
                SET statut_admission = :statut, 
                    decision_comite = :decision_comite,
                    date_decision = :date_decision
                WHERE admission_id = :admission_id";
        
        $success = db_execute($sql, [
            'statut' => ADMISSION_APPROUVE,
            'decision_comite' => $decision_comite,
            'date_decision' => date('Y-m-d'),
            'admission_id' => $admission_id
        ]);
        
        if (!$success) {
            throw new Exception("Échec de la mise à jour de l'admission");
        }
        
        // Mettre à jour le statut de l'élève
        $success = db_execute(
            "UPDATE eleves SET statut_etudiant = :statut WHERE eleve_id = :eleve_id",
            ['statut' => ELEVE_ACTIF, 'eleve_id' => $admission['eleve_id']]
        );
        
        if (!$success) {
            throw new Exception("Échec de la mise à jour du statut de l'élève");
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        log_action('Admission approuvée', [
            'admission_id' => $admission_id,
            'eleve_id' => $admission['eleve_id'],
            'eleve_nom' => $admission['eleve_nom_complet'],
            'class_id' => $admission['class_id'],
            'classe' => $admission['classe_libelle'],
            'type_decision' => $type_decision,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'admissions');
        
        return [
            'success' => true,
            'message' => 'Admission approuvée avec succès'
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur admission_approuver: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'approbation'];
    }
}

/**
 * Rejeter une admission
 */
function admission_rejeter(int $admission_id, string $motif = ''): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'admission existe
    $admission = admission_get_by_id($admission_id);
    if (!$admission) {
        return ['success' => false, 'error' => 'Admission non trouvée'];
    }
    
    // Vérifier que l'admission n'est pas déjà rejetée
    if ($admission['statut_admission'] === ADMISSION_REJETE) {
        return ['success' => false, 'error' => 'Admission déjà rejetée'];
    }
    
    try {
        // Mettre à jour l'admission
        $sql = "UPDATE admissions 
                SET statut_admission = :statut, 
                    decision_comite = :motif,
                    date_decision = :date_decision
                WHERE admission_id = :admission_id";
        
        $success = db_execute($sql, [
            'statut' => ADMISSION_REJETE,
            'motif' => $motif,
            'date_decision' => date('Y-m-d'),
            'admission_id' => $admission_id
        ]);
        
        if ($success) {
            // Si l'admission était approuvée, remettre l'élève en attente
            if ($admission['statut_admission'] === ADMISSION_APPROUVE) {
                db_execute(
                    "UPDATE eleves SET statut_etudiant = :statut WHERE eleve_id = :eleve_id",
                    ['statut' => ELEVE_EN_ATTENTE, 'eleve_id' => $admission['eleve_id']]
                );
            }
            
            // Journaliser l'action
            log_action('Admission rejetée', [
                'admission_id' => $admission_id,
                'eleve_id' => $admission['eleve_id'],
                'motif' => $motif,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'admissions');
            
            return [
                'success' => true,
                'message' => 'Admission rejetée avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors du rejet'];
        
    } catch (Exception $e) {
        error_log("Erreur admission_rejeter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Mettre une admission en liste d'attente
 */
function admission_liste_attente(int $admission_id, string $raison = ''): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'admission existe
    $admission = admission_get_by_id($admission_id);
    if (!$admission) {
        return ['success' => false, 'error' => 'Admission non trouvée'];
    }
    
    // Vérifier que l'admission n'est pas déjà en liste d'attente
    if ($admission['statut_admission'] === ADMISSION_LISTE_ATTENTE) {
        return ['success' => false, 'error' => 'Admission déjà en liste d\'attente'];
    }
    
    try {
        // Mettre à jour l'admission
        $sql = "UPDATE admissions 
                SET statut_admission = :statut, 
                    decision_comite = :raison,
                    date_decision = :date_decision
                WHERE admission_id = :admission_id";
        
        $success = db_execute($sql, [
            'statut' => ADMISSION_LISTE_ATTENTE,
            'raison' => $raison,
            'date_decision' => date('Y-m-d'),
            'admission_id' => $admission_id
        ]);
        
        if ($success) {
            // Si l'admission était approuvée, remettre l'élève en attente
            if ($admission['statut_admission'] === ADMISSION_APPROUVE) {
                db_execute(
                    "UPDATE eleves SET statut_etudiant = :statut WHERE eleve_id = :eleve_id",
                    ['statut' => ELEVE_EN_ATTENTE, 'eleve_id' => $admission['eleve_id']]
                );
            }
            
            // Journaliser l'action
            log_action('Admission mise en liste d\'attente', [
                'admission_id' => $admission_id,
                'eleve_id' => $admission['eleve_id'],
                'raison' => $raison,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'admissions');
            
            return [
                'success' => true,
                'message' => 'Admission mise en liste d\'attente'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise en liste d\'attente'];
        
    } catch (Exception $e) {
        error_log("Erreur admission_liste_attente: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE GESTION DE LA CAPACITÉ
// =============================================

/**
 * Vérifier la capacité d'une classe
 */
function verifier_capacite_classe(int $class_id, int $exclude_admission_id = 0): array
{
    $classe = db_query_single(
        "SELECT c.capacite_max, 
                (SELECT COUNT(*) FROM admissions a 
                 WHERE a.class_id = c.class_id 
                 AND a.statut_admission = :statut_approuve
                 AND a.admission_id != :exclude_admission_id) as nombre_eleves
         FROM classes c 
         WHERE c.class_id = :class_id",
        [
            'class_id' => $class_id,
            'statut_approuve' => ADMISSION_APPROUVE,
            'exclude_admission_id' => $exclude_admission_id
        ]
    );
    
    if (!$classe) {
        return [
            'disponible' => false,
            'places_disponibles' => 0,
            'message' => 'Classe non trouvée'
        ];
    }
    
    $capacite_max = (int)$classe['capacite_max'];
    $nombre_eleves = (int)$classe['nombre_eleves'];
    $places_disponibles = $capacite_max - $nombre_eleves;
    
    return [
        'disponible' => $capacite_max === 0 || $places_disponibles > 0,
        'capacite_max' => $capacite_max,
        'nombre_eleves' => $nombre_eleves,
        'places_disponibles' => $places_disponibles,
        'message' => $capacite_max === 0 ? 
            'Capacité illimitée' : 
            "{$places_disponibles} place(s) disponible(s) sur {$capacite_max}"
    ];
}

/**
 * Obtenir les statistiques de capacité par classe
 */
function admission_get_statistiques_capacite(int $annee_id): array
{
    $classes = db_query(
        "SELECT c.class_id, c.libelle, c.capacite_max,
                n.nom_niveau,
                s.nom_section,
                COUNT(a.admission_id) as nombre_admissions,
                SUM(CASE WHEN a.statut_admission = :approuve THEN 1 ELSE 0 END) as nombre_approuves,
                SUM(CASE WHEN a.statut_admission = :en_attente THEN 1 ELSE 0 END) as nombre_en_attente,
                SUM(CASE WHEN a.statut_admission = :rejete THEN 1 ELSE 0 END) as nombre_rejetes,
                SUM(CASE WHEN a.statut_admission = :liste_attente THEN 1 ELSE 0 END) as nombre_liste_attente
         FROM classes c
         JOIN niveau n ON c.niveau_id = n.niveau_id
         LEFT JOIN sections s ON c.section_id = s.section_id
         LEFT JOIN admissions a ON c.class_id = a.class_id AND a.annee_id = :annee_id
         WHERE c.annee_id = :annee_id2
         GROUP BY c.class_id, c.libelle, c.capacite_max, n.nom_niveau, s.nom_section
         ORDER BY n.nom_niveau, c.libelle",
        [
            'annee_id' => $annee_id,
            'annee_id2' => $annee_id,
            'approuve' => ADMISSION_APPROUVE,
            'en_attente' => ADMISSION_EN_ATTENTE,
            'rejete' => ADMISSION_REJETE,
            'liste_attente' => ADMISSION_LISTE_ATTENTE
        ]
    );
    
    // Ajouter les places disponibles
    foreach ($classes as &$classe) {
        $classe['places_disponibles'] = $classe['capacite_max'] > 0 ? 
            $classe['capacite_max'] - $classe['nombre_approuves'] : '∞';
        $classe['taux_occupation'] = $classe['capacite_max'] > 0 ? 
            round(($classe['nombre_approuves'] / $classe['capacite_max']) * 100, 1) : 0;
    }
    
    return $classes;
}

// =============================================
// FONCTIONS DE GESTION DES FRAIS
// =============================================

/**
 * Enregistrer un paiement pour une admission
 */
function admission_enregistrer_paiement(int $admission_id, float $montant, string $mode_paiement = 'Espèce', string $reference = ''): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'admission existe
    $admission = admission_get_by_id($admission_id);
    if (!$admission) {
        return ['success' => false, 'error' => 'Admission non trouvée'];
    }
    
    // Valider le montant
    if ($montant <= 0) {
        return ['success' => false, 'error' => 'Montant invalide'];
    }
    
    // Calculer le nouveau total payé
    $nouveau_total = $admission['frais_payes'] + $montant;
    
    // Vérifier que le paiement ne dépasse pas les frais d'inscription
    if ($nouveau_total > $admission['frais_inscription']) {
        $excédent = $nouveau_total - $admission['frais_inscription'];
        return [
            'success' => false,
            'error' => "Montant trop élevé. Excédent: {$excédent}"
        ];
    }
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        // Mettre à jour les frais payés
        $sql = "UPDATE admissions 
                SET frais_payes = :frais_payes 
                WHERE admission_id = :admission_id";
        
        $success = db_execute($sql, [
            'frais_payes' => $nouveau_total,
            'admission_id' => $admission_id
        ]);
        
        if (!$success) {
            throw new Exception("Échec de la mise à jour du paiement");
        }
        
        // Enregistrer le paiement dans la table paiements
        $reference_paiement = $reference ?: 'ADM-' . str_pad($admission_id, 6, '0', STR_PAD_LEFT) . '-' . date('YmdHis');
        
        $sql_paiement = "INSERT INTO paiements 
                        (reference, eleve_id, annee_id, type_frais, libelle, 
                         montant_total, montant_paye, mode_paiement, statut, caissier_id)
                        VALUES (:reference, :eleve_id, :annee_id, :type_frais, :libelle,
                                :montant_total, :montant, :mode_paiement, :statut, :caissier_id)";
        
        $success_paiement = db_execute($sql_paiement, [
            'reference' => $reference_paiement,
            'eleve_id' => $admission['eleve_id'],
            'annee_id' => $admission['annee_id'],
            'type_frais' => 'Inscription',
            'libelle' => 'Frais d\'inscription - Admission #' . $admission_id,
            'montant_total' => $admission['frais_inscription'],
            'montant' => $montant,
            'mode_paiement' => $mode_paiement,
            'statut' => $nouveau_total >= $admission['frais_inscription'] ? 'paye' : 'partiel',
            'caissier_id' => $_SESSION['user_id'] ?? null
        ]);
        
        if (!$success_paiement) {
            throw new Exception("Échec de l'enregistrement du paiement");
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        log_action('Paiement admission', [
            'admission_id' => $admission_id,
            'eleve_id' => $admission['eleve_id'],
            'montant' => $montant,
            'total_paye' => $nouveau_total,
            'reste_a_payer' => $admission['frais_inscription'] - $nouveau_total,
            'mode_paiement' => $mode_paiement,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'admissions');
        
        return [
            'success' => true,
            'message' => 'Paiement enregistré avec succès',
            'total_paye' => $nouveau_total,
            'reste_a_payer' => $admission['frais_inscription'] - $nouveau_total,
            'reference_paiement' => $reference_paiement
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur admission_enregistrer_paiement: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement du paiement'];
    }
}

/**
 * Obtenir l'historique des paiements d'une admission
 */
function admission_get_historique_paiements(int $admission_id): array
{
    $sql = "SELECT p.*, u.nom as caissier_nom, u.prenom as caissier_prenom
            FROM paiements p
            LEFT JOIN user_admins u ON p.caissier_id = u.user_id
            WHERE p.libelle LIKE :libelle_pattern
            ORDER BY p.date_creation DESC";
    
    $paiements = db_query($sql, [
        'libelle_pattern' => '%Admission #' . $admission_id . '%'
    ]);
    
    // Ajouter le nom complet du caissier
    foreach ($paiements as &$paiement) {
        $paiement['caissier_nom_complet'] = $paiement['caissier_prenom'] . ' ' . $paiement['caissier_nom'];
    }
    
    return $paiements;
}

// =============================================
// FONCTIONS DE STATISTIQUES ET RAPPORTS
// =============================================

/**
 * Obtenir les statistiques des admissions
 */
function admission_get_statistiques(int $annee_id = null): array
{
    try {
        // Si aucune année n'est spécifiée, utiliser l'année active
        if ($annee_id === null) {
            $annee_active = db_query_single(
                "SELECT annee_id FROM annees_scolaire WHERE statut = 'active' LIMIT 1"
            );
            $annee_id = $annee_active ? $annee_active['annee_id'] : 0;
        }
        
        // Statistiques générales
        $stats = db_query_single(
            "SELECT 
                COUNT(*) as total_admissions,
                SUM(CASE WHEN statut_admission = :approuve THEN 1 ELSE 0 END) as approuvees,
                SUM(CASE WHEN statut_admission = :en_attente THEN 1 ELSE 0 END) as en_attente,
                SUM(CASE WHEN statut_admission = :rejete THEN 1 ELSE 0 END) as rejetees,
                SUM(CASE WHEN statut_admission = :liste_attente THEN 1 ELSE 0 END) as liste_attente,
                SUM(frais_inscription) as total_frais,
                SUM(frais_payes) as total_paye,
                AVG(frais_inscription) as moyenne_frais
             FROM admissions
             WHERE annee_id = :annee_id",
            [
                'annee_id' => $annee_id,
                'approuve' => ADMISSION_APPROUVE,
                'en_attente' => ADMISSION_EN_ATTENTE,
                'rejete' => ADMISSION_REJETE,
                'liste_attente' => ADMISSION_LISTE_ATTENTE
            ]
        );
        
        // Admissions par mois
        $par_mois = db_query(
            "SELECT 
                DATE_FORMAT(date_admission, '%Y-%m') as mois,
                COUNT(*) as nombre_admissions,
                SUM(CASE WHEN statut_admission = :approuve THEN 1 ELSE 0 END) as approuvees,
                SUM(frais_inscription) as total_frais,
                SUM(frais_payes) as total_paye
             FROM admissions
             WHERE annee_id = :annee_id
             GROUP BY DATE_FORMAT(date_admission, '%Y-%m')
             ORDER BY mois",
            [
                'annee_id' => $annee_id,
                'approuve' => ADMISSION_APPROUVE
            ]
        );
        
        // Admissions par classe
        $par_classe = db_query(
            "SELECT 
                c.libelle as classe,
                COUNT(a.admission_id) as nombre_admissions,
                SUM(CASE WHEN a.statut_admission = :approuve THEN 1 ELSE 0 END) as approuvees
             FROM admissions a
             JOIN classes c ON a.class_id = c.class_id
             WHERE a.annee_id = :annee_id
             GROUP BY c.class_id, c.libelle
             ORDER BY c.libelle",
            [
                'annee_id' => $annee_id,
                'approuve' => ADMISSION_APPROUVE
            ]
        );
        
        // Taux d'acceptation
        $taux_acceptation = $stats['total_admissions'] > 0 ? 
            round(($stats['approuvees'] / $stats['total_admissions']) * 100, 1) : 0;
        
        // Taux de paiement
        $taux_paiement = $stats['total_frais'] > 0 ? 
            round(($stats['total_paye'] / $stats['total_frais']) * 100, 1) : 0;
        
        return [
            'success' => true,
            'statistiques' => [
                'general' => $stats,
                'par_mois' => $par_mois,
                'par_classe' => $par_classe,
                'taux_acceptation' => $taux_acceptation,
                'taux_paiement' => $taux_paiement,
                'reste_a_payer' => $stats['total_frais'] - $stats['total_paye']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erreur admission_get_statistiques: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du calcul des statistiques'];
    }
}

/**
 * Générer un rapport des admissions
 */
function admission_generer_rapport(array $filtres = []): array
{
    // Récupérer toutes les admissions selon les filtres
    $resultat = admission_rechercher($filtres, 1, 10000);
    
    if (!$resultat['success']) {
        return $resultat;
    }
    
    $admissions = $resultat['admissions'];
    
    // Calculer les totaux
    $totaux = [
        'total_admissions' => count($admissions),
        'total_frais' => 0,
        'total_paye' => 0,
        'par_statut' => []
    ];
    
    foreach ($admissions as $admission) {
        $totaux['total_frais'] += $admission['frais_inscription'];
        $totaux['total_paye'] += $admission['frais_payes'];
        
        $statut = $admission['statut_admission'];
        if (!isset($totaux['par_statut'][$statut])) {
            $totaux['par_statut'][$statut] = 0;
        }
        $totaux['par_statut'][$statut]++;
    }
    
    $totaux['reste_a_payer'] = $totaux['total_frais'] - $totaux['total_paye'];
    
    return [
        'success' => true,
        'rapport' => [
            'admissions' => $admissions,
            'totaux' => $totaux,
            'date_generation' => date('Y-m-d H:i:s'),
            'filtres_appliques' => $filtres
        ]
    ];
}

// =============================================
// FONCTIONS DE NOTIFICATION
// =============================================

/**
 * Notifier les admissions en attente de décision
 */
function admission_notifier_decisions_en_attente(): array
{
    // Récupérer les admissions en attente depuis plus de 7 jours
    $admissions_en_attente = db_query(
        "SELECT a.*, e.nom, e.prenom, e.email as eleve_email,
                p.email as parent_email, p.telephone as parent_telephone
         FROM admissions a
         JOIN eleves e ON a.eleve_id = e.eleve_id
         LEFT JOIN student_parents sp ON e.eleve_id = sp.eleve_id AND sp.est_responsable = 1
         LEFT JOIN parents p ON sp.parent_id = p.parent_id
         WHERE a.statut_admission = :en_attente
         AND DATEDIFF(CURDATE(), a.date_admission) >= 7
         AND (a.date_limite_paiement IS NULL OR a.date_limite_paiement > CURDATE())",
        ['en_attente' => ADMISSION_EN_ATTENTE]
    );
    
    $notifications = [];
    
    foreach ($admissions_en_attente as $admission) {
        // Préparer le message
        $message = "Bonjour,\n\n";
        $message .= "L'admission de {$admission['prenom']} {$admission['nom']} est toujours en attente de décision.\n";
        $message .= "Date de dépôt: " . format_date($admission['date_admission']) . "\n";
        $message .= "Merci de traiter cette admission dans les plus brefs délais.\n\n";
        $message .= "Cordialement,\nService des Admissions";
        
        // Enregistrer la notification
        $notifications[] = [
            'admission_id' => $admission['admission_id'],
            'eleve_nom' => $admission['prenom'] . ' ' . $admission['nom'],
            'destinataires' => [
                'email' => $admission['parent_email'],
                'telephone' => $admission['parent_telephone']
            ],
            'message' => $message,
            'date_notification' => date('Y-m-d H:i:s')
        ];
        
        // Journaliser
        log_action('Notification admission en attente', [
            'admission_id' => $admission['admission_id'],
            'eleve_id' => $admission['eleve_id'],
            'jours_attente' => date_diff(
                new DateTime($admission['date_admission']),
                new DateTime()
            )->days
        ], 'admissions');
    }
    
    return [
        'success' => true,
        'notifications' => $notifications,
        'nombre' => count($notifications),
        'message' => count($notifications) . ' notification(s) préparée(s)'
    ];
}

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Journaliser le chargement du module
log_action('Module admissions chargé', ['version' => '1.0.0']);