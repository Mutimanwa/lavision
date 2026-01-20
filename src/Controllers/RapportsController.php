<?php
/**
 * Contrôleur pour les rapports et statistiques
 * Gère la génération et l'affichage des rapports système
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Models/RapportsModel.php';

// =============================================
// CONSTANTES DU CONTRÔLEUR
// =============================================

/**
 * Actions disponibles dans le contrôleur rapports
 */
define('ACTION_TABLEAU_BORD', 'tableau_bord');
define('ACTION_RAPPORT_GENERAL', 'rapport_general');
define('ACTION_RAPPORT_ELEVES', 'rapport_eleves');
define('ACTION_RAPPORT_ACADEMIQUE', 'rapport_academique');
define('ACTION_RAPPORT_FINANCIER', 'rapport_financier');
define('ACTION_RAPPORT_SECURITE', 'rapport_securite');
define('ACTION_EXPORTER_RAPPORT', 'exporter_rapport');

// =============================================
// FONCTIONS PRINCIPALES DU CONTRÔLEUR
// =============================================

/**
 * Point d'entrée principal du contrôleur rapports
 */
function handle_rapports_request(string $action = ACTION_TABLEAU_BORD): void
{
    // Vérification des permissions
    if (!a_permission('reports_access')) {
        redirect_with_error('Accès non autorisé', '/dashboard');
        return;
    }

    switch ($action) {
        case ACTION_TABLEAU_BORD:
            afficher_tableau_bord();
            break;

        case ACTION_RAPPORT_GENERAL:
            afficher_rapport_general();
            break;

        case ACTION_RAPPORT_ELEVES:
            afficher_rapport_eleves();
            break;

        case ACTION_RAPPORT_ACADEMIQUE:
            afficher_rapport_academique();
            break;

        case ACTION_RAPPORT_FINANCIER:
            afficher_rapport_financier();
            break;

        case ACTION_RAPPORT_SECURITE:
            afficher_rapport_securite();
            break;

        case ACTION_EXPORTER_RAPPORT:
            exporter_rapport();
            break;

        default:
            redirect_with_error('Action non reconnue', '/rapports');
            break;
    }
}

/**
 * Affiche le tableau de bord avec les statistiques principales
 */
function afficher_tableau_bord(): void
{
    // Récupération des statistiques générales
    $statistiques_generales = generer_rapport_general();

    // Statistiques pour les graphiques
    $stats_graphiques = [
        'evolution_eleves' => get_evolution_eleves_6_mois(),
        'repartition_genre' => get_repartition_eleves_genre(),
        'paiements_mensuels' => get_paiements_mensuels_6_mois(),
        'activite_utilisateurs' => get_activite_utilisateurs_7_jours()
    ];

    // Alertes importantes
    $alertes = [
        'eleves_sans_classe' => compter_eleves_sans_classe(),
        'paiements_en_retard' => count(get_paiements_en_retard(30)),
        'professeurs_sans_matiere' => compter_professeurs_sans_matiere(),
        'erreurs_recent' => compter_erreurs_systeme(1)
    ];

    $data = [
        'statistiques_generales' => $statistiques_generales,
        'stats_graphiques' => $stats_graphiques,
        'alertes' => $alertes,
        'date_mise_a_jour' => date('d/m/Y H:i')
    ];

    // Affichage de la vue
    render_rapports_view('tableau_bord', $data);
}

/**
 * Affiche le rapport général du système
 */
function afficher_rapport_general(): void
{
    if (!a_permission('reports_general')) {
        redirect_with_error('Permission insuffisante', '/rapports');
        return;
    }

    // Récupération des paramètres
    $periode = sanitize_input($_GET['periode'] ?? 'general');
    $format = sanitize_input($_GET['format'] ?? 'html');

    $filtres = ['periode' => $periode];

    // Génération du rapport
    $rapport = generer_rapport_general($filtres);

    if ($format === 'pdf') {
        // Export PDF (à implémenter)
        exporter_rapport_pdf('general', $rapport);
        return;
    } elseif ($format === 'excel') {
        // Export Excel (à implémenter)
        exporter_rapport_excel('general', $rapport);
        return;
    }

    $data = [
        'rapport' => $rapport,
        'periode' => $periode,
        'format' => $format
    ];

    // Affichage de la vue
    render_rapports_view('rapport_general', $data);
}

/**
 * Affiche le rapport des élèves
 */
