<?php
/**
 * Modèle pour la gestion financière
 * Gère les données des paiements, frais scolaires, quittances et sessions de caisse
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

require_once __DIR__ . '/../Services/database.php';

// =============================================
// CONSTANTES DE BASE DE DONNÉES
// =============================================

/**
 * Tables utilisées par le module finance
 */
define('TABLE_PAIEMENTS', 'paiements');
define('TABLE_QUITTANCES', 'quittances');
define('TABLE_CAISSE_SESSIONS', 'caisse_sessions');
define('TABLE_PAIEMENT_JOURNAL', 'paiement_journal');

// =============================================
// FONCTIONS DE GESTION DES PAIEMENTS
// =============================================

/**
 * Récupère les paiements avec pagination et filtres
 */
function get_paiements_pagines(int $page = 1, int $par_page = 20, array $filtres = []): array
{
    $pdo = get_db_connection();
    $offset = ($page - 1) * $par_page;

    try {
        $where_conditions = [];
        $params = [];

        // Filtres
        if (!empty($filtres['statut'])) {
            $where_conditions[] = "p.statut = ?";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['type_frais'])) {
            $where_conditions[] = "p.type_frais = ?";
            $params[] = $filtres['type_frais'];
        }

        if (!empty($filtres['annee_id'])) {
            $where_conditions[] = "p.annee_id = ?";
            $params[] = $filtres['annee_id'];
        }

        if (!empty($filtres['eleve_id'])) {
            $where_conditions[] = "p.eleve_id = ?";
            $params[] = $filtres['eleve_id'];
        }

        if (!empty($filtres['date_debut']) && !empty($filtres['date_fin'])) {
            $where_conditions[] = "p.date_creation BETWEEN ? AND ?";
            $params[] = $filtres['date_debut'] . ' 00:00:00';
            $params[] = $filtres['date_fin'] . ' 23:59:59';
        }

        if (!empty($filtres['recherche'])) {
            $where_conditions[] = "(p.reference LIKE ? OR p.libelle LIKE ? OR e.matricule LIKE ? OR CONCAT(e.nom, ' ', e.prenom) LIKE ?)";
            $search_term = '%' . $filtres['recherche'] . '%';
            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        // Requête principale
        $stmt = $pdo->prepare("
            SELECT
                p.paiement_id,
                p.reference,
                p.libelle,
                p.type_frais,
                p.montant_total,
                p.montant_paye,
                p.reste_a_payer,
                p.date_echeance,
                p.date_paiement,
                p.mode_paiement,
                p.statut,
                p.date_creation,
                e.eleve_id,
                e.matricule,
                e.nom,
                e.post_nom,
                e.prenom,
                CONCAT(e.nom, ' ', e.post_nom, ' ', e.prenom) as nom_complet,
                a.annee_libelle,
                u.nom as caissier_nom,
                u.prenom as caissier_prenom
            FROM " . TABLE_PAIEMENTS . " p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            JOIN annees_scolaire a ON p.annee_id = a.annee_id
            LEFT JOIN user_admins u ON p.caissier_id = u.user_id
            {$where_clause}
            ORDER BY p.date_creation DESC
            LIMIT ? OFFSET ?
        ");

        $params[] = $par_page;
        $params[] = $offset;

        $stmt->execute($params);
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compter le total pour la pagination
        $stmt_count = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM " . TABLE_PAIEMENTS . " p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            {$where_clause}
        ");

        array_pop($params); // Retirer LIMIT
        array_pop($params); // Retirer OFFSET
        $stmt_count->execute($params);
        $total = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'paiements' => $paiements,
            'total' => $total,
            'pages' => ceil($total / $par_page),
            'page_actuelle' => $page
        ];

    } catch (PDOException $e) {
        logError('Erreur récupération paiements paginés', ['error' => $e->getMessage(), 'filtres' => $filtres]);
        return ['paiements' => [], 'total' => 0, 'pages' => 0, 'page_actuelle' => $page];
    }
}

