<?php
/**
 * Contrôleur pour la gestion financière
 * Gère les paiements, quittances, sessions de caisse et rapports financiers
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Models/FinanceModel.php';

// =============================================
// CONSTANTES DU CONTRÔLEUR
// =============================================

/**
 * Actions disponibles dans le contrôleur finance
 */
define('ACTION_LISTER_PAIEMENTS', 'lister_paiements');
define('ACTION_VOIR_PAIEMENT', 'voir_paiement');
define('ACTION_CREER_PAIEMENT', 'creer_paiement');
define('ACTION_MODIFIER_PAIEMENT', 'modifier_paiement');
define('ACTION_SUPPRIMER_PAIEMENT', 'supprimer_paiement');
define('ACTION_GENERER_QUITTANCE', 'generer_quittance');
define('ACTION_OUVRIR_SESSION', 'ouvrir_session');
define('ACTION_FERMER_SESSION', 'fermer_session');
define('ACTION_RAPPORTS_FINANCIERS', 'rapports_financiers');
define('ACTION_PAIEMENTS_EN_RETARD', 'paiements_en_retard');

// =============================================
// FONCTIONS PRINCIPALES DU CONTRÔLEUR
// =============================================

/**
 * Point d'entrée principal du contrôleur finance
 */
function handle_finance_request(string $action = ACTION_LISTER_PAIEMENTS): void
{
    // Vérification des permissions
    if (!a_permission('finance_access')) {
        redirect_with_error('Accès non autorisé', '/dashboard');
        return;
    }

    switch ($action) {
        case ACTION_LISTER_PAIEMENTS:
            lister_paiements();
            break;

        case ACTION_VOIR_PAIEMENT:
            voir_paiement();
            break;

        case ACTION_CREER_PAIEMENT:
            creer_paiement();
            break;

        case ACTION_MODIFIER_PAIEMENT:
            modifier_paiement();
            break;

        case ACTION_SUPPRIMER_PAIEMENT:
            supprimer_paiement();
            break;

        case ACTION_GENERER_QUITTANCE:
            generer_quittance();
            break;

        case ACTION_OUVRIR_SESSION:
            ouvrir_session();
            break;

        case ACTION_FERMER_SESSION:
            fermer_session();
            break;

        case ACTION_RAPPORTS_FINANCIERS:
            rapports_financiers();
            break;

        case ACTION_PAIEMENTS_EN_RETARD:
            paiements_en_retard();
            break;

        default:
            redirect_with_error('Action non reconnue', '/finance');
            break;
    }
}

/**
 * Affiche la liste des paiements avec pagination et filtres
 */
function lister_paiements(): void
{
    // Récupération des paramètres
    $page = (int)($_GET['page'] ?? 1);
    $par_page = (int)($_GET['par_page'] ?? 20);

    // Construction des filtres
    $filtres = [];
    if (!empty($_GET['statut'])) $filtres['statut'] = sanitize_input($_GET['statut']);
    if (!empty($_GET['type_frais'])) $filtres['type_frais'] = sanitize_input($_GET['type_frais']);
    if (!empty($_GET['annee_id'])) $filtres['annee_id'] = (int)$_GET['annee_id'];
    if (!empty($_GET['eleve_id'])) $filtres['eleve_id'] = (int)$_GET['eleve_id'];
    if (!empty($_GET['date_debut'])) $filtres['date_debut'] = sanitize_input($_GET['date_debut']);
    if (!empty($_GET['date_fin'])) $filtres['date_fin'] = sanitize_input($_GET['date_fin']);
    if (!empty($_GET['recherche'])) $filtres['recherche'] = sanitize_input($_GET['recherche']);

    // Récupération des paiements
    $resultat = get_paiements_pagines($page, $par_page, $filtres);

    // Statistiques générales
    $statistiques = get_statistiques_financieres($filtres);

    // Récupération des données pour les filtres
    $annees_scolaires = get_annees_scolaires_actives();
    $eleves = get_eleves_for_select();

    // Préparation des données pour la vue
    $data = [
        'paiements' => $resultat['paiements'],
        'pagination' => [
            'total' => $resultat['total'],
            'pages' => $resultat['pages'],
            'page_actuelle' => $resultat['page_actuelle'],
            'par_page' => $par_page
        ],
        'filtres' => $filtres,
        'statistiques' => $statistiques,
        'annees_scolaires' => $annees_scolaires,
        'eleves' => $eleves,
        'types_frais' => get_types_frais(),
        'statuts_paiement' => get_statuts_paiement()
    ];

    // Affichage de la vue
    render_finance_view('liste_paiements', $data);
}

