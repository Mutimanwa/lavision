<?php
/**
 * Module financier - Gestion des paiements, frais scolaires et finances
 * Sécurité: Transactions, reçus, rapports financiers
 * Version: 1.0.0
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../eleves/eleves.php';

// =============================================
// CONSTANTES FINANCIÈRES
// =============================================

// Types de frais
define('FRAIS_INSCRIPTION', 'Inscription');
define('FRAIS_SCOLARITE', 'Scolarite');
define('FRAIS_ACTIVITE', 'Activite');
define('FRAIS_TRANSPORT', 'Transport');
define('FRAIS_UNIFORME', 'Uniforme');
define('FRAIS_AUTRE', 'Autre');

// Modes de paiement
define('PAIEMENT_ESPECE', 'Espece');
define('PAIEMENT_VIREMENT', 'Virement');
define('PAIEMENT_CHEQUE', 'Cheque');
define('PAIEMENT_MOBILE', 'Mobile');
define('PAIEMENT_CARTE', 'Carte');

// Statuts de paiement
define('STATUT_IMPAYE', 'impaye');
define('STATUT_PARTIEL', 'partiel');
define('STATUT_PAYE', 'paye');
define('STATUT_ANNULE', 'annule');

// Périodes de scolarité
define('PERIODE_TRIMESTRE1', 'trimestre1');
define('PERIODE_TRIMESTRE2', 'trimestre2');
define('PERIODE_TRIMESTRE3', 'trimestre3');
define('PERIODE_ANNEE', 'annee');

// Devise
define('DEVISE', 'CDF');
define('SYMBOLE_DEVISE', 'FC');

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valider les données d'un paiement
 */
function finance_valider_paiement(array $donnees, bool $nouveau = true): array
{
    $erreurs = [];
    
    // Champs requis pour la création
    if ($nouveau) {
        $champs_requis = ['eleve_id', 'type_frais', 'libelle', 'montant_total', 'annee_id'];
        
        foreach ($champs_requis as $champ) {
            if (empty($donnees[$champ])) {
                $erreurs[$champ] = "Ce champ est requis";
            }
        }
    }
    
    // Validation de l'élève
    if (!empty($donnees['eleve_id'])) {
        $eleve = eleve_get_by_id((int)$donnees['eleve_id']);
        if (!$eleve) {
            $erreurs['eleve_id'] = "Élève non trouvé";
        } elseif ($eleve['statut_etudiant'] !== ELEVE_ACTIF) {
            $erreurs['eleve_id'] = "L'élève n'est pas actif";
        }
    }
    
    // Validation de l'année scolaire
    if (!empty($donnees['annee_id'])) {
        $annee = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE annee_id = :annee_id",
            ['annee_id' => $donnees['annee_id']]
        );
        if (!$annee) {
            $erreurs['annee_id'] = "Année scolaire non trouvée";
        }
    }
    
    // Validation du type de frais
    if (!empty($donnees['type_frais'])) {
        $types_valides = [FRAIS_INSCRIPTION, FRAIS_SCOLARITE, FRAIS_ACTIVITE, 
                         FRAIS_TRANSPORT, FRAIS_UNIFORME, FRAIS_AUTRE];
        if (!in_array($donnees['type_frais'], $types_valides)) {
            $erreurs['type_frais'] = "Type de frais invalide";
        }
    }
    
    // Validation du libellé
    if (isset($donnees['libelle'])) {
        $libelle = trim($donnees['libelle']);
        if (strlen($libelle) < 3) {
            $erreurs['libelle'] = "Le libellé doit faire au moins 3 caractères";
        } elseif (strlen($libelle) > 255) {
            $erreurs['libelle'] = "Le libellé ne peut pas dépasser 255 caractères";
        }
    }
    
    // Validation des montants
    if (isset($donnees['montant_total'])) {
        $montant = floatval($donnees['montant_total']);
        if ($montant <= 0) {
            $erreurs['montant_total'] = "Le montant total doit être supérieur à 0";
        } elseif ($montant > 10000000) { // 10 millions
            $erreurs['montant_total'] = "Le montant total est trop élevé";
        }
    }
    
    if (isset($donnees['montant_paye'])) {
        $montant_paye = floatval($donnees['montant_paye']);
        if ($montant_paye < 0) {
            $erreurs['montant_paye'] = "Le montant payé ne peut pas être négatif";
        }
        
        // Vérifier la cohérence avec le montant total
        if (isset($donnees['montant_total'])) {
            $montant_total = floatval($donnees['montant_total']);
            if ($montant_paye > $montant_total) {
                $erreurs['montant_paye'] = "Le montant payé ne peut pas dépasser le montant total";
            }
        }
    }
    
    // Validation des dates
    if (!empty($donnees['date_echeance'])) {
        $date = DateTime::createFromFormat('Y-m-d', $donnees['date_echeance']);
        if (!$date || $date->format('Y-m-d') !== $donnees['date_echeance']) {
            $erreurs['date_echeance'] = "Format de date invalide (YYYY-MM-DD)";
        }
    }
    
    if (!empty($donnees['date_paiement'])) {
        $date = DateTime::createFromFormat('Y-m-d', $donnees['date_paiement']);
        if (!$date || $date->format('Y-m-d') !== $donnees['date_paiement']) {
            $erreurs['date_paiement'] = "Format de date invalide (YYYY-MM-DD)";
        }
    }
    
    // Validation du mode de paiement
    if (!empty($donnees['mode_paiement'])) {
        $modes_valides = [PAIEMENT_ESPECE, PAIEMENT_VIREMENT, PAIEMENT_CHEQUE, 
                         PAIEMENT_MOBILE, PAIEMENT_CARTE];
        if (!in_array($donnees['mode_paiement'], $modes_valides)) {
            $erreurs['mode_paiement'] = "Mode de paiement invalide";
        }
    }
    
    // Validation du statut
    if (!empty($donnees['statut'])) {
        $statuts_valides = [STATUT_IMPAYE, STATUT_PARTIEL, STATUT_PAYE, STATUT_ANNULE];
        if (!in_array($donnees['statut'], $statuts_valides)) {
            $erreurs['statut'] = "Statut invalide";
        }
    }
    
    // Validation de la période (pour frais de scolarité)
    if (!empty($donnees['periode'])) {
        $periodes_valides = [PERIODE_TRIMESTRE1, PERIODE_TRIMESTRE2, 
                           PERIODE_TRIMESTRE3, PERIODE_ANNEE];
        if (!in_array($donnees['periode'], $periodes_valides)) {
            $erreurs['periode'] = "Période invalide";
        }
    }
    
    // Validation de la référence bancaire
    if (isset($donnees['reference_banque']) && !empty($donnees['reference_banque'])) {
        if (strlen($donnees['reference_banque']) > 100) {
            $erreurs['reference_banque'] = "La référence bancaire est trop longue";
        }
    }
    
    // Validation des notes
    if (isset($donnees['notes']) && strlen($donnees['notes']) > 1000) {
        $erreurs['notes'] = "Les notes sont trop longues (max 1000 caractères)";
    }
    
    return $erreurs;
}

// =============================================
// FONCTIONS CRUD - PAIEMENTS
// =============================================

/**
 * Créer un nouveau paiement
 */