/**
 * Récupère un paiement par son ID
 */
function get_paiement_by_id(int $paiement_id): ?array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT
                p.*,
                e.matricule,
                e.nom,
                e.post_nom,
                e.prenom,
                CONCAT(e.nom, ' ', e.post_nom, ' ', e.prenom) as nom_complet,
                a.annee_libelle,
                u.nom as caissier_nom,
                u.prenom as caissier_prenom
            FROM " . TABLE_PAIEMENTS . " p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            JOIN annees_scolaire a ON p.annee_id = a.annee_id
            LEFT JOIN user_admins u ON p.caissier_id = u.user_id
            WHERE p.paiement_id = ?
        ");

        $stmt->execute([$paiement_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    } catch (PDOException $e) {
        logError('Erreur récupération paiement par ID', ['error' => $e->getMessage(), 'paiement_id' => $paiement_id]);
        return null;
    }
}

/**
 * Crée un nouveau paiement
 */
function create_paiement(array $data): bool|int
{
    $pdo = get_db_connection();

    try {
        $pdo->beginTransaction();

        // Validation des données
        if (!validate_paiement_data($data)) {
            return false;
        }

        // Générer une référence unique
        $reference = generate_payment_reference();

        // Insertion du paiement
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_PAIEMENTS . " (
                reference, eleve_id, annee_id, type_frais, libelle, montant_total,
                montant_paye, date_echeance, mode_paiement, statut, caissier_id, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $reference,
            $data['eleve_id'],
            $data['annee_id'],
            $data['type_frais'],
            $data['libelle'],
            $data['montant_total'],
            $data['montant_paye'] ?? 0,
            $data['date_echeance'] ?? null,
            $data['mode_paiement'] ?? 'Espece',
            $data['statut'] ?? 'impaye',
            $data['caissier_id'] ?? null,
            $data['notes'] ?? null
        ]);

        $paiement_id = $pdo->lastInsertId();

        // Si paiement partiel ou complet, créer une entrée dans le journal
        if (($data['montant_paye'] ?? 0) > 0) {
            create_journal_entry($paiement_id, $data['montant_paye'], $data['mode_paiement'] ?? 'Espece', $data['caissier_id'] ?? null);
        }

        $pdo->commit();
        logAction('Création paiement', "Paiement {$reference} créé", ['paiement_id' => $paiement_id]);
        return $paiement_id;

    } catch (PDOException $e) {
        $pdo->rollBack();
        logError('Erreur création paiement', ['error' => $e->getMessage(), 'data' => $data]);
        return false;
    }
}

/**
 * Met à jour un paiement
 */
function update_paiement(int $paiement_id, array $data): bool
{
    $pdo = get_db_connection();

    try {
        $pdo->beginTransaction();

        // Validation des données
        if (!validate_paiement_data($data, false)) {
            return false;
        }

        // Mise à jour du paiement
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_PAIEMENTS . " SET
                type_frais = ?, libelle = ?, montant_total = ?, montant_paye = ?,
                date_echeance = ?, mode_paiement = ?, statut = ?, caissier_id = ?,
                notes = ?, date_modif = CURRENT_TIMESTAMP
            WHERE paiement_id = ?
        ");

        $stmt->execute([
            $data['type_frais'],
            $data['libelle'],
            $data['montant_total'],
            $data['montant_paye'] ?? 0,
            $data['date_echeance'] ?? null,
            $data['mode_paiement'] ?? 'Espece',
            $data['statut'] ?? 'impaye',
            $data['caissier_id'] ?? null,
            $data['notes'] ?? null,
            $paiement_id
        ]);

        // Créer une entrée dans le journal si nouveau paiement
        if (isset($data['montant_paye']) && $data['montant_paye'] > 0) {
            create_journal_entry($paiement_id, $data['montant_paye'], $data['mode_paiement'] ?? 'Espece', $data['caissier_id'] ?? null);
        }

        $pdo->commit();
        logAction('Modification paiement', "Paiement ID {$paiement_id} modifié", ['paiement_id' => $paiement_id]);
        return true;

    } catch (PDOException $e) {
        $pdo->rollBack();
        logError('Erreur modification paiement', ['error' => $e->getMessage(), 'paiement_id' => $paiement_id, 'data' => $data]);
        return false;
    }
}

