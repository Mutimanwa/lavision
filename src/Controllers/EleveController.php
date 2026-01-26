<?php
/**
 * Contrôleur pour la gestion des élèves
 * Gère toutes les actions liées aux élèves (CRUD, admission, etc.)
 * Programmation procédurale pour cohérence
 * Version: 1.0.0 - Refactorisé pour scalabilité
 */

require_once __DIR__ . '/../Models/EleveModel.php';
require_once __DIR__ . '/../Services/validation.php';
require_once __DIR__ . '/AcademiqueController.php';

/**
 * Récupère la liste des parents disponibles
 */
function get_parents_disponibles()
{    
    $query = "SELECT parent_id, nom, prenom, telephone, email 
              FROM parents ORDER BY nom, prenom";    
    return db_query($query);
}

// =============================================
// CONSTANTES SPÉCIFIQUES AUX ÉLÈVES
// =============================================

/**
 * Statuts possibles pour un élève (selon la base de données)
 */
define('ELEVE_STATUTS', [
    'en_attente' => 'En attente d\'admission',
    'actif' => 'Actif',
    'suspendu' => 'Suspendu',
    'desiste' => 'Désisté'
]);

/**
 * Genres disponibles (selon la base de données)
 */
define('ELEVE_GENRES', [
    'M' => 'Masculin',
    'F' => 'Féminin',
    'Autre' => 'Autre'
]);

/**
 * Groupes sanguins
 */
define('GROUPES_SANGUINS', [
    'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'
]);

// =============================================
// FONCTIONS DE CONTRÔLE PRINCIPALES
// =============================================

/**
 * Affiche la liste des élèves
 * Gère la pagination, le tri et les filtres
 */
