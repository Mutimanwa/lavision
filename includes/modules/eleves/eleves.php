<?php
/**
 * Gestion des élèves - Module principal
 * Fonctions CRUD pour la gestion des profils élèves
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';

// =============================================
// CONSTANTES SPÉCIFIQUES AUX ÉLÈVES
// =============================================

// Statuts possibles pour un élève
define('ELEVE_EN_ATTENTE', 'en_attente');
define('ELEVE_ACTIF', 'actif');
define('ELEVE_SUSPENDU', 'suspendu');
define('ELEVE_DESISTE', 'desiste');

// Genres
define('GENRE_MASCULIN', 'M');
define('GENRE_FEMININ', 'F');
define('GENRE_AUTRE', 'Autre');

// Groupes sanguins
$GROUPES_SANGUINS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'un élève
 */
function valider_donnees_eleve(array $donnees, bool $est_modification = false): array
{
    $erreurs = [];
    
    // Champs obligatoires
    $champs_requis = ['nom', 'post_nom', 'prenom', 'date_naissance', 'genre'];
    
    foreach ($champs_requis as $champ) {
        if (empty(trim($donnees[$champ] ?? ''))) {
            $erreurs[$champ] = "Ce champ est requis";
        }
    }
    
    // Validation spécifique
    if (!empty($donnees['date_naissance'])) {
        // Valider le format de date
        $date = DateTime::createFromFormat('Y-m-d', $donnees['date_naissance']);
        if (!$date || $date->format('Y-m-d') !== $donnees['date_naissance']) {
            $erreurs['date_naissance'] = "Format de date invalide (YYYY-MM-DD requis)";
        } else {
            // Calculer l'âge
            $age = (new DateTime())->diff($date)->y;
            
            // Vérifier l'âge minimum (5 ans) et maximum (25 ans)
            if ($age < 5) {
                $erreurs['date_naissance'] = "L'élève doit avoir au moins 5 ans";
            } elseif ($age > 25) {
                $erreurs['date_naissance'] = "L'élève ne peut pas avoir plus de 25 ans";
            }
        }
    }
    
    // Validation du genre
    if (!empty($donnees['genre']) && !in_array($donnees['genre'], [GENRE_MASCULIN, GENRE_FEMININ, GENRE_AUTRE])) {
        $erreurs['genre'] = "Genre invalide";
    }
    
    // Validation de l'email
    if (!empty($donnees['email'])) {
        if (!is_valid_email($donnees['email'])) {
            $erreurs['email'] = "Adresse email invalide";
        } elseif (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = "Format d'email invalide";
        }
    }
    
    // Validation du téléphone
    if (!empty($donnees['telephone'])) {
        $telephone = preg_replace('/[^0-9]/', '', $donnees['telephone']);
        if (strlen($telephone) < 9 || strlen($telephone) > 15) {
            $erreurs['telephone'] = "Numéro de téléphone invalide";
        }
    }
    
    // Validation du groupe sanguin
    if (!empty($donnees['groupe_sanguin'])) {
        global $GROUPES_SANGUINS;
        if (!in_array($donnees['groupe_sanguin'], $GROUPES_SANGUINS)) {
            $erreurs['groupe_sanguin'] = "Groupe sanguin invalide";
        }
    }
    
    // Validation du matricule (si fourni)
    if (!$est_modification && !empty($donnees['matricule'])) {
        if (eleve_matricule_existe($donnees['matricule'])) {
            $erreurs['matricule'] = "Ce matricule existe déjà";
        }
    }
    
    // Validation des allergies (limite de longueur)
    if (!empty($donnees['allergies']) && strlen($donnees['allergies']) > 500) {
        $erreurs['allergies'] = "Les informations d'allergies sont trop longues (max 500 caractères)";
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - ÉLÈVES
// =============================================

/**
 * Créer un nouvel élève
 */
function eleve_creer(array $donnees): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'create')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = valider_donnees_eleve($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Démarrer la transaction
    db_begin_transaction();
    
    try {
        // Générer un matricule unique si non fourni
        $matricule = $donnees['matricule'] ?? generer_matricule_eleve();
        
        // Préparer les données pour l'insertion
        $champs = [
            'matricule' => $matricule,
            'nom' => trim($donnees['nom']),
            'post_nom' => trim($donnees['post_nom']),
            'prenom' => trim($donnees['prenom']),
            'date_naissance' => $donnees['date_naissance'],
            'lieu_naissance' => trim($donnees['lieu_naissance'] ?? ''),
            'genre' => $donnees['genre'],
            'nationalite' => trim($donnees['nationalite'] ?? 'Congolaise'),
            'telephone' => trim($donnees['telephone'] ?? ''),
            'email' => trim(strtolower($donnees['email'] ?? '')),
            'adresse' => trim($donnees['adresse'] ?? ''),
            'groupe_sanguin' => trim($donnees['groupe_sanguin'] ?? ''),
            'allergies' => trim($donnees['allergies'] ?? ''),
            'statut_etudiant' => ELEVE_EN_ATTENTE,
            'date_inscription' => date('Y-m-d')
        ];
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO eleves ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $eleve_id = db_last_insert_id();
        
        // Gérer les parents si fournis
        if (!empty($donnees['parents']) && is_array($donnees['parents'])) {
            foreach ($donnees['parents'] as $parent_data) {
                $resultat_parent = eleve_ajouter_parent($eleve_id, $parent_data);
                if (!$resultat_parent['success']) {
                    throw new Exception("Erreur avec le parent: " . ($resultat_parent['error'] ?? 'Inconnue'));
                }
            }
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        log_action('Élève créé', [
            'eleve_id' => $eleve_id,
            'matricule' => $matricule,
            'nom_complet' => $champs['prenom'] . ' ' . $champs['nom'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'eleves');
        
        return [
            'success' => true,
            'eleve_id' => $eleve_id,
            'matricule' => $matricule,
            'message' => 'Élève créé avec succès'
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur eleve_creer: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour un élève existant
 */
function eleve_modifier(int $eleve_id, array $donnees): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'élève existe
    $eleve_existant = eleve_get_by_id($eleve_id);
    if (!$eleve_existant) {
        return ['success' => false, 'error' => 'Élève non trouvé'];
    }
    
    // Valider les données (mode modification)
    $erreurs = valider_donnees_eleve($donnees, true);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['eleve_id' => $eleve_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'nom', 'post_nom', 'prenom', 'date_naissance', 'lieu_naissance',
            'genre', 'nationalite', 'telephone', 'email', 'adresse',
            'groupe_sanguin', 'allergies', 'statut_etudiant'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                $params[$champ] = trim($donnees[$champ]);
            }
        }
        
        // Si email modifié, vérifier l'unicité
        if (isset($donnees['email']) && !empty($donnees['email'])) {
            $email = strtolower(trim($donnees['email']));
            if ($email !== $eleve_existant['email']) {
                if (eleve_email_existe($email, $eleve_id)) {
                    return ['success' => false, 'error' => 'Cet email est déjà utilisé par un autre élève'];
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Ajouter la date de modification
        $updates[] = "date_modif = NOW()";
        
        // Construction de la requête
        $sql = "UPDATE eleves SET " . implode(', ', $updates) . " WHERE eleve_id = :eleve_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Élève modifié', [
                'eleve_id' => $eleve_id,
                'matricule' => $eleve_existant['matricule'],
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'eleves');
            
            return [
                'success' => true,
                'message' => 'Élève mis à jour avec succès',
                'eleve_id' => $eleve_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur eleve_modifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Supprimer un élève (soft delete)
 */
function eleve_supprimer(int $eleve_id): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'delete')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'élève existe
    $eleve = eleve_get_by_id($eleve_id);
    if (!$eleve) {
        return ['success' => false, 'error' => 'Élève non trouvé'];
    }
    
    // Vérifier les dépendances (admissions actives, paiements impayés, etc.)
    $dependances = verifier_dependances_eleve($eleve_id);
    if (!empty($dependances)) {
        return [
            'success' => false,
            'error' => 'Impossible de supprimer cet élève',
            'dependances' => $dependances
        ];
    }
    
    try {
        // Soft delete: marquer comme désisté
        $sql = "UPDATE eleves 
                SET statut_etudiant = :statut, 
                    date_modif = NOW() 
                WHERE eleve_id = :eleve_id";
        
        $success = db_execute($sql, [
            'eleve_id' => $eleve_id,
            'statut' => ELEVE_DESISTE
        ]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Élève désisté (soft delete)', [
                'eleve_id' => $eleve_id,
                'matricule' => $eleve['matricule'],
                'nom_complet' => $eleve['prenom'] . ' ' . $eleve['nom'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'eleves');
            
            return [
                'success' => true,
                'message' => 'Élève marqué comme désisté'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        
    } catch (Exception $e) {
        error_log("Erreur eleve_supprimer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Vérifier les dépendances avant suppression
 */
function verifier_dependances_eleve(int $eleve_id): array
{
    $dependances = [];
    
    // Vérifier les admissions actives
    $admissions = db_query_single(
        "SELECT COUNT(*) as count 
         FROM admissions 
         WHERE eleve_id = :eleve_id 
         AND statut_admission = 'approuve'",
        ['eleve_id' => $eleve_id]
    );
    
    if ($admissions && $admissions['count'] > 0) {
        $dependances[] = "Admission(s) active(s): {$admissions['count']}";
    }
    
    // Vérifier les paiements impayés
    $paiements = db_query_single(
        "SELECT COUNT(*) as count 
         FROM paiements 
         WHERE eleve_id = :eleve_id 
         AND statut IN ('impaye', 'partiel')",
        ['eleve_id' => $eleve_id]
    );
    
    if ($paiements && $paiements['count'] > 0) {
        $dependances[] = "Paiement(s) impayé(s): {$paiements['count']}";
    }
    
    // Vérifier les notes enregistrées
    $notes = db_query_single(
        "SELECT COUNT(*) as count FROM notes WHERE eleve_id = :eleve_id",
        ['eleve_id' => $eleve_id]
    );
    
    if ($notes && $notes['count'] > 0) {
        $dependances[] = "Note(s) enregistrée(s): {$notes['count']}";
    }
    
    return $dependances;
}

// =============================================
// FONCTIONS DE RECHERCHE ET FILTRAGE
// =============================================

/**
 * Rechercher des élèves avec filtres
 */
function eleve_rechercher(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS e.* FROM eleves e WHERE 1=1";
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
        $sql .= " AND e.statut_etudiant = :statut";
        $params['statut'] = $filtres['statut'];
    }
    
    // Filtre par genre
    if (!empty($filtres['genre'])) {
        $sql .= " AND e.genre = :genre";
        $params['genre'] = $filtres['genre'];
    }
    
    // Filtre par année d'inscription
    if (!empty($filtres['annee_inscription'])) {
        $sql .= " AND YEAR(e.date_inscription) = :annee_inscription";
        $params['annee_inscription'] = $filtres['annee_inscription'];
    }
    
    // Filtre par classe (via admissions)
    if (!empty($filtres['class_id'])) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM admissions a 
            WHERE a.eleve_id = e.eleve_id 
            AND a.class_id = :class_id
            AND a.statut_admission = 'approuve'
        )";
        $params['class_id'] = $filtres['class_id'];
    }
    
    // Exclure les élèves désistés par défaut
    if (!isset($filtres['inclure_desistes']) || !$filtres['inclure_desistes']) {
        $sql .= " AND e.statut_etudiant != '" . ELEVE_DESISTE . "'";
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'nom';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY e.{$order_by} {$order_dir}, e.prenom {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $eleves = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        return [
            'success' => true,
            'eleves' => $eleves,
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
        error_log("Erreur eleve_rechercher: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

/**
 * Obtenir un élève par son ID
 */
function eleve_get_by_id(int $eleve_id): ?array
{
    $sql = "SELECT e.* FROM eleves e WHERE e.eleve_id = :eleve_id";
    
    $eleve = db_query_single($sql, ['eleve_id' => $eleve_id]);
    
    if ($eleve) {
        // Ajouter l'âge calculé
        $date_naissance = new DateTime($eleve['date_naissance']);
        $aujourdhui = new DateTime();
        $eleve['age'] = $aujourdhui->diff($date_naissance)->y;
        
        // Ajouter le nom complet
        $eleve['nom_complet'] = $eleve['prenom'] . ' ' . $eleve['nom'] . ' ' . $eleve['post_nom'];
        
        // Ajouter les parents
        $eleve['parents'] = eleve_get_parents($eleve_id);
        
        // Ajouter l'admission active
        $eleve['admission_actuelle'] = eleve_get_admission_actuelle($eleve_id);
    }
    
    return $eleve;
}

/**
 * Obtenir un élève par son matricule
 */
function eleve_get_by_matricule(string $matricule): ?array
{
    $sql = "SELECT e.* FROM eleves e WHERE e.matricule = :matricule";
    
    $eleve = db_query_single($sql, ['matricule' => $matricule]);
    
    if ($eleve) {
        // Ajouter l'âge calculé
        $date_naissance = new DateTime($eleve['date_naissance']);
        $aujourdhui = new DateTime();
        $eleve['age'] = $aujourdhui->diff($date_naissance)->y;
        
        // Ajouter le nom complet
        $eleve['nom_complet'] = $eleve['prenom'] . ' ' . $eleve['nom'] . ' ' . $eleve['post_nom'];
    }
    
    return $eleve;
}

/**
 * Vérifier si un matricule existe
 */
function eleve_matricule_existe(string $matricule, int $exclude_id = 0): bool
{
    $sql = "SELECT COUNT(*) as count 
            FROM eleves 
            WHERE matricule = :matricule";
    
    $params = ['matricule' => $matricule];
    
    if ($exclude_id > 0) {
        $sql .= " AND eleve_id != :exclude_id";
        $params['exclude_id'] = $exclude_id;
    }
    
    $result = db_query_single($sql, $params);
    return $result && $result['count'] > 0;
}

/**
 * Vérifier si un email existe
 */
function eleve_email_existe(string $email, int $exclude_id = 0): bool
{
    $sql = "SELECT COUNT(*) as count 
            FROM eleves 
            WHERE email = :email 
            AND email IS NOT NULL 
            AND email != ''";
    
    $params = ['email' => strtolower(trim($email))];
    
    if ($exclude_id > 0) {
        $sql .= " AND eleve_id != :exclude_id";
        $params['exclude_id'] = $exclude_id;
    }
    
    $result = db_query_single($sql, $params);
    return $result && $result['count'] > 0;
}

// =============================================
// FONCTIONS DE GÉNÉRATION ET UTILITAIRES
// =============================================

/**
 * Générer un matricule unique pour un élève
 */
function generer_matricule_eleve(): string
{
    // Format: ECO-YYYY-NNNNN (ECO-2024-00001)
    $prefixe = "ECO";
    $annee = date('Y');
    $compteur = 1;
    
    // Récupérer le dernier matricule de l'année
    $dernier_matricule = db_query_single(
        "SELECT matricule FROM eleves 
         WHERE matricule LIKE :pattern 
         ORDER BY eleve_id DESC LIMIT 1",
        ['pattern' => "{$prefixe}-{$annee}-%"]
    );
    
    if ($dernier_matricule && preg_match('/-(\d+)$/', $dernier_matricule['matricule'], $matches)) {
        $compteur = (int)$matches[1] + 1;
    }
    
    return sprintf("%s-%s-%05d", $prefixe, $annee, $compteur);
}

/**
 * Obtenir les statistiques des élèves
 */
function eleve_get_statistiques(): array
{
    $stats = [];
    
    try {
        // Nombre total d'élèves par statut
        $statuts = db_query(
            "SELECT statut_etudiant, COUNT(*) as count 
             FROM eleves 
             GROUP BY statut_etudiant 
             ORDER BY statut_etudiant"
        );
        
        $stats['par_statut'] = [];
        foreach ($statuts as $statut) {
            $stats['par_statut'][$statut['statut_etudiant']] = $statut['count'];
        }
        
        // Nombre d'élèves par genre
        $genres = db_query(
            "SELECT genre, COUNT(*) as count 
             FROM eleves 
             WHERE statut_etudiant != :desiste
             GROUP BY genre",
            ['desiste' => ELEVE_DESISTE]
        );
        
        $stats['par_genre'] = [];
        foreach ($genres as $genre) {
            $stats['par_genre'][$genre['genre']] = $genre['count'];
        }
        
        // Nombre d'élèves par année d'inscription
        $annees = db_query(
            "SELECT YEAR(date_inscription) as annee, COUNT(*) as count 
             FROM eleves 
             WHERE date_inscription IS NOT NULL
             GROUP BY YEAR(date_inscription) 
             ORDER BY annee DESC 
             LIMIT 5"
        );
        
        $stats['par_annee'] = $annees;
        
        // Âge moyen des élèves
        $age_moyen = db_query_single(
            "SELECT AVG(TIMESTAMPDIFF(YEAR, date_naissance, CURDATE())) as age_moyen 
             FROM eleves 
             WHERE statut_etudiant = :actif",
            ['actif' => ELEVE_ACTIF]
        );
        
        $stats['age_moyen'] = round($age_moyen['age_moyen'] ?? 0, 1);
        
        // Derniers élèves inscrits
        $derniers = db_query(
            "SELECT eleve_id, matricule, nom, prenom, date_inscription 
             FROM eleves 
             WHERE date_inscription IS NOT NULL 
             ORDER BY date_inscription DESC 
             LIMIT 10"
        );
        
        $stats['derniers_inscrits'] = $derniers;
        
        // Nombre total d'élèves actifs
        $total_actifs = db_query_single(
            "SELECT COUNT(*) as count FROM eleves WHERE statut_etudiant = :actif",
            ['actif' => ELEVE_ACTIF]
        );
        
        $stats['total_actifs'] = $total_actifs['count'] ?? 0;
        
        return [
            'success' => true,
            'statistiques' => $stats
        ];
        
    } catch (Exception $e) {
        error_log("Erreur eleve_get_statistiques: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du calcul des statistiques'];
    }
}

/**
 * Exporter la liste des élèves
 */
function eleve_exporter(array $filtres = [], string $format = 'csv'): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        // Récupérer tous les élèves selon les filtres
        $resultat = eleve_rechercher($filtres, 1, 10000); // 10000 max pour l'export
        
        if (!$resultat['success']) {
            return $resultat;
        }
        
        $eleves = $resultat['eleves'];
        
        // Préparer les données selon le format
        switch (strtolower($format)) {
            case 'csv':
                $export = eleve_generate_csv($eleves);
                $extension = 'csv';
                $mime_type = 'text/csv';
                break;
                
            case 'excel':
                $export = eleve_generate_excel($eleves);
                $extension = 'xlsx';
                $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
                
            case 'pdf':
                $export = eleve_generate_pdf($eleves);
                $extension = 'pdf';
                $mime_type = 'application/pdf';
                break;
                
            default:
                return ['success' => false, 'error' => 'Format non supporté'];
        }
        
        // Journaliser l'export
        log_action('Export élèves', [
            'format' => $format,
            'nombre' => count($eleves),
            'filtres' => $filtres,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'eleves');
        
        return [
            'success' => true,
            'data' => $export,
            'format' => $format,
            'extension' => $extension,
            'mime_type' => $mime_type,
            'filename' => 'eleves_' . date('Ymd_His') . '.' . $extension,
            'count' => count($eleves)
        ];
        
    } catch (Exception $e) {
        error_log("Erreur eleve_exporter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'export'];
    }
}

/**
 * Générer un CSV des élèves
 */
function eleve_generate_csv(array $eleves): string
{
    $output = fopen('php://temp', 'r+');
    
    // En-têtes
    $headers = [
        'Matricule', 'Nom', 'Post-nom', 'Prénom', 'Date Naissance', 'Âge',
        'Genre', 'Nationalité', 'Téléphone', 'Email', 'Adresse',
        'Groupe Sanguin', 'Allergies', 'Statut', 'Date Inscription'
    ];
    
    fputcsv($output, $headers, ';');
    
    // Données
    foreach ($eleves as $eleve) {
        $row = [
            $eleve['matricule'],
            $eleve['nom'],
            $eleve['post_nom'],
            $eleve['prenom'],
            format_date($eleve['date_naissance']),
            (new DateTime())->diff(new DateTime($eleve['date_naissance']))->y,
            $eleve['genre'],
            $eleve['nationalite'],
            $eleve['telephone'],
            $eleve['email'],
            $eleve['adresse'],
            $eleve['groupe_sanguin'],
            $eleve['allergies'],
            $eleve['statut_etudiant'],
            format_date($eleve['date_inscription'] ?? '')
        ];
        
        fputcsv($output, $row, ';');
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Générer un Excel des élèves (simplifié)
 */
function eleve_generate_excel(array $eleves): string
{
    // En production, utiliser une bibliothèque comme PhpSpreadsheet
    // Ici, on génère un CSV formaté pour Excel
    return eleve_generate_csv($eleves);
}

/**
 * Générer un PDF des élèves (simplifié)
 */
function eleve_generate_pdf(array $eleves): string
{
    // En production, utiliser une bibliothèque comme TCPDF ou Dompdf
    // Ici, on génère un HTML simple
    
    $html = '<html><head><meta charset="UTF-8"><title>Liste des Élèves</title>';
    $html .= '<style>body{font-family:Arial;margin:20px}';
    $html .= 'table{border-collapse:collapse;width:100%}';
    $html .= 'th,td{border:1px solid #ddd;padding:8px;text-align:left}';
    $html .= 'th{background-color:#f2f2f2}</style></head><body>';
    
    $html .= '<h1>Liste des Élèves</h1>';
    $html .= '<p>Généré le ' . date('d/m/Y à H:i') . '</p>';
    $html .= '<p>Total: ' . count($eleves) . ' élève(s)</p>';
    
    $html .= '<table>';
    $html .= '<tr><th>Matricule</th><th>Nom</th><th>Prénom</th><th>Date Naiss.</th>';
    $html .= '<th>Genre</th><th>Téléphone</th><th>Statut</th></tr>';
    
    foreach ($eleves as $eleve) {
        $html .= '<tr>';
        $html .= '<td>' . e($eleve['matricule']) . '</td>';
        $html .= '<td>' . e($eleve['nom']) . ' ' . e($eleve['post_nom']) . '</td>';
        $html .= '<td>' . e($eleve['prenom']) . '</td>';
        $html .= '<td>' . format_date($eleve['date_naissance']) . '</td>';
        $html .= '<td>' . e($eleve['genre']) . '</td>';
        $html .= '<td>' . e($eleve['telephone']) . '</td>';
        $html .= '<td>' . e($eleve['statut_etudiant']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table></body></html>';
    
    return $html;
}

// =============================================
// FONCTIONS DE GESTION DES PARENTS
// =============================================

/**
 * Ajouter un parent à un élève
 */
function eleve_ajouter_parent(int $eleve_id, array $parent_data): array
{
    require_once __DIR__ . '/parents.php';
    
    // Valider les données du parent
    $erreurs = parent_valider_donnees($parent_data);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Démarrer la transaction
    db_begin_transaction();
    
    try {
        // Vérifier si le parent existe déjà (par téléphone)
        $parent_existant = null;
        if (!empty($parent_data['telephone'])) {
            $parent_existant = parent_get_by_telephone($parent_data['telephone']);
        }
        
        $parent_id = null;
        
        if ($parent_existant) {
            // Utiliser le parent existant
            $parent_id = $parent_existant['parent_id'];
        } else {
            // Créer un nouveau parent
            $resultat_parent = parent_creer($parent_data);
            if (!$resultat_parent['success']) {
                throw new Exception("Erreur création parent: " . ($resultat_parent['error'] ?? ''));
            }
            $parent_id = $resultat_parent['parent_id'];
        }
        
        // Associer le parent à l'élève
        $sql = "INSERT INTO student_parents (eleve_id, parent_id, relation, est_responsable) 
                VALUES (:eleve_id, :parent_id, :relation, :est_responsable) 
                ON DUPLICATE KEY UPDATE relation = :relation, est_responsable = :est_responsable";
        
        $success = db_execute($sql, [
            'eleve_id' => $eleve_id,
            'parent_id' => $parent_id,
            'relation' => $parent_data['relation'] ?? 'parent',
            'est_responsable' => $parent_data['est_responsable'] ?? false ? 1 : 0
        ]);
        
        if (!$success) {
            throw new Exception("Erreur association parent-élève");
        }
        
        db_commit();
        
        log_action('Parent associé à élève', [
            'eleve_id' => $eleve_id,
            'parent_id' => $parent_id,
            'relation' => $parent_data['relation'] ?? 'parent',
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'eleves');
        
        return [
            'success' => true,
            'parent_id' => $parent_id,
            'message' => 'Parent ajouté avec succès'
        ];
        
    } catch (Exception $e) {
        db_rollback();
        error_log("Erreur eleve_ajouter_parent: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Obtenir les parents d'un élève
 */
function eleve_get_parents(int $eleve_id): array
{
    $sql = "SELECT p.*, sp.relation, sp.est_responsable 
            FROM student_parents sp 
            JOIN parents p ON sp.parent_id = p.parent_id 
            WHERE sp.eleve_id = :eleve_id 
            ORDER BY sp.est_responsable DESC, p.nom";
    
    return db_query($sql, ['eleve_id' => $eleve_id]);
}

/**
 * Supprimer un parent d'un élève
 */
function eleve_supprimer_parent(int $eleve_id, int $parent_id): array
{
    $sql = "DELETE FROM student_parents 
            WHERE eleve_id = :eleve_id 
            AND parent_id = :parent_id";
    
    $success = db_execute($sql, [
        'eleve_id' => $eleve_id,
        'parent_id' => $parent_id
    ]);
    
    if ($success) {
        log_action('Parent dissocié de élève', [
            'eleve_id' => $eleve_id,
            'parent_id' => $parent_id,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'eleves');
        
        return ['success' => true, 'message' => 'Parent retiré avec succès'];
    }
    
    return ['success' => false, 'error' => 'Erreur lors de la dissociation'];
}

// =============================================
// FONCTIONS D'ADMISSION (LIEN AVEC ADMISSIONS.PHP)
// =============================================

/**
 * Obtenir l'admission actuelle d'un élève
 */
function eleve_get_admission_actuelle(int $eleve_id): ?array
{
    require_once __DIR__ . '/admissions.php';
    return admission_get_actuelle($eleve_id);
}

/**
 * Obtenir l'historique des admissions d'un élève
 */
function eleve_get_admissions_historique(int $eleve_id): array
{
    require_once __DIR__ . '/admissions.php';
    return admission_get_historique($eleve_id);
}

// =============================================
// FONCTIONS DE MISE À JOUR EN MASSE
// =============================================

/**
 * Mettre à jour le statut de plusieurs élèves
 */
function eleve_mass_update_statut(array $eleve_ids, string $nouveau_statut): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider le statut
    $statuts_valides = [ELEVE_EN_ATTENTE, ELEVE_ACTIF, ELEVE_SUSPENDU, ELEVE_DESISTE];
    if (!in_array($nouveau_statut, $statuts_valides)) {
        return ['success' => false, 'error' => 'Statut invalide'];
    }
    
    // Valider la liste des IDs
    if (empty($eleve_ids)) {
        return ['success' => false, 'error' => 'Aucun élève sélectionné'];
    }
    
    // Limiter à 1000 mises à jour à la fois
    if (count($eleve_ids) > 1000) {
        return ['success' => false, 'error' => 'Trop d\'élèves sélectionnés (max 1000)'];
    }
    
    try {
        // Préparer les placeholders pour la requête IN
        $placeholders = implode(',', array_fill(0, count($eleve_ids), '?'));
        
        // Mettre à jour en masse
        $sql = "UPDATE eleves 
                SET statut_etudiant = ?, date_modif = NOW() 
                WHERE eleve_id IN ({$placeholders})";
        
        $params = array_merge([$nouveau_statut], $eleve_ids);
        $success = db_execute($sql, $params);
        
        if ($success) {
            $nombre_modifies = count($eleve_ids);
            
            log_action('Mise à jour statut masse élèves', [
                'nombre' => $nombre_modifies,
                'nouveau_statut' => $nouveau_statut,
                'eleve_ids' => $eleve_ids,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'eleves');
            
            return [
                'success' => true,
                'message' => "Statut mis à jour pour {$nombre_modifies} élève(s)",
                'nombre_modifies' => $nombre_modifies
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur eleve_mass_update_statut: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Importer des élèves depuis un fichier CSV
 */
function eleve_importer_csv(string $csv_content): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'import')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    $lignes = str_getcsv($csv_content, "\n");
    
    if (count($lignes) < 2) {
        return ['success' => false, 'error' => 'Fichier CSV vide ou invalide'];
    }
    
    // Lire les en-têtes
    $entetes = str_getcsv($lignes[0], ';');
    
    // Définir les en-têtes attendus
    $entetes_attendues = [
        'nom', 'post_nom', 'prenom', 'date_naissance', 
        'genre', 'telephone', 'email', 'adresse'
    ];
    
    $resultats = [
        'succes' => 0,
        'echecs' => 0,
        'erreurs' => [],
        'details' => []
    ];
    
    // Traiter chaque ligne
    for ($i = 1; $i < count($lignes); $i++) {
        if (empty(trim($lignes[$i]))) {
            continue;
        }
        
        $donnees_ligne = str_getcsv($lignes[$i], ';');
        
        // Assurer que chaque ligne a le même nombre de colonnes que les en-têtes
        if (count($donnees_ligne) !== count($entetes)) {
            $resultats['echecs']++;
            $resultats['erreurs'][] = "Ligne {$i}: Nombre de colonnes incorrect";
            continue;
        }
        
        // Créer un tableau associatif
        $donnees_eleve = array_combine($entetes, $donnees_ligne);
        
        // Traiter les données
        $donnees_eleve['date_naissance'] = convertir_date_import($donnees_eleve['date_naissance'] ?? '');
        $donnees_eleve['telephone'] = nettoyer_telephone($donnees_eleve['telephone'] ?? '');
        $donnees_eleve['email'] = strtolower(trim($donnees_eleve['email'] ?? ''));
        
        // Créer l'élève
        $resultat_creation = eleve_creer($donnees_eleve);
        
        if ($resultat_creation['success']) {
            $resultats['succes']++;
            $resultats['details'][] = [
                'ligne' => $i,
                'matricule' => $resultat_creation['matricule'],
                'nom_complet' => $donnees_eleve['prenom'] . ' ' . $donnees_eleve['nom']
            ];
        } else {
            $resultats['echecs']++;
            $resultats['erreurs'][] = "Ligne {$i}: " . ($resultat_creation['error'] ?? 'Erreur inconnue');
        }
    }
    
    // Journaliser l'import
    log_action('Import élèves CSV', [
        'succes' => $resultats['succes'],
        'echecs' => $resultats['echecs'],
        'total_lignes' => count($lignes) - 1,
        'par_utilisateur' => $_SESSION['user_id'] ?? null
    ], 'eleves');
    
    return [
        'success' => true,
        'resultats' => $resultats,
        'message' => "Import terminé: {$resultats['succes']} succès, {$resultats['echecs']} échecs"
    ];
}

/**
 * Convertir une date d'import
 */
function convertir_date_import(string $date): string
{
    $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d', 'm/d/Y'];
    
    foreach ($formats as $format) {
        $date_obj = DateTime::createFromFormat($format, $date);
        if ($date_obj !== false) {
            return $date_obj->format('Y-m-d');
        }
    }
    
    return '';
}

/**
 * Nettoyer un numéro de téléphone
 */
function nettoyer_telephone(string $telephone): string
{
    // Supprimer tous les caractères non numériques
    $telephone = preg_replace('/[^0-9]/', '', $telephone);
    
    // Ajouter l'indicatif par défaut si nécessaire
    if (strlen($telephone) === 9 && substr($telephone, 0, 1) !== '0') {
        $telephone = '0' . $telephone;
    }
    
    return $telephone;
}

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Journaliser le chargement du module
log_action('Module élèves chargé', ['version' => '1.0.0']);