function finance_creer_paiement(array $donnees): array
{
    // Vérifier les permissions (gestionnaire, admin ou secrétaire)
    if (!has_role(ROLE_GESTIONNAIRE) && !has_role(ROLE_ADMIN) && !has_role(ROLE_SECRETAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Valider les données
    $erreurs = finance_valider_paiement($donnees, true);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier que l'élève est inscrit pour cette année
    $inscription = db_query_single(
        "SELECT 1 FROM admissions 
         WHERE eleve_id = :eleve_id 
         AND annee_id = :annee_id 
         AND statut_admission = 'approuve'",
        ['eleve_id' => $donnees['eleve_id'], 'annee_id' => $donnees['annee_id']]
    );
    
    if (!$inscription) {
        return [
            'success' => false,
            'error' => "L'élève n'est pas inscrit pour cette année scolaire"
        ];
    }
    
    // Générer une référence unique
    $reference = finance_generer_reference($donnees['type_frais']);
    
    try {
        // Préparer les données pour l'insertion
        $champs = [
            'reference' => $reference,
            'eleve_id' => (int)$donnees['eleve_id'],
            'annee_id' => (int)$donnees['annee_id'],
            'type_frais' => $donnees['type_frais'],
            'libelle' => trim($donnees['libelle']),
            'montant_total' => floatval($donnees['montant_total']),
            'montant_paye' => isset($donnees['montant_paye']) ? floatval($donnees['montant_paye']) : 0,
            'date_echeance' => $donnees['date_echeance'] ?? null,
            'date_paiement' => $donnees['date_paiement'] ?? null,
            'mode_paiement' => $donnees['mode_paiement'] ?? PAIEMENT_ESPECE,
            'statut' => $donnees['statut'] ?? STATUT_IMPAYE,
            'banque' => isset($donnees['banque']) ? trim($donnees['banque']) : null,
            'reference_banque' => isset($donnees['reference_banque']) ? trim($donnees['reference_banque']) : null,
            'caissier_id' => $_SESSION['user_id'] ?? null,
            'notes' => isset($donnees['notes']) ? trim($donnees['notes']) : null,
            'periode' => $donnees['periode'] ?? null
        ];
        
        // Calculer le statut automatiquement
        if ($champs['montant_paye'] >= $champs['montant_total']) {
            $champs['statut'] = STATUT_PAYE;
            if (empty($champs['date_paiement'])) {
                $champs['date_paiement'] = date('Y-m-d');
            }
        } elseif ($champs['montant_paye'] > 0) {
            $champs['statut'] = STATUT_PARTIEL;
        }
        
        // Construction de la requête SQL
        $colonnes = implode(', ', array_keys($champs));
        $placeholders = ':' . implode(', :', array_keys($champs));
        
        $sql = "INSERT INTO paiements ({$colonnes}) VALUES ({$placeholders})";
        
        // Exécuter l'insertion
        $success = db_execute($sql, $champs);
        
        if (!$success) {
            throw new Exception("Échec de l'insertion dans la base de données");
        }
        
        $paiement_id = db_last_insert_id();
        
        // Journaliser l'action
        $eleve = eleve_get_by_id($champs['eleve_id']);
        
        log_action('Paiement créé', [
            'paiement_id' => $paiement_id,
            'reference' => $champs['reference'],
            'eleve_id' => $champs['eleve_id'],
            'eleve_nom' => $eleve ? $eleve['nom_complet'] : 'Inconnu',
            'type_frais' => $champs['type_frais'],
            'montant_total' => $champs['montant_total'],
            'montant_paye' => $champs['montant_paye'],
            'statut' => $champs['statut'],
            'caissier_id' => $champs['caissier_id'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'finance');
        
        // Générer un reçu si paiement effectué
        if ($champs['montant_paye'] > 0) {
            finance_generer_quittance($paiement_id);
        }
        
        return [
            'success' => true,
            'paiement_id' => $paiement_id,
            'reference' => $champs['reference'],
            'message' => 'Paiement créé avec succès'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur finance_creer_paiement: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'Erreur lors de la création: ' . $e->getMessage()
        ];
    }
}

/**
 * Générer une référence unique pour un paiement
 */
function finance_generer_reference(string $type_frais): string
{
    $prefixes = [
        FRAIS_INSCRIPTION => 'INS',
        FRAIS_SCOLARITE => 'SCO',
        FRAIS_ACTIVITE => 'ACT',
        FRAIS_TRANSPORT => 'TRP',
        FRAIS_UNIFORME => 'UNI',
        FRAIS_AUTRE => 'AUT'
    ];
    
    $prefix = $prefixes[$type_frais] ?? 'PAY';
    $date = date('ymd');
    $random = strtoupper(bin2hex(random_bytes(3))); // 6 caractères aléatoires
    
    // Vérifier l'unicité
    $reference = $prefix . $date . $random;
    $tentatives = 0;
    
    while ($tentatives < 5) {
        $existe = db_query_single(
            "SELECT paiement_id FROM paiements WHERE reference = :reference",
            ['reference' => $reference]
        );
        
        if (!$existe) {
            return $reference;
        }
        
        $random = strtoupper(bin2hex(random_bytes(3)));
        $reference = $prefix . $date . $random;
        $tentatives++;
    }
    
    // En cas de conflit persistant, ajouter un timestamp
    return $prefix . $date . substr((string)time(), -6);
}

/**
 * Mettre à jour un paiement
 */
function finance_modifier_paiement(int $paiement_id, array $donnees): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_GESTIONNAIRE) && !has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le paiement existe
    $paiement_existant = finance_get_paiement($paiement_id);
    if (!$paiement_existant) {
        return ['success' => false, 'error' => 'Paiement non trouvé'];
    }
    
    // Empêcher la modification des paiements annulés
    if ($paiement_existant['statut'] === STATUT_ANNULE) {
        return [
            'success' => false,
            'error' => 'Impossible de modifier un paiement annulé'
        ];
    }
    
    // Valider les données
    $erreurs = finance_valider_paiement($donnees, false);
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Ne pas permettre la modification des champs clés
    $champs_proteges = ['reference', 'eleve_id', 'annee_id', 'type_frais'];
    foreach ($champs_proteges as $champ) {
        if (isset($donnees[$champ]) && $donnees[$champ] != $paiement_existant[$champ]) {
            return [
                'success' => false,
                'error' => "Vous ne pouvez pas modifier le champ '$champ'"
            ];
        }
    }
    
    try {
        // Préparer les mises à jour
        $updates = [];
        $params = ['paiement_id' => $paiement_id];
        
        // Champs modifiables
        $champs_modifiables = [
            'libelle', 'montant_total', 'montant_paye', 'date_echeance',
            'date_paiement', 'mode_paiement', 'statut', 'banque',
            'reference_banque', 'notes', 'periode'
        ];
        
        foreach ($champs_modifiables as $champ) {
            if (isset($donnees[$champ])) {
                $updates[] = "{$champ} = :{$champ}";
                
                if (in_array($champ, ['montant_total', 'montant_paye'])) {
                    $params[$champ] = floatval($donnees[$champ]);
                } elseif (in_array($champ, ['date_echeance', 'date_paiement']) && empty($donnees[$champ])) {
                    $params[$champ] = null;
                } else {
                    $params[$champ] = trim($donnees[$champ]);
                }
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }
        
        // Construction de la requête
        $sql = "UPDATE paiements SET " . implode(', ', $updates) . " WHERE paiement_id = :paiement_id";
        
        // Exécuter la mise à jour
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            $changements = array_intersect_key($donnees, array_flip($champs_modifiables));
            
            log_action('Paiement modifié', [
                'paiement_id' => $paiement_id,
                'reference' => $paiement_existant['reference'],
                'eleve_id' => $paiement_existant['eleve_id'],
                'changements' => $changements,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'finance');
            
            // Recalculer le statut si nécessaire
            finance_recalculer_statut_paiement($paiement_id);
            
            // Générer un reçu si paiement complet
            if (isset($donnees['montant_paye']) && 
                floatval($donnees['montant_paye']) >= $paiement_existant['montant_total']) {
                finance_generer_quittance($paiement_id);
            }
            
            return [
                'success' => true,
                'message' => 'Paiement mis à jour avec succès',
                'paiement_id' => $paiement_id
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        
    } catch (Exception $e) {
        error_log("Erreur finance_modifier_paiement: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Annuler un paiement
 */
function finance_annuler_paiement(int $paiement_id, string $motif = ''): array
{
    // Vérifier les permissions (admin ou gestionnaire uniquement)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_GESTIONNAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le paiement existe
    $paiement = finance_get_paiement($paiement_id);
    if (!$paiement) {
        return ['success' => false, 'error' => 'Paiement non trouvé'];
    }
    
    // Vérifier que le paiement n'est pas déjà annulé
    if ($paiement['statut'] === STATUT_ANNULE) {
        return [
            'success' => false,
            'error' => 'Ce paiement est déjà annulé'
        ];
    }
    
    // Empêcher l'annulation des paiements déjà payés (sauf admin)
    if ($paiement['statut'] === STATUT_PAYE && !has_role(ROLE_ADMIN)) {
        return [
            'success' => false,
            'error' => 'Impossible d\'annuler un paiement complet sans autorisation admin'
        ];
    }
    
    try {
        // Annuler le paiement
        $sql = "UPDATE paiements 
                SET statut = :statut,
                    notes = CONCAT(COALESCE(notes, ''), ' | Annulé le ', NOW(), ' - Motif: ', :motif)
                WHERE paiement_id = :paiement_id";
        
        $success = db_execute($sql, [
            'paiement_id' => $paiement_id,
            'statut' => STATUT_ANNULE,
            'motif' => substr($motif, 0, 500)
        ]);
        
        if ($success) {
            // Journaliser l'action
            log_action('Paiement annulé', [
                'paiement_id' => $paiement_id,
                'reference' => $paiement['reference'],
                'eleve_id' => $paiement['eleve_id'],
                'ancien_statut' => $paiement['statut'],
                'motif' => $motif,
                'par_utilisateur' => $_SESSION['user_id'] ?? null
            ], 'finance');
            
            // Si c'était un paiement complet, créer un avoir
            if ($paiement['statut'] === STATUT_PAYE && $paiement['montant_paye'] > 0) {
                finance_creer_avoir($paiement_id, $paiement['montant_paye'], $motif);
            }
            
            return [
                'success' => true,
                'message' => 'Paiement annulé avec succès'
            ];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de l\'annulation'];
        
    } catch (Exception $e) {
        error_log("Erreur finance_annuler_paiement: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur interne'];
    }
}

/**
 * Créer un avoir suite à l'annulation d'un paiement
 */
function finance_creer_avoir(int $paiement_id, float $montant, string $motif = ''): array
{
    try {
        // Récupérer les informations du paiement
        $paiement = finance_get_paiement($paiement_id);
        
        if (!$paiement) {
            return ['success' => false, 'error' => 'Paiement non trouvé'];
        }
        
        // Créer l'avoir (paiement négatif)
        $donnees_avoir = [
            'eleve_id' => $paiement['eleve_id'],
            'annee_id' => $paiement['annee_id'],
            'type_frais' => FRAIS_AUTRE,
            'libelle' => "Avoir - Annulation paiement {$paiement['reference']}",
            'montant_total' => -$montant,
            'montant_paye' => -$montant,
            'date_paiement' => date('Y-m-d'),
            'mode_paiement' => $paiement['mode_paiement'],
            'statut' => STATUT_PAYE,
            'notes' => "Créé suite à l'annulation du paiement {$paiement['reference']}. Motif: {$motif}",
            'paiement_origine_id' => $paiement_id
        ];
        
        return finance_creer_paiement($donnees_avoir);
        
    } catch (Exception $e) {
        error_log("Erreur finance_creer_avoir: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la création de l\'avoir'];
    }
}

/**
 * Enregistrer un paiement partiel ou complet
 */
function finance_enregistrer_paiement(int $paiement_id, array $donnees_paiement): array
{
    // Vérifier les permissions (gestionnaire, admin ou secrétaire)
    if (!has_role(ROLE_GESTIONNAIRE) && !has_role(ROLE_ADMIN) && !has_role(ROLE_SECRETAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que le paiement existe
    $paiement = finance_get_paiement($paiement_id);
    if (!$paiement) {
        return ['success' => false, 'error' => 'Paiement non trouvé'];
    }
    
    // Vérifier que le paiement n'est pas annulé
    if ($paiement['statut'] === STATUT_ANNULE) {
        return [
            'success' => false,
            'error' => 'Impossible d\'enregistrer un paiement sur une facture annulée'
        ];
    }
    
    // Vérifier que le paiement n'est pas déjà complet
    if ($paiement['statut'] === STATUT_PAYE) {
        return [
            'success' => false,
            'error' => 'Ce paiement est déjà complet'
        ];
    }
    
    // Valider les données du paiement
    $erreurs = [];
    
    if (empty($donnees_paiement['montant_paye'])) {
        $erreurs['montant_paye'] = "Le montant payé est requis";
    } else {
        $montant_paye = floatval($donnees_paiement['montant_paye']);
        $reste_a_payer = $paiement['montant_total'] - $paiement['montant_paye'];
        
        if ($montant_paye <= 0) {
            $erreurs['montant_paye'] = "Le montant payé doit être supérieur à 0";
        } elseif ($montant_paye > $reste_a_payer) {
            $erreurs['montant_paye'] = "Le montant payé ne peut pas dépasser le reste à payer ({$reste_a_payer} " . SYMBOLE_DEVISE . ")";
        }
    }
    
    if (empty($donnees_paiement['mode_paiement'])) {
        $erreurs['mode_paiement'] = "Le mode de paiement est requis";
    }
    
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    try {
        // Démarrer la transaction
        db_begin_transaction();
        
        // Calculer le nouveau montant payé
        $nouveau_montant_paye = $paiement['montant_paye'] + floatval($donnees_paiement['montant_paye']);
        $nouveau_statut = STATUT_PARTIEL;
        
        if ($nouveau_montant_paye >= $paiement['montant_total']) {
            $nouveau_statut = STATUT_PAYE;
        }
        
        // Mettre à jour le paiement
        $sql_update = "UPDATE paiements 
                      SET montant_paye = :montant_paye,
                          statut = :statut,
                          date_paiement = :date_paiement,
                          mode_paiement = :mode_paiement,
                          banque = :banque,
                          reference_banque = :reference_banque,
                          notes = CONCAT(COALESCE(notes, ''), ' | Paiement enregistré le ', NOW(), ': ', :notes_paiement)
                      WHERE paiement_id = :paiement_id";
        
        $params_update = [
            'paiement_id' => $paiement_id,
            'montant_paye' => $nouveau_montant_paye,
            'statut' => $nouveau_statut,
            'date_paiement' => $donnees_paiement['date_paiement'] ?? date('Y-m-d'),
            'mode_paiement' => $donnees_paiement['mode_paiement'],
            'banque' => $donnees_paiement['banque'] ?? null,
            'reference_banque' => $donnees_paiement['reference_banque'] ?? null,
            'notes_paiement' => substr($donnees_paiement['notes'] ?? '', 0, 200)
        ];
        
        $success = db_execute($sql_update, $params_update);
        
        if (!$success) {
            throw new Exception("Échec de la mise à jour du paiement");
        }
        
        // Créer une ligne dans le journal des paiements
        $sql_journal = "INSERT INTO paiement_journal 
                       (paiement_id, montant, mode_paiement, reference_banque, caissier_id, notes)
                       VALUES (:paiement_id, :montant, :mode_paiement, :reference_banque, :caissier_id, :notes)";
        
        $params_journal = [
            'paiement_id' => $paiement_id,
            'montant' => floatval($donnees_paiement['montant_paye']),
            'mode_paiement' => $donnees_paiement['mode_paiement'],
            'reference_banque' => $donnees_paiement['reference_banque'] ?? null,
            'caissier_id' => $_SESSION['user_id'] ?? null,
            'notes' => $donnees_paiement['notes'] ?? null
        ];
        
        $success_journal = db_execute($sql_journal, $params_journal);
        
        if (!$success_journal) {
            throw new Exception("Échec de l'enregistrement dans le journal");
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        log_action('Paiement enregistré', [
            'paiement_id' => $paiement_id,
            'reference' => $paiement['reference'],
            'eleve_id' => $paiement['eleve_id'],
            'montant_paye' => floatval($donnees_paiement['montant_paye']),
            'nouveau_total_paye' => $nouveau_montant_paye,
            'nouveau_statut' => $nouveau_statut,
            'mode_paiement' => $donnees_paiement['mode_paiement'],
            'par_utilisateur' => $_SESSION['user_id'] ?? null
        ], 'finance');
        
        // Générer un reçu
        $quittance = finance_generer_quittance($paiement_id, floatval($donnees_paiement['montant_paye']));
        
        return [
            'success' => true,
            'message' => 'Paiement enregistré avec succès',
            'paiement_id' => $paiement_id,
            'nouveau_statut' => $nouveau_statut,
            'nouveau_montant_paye' => $nouveau_montant_paye,
            'reste_a_payer' => $paiement['montant_total'] - $nouveau_montant_paye,
            'quittance' => $quittance
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur finance_enregistrer_paiement: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement'];
    }
}

/**
 * Recalculer le statut d'un paiement
 */
function finance_recalculer_statut_paiement(int $paiement_id): void
{
    $paiement = finance_get_paiement($paiement_id);
    
    if (!$paiement) {
        return;
    }
    
    $nouveau_statut = $paiement['statut'];
    
    if ($paiement['montant_paye'] >= $paiement['montant_total']) {
        $nouveau_statut = STATUT_PAYE;
    } elseif ($paiement['montant_paye'] > 0) {
        $nouveau_statut = STATUT_PARTIEL;
    } else {
        $nouveau_statut = STATUT_IMPAYE;
    }
    
    // Mettre à jour si le statut a changé
    if ($nouveau_statut !== $paiement['statut']) {
        db_execute(
            "UPDATE paiements SET statut = :statut WHERE paiement_id = :paiement_id",
            ['paiement_id' => $paiement_id, 'statut' => $nouveau_statut]
        );
        
        log_action('Statut paiement recalculé', [
            'paiement_id' => $paiement_id,
            'reference' => $paiement['reference'],
            'ancien_statut' => $paiement['statut'],
            'nouveau_statut' => $nouveau_statut
        ], 'finance');
    }
}

// =============================================
// FONCTIONS DE RECHERCHE ET CONSULTATION
// =============================================

/**
 * Obtenir un paiement par son ID
 */
function finance_get_paiement(int $paiement_id): ?array
{
    $sql = "SELECT p.*,
                   e.matricule, e.nom as eleve_nom, e.prenom as eleve_prenom, e.post_nom as eleve_post_nom,
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   s.nom_section,
                   an.annee_libelle,
                   u.nom as caissier_nom, u.prenom as caissier_prenom
            FROM paiements p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            LEFT JOIN classes c ON adm.class_id = c.class_id
            LEFT JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            JOIN annees_scolaire an ON p.annee_id = an.annee_id
            LEFT JOIN user_admins u ON p.caissier_id = u.user_id
            WHERE p.paiement_id = :paiement_id";
    
    $paiement = db_query_single($sql, ['paiement_id' => $paiement_id]);
    
    if (!$paiement) {
        return null;
    }
    
    // Formater les données
    $paiement['nom_complet_eleve'] = $paiement['eleve_prenom'] . ' ' . $paiement['eleve_nom'] . ' ' . $paiement['eleve_post_nom'];
    $paiement['nom_complet_caissier'] = $paiement['caissier_prenom'] . ' ' . $paiement['caissier_nom'];
    
    // Calculer le reste à payer si nécessaire
    if (!isset($paiement['reste_a_payer'])) {
        $paiement['reste_a_payer'] = $paiement['montant_total'] - $paiement['montant_paye'];
    }
    
    return $paiement;
}

/**
 * Rechercher des paiements selon des critères
 */
function finance_rechercher_paiements(array $filtres = [], int $page = 1, int $par_page = 20): array
{
    // Construire la requête
    $sql = "SELECT p.*,
                   e.matricule, e.nom as eleve_nom, e.prenom as eleve_prenom, e.post_nom as eleve_post_nom,
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   s.nom_section,
                   an.annee_libelle,
                   u.nom as caissier_nom, u.prenom as caissier_prenom
            FROM paiements p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            LEFT JOIN classes c ON adm.class_id = c.class_id
            LEFT JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            JOIN annees_scolaire an ON p.annee_id = an.annee_id
            LEFT JOIN user_admins u ON p.caissier_id = u.user_id
            WHERE 1 = 1";
    
    $params = [];
    $conditions = [];
    
    // Appliquer les filtres
    if (!empty($filtres['eleve_id'])) {
        $conditions[] = "p.eleve_id = :eleve_id";
        $params['eleve_id'] = (int)$filtres['eleve_id'];
    }
    
    if (!empty($filtres['matricule'])) {
        $conditions[] = "e.matricule LIKE :matricule";
        $params['matricule'] = '%' . $filtres['matricule'] . '%';
    }
    
    if (!empty($filtres['nom_eleve'])) {
        $conditions[] = "(e.nom LIKE :nom_eleve OR e.prenom LIKE :nom_eleve OR e.post_nom LIKE :nom_eleve)";
        $params['nom_eleve'] = '%' . $filtres['nom_eleve'] . '%';
    }
    
    if (!empty($filtres['type_frais'])) {
        $conditions[] = "p.type_frais = :type_frais";
        $params['type_frais'] = $filtres['type_frais'];
    }
    
    if (!empty($filtres['statut'])) {
        $conditions[] = "p.statut = :statut";
        $params['statut'] = $filtres['statut'];
    }
    
    if (!empty($filtres['annee_id'])) {
        $conditions[] = "p.annee_id = :annee_id";
        $params['annee_id'] = (int)$filtres['annee_id'];
    }
    
    if (!empty($filtres['mode_paiement'])) {
        $conditions[] = "p.mode_paiement = :mode_paiement";
        $params['mode_paiement'] = $filtres['mode_paiement'];
    }
    
    if (!empty($filtres['date_debut'])) {
        $conditions[] = "p.date_creation >= :date_debut";
        $params['date_debut'] = $filtres['date_debut'];
    }
    
    if (!empty($filtres['date_fin'])) {
        $conditions[] = "p.date_creation <= :date_fin";
        $params['date_fin'] = $filtres['date_fin'] . ' 23:59:59';
    }
    
    if (!empty($filtres['reference'])) {
        $conditions[] = "p.reference LIKE :reference";
        $params['reference'] = '%' . $filtres['reference'] . '%';
    }
    
    if (!empty($filtres['classe_id'])) {
        $conditions[] = "c.class_id = :classe_id";
        $params['classe_id'] = (int)$filtres['classe_id'];
    }
    
    if (!empty($filtres['niveau_id'])) {
        $conditions[] = "n.niveau_id = :niveau_id";
        $params['niveau_id'] = (int)$filtres['niveau_id'];
    }
    
    if (!empty($filtres['caissier_id'])) {
        $conditions[] = "p.caissier_id = :caissier_id";
        $params['caissier_id'] = (int)$filtres['caissier_id'];
    }
    
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    // Compter le total pour la pagination
    $sql_count = "SELECT COUNT(*) as total FROM paiements p
                  JOIN eleves e ON p.eleve_id = e.eleve_id
                  WHERE 1 = 1";
    
    if (!empty($conditions)) {
        $sql_count .= " AND " . implode(" AND ", $conditions);
    }
    
    $count_result = db_query_single($sql_count, $params);
    $total = $count_result['total'] ?? 0;
    $total_pages = ceil($total / $par_page);
    
    // Ajouter l'ordre et la limite
    $sql .= " ORDER BY p.date_creation DESC, p.paiement_id DESC";
    
    if ($par_page > 0) {
        $offset = ($page - 1) * $par_page;
        $sql .= " LIMIT :offset, :limit";
        $params['offset'] = $offset;
        $params['limit'] = $par_page;
    }
    
    // Exécuter la requête
    $paiements = db_query($sql, $params);
    
    // Formater les données
    foreach ($paiements as &$paiement) {
        $paiement['nom_complet_eleve'] = $paiement['eleve_prenom'] . ' ' . $paiement['eleve_nom'] . ' ' . $paiement['eleve_post_nom'];
        $paiement['nom_complet_caissier'] = $paiement['caissier_prenom'] . ' ' . $paiement['caissier_nom'];
        $paiement['reste_a_payer'] = $paiement['montant_total'] - $paiement['montant_paye'];
    }
    
    return [
        'paiements' => $paiements,
        'pagination' => [
            'page' => $page,
            'par_page' => $par_page,
            'total' => $total,
            'total_pages' => $total_pages
        ]
    ];
}

/**
 * Obtenir les paiements d'un élève
 */
function finance_get_paiements_eleve(int $eleve_id, int $annee_id = null): array
{
    $sql = "SELECT p.*, an.annee_libelle
            FROM paiements p
            JOIN annees_scolaire an ON p.annee_id = an.annee_id
            WHERE p.eleve_id = :eleve_id";
    
    $params = ['eleve_id' => $eleve_id];
    
    if ($annee_id) {
        $sql .= " AND p.annee_id = :annee_id";
        $params['annee_id'] = $annee_id;
    }
    
    $sql .= " ORDER BY p.date_creation DESC";
    
    $paiements = db_query($sql, $params);
    
    // Calculer les totaux
    $total_a_payer = 0;
    $total_paye = 0;
    $total_impaye = 0;
    
    foreach ($paiements as &$paiement) {
        $paiement['reste_a_payer'] = $paiement['montant_total'] - $paiement['montant_paye'];
        
        $total_a_payer += $paiement['montant_total'];
        $total_paye += $paiement['montant_paye'];
        $total_impaye += $paiement['reste_a_payer'];
    }
    
    return [
        'paiements' => $paiements,
        'total' => [
            'a_payer' => $total_a_payer,
            'paye' => $total_paye,
            'impaye' => $total_impaye
        ]
    ];
}

/**
 * Obtenir les frais impayés d'un élève
 */
function finance_get_frais_impayes(int $eleve_id, int $annee_id = null): array
{
    $sql = "SELECT p.*, an.annee_libelle,
                   DATEDIFF(CURDATE(), p.date_echeance) as jours_retard,
                   CASE 
                     WHEN p.date_echeance IS NULL OR p.date_echeance >= CURDATE() THEN 0
                     ELSE p.reste_a_payer * (SELECT valeur FROM parametres WHERE cle = 'taux_penalite') / 100 
                          * DATEDIFF(CURDATE(), p.date_echeance) / 30
                   END as penalite_calculee
            FROM paiements p
            JOIN annees_scolaire an ON p.annee_id = an.annee_id
            WHERE p.eleve_id = :eleve_id 
            AND p.statut IN (:statut_impaye, :statut_partiel)
            AND p.date_echeance IS NOT NULL";
    
    $params = [
        'eleve_id' => $eleve_id,
        'statut_impaye' => STATUT_IMPAYE,
        'statut_partiel' => STATUT_PARTIEL
    ];
    
    if ($annee_id) {
        $sql .= " AND p.annee_id = :annee_id";
        $params['annee_id'] = $annee_id;
    }
    
    $sql .= " ORDER BY p.date_echeance ASC";
    
    $frais_impayes = db_query($sql, $params);
    
    $total_impaye = 0;
    $total_penalite = 0;
    
    foreach ($frais_impayes as &$frais) {
        $frais['reste_a_payer'] = $frais['montant_total'] - $frais['montant_paye'];
        $frais['penalite'] = $frais['penalite_calculee'] ?? 0;
        
        $total_impaye += $frais['reste_a_payer'];
        $total_penalite += $frais['penalite'];
    }
    
    return [
        'frais_impayes' => $frais_impayes,
        'total' => [
            'impaye' => $total_impaye,
            'penalite' => $total_penalite,
            'total_a_payer' => $total_impaye + $total_penalite
        ]
    ];
}

// =============================================
// FONCTIONS DE GÉNÉRATION DE REÇUS/QUITTANCES
// =============================================

/**
 * Générer une quittance/reçu de paiement
 */
function finance_generer_quittance(int $paiement_id, float $montant_paye = null): array
{
    $paiement = finance_get_paiement($paiement_id);
    
    if (!$paiement) {
        return ['success' => false, 'error' => 'Paiement non trouvé'];
    }
    
    // Si aucun montant spécifié, utiliser le montant total du paiement
    if ($montant_paye === null) {
        $montant_paye = $paiement['montant_paye'];
    }
    
    // Vérifier que le montant est valide
    if ($montant_paye <= 0) {
        return ['success' => false, 'error' => 'Montant invalide pour la quittance'];
    }
    
    try {
        // Générer un numéro de quittance unique
        $numero_quittance = 'QUITT-' . date('Ymd') . '-' . str_pad($paiement_id, 6, '0', STR_PAD_LEFT);
        
        // Vérifier l'unicité
        $existe = db_query_single(
            "SELECT quittance_id FROM quittances WHERE numero_quittance = :numero",
            ['numero' => $numero_quittance]
        );
        
        if ($existe) {
            $numero_quittance .= '-' . substr((string)time(), -4);
        }
        
        // Créer la quittance dans la base
        $sql = "INSERT INTO quittances 
                (numero_quittance, paiement_id, eleve_id, montant, date_quittance, 
                 mode_paiement, reference_banque, caissier_id, statut)
                VALUES (:numero, :paiement_id, :eleve_id, :montant, NOW(),
                       :mode_paiement, :reference_banque, :caissier_id, 'valide')";
        
        $params = [
            'numero' => $numero_quittance,
            'paiement_id' => $paiement_id,
            'eleve_id' => $paiement['eleve_id'],
            'montant' => $montant_paye,
            'mode_paiement' => $paiement['mode_paiement'],
            'reference_banque' => $paiement['reference_banque'],
            'caissier_id' => $paiement['caissier_id']
        ];
        
        $success = db_execute($sql, $params);
        
        if (!$success) {
            throw new Exception("Échec de la création de la quittance");
        }
        
        $quittance_id = db_last_insert_id();
        
        // Journaliser
        log_action('Quittance générée', [
            'quittance_id' => $quittance_id,
            'numero' => $numero_quittance,
            'paiement_id' => $paiement_id,
            'montant' => $montant_paye,
            'eleve_id' => $paiement['eleve_id']
        ], 'finance');
        
        return [
            'success' => true,
            'quittance_id' => $quittance_id,
            'numero_quittance' => $numero_quittance,
            'paiement_id' => $paiement_id,
            'montant' => $montant_paye
        ];
        
    } catch (Exception $e) {
        error_log("Erreur finance_generer_quittance: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la génération'];
    }
}

/**
 * Obtenir le HTML d'une quittance pour impression
 */
function finance_generer_html_quittance(int $quittance_id): string
{
    // Récupérer les informations de la quittance
    $sql = "SELECT q.*, p.reference as reference_paiement,
                   e.matricule, e.nom as eleve_nom, e.prenom as eleve_prenom, e.post_nom as eleve_post_nom,
                   c.libelle as classe_libelle,
                   n.nom_niveau,
                   s.nom_section,
                   an.annee_libelle,
                   u.nom as caissier_nom, u.prenom as caissier_prenom
            FROM quittances q
            JOIN paiements p ON q.paiement_id = p.paiement_id
            JOIN eleves e ON q.eleve_id = e.eleve_id
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            LEFT JOIN classes c ON adm.class_id = c.class_id
            LEFT JOIN niveau n ON c.niveau_id = n.niveau_id
            LEFT JOIN sections s ON c.section_id = s.section_id
            JOIN annees_scolaire an ON p.annee_id = an.annee_id
            JOIN user_admins u ON q.caissier_id = u.user_id
            WHERE q.quittance_id = :quittance_id";
    
    $quittance = db_query_single($sql, ['quittance_id' => $quittance_id]);
    
    if (!$quittance) {
        return '<div class="error">Quittance non trouvée</div>';
    }
    
    // Générer le HTML de la quittance
    $html = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quittance de paiement - ' . ECOLE_NOM . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
            .quittance-container { max-width: 800px; margin: 0 auto; border: 2px solid #000; padding: 30px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 20px; }
            .header h1 { margin: 0; font-size: 24px; color: #2c3e50; }
            .header .ecole-info { font-size: 14px; color: #666; margin-top: 10px; }
            .header .titre-quittance { font-size: 20px; font-weight: bold; margin-top: 20px; color: #e74c3c; }
            .content { display: flex; flex-wrap: wrap; justify-content: space-between; }
            .section { flex: 0 0 48%; margin-bottom: 25px; }
            .section-title { font-weight: bold; margin-bottom: 10px; color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
            .info-row { display: flex; margin-bottom: 8px; }
            .info-label { flex: 0 0 40%; font-weight: bold; }
            .info-value { flex: 1; }
            .montant { font-size: 18px; font-weight: bold; text-align: center; padding: 15px; background-color: #f8f9fa; border: 2px dashed #3498db; margin: 20px 0; }
            .signatures { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; border-top: 2px solid #000; }
            .signature-box { flex: 0 0 45%; text-align: center; }
            .signature-line { width: 80%; height: 1px; background-color: #000; margin: 40px auto 10px; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            @media print {
                body { padding: 0; }
                .quittance-container { border: none; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="quittance-container">
            <div class="header">
                <h1>' . ECOLE_NOM . '</h1>
                <div class="ecole-info">
                    ' . ECOLE_ADRESSE . ' | Tél: ' . ECOLE_TELEPHONE . ' | Email: ' . ECOLE_EMAIL . '
                </div>
                <div class="titre-quittance">QUITTANCE DE PAIEMENT</div>
            </div>
            
            <div class="content">
                <div class="section">
                    <div class="section-title">INFORMATIONS DE LA QUITTANCE</div>
                    <div class="info-row">
                        <div class="info-label">N° Quittance :</div>
                        <div class="info-value">' . e($quittance['numero_quittance']) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Date :</div>
                        <div class="info-value">' . date('d/m/Y à H:i', strtotime($quittance['date_quittance'])) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">N° Paiement :</div>
                        <div class="info-value">' . e($quittance['reference_paiement']) . '</div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">INFORMATIONS DE L\'ÉLÈVE</div>
                    <div class="info-row">
                        <div class="info-label">Matricule :</div>
                        <div class="info-value">' . e($quittance['matricule']) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nom complet :</div>
                        <div class="info-value">' . e($quittance['eleve_prenom'] . ' ' . $quittance['eleve_nom'] . ' ' . $quittance['eleve_post_nom']) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Classe :</div>
                        <div class="info-value">' . e($quittance['classe_libelle']) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Année scolaire :</div>
                        <div class="info-value">' . e($quittance['annee_libelle']) . '</div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">DÉTAILS DU PAIEMENT</div>
                    <div class="info-row">
                        <div class="info-label">Mode de paiement :</div>
                        <div class="info-value">' . e($quittance['mode_paiement']) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Référence bancaire :</div>
                        <div class="info-value">' . e($quittance['reference_banque'] ?? 'N/A') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Caissier :</div>
                        <div class="info-value">' . e($quittance['caissier_prenom'] . ' ' . $quittance['caissier_nom']) . '</div>
                    </div>
                </div>
            </div>
            
            <div class="montant">
                MONTANT REÇU : <span style="color: #27ae60; font-size: 24px;">' . number_format($quittance['montant'], 0, ',', ' ') . ' ' . SYMBOLE_DEVISE . '</span>
                <div style="font-size: 14px; margin-top: 5px; color: #7f8c8d;">(' . finance_montant_en_lettres($quittance['montant']) . ')</div>
            </div>
            
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Signature du Caissier</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Signature du Responsable</div>
                </div>
            </div>
            
            <div class="footer">
                <p>Cette quittance annule et remplace toute quittance antérieure du même numéro.</p>
                <p>Conservez ce document comme preuve de paiement.</p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()">Imprimer la quittance</button>
                <button onclick="window.close()">Fermer</button>
            </div>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    </body>
    </html>';
    
    return $html;
}

/**
 * Convertir un montant en lettres
 */
function finance_montant_en_lettres(float $montant): string
{
    $entier = intval($montant);
    $decimal = intval(round(($montant - $entier) * 100));
    
    $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
    $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
    $centaines = ['', 'cent', 'deux cent', 'trois cent', 'quatre cent', 'cinq cent', 'six cent', 'sept cent', 'huit cent', 'neuf cent'];
    
    // Pour simplifier, on retourne une version basique
    if ($entier == 0) {
        return 'zéro ' . SYMBOLE_DEVISE;
    }
    
    $texte = '';
    
    if ($entier >= 1000000) {
        $millions = intval($entier / 1000000);
        $texte .= finance_convertir_milliers($millions) . ' million' . ($millions > 1 ? 's' : '') . ' ';
        $entier %= 1000000;
    }
    
    if ($entier >= 1000) {
        $mille = intval($entier / 1000);
        if ($mille > 1) {
            $texte .= finance_convertir_centaines($mille) . ' ';
        }
        $texte .= 'mille ';
        $entier %= 1000;
    }
    
    if ($entier > 0) {
        $texte .= finance_convertir_centaines($entier) . ' ';
    }
    
    $texte .= SYMBOLE_DEVISE;
    
    if ($decimal > 0) {
        $texte .= ' et ' . finance_convertir_centaines($decimal) . ' centimes';
    }
    
    return ucfirst(trim($texte));
}

function finance_convertir_centaines(int $nombre): string
{
    if ($nombre == 0) return '';
    
    $unites = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
    $dizaines = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
    $centaines = ['', 'cent', 'deux cent', 'trois cent', 'quatre cent', 'cinq cent', 'six cent', 'sept cent', 'huit cent', 'neuf cent'];
    
    $texte = '';
    
    $c = intval($nombre / 100);
    $d = intval(($nombre % 100) / 10);
    $u = $nombre % 10;
    
    if ($c > 0) {
        $texte .= $centaines[$c];
        if ($d > 0 || $u > 0) $texte .= ' ';
    }
    
    if ($d > 0) {
        // Cas spéciaux
        if ($d == 7 || $d == 9) {
            $d--; // 70 = soixante-dix, 90 = quatre-vingt-dix
            $u += 10;
        }
        
        $texte .= $dizaines[$d];
        
        if ($u > 0) {
            if ($d == 1 && $u == 1) {
                $texte .= ' et onze';
            } elseif ($d == 1) {
                $texte .= '-' . $unites[$u];
            } elseif ($d == 8 && $u == 0) {
                $texte .= 's';
            } elseif ($u == 1) {
                $texte .= ' et un';
            } else {
                $texte .= '-' . $unites[$u];
            }
        } elseif ($d == 8) {
            $texte .= 's';
        }
    } elseif ($u > 0) {
        if ($c > 0) $texte .= ' ';
        $texte .= $unites[$u];
    }
    
    return $texte;
}

function finance_convertir_milliers(int $nombre): string
{
    if ($nombre >= 1000) {
        $milliers = intval($nombre / 1000);
        $reste = $nombre % 1000;
        $texte = finance_convertir_centaines($milliers) . ' mille';
        if ($reste > 0) {
            $texte .= ' ' . finance_convertir_centaines($reste);
        }
        return $texte;
    }
    return finance_convertir_centaines($nombre);
}

// =============================================
// FONCTIONS DE RAPPORTS ET STATISTIQUES
// =============================================

/**
 * Générer un rapport financier
 */
function finance_generer_rapport(string $type_rapport, array $parametres = []): array
{
    $resultats = [];
    
    switch ($type_rapport) {
        case 'recettes_journalieres':
            $resultats = finance_rapport_recettes_journalieres($parametres);
            break;
            
        case 'recettes_mensuelles':
            $resultats = finance_rapport_recettes_mensuelles($parametres);
            break;
            
        case 'recettes_annuelles':
            $resultats = finance_rapport_recettes_annuelles($parametres);
            break;
            
        case 'frais_par_type':
            $resultats = finance_rapport_frais_par_type($parametres);
            break;
            
        case 'frais_par_classe':
            $resultats = finance_rapport_frais_par_classe($parametres);
            break;
            
        case 'impayes_par_eleve':
            $resultats = finance_rapport_impayes_par_eleve($parametres);
            break;
            
        case 'caisse_caissier':
            $resultats = finance_rapport_caisse_caissier($parametres);
            break;
            
        default:
            return ['success' => false, 'error' => 'Type de rapport non supporté'];
    }
    
    return [
        'success' => true,
        'rapport' => $resultats,
        'type' => $type_rapport,
        'parametres' => $parametres
    ];
}

/**
 * Rapport des recettes journalières
 */
function finance_rapport_recettes_journalieres(array $parametres): array
{
    $date_debut = $parametres['date_debut'] ?? date('Y-m-01');
    $date_fin = $parametres['date_fin'] ?? date('Y-m-d');
    $caissier_id = $parametres['caissier_id'] ?? null;
    
    $sql = "SELECT 
                DATE(p.date_creation) as jour,
                COUNT(*) as nombre_paiements,
                SUM(p.montant_paye) as total_recettes,
                GROUP_CONCAT(DISTINCT p.type_frais) as types_frais,
                u.prenom as caissier_prenom,
                u.nom as caissier_nom
            FROM paiements p
            LEFT JOIN user_admins u ON p.caissier_id = u.user_id
            WHERE DATE(p.date_creation) BETWEEN :date_debut AND :date_fin
            AND p.statut = :statut_paye";
    
    $params = [
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'statut_paye' => STATUT_PAYE
    ];
    
    if ($caissier_id) {
        $sql .= " AND p.caissier_id = :caissier_id";
        $params['caissier_id'] = $caissier_id;
    }
    
    $sql .= " GROUP BY DATE(p.date_creation), p.caissier_id
              ORDER BY DATE(p.date_creation) DESC";
    
    $jours = db_query($sql, $params);
    
    // Calculer les totaux
    $total_recettes = 0;
    $total_paiements = 0;
    
    foreach ($jours as &$jour) {
        $total_recettes += $jour['total_recettes'];
        $total_paiements += $jour['nombre_paiements'];
        $jour['caissier'] = $jour['caissier_prenom'] . ' ' . $jour['caissier_nom'];
    }
    
    return [
        'jours' => $jours,
        'total' => [
            'recettes' => $total_recettes,
            'paiements' => $total_paiements
        ],
        'periode' => [
            'debut' => $date_debut,
            'fin' => $date_fin
        ]
    ];
}

/**
 * Rapport des recettes mensuelles
 */
function finance_rapport_recettes_mensuelles(array $parametres): array
{
    $annee = $parametres['annee'] ?? date('Y');
    $caissier_id = $parametres['caissier_id'] ?? null;
    
    $sql = "SELECT 
                DATE_FORMAT(p.date_creation, '%Y-%m') as mois,
                COUNT(*) as nombre_paiements,
                SUM(p.montant_paye) as total_recettes,
                SUM(CASE WHEN p.type_frais = :type_inscription THEN p.montant_paye ELSE 0 END) as inscriptions,
                SUM(CASE WHEN p.type_frais = :type_scolarite THEN p.montant_paye ELSE 0 END) as scolarite,
                SUM(CASE WHEN p.type_frais NOT IN (:type_inscription, :type_scolarite) THEN p.montant_paye ELSE 0 END) as autres
            FROM paiements p
            WHERE YEAR(p.date_creation) = :annee
            AND p.statut = :statut_paye";
    
    $params = [
        'annee' => $annee,
        'statut_paye' => STATUT_PAYE,
        'type_inscription' => FRAIS_INSCRIPTION,
        'type_scolarite' => FRAIS_SCOLARITE
    ];
    
    if ($caissier_id) {
        $sql .= " AND p.caissier_id = :caissier_id";
        $params['caissier_id'] = $caissier_id;
    }
    
    $sql .= " GROUP BY DATE_FORMAT(p.date_creation, '%Y-%m')
              ORDER BY mois DESC";
    
    $mois = db_query($sql, $params);
    
    // Noms des mois
    $noms_mois = [
        '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
        '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
        '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
    ];
    
    $total_recettes = 0;
    $total_inscriptions = 0;
    $total_scolarite = 0;
    $total_autres = 0;
    
    foreach ($mois as &$mois_data) {
        $parts = explode('-', $mois_data['mois']);
        $mois_data['mois_nom'] = $noms_mois[$parts[1]] . ' ' . $parts[0];
        
        $total_recettes += $mois_data['total_recettes'];
        $total_inscriptions += $mois_data['inscriptions'];
        $total_scolarite += $mois_data['scolarite'];
        $total_autres += $mois_data['autres'];
    }
    
    return [
        'mois' => $mois,
        'total' => [
            'recettes' => $total_recettes,
            'inscriptions' => $total_inscriptions,
            'scolarite' => $total_scolarite,
            'autres' => $total_autres
        ],
        'annee' => $annee
    ];
}

/**
 * Rapport des frais par type
 */
function finance_rapport_frais_par_type(array $parametres): array
{
    $annee_id = $parametres['annee_id'] ?? null;
    $date_debut = $parametres['date_debut'] ?? null;
    $date_fin = $parametres['date_fin'] ?? null;
    
    $sql = "SELECT 
                p.type_frais,
                COUNT(*) as nombre_frais,
                SUM(p.montant_total) as total_a_payer,
                SUM(p.montant_paye) as total_paye,
                SUM(p.montant_total - p.montant_paye) as total_impaye,
                AVG(p.montant_total) as moyenne_montant
            FROM paiements p
            WHERE 1 = 1";
    
    $params = [];
    
    if ($annee_id) {
        $sql .= " AND p.annee_id = :annee_id";
        $params['annee_id'] = $annee_id;
    }
    
    if ($date_debut) {
        $sql .= " AND p.date_creation >= :date_debut";
        $params['date_debut'] = $date_debut;
    }
    
    if ($date_fin) {
        $sql .= " AND p.date_creation <= :date_fin";
        $params['date_fin'] = $date_fin . ' 23:59:59';
    }
    
    $sql .= " GROUP BY p.type_frais
              ORDER BY total_a_payer DESC";
    
    $types_frais = db_query($sql, $params);
    
    $totaux = [
        'nombre_frais' => 0,
        'total_a_payer' => 0,
        'total_paye' => 0,
        'total_impaye' => 0
    ];
    
    foreach ($types_frais as &$type) {
        $totaux['nombre_frais'] += $type['nombre_frais'];
        $totaux['total_a_payer'] += $type['total_a_payer'];
        $totaux['total_paye'] += $type['total_paye'];
        $totaux['total_impaye'] += $type['total_impaye'];
    }
    
    return [
        'types_frais' => $types_frais,
        'totaux' => $totaux,
        'filtres' => $parametres
    ];
}

/**
 * Rapport des impayés par élève
 */
function finance_rapport_impayes_par_eleve(array $parametres): array
{
    $annee_id = $parametres['annee_id'] ?? null;
    $classe_id = $parametres['classe_id'] ?? null;
    $niveau_id = $parametres['niveau_id'] ?? null;
    
    $sql = "SELECT 
                e.eleve_id,
                e.matricule,
                e.nom,
                e.prenom,
                e.post_nom,
                c.libelle as classe,
                n.nom_niveau,
                COUNT(p.paiement_id) as nombre_frais_impayes,
                SUM(p.montant_total - p.montant_paye) as total_impaye,
                MAX(p.date_echeance) as dernier_echeance,
                DATEDIFF(CURDATE(), MAX(p.date_echeance)) as jours_retard
            FROM eleves e
            JOIN paiements p ON e.eleve_id = p.eleve_id
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            LEFT JOIN classes c ON adm.class_id = c.class_id
            LEFT JOIN niveau n ON c.niveau_id = n.niveau_id
            WHERE p.statut IN (:statut_impaye, :statut_partiel)
            AND p.date_echeance IS NOT NULL";
    
    $params = [
        'statut_impaye' => STATUT_IMPAYE,
        'statut_partiel' => STATUT_PARTIEL
    ];
    
    if ($annee_id) {
        $sql .= " AND p.annee_id = :annee_id";
        $params['annee_id'] = $annee_id;
    }
    
    if ($classe_id) {
        $sql .= " AND c.class_id = :classe_id";
        $params['classe_id'] = $classe_id;
    }
    
    if ($niveau_id) {
        $sql .= " AND n.niveau_id = :niveau_id";
        $params['niveau_id'] = $niveau_id;
    }
    
    $sql .= " GROUP BY e.eleve_id
              HAVING total_impaye > 0
              ORDER BY total_impaye DESC, jours_retard DESC";
    
    $eleves_impayes = db_query($sql, $params);
    
    $total_impaye = 0;
    $nombre_eleves = count($eleves_impayes);
    
    foreach ($eleves_impayes as &$eleve) {
        $total_impaye += $eleve['total_impaye'];
        $eleve['nom_complet'] = $eleve['prenom'] . ' ' . $eleve['nom'] . ' ' . $eleve['post_nom'];
    }
    
    return [
        'eleves_impayes' => $eleves_impayes,
        'total' => [
            'impaye' => $total_impaye,
            'nombre_eleves' => $nombre_eleves,
            'moyenne_par_eleve' => $nombre_eleves > 0 ? $total_impaye / $nombre_eleves : 0
        ],
        'filtres' => $parametres
    ];
}

// =============================================
// FONCTIONS D'AUTOMATISATION
// =============================================

/**
 * Générer automatiquement les frais de scolarité
 */
function finance_generer_frais_scolarite_automatique(int $annee_id, string $periode): array
{
    // Vérifier les permissions (admin uniquement)
    if (!has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier que la période est valide
    $periodes_valides = [PERIODE_TRIMESTRE1, PERIODE_TRIMESTRE2, PERIODE_TRIMESTRE3];
    if (!in_array($periode, $periodes_valides)) {
        return ['success' => false, 'error' => 'Période invalide'];
    }
    
    // Récupérer le montant des frais de scolarité depuis les paramètres
    $frais_scolarite = db_query_single(
        "SELECT valeur FROM parametres WHERE cle = 'frais_scolarite'"
    );
    
    if (!$frais_scolarite || empty($frais_scolarite['valeur'])) {
        return ['success' => false, 'error' => 'Montant des frais de scolarité non configuré'];
    }
    
    $montant = floatval($frais_scolarite['valeur']);
    
    // Récupérer tous les élèves actifs pour l'année donnée
    $sql = "SELECT e.eleve_id, c.libelle as classe
            FROM eleves e
            JOIN admissions adm ON e.eleve_id = adm.eleve_id
            JOIN classes c ON adm.class_id = c.class_id
            WHERE adm.annee_id = :annee_id
            AND adm.statut_admission = 'approuve'
            AND e.statut_etudiant = :statut_actif";
    
    $eleves = db_query($sql, [
        'annee_id' => $annee_id,
        'statut_actif' => ELEVE_ACTIF
    ]);
    
    if (empty($eleves)) {
        return ['success' => false, 'error' => 'Aucun élève actif trouvé pour cette année'];
    }
    
    $frais_crees = 0;
    $erreurs = [];
    
    try {
        // Démarrer une transaction
        db_begin_transaction();
        
        foreach ($eleves as $eleve) {
            // Vérifier si le frais existe déjà
            $frais_existant = db_query_single(
                "SELECT paiement_id FROM paiements 
                 WHERE eleve_id = :eleve_id 
                 AND annee_id = :annee_id 
                 AND type_frais = :type_frais 
                 AND periode = :periode",
                [
                    'eleve_id' => $eleve['eleve_id'],
                    'annee_id' => $annee_id,
                    'type_frais' => FRAIS_SCOLARITE,
                    'periode' => $periode
                ]
            );
            
            if ($frais_existant) {
                $erreurs[] = "Frais déjà existant pour l'élève {$eleve['eleve_id']}";
                continue;
            }
            
            // Déterminer la date d'échéance
            $date_echeance = finance_calculer_date_echeance_scolarite($periode, $annee_id);
            
            // Créer le paiement
            $donnees_paiement = [
                'eleve_id' => $eleve['eleve_id'],
                'annee_id' => $annee_id,
                'type_frais' => FRAIS_SCOLARITE,
                'libelle' => "Frais de scolarité - {$periode} - {$eleve['classe']}",
                'montant_total' => $montant,
                'montant_paye' => 0,
                'date_echeance' => $date_echeance,
                'mode_paiement' => PAIEMENT_ESPECE,
                'statut' => STATUT_IMPAYE,
                'periode' => $periode,
                'notes' => "Généré automatiquement le " . date('d/m/Y')
            ];
            
            $resultat = finance_creer_paiement($donnees_paiement);
            
            if ($resultat['success']) {
                $frais_crees++;
            } else {
                $erreurs[] = "Erreur pour l'élève {$eleve['eleve_id']}: " . ($resultat['error'] ?? 'Inconnue');
            }
        }
        
        // Valider la transaction
        db_commit();
        
        // Journaliser l'action
        log_action('Frais de scolarité générés automatiquement', [
            'annee_id' => $annee_id,
            'periode' => $periode,
            'nombre_eleves' => count($eleves),
            'frais_crees' => $frais_crees,
            'erreurs' => count($erreurs),
            'montant' => $montant
        ], 'finance');
        
        return [
            'success' => true,
            'message' => "{$frais_crees} frais de scolarité générés avec succès",
            'details' => [
                'total_eleves' => count($eleves),
                'frais_crees' => $frais_crees,
                'erreurs' => count($erreurs),
                'liste_erreurs' => $erreurs
            ]
        ];
        
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        db_rollback();
        
        error_log("Erreur finance_generer_frais_scolarite_automatique: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la génération: ' . $e->getMessage()];
    }
}

/**
 * Calculer la date d'échéance pour les frais de scolarité
 */
function finance_calculer_date_echeance_scolarite(string $periode, int $annee_id): string
{
    // Récupérer l'année scolaire
    $annee = db_query_single(
        "SELECT date_debut, date_fin FROM annees_scolaire WHERE annee_id = :annee_id",
        ['annee_id' => $annee_id]
    );
    
    if (!$annee) {
        return date('Y-m-d', strtotime('+30 days'));
    }
    
    $date_debut = new DateTime($annee['date_debut']);
    $date_fin = new DateTime($annee['date_fin']);
    
    // Calculer les dates pour chaque période
    $interval = $date_debut->diff($date_fin);
    $jours_totaux = $interval->days;
    $jours_par_periode = ceil($jours_totaux / 3);
    
    switch ($periode) {
        case PERIODE_TRIMESTRE1:
            $date_echeance = clone $date_debut;
            $date_echeance->modify("+{$jours_par_periode} days");
            break;
            
        case PERIODE_TRIMESTRE2:
            $date_echeance = clone $date_debut;
            $date_echeance->modify("+" . ($jours_par_periode * 2) . " days");
            break;
            
        case PERIODE_TRIMESTRE3:
            $date_echeance = clone $date_fin;
            break;
            
        default:
            $date_echeance = new DateTime();
            $date_echeance->modify('+30 days');
    }
    
    // Récupérer le délai de paiement depuis les paramètres
    $delai_paiement = db_query_single(
        "SELECT valeur FROM parametres WHERE cle = 'delai_paiement'"
    );
    
    if ($delai_paiement && is_numeric($delai_paiement['valeur'])) {
        $jours_delai = intval($delai_paiement['valeur']);
        $date_echeance->modify("-{$jours_delai} days");
    }
    
    return $date_echeance->format('Y-m-d');
}

/**
 * Envoyer des rappels pour les paiements en retard
 */
function finance_envoyer_rappels_retard(): array
{
    // Vérifier les permissions (admin ou gestionnaire)
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_GESTIONNAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Récupérer les paiements en retard
    $sql = "SELECT p.*, e.nom, e.prenom, e.post_nom, e.email, e.telephone,
                   c.libelle as classe, an.annee_libelle
            FROM paiements p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            LEFT JOIN admissions adm ON e.eleve_id = adm.eleve_id AND adm.statut_admission = 'approuve'
            LEFT JOIN classes c ON adm.class_id = c.class_id
            JOIN annees_scolaire an ON p.annee_id = an.annee_id
            WHERE p.statut IN (:statut_impaye, :statut_partiel)
            AND p.date_echeance < CURDATE()
            AND p.date_echeance IS NOT NULL
            AND p.date_dernier_rappel IS NULL 
            OR p.date_dernier_rappel < DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    
    $paiements_retard = db_query($sql, [
        'statut_impaye' => STATUT_IMPAYE,
        'statut_partiel' => STATUT_PARTIEL
    ]);
    
    if (empty($paiements_retard)) {
        return ['success' => true, 'message' => 'Aucun paiement en retard nécessitant un rappel'];
    }
    
    $rappels_envoyes = 0;
    $erreurs = [];
    
    foreach ($paiements_retard as $paiement) {
        try {
            // Calculer les jours de retard
            $jours_retard = floor((time() - strtotime($paiement['date_echeance'])) / (60 * 60 * 24));
            
            // Calculer la pénalité
            $penalite = 0;
            if ($jours_retard > 0) {
                $reste_a_payer = $paiement['montant_total'] - $paiement['montant_paye'];
                $taux_penalite = db_query_single(
                    "SELECT valeur FROM parametres WHERE cle = 'taux_penalite'"
                );
                $taux = $taux_penalite ? floatval($taux_penalite['valeur']) : 5;
                $penalite = $reste_a_payer * ($taux / 100) * ($jours_retard / 30);
            }
            
            // Préparer le message
            $sujet = "Rappel: Paiement en retard - " . ECOLE_NOM;
            
            $message = "Cher(e) parent/tuteur,\n\n";
            $message .= "Nous vous informons que le paiement suivant est en retard:\n\n";
            $message .= "Élève: " . $paiement['prenom'] . " " . $paiement['nom'] . " " . $paiement['post_nom'] . "\n";
            $message .= "Matricule: " . $paiement['reference'] . "\n";
            $message .= "Classe: " . $paiement['classe'] . "\n";
            $message .= "Année scolaire: " . $paiement['annee_libelle'] . "\n";
            $message .= "Type de frais: " . $paiement['type_frais'] . "\n";
            $message .= "Montant total: " . number_format($paiement['montant_total'], 0, ',', ' ') . " " . SYMBOLE_DEVISE . "\n";
            $message .= "Montant payé: " . number_format($paiement['montant_paye'], 0, ',', ' ') . " " . SYMBOLE_DEVISE . "\n";
            $message .= "Reste à payer: " . number_format($paiement['montant_total'] - $paiement['montant_paye'], 0, ',', ' ') . " " . SYMBOLE_DEVISE . "\n";
            $message .= "Date d'échéance: " . date('d/m/Y', strtotime($paiement['date_echeance'])) . "\n";
            $message .= "Jours de retard: " . $jours_retard . "\n";
            
            if ($penalite > 0) {
                $message .= "Pénalité de retard: " . number_format($penalite, 0, ',', ' ') . " " . SYMBOLE_DEVISE . "\n";
                $message .= "Total à payer avec pénalité: " . number_format(($paiement['montant_total'] - $paiement['montant_paye']) + $penalite, 0, ',', ' ') . " " . SYMBOLE_DEVISE . "\n";
            }
            
            $message .= "\nVeuillez régulariser votre situation au plus vite.\n\n";
            $message .= "Cordialement,\n";
            $message .= "Service Financier\n";
            $message .= ECOLE_NOM . "\n";
            $message .= ECOLE_TELEPHONE . "\n";
            
            // Envoyer l'email (simulé)
            // mail($paiement['email'], $sujet, $message);
            
            // Mettre à jour la date du dernier rappel
            db_execute(
                "UPDATE paiements SET date_dernier_rappel = NOW() WHERE paiement_id = :paiement_id",
                ['paiement_id' => $paiement['paiement_id']]
            );
            
            // Journaliser l'envoi
            log_action('Rappel de retard envoyé', [
                'paiement_id' => $paiement['paiement_id'],
                'eleve_id' => $paiement['eleve_id'],
                'jours_retard' => $jours_retard,
                'email' => $paiement['email'],
                'telephone' => $paiement['telephone']
            ], 'finance');
            
            $rappels_envoyes++;
            
        } catch (Exception $e) {
            $erreurs[] = "Erreur pour le paiement {$paiement['paiement_id']}: " . $e->getMessage();
        }
    }
    
    return [
        'success' => true,
        'message' => "{$rappels_envoyes} rappels envoyés avec succès",
        'details' => [
            'total_paiements' => count($paiements_retard),
            'rappels_envoyes' => $rappels_envoyes,
            'erreurs' => count($erreurs),
            'liste_erreurs' => $erreurs
        ]
    ];
}

// =============================================
// FONCTIONS DE CAISSE
// =============================================

/**
 * Ouvrir une session de caisse
 */
function finance_ouvrir_caisse(float $montant_ouverture): array
{
    // Vérifier les permissions (gestionnaire ou caissier)
    if (!has_role(ROLE_GESTIONNAIRE) && !has_role(ROLE_SECRETAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Vérifier si une session est déjà ouverte
    $session_active = db_query_single(
        "SELECT session_id FROM caisse_sessions 
         WHERE caissier_id = :caissier_id 
         AND date_fermeture IS NULL",
        ['caissier_id' => $_SESSION['user_id']]
    );
    
    if ($session_active) {
        return [
            'success' => false,
            'error' => 'Vous avez déjà une session de caisse active'
        ];
    }
    
    try {
        // Créer la session
        $sql = "INSERT INTO caisse_sessions 
                (caissier_id, montant_ouverture, date_ouverture, statut)
                VALUES (:caissier_id, :montant_ouverture, NOW(), 'ouverte')";
        
        $success = db_execute($sql, [
            'caissier_id' => $_SESSION['user_id'],
            'montant_ouverture' => $montant_ouverture
        ]);
        
        if (!$success) {
            throw new Exception("Échec de l'ouverture de la caisse");
        }
        
        $session_id = db_last_insert_id();
        
        // Journaliser
        log_action('Caisse ouverte', [
            'session_id' => $session_id,
            'caissier_id' => $_SESSION['user_id'],
            'montant_ouverture' => $montant_ouverture
        ], 'caisse');
        
        return [
            'success' => true,
            'session_id' => $session_id,
            'message' => 'Caisse ouverte avec succès'
        ];
        
    } catch (Exception $e) {
        error_log("Erreur finance_ouvrir_caisse: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'ouverture'];
    }
}

/**
 * Fermer une session de caisse
 */
function finance_fermer_caisse(float $montant_theorique, float $montant_reel, string $observations = ''): array
{
    // Vérifier les permissions (gestionnaire ou caissier)
    if (!has_role(ROLE_GESTIONNAIRE) && !has_role(ROLE_SECRETAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Récupérer la session active
    $session = db_query_single(
        "SELECT * FROM caisse_sessions 
         WHERE caissier_id = :caissier_id 
         AND date_fermeture IS NULL
         ORDER BY date_ouverture DESC LIMIT 1",
        ['caissier_id' => $_SESSION['user_id']]
    );
    
    if (!$session) {
        return [
            'success' => false,
            'error' => 'Aucune session de caisse active'
        ];
    }
    
    // Calculer l'écart
    $ecart = $montant_reel - $montant_theorique;
    
    try {
        // Fermer la session
        $sql = "UPDATE caisse_sessions 
                SET montant_fermeture = :montant_reel,
                    montant_theorique = :montant_theorique,
                    ecart = :ecart,
                    observations = :observations,
                    date_fermeture = NOW(),
                    statut = 'fermee'
                WHERE session_id = :session_id";
        
        $success = db_execute($sql, [
            'session_id' => $session['session_id'],
            'montant_reel' => $montant_reel,
            'montant_theorique' => $montant_theorique,
            'ecart' => $ecart,
            'observations' => substr($observations, 0, 500)
        ]);
        
        if (!$success) {
            throw new Exception("Échec de la fermeture de la caisse");
        }
        
        // Journaliser
        log_action('Caisse fermée', [
            'session_id' => $session['session_id'],
            'caissier_id' => $_SESSION['user_id'],
            'montant_ouverture' => $session['montant_ouverture'],
            'montant_theorique' => $montant_theorique,
            'montant_reel' => $montant_reel,
            'ecart' => $ecart,
            'observations' => $observations
        ], 'caisse');
        
        return [
            'success' => true,
            'session_id' => $session['session_id'],
            'message' => 'Caisse fermée avec succès',
            'details' => [
                'ecart' => $ecart,
                'statut_ecart' => $ecart == 0 ? 'correct' : ($ecart > 0 ? 'excédent' : 'déficit')
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erreur finance_fermer_caisse: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la fermeture'];
    }
}

/**
 * Obtenir le récapitulatif de caisse
 */
function finance_get_recap_caisse(int $session_id = null): array
{
    // Si aucune session spécifiée, prendre la session active
    if (!$session_id) {
        $session = db_query_single(
            "SELECT session_id FROM caisse_sessions 
             WHERE caissier_id = :caissier_id 
             AND date_fermeture IS NULL
             ORDER BY date_ouverture DESC LIMIT 1",
            ['caissier_id' => $_SESSION['user_id']]
        );
        
        if (!$session) {
            return ['success' => false, 'error' => 'Aucune session de caisse active'];
        }
        
        $session_id = $session['session_id'];
    }
    
    // Récupérer les détails de la session
    $session = db_query_single(
        "SELECT cs.*, 
                u.nom as caissier_nom, u.prenom as caissier_prenom
         FROM caisse_sessions cs
         JOIN user_admins u ON cs.caissier_id = u.user_id
         WHERE cs.session_id = :session_id",
        ['session_id' => $session_id]
    );
    
    if (!$session) {
        return ['success' => false, 'error' => 'Session non trouvée'];
    }
    
    // Récupérer les paiements de la session
    $sql = "SELECT p.*, e.prenom as eleve_prenom, e.nom as eleve_nom, e.post_nom as eleve_post_nom
            FROM paiements p
            JOIN eleves e ON p.eleve_id = e.eleve_id
            WHERE p.caissier_id = :caissier_id
            AND p.date_creation BETWEEN :date_debut AND COALESCE(:date_fin, NOW())
            AND p.statut = :statut_paye
            ORDER BY p.date_creation DESC";
    
    $paiements = db_query($sql, [
        'caissier_id' => $session['caissier_id'],
        'date_debut' => $session['date_ouverture'],
        'date_fin' => $session['date_fermeture'],
        'statut_paye' => STATUT_PAYE
    ]);
    
    // Calculer les totaux
    $total_espece = 0;
    $total_virement = 0;
    $total_cheque = 0;
    $total_mobile = 0;
    $total_carte = 0;
    $total_general = 0;
    
    $types_frais = [];
    
    foreach ($paiements as &$paiement) {
        $paiement['nom_complet_eleve'] = $paiement['eleve_prenom'] . ' ' . $paiement['eleve_nom'] . ' ' . $paiement['eleve_post_nom'];
        
        // Accumuler par mode de paiement
        switch ($paiement['mode_paiement']) {
            case PAIEMENT_ESPECE:
                $total_espece += $paiement['montant_paye'];
                break;
            case PAIEMENT_VIREMENT:
                $total_virement += $paiement['montant_paye'];
                break;
            case PAIEMENT_CHEQUE:
                $total_cheque += $paiement['montant_paye'];
                break;
            case PAIEMENT_MOBILE:
                $total_mobile += $paiement['montant_paye'];
                break;
            case PAIEMENT_CARTE:
                $total_carte += $paiement['montant_paye'];
                break;
        }
        
        $total_general += $paiement['montant_paye'];
        
        // Accumuler par type de frais
        if (!isset($types_frais[$paiement['type_frais']])) {
            $types_frais[$paiement['type_frais']] = 0;
        }
        $types_frais[$paiement['type_frais']] += $paiement['montant_paye'];
    }
    
    // Calculer le montant théorique
    $montant_theorique = $session['montant_ouverture'] + $total_general;
    
    return [
        'success' => true,
        'session' => $session,
        'paiements' => $paiements,
        'total' => [
            'espece' => $total_espece,
            'virement' => $total_virement,
            'cheque' => $total_cheque,
            'mobile' => $total_mobile,
            'carte' => $total_carte,
            'general' => $total_general
        ],
        'types_frais' => $types_frais,
        'montant_theorique' => $montant_theorique,
        'statut' => $session['statut']
    ];
}

// =============================================
// FONCTIONS D'EXPORT ET IMPORT
// =============================================

/**
 * Exporter les données financières au format CSV
 */
function finance_exporter_csv(array $filtres = []): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_GESTIONNAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Rechercher les paiements
    $resultats = finance_rechercher_paiements($filtres, 1, 1000000); // Tous les résultats
    
    if (empty($resultats['paiements'])) {
        return ['success' => false, 'error' => 'Aucune donnée à exporter'];
    }
    
    // Préparer le contenu CSV
    $csv_lines = [];
    
    // En-têtes
    $headers = [
        'Référence',
        'Date création',
        'Élève',
        'Matricule',
        'Classe',
        'Type frais',
        'Libellé',
        'Montant total',
        'Montant payé',
        'Reste à payer',
        'Statut',
        'Mode paiement',
        'Date échéance',
        'Date paiement',
        'Caissier',
        'Année scolaire'
    ];
    
    $csv_lines[] = implode(';', $headers);
    
    // Données
    foreach ($resultats['paiements'] as $paiement) {
        $line = [
            $paiement['reference'],
            date('d/m/Y H:i', strtotime($paiement['date_creation'])),
            $paiement['nom_complet_eleve'],
            $paiement['matricule'],
            $paiement['classe_libelle'],
            $paiement['type_frais'],
            $paiement['libelle'],
            number_format($paiement['montant_total'], 0, ',', ''),
            number_format($paiement['montant_paye'], 0, ',', ''),
            number_format($paiement['reste_a_payer'], 0, ',', ''),
            $paiement['statut'],
            $paiement['mode_paiement'],
            $paiement['date_echeance'] ? date('d/m/Y', strtotime($paiement['date_echeance'])) : '',
            $paiement['date_paiement'] ? date('d/m/Y', strtotime($paiement['date_paiement'])) : '',
            $paiement['nom_complet_caissier'],
            $paiement['annee_libelle']
        ];
        
        $csv_lines[] = implode(';', $line);
    }
    
    $csv_content = implode("\n", $csv_lines);
    
    // Nom du fichier
    $filename = 'export_financier_' . date('Ymd_His') . '.csv';
    
    return [
        'success' => true,
        'filename' => $filename,
        'content' => $csv_content,
        'count' => count($resultats['paiements'])
    ];
}

/**
 * Générer un rapport PDF
 */
function finance_generer_pdf_rapport(string $type_rapport, array $parametres = []): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_ADMIN) && !has_role(ROLE_GESTIONNAIRE)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    // Générer le rapport
    $rapport = finance_generer_rapport($type_rapport, $parametres);
    
    if (!$rapport['success']) {
        return $rapport;
    }
    
    // Ici, on générerait le PDF avec une bibliothèque comme TCPDF ou Dompdf
    // Pour l'instant, on retourne un lien vers une page HTML d'impression
    
    $token = bin2hex(random_bytes(16));
    $_SESSION['rapport_pdf_' . $token] = $rapport;
    
    return [
        'success' => true,
        'pdf_url' => BASE_URL . 'finance/generer_pdf.php?token=' . $token,
        'token' => $token,
        'rapport' => $rapport['rapport']
    ];
}

// =============================================
// FONCTIONS DE CONFIGURATION
// =============================================

/**
 * Configurer les paramètres financiers
 */
function finance_configurer_parametres(array $parametres): array
{
    // Vérifier les permissions (admin uniquement)
    if (!has_role(ROLE_ADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    $erreurs = [];
    $parametres_ajoutes = [];
    
    foreach ($parametres as $cle => $valeur) {
        // Valider selon le type de paramètre
        switch ($cle) {
            case 'frais_scolarite':
                if (!is_numeric($valeur) || $valeur < 0) {
                    $erreurs[$cle] = "Le montant doit être un nombre positif";
                }
                break;
                
            case 'frais_inscription':
                if (!is_numeric($valeur) || $valeur < 0) {
                    $erreurs[$cle] = "Le montant doit être un nombre positif";
                }
                break;
                
            case 'delai_paiement':
                if (!is_numeric($valeur) || $valeur < 1 || $valeur > 90) {
                    $erreurs[$cle] = "Le délai doit être entre 1 et 90 jours";
                }
                break;
                
            case 'taux_penalite':
                if (!is_numeric($valeur) || $valeur < 0 || $valeur > 100) {
                    $erreurs[$cle] = "Le taux doit être entre 0 et 100%";
                }
                break;
        }
    }
    
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    try {
        foreach ($parametres as $cle => $valeur) {
            // Vérifier si le paramètre existe
            $existe = db_query_single(
                "SELECT parametre_id FROM parametres WHERE cle = :cle AND categorie = 'financier'",
                ['cle' => $cle]
            );
            
            if ($existe) {
                // Mettre à jour
                db_execute(
                    "UPDATE parametres SET valeur = :valeur WHERE cle = :cle AND categorie = 'financier'",
                    ['cle' => $cle, 'valeur' => $valeur]
                );
            } else {
                // Créer
                db_execute(
                    "INSERT INTO parametres (cle, valeur, type, categorie, description) 
                     VALUES (:cle, :valeur, 'integer', 'financier', 'Paramètre financier')",
                    ['cle' => $cle, 'valeur' => $valeur]
                );
            }
            
            $parametres_ajoutes[] = $cle;
        }
        
        // Journaliser
        log_action('Paramètres financiers modifiés', [
            'parametres' => $parametres_ajoutes,
            'valeurs' => $parametres
        ], 'config');
        
        return [
            'success' => true,
            'message' => 'Paramètres mis à jour avec succès',
            'parametres' => $parametres_ajoutes
        ];
        
    } catch (Exception $e) {
        error_log("Erreur finance_configurer_parametres: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la configuration'];
    }
}

// =============================================
// INITIALISATION DU MODULE
// =============================================

// Vérifier si la table paiements_journal existe, sinon la créer
function finance_creer_table_journal(): void
{
    $sql = "CREATE TABLE IF NOT EXISTS paiement_journal (
        journal_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        paiement_id BIGINT NOT NULL,
        montant DECIMAL(10,2) NOT NULL,
        mode_paiement ENUM('Espece','Virement','Cheque','Mobile','Carte') NOT NULL,
        reference_banque VARCHAR(100),
        caissier_id BIGINT NOT NULL,
        notes TEXT,
        date_operation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (paiement_id) REFERENCES paiements(paiement_id) ON DELETE CASCADE,
        FOREIGN KEY (caissier_id) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
        INDEX idx_journal_paiement (paiement_id),
        INDEX idx_journal_date (date_operation)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db_execute($sql);
    
    $sql2 = "CREATE TABLE IF NOT EXISTS quittances (
        quittance_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        numero_quittance VARCHAR(50) UNIQUE NOT NULL,
        paiement_id BIGINT NOT NULL,
        eleve_id BIGINT NOT NULL,
        montant DECIMAL(10,2) NOT NULL,
        date_quittance DATETIME NOT NULL,
        mode_paiement ENUM('Espece','Virement','Cheque','Mobile','Carte') NOT NULL,
        reference_banque VARCHAR(100),
        caissier_id BIGINT NOT NULL,
        statut ENUM('valide', 'annule') DEFAULT 'valide',
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (paiement_id) REFERENCES paiements(paiement_id) ON DELETE RESTRICT,
        FOREIGN KEY (eleve_id) REFERENCES eleves(eleve_id) ON DELETE RESTRICT,
        FOREIGN KEY (caissier_id) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
        INDEX idx_quittance_numero (numero_quittance),
        INDEX idx_quittance_date (date_quittance)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db_execute($sql2);
    
    $sql3 = "CREATE TABLE IF NOT EXISTS caisse_sessions (
        session_id BIGINT AUTO_INCREMENT PRIMARY KEY,
        caissier_id BIGINT NOT NULL,
        montant_ouverture DECIMAL(10,2) NOT NULL,
        montant_fermeture DECIMAL(10,2),
        montant_theorique DECIMAL(10,2),
        ecart DECIMAL(10,2),
        observations TEXT,
        date_ouverture DATETIME NOT NULL,
        date_fermeture DATETIME,
        statut ENUM('ouverte', 'fermee') DEFAULT 'ouverte',
        FOREIGN KEY (caissier_id) REFERENCES user_admins(user_id) ON DELETE RESTRICT,
        INDEX idx_session_caissier (caissier_id),
        INDEX idx_session_statut (statut)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    db_execute($sql3);
}

// Créer les tables nécessaires
finance_creer_table_journal();

// Journaliser le chargement du module
log_action('Module finance chargé', ['version' => '1.0.0'], 'system');

// =============================================
// FONCTION D'ASSISTANCE POUR LE TABLEAU DE BORD
// =============================================

/**
 * Obtenir les statistiques financières pour le tableau de bord
 */
function finance_get_stats_dashboard(int $annee_id = null): array
{
    if (!$annee_id) {
        // Récupérer l'année courante
        $annee_courante = db_query_single(
            "SELECT annee_id FROM annees_scolaire WHERE statut = 'active' ORDER BY date_debut DESC LIMIT 1"
        );
        $annee_id = $annee_courante['annee_id'] ?? null;
    }
    
    if (!$annee_id) {
        return ['success' => false, 'error' => 'Aucune année scolaire active'];
    }
    
    // Récupérer l'année scolaire
    $annee = db_query_single(
        "SELECT annee_libelle FROM annees_scolaire WHERE annee_id = :annee_id",
        ['annee_id' => $annee_id]
    );
    
    // Statistiques générales
    $stats = db_query_single("
        SELECT 
            COUNT(*) as total_paiements,
            SUM(montant_total) as total_a_payer,
            SUM(montant_paye) as total_paye,
            SUM(montant_total - montant_paye) as total_impaye,
            AVG(montant_total) as moyenne_montant
        FROM paiements
        WHERE annee_id = :annee_id
    ", ['annee_id' => $annee_id]);
    
    // Par statut
    $par_statut = db_query("
        SELECT 
            statut,
            COUNT(*) as nombre,
            SUM(montant_total) as total_a_payer,
            SUM(montant_paye) as total_paye
        FROM paiements
        WHERE annee_id = :annee_id
        GROUP BY statut
        ORDER BY FIELD(statut, 'paye', 'partiel', 'impaye', 'annule')
    ", ['annee_id' => $annee_id]);
    
    // Par type de frais
    $par_type = db_query("
        SELECT 
            type_frais,
            COUNT(*) as nombre,
            SUM(montant_total) as total_a_payer,
            SUM(montant_paye) as total_paye
        FROM paiements
        WHERE annee_id = :annee_id
        GROUP BY type_frais
        ORDER BY total_a_payer DESC
    ", ['annee_id' => $annee_id]);
    
    // Recettes du mois
    $mois_courant = date('Y-m');
    $recettes_mois = db_query_single("
        SELECT 
            COUNT(*) as paiements_mois,
            SUM(montant_paye) as recettes_mois
        FROM paiements
        WHERE DATE_FORMAT(date_creation, '%Y-%m') = :mois
        AND statut = :statut_paye
    ", ['mois' => $mois_courant, 'statut_paye' => STATUT_PAYE]);
    
    // Élèves avec impayés
    $eleves_impayes = db_query_single("
        SELECT COUNT(DISTINCT eleve_id) as nombre_eleves_impayes
        FROM paiements
        WHERE annee_id = :annee_id
        AND statut IN (:statut_impaye, :statut_partiel)
        AND montant_total > montant_paye
    ", [
        'annee_id' => $annee_id,
        'statut_impaye' => STATUT_IMPAYE,
        'statut_partiel' => STATUT_PARTIEL
    ]);
    
    // Caissier le plus actif du mois
    $caissier_actif = db_query_single("
        SELECT 
            u.prenom, u.nom,
            COUNT(p.paiement_id) as nombre_paiements,
            SUM(p.montant_paye) as total_recettes
        FROM paiements p
        JOIN user_admins u ON p.caissier_id = u.user_id
        WHERE DATE_FORMAT(p.date_creation, '%Y-%m') = :mois
        AND p.statut = :statut_paye
        GROUP BY p.caissier_id
        ORDER BY total_recettes DESC
        LIMIT 1
    ", ['mois' => $mois_courant, 'statut_paye' => STATUT_PAYE]);
    
    return [
        'success' => true,
        'annee' => $annee ? $annee['annee_libelle'] : 'Inconnue',
        'stats_generales' => $stats,
        'par_statut' => $par_statut,
        'par_type' => $par_type,
        'recettes_mois' => $recettes_mois,
        'eleves_impayes' => $eleves_impayes ? $eleves_impayes['nombre_eleves_impayes'] : 0,
        'caissier_actif' => $caissier_actif ? [
            'nom' => $caissier_actif['prenom'] . ' ' . $caissier_actif['nom'],
            'paiements' => $caissier_actif['nombre_paiements'],
            'recettes' => $caissier_actif['total_recettes']
        ] : null
    ];
}