/**
 * Supprime un paiement
 */
function delete_paiement(int $paiement_id): bool
{
    $pdo = get_db_connection();

    try {
        // Vérifier si le paiement a des quittances
        $stmt_check = $pdo->prepare("SELECT COUNT(*) as count FROM " . TABLE_QUITTANCES . " WHERE paiement_id = ?");
        $stmt_check->execute([$paiement_id]);
        $count = $stmt_check->fetch(PDO::FETCH_ASSOC)['count'];

        if ($count > 0) {
            logError('Impossible de supprimer paiement avec quittances', ['paiement_id' => $paiement_id]);
            return false;
        }

        $stmt = $pdo->prepare("DELETE FROM " . TABLE_PAIEMENTS . " WHERE paiement_id = ?");
        $stmt->execute([$paiement_id]);

        logAction('Suppression paiement', "Paiement ID {$paiement_id} supprimé", ['paiement_id' => $paiement_id]);
        return true;

    } catch (PDOException $e) {
        logError('Erreur suppression paiement', ['error' => $e->getMessage(), 'paiement_id' => $paiement_id]);
        return false;
    }
}

// =============================================
// FONCTIONS DE GESTION DES QUITTANCES
// =============================================

/**
 * Crée une quittance pour un paiement
 */
function create_quittance(int $paiement_id, array $data): bool|int
{
    $pdo = get_db_connection();

    try {
        $pdo->beginTransaction();

        // Récupérer les informations du paiement
        $paiement = get_paiement_by_id($paiement_id);
        if (!$paiement) {
            return false;
        }

        // Générer le numéro de quittance
        $numero_quittance = generate_receipt_number();

        // Insertion de la quittance
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_QUITTANCES . " (
                numero_quittance, paiement_id, eleve_id, montant, date_quittance,
                mode_paiement, reference_banque, caissier_id, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $numero_quittance,
            $paiement_id,
            $paiement['eleve_id'],
            $data['montant'],
            $data['date_quittance'] ?? date('Y-m-d H:i:s'),
            $data['mode_paiement'] ?? $paiement['mode_paiement'],
            $data['reference_banque'] ?? null,
            $data['caissier_id'] ?? null,
            'valide'
        ]);

        $quittance_id = $pdo->lastInsertId();

        $pdo->commit();
        logAction('Création quittance', "Quittance {$numero_quittance} créée", ['quittance_id' => $quittance_id]);
        return $quittance_id;

    } catch (PDOException $e) {
        $pdo->rollBack();
        logError('Erreur création quittance', ['error' => $e->getMessage(), 'paiement_id' => $paiement_id, 'data' => $data]);
        return false;
    }
}

/**
 * Récupère les quittances d'un paiement
 */
function get_quittances_by_paiement(int $paiement_id): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT q.*, u.nom as caissier_nom, u.prenom as caissier_prenom
            FROM " . TABLE_QUITTANCES . " q
            LEFT JOIN user_admins u ON q.caissier_id = u.user_id
            WHERE q.paiement_id = ?
            ORDER BY q.date_quittance DESC
        ");

        $stmt->execute([$paiement_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération quittances', ['error' => $e->getMessage(), 'paiement_id' => $paiement_id]);
        return [];
    }
}

// =============================================
// FONCTIONS DE GESTION DES SESSIONS DE CAISSE
// =============================================

/**
 * Ouvre une session de caisse
 */
