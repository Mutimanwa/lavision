<?php
/**
 * Gestion des parents et tuteurs d'élèves
 * Liaison avec les élèves, informations de contact
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';

// =============================================
// CONSTANTES POUR LES PARENTS
// =============================================

// Types de relations
define('RELATION_PERE', 'père');
define('RELATION_MERE', 'mère');
define('RELATION_TUTEUR', 'tuteur');
define('RELATION_GRAND_PARENT', 'grand-parent');
define('RELATION_FRERE_SOEUR', 'frère/soeur');
define('RELATION_ONCLE_TANTE', 'oncle/tante');
define('RELATION_AUTRE', 'autre');

// Genres
define('PARENT_GENRE_MASCULIN', 'M');
define('PARENT_GENRE_FEMININ', 'F');

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'un parent
 */
function parent_valider_donnees(array $donnees): array
{
    $erreurs = [];
    
    // Champs obligatoires
    if (empty(trim($donnees['nom'] ?? ''))) {
        $erreurs['nom'] = "Le nom est requis";
    }
    
    if (empty(trim($donnees['prenom'] ?? ''))) {
        $erreurs['prenom'] = "Le prénom est requis";
    }
    
    // Téléphone obligatoire
    if (empty(trim($donnees['telephone'] ?? ''))) {
        $erreurs['telephone'] = "Le téléphone est requis";
    } else {
        $telephone = nettoyer_telephone($donnees['telephone']);
        if (strlen($telephone) < 9 || strlen($telephone) > 15) {
            $erreurs['telephone'] = "Numéro de téléphone invalide";
        }
    }
    
    // Validation de l'email
    if (!empty($donnees['email'])) {
        if (!is_valid_email($donnees['email'])) {
            $erreurs['email'] = "Adresse email invalide";
        }
    }
    
    // Validation du CIN (si fourni)
    if (!empty($donnees['cin']) && strlen(trim($donnees['cin'])) < 5) {
        $erreurs['cin'] = "Le CIN doit faire au moins 5 caractères";
    }
    
    // Validation de la relation
    if (!empty($donnees['relation'])) {
        $relations_valides = [
            RELATION_PERE, RELATION_MERE, RELATION_TUTEUR,
            RELATION_GRAND_PARENT, RELATION_FRERE_SOEUR,
            RELATION_ONCLE_TANTE, RELATION_AUTRE
        ];
        
        if (!in_array($donnees['relation'], $relations_valides)) {
            $erreurs['relation'] = "Relation invalide";
        }
    }
    
    // Validation du genre (si fourni)
    if (!empty($donnees['genre'])) {
        if (!in_array($donnees['genre'], [PARENT_GENRE_MASCULIN, PARENT_GENRE_FEMININ])) {
            $erreurs['genre'] = "Genre invalide";
        }
    }
    
    // Validation de la profession (limite de longueur)
    if (!empty($donnees['profession']) && strlen($donnees['profession']) > 100) {
        $erreurs['profession'] = "La profession est trop longue (max 100 caractères)";
    }
    
    // Validation de l'entreprise (limite de longueur)
    if (!empty($donnees['entreprise']) && strlen($donnees['entreprise']) > 100) {
        $erreurs['entreprise'] = "Le nom de l'entreprise est trop long (max 100 caractères)";
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - PARENTS
// =============================================

/**
 * Créer un nouveau parent
 */
function parent_creer(array $donnees): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'create')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = parent_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité du téléphone (optionnel mais recommandé)
    if (!empty($donnees['telephone'])) {
        $telephone = nettoyer_telephone($donnees['telephone']);
        $parent_existant = parent_get_by_telephone($telephone);
        
        if ($parent_existant) {
            return [
                'success' => false,
                'error' => "Un parent avec ce numéro de téléphone existe déjà",
                'parent_existant' => $parent_existant
            ];
        }
    }
    
    // Vérifier l'unicité du CIN (si fourni)
    if (!empty($donnees['cin'])) {
        $cin = trim($donnees['cin']);
        if (parent_cin_existe($cin)) {
            return [
                'success' => false,
                'error' => "Un parent avec ce CIN existe déjà"
            ];
        }
    }
    
    try {
        // Préparer les données pour l'insertion
        $champs = [
            'cin' => trim($donnees['cin'] ?? ''),
            'prenom' => trim($donnees['prenom']),
            'nom' => trim($donnees['nom']),
            'genre' => trim($donnees['genre'] ?? ''),
            'profession' => trim($donnees['profession'] ?? ''),
            'entreprise' => trim($donnees['entreprise'] ?? ''),
            'telephone' => nettoyer_telephone($donnees['telephone']),
            'telephone_bureau' => !empty($donnees['telephone_bureau']) ? 
                nettoyer_telephone($donnees['telephone_bureau']) : null,
            'email' => trim(strtolower($donnees['email'] ?? '')),
            'adresse' => trim($donnees['adresse'] ?? ''),
            'est_principal' => isset($donnees['est_principal']) && $donnees['est_principal'] ? 1 : 0
        ];
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO parents ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $parent_id = db_last_insert_id();
        
        // Journaliser l'action
        log_action('Parent créé', [
            'parent_id' => $parent_id,
            'nom_complet' => $champs['prenom'] . ' ' . $champs['nom'],
            'telephone' => $champs['telephone'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'parents');
        
        return [
            'success' => true,
            'parent_id' => $parent_id,
            'message' => 'Parent créé avec succès'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur parent_creer: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour un parent existant
 */
function parent_modifier(int $parent_id, array $donnees): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le parent existe
    $parent_existant = parent_get_by_id($parent_id);
    if (!$parent_existant) {
        return ['success' => false, 'error' => 'Parent non trouvé'];
    }
    
    // Valider les données
    $erreurs = parent_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité du téléphone (si modifié)
    if (!empty($donnees['telephone'])) {
        $telephone = nettoyer_telephone($donnees['telephone']);
        if ($telephone !== $parent_existant['telephone']) {
            $parent_avec_telephone = parent_get_by_telephone($telephone);
            if ($parent_avec_telephone && $parent_avec_telephone['parent_id'] != $parent_id) {
                return [
                    'success' => false,
                    'error' => "Un autre parent utilise déjà ce numéro de téléphone"
                ];
            }
        }
    }
    
    // Vérifier l'unicité du CIN (si modifié)
    if (!empty($donnees['cin']) && $donnees['cin'] !== $parent_existant['cin']) {
        $cin = trim($donnees['cin']);
        if (parent_cin_existe($cin, $parent_id)) {
            return [
                'success' => false,
                'error' => "Un autre parent utilise déjà ce CIN"
            ];
        }
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['parent_id' => $parent_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'cin', 'nom', 'prenom', 'genre', 'profession', 'entreprise',
            'telephone', 'telephone_bureau', 'email', 'adresse', 'est_principal'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                
                // Traitement spécifique pour les téléphones
                if ($champ === 'telephone' || $champ === 'telephone_bureau') {
                    $params[$champ] = !empty($donnees[$champ]) ? 
                        nettoyer_telephone($donnees[$champ]) : null;
                } elseif ($champ === 'email') {
                    $params[$champ] = strtolower(trim($donnees[$champ]));
                } elseif ($champ === 'est_principal') {
                    $params[$champ] = $donnees[$champ] ? 1 : 0;
                } else {
                    $params[$champ] = trim($donnees[$champ]);
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Si on définit ce parent comme principal, désactiver les autres parents principaux des mêmes élèves
        if (isset($donnees['est_principal']) && $donnees['est_principal']) {
            // Récupérer les élèves de ce parent
            $eleves_du_parent = parent_get_eleves($parent_id);
            
            foreach ($eleves_du_parent as $eleve) {
                // Désactiver les autres parents principaux pour cet élève
                db_execute(
                    "UPDATE student_parents 
                     SET est_responsable = 0 
                     WHERE eleve_id = :eleve_id 
                     AND parent_id != :parent_id",
                    ['eleve_id' => $eleve['eleve_id'], 'parent_id' => $parent_id]
                );
            }
        }
        
        // Construction de la requête
        $sql = "UPDATE parents SET " . implode(', ', $updates) . " WHERE parent_id = :parent_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Parent modifié', [
                'parent_id' => $parent_id,
                'nom_complet' => ($donnees['prenom'] ?? $parent_existant['prenom']) . ' ' . 
                               ($donnees['nom'] ?? $parent_existant['nom']),
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'parents');
            
            return [
                'success' => true,
                'message' => 'Parent mis à jour avec succès',
                'parent_id' => $parent_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur parent_modifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Supprimer un parent
 */
function parent_supprimer(int $parent_id): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'delete')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le parent existe
    $parent = parent_get_by_id($parent_id);
    if (!$parent) {
        return ['success' => false, 'error' => 'Parent non trouvé'];
    }
    
    // Vérifier s'il a des enfants associés
    $eleves_associes = parent_get_eleves($parent_id);
    
    if (!empty($eleves_associes)) {
        return [
            'success' => false,
            'error' => 'Impossible de supprimer un parent ayant des enfants associés',
            'eleves_associes' => $eleves_associes
        ];
    }
    
    try {
        // Supprimer le parent
        $sql = "DELETE FROM parents WHERE parent_id = :parent_id";
        $success = db_execute($sql, ['parent_id' => $parent_id]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Parent supprimé', [
                'parent_id' => $parent_id,
                'nom_complet' => $parent['prenom'] . ' ' . $parent['nom'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'parents');
            
            return [
                'success' => true,
                'message' => 'Parent supprimé avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        
    } catch (Exception $e) {
        error_log("Erreur parent_supprimer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir un parent par son ID
 */
function parent_get_by_id(int $parent_id): ?array
{
    $sql = "SELECT p.* FROM parents p WHERE p.parent_id = :parent_id";
    
    $parent = db_query_single($sql, ['parent_id' => $parent_id]);
    
    if ($parent) {
        // Ajouter le nom complet
        $parent['nom_complet'] = $parent['prenom'] . ' ' . $parent['nom'];
        
        // Ajouter les enfants
        $parent['eleves'] = parent_get_eleves($parent_id);
        
        // Compter le nombre d'enfants
        $parent['nombre_enfants'] = count($parent['eleves']);
    }
    
    return $parent;
}

/**
 * Obtenir un parent par son téléphone
 */
function parent_get_by_telephone(string $telephone): ?array
{
    $telephone_nettoye = nettoyer_telephone($telephone);
    
    $sql = "SELECT p.* FROM parents p WHERE p.telephone = :telephone";
    
    $parent = db_query_single($sql, ['telephone' => $telephone_nettoye]);
    
    if ($parent) {
        $parent['nom_complet'] = $parent['prenom'] . ' ' . $parent['nom'];
    }
    
    return $parent;
}

/**
 * Rechercher des parents avec filtres
 */
function parent_rechercher(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS p.* FROM parents p WHERE 1=1";
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(p.nom LIKE :{$key} OR p.prenom LIKE :{$key} OR p.telephone LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par profession
    if (!empty($filtres['profession'])) {
        $sql .= " AND p.profession LIKE :profession";
        $params['profession'] = "%{$filtres['profession']}%";
    }
    
    // Filtre par entreprise
    if (!empty($filtres['entreprise'])) {
        $sql .= " AND p.entreprise LIKE :entreprise";
        $params['entreprise'] = "%{$filtres['entreprise']}%";
    }
    
    // Filtre par parent principal
    if (isset($filtres['est_principal'])) {
        $sql .= " AND p.est_principal = :est_principal";
        $params['est_principal'] = $filtres['est_principal'] ? 1 : 0;
    }
    
    // Filtre par élève spécifique
    if (!empty($filtres['eleve_id'])) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM student_parents sp 
            WHERE sp.parent_id = p.parent_id 
            AND sp.eleve_id = :eleve_id
        )";
        $params['eleve_id'] = $filtres['eleve_id'];
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'nom';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY p.{$order_by} {$order_dir}, p.prenom {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $parents = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($parents as &$parent) {
            $parent['nom_complet'] = $parent['prenom'] . ' ' . $parent['nom'];
            
            // Récupérer le nombre d'enfants
            $nombre_enfants = db_query_single(
                "SELECT COUNT(*) as count FROM student_parents WHERE parent_id = :parent_id",
                ['parent_id' => $parent['parent_id']]
            );
            
            $parent['nombre_enfants'] = $nombre_enfants['count'] ?? 0;
        }
        
        return [
            'success' => true,
            'parents' => $parents,
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
        error_log("Erreur parent_rechercher: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

/**
 * Vérifier si un CIN existe déjà
 */
function parent_cin_existe(string $cin, int $exclude_id = 0): bool
{
    $sql = "SELECT COUNT(*) as count 
            FROM parents 
            WHERE cin = :cin 
            AND cin IS NOT NULL 
            AND cin != ''";
    
    $params = ['cin' => trim($cin)];
    
    if ($exclude_id > 0) {
        $sql .= " AND parent_id != :exclude_id";
        $params['exclude_id'] = $exclude_id;
    }
    
    $result = db_query_single($sql, $params);
    return $result && $result['count'] > 0;
}

// =============================================
// FONCTIONS DE GESTION DES RELATIONS
// =============================================

/**
 * Obtenir les élèves d'un parent
 */
function parent_get_eleves(int $parent_id): array
{
    $sql = "SELECT e.*, sp.relation, sp.est_responsable
            FROM student_parents sp
            JOIN eleves e ON sp.eleve_id = e.eleve_id
            WHERE sp.parent_id = :parent_id
            ORDER BY sp.est_responsable DESC, e.nom, e.prenom";
    
    $eleves = db_query($sql, ['parent_id' => $parent_id]);
    
    // Ajouter des informations supplémentaires
    foreach ($eleves as &$eleve) {
        $eleve['nom_complet'] = $eleve['prenom'] . ' ' . $eleve['nom'] . ' ' . $eleve['post_nom'];
        
        // Ajouter l'admission actuelle
        $admission = admission_get_actuelle($eleve['eleve_id']);
        $eleve['classe_actuelle'] = $admission ? $admission['classe_libelle'] : 'Non admis';
        $eleve['statut_eleve'] = $eleve['statut_etudiant'];
    }
    
    return $eleves;
}

/**
 * Associer un parent à un élève
 */
function parent_associer_eleve(int $parent_id, int $eleve_id, string $relation = 'parent', bool $est_responsable = false): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le parent existe
    $parent = parent_get_by_id($parent_id);
    if (!$parent) {
        return ['success' => false, 'error' => 'Parent non trouvé'];
    }
    
    // Vérifier que l'élève existe
    $eleve = eleve_get_by_id($eleve_id);
    if (!$eleve) {
        return ['success' => false, 'error' => 'Élève non trouvé'];
    }
    
    // Valider la relation
    $relations_valides = [
        RELATION_PERE, RELATION_MERE, RELATION_TUTEUR,
        RELATION_GRAND_PARENT, RELATION_FRERE_SOEUR,
        RELATION_ONCLE_TANTE, RELATION_AUTRE
    ];
    
    if (!in_array($relation, $relations_valides)) {
        return ['success' => false, 'error' => 'Relation invalide'];
    }
    
    // Vérifier si l'association existe déjà
    $association_existante = db_query_single(
        "SELECT * FROM student_parents 
         WHERE parent_id = :parent_id 
         AND eleve_id = :eleve_id",
        ['parent_id' => $parent_id, 'eleve_id' => $eleve_id]
    );
    
    if ($association_existante) {
        return [
            'success' => false,
            'error' => 'Cette association existe déjà'
        ];
    }
    
    try {
        // Si ce parent doit être responsable, désactiver les autres responsables pour cet élève
        if ($est_responsable) {
            db_execute(
                "UPDATE student_parents 
                 SET est_responsable = 0 
                 WHERE eleve_id = :eleve_id",
                ['eleve_id' => $eleve_id]
            );
        }
        
        // Créer l'association
        $sql = "INSERT INTO student_parents (parent_id, eleve_id, relation, est_responsable) 
                VALUES (:parent_id, :eleve_id, :relation, :est_responsable)";
        
        $success = db_execute($sql, [
            'parent_id' => $parent_id,
            'eleve_id' => $eleve_id,
            'relation' => $relation,
            'est_responsable' => $est_responsable ? 1 : 0
        ]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Parent associé à élève', [
                'parent_id' => $parent_id,
                'parent_nom' => $parent['prenom'] . ' ' . $parent['nom'],
                'eleve_id' => $eleve_id,
                'eleve_nom' => $eleve['prenom'] . ' ' . $eleve['nom'],
                'relation' => $relation,
                'est_responsable' => $est_responsable,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'parents');
            
            return [
                'success' => true,
                'message' => 'Parent associé à l\'élève avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de l\'association'];
        
    } catch (Exception $e) {
        error_log("Erreur parent_associer_eleve: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Modifier l'association parent-élève
 */
function parent_modifier_association(int $parent_id, int $eleve_id, array $donnees): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'association existe
    $association = db_query_single(
        "SELECT * FROM student_parents 
         WHERE parent_id = :parent_id 
         AND eleve_id = :eleve_id",
        ['parent_id' => $parent_id, 'eleve_id' => $eleve_id]
    );
    
    if (!$association) {
        return ['success' => false, 'error' => 'Association non trouvée'];
    }
    
    // Valider les données
    $updates = [];
    $params = [
        'parent_id' => $parent_id,
        'eleve_id' => $eleve_id
    ];
    
    if (isset($donnees['relation'])) {
        $relations_valides = [
            RELATION_PERE, RELATION_MERE, RELATION_TUTEUR,
            RELATION_GRAND_PARENT, RELATION_FRERE_SOEUR,
            RELATION_ONCLE_TANTE, RELATION_AUTRE
        ];
        
        if (!in_array($donnees['relation'], $relations_valides)) {
            return ['success' => false, 'error' => 'Relation invalide'];
        }
        
        $updates[] = "relation = :relation";
        $params['relation'] = $donnees['relation'];
    }
    
    if (isset($donnees['est_responsable'])) {
        $updates[] = "est_responsable = :est_responsable";
        $params['est_responsable'] = $donnees['est_responsable'] ? 1 : 0;
        
        // Si on définit comme responsable, désactiver les autres responsables pour cet élève
        if ($donnees['est_responsable']) {
            db_execute(
                "UPDATE student_parents 
                 SET est_responsable = 0 
                 WHERE eleve_id = :eleve_id 
                 AND parent_id != :parent_id",
                ['eleve_id' => $eleve_id, 'parent_id' => $parent_id]
            );
        }
    }
    
    if (empty($updates)) {
        return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
    }
    
    try {
        // Mettre à jour l'association
        $sql = "UPDATE student_parents 
                SET " . implode(', ', $updates) . " 
                WHERE parent_id = :parent_id 
                AND eleve_id = :eleve_id";
        
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            log_action('Association parent-élève modifiée', [
                'parent_id' => $parent_id,
                'eleve_id' => $eleve_id,
                'modifications' => $donnees,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'parents');
            
            return [
                'success' => true,
                'message' => 'Association modifiée avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la modification'];
        
    } catch (Exception $e) {
        error_log("Erreur parent_modifier_association: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Dissocier un parent d'un élève
 */
function parent_dissocier_eleve(int $parent_id, int $eleve_id): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'edit')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'association existe
    $association = db_query_single(
        "SELECT * FROM student_parents 
         WHERE parent_id = :parent_id 
         AND eleve_id = :eleve_id",
        ['parent_id' => $parent_id, 'eleve_id' => $eleve_id]
    );
    
    if (!$association) {
        return ['success' => false, 'error' => 'Association non trouvée'];
    }
    
    // Vérifier si c'est le seul parent de l'élève
    $nombre_parents = db_query_single(
        "SELECT COUNT(*) as count 
         FROM student_parents 
         WHERE eleve_id = :eleve_id",
        ['eleve_id' => $eleve_id]
    );
    
    if ($nombre_parents['count'] <= 1) {
        return [
            'success' => false,
            'error' => 'Impossible de dissocier le dernier parent de l\'élève'
        ];
    }
    
    try {
        // Supprimer l'association
        $sql = "DELETE FROM student_parents 
                WHERE parent_id = :parent_id 
                AND eleve_id = :eleve_id";
        
        $success = db_execute($sql, [
            'parent_id' => $parent_id,
            'eleve_id' => $eleve_id
        ]);
        
        if ($success) {
            // Si ce parent était responsable, définir un autre parent comme responsable
            if ($association['est_responsable']) {
                $autre_parent = db_query_single(
                    "SELECT parent_id 
                     FROM student_parents 
                     WHERE eleve_id = :eleve_id 
                     LIMIT 1",
                    ['eleve_id' => $eleve_id]
                );
                
                if ($autre_parent) {
                    db_execute(
                        "UPDATE student_parents 
                         SET est_responsable = 1 
                         WHERE parent_id = :parent_id 
                         AND eleve_id = :eleve_id",
                        [
                            'parent_id' => $autre_parent['parent_id'],
                            'eleve_id' => $eleve_id
                        ]
                    );
                }
            }
            
            // Journaliser l'action
            log_action('Parent dissocié de élève', [
                'parent_id' => $parent_id,
                'eleve_id' => $eleve_id,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'parents');
            
            return [
                'success' => true,
                'message' => 'Parent dissocié de l\'élève avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la dissociation'];
        
    } catch (Exception $e) {
        error_log("Erreur parent_dissocier_eleve: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE CONTACT ET COMMUNICATION
// =============================================

/**
 * Obtenir les contacts d'urgence d'un élève
 */
function parent_get_contacts_urgence(int $eleve_id): array
{
    $sql = "SELECT p.*, sp.relation, sp.est_responsable
            FROM student_parents sp
            JOIN parents p ON sp.parent_id = p.parent_id
            WHERE sp.eleve_id = :eleve_id
            ORDER BY sp.est_responsable DESC, sp.relation";
    
    $parents = db_query($sql, ['eleve_id' => $eleve_id]);
    
    // Ajouter des informations de contact
    foreach ($parents as &$parent) {
        $parent['nom_complet'] = $parent['prenom'] . ' ' . $parent['nom'];
        $parent['contacts'] = [];
        
        // Téléphone principal
        if (!empty($parent['telephone'])) {
            $parent['contacts'][] = [
                'type' => 'téléphone',
                'valeur' => $parent['telephone'],
                'principal' => true
            ];
        }
        
        // Téléphone bureau
        if (!empty($parent['telephone_bureau'])) {
            $parent['contacts'][] = [
                'type' => 'téléphone bureau',
                'valeur' => $parent['telephone_bureau'],
                'principal' => false
            ];
        }
        
        // Email
        if (!empty($parent['email'])) {
            $parent['contacts'][] = [
                'type' => 'email',
                'valeur' => $parent['email'],
                'principal' => false
            ];
        }
    }
    
    return $parents;
}

/**
 * Obtenir le parent responsable d'un élève
 */
function parent_get_responsable(int $eleve_id): ?array
{
    $sql = "SELECT p.*, sp.relation
            FROM student_parents sp
            JOIN parents p ON sp.parent_id = p.parent_id
            WHERE sp.eleve_id = :eleve_id
            AND sp.est_responsable = 1
            LIMIT 1";
    
    $parent = db_query_single($sql, ['eleve_id' => $eleve_id]);
    
    if ($parent) {
        $parent['nom_complet'] = $parent['prenom'] . ' ' . $parent['nom'];
    }
    
    return $parent;
}

/**
 * Définir un parent comme responsable
 */
function parent_set_responsable(int $parent_id, int $eleve_id): array
{
    // Vérifier que l'association existe
    $association = db_query_single(
        "SELECT * FROM student_parents 
         WHERE parent_id = :parent_id 
         AND eleve_id = :eleve_id",
        ['parent_id' => $parent_id, 'eleve_id' => $eleve_id]
    );
    
    if (!$association) {
        return ['success' => false, 'error' => 'Association parent-élève non trouvée'];
    }
    
    try {
        // Désactiver tous les responsables pour cet élève
        db_execute(
            "UPDATE student_parents 
             SET est_responsable = 0 
             WHERE eleve_id = :eleve_id",
            ['eleve_id' => $eleve_id]
        );
        
        // Définir ce parent comme responsable
        $sql = "UPDATE student_parents 
                SET est_responsable = 1 
                WHERE parent_id = :parent_id 
                AND eleve_id = :eleve_id";
        
        $success = db_execute($sql, [
            'parent_id' => $parent_id,
            'eleve_id' => $eleve_id
        ]);
        
        if ($success) {
            // Mettre à jour le flag est_principal dans la table parents
            db_execute(
                "UPDATE parents SET est_principal = 1 WHERE parent_id = :parent_id",
                ['parent_id' => $parent_id]
            );
            
            // Journaliser l'action
            log_action('Parent défini comme responsable', [
                'parent_id' => $parent_id,
                'eleve_id' => $eleve_id,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'parents');
            
            return [
                'success' => true,
                'message' => 'Parent défini comme responsable'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la modification'];
        
    } catch (Exception $e) {
        error_log("Erreur parent_set_responsable: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE STATISTIQUES
// =============================================

/**
 * Obtenir les statistiques des parents
 */
function parent_get_statistiques(): array
{
    $stats = [];
    
    try {
        // Nombre total de parents
        $total_parents = db_query_single("SELECT COUNT(*) as count FROM parents");
        $stats['total_parents'] = $total_parents['count'] ?? 0;
        
        // Parents par profession (top 10)
        $par_profession = db_query(
            "SELECT profession, COUNT(*) as count 
             FROM parents 
             WHERE profession IS NOT NULL AND profession != ''
             GROUP BY profession 
             ORDER BY count DESC 
             LIMIT 10"
        );
        $stats['par_profession'] = $par_profession;
        
        // Parents par entreprise (top 10)
        $par_entreprise = db_query(
            "SELECT entreprise, COUNT(*) as count 
             FROM parents 
             WHERE entreprise IS NOT NULL AND entreprise != ''
             GROUP BY entreprise 
             ORDER BY count DESC 
             LIMIT 10"
        );
        $stats['par_entreprise'] = $par_entreprise;
        
        // Nombre de parents responsables
        $parents_responsables = db_query_single(
            "SELECT COUNT(*) as count FROM parents WHERE est_principal = 1"
        );
        $stats['parents_responsables'] = $parents_responsables['count'] ?? 0;
        
        // Nombre moyen d'enfants par parent
        $moyenne_enfants = db_query_single(
            "SELECT AVG(enfant_count) as moyenne
             FROM (
                 SELECT parent_id, COUNT(*) as enfant_count
                 FROM student_parents
                 GROUP BY parent_id
             ) as enfants_par_parent"
        );
        $stats['moyenne_enfants'] = round($moyenne_enfants['moyenne'] ?? 0, 1);
        
        // Parents avec email
        $avec_email = db_query_single(
            "SELECT COUNT(*) as count 
             FROM parents 
             WHERE email IS NOT NULL AND email != ''"
        );
        $stats['avec_email'] = $avec_email['count'] ?? 0;
        $stats['pourcentage_email'] = $stats['total_parents'] > 0 ? 
            round(($stats['avec_email'] / $stats['total_parents']) * 100, 1) : 0;
        
        // Distribution des relations
        $distribution_relations = db_query(
            "SELECT sp.relation, COUNT(*) as count
             FROM student_parents sp
             GROUP BY sp.relation
             ORDER BY count DESC"
        );
        $stats['distribution_relations'] = $distribution_relations;
        
        return [
            'success' => true,
            'statistiques' => $stats
        ];
        
    } catch (Exception $e) {
        error_log("Erreur parent_get_statistiques: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du calcul des statistiques'];
    }
}

/**
 * Obtenir les parents avec plusieurs enfants
 */
function parent_get_avec_plusieurs_enfants(int $minimum = 2): array
{
    $sql = "SELECT p.*, COUNT(sp.eleve_id) as nombre_enfants
            FROM parents p
            JOIN student_parents sp ON p.parent_id = sp.parent_id
            GROUP BY p.parent_id
            HAVING COUNT(sp.eleve_id) >= :minimum
            ORDER BY nombre_enfants DESC, p.nom, p.prenom";
    
    $parents = db_query($sql, ['minimum' => $minimum]);
    
    // Ajouter les informations des enfants
    foreach ($parents as &$parent) {
        $parent['nom_complet'] = $parent['prenom'] . ' ' . $parent['nom'];
        $parent['enfants'] = parent_get_eleves($parent['parent_id']);
    }
    
    return $parents;
}

// =============================================
// FONCTIONS D'EXPORT ET IMPORT
// =============================================

/**
 * Exporter la liste des parents
 */
function parent_exporter(array $filtres = [], string $format = 'csv'): array
{
    // Vérifier les permissions
    if (!check_access('eleves', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        // Récupérer tous les parents selon les filtres
        $resultat = parent_rechercher($filtres, 1, 10000);
        
        if (!$resultat['success']) {
            return $resultat;
        }
        
        $parents = $resultat['parents'];
        
        // Générer l'export selon le format
        switch (strtolower($format)) {
            case 'csv':
                $export = parent_generate_csv($parents);
                $extension = 'csv';
                $mime_type = 'text/csv';
                break;
                
            case 'excel':
                $export = parent_generate_excel($parents);
                $extension = 'xlsx';
                $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
                
            default:
                return ['success' => false, 'error' => 'Format non supporté'];
        }
        
        // Journaliser l'export
        log_action('Export parents', [
            'format' => $format,
            'nombre' => count($parents),
            'filtres' => $filtres,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'parents');
        
        return [
            'success' => true,
            'data' => $export,
            'format' => $format,
            'extension' => $extension,
            'mime_type' => $mime_type,
            'filename' => 'parents_' . date('Ymd_His') . '.' . $extension,
            'count' => count($parents)
        ];
        
    } catch (Exception $e) {
        error_log("Erreur parent_exporter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'export'];
    }
}

/**
 * Générer un CSV des parents
 */
function parent_generate_csv(array $parents): string
{
    $output = fopen('php://temp', 'r+');
    
    // En-têtes
    $headers = [
        'CIN', 'Nom', 'Prénom', 'Genre', 'Profession', 'Entreprise',
        'Téléphone', 'Téléphone Bureau', 'Email', 'Adresse',
        'Est Principal', 'Nombre Enfants', 'Date Création'
    ];
    
    fputcsv($output, $headers, ';');
    
    // Données
    foreach ($parents as $parent) {
        $row = [
            $parent['cin'] ?? '',
            $parent['nom'],
            $parent['prenom'],
            $parent['genre'] ?? '',
            $parent['profession'] ?? '',
            $parent['entreprise'] ?? '',
            $parent['telephone'],
            $parent['telephone_bureau'] ?? '',
            $parent['email'] ?? '',
            $parent['adresse'] ?? '',
            $parent['est_principal'] ? 'Oui' : 'Non',
            $parent['nombre_enfants'] ?? 0,
            format_date($parent['date_creation'] ?? '')
        ];
        
        fputcsv($output, $row, ';');
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Générer un Excel des parents (simplifié)
 */
function parent_generate_excel(array $parents): string
{
    // En production, utiliser PhpSpreadsheet
    // Pour l'instant, on retourne un CSV
    return parent_generate_csv($parents);
}

/**
 * Importer des parents depuis un fichier CSV
 */
function parent_importer_csv(string $csv_content): array
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
        
        if (count($donnees_ligne) !== count($entetes)) {
            $resultats['echecs']++;
            $resultats['erreurs'][] = "Ligne {$i}: Nombre de colonnes incorrect";
            continue;
        }
        
        // Créer un tableau associatif
        $donnees_parent = array_combine($entetes, $donnees_ligne);
        
        // Nettoyer les données
        $donnees_parent['telephone'] = nettoyer_telephone($donnees_parent['telephone'] ?? '');
        $donnees_parent['telephone_bureau'] = !empty($donnees_parent['telephone_bureau']) ? 
            nettoyer_telephone($donnees_parent['telephone_bureau']) : null;
        $donnees_parent['email'] = strtolower(trim($donnees_parent['email'] ?? ''));
        $donnees_parent['est_principal'] = isset($donnees_parent['est_principal']) && 
            strtolower($donnees_parent['est_principal']) === 'oui';
        
        // Créer le parent
        $resultat_creation = parent_creer($donnees_parent);
        
        if ($resultat_creation['success']) {
            $resultats['succes']++;
            $resultats['details'][] = [
                'ligne' => $i,
                'parent_id' => $resultat_creation['parent_id'],
                'nom_complet' => $donnees_parent['prenom'] . ' ' . $donnees_parent['nom']
            ];
        } else {
            $resultats['echecs']++;
            $resultats['erreurs'][] = "Ligne {$i}: " . ($resultat_creation['error'] ?? 'Erreur inconnue');
        }
    }
    
    // Journaliser l'import
    log_action('Import parents CSV', [
        'succes' => $resultats['succes'],
        'echecs' => $resultats['echecs'],
        'total_lignes' => count($lignes) - 1,
        'par_utilisateur' => $_SESSION['user_id'] ?? null
    ], 'parents');
    
    return [
        'success' => true,
        'resultats' => $resultats,
        'message' => "Import terminé: {$resultats['succes']} succès, {$resultats['echecs']} échecs"
    ];
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Nettoyer un numéro de téléphone (réutilisée depuis eleves.php)
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

/**
 * Formater un numéro de téléphone pour l'affichage
 */
function formater_telephone(string $telephone): string
{
    $telephone = nettoyer_telephone($telephone);
    
    if (strlen($telephone) === 10) {
        // Format: 0XX XX XX XX
        return substr($telephone, 0, 3) . ' ' . 
               substr($telephone, 3, 2) . ' ' . 
               substr($telephone, 5, 2) . ' ' . 
               substr($telephone, 7, 2);
    }
    
    return $telephone;
}

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Journaliser le chargement du module
log_action('Module parents chargé', ['version' => '1.0.0']);