/**
 * Affiche les détails d'un paiement
 */
function voir_paiement(): void
{
    $paiement_id = (int)($_GET['id'] ?? 0);

    if (!$paiement_id) {
        redirect_with_error('ID de paiement manquant', '/finance');
        return;
    }

    // Récupération du paiement
    $paiement = get_paiement_by_id($paiement_id);

    if (!$paiement) {
        redirect_with_error('Paiement non trouvé', '/finance');
        return;
    }

    // Récupération des quittances associées
    $quittances = get_quittances_by_paiement($paiement_id);

    // Récupération de l'historique des paiements
    $historique = get_historique_paiement($paiement_id);

    // Préparation des données pour la vue
    $data = [
        'paiement' => $paiement,
        'quittances' => $quittances,
        'historique' => $historique
    ];

    // Affichage de la vue
    render_finance_view('detail_paiement', $data);
}

/**
 * Affiche le formulaire de création d'un paiement
 */
function creer_paiement(): void
{
    if (!a_permission('finance_create')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    // Récupération des données pour les listes déroulantes
    $annees_scolaires = get_annees_scolaires_actives();
    $eleves = get_eleves_for_select();

    // Données par défaut
    $data = [
        'annees_scolaires' => $annees_scolaires,
        'eleves' => $eleves,
        'types_frais' => get_types_frais(),
        'modes_paiement' => get_modes_paiement(),
        'paiement' => null,
        'errors' => []
    ];

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = traiter_creation_paiement();
        if ($result['success']) {
            redirect_with_success('Paiement créé avec succès', '/finance?action=voir_paiement&id=' . $result['paiement_id']);
            return;
        } else {
            $data['errors'] = $result['errors'];
        }
    }

    // Affichage de la vue
    render_finance_view('formulaire_paiement', $data);
}

/**
 * Affiche le formulaire de modification d'un paiement
 */
function modifier_paiement(): void
{
    if (!a_permission('finance_edit')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    $paiement_id = (int)($_GET['id'] ?? 0);

    if (!$paiement_id) {
        redirect_with_error('ID de paiement manquant', '/finance');
        return;
    }

    // Récupération du paiement
    $paiement = get_paiement_by_id($paiement_id);

    if (!$paiement) {
        redirect_with_error('Paiement non trouvé', '/finance');
        return;
    }

    // Récupération des données pour les listes déroulantes
    $annees_scolaires = get_annees_scolaires_actives();
    $eleves = get_eleves_for_select();

    $data = [
        'annees_scolaires' => $annees_scolaires,
        'eleves' => $eleves,
        'types_frais' => get_types_frais(),
        'modes_paiement' => get_modes_paiement(),
        'paiement' => $paiement,
        'errors' => []
    ];

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = traiter_modification_paiement($paiement_id);
        if ($result['success']) {
            redirect_with_success('Paiement modifié avec succès', '/finance?action=voir_paiement&id=' . $paiement_id);
            return;
        } else {
            $data['errors'] = $result['errors'];
        }
    }

    // Affichage de la vue
    render_finance_view('formulaire_paiement', $data);
}

/**
 * Supprime un paiement
 */
function supprimer_paiement(): void
{
    if (!a_permission('finance_delete')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    $paiement_id = (int)($_POST['paiement_id'] ?? 0);

    if (!$paiement_id) {
        redirect_with_error('ID de paiement manquant', '/finance');
        return;
    }

    // Vérification du token CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        redirect_with_error('Token de sécurité invalide', '/finance');
        return;
    }

    // Tentative de suppression
    if (delete_paiement($paiement_id)) {
        redirect_with_success('Paiement supprimé avec succès', '/finance');
    } else {
        redirect_with_error('Impossible de supprimer ce paiement (quittances existantes)', '/finance');
    }
}

/**
 * Génère une quittance pour un paiement
 */
function generer_quittance(): void
{
    if (!a_permission('finance_create')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    $paiement_id = (int)($_GET['paiement_id'] ?? 0);

    if (!$paiement_id) {
        redirect_with_error('ID de paiement manquant', '/finance');
        return;
    }

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = traiter_generation_quittance($paiement_id);
        if ($result['success']) {
            redirect_with_success('Quittance générée avec succès', '/finance?action=voir_paiement&id=' . $paiement_id);
            return;
        } else {
            redirect_with_error($result['message'], '/finance?action=voir_paiement&id=' . $paiement_id);
            return;
        }
    }

    // Récupération du paiement pour pré-remplir
    $paiement = get_paiement_by_id($paiement_id);

    if (!$paiement) {
        redirect_with_error('Paiement non trouvé', '/finance');
        return;
    }

    $data = [
        'paiement' => $paiement,
        'modes_paiement' => get_modes_paiement(),
        'errors' => []
    ];

    // Affichage de la vue
    render_finance_view('formulaire_quittance', $data);
}