function afficher_rapport_eleves(): void
{
    if (!a_permission('reports_students')) {
        redirect_with_error('Permission insuffisante', '/rapports');
        return;
    }

    // Récupération des paramètres
    $statut = sanitize_input($_GET['statut'] ?? '');
    $genre = sanitize_input($_GET['genre'] ?? '');
    $annee_naissance = (int)($_GET['annee_naissance'] ?? 0);
    $classe_id = (int)($_GET['classe_id'] ?? 0);
    $format = sanitize_input($_GET['format'] ?? 'html');

    $filtres = array_filter([
        'statut' => $statut,
        'genre' => $genre,
        'annee_naissance' => $annee_naissance > 0 ? $annee_naissance : null,
        'classe_id' => $classe_id > 0 ? $classe_id : null
    ]);

    // Génération du rapport
    $rapport = generer_rapport_eleves($filtres);

    if ($format === 'pdf') {
        exporter_rapport_pdf('eleves', $rapport);
        return;
    } elseif ($format === 'excel') {
        exporter_rapport_excel('eleves', $rapport);
        return;
    }

    // Données pour les filtres
    $classes = get_classes_for_select();
    $annees_naissance = get_annees_naissance_eleves();

    $data = [
        'rapport' => $rapport,
        'filtres' => $filtres,
        'classes' => $classes,
        'annees_naissance' => $annees_naissance,
        'format' => $format
    ];

    // Affichage de la vue
    render_rapports_view('rapport_eleves', $data);
}

/**
 * Affiche le rapport académique
 */
function afficher_rapport_academique(): void
{
    if (!a_permission('reports_academic')) {
        redirect_with_error('Permission insuffisante', '/rapports');
        return;
    }

    // Récupération des paramètres
    $format = sanitize_input($_GET['format'] ?? 'html');

    // Génération du rapport
    $rapport = generer_rapport_academique();

    if ($format === 'pdf') {
        exporter_rapport_pdf('academique', $rapport);
        return;
    } elseif ($format === 'excel') {
        exporter_rapport_excel('academique', $rapport);
        return;
    }

    $data = [
        'rapport' => $rapport,
        'format' => $format
    ];

    // Affichage de la vue
    render_rapports_view('rapport_academique', $data);
}

/**
 * Affiche le rapport financier
 */
function afficher_rapport_financier(): void
{
    if (!a_permission('reports_financial')) {
        redirect_with_error('Permission insuffisante', '/rapports');
        return;
    }

    // Récupération des paramètres
    $annee_id = (int)($_GET['annee_id'] ?? 0);
    $format = sanitize_input($_GET['format'] ?? 'html');

    $filtres = $annee_id > 0 ? ['annee_id' => $annee_id] : [];

    // Génération du rapport
    $rapport = generer_rapport_financier($filtres);

    if ($format === 'pdf') {
        exporter_rapport_pdf('financier', $rapport);
        return;
    } elseif ($format === 'excel') {
        exporter_rapport_excel('financier', $rapport);
        return;
    }

    // Données pour les filtres
    $annees_scolaires = get_annees_scolaires_actives();

    $data = [
        'rapport' => $rapport,
        'annees_scolaires' => $annees_scolaires,
        'annee_id' => $annee_id,
        'format' => $format
    ];

    // Affichage de la vue
    render_rapports_view('rapport_financier', $data);
}

/**
 * Affiche le rapport de sécurité
 */
function afficher_rapport_securite(): void
{
    if (!a_permission('reports_security')) {
        redirect_with_error('Permission insuffisante', '/rapports');
        return;
    }

    // Récupération des paramètres
    $periode_heures = (int)($_GET['periode_heures'] ?? 24);
    $format = sanitize_input($_GET['format'] ?? 'html');

    $filtres = ['periode_heures' => $periode_heures];

    // Génération du rapport
    $rapport = generer_rapport_securite($filtres);

    if ($format === 'pdf') {
        exporter_rapport_pdf('securite', $rapport);
        return;
    } elseif ($format === 'excel') {
        exporter_rapport_excel('securite', $rapport);
        return;
    }

    $data = [
        'rapport' => $rapport,
        'periode_heures' => $periode_heures,
        'format' => $format
    ];

    // Affichage de la vue
    render_rapports_view('rapport_securite', $data);
}

/**
 * Exporte un rapport dans différents formats
 */
