<?php
/**
 * Contrôleur pour la gestion des élèves
 * Gère toutes les actions liées aux élèves (CRUD, admission, etc.)
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Models/EleveModel.php';
require_once __DIR__ . '/../Services/validation.php';

// =============================================
// CONSTANTES SPÉCIFIQUES AUX ÉLÈVES
// =============================================

/**
 * Statuts possibles pour un élève
 */
define('ELEVE_STATUTS', [
    'en_attente' => 'En attente d\'admission',
    'actif' => 'Actif',
    'suspendu' => 'Suspendu',
    'desiste' => 'Désisté',
    'diplome' => 'Diplômé'
]);

/**
 * Genres disponibles
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
        'genre' => $_GET['genre'] ?? ''
    ];

    // Paramètres de pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $par_page = 20;

    // Tri
    $tri = $_GET['tri'] ?? 'nom';
    $ordre = $_GET['ordre'] ?? 'ASC';

    try {
        // Récupération des données
        $eleves = get_eleves_pagines($filtres, $page, $par_page, $tri, $ordre);
        $total_eleves = compter_eleves($filtres);
        $total_pages = ceil($total_eleves / $par_page);

        // Récupération des classes pour le filtre
        $classes = get_classes_pour_filtre();

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
            'statuts' => ELEVE_STATUTS,
            'genres' => ELEVE_GENRES
        ];

        // Chargement de la vue
        require_once VIEWS_PATH . '/eleves/liste.php';

    } catch (Exception $e) {
        log_error('Erreur affichage liste élèves', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement de la liste des élèves.");
    }
}

/**
 * Affiche le formulaire d'ajout d'un élève
 */
