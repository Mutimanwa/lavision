<?php
/**
 * Contrôleur pour la gestion du personnel
 * Gère les professeurs, administrateurs et autres membres du personnel
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Models/PersonnelModel.php';
require_once __DIR__ . '/../Services/validation.php';

// =============================================
// CONSTANTES SPÉCIFIQUES AU PERSONNEL
// =============================================

/**
 * Rôles disponibles pour les administrateurs
 */
define('ROLES_ADMIN', [
    'superadmin' => 'Super Administrateur',
    'admin' => 'Administrateur',
    'secretaire' => 'Secrétaire',
    'gestionnaire' => 'Gestionnaire',
    'proviseur' => 'Proviseur'
]);

/**
 * Statuts des professeurs
 */
define('STATUTS_PROFESSEUR', [
    'actif' => 'Actif',
    'inactif' => 'Inactif',
    'conge' => 'En congé',
    'retraite' => 'Retraité'
]);

/**
 * Types de contrat
 */
define('TYPES_CONTRAT', [
    'titulaire' => 'Titulaire',
    'contractuel' => 'Contractuel',
    'vacataire' => 'Vacataire'
]);

/**
 * Genres disponibles
 */
define('GENRES', [
    'M' => 'Masculin',
    'F' => 'Féminin',
    'Autre' => 'Autre'
]);

// =============================================
// GESTION DES PROFESSEURS
// =============================================

/**
 * Affiche la liste des professeurs
 */
function afficher_professeurs()
{
    // Récupération des paramètres de filtrage
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $statut = $_GET['statut'] ?? '';
    $specialite = $_GET['specialite'] ?? '';
    $recherche = trim($_GET['recherche'] ?? '');

    $filtres = [];
    if (!empty($statut)) $filtres['statut'] = $statut;
    if (!empty($specialite)) $filtres['specialite'] = $specialite;
    if (!empty($recherche)) $filtres['recherche'] = $recherche;

    // Récupération des données
    $resultat = get_professeurs_pagines($page, 20, $filtres);
    $statistiques = get_statistiques_personnel();

    // Préparation des données pour la vue
    $donnees_vue = [
        'professeurs' => $resultat['professeurs'],
        'pagination' => [
            'page_actuelle' => $resultat['page_actuelle'],
            'total_pages' => $resultat['pages'],
            'total_professeurs' => $resultat['total']
        ],
        'filtres' => $filtres,
        'statistiques' => $statistiques,
        'statuts_disponibles' => STATUTS_PROFESSEUR,
        'titre_page' => 'Gestion des Professeurs',
        'sous_titre' => 'Liste et gestion des enseignants'
    ];

    // Chargement de la vue
    require_once VIEWS_PATH . '/personnel/liste_professeurs.php';
}

/**
 * Affiche le formulaire d'ajout d'un professeur
 */
function afficher_formulaire_professeur($professeur_id = null)
{
    $professeur = null;
    $erreurs = [];
    $succes = '';

    // Si modification, récupérer les données existantes
    if ($professeur_id) {
        $professeur = get_professeur_by_id($professeur_id);
        if (!$professeur) {
            set_flash_message('danger', 'Professeur non trouvé.');
            redirect(BASE_URL . 'personnel/professeurs');
            return;
        }
    }

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $resultat = traiter_formulaire_professeur($professeur_id);
        if ($resultat['succes']) {
            set_flash_message('success', $resultat['message']);
            redirect(BASE_URL . 'personnel/professeurs');
            return;
        } else {
            $erreurs = $resultat['erreurs'];
            $professeur = $_POST; // Conserver les données saisies
        }
    }

    // Préparation des données pour la vue
    $donnees_vue = [
        'professeur' => $professeur,
        'erreurs' => $erreurs,
        'succes' => $succes,
        'statuts_disponibles' => STATUTS_PROFESSEUR,
        'types_contrat' => TYPES_CONTRAT,
        'genres' => GENRES,
        'est_modification' => ($professeur_id !== null),
        'titre_page' => $professeur_id ? 'Modifier un Professeur' : 'Ajouter un Professeur',
        'sous_titre' => $professeur_id ? 'Modification des informations' : 'Création d\'un nouveau professeur'
    ];

    // Chargement de la vue
    require_once VIEWS_PATH . '/personnel/formulaire_professeur.php';
}

/**
 * Traite le formulaire d'ajout/modification d'un professeur
 */
