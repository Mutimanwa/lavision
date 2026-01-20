<?php
/**
 * Gestion des classes académiques
 * Organisation: Niveau + Section + Année scolaire
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';

// =============================================
// CONSTANTES POUR LES CLASSES
// =============================================

// Statuts des classes
define('CLASSE_ACTIVE', 'active');
define('CLASSE_INACTIVE', 'inactive');

// Types de salles
define('SALLE_CLASSIQUE', 'classique');
define('SALLE_LABO', 'laboratoire');
define('SALLE_INFORMATIQUE', 'informatique');
define('SALLE_SPECIALE', 'spéciale');

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'une classe
 */
function classe_valider_donnees(array $donnees): array
{
    $erreurs = [];
    
    // Champs obligatoires
    $champs_requis = ['niveau_id', 'libelle', 'annee_id'];
    
    foreach ($champs_requis as $champ) {
        if (empty($donnees[$champ])) {
            $erreurs[$champ] = "Ce champ est requis";
        } elseif (!is_numeric($donnees[$champ])) {
            $erreurs[$champ] = "Valeur numérique requise";
        }
    }
    
    // Vérifier que le niveau existe
    if (!empty($donnees['niveau_id'])) {
        $niveau = db_query_single(
            "SELECT niveau_id FROM niveau WHERE niveau_id = :niveau_id",
            ['niveau_id' => $donnees['niveau_id']]
        );
        
        if (!$niveau) {
            $erreurs['niveau_id'] = "Niveau non trouvé";
        }
    }
    
    // Vérifier que l'année scolaire existe et est active
    if (!empty($donnees['annee_id'])) {
        $annee = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE annee_id = :annee_id",
            ['annee_id' => $donnees['annee_id']]
        );
        
        if (!$annee) {
            $erreurs['annee_id'] = "Année scolaire non trouvée";
        }
    }
    
    // Vérifier que la section existe (si fournie)
    if (!empty($donnees['section_id'])) {
        $section = db_query_single(
            "SELECT section_id FROM sections WHERE section_id = :section_id",
            ['section_id' => $donnees['section_id']]
        );
        
        if (!$section) {
            $erreurs['section_id'] = "Section non trouvée";
        }
    }
    
    // Vérifier le libellé (unicité pour l'année)
    if (!empty($donnees['libelle'])) {
        $libelle = trim($donnees['libelle']);
        if (strlen($libelle) < 2) {
            $erreurs['libelle'] = "Le libellé doit faire au moins 2 caractères";
        } elseif (strlen($libelle) > 100) {
            $erreurs['libelle'] = "Le libellé ne peut pas dépasser 100 caractères";
        }
    }
    
    // Vérifier la capacité maximale
    if (isset($donnees['capacite_max']) && $donnees['capacite_max'] !== '') {
        $capacite = (int)$donnees['capacite_max'];
        if ($capacite < 0) {
            $erreurs['capacite_max'] = "La capacité ne peut pas être négative";
        } elseif ($capacite > 100) {
            $erreurs['capacite_max'] = "La capacité maximale est de 100 élèves";
        }
    }
    
    // Vérifier le tuteur (professeur principal)
    if (!empty($donnees['tuteur_id'])) {
        $professeur = db_query_single(
            "SELECT professeur_id FROM professeurs WHERE professeur_id = :tuteur_id",
            ['tuteur_id' => $donnees['tuteur_id']]
        );
        
        if (!$professeur) {
            $erreurs['tuteur_id'] = "Professeur non trouvé";
        }
    }
    
    // Vérifier la salle (si fournie)
    if (!empty($donnees['salle']) && strlen($donnees['salle']) > 50) {
        $erreurs['salle'] = "Le nom de la salle est trop long (max 50 caractères)";
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - CLASSES
// =============================================

/**
 * Créer une nouvelle classe
 */
function classe_creer(array $donnees): array
{
    // Vérifier les permissions (admin ou proviseur uniquement)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = classe_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité du libellé pour cette année
    $classe_existante = db_query_single(
        "SELECT class_id FROM classes 
         WHERE libelle = :libelle 
         AND annee_id = :annee_id",
        [
            'libelle' => trim($donnees['libelle']),
            'annee_id' => $donnees['annee_id']
        ]
    );
    
    if ($classe_existante) {
        return [
            'success' => false,
            'error' => "Une classe avec ce libellé existe déjà pour cette année scolaire"
        ];
    }
    
    try {
        // Préparer les données pour l'insertion
        $champs = [
            'niveau_id' => (int)$donnees['niveau_id'],
            'section_id' => !empty($donnees['section_id']) ? (int)$donnees['section_id'] : null,
            'libelle' => trim($donnees['libelle']),
            'annee_id' => (int)$donnees['annee_id'],
            'capacite_max' => isset($donnees['capacite_max']) && $donnees['capacite_max'] !== '' ? 
                (int)$donnees['capacite_max'] : null,
            'salle' => !empty($donnees['salle']) ? trim($donnees['salle']) : null,
            'tuteur_id' => !empty($donnees['tuteur_id']) ? (int)$donnees['tuteur_id'] : null,
            'statut' => $donnees['statut'] ?? CLASSE_ACTIVE
        ];
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO classes ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $class_id = db_last_insert_id();
        
        // Journaliser l'action
        $niveau = db_query_single(
            "SELECT nom_niveau FROM niveau WHERE niveau_id = :niveau_id",
            ['niveau_id' => $champs['niveau_id']]
        );
        
        $section = $champs['section_id'] ? db_query_single(
            "SELECT nom_section FROM sections WHERE section_id = :section_id",
            ['section_id' => $champs['section_id']]
        ) : null;
        
        $annee = db_query_single(
            "SELECT annee_libelle FROM annees_scolaire WHERE annee_id = :annee_id",
            ['annee_id' => $champs['annee_id']]
        );
        
        log_action('Classe créée', [
            'class_id' => $class_id,
            'libelle' => $champs['libelle'],
            'niveau' => $niveau ? $niveau['nom_niveau'] : 'Inconnu',
            'section' => $section ? $section['nom_section'] : 'Non spécifiée',
            'annee' => $annee ? $annee['annee_libelle'] : 'Inconnue',
            'capacite_max' => $champs['capacite_max'],
            'salle' => $champs['salle'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'classes');
        
        return [
            'success' => true,
            'class_id' => $class_id,
            'message' => 'Classe créée avec succès'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur classe_creer: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Mettre à jour une classe existante
 */
function classe_modifier(int $class_id, array $donnees): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la classe existe
    $classe_existante = classe_get_by_id($class_id);
    if (!$classe_existante) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Valider les données
    $erreurs = classe_valider_donnees($donnees);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier l'unicité du libellé (si modifié)
    if (isset($donnees['libelle']) && $donnees['libelle'] !== $classe_existante['libelle']) {
        $annee_id = $donnees['annee_id'] ?? $classe_existante['annee_id'];
        
        $classe_avec_libelle = db_query_single(
            "SELECT class_id FROM classes 
             WHERE libelle = :libelle 
             AND annee_id = :annee_id 
             AND class_id != :class_id",
            [
                'libelle' => trim($donnees['libelle']),
                'annee_id' => $annee_id,
                'class_id' => $class_id
            ]
        );
        
        if ($classe_avec_libelle) {
            return [
                'success' => false,
                'error' => "Une classe avec ce libellé existe déjà pour cette année scolaire"
            ];
        }
    }
    
    // Ne pas permettre la modification de l'année si la classe a des élèves
    if (isset($donnees['annee_id']) && $donnees['annee_id'] != $classe_existante['annee_id']) {
        $nombre_eleves = classe_get_nombre_eleves($class_id);
        if ($nombre_eleves > 0) {
            return [
                'success' => false,
                'error' => "Impossible de changer l'année scolaire d'une classe ayant des élèves"
            ];
        }
    }
    
    // Vérifier la capacité (ne pas la réduire en dessous du nombre d'élèves actuels)
    if (isset($donnees['capacite_max']) && $donnees['capacite_max'] !== '') {
        $nouvelle_capacite = (int)$donnees['capacite_max'];
        $nombre_eleves = classe_get_nombre_eleves($class_id);
        
        if ($nouvelle_capacite > 0 && $nouvelle_capacite < $nombre_eleves) {
            return [
                'success' => false,
                'error' => "La capacité ne peut pas être inférieure au nombre d'élèves actuels ($nombre_eleves)"
            ];
        }
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['class_id' => $class_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'niveau_id', 'section_id', 'libelle', 'annee_id',
            'capacite_max', 'salle', 'tuteur_id', 'statut'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                
                if ($champ === 'capacite_max' && $donnees[$champ] === '') {
                    $params[$champ] = null;
                } elseif ($champ === 'section_id' && empty($donnees[$champ])) {
                    $params[$champ] = null;
                } elseif ($champ === 'tuteur_id' && empty($donnees[$champ])) {
                    $params[$champ] = null;
                } else {
                    $params[$champ] = $donnees[$champ];
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Construction de la requête
        $sql = "UPDATE classes SET " . implode(', ', $updates) . " WHERE class_id = :class_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Classe modifiée', [
                'class_id' => $class_id,
                'ancien_libelle' => $classe_existante['libelle'],
                'nouveau_libelle' => $donnees['libelle'] ?? $classe_existante['libelle'],
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'classes');
            
            return [
                'success' => true,
                'message' => 'Classe mise à jour avec succès',
                'class_id' => $class_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur classe_modifier: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Désactiver une classe
 */
function classe_desactiver(int $class_id): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Vérifier que la classe n'est pas déjà inactive
    if ($classe['statut'] === CLASSE_INACTIVE) {
        return ['success' => false, 'error' => 'Classe déjà inactive'];
    }
    
    // Vérifier si la classe a des élèves actifs
    $eleves_actifs = classe_get_eleves($class_id, true);
    if (!empty($eleves_actifs)) {
        return [
            'success' => false,
            'error' => 'Impossible de désactiver une classe avec des élèves actifs',
            'nombre_eleves' => count($eleves_actifs)
        ];
    }
    
    try {
        // Désactiver la classe
        $sql = "UPDATE classes SET statut = :statut WHERE class_id = :class_id";
        
        $success = db_execute($sql, [
            'statut' => CLASSE_INACTIVE,
            'class_id' => $class_id
        ]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Classe désactivée', [
                'class_id' => $class_id,
                'libelle' => $classe['libelle'],
                'niveau' => $classe['nom_niveau'],
                'section' => $classe['nom_section'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'classes');
            
            return [
                'success' => true,
                'message' => 'Classe désactivée avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la désactivation'];
        
    } catch (Exception $e) {
        error_log("Erreur classe_desactiver: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Activer une classe
 */
function classe_activer(int $class_id): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Vérifier que la classe n'est pas déjà active
    if ($classe['statut'] === CLASSE_ACTIVE) {
        return ['success' => false, 'error' => 'Classe déjà active'];
    }
    
    try {
        // Activer la classe
        $sql = "UPDATE classes SET statut = :statut WHERE class_id = :class_id";
        
        $success = db_execute($sql, [
            'statut' => CLASSE_ACTIVE,
            'class_id' => $class_id
        ]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Classe activée', [
                'class_id' => $class_id,
                'libelle' => $classe['libelle'],
                'niveau' => $classe['nom_niveau'],
                'section' => $classe['nom_section'],
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'classes');
            
            return [
                'success' => true,
                'message' => 'Classe activée avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de l\'activation'];
        
    } catch (Exception $e) {
        error_log("Erreur classe_activer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir une classe par son ID
 */
function classe_get_by_id(int $class_id): ?array
{
    $sql = "SELECT c.*, 
                   n.nom_niveau,
                   s.nom_section,
                   s.couleur as section_couleur,
                   a.annee_libelle,
                   a.statut as annee_statut,
                   p.nom as tuteur_nom, p.prenom as tuteur_prenom,
                   p.matricule_prof as tuteur_matricule
            FROM classes c
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            JOIN annees_scolaire a ON c.annee_id = a.annee_id
            LEFT JOIN professeurs p ON c.tuteur_id = p.professeur_id
            WHERE c.class_id = :class_id";
    
    $classe = db_query_single($sql, ['class_id' => $class_id]);
    
    if ($classe) {
        // Ajouter des informations calculées
        $classe['nom_complet'] = $classe['libelle'] . ' - ' . $classe['nom_niveau'];
        if ($classe['nom_section']) {
            $classe['nom_complet'] .= ' ' . $classe['nom_section'];
        }
        
        // Ajouter le tuteur complet
        if ($classe['tuteur_nom']) {
            $classe['tuteur_nom_complet'] = $classe['tuteur_prenom'] . ' ' . $classe['tuteur_nom'];
        }
        
        // Ajouter les statistiques
        $classe['statistiques'] = classe_get_statistiques($class_id);
        
        // Ajouter la liste des élèves
        $classe['eleves'] = classe_get_eleves($class_id);
        
        // Ajouter l'emploi du temps
        $classe['emploi_du_temps'] = classe_get_emploi_du_temps($class_id);
    }
    
    return $classe;
}

/**
 * Rechercher des classes avec filtres
 */
function classe_rechercher(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    $offset = ($page - 1) * $par_page;
    
    // Construction de la requête de base
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   c.*,
                   n.nom_niveau,
                   s.nom_section,
                   s.couleur as section_couleur,
                   a.annee_libelle,
                   a.statut as annee_statut,
                   p.nom as tuteur_nom, p.prenom as tuteur_prenom
            FROM classes c
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            JOIN annees_scolaire a ON c.annee_id = a.annee_id
            LEFT JOIN professeurs p ON c.tuteur_id = p.professeur_id
            WHERE 1=1";
    
    $params = [];
    
    // Filtres de recherche
    if (!empty($filtres['recherche'])) {
        $termes = explode(' ', trim($filtres['recherche']));
        $conditions = [];
        
        foreach ($termes as $index => $terme) {
            $key = "recherche_{$index}";
            $conditions[] = "(c.libelle LIKE :{$key} OR n.nom_niveau LIKE :{$key} OR s.nom_section LIKE :{$key})";
            $params[$key] = "%{$terme}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    }
    
    // Filtre par statut
    if (!empty($filtres['statut'])) {
        $sql .= " AND c.statut = :statut";
        $params['statut'] = $filtres['statut'];
    }
    
    // Filtre par année scolaire
    if (!empty($filtres['annee_id'])) {
        $sql .= " AND c.annee_id = :annee_id";
        $params['annee_id'] = $filtres['annee_id'];
    }
    
    // Filtre par niveau
    if (!empty($filtres['niveau_id'])) {
        $sql .= " AND c.niveau_id = :niveau_id";
        $params['niveau_id'] = $filtres['niveau_id'];
    }
    
    // Filtre par section
    if (!empty($filtres['section_id'])) {
        $sql .= " AND c.section_id = :section_id";
        $params['section_id'] = $filtres['section_id'];
    }
    
    // Filtre par tuteur
    if (!empty($filtres['tuteur_id'])) {
        $sql .= " AND c.tuteur_id = :tuteur_id";
        $params['tuteur_id'] = $filtres['tuteur_id'];
    }
    
    // Filtre par salle
    if (!empty($filtres['salle'])) {
        $sql .= " AND c.salle LIKE :salle";
        $params['salle'] = "%{$filtres['salle']}%";
    }
    
    // Exclure les classes inactives par défaut
    if (!isset($filtres['inclure_inactives']) || !$filtres['inclure_inactives']) {
        $sql .= " AND c.statut = '" . CLASSE_ACTIVE . "'";
    }
    
    // Exclure les années inactives par défaut
    if (!isset($filtres['inclure_annees_inactives']) || !$filtres['inclure_annees_inactives']) {
        $sql .= " AND a.statut = 'active'";
    }
    
    // Ordre de tri
    $order_by = $filtres['order_by'] ?? 'n.nom_niveau, c.libelle';
    $order_dir = isset($filtres['order_dir']) && strtoupper($filtres['order_dir']) === 'DESC' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY {$order_by} {$order_dir}";
    
    // Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params['limit'] = $par_page;
    $params['offset'] = $offset;
    
    try {
        // Exécuter la requête
        $classes = db_query($sql, $params);
        
        // Obtenir le nombre total
        $total_result = db_query_single("SELECT FOUND_ROWS() as total");
        $total = $total_result['total'] ?? 0;
        
        // Calculer les informations de pagination
        $total_pages = ceil($total / $par_page);
        
        // Ajouter des informations supplémentaires
        foreach ($classes as &$classe) {
            $classe['nom_complet'] = $classe['libelle'] . ' - ' . $classe['nom_niveau'];
            if ($classe['nom_section']) {
                $classe['nom_complet'] .= ' ' . $classe['nom_section'];
            }
            
            if ($classe['tuteur_nom']) {
                $classe['tuteur_nom_complet'] = $classe['tuteur_prenom'] . ' ' . $classe['tuteur_nom'];
            }
            
            // Ajouter le nombre d'élèves
            $nombre_eleves = classe_get_nombre_eleves($classe['class_id']);
            $classe['nombre_eleves'] = $nombre_eleves;
            
            // Calculer le taux d'occupation
            if ($classe['capacite_max'] > 0) {
                $classe['taux_occupation'] = round(($nombre_eleves / $classe['capacite_max']) * 100, 1);
                $classe['places_disponibles'] = $classe['capacite_max'] - $nombre_eleves;
            } else {
                $classe['taux_occupation'] = 0;
                $classe['places_disponibles'] = '∞';
            }
        }
        
        return [
            'success' => true,
            'classes' => $classes,
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
        error_log("Erreur classe_rechercher: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la recherche'];
    }
}

/**
 * Obtenir les classes actives pour une année scolaire
 */
function classe_get_actives_par_annee(int $annee_id): array
{
    $sql = "SELECT c.*, 
                   n.nom_niveau,
                   s.nom_section,
                   s.couleur as section_couleur
            FROM classes c
            JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            WHERE c.annee_id = :annee_id
            AND c.statut = :statut_actif
            ORDER BY n.nom_niveau, c.libelle";
    
    $classes = db_query($sql, [
        'annee_id' => $annee_id,
        'statut_actif' => CLASSE_ACTIVE
    ]);
    
    // Ajouter des informations supplémentaires
    foreach ($classes as &$classe) {
        $classe['nom_complet'] = $classe['libelle'] . ' - ' . $classe['nom_niveau'];
        if ($classe['nom_section']) {
            $classe['nom_complet'] .= ' ' . $classe['nom_section'];
        }
        
        // Nombre d'élèves
        $nombre_eleves = classe_get_nombre_eleves($classe['class_id']);
        $classe['nombre_eleves'] = $nombre_eleves;
    }
    
    return $classes;
}

// =============================================
// FONCTIONS DE GESTION DES ÉLÈVES DANS LES CLASSES
// =============================================

/**
 * Obtenir les élèves d'une classe
 */
function classe_get_eleves(int $class_id, bool $actifs_seulement = true): array
{
    $sql = "SELECT e.*, 
                   a.date_admission,
                   a.statut_admission
            FROM admissions a
            JOIN eleves e ON a.eleve_id = e.eleve_id
            WHERE a.class_id = :class_id";
    
    if ($actifs_seulement) {
        $sql .= " AND a.statut_admission = :statut_approuve";
    }
    
    $sql .= " ORDER BY e.nom, e.prenom";
    
    $params = ['class_id' => $class_id];
    if ($actifs_seulement) {
        $params['statut_approuve'] = ADMISSION_APPROUVE;
    }
    
    $eleves = db_query($sql, $params);
    
    // Ajouter des informations supplémentaires
    foreach ($eleves as &$eleve) {
        $eleve['nom_complet'] = $eleve['prenom'] . ' ' . $eleve['nom'] . ' ' . $eleve['post_nom'];
        
        // Calculer l'âge
        if ($eleve['date_naissance']) {
            $date_naissance = new DateTime($eleve['date_naissance']);
            $aujourdhui = new DateTime();
            $eleve['age'] = $aujourdhui->diff($date_naissance)->y;
        }
        
        // Ajouter les parents
        $eleve['parents'] = eleve_get_parents($eleve['eleve_id']);
    }
    
    return $eleves;
}

/**
 * Obtenir le nombre d'élèves dans une classe
 */
function classe_get_nombre_eleves(int $class_id): int
{
    $result = db_query_single(
        "SELECT COUNT(*) as count 
         FROM admissions 
         WHERE class_id = :class_id 
         AND statut_admission = :statut_approuve",
        ['class_id' => $class_id, 'statut_approuve' => ADMISSION_APPROUVE]
    );
    
    return $result ? (int)$result['count'] : 0;
}

/**
 * Transférer un élève d'une classe à une autre
 */
function classe_transferer_eleve(int $eleve_id, int $nouvelle_class_id, string $motif = ''): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'élève existe
    $eleve = eleve_get_by_id($eleve_id);
    if (!$eleve) {
        return ['success' => false, 'error' => 'Élève non trouvé'];
    }
    
    // Vérifier que la nouvelle classe existe
    $nouvelle_classe = classe_get_by_id($nouvelle_class_id);
    if (!$nouvelle_classe) {
        return ['success' => false, 'error' => 'Nouvelle classe non trouvée'];
    }
    
    // Vérifier que la nouvelle classe est active
    if ($nouvelle_classe['statut'] !== CLASSE_ACTIVE) {
        return ['success' => false, 'error' => 'La nouvelle classe n\'est pas active'];
    }
    
    // Vérifier la capacité de la nouvelle classe
    $capacite = verifier_capacite_classe($nouvelle_class_id);
    if (!$capacite['disponible']) {
        return [
            'success' => false,
            'error' => "Classe au complet. Places disponibles: {$capacite['places_disponibles']}"
        ];
    }
    
    // Récupérer l'admission actuelle de l'élève
    $admission_actuelle = admission_get_actuelle($eleve_id);
    if (!$admission_actuelle) {
        return ['success' => false, 'error' => 'L\'élève n\'a pas d\'admission active'];
    }
    
    $ancienne_class_id = $admission_actuelle['class_id'];
    
    // Vérifier que ce n'est pas la même classe
    if ($ancienne_class_id == $nouvelle_class_id) {
        return ['success' => false, 'error' => 'L\'élève est déjà dans cette classe'];
    }
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        // Clôturer l'ancienne admission
        $sql_cloturer = "UPDATE admissions 
                        SET statut_admission = :statut_rejete,
                            decision_comite = :motif_cloture
                        WHERE eleve_id = :eleve_id 
                        AND class_id = :ancienne_class_id
                        AND statut_admission = :statut_approuve";
        
        $success_cloturer = db_execute($sql_cloturer, [
            'statut_rejete' => ADMISSION_REJETE,
            'motif_cloture' => "Transfert vers classe #{$nouvelle_class_id}. " . $motif,
            'eleve_id' => $eleve_id,
            'ancienne_class_id' => $ancienne_class_id,
            'statut_approuve' => ADMISSION_APPROUVE
        ]);
        
        if (!$success_cloturer) {
            throw new Exception("Échec de la clôture de l'ancienne admission");
        }
        
        // Créer une nouvelle admission dans la nouvelle classe
        $donnees_admission = [
            'eleve_id' => $eleve_id,
            'class_id' => $nouvelle_class_id,
            'annee_id' => $nouvelle_classe['annee_id'],
            'statut_admission' => ADMISSION_APPROUVE,
            'notes_entretien' => "Transfert depuis classe #{$ancienne_class_id}. " . $motif
        ];
        
        $resultat_admission = admission_creer($donnees_admission);
        
        if (!$resultat_admission['success']) {
            throw new Exception("Échec de la création de la nouvelle admission: " . 
                ($resultat_admission['error'] ?? ''));
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        $ancienne_classe = classe_get_by_id($ancienne_class_id);
        
        log_action('Élève transféré de classe', [
            'eleve_id' => $eleve_id,
            'eleve_nom' => $eleve['nom_complet'],
            'ancienne_classe_id' => $ancienne_class_id,
            'ancienne_classe' => $ancienne_classe ? $ancienne_classe['nom_complet'] : 'Inconnue',
            'nouvelle_classe_id' => $nouvelle_class_id,
            'nouvelle_classe' => $nouvelle_classe['nom_complet'],
            'motif' => $motif,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'classes');
        
        return [
            'success' => true,
            'message' => 'Élève transféré avec succès',
            'ancienne_classe' => $ancienne_classe ? $ancienne_classe['nom_complet'] : 'Inconnue',
            'nouvelle_classe' => $nouvelle_classe['nom_complet']
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur classe_transferer_eleve: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du transfert'];
    }
}

/**
 * Transférer plusieurs élèves en masse
 */
function classe_transferer_masse(array $eleve_ids, int $nouvelle_class_id, string $motif = ''): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la nouvelle classe existe
    $nouvelle_classe = classe_get_by_id($nouvelle_class_id);
    if (!$nouvelle_classe) {
        return ['success' => false, 'error' => 'Nouvelle classe non trouvée'];
    }
    
    // Vérifier que la nouvelle classe est active
    if ($nouvelle_classe['statut'] !== CLASSE_ACTIVE) {
        return ['success' => false, 'error' => 'La nouvelle classe n\'est pas active'];
    }
    
    // Vérifier la capacité de la nouvelle classe
    $capacite = verifier_capacite_classe($nouvelle_class_id);
    if ($capacite['capacite_max'] > 0 && count($eleve_ids) > $capacite['places_disponibles']) {
        return [
            'success' => false,
            'error' => "Pas assez de places. Places disponibles: {$capacite['places_disponibles']}, " .
                      "Élèves à transférer: " . count($eleve_ids)
        ];
    }
    
    $resultats = [
        'succes' => 0,
        'echecs' => 0,
        'details' => []
    ];
    
    foreach ($eleve_ids as $eleve_id) {
        $resultat = classe_transferer_eleve($eleve_id, $nouvelle_class_id, $motif);
        
        if ($resultat['success']) {
            $resultats['succes']++;
            $resultats['details'][] = [
                'eleve_id' => $eleve_id,
                'success' => true,
                'message' => $resultat['message']
            ];
        } else {
            $resultats['echecs']++;
            $resultats['details'][] = [
                'eleve_id' => $eleve_id,
                'success' => false,
                'error' => $resultat['error']
            ];
        }
    }
    
    // Journaliser l'action en masse
    log_action('Transfert masse d\'élèves', [
        'nouvelle_classe_id' => $nouvelle_class_id,
        'nouvelle_classe' => $nouvelle_classe['nom_complet'],
        'nombre_eleves' => count($eleve_ids),
        'succes' => $resultats['succes'],
        'echecs' => $resultats['echecs'],
        'motif' => $motif,
        'par_utilisateur' => $_SESSION['user_id'] ?? null
    ], 'classes');
    
    return [
        'success' => true,
        'resultats' => $resultats,
        'message' => "Transfert terminé: {$resultats['succes']} succès, {$resultats['echecs']} échecs"
    ];
}

// =============================================
// FONCTIONS DE GESTION DES TUTEURS
// =============================================

/**
 * Définir un tuteur (professeur principal) pour une classe
 */
function classe_set_tuteur(int $class_id, int $professeur_id): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Vérifier que le professeur existe
    $professeur = db_query_single(
        "SELECT professeur_id, nom, prenom FROM professeurs WHERE professeur_id = :professeur_id",
        ['professeur_id' => $professeur_id]
    );
    
    if (!$professeur) {
        return ['success' => false, 'error' => 'Professeur non trouvé'];
    }
    
    // Vérifier que le professeur n'est pas déjà tuteur d'une autre classe pour la même année
    $tuteur_existant = db_query_single(
        "SELECT c.class_id, c.libelle 
         FROM classes c
         WHERE c.tuteur_id = :professeur_id 
         AND c.annee_id = :annee_id 
         AND c.class_id != :class_id",
        [
            'professeur_id' => $professeur_id,
            'annee_id' => $classe['annee_id'],
            'class_id' => $class_id
        ]
    );
    
    if ($tuteur_existant) {
        return [
            'success' => false,
            'error' => "Ce professeur est déjà tuteur de la classe {$tuteur_existant['libelle']} pour cette année"
        ];
    }
    
    try {
        // Mettre à jour le tuteur
        $sql = "UPDATE classes SET tuteur_id = :professeur_id WHERE class_id = :class_id";
        
        $success = db_execute($sql, [
            'professeur_id' => $professeur_id,
            'class_id' => $class_id
        ]);
        
        if ($success) {
            // Journaliser l'action
            $professeur_nom = $professeur['prenom'] . ' ' . $professeur['nom'];
            
            log_action('Tuteur défini pour classe', [
                'class_id' => $class_id,
                'classe' => $classe['nom_complet'],
                'professeur_id' => $professeur_id,
                'professeur_nom' => $professeur_nom,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'classes');
            
            return [
                'success' => true,
                'message' => "Tuteur défini: $professeur_nom",
                'professeur_nom' => $professeur_nom
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la définition du tuteur'];
        
    } catch (Exception $e) {
        error_log("Erreur classe_set_tuteur: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Retirer le tuteur d'une classe
 */
function classe_remove_tuteur(int $class_id): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la classe existe
    $classe = classe_get_by_id($class_id);
    if (!$classe) {
        return ['success' => false, 'error' => 'Classe non trouvée'];
    }
    
    // Vérifier que la classe a un tuteur
    if (!$classe['tuteur_id']) {
        return ['success' => false, 'error' => 'Cette classe n\'a pas de tuteur'];
    }
    
    try {
        // Retirer le tuteur
        $sql = "UPDATE classes SET tuteur_id = NULL WHERE class_id = :class_id";
        
        $success = db_execute($sql, ['class_id' => $class_id]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Tuteur retiré de classe', [
                'class_id' => $class_id,
                'classe' => $classe['nom_complet'],
                'ancien_tuteur' => $classe['tuteur_nom_complet'] ?? 'Inconnu',
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'classes');
            
            return [
                'success' => true,
                'message' => 'Tuteur retiré avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors du retrait du tuteur'];
        
    } catch (Exception $e) {
        error_log("Erreur classe_remove_tuteur: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

// =============================================
// FONCTIONS DE GESTION DE L'EMPLOI DU TEMPS
// =============================================

/**
 * Obtenir l'emploi du temps d'une classe
 */
function classe_get_emploi_du_temps(int $class_id, int $annee_id = null, string $periode = null): array
{
    // Si l'année n'est pas spécifiée, utiliser l'année de la classe
    if ($annee_id === null) {
        $classe = db_query_single(
            "SELECT annee_id FROM classes WHERE class_id = :class_id",
            ['class_id' => $class_id]
        );
        $annee_id = $classe ? $classe['annee_id'] : 0;
    }
    
    $sql = "SELECT e.*,
                   m.nom_matiere,
                   m.code_matiere,
                   m.couleur as matiere_couleur,
                   p.nom as professeur_nom,
                   p.prenom as professeur_prenom,
                   p.matricule_prof
            FROM emploi_du_temps e
            JOIN matieres m ON e.matiere_id = m.matiere_id
            JOIN professeurs p ON e.professeur_id = p.professeur_id
            WHERE e.class_id = :class_id
            AND e.annee_id = :annee_id";
    
    $params = [
        'class_id' => $class_id,
        'annee_id' => $annee_id
    ];
    
    if ($periode !== null) {
        $sql .= " AND e.periode = :periode";
        $params['periode'] = $periode;
    }
    
    $sql .= " AND e.statut = 'actif'
              ORDER BY 
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
    $emploi_du_temps = [
        'Lundi' => [],
        'Mardi' => [],
        'Mercredi' => [],
        'Jeudi' => [],
        'Vendredi' => [],
        'Samedi' => []
    ];
    
    foreach ($cours as $cours_item) {
        $jour = $cours_item['jour_semaine'];
        $cours_item['professeur_nom_complet'] = $cours_item['professeur_prenom'] . ' ' . $cours_item['professeur_nom'];
        $emploi_du_temps[$jour][] = $cours_item;
    }
    
    return $emploi_du_temps;
}

// =============================================
// FONCTIONS DE STATISTIQUES
// =============================================

/**
 * Obtenir les statistiques d'une classe
 */
function classe_get_statistiques(int $class_id): array
{
    $statistiques = [];
    
    try {
        // Nombre d'élèves
        $nombre_eleves = classe_get_nombre_eleves($class_id);
        $statistiques['nombre_eleves'] = $nombre_eleves;
        
        // Répartition par genre
        $par_genre = db_query(
            "SELECT e.genre, COUNT(*) as count
             FROM admissions a
             JOIN eleves e ON a.eleve_id = e.eleve_id
             WHERE a.class_id = :class_id
             AND a.statut_admission = :statut_approuve
             GROUP BY e.genre",
            ['class_id' => $class_id, 'statut_approuve' => ADMISSION_APPROUVE]
        );
        $statistiques['par_genre'] = $par_genre;
        
        // Âge moyen
        $age_moyen = db_query_single(
            "SELECT AVG(TIMESTAMPDIFF(YEAR, e.date_naissance, CURDATE())) as age_moyen
             FROM admissions a
             JOIN eleves e ON a.eleve_id = e.eleve_id
             WHERE a.class_id = :class_id
             AND a.statut_admission = :statut_approuve",
            ['class_id' => $class_id, 'statut_approuve' => ADMISSION_APPROUVE]
        );
        $statistiques['age_moyen'] = round($age_moyen['age_moyen'] ?? 0, 1);
        
        // Nombre de matières enseignées
        $nombre_matieres = db_query_single(
            "SELECT COUNT(DISTINCT e.matiere_id) as count
             FROM emploi_du_temps e
             WHERE e.class_id = :class_id
             AND e.statut = 'actif'",
            ['class_id' => $class_id]
        );
        $statistiques['nombre_matieres'] = $nombre_matieres['count'] ?? 0;
        
        // Nombre de professeurs
        $nombre_professeurs = db_query_single(
            "SELECT COUNT(DISTINCT e.professeur_id) as count
             FROM emploi_du_temps e
             WHERE e.class_id = :class_id
             AND e.statut = 'actif'",
            ['class_id' => $class_id]
        );
        $statistiques['nombre_professeurs'] = $nombre_professeurs['count'] ?? 0;
        
        // Heures de cours par semaine
        $heures_semaine = db_query_single(
            "SELECT SUM(TIMESTAMPDIFF(HOUR, e.heure_debut, e.heure_fin)) as total_heures
             FROM emploi_du_temps e
             WHERE e.class_id = :class_id
             AND e.statut = 'actif'
             AND e.jour_semaine IN ('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi')",
            ['class_id' => $class_id]
        );
        $statistiques['heures_semaine'] = $heures_semaine['total_heures'] ?? 0;
        
        // Taux d'occupation
        $classe = db_query_single(
            "SELECT capacite_max FROM classes WHERE class_id = :class_id",
            ['class_id' => $class_id]
        );
        
        if ($classe && $classe['capacite_max'] > 0) {
            $statistiques['taux_occupation'] = round(($nombre_eleves / $classe['capacite_max']) * 100, 1);
            $statistiques['places_disponibles'] = $classe['capacite_max'] - $nombre_eleves;
        } else {
            $statistiques['taux_occupation'] = 0;
            $statistiques['places_disponibles'] = '∞';
        }
        
        // Dernières admissions
        $dernieres_admissions = db_query(
            "SELECT e.prenom, e.nom, e.post_nom, a.date_admission
             FROM admissions a
             JOIN eleves e ON a.eleve_id = e.eleve_id
             WHERE a.class_id = :class_id
             AND a.statut_admission = :statut_approuve
             ORDER BY a.date_admission DESC
             LIMIT 5",
            ['class_id' => $class_id, 'statut_approuve' => ADMISSION_APPROUVE]
        );
        $statistiques['dernieres_admissions'] = $dernieres_admissions;
        
        return $statistiques;
        
    } catch (Exception $e) {
        error_log("Erreur classe_get_statistiques: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtenir les statistiques globales des classes
 */
function classe_get_statistiques_globales(int $annee_id = null): array
{
    // Si l'année n'est pas spécifiée, utiliser l'année active
    if ($annee_id === null) {
        $annee_active = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE statut = 'active' LIMIT 1"
        );
        $annee_id = $annee_active ? $annee_active['annee_id'] : 0;
    }
    
    try {
        $stats = [];
        
        // Nombre total de classes
        $total_classes = db_query_single(
            "SELECT COUNT(*) as count FROM classes WHERE annee_id = :annee_id AND statut = :statut_actif",
            ['annee_id' => $annee_id, 'statut_actif' => CLASSE_ACTIVE]
        );
        $stats['total_classes'] = $total_classes['count'] ?? 0;
        
        // Nombre total d'élèves dans toutes les classes
        $total_eleves = db_query_single(
            "SELECT COUNT(*) as count
             FROM admissions a
             JOIN classes c ON a.class_id = c.class_id
             WHERE c.annee_id = :annee_id
             AND c.statut = :statut_actif
             AND a.statut_admission = :statut_approuve",
            [
                'annee_id' => $annee_id,
                'statut_actif' => CLASSE_ACTIVE,
                'statut_approuve' => ADMISSION_APPROUVE
            ]
        );
        $stats['total_eleves'] = $total_eleves['count'] ?? 0;
        
        // Classes par niveau
        $classes_par_niveau = db_query(
            "SELECT n.nom_niveau, COUNT(c.class_id) as nombre_classes,
                    SUM(CASE WHEN a.statut_admission = :statut_approuve THEN 1 ELSE 0 END) as nombre_eleves
             FROM classes c
             JOIN niveau n ON c.niveau_id = n.niveau_id
             LEFT JOIN admissions a ON c.class_id = a.class_id
             WHERE c.annee_id = :annee_id
             AND c.statut = :statut_actif
             GROUP BY n.niveau_id, n.nom_niveau
             ORDER BY n.nom_niveau",
            [
                'annee_id' => $annee_id,
                'statut_actif' => CLASSE_ACTIVE,
                'statut_approuve' => ADMISSION_APPROUVE
            ]
        );
        $stats['par_niveau'] = $classes_par_niveau;
        
        // Classes par section
        $classes_par_section = db_query(
            "SELECT s.nom_section, COUNT(c.class_id) as nombre_classes,
                    SUM(CASE WHEN a.statut_admission = :statut_approuve THEN 1 ELSE 0 END) as nombre_eleves
             FROM classes c
             LEFT JOIN sections s ON c.section_id = s.section_id
             LEFT JOIN admissions a ON c.class_id = a.class_id
             WHERE c.annee_id = :annee_id
             AND c.statut = :statut_actif
             GROUP BY s.section_id, s.nom_section
             ORDER BY s.nom_section",
            [
                'annee_id' => $annee_id,
                'statut_actif' => CLASSE_ACTIVE,
                'statut_approuve' => ADMISSION_APPROUVE
            ]
        );
        $stats['par_section'] = $classes_par_section;
        
        // Capacité moyenne et taux d'occupation
        $capacite_stats = db_query_single(
            "SELECT 
                AVG(c.capacite_max) as capacite_moyenne,
                SUM(c.capacite_max) as capacite_totale,
                SUM(CASE WHEN a.statut_admission = :statut_approuve THEN 1 ELSE 0 END) as eleves_totaux
             FROM classes c
             LEFT JOIN admissions a ON c.class_id = a.class_id
             WHERE c.annee_id = :annee_id
             AND c.statut = :statut_actif
             AND c.capacite_max > 0",
            [
                'annee_id' => $annee_id,
                'statut_actif' => CLASSE_ACTIVE,
                'statut_approuve' => ADMISSION_APPROUVE
            ]
        );
        
        $stats['capacite_moyenne'] = round($capacite_stats['capacite_moyenne'] ?? 0, 1);
        $stats['capacite_totale'] = $capacite_stats['capacite_totale'] ?? 0;
        $stats['eleves_totaux'] = $capacite_stats['eleves_totaux'] ?? 0;
        
        if ($stats['capacite_totale'] > 0) {
            $stats['taux_occupation_global'] = round(($stats['eleves_totaux'] / $stats['capacite_totale']) * 100, 1);
            $stats['places_disponibles_global'] = $stats['capacite_totale'] - $stats['eleves_totaux'];
        } else {
            $stats['taux_occupation_global'] = 0;
            $stats['places_disponibles_global'] = 0;
        }
        
        // Classes complètes (à plus de 90%)
        $classes_completes = db_query(
            "SELECT c.libelle, c.capacite_max,
                    COUNT(a.admission_id) as nombre_eleves,
                    ROUND((COUNT(a.admission_id) / c.capacite_max) * 100, 1) as taux_occupation
             FROM classes c
             LEFT JOIN admissions a ON c.class_id = a.class_id AND a.statut_admission = :statut_approuve
             WHERE c.annee_id = :annee_id
             AND c.statut = :statut_actif
             AND c.capacite_max > 0
             GROUP BY c.class_id, c.libelle, c.capacite_max
             HAVING (COUNT(a.admission_id) / c.capacite_max) >= 0.9
             ORDER BY taux_occupation DESC",
            [
                'annee_id' => $annee_id,
                'statut_actif' => CLASSE_ACTIVE,
                'statut_approuve' => ADMISSION_APPROUVE
            ]
        );
        $stats['classes_completes'] = $classes_completes;
        
        return [
            'success' => true,
            'statistiques' => $stats
        ];
        
    } catch (Exception $e) {
        error_log("Erreur classe_get_statistiques_globales: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du calcul des statistiques'];
    }
}

// =============================================
// FONCTIONS D'EXPORT ET RAPPORTS
// =============================================

/**
 * Générer la liste des classes
 */
function classe_generer_liste(int $annee_id = null): array
{
    // Si l'année n'est pas spécifiée, utiliser l'année active
    if ($annee_id === null) {
        $annee_active = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE statut = 'active' LIMIT 1"
        );
        $annee_id = $annee_active ? $annee_active['annee_id'] : 0;
    }
    
    $classes = classe_get_actives_par_annee($annee_id);
    
    // Ajouter des informations détaillées pour chaque classe
    foreach ($classes as &$classe) {
        // Élèves avec informations de contact
        $eleves = classe_get_eleves($classe['class_id']);
        
        // Ajouter les informations des parents pour chaque élève
        foreach ($eleves as &$eleve) {
            $eleve['parents'] = parent_get_contacts_urgence($eleve['eleve_id']);
        }
        
        $classe['eleves_detaille'] = $eleves;
        
        // Emploi du temps
        $classe['emploi_du_temps'] = classe_get_emploi_du_temps($classe['class_id'], $annee_id);
        
        // Tuteur avec informations de contact
        if ($classe['tuteur_id']) {
            $tuteur = db_query_single(
                "SELECT p.*, u.email, u.telephone
                 FROM professeurs p
                 LEFT JOIN user_admins u ON p.user_id = u.user_id
                 WHERE p.professeur_id = :tuteur_id",
                ['tuteur_id' => $classe['tuteur_id']]
            );
            
            if ($tuteur) {
                $tuteur['nom_complet'] = $tuteur['prenom'] . ' ' . $tuteur['nom'];
                $classe['tuteur_detaille'] = $tuteur;
            }
        }
    }
    
    // Récupérer les informations de l'année scolaire
    $annee = db_query_single(
        "SELECT * FROM annees_scolaire WHERE annee_id = :annee_id",
        ['annee_id' => $annee_id]
    );
    
    return [
        'success' => true,
        'classes' => $classes,
        'annee_scolaire' => $annee,
        'date_generation' => date('Y-m-d H:i:s'),
        'nombre_classes' => count($classes)
    ];
}

/**
 * Exporter la liste des classes
 */
function classe_exporter(int $annee_id = null, string $format = 'pdf'): array
{
    // Vérifier les permissions
    if (!check_access('classes', 'export')) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        // Générer la liste des classes
        $resultat = classe_generer_liste($annee_id);
        
        if (!$resultat['success']) {
            return $resultat;
        }
        
        $classes = $resultat['classes'];
        $annee_scolaire = $resultat['annee_scolaire'];
        
        // Générer l'export selon le format
        switch (strtolower($format)) {
            case 'pdf':
                $export = classe_generate_pdf($classes, $annee_scolaire);
                $extension = 'pdf';
                $mime_type = 'application/pdf';
                break;
                
            case 'excel':
                $export = classe_generate_excel($classes, $annee_scolaire);
                $extension = 'xlsx';
                $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
                
            default:
                return ['success' => false, 'error' => 'Format non supporté'];
        }
        
        // Journaliser l'export
        log_action('Export classes', [
            'format' => $format,
            'annee_id' => $annee_id,
            'nombre_classes' => count($classes),
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'classes');
        
        return [
            'success' => true,
            'data' => $export,
            'format' => $format,
            'extension' => $extension,
            'mime_type' => $mime_type,
            'filename' => 'classes_' . ($annee_scolaire['annee_libelle'] ?? date('Y')) . '_' . date('Ymd_His') . '.' . $extension,
            'count' => count($classes)
        ];
        
    } catch (Exception $e) {
        error_log("Erreur classe_exporter: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'export'];
    }
}

/**
 * Générer un PDF des classes
 */
function classe_generate_pdf(array $classes, array $annee_scolaire): string
{
    // En production, utiliser TCPDF ou Dompdf
    // Ici, on génère un HTML simple
    
    $html = '<html><head><meta charset="UTF-8">';
    $html .= '<title>Liste des Classes - ' . ($annee_scolaire['annee_libelle'] ?? '') . '</title>';
    $html .= '<style>';
    $html .= 'body{font-family:Arial;margin:20px;line-height:1.6}';
    $html .= 'h1,h2,h3{color:#333}';
    $html .= 'table{border-collapse:collapse;width:100%;margin-bottom:20px}';
    $html .= 'th,td{border:1px solid #ddd;padding:8px;text-align:left}';
    $html .= 'th{background-color:#f2f2f2;font-weight:bold}';
    $html .= '.classe-header{background-color:#e8f4f8;padding:10px;margin-bottom:15px;border-left:4px solid #3498db}';
    $html .= '.eleve-row:nth-child(even){background-color:#f9f9f9}';
    $html .= '.section{color:#fff;padding:2px 6px;border-radius:3px;font-size:0.9em}';
    $html .= '</style></head><body>';
    
    $html .= '<h1>Liste des Classes</h1>';
    $html .= '<h2>' . e($annee_scolaire['annee_libelle'] ?? 'Année scolaire') . '</h2>';
    $html .= '<p>Établissement: ' . e(ECOLE_NOM) . '</p>';
    $html .= '<p>Généré le ' . date('d/m/Y à H:i') . '</p>';
    $html .= '<p>Total: ' . count($classes) . ' classe(s)</p>';
    
    foreach ($classes as $classe) {
        $html .= '<div class="classe-header">';
        $html .= '<h3>' . e($classe['nom_complet']) . '</h3>';
        $html .= '<p>';
        $html .= 'Salle: ' . e($classe['salle'] ?? 'Non spécifiée') . ' | ';
        $html .= 'Capacité: ' . ($classe['capacite_max'] ?? 'Illimitée') . ' | ';
        $html .= 'Élèves: ' . $classe['nombre_eleves'];
        
        if ($classe['capacite_max'] > 0) {
            $html .= ' (' . ($classe['taux_occupation'] ?? 0) . '%)';
        }
        
        if ($classe['tuteur_nom_complet'] ?? '') {
            $html .= ' | Tuteur: ' . e($classe['tuteur_nom_complet']);
        }
        
        $html .= '</p>';
        $html .= '</div>';
        
        if (!empty($classe['eleves_detaille'])) {
            $html .= '<h4>Élèves (' . count($classe['eleves_detaille']) . ')</h4>';
            $html .= '<table>';
            $html .= '<tr><th>Matricule</th><th>Nom</th><th>Date Naiss.</th><th>Genre</th><th>Téléphone</th><th>Parents</th></tr>';
            
            foreach ($classe['eleves_detaille'] as $eleve) {
                $html .= '<tr class="eleve-row">';
                $html .= '<td>' . e($eleve['matricule']) . '</td>';
                $html .= '<td>' . e($eleve['nom_complet']) . '</td>';
                $html .= '<td>' . format_date($eleve['date_naissance']) . '</td>';
                $html .= '<td>' . e($eleve['genre']) . '</td>';
                $html .= '<td>' . e($eleve['telephone'] ?? '') . '</td>';
                
                // Parents
                $parents_html = '';
                if (!empty($eleve['parents'])) {
                    foreach ($eleve['parents'] as $parent) {
                        if (!empty($parents_html)) $parents_html .= '<br>';
                        $parents_html .= e($parent['nom_complet']) . ' (' . e($parent['relation']) . ')';
                        if ($parent['est_responsable']) {
                            $parents_html .= ' ★';
                        }
                    }
                }
                $html .= '<td>' . $parents_html . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</table>';
        } else {
            $html .= '<p>Aucun élève dans cette classe.</p>';
        }
        
        $html .= '<hr style="margin:30px 0">';
    }
    
    $html .= '</body></html>';
    
    return $html;
}

/**
 * Générer un Excel des classes
 */
function classe_generate_excel(array $classes, array $annee_scolaire): string
{
    // En production, utiliser PhpSpreadsheet
    // Pour l'instant, on génère un CSV structuré
    
    $output = fopen('php://temp', 'r+');
    
    // En-tête du document
    fputcsv($output, ["Liste des Classes - " . ($annee_scolaire['annee_libelle'] ?? '')], ';');
    fputcsv($output, ["Établissement: " . ECOLE_NOM], ';');
    fputcsv($output, ["Date de génération: " . date('d/m/Y à H:i')], ';');
    fputcsv($output, ["Total classes: " . count($classes)], ';');
    fputcsv($output, [], ';'); // Ligne vide
    
    foreach ($classes as $classe) {
        // En-tête de la classe
        fputcsv($output, ["Classe: " . $classe['nom_complet']], ';');
        fputcsv($output, [
            "Salle: " . ($classe['salle'] ?? ''),
            "Capacité: " . ($classe['capacite_max'] ?? 'Illimitée'),
            "Élèves: " . $classe['nombre_eleves'],
            "Tuteur: " . ($classe['tuteur_nom_complet'] ?? '')
        ], ';');
        
        // En-tête des élèves
        fputcsv($output, [], ';');
        fputcsv($output, [
            'Matricule', 'Nom', 'Prénom', 'Date Naissance', 'Genre', 
            'Téléphone', 'Email', 'Parent Responsable', 'Téléphone Parent'
        ], ';');
        
        // Données des élèves
        if (!empty($classe['eleves_detaille'])) {
            foreach ($classe['eleves_detaille'] as $eleve) {
                // Trouver le parent responsable
                $parent_responsable = '';
                $telephone_parent = '';
                
                if (!empty($eleve['parents'])) {
                    foreach ($eleve['parents'] as $parent) {
                        if ($parent['est_responsable']) {
                            $parent_responsable = $parent['nom_complet'] . ' (' . $parent['relation'] . ')';
                            $telephone_parent = $parent['telephone'] ?? '';
                            break;
                        }
                    }
                }
                
                fputcsv($output, [
                    $eleve['matricule'],
                    $eleve['nom'] . ' ' . $eleve['post_nom'],
                    $eleve['prenom'],
                    format_date($eleve['date_naissance']),
                    $eleve['genre'],
                    $eleve['telephone'] ?? '',
                    $eleve['email'] ?? '',
                    $parent_responsable,
                    $telephone_parent
                ], ';');
            }
        } else {
            fputcsv($output, ['Aucun élève dans cette classe'], ';');
        }
        
        fputcsv($output, [], ';'); // Ligne vide entre les classes
        fputcsv($output, [], ';'); // Ligne vide
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

// =============================================
// FONCTIONS DE RÉPLICATION DES CLASSES
// =============================================

/**
 * Répliquer les classes d'une année à l'autre
 */
function classe_repliquer(int $annee_source_id, int $annee_destination_id): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_PROVISEUR)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que l'année source existe
    $annee_source = db_query_single(
        "SELECT * FROM annees_scolaire WHERE annee_id = :annee_id",
        ['annee_id' => $annee_source_id]
    );
    
    if (!$annee_source) {
        return ['success' => false, 'error' => 'Année source non trouvée'];
    }
    
    // Vérifier que l'année destination existe
    $annee_destination = db_query_single(
        "SELECT * FROM annees_scolaire WHERE annee_id = :annee_id",
        ['annee_id' => $annee_destination_id]
    );
    
    if (!$annee_destination) {
        return ['success' => false, 'error' => 'Année destination non trouvée'];
    }
    
    // Vérifier qu'il n'y a pas déjà des classes pour l'année destination
    $classes_existantes = db_query_single(
        "SELECT COUNT(*) as count FROM classes WHERE annee_id = :annee_id",
        ['annee_id' => $annee_destination_id]
    );
    
    if ($classes_existantes['count'] > 0) {
        return [
            'success' => false,
            'error' => "Des classes existent déjà pour l'année {$annee_destination['annee_libelle']}"
        ];
    }
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        // Récupérer les classes de l'année source
        $classes_source = db_query(
            "SELECT niveau_id, section_id, libelle, capacite_max, salle, tuteur_id
             FROM classes 
             WHERE annee_id = :annee_id 
             AND statut = :statut_actif",
            ['annee_id' => $annee_source_id, 'statut_actif' => CLASSE_ACTIVE]
        );
        
        $classes_creees = 0;
        $erreurs = [];
        
        foreach ($classes_source as $classe_source) {
            // Adapter le libellé pour la nouvelle année
            $nouveau_libelle = str_replace(
                $annee_source['annee_libelle'],
                $annee_destination['annee_libelle'],
                $classe_source['libelle']
            );
            
            // Si le remplacement n'a pas fonctionné, ajouter l'année
            if ($nouveau_libelle === $classe_source['libelle']) {
                $nouveau_libelle = $classe_source['libelle'] . ' ' . $annee_destination['annee_libelle'];
            }
            
            // Vérifier que le nouveau libellé n'existe pas déjà
            $libelle_existant = db_query_single(
                "SELECT class_id FROM classes WHERE libelle = :libelle AND annee_id = :annee_id",
                ['libelle' => $nouveau_libelle, 'annee_id' => $annee_destination_id]
            );
            
            if ($libelle_existant) {
                $erreurs[] = "Le libellé '$nouveau_libelle' existe déjà";
                continue;
            }
            
            // Créer la nouvelle classe
            $donnees_classe = [
                'niveau_id' => $classe_source['niveau_id'],
                'section_id' => $classe_source['section_id'],
                'libelle' => $nouveau_libelle,
                'annee_id' => $annee_destination_id,
                'capacite_max' => $classe_source['capacite_max'],
                'salle' => $classe_source['salle'],
                'tuteur_id' => $classe_source['tuteur_id'],
                'statut' => CLASSE_ACTIVE
            ];
            
            $resultat = classe_creer($donnees_classe);
            
            if ($resultat['success']) {
                $classes_creees++;
            } else {
                $erreurs[] = "Classe '{$classe_source['libelle']}': " . ($resultat['error'] ?? 'Erreur inconnue');
            }
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        log_action('Classes répliquées', [
            'annee_source' => $annee_source['annee_libelle'],
            'annee_destination' => $annee_destination['annee_libelle'],
            'classes_creees' => $classes_creees,
            'erreurs' => $erreurs,
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'classes');
        
        return [
            'success' => true,
            'message' => "Réplication terminée: $classes_creees classe(s) créée(s)",
            'classes_creees' => $classes_creees,
            'erreurs' => $erreurs
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur classe_repliquer: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la réplication'];
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Vérifier la capacité d'une classe (réutilisée depuis admissions.php)
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
    $places_disponibles = $capacite_max > 0 ? $capacite_max - $nombre_eleves : PHP_INT_MAX;
    
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

// =============================================
// INITIALISATION ET JOURNALISATION
// =============================================

// Journaliser le chargement du module
log_action('Module classes chargé', ['version' => '1.0.0']);