/**
 * Ouvre une session de caisse
 */
function ouvrir_session(): void
{
    if (!a_permission('caisse_open')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    // Vérifier si une session est déjà ouverte
    $session_active = get_session_caisse_active(get_current_user_id());

    if ($session_active) {
        redirect_with_error('Une session de caisse est déjà ouverte', '/finance');
        return;
    }

    $data = ['errors' => []];

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = traiter_ouverture_session();
        if ($result['success']) {
            redirect_with_success('Session de caisse ouverte avec succès', '/finance');
            return;
        } else {
            $data['errors'] = $result['errors'];
        }
    }

    // Affichage de la vue
    render_finance_view('ouvrir_session', $data);
}

/**
 * Ferme une session de caisse
 */
function fermer_session(): void
{
    if (!a_permission('caisse_close')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    // Récupération de la session active
    $session = get_session_caisse_active(get_current_user_id());

    if (!$session) {
        redirect_with_error('Aucune session de caisse ouverte', '/finance');
        return;
    }

    // Calcul du montant théorique
    $montant_theorique = calculer_montant_theorique_session($session['session_id']);

    $data = [
        'session' => $session,
        'montant_theorique' => $montant_theorique,
        'errors' => []
    ];

    // Traitement du formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = traiter_fermeture_session($session['session_id']);
        if ($result['success']) {
            redirect_with_success('Session de caisse fermée avec succès', '/finance');
            return;
        } else {
            $data['errors'] = $result['errors'];
        }
    }

    // Affichage de la vue
    render_finance_view('fermer_session', $data);
}

/**
 * Affiche les rapports financiers
 */
function rapports_financiers(): void
{
    if (!a_permission('finance_reports')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    // Récupération des paramètres
    $annee_id = (int)($_GET['annee_id'] ?? 0);
    $type_rapport = sanitize_input($_GET['type'] ?? 'general');

    // Récupération des données selon le type de rapport
    switch ($type_rapport) {
        case 'mensuel':
            $data = generer_rapport_mensuel($annee_id);
            break;
        case 'eleve':
            $data = generer_rapport_par_eleve($annee_id);
            break;
        case 'caissier':
            $data = generer_rapport_par_caissier($annee_id);
            break;
        default:
            $data = generer_rapport_general($annee_id);
            break;
    }

    $data['annees_scolaires'] = get_annees_scolaires_actives();
    $data['type_rapport'] = $type_rapport;
    $data['annee_id'] = $annee_id;

    // Affichage de la vue
    render_finance_view('rapports_financiers', $data);
}

/**
 * Affiche les paiements en retard
 */
function paiements_en_retard(): void
{
    if (!a_permission('finance_access')) {
        redirect_with_error('Permission insuffisante', '/finance');
        return;
    }

    $jours_retard = (int)($_GET['jours'] ?? 30);

    // Récupération des paiements en retard
    $paiements_retard = get_paiements_en_retard($jours_retard);

    // Statistiques des retards
    $statistiques_retard = [
        'total_retard' => count($paiements_retard),
        'montant_total_retard' => array_sum(array_column($paiements_retard, 'reste_a_payer')),
        'jours_retard_moyen' => count($paiements_retard) > 0 ? array_sum(array_column($paiements_retard, 'jours_retard')) / count($paiements_retard) : 0
    ];

    $data = [
        'paiements_retard' => $paiements_retard,
        'statistiques_retard' => $statistiques_retard,
        'jours_retard' => $jours_retard
    ];

    // Affichage de la vue
    render_finance_view('paiements_en_retard', $data);
}

// =============================================
// FONCTIONS DE TRAITEMENT DES FORMULAIRES
// =============================================

/**
 * Traite la création d'un paiement
 */
function traiter_creation_paiement(): array
{
    // Validation CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'errors' => ['Token de sécurité invalide']];
    }

    // Récupération et validation des données
    $data = [
        'eleve_id' => (int)($_POST['eleve_id'] ?? 0),
        'annee_id' => (int)($_POST['annee_id'] ?? 0),
        'type_frais' => sanitize_input($_POST['type_frais'] ?? ''),
        'libelle' => sanitize_input($_POST['libelle'] ?? ''),
        'montant_total' => (float)($_POST['montant_total'] ?? 0),
        'montant_paye' => (float)($_POST['montant_paye'] ?? 0),
        'date_echeance' => sanitize_input($_POST['date_echeance'] ?? ''),
        'mode_paiement' => sanitize_input($_POST['mode_paiement'] ?? 'Espece'),
        'notes' => sanitize_input($_POST['notes'] ?? ''),
        'caissier_id' => get_current_user_id()
    ];

    // Validation des données
    $errors = valider_donnees_paiement($data);

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Détermination du statut
    $data['statut'] = determiner_statut_paiement($data['montant_total'], $data['montant_paye']);

    // Création du paiement
    $paiement_id = create_paiement($data);

    if ($paiement_id) {
        return ['success' => true, 'paiement_id' => $paiement_id];
    } else {
        return ['success' => false, 'errors' => ['Erreur lors de la création du paiement']];
    }
}

