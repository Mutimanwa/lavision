<?php
/**
 * Gestion des matières académiques
 * Organisation des cours par niveau et coefficient
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';

// =============================================
// CONSTANTES POUR LES MATIÈRES
// =============================================

// Types de matières
define('MATIERE_OBLIGATOIRE', 'obligatoire');
define('MATIERE_OPTIONNELLE', 'optionnelle');
define('MATIERE_COMPLEMENTAIRE', 'complémentaire');

// Domaines de matières
define('DOMAINE_LETTRES', 'lettres');
define('DOMAINE_SCIENCES', 'sciences');
define('DOMAINE_TECHNIQUE', 'technique');
define('DOMAINE_ARTISTIQUE', 'artistique');
define('DOMAINE_SPORT', 'sport');
define('DOMAINE_LANGUES', 'langues');

// Coefficients standards
define('COEFFICIENT_FORT', 3.0);
define('COEFFICIENT_MOYEN', 2.0);
define('COEFFICIENT_FAIBLE', 1.0);

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'une matière
 */
function matiere_valider_donnees(array $donnees): array
{
    $erreurs = [];
    
    // Champs obligatoires
    if (empty(trim($donnees['code_matiere'] ?? ''))) {
        $erreurs['code_matiere'] = "Le code de la matière est requis";
    } elseif (strlen(trim($donnees['code_matiere'])) < 2) {
        $erreurs['code_matiere'] = "Le code doit faire au moins 2 caractères";
    } elseif (strlen(trim($donnees['code_matiere'])) > 20) {
        $erreurs['code_matiere'] = "Le code ne peut pas dépasser 20 caractères";
    }
    
    if (empty(trim($donnees['nom_matiere'] ?? ''))) {
        $erreurs['nom_matiere'] = "Le nom de la matière est requis";
    } elseif (strlen(trim($donnees['nom_matiere'])) < 3) {
        $erreurs['nom_matiere'] = "Le nom doit faire au moins 3 caractères";
    } elseif (strlen(trim($donnees['nom_matiere'])) > 100) {
        $erreurs['nom_matiere'] = "Le nom ne peut pas dépasser 100 caractères";
    }
    
    // Vérifier le niveau (optionnel)
    if (!empty($donnees['niveau_id'])) {
        $niveau = db_query_single(
            "SELECT niveau_id FROM niveau WHERE niveau_id = :niveau_id",
            ['niveau_id' => $donnees['niveau_id']]
        );
        
        if (!$niveau) {
            $erreurs['niveau_id'] = "Niveau non trouvé";
        }
    }
    
    // Vérifier le coefficient
    if (isset($donnees['coefficient'])) {
        $coefficient = floatval($donnees['coefficient']);
        if ($coefficient < 0.1 || $coefficient > 5.0) {
            $erreurs['coefficient'] = "Le coefficient doit être entre 0.1 et 5.0";
        }
    }
    
    // Vérifier les heures par semaine
    if (isset($donnees['heures_semaine'])) {
        $heures = intval($donnees['heures_semaine']);
        if ($heures < 1 || $heures > 20) {
            $erreurs['heures_semaine'] = "Les heures par semaine doivent être entre 1 et 20";
        }
    }
    
    // Vérifier la couleur (si fournie)
    if (!empty($donnees['couleur'])) {
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $donnees['couleur'])) {
            $erreurs['couleur'] = "Format de couleur invalide (ex: #3498db)";
        }
    }
    
    // Vérifier la description (limite de longueur)
    if (!empty($donnees['description']) && strlen($donnees['description']) > 1000) {
        $erreurs['description'] = "La description est trop longue (max 1000 caractères)";
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - MATIÈRES
// =============================================

/**
 * Créer une nouvelle matière
 */
function matiere_creer(array $donnees): array
{
    // Vérifier les permissions (admin ou proviseur uniquement)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = matiere_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité du code
    $matiere_existante = db_query_single(
        "SELECT matiere_id FROM matieres WHERE code_matiere = :code_matiere",
        ['code_matiere' => trim($donnees['code_matiere'])]
    );
    
    if ($matiere_existante) {
        return [
            'success' => false,
            'error' => "Une matière avec ce code existe déjà"
        ];
    }
    
    // Vérifier l'unicité du nom (au sein du même niveau)
    $nom_existant = db_query_single(
        "SELECT matiere_id FROM matieres 
         WHERE nom_matiere = :nom_matiere 
         AND (niveau_id = :niveau_id OR niveau_id IS NULL)",
        [
            'nom_matiere' => trim($donnees['nom_matiere']),
            'niveau_id' => $donnees['niveau_id'] ?? null
        ]
    );
    
    if ($nom_existant) {
        return [
            'success' => false,
            'error' => "Une matière avec ce nom existe déjà pour ce niveau"
        ];
    }
    
    try {
        // Préparer les données pour l'insertion
        $champs = [
            'code_matiere' => strtoupper(trim($donnees['code_matiere'])),
            'nom_matiere' => trim($donnees['nom_matiere']),
            'description' => trim($donnees['description'] ?? ''),
            'coefficient' => isset($donnees['coefficient']) ? floatval($donnees['coefficient']) : 1.0,
            'niveau_id' => !empty($donnees['niveau_id']) ? (int)$donnees['niveau_id'] : null,
            'couleur' => !empty($donnees['couleur']) ? trim($donnees['couleur']) : '#3498db',
            'heures_semaine' => isset($donnees['heures_semaine']) ? (int)$donnees['heures_semaine'] : 4,
            'est_obligatoire' => isset($donnees['est_obligatoire']) ? ($donnees['est_obligatoire'] ? 1 : 0) : 1
        ];
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO matieres ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $matiere_id = db_last_insert_id();
        
        // Journaliser l'action
        $niveau_info = '';
        if ($champs['niveau_id']) {
            $niveau = db_query_single(
                "SELECT nom_niveau FROM niveau WHERE niveau_id = :niveau_id",
                ['niveau_id' => $champs['niveau_id']]
            );
            $niveau_info = $niveau ? $niveau['nom_niveau'] : 'Inconnu';
        }
        
        log_action('Matière créée', [
            'matiere_id' => $matiere_id,
            'code' => $champs['code_matiere'],
            'nom' => $champs['nom_matiere'],
            'niveau' => $niveau_info ?: 'Tous niveaux',
            'coefficient' => $champs['coefficient'],
            'heures_semaine' => $champs['heures_semaine'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'matieres');
        
        return [
            'success' => true,
            'matiere_id' => $matiere_id,
            'message' => 'Matière créée avec succès'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur matiere_creer: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour une matière existante
 */
function matiere_modifier(int $matiere_id, array $donnees): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROViseur)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la matière existe
    $matiere_existante = matiere_get_by_id($matiere_id);
    if (!$matiere_existante) {
        return ['success' => false, 'error' => 'Matière non trouvée'];
    }
    
    // Valider les données
    $erreurs = matiere_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité du code (si modifié)
    if (isset($donnees['code_matiere']) && $donnees['code_matiere'] !== $matiere_existante['code_matiere']) {
        $code_existant = db_query_single(
            "SELECT matiere_id FROM matieres 
             WHERE code_matiere = :code_matiere 
             AND matiere_id != :matiere_id",
            [
                'code_matiere' => strtoupper(trim($donnees['code_matiere'])),
                'matiere_id' => $matiere_id
            ]
        );
        
        if ($code_existant) {
            return [
                'success' => false,
                'error' => "Une matière avec ce code existe déjà"
            ];
        }
    }
    
    // Vérifier l'unicité du nom (si modifié)
    if (isset($donnees['nom_matiere']) && $donnees['nom_matiere'] !== $matiere_existante['nom_matiere']) {
        $niveau_id = $donnees['niveau_id'] ?? $matiere_existante['niveau_id'];
        
        $nom_existant = db_query_single(
            "SELECT matiere_id FROM matieres 
             WHERE nom_matiere = :nom_matiere 
             AND (niveau_id = :niveau_id OR niveau_id IS NULL)
             AND matiere_id != :matiere_id",
            [
                'nom_matiere' => trim($donnees['nom_matiere']),
                'niveau_id' => $niveau_id,
                'matiere_id' => $matiere_id
            ]
        );
        
        if ($nom_existant) {
            return [
                'success' => false,
                'error' => "Une matière avec ce nom existe déjà pour ce niveau"
            ];
        }
    }
    
    // Ne pas permettre de changer le niveau si la matière est utilisée dans des notes
    if (isset($donnees['niveau_id']) && $donnees['niveau_id'] != $matiere_existante['niveau_id']) {
        $notes_existantes = db_query_single(
            "SELECT COUNT(*) as count FROM notes WHERE matiere_id = :matiere_id",
            ['matiere_id' => $matiere_id]
        );
        
        if ($notes_existantes && $notes_existantes['count'] > 0) {
            return [
                'success' => false,
                'error' => "Impossible de changer le niveau d'une matière ayant des notes enregistrées"
            ];
        }
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['matiere_id' => $matiere_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'code_matiere', 'nom_matiere', 'description', 'coefficient',
            'niveau_id', 'couleur', 'heures_semaine', 'est_obligatoire'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                
                if ($champ === 'code_matiere') {
                    $params[$champ] = strtoupper(trim($donnees[$champ]));
                } elseif ($champ === 'coefficient') {
                    $params[$champ] = floatval($donnees[$champ]);
                } elseif ($champ === 'heures_semaine') {
                    $params[$champ] = (int)$donnees[$champ];
                } elseif ($champ === 'niveau_id' && empty($donnees[$champ])) {
                    $params[$champ] = null;
                } elseif ($champ === 'est_obligatoire') {
                    $params[$champ] = $donnees[$champ] ? 1 : 0;
                } else {
                    $params[$champ] = trim($donnees[$champ]);
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Construction de la requête
        $sql = "UPDATE matieres SET " . implode(', ', $updates) . " WHERE matiere_id = :matiere_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Matière modifiée', [
                'matiere_id' => $matiere_id,
                'ancien_nom' => $matiere_existante['nom_matiere'],
                'nouveau_nom' => $donnees['nom_matiere'] ?? $matiere_existante['nom_matiere'],
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'matieres');
            
            return [
                'success' => true,
                'message' => 'Matière mise à jour avec succès',
                'matiere_id' => $matiere_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur matiere_modifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Supprimer une matière
 */
function matiere_supprimer(int $matiere_id): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la matière existe
    $matiere = matiere_get_by_id($matiere_id);
    if (!$matiere) {
        return ['success' => false, 'error' => 'Matière non trouvée'];
    }
    
    // Vérifier les dépendances
    $dependances = matiere_verifier_dependances($matiere_id);
    if (!empty($dependances)) {
        return [
            'success' => false,
            'error' => 'Impossible de supprimer cette matière',
            'dependances' => $dependances
        ];
    }
    
    try {
        // Supprimer la matière
        $sql = "DELETE FROM matieres WHERE matiere_id = :matiere_id";
        $success = db_execute($sql, ['matiere_id' => $matiere_id]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Matière supprimée', [
                'matiere_id' => $matiere_id,
                'code' => $matiere['code_matiere'],
                'nom' => $matiere['nom_matiere'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'matieres');
            
            return [
                'success' => true,
                'message' => 'Matière supprimée avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        
    } catch (Exception $e) {
        error_log("Erreur matiere_supprimer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Vérifier les dépendances d'une matière
 */
function matiere_verifier_dependances(int $matiere_id): array
{
    $dependances = [];
    
    // Vérifier les notes
    $notes = db_query_single(
        "SELECT COUNT(*) as count FROM notes WHERE matiere_id = :matiere_id",
        ['matiere_id' => $matiere_id]
    );
    
    if ($notes && $notes['count'] > 0) {
        $dependances[] = "Note(s) enregistrée(s): {$notes['count']}";
    }
    
    // Vérifier l'emploi du temps
    $emploi_temps = db_query_single(
        "SELECT COUNT(*) as count FROM emploi_du_temps WHERE matiere_id = :matiere_id",
        ['matiere_id' => $matiere_id]
    );
    
    if ($emploi_temps && $emploi_temps['count'] > 0) {
        $dependances[] = "Cours dans l'emploi du temps: {$emploi_temps['count']}";
    }
    
    return $dependances;
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir une matière par son ID
 */
function matiere_get_by_id(int $matiere_id): ?array
{
    $sql = "SELECT m.*, 
                   n.nom_niveau,
                   n.niveau_id
            FROM matieres m
            LEFT JOIN niveau n ON m.niveau_id = n.niveau_id
            WHERE m.matiere_id = :matiere_id";
    
    $matiere = db_query_single($sql, ['matiere_id' => $matiere_id]);
    
    if ($matiere) {
        // Ajouter des informations calculées
        $matiere['nom_complet'] = $matiere['code_matiere'] . ' - ' . $matiere['nom_matiere'];
        if ($matiere['nom_niveau']) {
            $matiere['nom_complet'] .= ' (' . $matiere['nom_niveau'] . ')';
        }
        
        // Ajouter les statistiques
        $matiere['statistiques'] = matiere_get_statistiques($matiere_id);
        
        // Ajouter les professeurs qui enseignent cette matière
        $matiere['professeurs'] = matiere_get_professeurs($matiere_id);
        
        // Ajouter les classes qui ont cette matière
        $matiere['classes'] = matiere_get_classes($matiere_id);
    }
    
    return $matiere;
}

/**
 * Obtenir une matière par son code
 */
function matiere_get_by_code(string $code_matiere): ?array
{
    $sql = "SELECT m.*, 
                   n.nom_niveau
            FROM matieres m
            LEFT JOIN niveau n ON m.niveau_id = n.niveau_id
            WHERE m.code_matiere = :code_matiere";
    
    $matiere = db_query_single($sql, ['code_matiere' => strtoupper(trim($code_matiere))]);
    
    if ($matiere) {
        $matiere['nom_complet'] = $matiere['code_matiere'] . ' - ' . $matiere['nom_matiere'];
        if ($matiere['nom_niveau']) {
            $matiere['nom_complet'] .= ' (' . $matiere['nom_niveau'] . ')';
        }
    }
    
    return $matiere;
}

/**
 * Rechercher des matières avec filtres
 */
function matiere_rechercher(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   m.*,
                   n.nom_niveau
            FROM matieres m
            LEFT JOIN niveau n ON m.niveau_id = n.niveau_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(m.code_matiere LIKE :{$key} OR m.nom_matiere LIKE :{$key} OR n.nom_niveau LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par niveau
    if (!empty($filtres['niveau_id'])) {
        if ($filtres['niveau_id'] === 'null') {
            $sql .= " AND m.niveau_id IS NULL";
        } else {
            $sql .= " AND m.niveau_id = :niveau_id";
            $params['niveau_id'] = $filtres['niveau_id'];
        }
    }
    
    // Filtre par coefficient minimum
    if (!empty($filtres['coefficient_min'])) {
        $sql .= " AND m.coefficient >= :coefficient_min";
        $params['coefficient_min'] = floatval($filtres['coefficient_min']);
    }
    
    // Filtre par coefficient maximum
    if (!empty($filtres['coefficient_max'])) {
        $sql .= " AND m.coefficient <= :coefficient_max";
        $params['coefficient_max'] = floatval($filtres['coefficient_max']);
    }
    
    // Filtre par heures minimum
    if (!empty($filtres['heures_min'])) {
        $sql .= " AND m.heures_semaine >= :heures_min";
        $params['heures_min'] = (int)$filtres['heures_min'];
    }
    
    // Filtre par heures maximum
    if (!empty($filtres['heures_max'])) {
        $sql .= " AND m.heures_semaine <= :heures_max";
        $params['heures_max'] = (int)$filtres['heures_max'];
    }
    
    // Filtre par matière obligatoire/optionnelle
    if (isset($filtres['est_obligatoire'])) {
        $sql .= " AND m.est_obligatoire = :est_obligatoire";
        $params['est_obligatoire'] = $filtres['est_obligatoire'] ? 1 : 0;
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'm.code_matiere';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY {$order_by} {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $matieres = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($matieres as &$matiere) {
            $matiere['nom_complet'] = $matiere['code_matiere'] . ' - ' . $matiere['nom_matiere'];
            if ($matiere['nom_niveau']) {
                $matiere['nom_complet'] .= ' (' . $matiere['nom_niveau'] . ')';
            }
            
            // Ajouter le nombre de professeurs
            $nombre_professeurs = matiere_count_professeurs($matiere['matiere_id']);
            $matiere['nombre_professeurs'] = $nombre_professeurs;
            
            // Ajouter le nombre de classes
            $nombre_classes = matiere_count_classes($matiere['matiere_id']);
            $matiere['nombre_classes'] = $nombre_classes;
            
            // Déterminer le type de matière
            if ($matiere['coefficient'] >= 3.0) {
                $matiere['type_importance'] = 'forte';
            } elseif ($matiere['coefficient'] >= 2.0) {
                $matiere['type_importance'] = 'moyenne';
            } else {
                $matiere['type_importance'] = 'faible';
            }
        }
        
        return [
            'success' => true,
            'matieres' => $matieres,
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
        error_log("Erreur matiere_rechercher: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

/**
 * Obtenir les matières par niveau
 */
function matiere_get_par_niveau(int $niveau_id): array
{
    $sql = "SELECT m.*
            FROM matieres m
            WHERE m.niveau_id = :niveau_id OR m.niveau_id IS NULL
            ORDER BY m.coefficient DESC, m.nom_matiere";
    
    $matieres = db_query($sql, ['niveau_id' => $niveau_id]);
    
    // Ajouter des informations supplémentaires
    foreach ($matieres as &$matiere) {
        $matiere['nom_complet'] = $matiere['code_matiere'] . ' - ' . $matiere['nom_matiere'];
    }
    
    return $matieres;
}

/**
 * Obtenir les matières obligatoires par niveau
 */
function matiere_get_obligatoires_par_niveau(int $niveau_id): array
{
    $sql = "SELECT m.*
            FROM matieres m
            WHERE (m.niveau_id = :niveau_id OR m.niveau_id IS NULL)
            AND m.est_obligatoire = 1
            ORDER BY m.coefficient DESC, m.nom_matiere";
    
    $matieres = db_query($sql, ['niveau_id' => $niveau_id]);
    
    foreach ($matieres as &$matiere) {
        $matiere['nom_complet'] = $matiere['code_matiere'] . ' - ' . $matiere['nom_matiere'];
    }
    
    return $matieres;
}

// =============================================
// FONCTIONS DE GESTION DES PROFESSEURS
// =============================================

/**
 * Obtenir les professeurs qui enseignent une matière
 */
function matiere_get_professeurs(int $matiere_id): array
{
    $sql = "SELECT DISTINCT p.*,
                   u.email,
                   u.telephone as telephone_user
            FROM emploi_du_temps e
            JOIN professeurs p ON e.professeur_id = p.professeur_id
            LEFT JOIN user_admins u ON p.user_id = u.user_id
            WHERE e.matiere_id = :matiere_id
            AND e.statut = 'actif'
            ORDER BY p.nom, p.prenom";
    
    $professeurs = db_query($sql, ['matiere_id' => $matiere_id]);
    
    // Ajouter des informations supplémentaires
    foreach ($professeurs as &$professeur) {
        $professeur['nom_complet'] = $professeur['prenom'] . ' ' . $professeur['nom'];
        
        // Compter le nombre de classes où il enseigne cette matière
        $nombre_classes = db_query_single(
            "SELECT COUNT(DISTINCT e.class_id) as count
             FROM emploi_du_temps e
             WHERE e.professeur_id = :professeur_id
             AND e.matiere_id = :matiere_id
             AND e.statut = 'actif'",
            ['professeur_id' => $professeur['professeur_id'], 'matiere_id' => $matiere_id]
        );
        
        $professeur['nombre_classes'] = $nombre_classes['count'] ?? 0;
    }
    
    return $professeurs;
}

/**
 * Compter le nombre de professeurs pour une matière
 */
function matiere_count_professeurs(int $matiere_id): int
{
    $result = db_query_single(
        "SELECT COUNT(DISTINCT e.professeur_id) as count
         FROM emploi_du_temps e
         WHERE e.matiere_id = :matiere_id
         AND e.statut = 'actif'",
        ['matiere_id' => $matiere_id]
    );
    
    return $result ? (int)$result['count'] : 0;
}

/**
 * Assigner un professeur à une matière pour une classe
 */
function matiere_assigner_professeur(int $matiere_id, int $professeur_id, int $class_id, array $horaires = []): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la matière existe
    $matiere = matiere_get_by_id($matiere_id);
    if (!$matiere) {
        return ['success' => false, 'error' => 'Matière non trouvée'];
    }
    
    // Vérifier que le professeur existe
    $professeur = db_query_single(
        "SELECT professeur_id, nom, prenom FROM professeurs WHERE professeur_id = :professeur_id",
        ['professeur_id' => $professeur_id]
    );
    
    if (!$professeur) {
        return ['success' => false, 'error' => 'Professeur non trouvé'];
    }
    
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Vérifier les horaires
    if (empty($horaires)) {
        return ['success' => false, 'error' => 'Aucun horaire fourni'];
    }
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        $cours_crees = 0;
        $erreurs = [];
        
        foreach ($horaires as $horaire) {
            // Valider l'horaire
            if (empty($horaire['jour_semaine']) || empty($horaire['heure_debut']) || empty($horaire['heure_fin'])) {
                $erreurs[] = "Horaire incomplet";
                continue;
            }
            
            // Vérifier les conflits d'horaires pour le professeur
            $conflit_professeur = db_query_single(
                "SELECT e.edt_id, c.libelle as classe
                 FROM emploi_du_temps e
                 JOIN classes c ON e.class_id = c.class_id
                 WHERE e.professeur_id = :professeur_id
                 AND e.jour_semaine = :jour_semaine
                 AND e.annee_id = :annee_id
                 AND e.statut = 'actif'
                 AND (
                     (e.heure_debut <= :heure_debut AND e.heure_fin > :heure_debut) OR
                     (e.heure_debut < :heure_fin AND e.heure_fin >= :heure_fin) OR
                     (e.heure_debut >= :heure_debut AND e.heure_fin <= :heure_fin)
                 )",
                [
                    'professeur_id' => $professeur_id,
                    'jour_semaine' => $horaire['jour_semaine'],
                    'annee_id' => $classe['annee_id'],
                    'heure_debut' => $horaire['heure_debut'],
                    'heure_fin' => $horaire['heure_fin']
                ]
            );
            
            if ($conflit_professeur) {
                $erreurs[] = "Conflit d'horaire avec la classe {$conflit_professeur['classe']}";
                continue;
            }
            
            // Vérifier les conflits d'horaires pour la classe
            $conflit_classe = db_query_single(
                "SELECT e.edt_id, m.nom_matiere
                 FROM emploi_du_temps e
                 JOIN matieres m ON e.matiere_id = m.matiere_id
                 WHERE e.class_id = :class_id
                 AND e.jour_semaine = :jour_semaine
                 AND e.annee_id = :annee_id
                 AND e.statut = 'actif'
                 AND (
                     (e.heure_debut <= :heure_debut AND e.heure_fin > :heure_debut) OR
                     (e.heure_debut < :heure_fin AND e.heure_fin >= :heure_fin) OR
                     (e.heure_debut >= :heure_debut AND e.heure_fin <= :heure_fin)
                 )",
                [
                    'class_id' => $class_id,
                    'jour_semaine' => $horaire['jour_semaine'],
                    'annee_id' => $classe['annee_id'],
                    'heure_debut' => $horaire['heure_debut'],
                    'heure_fin' => $horaire['heure_fin']
                ]
            );
            
            if ($conflit_classe) {
                $erreurs[] = "Conflit d'horaire avec la matière {$conflit_classe['nom_matiere']}";
                continue;
            }
            
            // Créer le cours
            $sql = "INSERT INTO emploi_du_temps 
                    (class_id, matiere_id, professeur_id, jour_semaine, heure_debut, heure_fin, 
                     salle, type_cours, annee_id, periode, date_debut, date_fin, statut)
                    VALUES (:class_id, :matiere_id, :professeur_id, :jour_semaine, :heure_debut, :heure_fin,
                            :salle, :type_cours, :annee_id, :periode, :date_debut, :date_fin, 'actif')";
            
            $success = db_execute($sql, [
                'class_id' => $class_id,
                'matiere_id' => $matiere_id,
                'professeur_id' => $professeur_id,
                'jour_semaine' => $horaire['jour_semaine'],
                'heure_debut' => $horaire['heure_debut'],
                'heure_fin' => $horaire['heure_fin'],
                'salle' => $horaire['salle'] ?? $classe['salle'],
                'type_cours' => $horaire['type_cours'] ?? 'cours',
                'annee_id' => $classe['annee_id'],
                'periode' => $horaire['periode'] ?? 'trimestre1',
                'date_debut' => $horaire['date_debut'] ?? $classe['date_debut'] ?? date('Y-m-d'),
                'date_fin' => $horaire['date_fin'] ?? $classe['date_fin'] ?? date('Y-m-d', strtotime('+3 months'))
            ]);
            
            if ($success) {
                $cours_crees++;
            } else {
                $erreurs[] = "Erreur lors de la création du cours pour {$horaire['jour_semaine']}";
            }
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        $professeur_nom = $professeur['prenom'] . ' ' . $professeur['nom'];
        
        log_action('Professeur assigné à matière', [
            'matiere_id' => $matiere_id,
            'matiere_nom' => $matiere['nom_matiere'],
            'professeur_id' => $professeur_id,
            'professeur_nom' => $professeur_nom,
            'class_id' => $class_id,
            'classe_nom' => $classe['nom_complet'],
            'cours_crees' => $cours_crees,
            'erreurs' => $erreurs,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'matieres');
        
        return [
            'success' => true,
            'message' => "{$cours_crees} cours créé(s) pour {$professeur_nom}",
            'cours_crees' => $cours_crees,
            'erreurs' => $erreurs
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur matiere_assigner_professeur: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'assignation'];
    }
}

// =============================================
// FONCTIONS DE GESTION DES CLASSES
// =============================================

/**
 * Obtenir les classes qui ont une matière
 */
function matiere_get_classes(int $matiere_id): array
{
    $sql = "SELECT DISTINCT c.*,
                   n.nom_niveau,
                   s.nom_section
            FROM emploi_du_temps e
            JOIN classes c ON e.class_id = c.class_id
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            WHERE e.matiere_id = :matiere_id
            AND e.statut = 'actif'
            ORDER BY n.nom_niveau, c.libelle";
    
    $classes = db_query($sql, ['matiere_id' => $matiere_id]);
    
    // Ajouter des informations supplémentaires
    foreach ($classes as &$classe) {
        $classe['nom_complet'] = $classe['libelle'] . ' - ' . $classe['nom_niveau'];
        if ($classe['nom_section']) {
            $classe['nom_complet'] .= ' ' . $classe['nom_section'];
        }
        
        // Compter le nombre d'heures par semaine
        $heures = db_query_single(
            "SELECT SUM(TIMESTAMPDIFF(HOUR, e.heure_debut, e.heure_fin)) as total_heures
             FROM emploi_du_temps e
             WHERE e.matiere_id = :matiere_id
             AND e.class_id = :class_id
             AND e.statut = 'actif'",
            ['matiere_id' => $matiere_id, 'class_id' => $classe['class_id']]
        );
        
        $classe['heures_semaine_classe'] = $heures['total_heures'] ?? 0;
        
        // Obtenir les professeurs pour cette classe
        $professeurs = db_query(
            "SELECT DISTINCT p.professeur_id, p.nom, p.prenom
             FROM emploi_du_temps e
             JOIN professeurs p ON e.professeur_id = p.professeur_id
             WHERE e.matiere_id = :matiere_id
             AND e.class_id = :class_id
             AND e.statut = 'actif'",
            ['matiere_id' => $matiere_id, 'class_id' => $classe['class_id']]
        );
        
        $classe['professeurs'] = $professeurs;
    }
    
    return $classes;
}

/**
 * Compter le nombre de classes pour une matière
 */
function matiere_count_classes(int $matiere_id): int
{
    $result = db_query_single(
        "SELECT COUNT(DISTINCT e.class_id) as count
         FROM emploi_du_temps e
         WHERE e.matiere_id = :matiere_id
         AND e.statut = 'actif'",
        ['matiere_id' => $matiere_id]
    );
    
    return $result ? (int)$result['count'] : 0;
}

/**
 * Ajouter une matière à une classe
 */
function matiere_ajouter_a_classe(int $matiere_id, int $class_id, array $horaires): array
{
    // Vérifier que la matière existe
    $matiere = matiere_get_by_id($matiere_id);
    if (!$matiere) {
        return ['success' => false, 'error' => 'Matière non trouvée'];
    }
    
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Vérifier que la matière correspond au niveau de la classe
    if ($matiere['niveau_id'] && $matiere['niveau_id'] != $classe['niveau_id']) {
        $niveau_matiere = db_query_single(
            "SELECT nom_niveau FROM niveau WHERE niveau_id = :niveau_id",
            ['niveau_id' => $matiere['niveau_id']]
        );
        
        return [
            'success' => false,
            'error' => "Cette matière est destinée au niveau {$niveau_matiere['nom_niveau']}, pas au {$classe['nom_niveau']}"
        ];
    }
    
    // Vérifier que la matière n'est pas déjà dans la classe
    $deja_presente = db_query_single(
        "SELECT edt_id FROM emploi_du_temps 
         WHERE matiere_id = :matiere_id 
         AND class_id = :class_id 
         AND statut = 'actif' 
         LIMIT 1",
        ['matiere_id' => $matiere_id, 'class_id' => $class_id]
    );
    
    if ($deja_presente) {
        return [
            'success' => false,
            'error' => 'Cette matière est déjà enseignée dans cette classe'
        ];
    }
    
    // Demander les informations manquantes
    if (empty($horaires) || empty($horaires['professeur_id'])) {
        return [
            'success' => false,
            'error' => 'Informations manquantes',
            'besoins' => [
                'professeur_id' => 'ID du professeur',
                'horaires' => 'Tableau des horaires (jour_semaine, heure_debut, heure_fin)'
            ]
        ];
    }
    
    // Utiliser la fonction d'assignation de professeur
    return matiere_assigner_professeur($matiere_id, $horaires['professeur_id'], $class_id, $horaires['cours'] ?? []);
}

/**
 * Retirer une matière d'une classe
 */
function matiere_retirer_de_classe(int $matiere_id, int $class_id, string $motif = ''): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la matière est bien enseignée dans cette classe
    $cours_existants = db_query(
        "SELECT e.*, p.nom as professeur_nom, p.prenom as professeur_prenom
         FROM emploi_du_temps e
         JOIN professeurs p ON e.professeur_id = p.professeur_id
         WHERE e.matiere_id = :matiere_id
         AND e.class_id = :class_id
         AND e.statut = 'actif'",
        ['matiere_id' => $matiere_id, 'class_id' => $class_id]
    );
    
    if (empty($cours_existants)) {
        return [
            'success' => false,
            'error' => 'Cette matière n\'est pas enseignée dans cette classe'
        ];
    }
    
    // Vérifier s'il y a des notes enregistrées pour cette matière dans cette classe
    $notes_existantes = db_query_single(
        "SELECT COUNT(*) as count
         FROM notes n
         JOIN admissions a ON n.eleve_id = a.eleve_id
         WHERE n.matiere_id = :matiere_id
         AND a.class_id = :class_id
         AND a.statut_admission = 'approuve'",
        ['matiere_id' => $matiere_id, 'class_id' => $class_id]
    );
    
    if ($notes_existantes && $notes_existantes['count'] > 0) {
        return [
            'success' => false,
            'error' => "Impossible de retirer la matière: {$notes_existantes['count']} note(s) enregistrée(s)"
        ];
    }
    
    try {
        // Désactiver tous les cours de cette matière dans cette classe
        $sql = "UPDATE emploi_du_temps 
                SET statut = 'suspendu',
                    date_fin = :date_fin
                WHERE matiere_id = :matiere_id
                AND class_id = :class_id
                AND statut = 'actif'";
        
        $success = db_execute($sql, [
            'matiere_id' => $matiere_id,
            'class_id' => $class_id,
            'date_fin' => date('Y-m-d')
        ]);
        
        if ($success) {
            // Journaliser l'action
            $matiere = matiere_get_by_id($matiere_id);
            $classe = classe_get_by_id($class_id);
            
            $professeurs = array_map(function($cours) {
                return $cours['professeur_prenom'] . ' ' . $cours['professeur_nom'];
            }, $cours_existants);
            
            log_action('Matière retirée de classe', [
                'matiere_id' => $matiere_id,
                'matiere_nom' => $matiere['nom_matiere'],
                'class_id' => $class_id,
                'classe_nom' => $classe['nom_complet'],
                'professeurs_concernes' => array_unique($professeurs),
                'nombre_cours' => count($cours_existants),
                'motif' => $motif,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'matieres');
            
            return [
                'success' => true,
                'message' => 'Matière retirée de la classe avec succès',
                'nombre_cours_desactives' => count($cours_existants)
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors du retrait de la matière'];
        
    } catch (Exception $e) {
        error_log("Erreur matiere_retirer_de_classe: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE STATISTIQUES
// =============================================

/**
 * Obtenir les statistiques d'une matière
 */
function matiere_get_statistiques(int $matiere_id): array
{
    $statistiques = [];
    
    try {
        // Nombre de classes qui ont cette matière
        $nombre_classes = matiere_count_classes($matiere_id);
        $statistiques['nombre_classes'] = $nombre_classes;
        
        // Nombre de professeurs qui enseignent cette matière
        $nombre_professeurs = matiere_count_professeurs($matiere_id);
        $statistiques['nombre_professeurs'] = $nombre_professeurs;
        
        // Nombre total d'élèves étudiant cette matière
        $nombre_eleves = db_query_single(
            "SELECT COUNT(DISTINCT a.eleve_id) as count
             FROM admissions a
             JOIN emploi_du_temps e ON a.class_id = e.class_id
             WHERE e.matiere_id = :matiere_id
             AND a.statut_admission = 'approuve'
             AND e.statut = 'actif'",
            ['matiere_id' => $matiere_id]
        );
        $statistiques['nombre_eleves'] = $nombre_eleves['count'] ?? 0;
        
        // Nombre total d'heures de cours par semaine
        $total_heures = db_query_single(
            "SELECT SUM(TIMESTAMPDIFF(HOUR, e.heure_debut, e.heure_fin)) as total_heures
             FROM emploi_du_temps e
             WHERE e.matiere_id = :matiere_id
             AND e.statut = 'actif'",
            ['matiere_id' => $matiere_id]
        );
        $statistiques['total_heures_semaine'] = $total_heures['total_heures'] ?? 0;
        
        // Répartition par type de cours
        $par_type_cours = db_query(
            "SELECT e.type_cours, COUNT(*) as nombre_cours,
                    SUM(TIMESTAMPDIFF(HOUR, e.heure_debut, e.heure_fin)) as total_heures
             FROM emploi_du_temps e
             WHERE e.matiere_id = :matiere_id
             AND e.statut = 'actif'
             GROUP BY e.type_cours",
            ['matiere_id' => $matiere_id]
        );
        $statistiques['par_type_cours'] = $par_type_cours;
        
        // Répartition par jour de la semaine
        $par_jour = db_query(
            "SELECT e.jour_semaine, COUNT(*) as nombre_cours,
                    SUM(TIMESTAMPDIFF(HOUR, e.heure_debut, e.heure_fin)) as total_heures
             FROM emploi_du_temps e
             WHERE e.matiere_id = :matiere_id
             AND e.statut = 'actif'
             GROUP BY e.jour_semaine
             ORDER BY 
                CASE e.jour_semaine
                    WHEN 'Lundi' THEN 1
                    WHEN 'Mardi' THEN 2
                    WHEN 'Mercredi' THEN 3
                    WHEN 'Jeudi' THEN 4
                    WHEN 'Vendredi' THEN 5
                    WHEN 'Samedi' THEN 6
                    ELSE 7
                END",
            ['matiere_id' => $matiere_id]
        );
        $statistiques['par_jour'] = $par_jour;
        
        // Statistiques de notes (si disponibles)
        $stats_notes = db_query_single(
            "SELECT 
                COUNT(*) as nombre_notes,
                AVG(n.note) as moyenne_generale,
                MIN(n.note) as note_minimale,
                MAX(n.note) as note_maximale,
                COUNT(CASE WHEN n.note >= 10 THEN 1 END) as nombre_reussites,
                COUNT(CASE WHEN n.note < 10 THEN 1 END) as nombre_echecs
             FROM notes n
             WHERE n.matiere_id = :matiere_id",
            ['matiere_id' => $matiere_id]
        );
        
        if ($stats_notes && $stats_notes['nombre_notes'] > 0) {
            $statistiques['notes'] = [
                'nombre_notes' => $stats_notes['nombre_notes'],
                'moyenne_generale' => round($stats_notes['moyenne_generale'], 2),
                'note_minimale' => $stats_notes['note_minimale'],
                'note_maximale' => $stats_notes['note_maximale'],
                'taux_reussite' => round(($stats_notes['nombre_reussites'] / $stats_notes['nombre_notes']) * 100, 1),
                'nombre_reussites' => $stats_notes['nombre_reussites'],
                'nombre_echecs' => $stats_notes['nombre_echecs']
            ];
        }
        
        return $statistiques;
        
    } catch (Exception $e) {
        error_log("Erreur matiere_get_statistiques: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtenir les statistiques globales des matières
 */
function matiere_get_statistiques_globales(): array
{
    try {
        $stats = [];
        
        // Nombre total de matières
        $total_matieres = db_query_single("SELECT COUNT(*) as count FROM matieres");
        $stats['total_matieres'] = $total_matieres['count'] ?? 0;
        
        // Matières par niveau
        $par_niveau = db_query(
            "SELECT n.nom_niveau, COUNT(m.matiere_id) as nombre_matieres,
                    AVG(m.coefficient) as coefficient_moyen,
                    SUM(m.heures_semaine) as total_heures
             FROM matieres m
             LEFT JOIN niveau n ON m.niveau_id = n.niveau_id
             GROUP BY n.niveau_id, n.nom_niveau
             ORDER BY n.nom_niveau"
        );
        $stats['par_niveau'] = $par_niveau;
        
        // Répartition obligatoire/optionnelle
        $par_type = db_query(
            "SELECT 
                CASE WHEN est_obligatoire = 1 THEN 'Obligatoire' ELSE 'Optionnelle' END as type,
                COUNT(*) as nombre,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM matieres)), 1) as pourcentage
             FROM matieres 
             GROUP BY est_obligatoire"
        );
        $stats['par_type'] = $par_type;
        
        // Coefficient moyen
        $coefficient_moyen = db_query_single("SELECT AVG(coefficient) as moyen FROM matieres");
        $stats['coefficient_moyen'] = round($coefficient_moyen['moyen'] ?? 0, 2);
        
        // Heures moyennes par semaine
        $heures_moyennes = db_query_single("SELECT AVG(heures_semaine) as moyen FROM matieres");
        $stats['heures_moyennes'] = round($heures_moyennes['moyen'] ?? 0, 1);
        
        // Matières les plus enseignées (par nombre de classes)
        $plus_enseignees = db_query(
            "SELECT m.matiere_id, m.code_matiere, m.nom_matiere,
                    COUNT(DISTINCT e.class_id) as nombre_classes,
                    COUNT(DISTINCT e.professeur_id) as nombre_professeurs
             FROM matieres m
             LEFT JOIN emploi_du_temps e ON m.matiere_id = e.matiere_id AND e.statut = 'actif'
             GROUP BY m.matiere_id, m.code_matiere, m.nom_matiere
             ORDER BY nombre_classes DESC
             LIMIT 10"
        );
        $stats['plus_enseignees'] = $plus_enseignees;
        
        // Matières avec le plus grand coefficient
        $plus_fort_coefficient = db_query(
            "SELECT matiere_id, code_matiere, nom_matiere, coefficient, heures_semaine
             FROM matieres
             ORDER BY coefficient DESC, heures_semaine DESC
             LIMIT 10"
        );
        $stats['plus_fort_coefficient'] = $plus_fort_coefficient;
        
        return [
            'success' => true,
            'statistiques' => $stats
        ];
        
    } catch (Exception $e) {
        error_log("Erreur matiere_get_statistiques_globales: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du calcul des statistiques'];
    }
}

// =============================================
// FONCTIONS D'EXPORT ET IMPORT
// =============================================

/**
 * Exporter la liste des matières
 */
function matiere_exporter(array $filtres = [], string $format = 'csv'): array
{
    // Vérifier les permissions
    if (!check_access('matieres', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        // Récupérer toutes les matières selon les filtres
        $resultat = matiere_rechercher($filtres, 1, 10000);
        
        if (!$resultat['success']) {
            return $resultat;
        }
        
        $matieres = $resultat['matieres'];
        
        // Générer l'export selon le format
        switch (strtolower($format)) {
            case 'csv':
                $export = matiere_generate_csv($matieres);
                $extension = 'csv';
                $mime_type = 'text/csv';
                break;
                
            case 'excel':
                $export = matiere_generate_excel($matieres);
                $extension = 'xlsx';
                $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
                
            default:
                return ['success' => false, 'error' => 'Format non supporté'];
        }
        
        // Journaliser l'export
        log_action('Export matières', [
            'format' => $format,
            'nombre' => count($matieres),
            'filtres' => $filtres,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'matieres');
        
        return [
            'success' => true,
            'data' => $export,
            'format' => $format,
            'extension' => $extension,
            'mime_type' => $mime_type,
            'filename' => 'matieres_' . date('Ymd_His') . '.' . $extension,
            'count' => count($matieres)
        ];
        
    } catch (Exception $e) {
        error_log("Erreur matiere_exporter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'export'];
    }
}

/**
 * Générer un CSV des matières
 */
function matiere_generate_csv(array $matieres): string
{
    $output = fopen('php://temp', 'r+');
    
    // En-têtes
    $headers = [
        'Code', 'Nom', 'Description', 'Niveau', 'Coefficient',
        'Heures/Semaine', 'Obligatoire', 'Couleur', 'Date Création'
    ];
    
    fputcsv($output, $headers, ';');
    
    // Données
    foreach ($matieres as $matiere) {
        $row = [
            $matiere['code_matiere'],
            $matiere['nom_matiere'],
            $matiere['description'] ?? '',
            $matiere['nom_niveau'] ?? 'Tous niveaux',
            $matiere['coefficient'],
            $matiere['heures_semaine'],
            $matiere['est_obligatoire'] ? 'Oui' : 'Non',
            $matiere['couleur'] ?? '#3498db',
            format_date($matiere['date_creation'] ?? '')
        ];
        
        fputcsv($output, $row, ';');
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Générer un Excel des matières
 */
function matiere_generate_excel(array $matieres): string
{
    // En production, utiliser PhpSpreadsheet
    // Pour l'instant, on retourne un CSV
    return matiere_generate_csv($matieres);
}

/**
 * Importer des matières depuis un fichier CSV
 */
function matiere_importer_csv(string $csv_content): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
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
        $donnees_matiere = array_combine($entetes, $donnees_ligne);
        
        // Nettoyer et convertir les données
        $donnees_matiere['code_matiere'] = strtoupper(trim($donnees_matiere['code_matiere']));
        $donnees_matiere['nom_matiere'] = trim($donnees_matiere['nom_matiere']);
        
        // Convertir le niveau si fourni
        if (!empty($donnees_matiere['niveau'])) {
            $niveau = db_query_single(
                "SELECT niveau_id FROM niveau WHERE nom_niveau LIKE :niveau_nom",
                ['niveau_nom' => '%' . trim($donnees_matiere['niveau']) . '%']
            );
            
            if ($niveau) {
                $donnees_matiere['niveau_id'] = $niveau['niveau_id'];
            }
            unset($donnees_matiere['niveau']);
        }
        
        // Convertir les booléens
        if (isset($donnees_matiere['est_obligatoire'])) {
            $donnees_matiere['est_obligatoire'] = strtolower($donnees_matiere['est_obligatoire']) === 'oui';
        }
        
        // Convertir les nombres
        if (isset($donnees_matiere['coefficient'])) {
            $donnees_matiere['coefficient'] = floatval($donnees_matiere['coefficient']);
        }
        
        if (isset($donnees_matiere['heures_semaine'])) {
            $donnees_matiere['heures_semaine'] = intval($donnees_matiere['heures_semaine']);
        }
        
        // Créer la matière
        $resultat_creation = matiere_creer($donnees_matiere);
        
        if ($resultat_creation['success']) {
            $resultats['succes']++;
            $resultats['details'][] = [
                'ligne' => $i,
                'matiere_id' => $resultat_creation['matiere_id'],
                'nom' => $donnees_matiere['nom_matiere']
            ];
        } else {
            $resultats['echecs']++;
            $resultats['erreurs'][] = "Ligne {$i}: " . ($resultat_creation['error'] ?? 'Erreur inconnue');
        }
    }
    
    // Journaliser l'import
    log_action('Import matières CSV', [
        'succes' => $resultats['succes'],
        'echecs' => $resultats['echecs'],
        'total_lignes' => count($lignes) - 1,
        'par_utilisateur' => $_SESSION['user_id'] ?? null
    ], 'matieres');
    
    return [
        'success' => true,
        'resultats' => $resultats,
        'message' => "Import terminé: {$resultats['succes']} succès, {$resultats['echecs']} échecs"
    ];
}

// =============================================
// FONCTIONS DE PLANIFICATION
// =============================================

/**
 * Générer le plan d'études pour un niveau
 */
function matiere_generer_plan_etudes(int $niveau_id): array
{
    // Récupérer toutes les matières pour ce niveau
    $matieres = matiere_get_par_niveau($niveau_id);
    
    // Séparer les matières obligatoires et optionnelles
    $plan = [
        'obligatoires' => [],
        'optionnelles' => []
    ];
    
    foreach ($matieres as $matiere) {
        if ($matiere['est_obligatoire']) {
            $plan['obligatoires'][] = $matiere;
        } else {
            $plan['optionnelles'][] = $matiere;
        }
    }
    
    // Calculer les totaux
    $plan['totaux'] = [
        'nombre_obligatoires' => count($plan['obligatoires']),
        'nombre_optionnelles' => count($plan['optionnelles']),
        'total_heures_obligatoires' => array_sum(array_column($plan['obligatoires'], 'heures_semaine')),
        'total_heures_optionnelles' => array_sum(array_column($plan['optionnelles'], 'heures_semaine')),
        'total_coefficient_obligatoires' => array_sum(array_column($plan['obligatoires'], 'coefficient')),
        'total_coefficient_optionnelles' => array_sum(array_column($plan['optionnelles'], 'coefficient'))
    ];
    
    // Récupérer les informations du niveau
    $niveau = db_query_single(
        "SELECT * FROM niveau WHERE niveau_id = :niveau_id",
        ['niveau_id' => $niveau_id]
    );
    
    return [
        'success' => true,
        'plan_etudes' => $plan,
        'niveau' => $niveau,
        'date_generation' => date('Y-m-d H:i:s')
    ];
}

/**
 * Vérifier la cohérence des coefficients
 */
function matiere_verifier_coherence_coefficients(int $niveau_id): array
{
    $plan = matiere_generer_plan_etudes($niveau_id);
    
    if (!$plan['success']) {
        return $plan;
    }
    
    $totaux = $plan['plan_etudes']['totaux'];
    $anomalies = [];
    
    // Vérifier si le total des coefficients est raisonnable
    $total_coefficient = $totaux['total_coefficient_obligatoires'] + $totaux['total_coefficient_optionnelles'];
    
    if ($total_coefficient > 30) {
        $anomalies[] = "Total des coefficients trop élevé: $total_coefficient (max recommandé: 30)";
    }
    
    if ($total_coefficient < 10) {
        $anomalies[] = "Total des coefficients trop faible: $total_coefficient (min recommandé: 10)";
    }
    
    // Vérifier les matières avec coefficient trop élevé
    $matieres_fortes = array_filter(
        array_merge($plan['plan_etudes']['obligatoires'], $plan['plan_etudes']['optionnelles']),
        function($matiere) {
            return $matiere['coefficient'] > 4.0;
        }
    );
    
    if (!empty($matieres_fortes)) {
        $anomalies[] = count($matieres_fortes) . " matière(s) avec coefficient > 4.0";
    }
    
    // Vérifier les matières avec coefficient trop faible
    $matieres_faibles = array_filter(
        array_merge($plan['plan_etudes']['obligatoires'], $plan['plan_etudes']['optionnelles']),
        function($matiere) {
            return $matiere['coefficient'] < 0.5;
        }
    );
    
    if (!empty($matieres_faibles)) {
        $anomalies[] = count($matieres_faibles) . " matière(s) avec coefficient < 0.5";
    }
    
    // Vérifier les matières avec heures insuffisantes
    $matieres_peu_heures = array_filter(
        array_merge($plan['plan_etudes']['obligatoires'], $plan['plan_etudes']['optionnelles']),
        function($matiere) {
            return $matiere['coefficient'] >= 2.0 && $matiere['heures_semaine'] < 3;
        }
    );
    
    if (!empty($matieres_peu_heures)) {
        $anomalies[] = count($matieres_peu_heures) . " matière(s) forte(s) avec moins de 3 heures/semaine";
    }
    
    return [
        'success' => true,
        'niveau_id' => $niveau_id,
        'total_coefficient' => $total_coefficient,
        'anomalies' => $anomalies,
        'est_coherent' => empty($anomalies),
        'recommandations' => [
            'coefficient_total_ideal' => 'Entre 15 et 25',
            'coefficient_matiere_max' => '4.0',
            'heures_par_coefficient' => 'Minimum 1 heure/semaine par point de coefficient'
        ]
    ];
}

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Journaliser le chargement du module
log_action('Module matières chargé', ['version' => '1.0.0']);