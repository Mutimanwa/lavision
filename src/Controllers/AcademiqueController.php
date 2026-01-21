<?php
/**
 * Contrôleur pour la gestion académique
 * Gère les classes, matières, horaires et autres éléments académiques
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Models/AcademiqueModel.php';
require_once __DIR__ . '/../Services/validation.php';
require_once __DIR__ . '/../Services/validation_academique.php';

// =============================================
// CONSTANTES SPÉCIFIQUES À L'ACADÉMIQUE
// =============================================

/**
 * Niveaux scolaires disponibles
 */
define('NIVEAUX_SCOLAIRES', [
    'maternelle' => 'Maternelle',
    'primaire' => 'Primaire',
    'secondaire' => 'Secondaire',
    'lycee' => 'Lycée'
]);

/**
 * Sections disponibles
 */
define('SECTIONS', [
    'generale' => 'Générale',
    'technique' => 'Technique',
    'litteraire' => 'Littéraire',
    'scientifique' => 'Scientifique'
]);

/**
 * Types de matières
 */
define('TYPES_MATIERES', [
    'fondamentale' => 'Fondamentale',
    'optionnelle' => 'Optionnelle',
    'specialisee' => 'Spécialisée'
]);

/**
 * Jours de la semaine
 */
define('JOURS_SEMAINE', [
    1 => 'Lundi',
    2 => 'Mardi',
    3 => 'Mercredi',
    4 => 'Jeudi',
    5 => 'Vendredi',
    6 => 'Samedi'
]);

/**
 * Périodes de la journée
 */
define('PERIODES_JOURNEE', [
    'matin' => 'Matin',
    'apres_midi' => 'Après-midi',
    'soir' => 'Soir'
]);

// =============================================
// FONCTIONS DE GESTION DES OPTIONS ACADÉMIQUES
// =============================================

/**
 * Affiche la page des options académiques
 */
function afficher_options_academiques(): void
{
    // Récupération des données
    $niveaux = get_niveaux_academiques();
    $sections = get_sections_academiques();
    $annee_scolaire = get_annee_scolaire_active();

    $data = [
        'niveaux' => $niveaux,
        'sections' => $sections,
        'annee_scolaire' => $annee_scolaire,
        'niveaux_constantes' => NIVEAUX_SCOLAIRES,
        'sections_constantes' => SECTIONS
    ];

    require_once VIEWS_PATH . '/academique/options.php';
}

/**
 * Traite la modification des options académiques
 */