function ouvrir_session_caisse(int $caissier_id, float $montant_ouverture): bool|int
{
    $pdo = get_db_connection();

    try {
        // Vérifier qu'il n'y a pas de session ouverte pour ce caissier
        $stmt_check = $pdo->prepare("
            SELECT session_id FROM " . TABLE_CAISSE_SESSIONS . "
            WHERE caissier_id = ? AND statut = 'ouverte'
        ");
        $stmt_check->execute([$caissier_id]);

        if ($stmt_check->fetch()) {
            logError('Session de caisse déjà ouverte pour ce caissier', ['caissier_id' => $caissier_id]);
            return false;
        }

        // Créer la session
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_CAISSE_SESSIONS . " (
                caissier_id, montant_ouverture, date_ouverture, statut
            ) VALUES (?, ?, NOW(), 'ouverte')
        ");

        $stmt->execute([$caissier_id, $montant_ouverture]);
        $session_id = $pdo->lastInsertId();

        logAction('Ouverture session caisse', "Session {$session_id} ouverte", ['session_id' => $session_id]);
        return $session_id;

    } catch (PDOException $e) {
        logError('Erreur ouverture session caisse', ['error' => $e->getMessage(), 'caissier_id' => $caissier_id]);
        return false;
    }
}

/**
 * Ferme une session de caisse
 */
function fermer_session_caisse(int $session_id, float $montant_fermeture, string $observations = null): bool
{
    $pdo = get_db_connection();

    try {
        // Calculer le montant théorique
        $montant_theorique = calculer_montant_theorique_session($session_id);

        // Calculer l'écart
        $ecart = $montant_fermeture - $montant_theorique;

        // Mettre à jour la session
        $stmt = $pdo->prepare("
            UPDATE " . TABLE_CAISSE_SESSIONS . " SET
                montant_fermeture = ?, montant_theorique = ?, ecart = ?,
                observations = ?, date_fermeture = NOW(), statut = 'fermee'
            WHERE session_id = ? AND statut = 'ouverte'
        ");

        $stmt->execute([$montant_fermeture, $montant_theorique, $ecart, $observations, $session_id]);

        logAction('Fermeture session caisse', "Session {$session_id} fermée", ['session_id' => $session_id, 'ecart' => $ecart]);
        return true;

    } catch (PDOException $e) {
        logError('Erreur fermeture session caisse', ['error' => $e->getMessage(), 'session_id' => $session_id]);
        return false;
    }
}

/**
 * Récupère la session de caisse active d'un caissier
 */
function get_session_caisse_active(int $caissier_id): ?array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM " . TABLE_CAISSE_SESSIONS . "
            WHERE caissier_id = ? AND statut = 'ouverte'
            ORDER BY date_ouverture DESC LIMIT 1
        ");

        $stmt->execute([$caissier_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    } catch (PDOException $e) {
        logError('Erreur récupération session caisse active', ['error' => $e->getMessage(), 'caissier_id' => $caissier_id]);
        return null;
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Génère une référence de paiement unique
 */
function generate_payment_reference(): string
{
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    return "PAY-{$date}-{$random}";
}

/**
 * Génère un numéro de quittance unique
 */
function generate_receipt_number(): string
{
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid()), 0, 4));
    return "REC-{$date}-{$random}";
}

/**
 * Valide les données d'un paiement
 */
function validate_paiement_data(array $data, bool $is_creation = true): bool
{
    // Validation de base
    if (empty($data['eleve_id']) || empty($data['annee_id']) || empty($data['type_frais']) ||
        empty($data['libelle']) || !isset($data['montant_total'])) {
        return false;
    }

    if ($data['montant_total'] <= 0) {
        return false;
    }

    if (isset($data['montant_paye']) && $data['montant_paye'] < 0) {
        return false;
    }

    if (!empty($data['date_echeance']) && !strtotime($data['date_echeance'])) {
        return false;
    }

    return true;
}

/**
 * Crée une entrée dans le journal des paiements
 */