/**
 * Traite la modification d'un paiement
 */
function traiter_modification_paiement(int $paiement_id): array
{
    // Validation CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'errors' => ['Token de sécurité invalide']];
    }

    // Récupération et validation des données
    $data = [
        'type_frais' => sanitize_input($_POST['type_frais'] ?? ''),
        'libelle' => sanitize_input($_POST['libelle'] ?? ''),
        'montant_total' => (float)($_POST['montant_total'] ?? 0),
        'montant_paye' => (float)($_POST['montant_paye'] ?? 0),
        'date_echeance' => sanitize_input($_POST['date_echeance'] ?? ''),
        'mode_paiement' => sanitize_input($_POST['mode_paiement'] ?? 'Espece'),
        'notes' => sanitize_input($_POST['notes'] ?? ''),
        'caissier_id' => get_current_user_id()
    ];

    // Validation des données
    $errors = valider_donnees_paiement($data, false);

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Détermination du statut
    $data['statut'] = determiner_statut_paiement($data['montant_total'], $data['montant_paye']);

    // Modification du paiement
    if (update_paiement($paiement_id, $data)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'errors' => ['Erreur lors de la modification du paiement']];
    }
}

/**
 * Traite la génération d'une quittance
 */
function traiter_generation_quittance(int $paiement_id): array
{
    // Validation CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'message' => 'Token de sécurité invalide'];
    }

    // Récupération des données
    $data = [
        'montant' => (float)($_POST['montant'] ?? 0),
        'mode_paiement' => sanitize_input($_POST['mode_paiement'] ?? 'Espece'),
        'reference_banque' => sanitize_input($_POST['reference_banque'] ?? ''),
        'date_quittance' => sanitize_input($_POST['date_quittance'] ?? date('Y-m-d H:i:s')),
        'caissier_id' => get_current_user_id()
    ];

    // Validation
    if ($data['montant'] <= 0) {
        return ['success' => false, 'message' => 'Le montant doit être positif'];
    }

    // Création de la quittance
    $quittance_id = create_quittance($paiement_id, $data);

    if ($quittance_id) {
        return ['success' => true, 'quittance_id' => $quittance_id];
    } else {
        return ['success' => false, 'message' => 'Erreur lors de la génération de la quittance'];
    }
}

/**
 * Traite l'ouverture d'une session de caisse
 */
function traiter_ouverture_session(): array
{
    // Validation CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'errors' => ['Token de sécurité invalide']];
    }

    $montant_ouverture = (float)($_POST['montant_ouverture'] ?? 0);

    if ($montant_ouverture < 0) {
        return ['success' => false, 'errors' => ['Le montant d\'ouverture ne peut pas être négatif']];
    }

    $session_id = ouvrir_session_caisse(get_current_user_id(), $montant_ouverture);

    if ($session_id) {
        return ['success' => true, 'session_id' => $session_id];
    } else {
        return ['success' => false, 'errors' => ['Erreur lors de l\'ouverture de la session']];
    }
}

/**
 * Traite la fermeture d'une session de caisse
 */
function traiter_fermeture_session(int $session_id): array
{
    // Validation CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        return ['success' => false, 'errors' => ['Token de sécurité invalide']];
    }

    $montant_fermeture = (float)($_POST['montant_fermeture'] ?? 0);
    $observations = sanitize_input($_POST['observations'] ?? '');

    if ($montant_fermeture < 0) {
        return ['success' => false, 'errors' => ['Le montant de fermeture ne peut pas être négatif']];
    }

    if (fermer_session_caisse($session_id, $montant_fermeture, $observations)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'errors' => ['Erreur lors de la fermeture de la session']];
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Valide les données d'un paiement
 */