function traiter_modification_options(): void
{
    // Vérification des permissions
    if (!hasPermission('academique_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    try {
        $modifications = [];

        // Modification des niveaux
        if (isset($_POST['niveaux'])) {
            foreach ($_POST['niveaux'] as $id => $niveau) {
                if (isset($niveau['actif'])) {
                    $modifications[] = modifier_niveau_academique($id, ['actif' => 1]);
                } else {
                    $modifications[] = modifier_niveau_academique($id, ['actif' => 0]);
                }
            }
        }

        // Modification des sections
        if (isset($_POST['sections'])) {
            foreach ($_POST['sections'] as $id => $section) {
                if (isset($section['actif'])) {
                    $modifications[] = modifier_section_academique($id, ['actif' => 1]);
                } else {
                    $modifications[] = modifier_section_academique($id, ['actif' => 0]);
                }
            }
        }

        logAction('Options académiques modifiées', 'Traitement des modifications',['modifications' => count($modifications)]);
        set_message_succes("Les options académiques ont été mises à jour avec succès.");
        redirect('academique/options');

    } catch (Exception $e) {
        logError('Erreur modification options académiques', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de la modification des options académiques.");
    }
}

// =============================================
// FONCTIONS DE GESTION DES CLASSES
// =============================================

/**
 * Affiche la liste des classes
 */
function afficher_gestion_classes(): void
{
    // Récupération des paramètres de filtrage
    $filtres = [
        'niveau' => $_GET['niveau'] ?? '',
        'section' => $_GET['section'] ?? '',
        'annee_scolaire' => $_GET['annee_scolaire'] ?? ''
    ];

    // Paramètres de pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $par_page = 20;

    try {
        // Récupération des données
        $classes = get_classes_pagines($filtres, $page, $par_page);
        $total_classes = compter_classes($filtres);
        $total_pages = ceil($total_classes / $par_page);

        // Données pour les filtres
        $niveaux = get_niveaux_actifs();
        $sections = get_sections_actives();
        $annees_scolaires = get_annees_scolaires();

        $data = [
            'classes' => $classes,
            'filtres' => $filtres,
            'pagination' => [
                'page_actuelle' => $page,
                'total_pages' => $total_pages,
                'total_classes' => $total_classes,
                'par_page' => $par_page
            ],
            'niveaux' => $niveaux,
            'sections' => $sections,
            'annees_scolaires' => $annees_scolaires,
            'niveaux_constantes' => NIVEAUX_SCOLAIRES,
            'sections_constantes' => SECTIONS
        ];

        require_once VIEWS_PATH . '/academique/classes.php';

    } catch (Exception $e) {
        logError('Erreur affichage classes', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement des classes.");
    }
}

/**
 * Affiche le formulaire d'ajout d'une classe
 */
function afficher_formulaire_ajout_classe(): void
{
    // Vérification des permissions
    if (!hasPermission('academique_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Données nécessaires
    $niveaux = get_niveaux_actifs();
    $sections = get_sections_actives();
    $annees_scolaires = get_annees_scolaires();
    $professeurs = get_professeurs_disponibles();

    $data = [
        'niveaux' => $niveaux,
        'sections' => $sections,
        'annees_scolaires' => $annees_scolaires,
        'professeurs' => $professeurs,
        'niveaux_constantes' => NIVEAUX_SCOLAIRES,
        'sections_constantes' => SECTIONS,
        'mode' => 'ajout'
    ];

    require_once VIEWS_PATH . '/academique/formulaire_classe.php';
}

/**
 * Traite l'ajout d'une nouvelle classe
 */
function traiter_ajout_classe(): void
{
    // Vérification des permissions
    if (!hasPermission('academique_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    try {
        // Validation des données
        $donnees = valider_donnees_classe($_POST);

        if (empty($donnees['erreurs'])) {
            // Création de la classe
            $id_classe = ajouter_classe($donnees['validees']);

            if ($id_classe) {
                logAction('Classe ajoutée', 'Traitement ajout classe',['id_classe' => $id_classe, 'nom' => $donnees['validees']['nom_classe']]);
                set_message_succes("La classe a été ajoutée avec succès.");
                redirect('academique/classes');
            } else {
                afficher_erreur("Erreur lors de l'ajout de la classe.");
            }
        } else {
            // Réaffichage du formulaire avec erreurs
            $niveaux = get_niveaux_actifs();
            $sections = get_sections_actives();
            $annees_scolaires = get_annees_scolaires();
            $professeurs = get_professeurs_disponibles();

            $data = [
                'niveaux' => $niveaux,
                'sections' => $sections,
                'annees_scolaires' => $annees_scolaires,
                'professeurs' => $professeurs,
                'niveaux_constantes' => NIVEAUX_SCOLAIRES,
                'sections_constantes' => SECTIONS,
                'mode' => 'ajout',
                'valeurs' => $_POST,
                'erreurs' => $donnees['erreurs']
            ];

            require_once VIEWS_PATH . '/academique/formulaire_classe.php';
        }

    } catch (Exception $e) {
        logError('Erreur ajout classe', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de l'ajout de la classe.");
    }
}

// =============================================
// FONCTIONS DE GESTION DES MATIÈRES
// =============================================

/**
 * Affiche la liste des matières
 */
function afficher_gestion_matieres(): void
{
    // Récupération des paramètres de filtrage
    $filtres = [
        'type' => $_GET['type'] ?? '',
        'niveau' => $_GET['niveau'] ?? ''
    ];

    // Paramètres de pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $par_page = 20;

    try {
        // Récupération des données
        $matieres = get_matieres_pagines($filtres, $page, $par_page);
        $total_matieres = compter_matieres($filtres);
        $total_pages = ceil($total_matieres / $par_page);

        // Données pour les filtres
        $niveaux = get_niveaux_actifs();

        $data = [
            'matieres' => $matieres,
            'filtres' => $filtres,
            'pagination' => [
                'page_actuelle' => $page,
                'total_pages' => $total_pages,
                'total_matieres' => $total_matieres,
                'par_page' => $par_page
            ],
            'niveaux' => $niveaux,
            'types_matieres' => TYPES_MATIERES,
            'niveaux_constantes' => NIVEAUX_SCOLAIRES
        ];

        require_once VIEWS_PATH . '/academique/matieres.php';

    } catch (Exception $e) {
        logError('Erreur affichage matières', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement des matières.");
    }
}

/**
 * Affiche le formulaire d'ajout d'une matière
 */
function afficher_formulaire_ajout_matiere(): void
{
    // Vérification des permissions
    if (!hasPermission('academique_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Données nécessaires
    $niveaux = get_niveaux_actifs();

    $data = [
        'niveaux' => $niveaux,
        'types_matieres' => TYPES_MATIERES,
        'niveaux_constantes' => NIVEAUX_SCOLAIRES,
        'mode' => 'ajout'
    ];

    require_once VIEWS_PATH . '/academique/formulaire_matiere.php';
}

/**
 * Traite l'ajout d'une nouvelle matière
 */
function traiter_ajout_matiere(): void
{
    // Vérification des permissions
    if (!hasPermission('academique_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    try {
        // Validation des données
        $donnees = valider_donnees_matiere($_POST);

        if (empty($donnees['erreurs'])) {
            // Création de la matière
            $id_matiere = ajouter_matiere($donnees['validees']);

            if ($id_matiere) {
                logAction('Matière ajoutée', 'Traitement ajout matière',['id_matiere' => $id_matiere, 'nom' => $donnees['validees']['nom_matiere']]);
                set_message_succes("La matière a été ajoutée avec succès.");
                redirect('academique/matieres');
            } else {
                afficher_erreur("Erreur lors de l'ajout de la matière.");
            }
        } else {
            // Réaffichage du formulaire avec erreurs
            $niveaux = get_niveaux_actifs();

            $data = [
                'niveaux' => $niveaux,
                'types_matieres' => TYPES_MATIERES,
                'niveaux_constantes' => NIVEAUX_SCOLAIRES,
                'mode' => 'ajout',
                'valeurs' => $_POST,
                'erreurs' => $donnees['erreurs']
            ];

            require_once VIEWS_PATH . '/academique/formulaire_matiere.php';
        }

    } catch (Exception $e) {
        logError('Erreur ajout matière', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de l'ajout de la matière.");
    }
}

// =============================================
// FONCTIONS DE GESTION DES HORAIRES
// =============================================

/**
 * Affiche la gestion des horaires
 */
function afficher_gestion_horaires(): void
{
    // Récupération des paramètres
    $id_classe = intval($_GET['classe'] ?? 0);
    $jour_semaine = intval($_GET['jour'] ?? date('N')); // Jour actuel par défaut

    try {
        // Récupération des données
        $classes = get_classes_actives();
        $horaires = [];

        if ($id_classe > 0) {
            $horaires = get_horaires_classe($id_classe, $jour_semaine);
        }

        $matieres = get_matieres_actives();
        $professeurs = get_professeurs_disponibles();

        $data = [
            'classes' => $classes,
            'horaires' => $horaires,
            'matieres' => $matieres,
            'professeurs' => $professeurs,
            'id_classe_selectionnee' => $id_classe,
            'jour_selectionne' => $jour_semaine,
            'jours_semaine' => JOURS_SEMAINE,
            'periodes_journee' => PERIODES_JOURNEE
        ];

        require_once VIEWS_PATH . '/academique/horaires.php';

    } catch (Exception $e) {
        logError('Erreur affichage horaires', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors du chargement des horaires.");
    }
}

/**
 * Traite la sauvegarde des horaires
 */
function traiter_sauvegarde_horaires(): void
{
    // Vérification des permissions
    if (!a_permission('academique_gerer')) {
        afficher_erreur("Vous n'avez pas les permissions nécessaires.", 403);
        return;
    }

    // Vérification du token CSRF
    if (!verifier_csrf_token($_POST['csrf_token'] ?? '')) {
        afficher_erreur("Token de sécurité invalide.", 403);
        return;
    }

    $id_classe = intval($_POST['id_classe'] ?? 0);
    $jour_semaine = intval($_POST['jour_semaine'] ?? 0);

    if ($id_classe <= 0 || $jour_semaine <= 0) {
        afficher_erreur("Classe et jour de la semaine requis.", 400);
        return;
    }

    try {
        $horaires_modifies = 0;

        // Traitement des horaires soumis
        if (isset($_POST['horaires'])) {
            foreach ($_POST['horaires'] as $index => $horaire) {
                $heure_debut = $horaire['heure_debut'] ?? '';
                $heure_fin = $horaire['heure_fin'] ?? '';
                $id_matiere = intval($horaire['id_matiere'] ?? 0);
                $id_professeur = intval($horaire['id_professeur'] ?? 0);

                // Validation basique
                if (!empty($heure_debut) && !empty($heure_fin) && $id_matiere > 0) {
                    // Création ou mise à jour de l'horaire
                    $donnees_horaire = [
                        'id_classe' => $id_classe,
                        'jour_semaine' => $jour_semaine,
                        'heure_debut' => $heure_debut,
                        'heure_fin' => $heure_fin,
                        'id_matiere' => $id_matiere,
                        'id_professeur' => $id_professeur
                    ];

                    if (isset($horaire['id']) && $horaire['id'] > 0) {
                        // Mise à jour
                        modifier_horaire($horaire['id'], $donnees_horaire);
                    } else {
                        // Création
                        ajouter_horaire($donnees_horaire);
                    }

                    $horaires_modifies++;
                }
            }
        }

        logAction('Horaires sauvegardés', 'Traitement des sauvegardes d\'horaires',[
            'id_classe' => $id_classe,
            'jour_semaine' => $jour_semaine,
            'horaires_modifies' => $horaires_modifies
        ]);

        set_message_succes("$horaires_modifies horaire(s) sauvegardé(s) avec succès.");
        redirect('academique/horaires', ['classe' => $id_classe, 'jour' => $jour_semaine]);

    } catch (Exception $e) {
        logError('Erreur sauvegarde horaires', ['error' => $e->getMessage()]);
        afficher_erreur("Erreur lors de la sauvegarde des horaires.");
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================