function create_journal_entry(int $paiement_id, float $montant, string $mode_paiement, ?int $caissier_id): bool
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO " . TABLE_PAIEMENT_JOURNAL . " (
                paiement_id, montant, mode_paiement, reference_banque, caissier_id, date_operation
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([$paiement_id, $montant, $mode_paiement, null, $caissier_id]);
        return true;

    } catch (PDOException $e) {
        logError('Erreur création entrée journal', ['error' => $e->getMessage(), 'paiement_id' => $paiement_id]);
        return false;
    }
}

/**
 * Calcule le montant théorique d'une session de caisse
 */
function calculer_montant_theorique_session(int $session_id): float
{
    $pdo = get_db_connection();

    try {
        // Récupérer le montant d'ouverture
        $stmt = $pdo->prepare("SELECT montant_ouverture FROM " . TABLE_CAISSE_SESSIONS . " WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $montant_ouverture = $stmt->fetch(PDO::FETCH_ASSOC)['montant_ouverture'] ?? 0;

        // Calculer les entrées (paiements reçus pendant la session)
        $stmt_entrees = $pdo->prepare("
            SELECT COALESCE(SUM(pj.montant), 0) as total_entrees
            FROM " . TABLE_PAIEMENT_JOURNAL . " pj
            JOIN " . TABLE_PAIEMENTS . " p ON pj.paiement_id = p.paiement_id
            WHERE p.caissier_id = (SELECT caissier_id FROM " . TABLE_CAISSE_SESSIONS . " WHERE session_id = ?)
            AND pj.date_operation >= (SELECT date_ouverture FROM " . TABLE_CAISSE_SESSIONS . " WHERE session_id = ?)
            AND pj.date_operation <= NOW()
        ");
        $stmt_entrees->execute([$session_id, $session_id]);
        $total_entrees = $stmt_entrees->fetch(PDO::FETCH_ASSOC)['total_entrees'];

        return $montant_ouverture + $total_entrees;

    } catch (PDOException $e) {
        logError('Erreur calcul montant théorique', ['error' => $e->getMessage(), 'session_id' => $session_id]);
        return 0;
    }
}

/**
 * Récupère les statistiques financières
 */
function get_statistiques_financieres(array $filtres = []): array
{
    $pdo = get_db_connection();

    try {
        $where_clause = "";
        $params = [];

        if (!empty($filtres['annee_id'])) {
            $where_clause = "WHERE annee_id = ?";
            $params[] = $filtres['annee_id'];
        }

        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total_paiements,
                SUM(montant_total) as total_a_payer,
                SUM(montant_paye) as total_paye,
                SUM(reste_a_payer) as total_impaye,
                AVG(montant_total) as moyenne_paiement,
                COUNT(CASE WHEN statut = 'paye' THEN 1 END) as paiements_complets,
                COUNT(CASE WHEN statut = 'partiel' THEN 1 END) as paiements_partiels,
                COUNT(CASE WHEN statut = 'impaye' THEN 1 END) as paiements_impayes
            FROM " . TABLE_PAIEMENTS . "
            {$where_clause}
        ");

        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération statistiques financières', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Récupère les paiements en retard
 */
function get_paiements_en_retard(int $jours_retard = 30): array
{
    $pdo = get_db_connection();

    try {
        $stmt = $pdo->prepare("
            SELECT
                p.*,
                e.matricule,
                e.nom,
                e.post_nom,
                e.prenom,
                CONCAT(e.nom, ' ', e.post_nom, ' ', e.prenom) as nom_complet,
                DATEDIFF(CURDATE(), p.date_echeance) as jours_retard
            FROM " . TABLE_PAIEMENTS . " p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            WHERE p.statut IN ('impaye', 'partiel')
            AND p.date_echeance < CURDATE()
            AND DATEDIFF(CURDATE(), p.date_echeance) >= ?
            ORDER BY p.date_echeance ASC
        ");

        $stmt->execute([$jours_retard]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logError('Erreur récupération paiements en retard', ['error' => $e->getMessage(), 'jours_retard' => $jours_retard]);
        return [];
    }
}
?>