function afficher_liste_eleves(): void
{
    // Récupération des paramètres de filtrage
    $filtres = [
        'nom' => $_GET['nom'] ?? '',
        'classe' => $_GET['classe'] ?? '',
        'statut' => $_GET['statut'] ?? '',
        'genre' => $_GET['genre'] ?? '',
        'matricule' => $_GET['matricule'] ?? '',
        'annee' => $_GET['annee'] ?? ''
    ];

    // Paramètres de pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $par_page = 20;

    // Tri
    $tri = $_GET['tri'] ?? 'nom';
    $ordre = $_GET['ordre'] ?? 'ASC';

    // Mapping des noms de tri vers les champs de la BD
    $tri_fields = [
        'nom' => 'e.nom',
        'prenom' => 'e.prenom',
        'matricule' => 'e.matricule',
        'classe' => 'c.libelle',
        'date_inscription' => 'e.date_inscription',
        'statut' => 'e.statut_etudiant'
    ];
    $tri_field = $tri_fields[$tri] ?? 'e.nom';

    try {
        // Récupération des données depuis la BD adaptée
        $eleves = get_eleves_pagines($filtres, $page, $par_page, $tri_field, $ordre);
        $total_eleves = compter_eleves($filtres);
        $total_pages = ceil($total_eleves / $par_page);

        // Récupération des classes pour le filtre
        $classes = get_classes_actives();
        
        // Récupération des années scolaires
        $annees = get_annees_scolaires();

        // Préparation des données pour la vue
        $data = [
            'eleves' => $eleves,
            'filtres' => $filtres,
            'pagination' => [
                'page_actuelle' => $page,
                'total_pages' => $total_pages,
                'total_eleves' => $total_eleves,
                'par_page' => $par_page
            ],
            'tri' => [
                'champ' => $tri,
                'ordre' => $ordre
            ],
            'classes' => $classes,
            'annees' => $annees,
            'statuts' => ELEVE_STATUTS,
            'genres' => ELEVE_GENRES
        ];

        // Chargement de la vue

        render('eleves/liste', $data);

    } catch (Exception $e) {
        logError('Erreur affichage liste élèves', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement de la liste des élèves.");
    }
}

/**
 * Affiche le formulaire d'ajout d'un élève
 */
function afficher_formulaire_ajout_eleve(): void
{
    // Vérification des permissions
    // if (!a_permission('eleves_gerer')) {
    //     afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
    //     return;
    // }

    // Récupération des données nécessaires depuis la BD
    $classes = get_classes_actives();
    $parents = get_parents_disponibles();
    $annees = get_annees_scolaires();

    $data = [
        'classes' => $classes,
        'parents' => $parents,
        'annees' => $annees,
        'statuts' => ELEVE_STATUTS,
        'genres' => ELEVE_GENRES,
        'groupes_sanguins' => GROUPES_SANGUINS,
        'mode' => 'ajout',
        'nationalites' => ['Congolaise', 'Étrangère'] // Valeur par défaut selon BD
    ];

    render('eleves/formulaire', $data);
}

/**
 * Traite l'ajout d'un nouvel élève
 */
function traiter_ajout_eleve(): void
{
    // Vérification des permissions
    // if (!a_permission('eleves_gerer')) {
    //     afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
    //     return;
    // }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    try {
        // Récupération et validation des données selon structure BD
        $donnees = valider_donnees_eleve($_POST);

        if (empty($donnees['erreurs'])) {
            // Génération du matricule si non fourni
            if (empty($donnees['validees']['matricule'])) {
                $donnees['validees']['matricule'] = generer_matricule();
            }
            
            // Formatage des dates selon BD
            if (isset($donnees['validees']['date_naissance'])) {
                $donnees['validees']['date_naissance'] = date('Y-m-d', strtotime($donnees['validees']['date_naissance']));
            }
            
            if (isset($donnees['validees']['date_inscription'])) {
                $donnees['validees']['date_inscription'] = date('Y-m-d', strtotime($donnees['validees']['date_inscription']));
            }

            // Création de l'élève
            $id_eleve = ajouter_eleve($donnees['validees']);

            if ($id_eleve) {
                // Si une classe est spécifiée, créer l'admission
                if (!empty($donnees['validees']['class_id']) && !empty($donnees['validees']['annee_id'])) {
                    $admission_data = [
                        'eleve_id' => $id_eleve,
                        'class_id' => $donnees['validees']['class_id'],
                        'annee_id' => $donnees['validees']['annee_id'],
                        'admis_par' => $_SESSION['utilisateur_id'],
                        'statut_admission' => 'approuve',
                        'frais_inscription' => $donnees['validees']['frais_inscription'] ?? 0,
                        'frais_payes' => $donnees['validees']['frais_payes'] ?? 0
                    ];
                    creer_admission($admission_data);
                }

                logger_action('Élève ajouté', [
                    'eleve_id' => $id_eleve, 
                    'matricule' => $donnees['validees']['matricule'],
                    'nom' => $donnees['validees']['nom']
                ]);
                
                set_message_succes("L'élève a été ajouté avec succès.");
                redirect('eleves/detail', ['id' => $id_eleve]);
            } else {
                afficher_erreur("Erreur lors de l'ajout de l'élève.");
            }
        } else {
            // Réaffichage du formulaire avec erreurs
            $classes = get_classes_actives();
            $parents = get_parents_disponibles();
            $annees = get_annees_scolaires();

            $data = [
                'classes' => $classes,
                'parents' => $parents,
                'annees' => $annees,
                'statuts' => ELEVE_STATUTS,
                'genres' => ELEVE_GENRES,
                'groupes_sanguins' => GROUPES_SANGUINS,
                'mode' => 'ajout',
                'valeurs' => $_POST,
                'erreurs' => $donnees['erreurs'],
                'nationalites' => ['Congolaise', 'Étrangère']
            ];

            require_once VIEWS_PATH . '/eleves/formulaire.php';
        }

    } catch (Exception $e) {
        logError('Erreur ajout élève', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de l'ajout de l'élève: " . $e->getMessage());
    }
}

/**
 * Affiche les détails d'un élève
 */
function afficher_details_eleve(): void
{
    $id_eleve = intval($_GET['id'] ?? 0);

    if ($id_eleve <= 0) {
        afficher_erreur("ID d'élève invalide.", 400);
        return;
    }

    try {
        // Récupération des données de l'élève selon structure BD
        $eleve = get_eleve_par_id($id_eleve);

        if (!$eleve) {
            afficher_erreur("Élève non trouvé.", 404);
            return;
        }

        // Vérification des permissions
        if (!a_permission('eleves_consulter')) {
            afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
            return;
        }

        // Récupération des données liées selon structure BD
        $notes = get_notes_eleve($id_eleve);
        $absences = get_absences_eleve($id_eleve);
        $paiements = get_paiements_eleve($id_eleve);
        $admission = get_admission_eleve($id_eleve);
        $parents = get_parents_eleve($id_eleve);
        $sanctions = get_sanctions_eleve($id_eleve);

        // Calcul de l'âge
        $age = null;
        if ($eleve['date_naissance']) {
            $birthDate = new DateTime($eleve['date_naissance']);
            $today = new DateTime();
            $age = $birthDate->diff($today)->y;
        }

        $data = [
            'eleve' => $eleve,
            'age' => $age,
            'notes' => $notes,
            'absences' => $absences,
            'paiements' => $paiements,
            'admission' => $admission,
            'parents' => $parents,
            'sanctions' => $sanctions,
            'statuts' => ELEVE_STATUTS
        ];

        require_once VIEWS_PATH . '/eleves/detail.php';

    } catch (Exception $e) {
        logError('Erreur détails élève', ['eleve_id' => $id_eleve, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement des détails de l'élève.");
    }
}

/**
 * Affiche le formulaire de modification d'un élève
 */
function afficher_formulaire_modification_eleve(): void
{
    $eleve_id = intval($_GET['id'] ?? 0);

    if ($eleve_id <= 0) {
        afficher_erreur("ID d'élève invalide.", 400);
        return;
    }

    // Vérification des permissions
    if (!a_permission('eleves_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    try {
        // Récupération des données actuelles
        $eleve = get_eleve_par_id($eleve_id);

        if (!$eleve) {
            afficher_erreur("Élève non trouvé.", 404);
            return;
        }

        $classes = get_classes_actives();
        $parents = get_parents_disponibles();
        $annees = get_annees_scolaires();
        $admission = get_admission_eleve($eleve_id);

        $data = [
            'eleve' => $eleve,
            'admission' => $admission,
            'classes' => $classes,
            'parents' => $parents,
            'annees' => $annees,
            'statuts' => ELEVE_STATUTS,
            'genres' => ELEVE_GENRES,
            'groupes_sanguins' => GROUPES_SANGUINS,
            'mode' => 'modification',
            'nationalites' => ['Congolaise', 'Étrangère']
        ];

        require_once VIEWS_PATH . '/eleves/formulaire.php';

    } catch (Exception $e) {
        logError('Erreur formulaire modification élève', ['eleve_id' => $eleve_id, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement du formulaire.");
    }
}

/**
 * Traite la modification d'un élève
 */
function traiter_modification_eleve(): void
{
    $eleve_id = intval($_POST['eleve_id'] ?? 0);

    if ($eleve_id <= 0) {
        afficher_erreur("ID d'élève invalide.", 400);
        return;
    }

    // Vérification des permissions
    if (!a_permission('eleves_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    try {
        // Récupération et validation des données
        $donnees = valider_donnees_eleve($_POST, true);

        if (empty($donnees['erreurs'])) {
            // Formatage des dates selon BD
            if (isset($donnees['validees']['date_naissance'])) {
                $donnees['validees']['date_naissance'] = date('Y-m-d', strtotime($donnees['validees']['date_naissance']));
            }
            
            if (isset($donnees['validees']['date_inscription'])) {
                $donnees['validees']['date_inscription'] = date('Y-m-d', strtotime($donnees['validees']['date_inscription']));
            }

            // Modification de l'élève
            $succes = modifier_eleve($eleve_id, $donnees['validees']);

            if ($succes) {
                // Mise à jour de l'admission si nécessaire
                if (!empty($donnees['validees']['class_id']) && !empty($donnees['validees']['annee_id'])) {
                    $admission_data = [
                        'eleve_id' => $eleve_id,
                        'class_id' => $donnees['validees']['class_id'],
                        'annee_id' => $donnees['validees']['annee_id'],
                        'statut_admission' => 'approuve'
                    ];
                    mettre_a_jour_admission($eleve_id, $admission_data);
                }

                log_action('Élève modifié', [
                    'eleve_id' => $eleve_id, 
                    'matricule' => $donnees['validees']['matricule'] ?? '',
                    'nom' => $donnees['validees']['nom']
                ]);
                
                set_message_succes("L'élève a été modifié avec succès.");
                redirect('eleves/detail', ['id' => $eleve_id]);
            } else {
                afficher_erreur("Erreur lors de la modification de l'élève.");
            }
        } else {
            // Réaffichage du formulaire avec erreurs
            $eleve = get_eleve_par_id($eleve_id);
            $classes = get_classes_actives();
            $parents = get_parents_disponibles();
            $annees = get_annees_scolaires();
            $admission = get_admission_eleve($eleve_id);

            $data = [
                'eleve' => $eleve,
                'admission' => $admission,
                'classes' => $classes,
                'parents' => $parents,
                'annees' => $annees,
                'statuts' => ELEVE_STATUTS,
                'genres' => ELEVE_GENRES,
                'groupes_sanguins' => GROUPES_SANGUINS,
                'mode' => 'modification',
                'valeurs' => $_POST,
                'erreurs' => $donnees['erreurs'],
                'nationalites' => ['Congolaise', 'Étrangère']
            ];

            require_once VIEWS_PATH . '/eleves/formulaire.php';
        }

    } catch (Exception $e) {
        logError('Erreur modification élève', ['eleve_id' => $eleve_id, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de la modification de l'élève: " . $e->getMessage());
    }
}

/**
 * Supprime un élève (changement de statut)
 */
function supprimer_eleve(): void
{
    $eleve_id = intval($_POST['eleve_id'] ?? 0);

    if ($eleve_id <= 0) {
        afficher_erreur("ID d'élève invalide.", 400);
        return;
    }

    // Vérification des permissions
    if (!a_permission('eleves_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    try {
        // Récupération des infos avant désinscription
        $eleve = get_eleve_par_id($eleve_id);

        if (!$eleve) {
            afficher_erreur("Élève non trouvé.", 404);
            return;
        }

        // Changement de statut (désisté) plutôt que suppression
        $succes = desinscrire_eleve($eleve_id);

        if ($succes) {
            logger_action('Élève désinscrit', [
                'eleve_id' => $eleve_id, 
                'matricule' => $eleve['matricule'], 
                'nom' => $eleve['nom'] . ' ' . $eleve['prenom']
            ]);
            
            set_message_succes("L'élève a été désinscrit avec succès.");
            redirect('eleves');
        } else {
            afficher_erreur("Erreur lors de la désinscription de l'élève.");
        }

    } catch (Exception $e) {
        logError('Erreur désinscription élève', ['eleve_id' => $eleve_id, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de la désinscription de l'élève.");
    }
}

/**
 * Gère l'admission d'un élève dans une classe
 */
function traiter_admission_eleve(): void
{
    $eleve_id = intval($_POST['eleve_id'] ?? 0);
    $class_id = intval($_POST['class_id'] ?? 0);
    $annee_id = intval($_POST['annee_id'] ?? 0);

    if ($eleve_id <= 0 || $class_id <= 0 || $annee_id <= 0) {
        afficher_erreur("Données d'admission invalides.", 400);
        return;
    }

    // Vérification des permissions
    if (!a_permission('eleves_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    try {
        // Vérifier la capacité de la classe
        if (!verifier_capacite_classe($class_id)) {
            afficher_erreur("La classe a atteint sa capacité maximale.");
            return;
        }
        if(!$_SESSION['utilisateur_id']){
            afficher_erreur("l'identifiant non disponible.");
            return;
        }
        $utilisateur_id = $_SESSION['utilisateur_id'];

        $admission_data = [
            'eleve_id' => $eleve_id,
            'class_id' => $class_id,
            'annee_id' => $annee_id,
            'admis_par' => $utilisateur_id,
            'statut_admission' => $_POST['statut'] ?? 'approuve',
            'frais_inscription' => floatval($_POST['frais_inscription'] ?? 0),
            'frais_payes' => floatval($_POST['frais_payes'] ?? 0),
            'date_limite_paiement' => $_POST['date_limite_paiement'] ?? null,
            'notes_entretien' => $_POST['notes_entretien'] ?? null,
            'decision_comite' => $_POST['decision_comite'] ?? null
        ];

        // Créer ou mettre à jour l'admission
        $succes = creer_ou_mettre_a_jour_admission($admission_data);

        if ($succes) {
            // Mettre à jour le statut de l'élève si admission approuvée
            if ($admission_data['statut_admission'] === 'approuve') {
                changer_statut_eleve($eleve_id, 'actif');
            }
            
            log_action('Admission élève', [
                'eleve_id' => $eleve_id,
                'class_id' => $class_id,
                'statut' => $admission_data['statut_admission']
            ]);
            
            set_message_succes("L'admission a été traitée avec succès.");
            redirect('eleves/detail', ['id' => $eleve_id]);
        } else {
            afficher_erreur("Erreur lors du traitement de l'admission.");
        }

    } catch (Exception $e) {
        logError('Erreur admission élève', [
            'eleve_id' => $eleve_id,
            'class_id' => $class_id,
            'error' => $e->getMessage()
        ]);
        afficher_erreur("Erreur lors du traitement de l'admission.");
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Génère un matricule unique pour un nouvel élève
 */
function generer_matricule(): string
{
    $prefix = 'ELV';
    $annee = date('Y');
    $random = strtoupper(bin2hex(random_bytes(3)));
    
    return $prefix . $annee . $random;
}

/**
 * Vérifie la capacité d'une classe
 */
function verifier_capacite_classe(int $class_id): bool
{
    global $db;
    
    $query = "SELECT 
                c.capacite_max,
                COUNT(a.eleve_id) as eleves_admis
              FROM classes c
              LEFT JOIN admissions a ON c.class_id = a.class_id 
                AND a.statut_admission = 'approuve'
              WHERE c.class_id = :class_id
              GROUP BY c.class_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':class_id' => $class_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        return false;
    }
    
    return $result['eleves_admis'] < $result['capacite_max'];
}

?>