function valider_donnees_paiement(array $data, bool $is_creation = true): array
{
    $errors = [];

    if ($is_creation) {
        if (empty($data['eleve_id'])) $errors[] = 'L\'élève est obligatoire';
        if (empty($data['annee_id'])) $errors[] = 'L\'année scolaire est obligatoire';
    }

    if (empty($data['type_frais'])) $errors[] = 'Le type de frais est obligatoire';
    if (empty($data['libelle'])) $errors[] = 'Le libellé est obligatoire';
    if ($data['montant_total'] <= 0) $errors[] = 'Le montant total doit être positif';
    if ($data['montant_paye'] < 0) $errors[] = 'Le montant payé ne peut pas être négatif';
    if ($data['montant_paye'] > $data['montant_total']) $errors[] = 'Le montant payé ne peut pas dépasser le montant total';

    if (!empty($data['date_echeance']) && !strtotime($data['date_echeance'])) {
        $errors[] = 'La date d\'échéance n\'est pas valide';
    }

    return $errors;
}

/**
 * Détermine le statut d'un paiement
 */
function determiner_statut_paiement(float $montant_total, float $montant_paye): string
{
    if ($montant_paye == 0) {
        return 'impaye';
    } elseif ($montant_paye >= $montant_total) {
        return 'paye';
    } else {
        return 'partiel';
    }
}

/**
 * Génère un rapport financier général
 */
function generer_rapport_financier_general(int $annee_id = 0): array
{
    $filtres = $annee_id > 0 ? ['annee_id' => $annee_id] : [];
    return ['statistiques' => get_statistiques_financieres($filtres)];
}

/**
 * Génère un rapport mensuel
 */
function generer_rapport_mensuel(int $annee_id): array
{
    // Cette fonction serait implémentée pour générer des rapports mensuels détaillés
    return ['message' => 'Rapport mensuel - Fonction à implémenter'];
}

/**
 * Génère un rapport par élève
 */
function generer_rapport_par_eleve(int $annee_id): array
{
    // Cette fonction serait implémentée pour générer des rapports par élève
    return ['message' => 'Rapport par élève - Fonction à implémenter'];
}

/**
 * Génère un rapport par caissier
 */
function generer_rapport_par_caissier(int $annee_id): array
{
    // Cette fonction serait implémentée pour générer des rapports par caissier
    return ['message' => 'Rapport par caissier - Fonction à implémenter'];
}

/**
 * Récupère l'historique d'un paiement
 */
function get_historique_paiement(int $paiement_id): array
{
    // Cette fonction récupérerait l'historique des modifications du paiement
    return [];
}

/**
 * Rend une vue du module finance
 */
function render_finance_view(string $view_name, array $data = []): void
{
    // Définition du chemin de base pour les vues finance
    $view_path = VIEWS_PATH . '/finance/' . $view_name . '.php';

    // Vérification de l'existence de la vue
    if (!file_exists($view_path)) {
        error_log("Vue finance non trouvée: $view_path");
        echo "<h1>Erreur</h1><p>Vue non trouvée: $view_name</p>";
        return;
    }

    // Extraction des données pour la vue
    extract($data);

    // Inclusion de la vue
    require $view_path;
}

// =============================================
// FONCTIONS DE DONNÉES STATIQUES
// =============================================

/**
 * Récupère les types de frais disponibles
 */
function get_types_frais(): array
{
    return [
        'scolarite' => 'Scolarité',
        'inscription' => 'Inscription',
        'transport' => 'Transport',
        'cantine' => 'Cantine',
        'activites' => 'Activités parascolaires',
        'materiel' => 'Matériel scolaire',
        'autres' => 'Autres frais'
    ];
}

/**
 * Récupère les modes de paiement disponibles
 */
function get_modes_paiement(): array
{
    return [
        'Espece' => 'Espèces',
        'Carte' => 'Carte bancaire',
        'Cheque' => 'Chèque',
        'Virement' => 'Virement bancaire',
        'Mobile' => 'Paiement mobile'
    ];
}

/**
 * Récupère les statuts de paiement disponibles
 */
function get_statuts_paiement(): array
{
    return [
        'impaye' => 'Impayé',
        'partiel' => 'Partiellement payé',
        'paye' => 'Payé'
    ];
}
?>