function traiter_formulaire_professeur($professeur_id = null)
{
    $erreurs = [];

    // Récupération et nettoyage des données
    $donnees = [
        'matricule_prof' => trim($_POST['matricule_prof'] ?? ''),
        'nom' => trim($_POST['nom'] ?? ''),
        'post_nom' => trim($_POST['post_nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'date_naissance' => trim($_POST['date_naissance'] ?? ''),
        'lieu_naissance' => trim($_POST['lieu_naissance'] ?? ''),
        'genre' => trim($_POST['genre'] ?? ''),
        'nationalite' => trim($_POST['nationalite'] ?? 'Congolaise'),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'adresse' => trim($_POST['adresse'] ?? ''),
        'specialite' => trim($_POST['specialite'] ?? ''),
        'diplome' => trim($_POST['diplome'] ?? ''),
        'date_embauche' => trim($_POST['date_embauche'] ?? ''),
        'statut' => trim($_POST['statut'] ?? 'actif'),
        'type_contrat' => trim($_POST['type_contrat'] ?? 'contractuel'),
        'salaire_base' => floatval($_POST['salaire_base'] ?? 0),
        'banque' => trim($_POST['banque'] ?? ''),
        'numero_compte' => trim($_POST['numero_compte'] ?? ''),
        'creer_compte' => isset($_POST['creer_compte'])
    ];

    // Validation des données
    if (empty($donnees['matricule_prof'])) {
        $erreurs['matricule_prof'] = 'Le matricule est obligatoire.';
    }

    if (empty($donnees['nom'])) {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    }

    if (empty($donnees['prenom'])) {
        $erreurs['prenom'] = 'Le prénom est obligatoire.';
    }

    if (empty($donnees['telephone'])) {
        $erreurs['telephone'] = 'Le téléphone est obligatoire.';
    }

    if (!empty($donnees['email']) && !filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'L\'adresse email n\'est pas valide.';
    }

    if (!empty($donnees['date_naissance']) && !strtotime($donnees['date_naissance'])) {
        $erreurs['date_naissance'] = 'La date de naissance n\'est pas valide.';
    }

    if (!empty($donnees['date_embauche']) && !strtotime($donnees['date_embauche'])) {
        $erreurs['date_embauche'] = 'La date d\'embauche n\'est pas valide.';
    }

    // Si pas d'erreurs, procéder à la sauvegarde
    if (empty($erreurs)) {
        if ($professeur_id) {
            $resultat = update_professeur($professeur_id, $donnees);
            $message = $resultat ? 'Professeur modifié avec succès.' : 'Erreur lors de la modification.';
        } else {
            $resultat = create_professeur($donnees);
            $message = $resultat ? 'Professeur créé avec succès.' : 'Erreur lors de la création.';
        }

        if ($resultat) {
            return ['succes' => true, 'message' => $message];
        } else {
            $erreurs['general'] = $message;
        }
    }

    return ['succes' => false, 'erreurs' => $erreurs];
}

/**
 * Supprime un professeur
 */
function supprimer_professeur($professeur_id)
{
    // Vérification CSRF
    verifier_csrf_token();

    $resultat = delete_professeur($professeur_id);

    if ($resultat) {
        set_flash_message('success', 'Professeur supprimé avec succès.');
    } else {
        set_flash_message('danger', 'Erreur lors de la suppression du professeur.');
    }

    redirect(BASE_URL . 'personnel/professeurs');
}

// =============================================
// GESTION DES ADMINISTRATEURS
// =============================================

/**
 * Affiche la liste des administrateurs
 */
function afficher_administrateurs()
{
    // Récupération des paramètres de filtrage
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $role = $_GET['role'] ?? '';
    $statut = $_GET['statut'] ?? '';
    $recherche = trim($_GET['recherche'] ?? '');

    $filtres = [];
    if (!empty($role)) $filtres['role'] = $role;
    if (!empty($statut)) $filtres['statut'] = $statut;
    if (!empty($recherche)) $filtres['recherche'] = $recherche;

    // Récupération des données
    $resultat = get_administrateurs_pagines($page, 20, $filtres);
    $statistiques = get_statistiques_personnel();

    // Préparation des données pour la vue
    $donnees_vue = [
        'administrateurs' => $resultat['administrateurs'],
        'pagination' => [
            'page_actuelle' => $resultat['page_actuelle'],
            'total_pages' => $resultat['pages'],
            'total_admins' => $resultat['total']
        ],
        'filtres' => $filtres,
        'statistiques' => $statistiques,
        'roles_disponibles' => ROLES_ADMIN,
        'titre_page' => 'Gestion des Administrateurs',
        'sous_titre' => 'Liste et gestion du personnel administratif'
    ];

    // Chargement de la vue
    require_once VIEWS_PATH . '/personnel/liste_administrateurs.php';
}

/**
 * Affiche le formulaire d'ajout d'un administrateur
 */
function afficher_formulaire_administrateur($admin_id = null)
{
    $administrateur = null;
    $erreurs = [];
    $succes = '';

    // Si modification, récupérer les données existantes
    if ($admin_id) {
        // TODO: Implémenter get_administrateur_by_id
        $administrateur = ['user_id' => $admin_id]; // Temporaire
    }

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $resultat = traiter_formulaire_administrateur($admin_id);
        if ($resultat['succes']) {
            set_flash_message('success', $resultat['message']);
            redirect(BASE_URL . 'personnel/administrateurs');
            return;
        } else {
            $erreurs = $resultat['erreurs'];
            $administrateur = $_POST; // Conserver les données saisies
        }
    }

    // Préparation des données pour la vue
    $donnees_vue = [
        'administrateur' => $administrateur,
        'erreurs' => $erreurs,
        'succes' => $succes,
        'roles_disponibles' => ROLES_ADMIN,
        'genres' => GENRES,
        'est_modification' => ($admin_id !== null),
        'titre_page' => $admin_id ? 'Modifier un Administrateur' : 'Ajouter un Administrateur',
        'sous_titre' => $admin_id ? 'Modification des informations' : 'Création d\'un nouveau compte administratif'
    ];

    // Chargement de la vue
    require_once VIEWS_PATH . '/personnel/formulaire_administrateur.php';
}

/**
 * Traite le formulaire d'ajout/modification d'un administrateur
 */
function traiter_formulaire_administrateur($admin_id = null)
{
    $erreurs = [];

    // Récupération et nettoyage des données
    $donnees = [
        'identifiant' => trim($_POST['identifiant'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'role' => trim($_POST['role'] ?? 'secretaire'),
        'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
        'confirmer_mot_de_passe' => $_POST['confirmer_mot_de_passe'] ?? ''
    ];

    // Validation des données
    if (empty($donnees['identifiant'])) {
        $erreurs['identifiant'] = 'L\'identifiant est obligatoire.';
    }

    if (empty($donnees['email'])) {
        $erreurs['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'L\'adresse email n\'est pas valide.';
    }

    if (empty($donnees['nom'])) {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    }

    if (empty($donnees['prenom'])) {
        $erreurs['prenom'] = 'Le prénom est obligatoire.';
    }

    if (!$admin_id && empty($donnees['mot_de_passe'])) {
        $erreurs['mot_de_passe'] = 'Le mot de passe est obligatoire.';
    }

    if (!empty($donnees['mot_de_passe']) && strlen($donnees['mot_de_passe']) < 6) {
        $erreurs['mot_de_passe'] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }

    if ($donnees['mot_de_passe'] !== $donnees['confirmer_mot_de_passe']) {
        $erreurs['confirmer_mot_de_passe'] = 'Les mots de passe ne correspondent pas.';
    }

    // Si pas d'erreurs, procéder à la sauvegarde
    if (empty($erreurs)) {
        // TODO: Implémenter create_administrateur et update_administrateur
        $resultat = true; // Temporaire
        $message = $admin_id ? 'Administrateur modifié avec succès.' : 'Administrateur créé avec succès.';

        if ($resultat) {
            return ['succes' => true, 'message' => $message];
        } else {
            $erreurs['general'] = 'Erreur lors de la sauvegarde.';
        }
    }

    return ['succes' => false, 'erreurs' => $erreurs];
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Affiche le tableau de bord du personnel
 */
function afficher_tableau_bord_personnel()
{
    $statistiques = get_statistiques_personnel();

    $donnees_vue = [
        'statistiques' => $statistiques,
        'titre_page' => 'Tableau de Bord - Personnel',
        'sous_titre' => 'Vue d\'ensemble du personnel scolaire'
    ];

    require_once VIEWS_PATH . '/personnel/tableau_bord.php';
}

/**
 * Exporte la liste des professeurs en CSV
 */
function exporter_professeurs_csv()
{
    $professeurs = get_professeurs_pagines(1, 1000)['professeurs'];

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=professeurs_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');

    // En-têtes
    fputcsv($output, [
        'Matricule', 'Nom', 'Post-nom', 'Prénom', 'Téléphone', 'Email',
        'Spécialité', 'Statut', 'Type de contrat', 'Date d\'embauche'
    ]);

    // Données
    foreach ($professeurs as $prof) {
        fputcsv($output, [
            $prof['matricule_prof'],
            $prof['nom'],
            $prof['post_nom'],
            $prof['prenom'],
            $prof['telephone'],
            $prof['email'],
            $prof['specialite'],
            $prof['statut'],
            $prof['type_contrat'],
            $prof['date_embauche']
        ]);
    }

    fclose($output);
    exit;
}
?>