function afficher_formulaire_ajout_eleve(): void
{
    // Vérification des permissions
    if (!a_permission('eleves_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Récupération des données nécessaires
    $classes = get_classes_actives();
    $parents = get_parents_disponibles();

    $data = [
        'classes' => $classes,
        'parents' => $parents,
        'statuts' => ELEVE_STATUTS,
        'genres' => ELEVE_GENRES,
        'groupes_sanguins' => GROUPES_SANGUINS,
        'mode' => 'ajout'
    ];

    require_once VIEWS_PATH . '/eleves/formulaire.php';
}

/**
 * Traite l'ajout d'un nouvel élève
 */
function traiter_ajout_eleve(): void
{
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
        $donnees = valider_donnees_eleve($_POST);

        if (empty($donnees['erreurs'])) {
            // Création de l'élève
            $id_eleve = ajouter_eleve($donnees['validees']);

            if ($id_eleve) {
                log_action('Élève ajouté', ['id_eleve' => $id_eleve, 'nom' => $donnees['validees']['nom']]);
                set_message_succes("L'élève a été ajouté avec succès.");
                redirect('eleves/detail', ['id' => $id_eleve]);
            } else {
                afficher_erreur("Erreur lors de l'ajout de l'élève.");
            }
        } else {
            // Réaffichage du formulaire avec erreurs
            $classes = get_classes_actives();
            $parents = get_parents_disponibles();

            $data = [
                'classes' => $classes,
                'parents' => $parents,
                'statuts' => ELEVE_STATUTS,
                'genres' => ELEVE_GENRES,
                'groupes_sanguins' => GROUPES_SANGUINS,
                'mode' => 'ajout',
                'valeurs' => $_POST,
                'erreurs' => $donnees['erreurs']
            ];

            require_once VIEWS_PATH . '/eleves/formulaire.php';
        }

    } catch (Exception $e) {
        log_error('Erreur ajout élève', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de l'ajout de l'élève.");
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
        // Récupération des données de l'élève
        $eleve = get_eleve_par_id($id_eleve);

        if (!$eleve) {
            afficher_erreur("Élève non trouvé.", 404);
            return;
        }

        // Vérification des permissions (l'élève peut voir son propre profil)
        if (!a_permission('eleves_consulter') && get_current_user_id() !== $eleve['id_utilisateur']) {
            afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
            return;
        }

        // Récupération des données liées
        $notes = get_notes_eleve($id_eleve);
        $presences = get_presences_eleve($id_eleve);
        $paiements = get_paiements_eleve($id_eleve);

        $data = [
            'eleve' => $eleve,
            'notes' => $notes,
            'presences' => $presences,
            'paiements' => $paiements
        ];

        require_once VIEWS_PATH . '/eleves/detail.php';

    } catch (Exception $e) {
        log_error('Erreur détails élève', ['id_eleve' => $id_eleve, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement des détails de l'élève.");
    }
}

/**
 * Affiche le formulaire de modification d'un élève
 */
function afficher_formulaire_modification_eleve(): void
{
    $id_eleve = intval($_GET['id'] ?? 0);

    if ($id_eleve <= 0) {
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
        $eleve = get_eleve_par_id($id_eleve);

        if (!$eleve) {
            afficher_erreur("Élève non trouvé.", 404);
            return;
        }

        $classes = get_classes_actives();
        $parents = get_parents_disponibles();

        $data = [
            'eleve' => $eleve,
            'classes' => $classes,
            'parents' => $parents,
            'statuts' => ELEVE_STATUTS,
            'genres' => ELEVE_GENRES,
            'groupes_sanguins' => GROUPES_SANGUINS,
            'mode' => 'modification'
        ];

        require_once VIEWS_PATH . '/eleves/formulaire.php';

    } catch (Exception $e) {
        log_error('Erreur formulaire modification élève', ['id_eleve' => $id_eleve, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement du formulaire.");
    }
}

/**
 * Traite la modification d'un élève
 */
function traiter_modification_eleve(): void
{
    $id_eleve = intval($_POST['id_eleve'] ?? 0);

    if ($id_eleve <= 0) {
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
            // Modification de l'élève
            $succes = modifier_eleve($id_eleve, $donnees['validees']);

            if ($succes) {
                log_action('Élève modifié', ['id_eleve' => $id_eleve, 'nom' => $donnees['validees']['nom']]);
                set_message_succes("L'élève a été modifié avec succès.");
                redirect('eleves/detail', ['id' => $id_eleve]);
            } else {
                afficher_erreur("Erreur lors de la modification de l'élève.");
            }
        } else {
            // Réaffichage du formulaire avec erreurs
            $eleve = get_eleve_par_id($id_eleve);
            $classes = get_classes_actives();
            $parents = get_parents_disponibles();

            $data = [
                'eleve' => $eleve,
                'classes' => $classes,
                'parents' => $parents,
                'statuts' => ELEVE_STATUTS,
                'genres' => ELEVE_GENRES,
                'groupes_sanguins' => GROUPES_SANGUINS,
                'mode' => 'modification',
                'valeurs' => $_POST,
                'erreurs' => $donnees['erreurs']
            ];

            require_once VIEWS_PATH . '/eleves/formulaire.php';
        }

    } catch (Exception $e) {
        log_error('Erreur modification élève', ['id_eleve' => $id_eleve, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de la modification de l'élève.");
    }
}

/**
 * Supprime un élève (désinscription)
 */
function supprimer_eleve(): void
{
    $id_eleve = intval($_POST['id_eleve'] ?? 0);

    if ($id_eleve <= 0) {
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
        // Récupération des infos avant suppression
        $eleve = get_eleve_par_id($id_eleve);

        if (!$eleve) {
            afficher_erreur("Élève non trouvé.", 404);
            return;
        }

        // Désinscription de l'élève
        $succes = desinscrire_eleve($id_eleve);

        if ($succes) {
            log_action('Élève désinscrit', ['id_eleve' => $id_eleve, 'nom' => $eleve['nom_complet']]);
            set_message_succes("L'élève a été désinscrit avec succès.");
            redirect('eleves');
        } else {
            afficher_erreur("Erreur lors de la désinscription de l'élève.");
        }

    } catch (Exception $e) {
        log_error('Erreur suppression élève', ['id_eleve' => $id_eleve, 'error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de la désinscription de l'élève.");
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

?>