function exporter_rapport(): void
{
    $type_rapport = sanitize_input($_GET['type'] ?? '');
    $format = sanitize_input($_GET['format'] ?? 'pdf');

    if (empty($type_rapport)) {
        redirect_with_error('Type de rapport manquant', '/rapports');
        return;
    }

    // Génération du rapport selon le type
    $rapport = match($type_rapport) {
        'general' => generer_rapport_general(),
        'eleves' => generer_rapport_eleves(),
        'academique' => generer_rapport_academique(),
        'financier' => generer_rapport_financier(),
        'securite' => generer_rapport_securite(),
        default => []
    };

    if (empty($rapport)) {
        redirect_with_error('Erreur lors de la génération du rapport', '/rapports');
        return;
    }

    // Export selon le format
    if ($format === 'pdf') {
        $result = exporter_rapport_pdf($type_rapport, $rapport);
        echo $result; // Temporaire, à remplacer par téléchargement réel
    } elseif ($format === 'excel') {
        $result = exporter_rapport_excel($type_rapport, $rapport);
        echo $result; // Temporaire, à remplacer par téléchargement réel
    } else {
        redirect_with_error('Format d\'export non supporté', '/rapports');
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Récupère l'évolution des élèves sur 6 mois
 */
function get_evolution_eleves_6_mois(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT
                DATE_FORMAT(date_inscription, '%Y-%m') as mois,
                COUNT(*) as nombre_inscriptions
            FROM eleves
            WHERE date_inscription >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY mois
            ORDER BY mois
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Récupère la répartition des élèves par genre
 */
function get_repartition_eleves_genre(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT genre, COUNT(*) as nombre
            FROM eleves
            WHERE statut = 'actif'
            GROUP BY genre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Récupère les paiements mensuels sur 6 mois
 */
function get_paiements_mensuels_6_mois(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT
                DATE_FORMAT(date_creation, '%Y-%m') as mois,
                SUM(montant_paye) as total_paye
            FROM paiements
            WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY mois
            ORDER BY mois
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Récupère l'activité des utilisateurs sur 7 jours
 */
function get_activite_utilisateurs_7_jours(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT
                DATE_FORMAT(date_action, '%Y-%m-%d') as jour,
                COUNT(*) as nombre_actions
            FROM logs_actions
            WHERE date_action >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY jour
            ORDER BY jour
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Compte les élèves sans classe assignée
 */
function compter_eleves_sans_classe(): int
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM eleves WHERE classe_id IS NULL AND statut = 'actif'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Compte les professeurs sans matière assignée
 */
function compter_professeurs_sans_matiere(): int
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT p.professeur_id) as total
            FROM professeurs p
            LEFT JOIN matieres m ON p.professeur_id = m.professeur_id
            WHERE p.statut = 'actif' AND m.matiere_id IS NULL
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Récupère les classes pour les listes déroulantes
 */
function get_classes_for_select(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT
                c.classe_id,
                CONCAT(c.nom_classe, ' - ', n.nom_niveau, ' ', s.nom_section) as nom_complet
            FROM classes c
            JOIN niveaux n ON c.niveau_id = n.niveau_id
            JOIN sections s ON c.section_id = s.section_id
            ORDER BY n.nom_niveau, s.nom_section, c.nom_classe
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Récupère les années de naissance des élèves
 */
function get_annees_naissance_eleves(): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->query("
            SELECT DISTINCT YEAR(date_naissance) as annee
            FROM eleves
            WHERE date_naissance IS NOT NULL
            ORDER BY annee DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Rend une vue du module rapports
 */
function render_rapports_view(string $view_name, array $data = []): void
{
    // Définition du chemin de base pour les vues rapports
    $view_path = VIEWS_PATH . '/rapports/' . $view_name . '.php';

    // Vérification de l'existence de la vue
    if (!file_exists($view_path)) {
        error_log("Vue rapports non trouvée: $view_path");
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
 * Récupère les types de rapports disponibles
 */
function get_types_rapports(): array
{
    return [
        'general' => 'Rapport Général',
        'eleves' => 'Rapport des Élèves',
        'academique' => 'Rapport Académique',
        'financier' => 'Rapport Financier',
        'securite' => 'Rapport de Sécurité'
    ];
}

/**
 * Récupère les formats d'export disponibles
 */
function get_formats_export(): array
{
    return [
        'html' => 'Affichage Web',
        'pdf' => 'PDF',
        'excel' => 'Excel'
    ];
}

/**
 * Récupère les périodes disponibles pour les rapports
 */
function get_periodes_rapport(): array
{
    return [
        'general' => 'Données générales',
        'month' => 'Ce mois',
        'quarter' => 'Ce trimestre',
        'year' => 'Cette année'
    ];
}
?>