<?php
/**
 * Module de configuration - Gestion des paramètres système
 * Version: 1.0.0
 */

require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/auth.php';

// =============================================
// CONSTANTES DE CONFIGURATION
// =============================================

// Catégories de paramètres
define('PARAM_CATEGORIE_GENERAL', 'general');
define('PARAM_CATEGORIE_ACADEMIQUE', 'academique');
define('PARAM_CATEGORIE_FINANCIER', 'financier');
define('PARAM_CATEGORIE_EDT', 'emploi_temps');
define('PARAM_CATEGORIE_DISCIPLINAIRE', 'disciplinaire');
define('PARAM_CATEGORIE_SYSTEME', 'system');
define('PARAM_CATEGORIE_SECURITE', 'securite');
define('PARAM_CATEGORIE_NOTIFICATION', 'notification');
define('PARAM_CATEGORIE_APPEARANCE', 'appearance');

// Types de paramètres
define('PARAM_TYPE_STRING', 'string');
define('PARAM_TYPE_INTEGER', 'integer');
define('PARAM_TYPE_FLOAT', 'float');
define('PARAM_TYPE_BOOLEAN', 'boolean');
define('PARAM_TYPE_JSON', 'json');
define('PARAM_TYPE_ARRAY', 'array');
define('PARAM_TYPE_DATETIME', 'datetime');
define('PARAM_TYPE_COLOR', 'color');
define('PARAM_TYPE_FILE', 'file');

// =============================================
// FONCTIONS DE BASE DE CONFIGURATION
// =============================================

/**
 * Obtenir un paramètre système
 */
function config_get(string $cle, $valeur_par_defaut = null, bool $from_cache = true)
{
    static $cache = [];
    
    // Utiliser le cache si demandé
    if ($from_cache && isset($cache[$cle])) {
        return $cache[$cle];
    }
    
    $sql = "SELECT valeur, type FROM parametres WHERE cle = :cle";
    $parametre = db_query_single($sql, ['cle' => $cle]);
    
    if (!$parametre) {
        $cache[$cle] = $valeur_par_defaut;
        return $valeur_par_defaut;
    }
    
    // Convertir selon le type
    $valeur = $parametre['valeur'];
    $type = $parametre['type'];
    
    switch ($type) {
        case PARAM_TYPE_INTEGER:
            $valeur = intval($valeur);
            break;
        case PARAM_TYPE_FLOAT:
            $valeur = floatval($valeur);
            break;
        case PARAM_TYPE_BOOLEAN:
            $valeur = filter_var($valeur, FILTER_VALIDATE_BOOLEAN);
            break;
        case PARAM_TYPE_JSON:
            $valeur = json_decode($valeur, true);
            if ($valeur === null && json_last_error() !== JSON_ERROR_NONE) {
                $valeur = $valeur_par_defaut;
            }
            break;
        case PARAM_TYPE_ARRAY:
            $valeur = explode(',', $valeur);
            $valeur = array_map('trim', $valeur);
            break;
        case PARAM_TYPE_DATETIME:
            try {
                $valeur = new DateTime($valeur);
            } catch (Exception $e) {
                $valeur = $valeur_par_defaut;
            }
            break;
        default:
            // STRING, COLOR, FILE - retourner tel quel
            break;
    }
    
    $cache[$cle] = $valeur;
    return $valeur;
}

/**
 * Définir un paramètre système
 */
function config_set(string $cle, $valeur, string $type = PARAM_TYPE_STRING, 
                   string $categorie = PARAM_CATEGORIE_GENERAL, 
                   string $description = '', 
                   bool $modifiable = true): bool
{
    // Vérifier les permissions
    if (!has_permission(PERM_MANAGE_SETTINGS)) {
        log_action('Tentative de modification de paramètre sans permission', 
                  ['cle' => $cle], 'security');
        return false;
    }
    
    // Valider le type
    if (!in_array($type, [PARAM_TYPE_STRING, PARAM_TYPE_INTEGER, PARAM_TYPE_FLOAT, 
                         PARAM_TYPE_BOOLEAN, PARAM_TYPE_JSON, PARAM_TYPE_ARRAY, 
                         PARAM_TYPE_DATETIME, PARAM_TYPE_COLOR, PARAM_TYPE_FILE])) {
        log_action('Type de paramètre invalide', ['cle' => $cle, 'type' => $type], 'system');
        return false;
    }
    
    // Convertir la valeur selon le type pour le stockage
    $valeur_stockee = '';
    
    switch ($type) {
        case PARAM_TYPE_INTEGER:
            $valeur_stockee = strval(intval($valeur));
            break;
        case PARAM_TYPE_FLOAT:
            $valeur_stockee = strval(floatval($valeur));
            break;
        case PARAM_TYPE_BOOLEAN:
            $valeur_stockee = $valeur ? '1' : '0';
            break;
        case PARAM_TYPE_JSON:
            if (is_array($valeur) || is_object($valeur)) {
                $valeur_stockee = json_encode($valeur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $valeur_stockee = strval($valeur);
            }
            break;
        case PARAM_TYPE_ARRAY:
            if (is_array($valeur)) {
                $valeur_stockee = implode(',', array_map('trim', $valeur));
            } else {
                $valeur_stockee = strval($valeur);
            }
            break;
        case PARAM_TYPE_DATETIME:
            if ($valeur instanceof DateTime) {
                $valeur_stockee = $valeur->format('Y-m-d H:i:s');
            } else {
                $valeur_stockee = date('Y-m-d H:i:s', strtotime($valeur));
            }
            break;
        default:
            $valeur_stockee = strval($valeur);
    }
    
    // Valider les valeurs spécifiques
    $validation_result = config_valider_parametre($cle, $valeur_stockee, $type);
    if (!$validation_result['success']) {
        log_action('Validation de paramètre échouée', 
                  ['cle' => $cle, 'valeur' => $valeur_stockee, 'erreur' => $validation_result['error']], 
                  'system');
        return false;
    }
    
    try {
        // Vérifier si le paramètre existe déjà
        $sql_check = "SELECT parametre_id FROM parametres WHERE cle = :cle";
        $existe = db_query_single($sql_check, ['cle' => $cle]);
        
        if ($existe) {
            // Mettre à jour
            $sql = "UPDATE parametres 
                    SET valeur = :valeur, 
                        type = :type, 
                        categorie = :categorie, 
                        description = :description,
                        modifiable = :modifiable,
                        date_modif = NOW()
                    WHERE cle = :cle";
        } else {
            // Créer
            $sql = "INSERT INTO parametres (cle, valeur, type, categorie, description, modifiable)
                    VALUES (:cle, :valeur, :type, :categorie, :description, :modifiable)";
        }
        
        $params = [
            'cle' => $cle,
            'valeur' => $valeur_stockee,
            'type' => $type,
            'categorie' => $categorie,
            'description' => $description,
            'modifiable' => $modifiable ? 1 : 0
        ];
        
        $success = db_execute($sql, $params);
        
        if ($success) {
            // Journaliser l'action
            log_action('Paramètre modifié', 
                      ['cle' => $cle, 'type' => $type, 'categorie' => $categorie], 
                      'system');
            
            // Invalider le cache
            config_clear_cache($cle);
            
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Erreur config_set: " . $e->getMessage());
        log_action('Erreur lors de la modification du paramètre', 
                  ['cle' => $cle, 'erreur' => $e->getMessage()], 
                  'system', 'error');
        return false;
    }
}

/**
 * Valider un paramètre selon sa clé
 */
function config_valider_parametre(string $cle, $valeur, string $type): array
{
    $erreurs = [];
    
    // Validation spécifique par clé
    switch ($cle) {
        case 'nom_ecole':
        case 'adresse_ecole':
        case 'devise_ecole':
            if (strlen($valeur) > 255) {
                $erreurs[] = "La valeur ne peut pas dépasser 255 caractères";
            }
            break;
            
        case 'email_ecole':
            if (!filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
                $erreurs[] = "Email invalide";
            }
            break;
            
        case 'telephone_ecole':
            if (!preg_match('/^[0-9+\s\-\(\)]{8,20}$/', $valeur)) {
                $erreurs[] = "Numéro de téléphone invalide";
            }
            break;
            
        case 'annee_courante':
            if (!is_numeric($valeur) || $valeur < 1) {
                $erreurs[] = "ID d'année scolaire invalide";
            }
            break;
            
        case 'seuil_reussite':
            $num = intval($valeur);
            if ($num < 0 || $num > 20) {
                $erreurs[] = "Le seuil doit être entre 0 et 20";
            }
            break;
            
        case 'limite_absence':
        case 'delai_paiement':
            $num = intval($valeur);
            if ($num < 0) {
                $erreurs[] = "La valeur ne peut pas être négative";
            }
            break;
            
        case 'taux_penalite':
            $num = floatval($valeur);
            if ($num < 0 || $num > 100) {
                $erreurs[] = "Le taux doit être entre 0 et 100";
            }
            break;
            
        case 'frais_inscription':
        case 'frais_scolarite':
            $num = floatval($valeur);
            if ($num < 0) {
                $erreurs[] = "Les frais ne peuvent pas être négatifs";
            }
            break;
            
        case 'heure_debut_cours':
        case 'heure_fin_cours':
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $valeur)) {
                $erreurs[] = "Format d'heure invalide (HH:MM)";
            }
            break;
            
        case 'couleur_principale':
        case 'couleur_secondaire':
            if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $valeur)) {
                $erreurs[] = "Format de couleur invalide (hexadecimal)";
            }
            break;
            
        case 'session_timeout':
            $num = intval($valeur);
            if ($num < 5 || $num > 480) {
                $erreurs[] = "Le timeout doit être entre 5 et 480 minutes";
            }
            break;
            
        case 'max_login_attempts':
            $num = intval($valeur);
            if ($num < 1 || $num > 10) {
                $erreurs[] = "Le nombre de tentatives doit être entre 1 et 10";
            }
            break;
    }
    
    // Validation par type
    switch ($type) {
        case PARAM_TYPE_INTEGER:
            if (!is_numeric($valeur)) {
                $erreurs[] = "Valeur numérique attendue";
            }
            break;
            
        case PARAM_TYPE_FLOAT:
            if (!is_numeric($valeur)) {
                $erreurs[] = "Valeur numérique décimale attendue";
            }
            break;
            
        case PARAM_TYPE_BOOLEAN:
            if (!in_array(strtolower($valeur), ['0', '1', 'true', 'false', 'yes', 'no'])) {
                $erreurs[] = "Valeur booléenne attendue";
            }
            break;
            
        case PARAM_TYPE_JSON:
            if (json_decode($valeur) === null && json_last_error() !== JSON_ERROR_NONE) {
                $erreurs[] = "JSON invalide";
            }
            break;
            
        case PARAM_TYPE_COLOR:
            if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $valeur)) {
                $erreurs[] = "Format de couleur hexadécimal invalide";
            }
            break;
    }
    
    if (empty($erreurs)) {
        return ['success' => true];
    } else {
        return [
            'success' => false,
            'error' => implode(', ', $erreurs)
        ];
    }
}

/**
 * Supprimer un paramètre
 */
function config_remove(string $cle): bool
{
    // Vérifier les permissions
    if (!has_permission(PERM_MANAGE_SETTINGS)) {
        return false;
    }
    
    // Empêcher la suppression des paramètres système critiques
    $parametres_critiques = [
        'nom_ecole', 'annee_courante', 'seuil_reussite',
        'frais_inscription', 'session_timeout'
    ];
    
    if (in_array($cle, $parametres_critiques)) {
        log_action('Tentative de suppression de paramètre critique', ['cle' => $cle], 'security');
        return false;
    }
    
    try {
        $sql = "DELETE FROM parametres WHERE cle = :cle AND modifiable = 1";
        $success = db_execute($sql, ['cle' => $cle]);
        
        if ($success) {
            // Journaliser
            log_action('Paramètre supprimé', ['cle' => $cle], 'system');
            
            // Invalider le cache
            config_clear_cache($cle);
            
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Erreur config_remove: " . $e->getMessage());
        return false;
    }
}

/**
 * Vider le cache des paramètres
 */
function config_clear_cache(?string $cle = null): void
{
    static $cache = [];
    
    if ($cle === null) {
        $cache = [];
    } elseif (isset($cache[$cle])) {
        unset($cache[$cle]);
    }
}

// =============================================
// FONCTIONS DE GESTION PAR CATÉGORIE
// =============================================

/**
 * Obtenir tous les paramètres d'une catégorie
 */
function config_get_all_by_category(string $categorie, bool $only_modifiable = false): array
{
    $sql = "SELECT * FROM parametres WHERE categorie = :categorie";
    
    if ($only_modifiable) {
        $sql .= " AND modifiable = 1";
    }
    
    $sql .= " ORDER BY cle";
    
    $parametres = db_query($sql, ['categorie' => $categorie]);
    
    // Convertir les valeurs selon leur type
    foreach ($parametres as &$parametre) {
        $parametre['valeur_convertie'] = config_convert_value(
            $parametre['valeur'], 
            $parametre['type']
        );
    }
    
    return $parametres;
}

/**
 * Obtenir toutes les catégories avec leurs paramètres
 */
function config_get_all_categories(bool $grouped = true): array
{
    $sql = "SELECT * FROM parametres ORDER BY categorie, cle";
    $parametres = db_query($sql);
    
    if (!$grouped) {
        foreach ($parametres as &$parametre) {
            $parametre['valeur_convertie'] = config_convert_value(
                $parametre['valeur'], 
                $parametre['type']
            );
        }
        return $parametres;
    }
    
    // Grouper par catégorie
    $categories = [];
    foreach ($parametres as $parametre) {
        $categorie = $parametre['categorie'];
        
        if (!isset($categories[$categorie])) {
            $categories[$categorie] = [
                'nom' => $categorie,
                'nom_affiche' => config_get_category_display_name($categorie),
                'description' => config_get_category_description($categorie),
                'parametres' => []
            ];
        }
        
        $parametre['valeur_convertie'] = config_convert_value(
            $parametre['valeur'], 
            $parametre['type']
        );
        
        $categories[$categorie]['parametres'][] = $parametre;
    }
    
    return $categories;
}

/**
 * Convertir une valeur selon son type
 */
function config_convert_value($valeur, string $type)
{
    switch ($type) {
        case PARAM_TYPE_INTEGER:
            return intval($valeur);
        case PARAM_TYPE_FLOAT:
            return floatval($valeur);
        case PARAM_TYPE_BOOLEAN:
            return filter_var($valeur, FILTER_VALIDATE_BOOLEAN);
        case PARAM_TYPE_JSON:
            $decoded = json_decode($valeur, true);
            return ($decoded !== null || json_last_error() === JSON_ERROR_NONE) ? $decoded : $valeur;
        case PARAM_TYPE_ARRAY:
            return explode(',', $valeur);
        case PARAM_TYPE_DATETIME:
            try {
                return new DateTime($valeur);
            } catch (Exception $e) {
                return $valeur;
            }
        default:
            return $valeur;
    }
}

/**
 * Obtenir le nom d'affichage d'une catégorie
 */
function config_get_category_display_name(string $categorie): string
{
    $noms = [
        PARAM_CATEGORIE_GENERAL => 'Général',
        PARAM_CATEGORIE_ACADEMIQUE => 'Académique',
        PARAM_CATEGORIE_FINANCIER => 'Financier',
        PARAM_CATEGORIE_EDT => 'Emploi du temps',
        PARAM_CATEGORIE_DISCIPLINAIRE => 'Disciplinaire',
        PARAM_CATEGORIE_SYSTEME => 'Système',
        PARAM_CATEGORIE_SECURITE => 'Sécurité',
        PARAM_CATEGORIE_NOTIFICATION => 'Notifications',
        PARAM_CATEGORIE_APPEARANCE => 'Apparence'
    ];
    
    return $noms[$categorie] ?? ucfirst(str_replace('_', ' ', $categorie));
}

/**
 * Obtenir la description d'une catégorie
 */
function config_get_category_description(string $categorie): string
{
    $descriptions = [
        PARAM_CATEGORIE_GENERAL => 'Paramètres généraux de l\'établissement',
        PARAM_CATEGORIE_ACADEMIQUE => 'Paramètres académiques et pédagogiques',
        PARAM_CATEGORIE_FINANCIER => 'Paramètres financiers et paiements',
        PARAM_CATEGORIE_EDT => 'Paramètres des emplois du temps',
        PARAM_CATEGORIE_DISCIPLINAIRE => 'Paramètres disciplinaires',
        PARAM_CATEGORIE_SYSTEME => 'Paramètres système et maintenance',
        PARAM_CATEGORIE_SECURITE => 'Paramètres de sécurité et accès',
        PARAM_CATEGORIE_NOTIFICATION => 'Paramètres des notifications',
        PARAM_CATEGORIE_APPEARANCE => 'Paramètres d\'apparence et interface'
    ];
    
    return $descriptions[$categorie] ?? '';
}

// =============================================
// FONCTIONS POUR PARAMÈTRES SPÉCIFIQUES
// =============================================

/**
 * Obtenir la configuration académique
 */
function config_get_academique(): array
{
    return [
        'annee_courante' => config_get('annee_courante', 1),
        'seuil_reussite' => config_get('seuil_reussite', 10),
        'limite_absence' => config_get('limite_absence', 10),
        'heure_debut_cours' => config_get('heure_debut_cours', '08:00'),
        'heure_fin_cours' => config_get('heure_fin_cours', '16:00'),
        'jours_cours' => config_get('jours_cours', ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'], PARAM_TYPE_ARRAY),
        'trimestre_en_cours' => config_get('trimestre_en_cours', 'trimestre1'),
        'delai_saisie_notes' => config_get('delai_saisie_notes', 7), // jours
        'coeff_examen' => config_get('coeff_examen', 2.0, PARAM_TYPE_FLOAT),
        'coeff_devoir' => config_get('coeff_devoir', 1.0, PARAM_TYPE_FLOAT)
    ];
}

/**
 * Obtenir la configuration financière
 */
function config_get_financier(): array
{
    return [
        'frais_inscription' => config_get('frais_inscription', 50000),
        'frais_scolarite' => config_get('frais_scolarite', 300000),
        'delai_paiement' => config_get('delai_paiement', 15),
        'taux_penalite' => config_get('taux_penalite', 5.0, PARAM_TYPE_FLOAT),
        'penalite_fixe' => config_get('penalite_fixe', 5000),
        'devise' => config_get('devise_monetaire', 'FCFA'),
        'modes_paiement' => config_get('modes_paiement', ['Espece', 'Virement', 'Mobile'], PARAM_TYPE_ARRAY),
        'banques_acceptees' => config_get('banques_acceptees', [], PARAM_TYPE_ARRAY),
        'tva_applicable' => config_get('tva_applicable', 0.0, PARAM_TYPE_FLOAT),
        'remise_famille' => config_get('remise_famille', 0.0, PARAM_TYPE_FLOAT)
    ];
}

/**
 * Obtenir la configuration système
 */
function config_get_systeme(): array
{
    return [
        'maintenance_mode' => config_get('maintenance_mode', false, PARAM_TYPE_BOOLEAN),
        'debug_mode' => config_get('debug_mode', false, PARAM_TYPE_BOOLEAN),
        'timezone' => config_get('timezone', 'Africa/Kinshasa'),
        'locale' => config_get('locale', 'fr_FR'),
        'charset' => config_get('charset', 'UTF-8'),
        'session_timeout' => config_get('session_timeout', 30),
        'max_login_attempts' => config_get('max_login_attempts', 3),
        'backup_auto' => config_get('backup_auto', true, PARAM_TYPE_BOOLEAN),
        'backup_frequency' => config_get('backup_frequency', 'daily'),
        'backup_keep_days' => config_get('backup_keep_days', 30),
        'log_level' => config_get('log_level', 'info'),
        'log_keep_days' => config_get('log_keep_days', 90),
        'cache_enabled' => config_get('cache_enabled', true, PARAM_TYPE_BOOLEAN),
        'cache_ttl' => config_get('cache_ttl', 3600),
        'email_enabled' => config_get('email_enabled', true, PARAM_TYPE_BOOLEAN),
        'smtp_host' => config_get('smtp_host', ''),
        'smtp_port' => config_get('smtp_port', 587),
        'smtp_secure' => config_get('smtp_secure', 'tls')
    ];
}

/**
 * Obtenir la configuration de sécurité
 */
function config_get_securite(): array
{
    return [
        'password_min_length' => config_get('password_min_length', 8),
        'password_require_uppercase' => config_get('password_require_uppercase', true, PARAM_TYPE_BOOLEAN),
        'password_require_lowercase' => config_get('password_require_lowercase', true, PARAM_TYPE_BOOLEAN),
        'password_require_numbers' => config_get('password_require_numbers', true, PARAM_TYPE_BOOLEAN),
        'password_require_special' => config_get('password_require_special', true, PARAM_TYPE_BOOLEAN),
        'password_expire_days' => config_get('password_expire_days', 90),
        'two_factor_enabled' => config_get('two_factor_enabled', false, PARAM_TYPE_BOOLEAN),
        'ip_whitelist' => config_get('ip_whitelist', [], PARAM_TYPE_ARRAY),
        'ip_blacklist' => config_get('ip_blacklist', [], PARAM_TYPE_ARRAY),
        'session_regenerate' => config_get('session_regenerate', true, PARAM_TYPE_BOOLEAN),
        'force_https' => config_get('force_https', false, PARAM_TYPE_BOOLEAN),
        'xss_protection' => config_get('xss_protection', true, PARAM_TYPE_BOOLEAN),
        'csrf_protection' => config_get('csrf_protection', true, PARAM_TYPE_BOOLEAN)
    ];
}

/**
 * Obtenir la configuration d'apparence
 */
function config_get_appearance(): array
{
    return [
        'theme' => config_get('theme', 'default'),
        'couleur_principale' => config_get('couleur_principale', '#3498db'),
        'couleur_secondaire' => config_get('couleur_secondaire', '#2ecc71'),
        'couleur_danger' => config_get('couleur_danger', '#e74c3c'),
        'couleur_warning' => config_get('couleur_warning', '#f39c12'),
        'couleur_success' => config_get('couleur_success', '#27ae60'),
        'logo_ecole' => config_get('logo_ecole', ''),
        'favicon' => config_get('favicon', ''),
        'font_family' => config_get('font_family', 'Arial, sans-serif'),
        'font_size' => config_get('font_size', '14px'),
        'sidebar_collapsed' => config_get('sidebar_collapsed', false, PARAM_TYPE_BOOLEAN),
        'dashboard_widgets' => config_get('dashboard_widgets', ['stats', 'calendar', 'notifications'], PARAM_TYPE_ARRAY),
        'animations_enabled' => config_get('animations_enabled', true, PARAM_TYPE_BOOLEAN)
    ];
}

/**
 * Obtenir la configuration des notifications
 */
function config_get_notifications(): array
{
    return [
        'notify_new_student' => config_get('notify_new_student', true, PARAM_TYPE_BOOLEAN),
        'notify_payment_due' => config_get('notify_payment_due', true, PARAM_TYPE_BOOLEAN),
        'notify_absence' => config_get('notify_absence', true, PARAM_TYPE_BOOLEAN),
        'notify_low_grade' => config_get('notify_low_grade', true, PARAM_TYPE_BOOLEAN),
        'notify_system_alert' => config_get('notify_system_alert', true, PARAM_TYPE_BOOLEAN),
        'email_notifications' => config_get('email_notifications', true, PARAM_TYPE_BOOLEAN),
        'sms_notifications' => config_get('sms_notifications', false, PARAM_TYPE_BOOLEAN),
        'push_notifications' => config_get('push_notifications', false, PARAM_TYPE_BOOLEAN),
        'notification_sound' => config_get('notification_sound', true, PARAM_TYPE_BOOLEAN),
        'auto_clear_notifications' => config_get('auto_clear_notifications', 7), // jours
        'digest_frequency' => config_get('digest_frequency', 'daily') // daily, weekly, monthly
    ];
}

// =============================================
// FONCTIONS D'INITIALISATION
// =============================================

/**
 * Initialiser les paramètres par défaut
 */
function config_initialize_defaults(): array
{
    $parametres_defaut = [
        // Général
        [
            'cle' => 'nom_ecole',
            'valeur' => 'École Secondaire d\'Excellence',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_GENERAL,
            'description' => 'Nom officiel de l\'établissement',
            'modifiable' => true
        ],
        [
            'cle' => 'adresse_ecole',
            'valeur' => '123 Avenue de l\'Éducation, Kinshasa',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_GENERAL,
            'description' => 'Adresse physique de l\'établissement',
            'modifiable' => true
        ],
        [
            'cle' => 'telephone_ecole',
            'valeur' => '+243 81 234 5678',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_GENERAL,
            'description' => 'Numéro de téléphone de contact',
            'modifiable' => true
        ],
        [
            'cle' => 'email_ecole',
            'valeur' => 'contact@ecole-excellence.cd',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_GENERAL,
            'description' => 'Email de contact principal',
            'modifiable' => true
        ],
        [
            'cle' => 'devise_ecole',
            'valeur' => 'Savoir, Excellence, Discipline',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_GENERAL,
            'description' => 'Devise ou slogan de l\'établissement',
            'modifiable' => true
        ],
        
        // Académique
        [
            'cle' => 'annee_courante',
            'valeur' => '1',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_ACADEMIQUE,
            'description' => 'ID de l\'année scolaire courante',
            'modifiable' => true
        ],
        [
            'cle' => 'seuil_reussite',
            'valeur' => '10',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_ACADEMIQUE,
            'description' => 'Note minimale sur 20 pour réussir',
            'modifiable' => true
        ],
        [
            'cle' => 'limite_absence',
            'valeur' => '10',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_ACADEMIQUE,
            'description' => 'Nombre maximum d\'absences non justifiées autorisées',
            'modifiable' => true
        ],
        [
            'cle' => 'heure_debut_cours',
            'valeur' => '08:00',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_ACADEMIQUE,
            'description' => 'Heure de début des cours',
            'modifiable' => true
        ],
        [
            'cle' => 'heure_fin_cours',
            'valeur' => '16:00',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_ACADEMIQUE,
            'description' => 'Heure de fin des cours',
            'modifiable' => true
        ],
        
        // Financier
        [
            'cle' => 'frais_inscription',
            'valeur' => '50000',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_FINANCIER,
            'description' => 'Frais d\'inscription par défaut (en FCFA)',
            'modifiable' => true
        ],
        [
            'cle' => 'frais_scolarite',
            'valeur' => '300000',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_FINANCIER,
            'description' => 'Frais de scolarité par trimestre (en FCFA)',
            'modifiable' => true
        ],
        [
            'cle' => 'delai_paiement',
            'valeur' => '15',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_FINANCIER,
            'description' => 'Délai de paiement en jours après émission',
            'modifiable' => true
        ],
        [
            'cle' => 'taux_penalite',
            'valeur' => '5',
            'type' => PARAM_TYPE_FLOAT,
            'categorie' => PARAM_CATEGORIE_FINANCIER,
            'description' => 'Taux de pénalité pour retard de paiement (%)',
            'modifiable' => true
        ],
        
        // Système
        [
            'cle' => 'session_timeout',
            'valeur' => '30',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_SYSTEME,
            'description' => 'Durée d\'inactivité avant déconnexion (minutes)',
            'modifiable' => true
        ],
        [
            'cle' => 'max_login_attempts',
            'valeur' => '3',
            'type' => PARAM_TYPE_INTEGER,
            'categorie' => PARAM_CATEGORIE_SYSTEME,
            'description' => 'Nombre maximum de tentatives de connexion échouées',
            'modifiable' => true
        ],
        [
            'cle' => 'backup_auto',
            'valeur' => '1',
            'type' => PARAM_TYPE_BOOLEAN,
            'categorie' => PARAM_CATEGORIE_SYSTEME,
            'description' => 'Sauvegarde automatique activée',
            'modifiable' => true
        ],
        [
            'cle' => 'backup_frequency',
            'valeur' => 'daily',
            'type' => PARAM_TYPE_STRING,
            'categorie' => PARAM_CATEGORIE_SYSTEME,
            'description' => 'Fréquence des sauvegardes automatiques',
            'modifiable' => true
        ]
    ];
    
    $resultats = [];
    $inserted = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($parametres_defaut as $parametre) {
        // Vérifier si le paramètre existe déjà
        $existe = db_query_single(
            "SELECT COUNT(*) as count FROM parametres WHERE cle = :cle",
            ['cle' => $parametre['cle']]
        );
        
        if ($existe && $existe['count'] > 0) {
            $skipped++;
            $resultats[$parametre['cle']] = 'existe déjà';
            continue;
        }
        
        try {
            // Insérer le paramètre
            $sql = "INSERT INTO parametres (cle, valeur, type, categorie, description, modifiable, date_creation)
                    VALUES (:cle, :valeur, :type, :categorie, :description, :modifiable, NOW())";
            
            $success = db_execute($sql, [
                'cle' => $parametre['cle'],
                'valeur' => $parametre['valeur'],
                'type' => $parametre['type'],
                'categorie' => $parametre['categorie'],
                'description' => $parametre['description'],
                'modifiable' => $parametre['modifiable'] ? 1 : 0
            ]);
            
            if ($success) {
                $inserted++;
                $resultats[$parametre['cle']] = 'inséré';
            } else {
                $errors++;
                $resultats[$parametre['cle']] = 'erreur d\'insertion';
            }
            
        } catch (Exception $e) {
            $errors++;
            $resultats[$parametre['cle']] = 'exception: ' . $e->getMessage();
            error_log("Erreur insertion paramètre {$parametre['cle']}: " . $e->getMessage());
        }
    }
    
    return [
        'success' => $errors === 0,
        'inserted' => $inserted,
        'skipped' => $skipped,
        'errors' => $errors,
        'details' => $resultats,
        'total' => count($parametres_defaut)
    ];
}

/**
 * Vérifier et réparer la configuration
 */
function config_check_and_repair(): array
{
    $problemes = [];
    $reparations = [];
    
    // Vérifier les paramètres critiques
    $parametres_critiques = [
        'nom_ecole' => PARAM_TYPE_STRING,
        'annee_courante' => PARAM_TYPE_INTEGER,
        'seuil_reussite' => PARAM_TYPE_INTEGER,
        'session_timeout' => PARAM_TYPE_INTEGER
    ];
    
    foreach ($parametres_critiques as $cle => $type_attendu) {
        $parametre = db_query_single(
            "SELECT valeur, type FROM parametres WHERE cle = :cle",
            ['cle' => $cle]
        );
        
        if (!$parametre) {
            $problemes[] = "Paramètre critique manquant: {$cle}";
            
            // Essayer de le réparer avec une valeur par défaut
            $valeur_defaut = config_get_default_value($cle);
            if ($valeur_defaut !== null) {
                if (config_set($cle, $valeur_defaut, $type_attendu)) {
                    $reparations[] = "Paramètre {$cle} restauré avec valeur par défaut";
                } else {
                    $problemes[] = "Échec de restauration du paramètre {$cle}";
                }
            }
        } elseif ($parametre['type'] !== $type_attendu) {
            $problemes[] = "Type incorrect pour {$cle}: {$parametre['type']} au lieu de {$type_attendu}";
        }
    }
    
    // Vérifier les valeurs invalides
    $valeurs_a_verifier = [
        'seuil_reussite' => function($v) { return $v >= 0 && $v <= 20; },
        'session_timeout' => function($v) { return $v >= 5 && $v <= 480; },
        'max_login_attempts' => function($v) { return $v >= 1 && $v <= 10; },
        'taux_penalite' => function($v) { return $v >= 0 && $v <= 100; }
    ];
    
    foreach ($valeurs_a_verifier as $cle => $validation) {
        $valeur = config_get($cle);
        if (!$validation($valeur)) {
            $problemes[] = "Valeur invalide pour {$cle}: {$valeur}";
            
            // Corriger avec une valeur par défaut
            $valeur_defaut = config_get_default_value($cle);
            if ($valeur_defaut !== null && $validation($valeur_defaut)) {
                config_set($cle, $valeur_defaut);
                $reparations[] = "Paramètre {$cle} corrigé à {$valeur_defaut}";
            }
        }
    }
    
    return [
        'success' => empty($problemes),
        'problemes' => $problemes,
        'reparations' => $reparations,
        'date_verification' => date('Y-m-d H:i:s')
    ];
}

/**
 * Obtenir une valeur par défaut pour un paramètre
 */
function config_get_default_value(string $cle)
{
    $defaults = [
        'nom_ecole' => 'École Secondaire d\'Excellence',
        'annee_courante' => 1,
        'seuil_reussite' => 10,
        'session_timeout' => 30,
        'max_login_attempts' => 3,
        'taux_penalite' => 5.0
    ];
    
    return $defaults[$cle] ?? null;
}

// =============================================
// FONCTIONS D'EXPORT/IMPORT
// =============================================

/**
 * Exporter la configuration au format JSON
 */
function config_export_json(?array $categories = null, bool $include_non_modifiable = false): array
{
    // Vérifier les permissions
    if (!has_permission(PERM_EXPORT)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        $sql = "SELECT * FROM parametres WHERE 1=1";
        $params = [];
        
        if (!$include_non_modifiable) {
            $sql .= " AND modifiable = 1";
        }
        
        if ($categories !== null && !empty($categories)) {
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $sql .= " AND categorie IN ({$placeholders})";
            $params = $categories;
        }
        
        $sql .= " ORDER BY categorie, cle";
        
        $parametres = db_query($sql, $params);
        
        // Formater pour l'export
        $export_data = [
            'metadata' => [
                'export_date' => date('Y-m-d H:i:s'),
                'exported_by' => $_SESSION['user_id'] ?? null,
                'total_params' => count($parametres),
                'version' => '1.0'
            ],
            'parameters' => []
        ];
        
        foreach ($parametres as $parametre) {
            $export_data['parameters'][] = [
                'cle' => $parametre['cle'],
                'valeur' => $parametre['valeur'],
                'type' => $parametre['type'],
                'categorie' => $parametre['categorie'],
                'description' => $parametre['description'],
                'modifiable' => (bool)$parametre['modifiable']
            ];
        }
        
        // Journaliser l'export
        log_action('Configuration exportée', 
                  ['format' => 'json', 'count' => count($parametres)], 
                  'system');
        
        return [
            'success' => true,
            'data' => json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'format' => 'json',
            'count' => count($parametres)
        ];
        
    } catch (Exception $e) {
        error_log("Erreur config_export_json: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'export'];
    }
}

/**
 * Importer la configuration depuis JSON
 */
function config_import_json(string $json_data, bool $overwrite = false, bool $skip_non_modifiable = true): array
{
    // Vérifier les permissions
    if (!has_permission(PERM_IMPORT)) {
        return ['success' => false, 'error' => 'Permission refusée'];
    }
    
    try {
        $data = json_decode($json_data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'JSON invalide: ' . json_last_error_msg()];
        }
        
        if (!isset($data['parameters']) || !is_array($data['parameters'])) {
            return ['success' => false, 'error' => 'Format d\'import invalide'];
        }
        
        $results = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];
        
        foreach ($data['parameters'] as $parametre) {
            // Vérifier les champs requis
            if (!isset($parametre['cle'], $parametre['valeur'], $parametre['type'], $parametre['categorie'])) {
                $results['errors']++;
                $results['details'][$parametre['cle'] ?? 'unknown'] = 'champs manquants';
                continue;
            }
            
            // Vérifier si le paramètre est modifiable
            if ($skip_non_modifiable && isset($parametre['modifiable']) && !$parametre['modifiable']) {
                $results['skipped']++;
                $results['details'][$parametre['cle']] = 'non modifiable';
                continue;
            }
            
            // Vérifier si le paramètre existe déjà
            $existe = db_query_single(
                "SELECT parametre_id, modifiable FROM parametres WHERE cle = :cle",
                ['cle' => $parametre['cle']]
            );
            
            if ($existe) {
                if (!$overwrite) {
                    $results['skipped']++;
                    $results['details'][$parametre['cle']] = 'existe déjà (sans overwrite)';
                    continue;
                }
                
                // Vérifier si on peut modifier un paramètre non modifiable
                if ($existe['modifiable'] == 0 && $skip_non_modifiable) {
                    $results['skipped']++;
                    $results['details'][$parametre['cle']] = 'non modifiable dans la base';
                    continue;
                }
                
                // Mettre à jour
                $sql = "UPDATE parametres 
                        SET valeur = :valeur, 
                            type = :type, 
                            categorie = :categorie,
                            description = :description,
                            modifiable = :modifiable,
                            date_modif = NOW()
                        WHERE cle = :cle";
                
                $result = 'updated';
                $results['updated']++;
            } else {
                // Créer
                $sql = "INSERT INTO parametres (cle, valeur, type, categorie, description, modifiable, date_creation)
                        VALUES (:cle, :valeur, :type, :categorie, :description, :modifiable, NOW())";
                
                $result = 'imported';
                $results['imported']++;
            }
            
            try {
                $success = db_execute($sql, [
                    'cle' => $parametre['cle'],
                    'valeur' => $parametre['valeur'],
                    'type' => $parametre['type'],
                    'categorie' => $parametre['categorie'],
                    'description' => $parametre['description'] ?? '',
                    'modifiable' => isset($parametre['modifiable']) ? ($parametre['modifiable'] ? 1 : 0) : 1
                ]);
                
                if ($success) {
                    $results['details'][$parametre['cle']] = $result;
                    
                    // Invalider le cache pour ce paramètre
                    config_clear_cache($parametre['cle']);
                } else {
                    $results['errors']++;
                    $results['details'][$parametre['cle']] = 'erreur d\'exécution';
                }
                
            } catch (Exception $e) {
                $results['errors']++;
                $results['details'][$parametre['cle']] = 'exception: ' . $e->getMessage();
                error_log("Erreur import paramètre {$parametre['cle']}: " . $e->getMessage());
            }
        }
        
        // Journaliser l'import
        log_action('Configuration importée', 
                  [
                      'imported' => $results['imported'],
                      'updated' => $results['updated'],
                      'skipped' => $results['skipped'],
                      'errors' => $results['errors'],
                      'overwrite' => $overwrite
                  ], 
                  'system');
        
        return [
            'success' => $results['errors'] === 0,
            'results' => $results,
            'total_processed' => count($data['parameters'])
        ];
        
    } catch (Exception $e) {
        error_log("Erreur config_import_json: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'import: ' . $e->getMessage()];
    }
}

/**
 * Réinitialiser la configuration aux valeurs par défaut
 */
function config_reset_to_defaults(array $categories = []): array
{
    // Vérifier les permissions
    if (!has_role(ROLE_SUPERADMIN)) {
        return ['success' => false, 'error' => 'Permission refusée - Superadmin requis'];
    }
    
    try {
        // Obtenir les paramètres par défaut
        $parametres_defaut = config_initialize_defaults();
        
        if (empty($categories)) {
            // Supprimer tous les paramètres modifiables
            $sql = "DELETE FROM parametres WHERE modifiable = 1";
            db_execute($sql);
            
            // Réinitialiser avec les valeurs par défaut
            config_initialize_defaults();
            
            $message = 'Tous les paramètres modifiables ont été réinitialisés';
        } else {
            // Supprimer les paramètres des catégories spécifiées
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $sql = "DELETE FROM parametres WHERE modifiable = 1 AND categorie IN ({$placeholders})";
            db_execute($sql, $categories);
            
            // Réinitialiser les paramètres par défaut pour ces catégories
            foreach ($parametres_defaut as $parametre) {
                if (in_array($parametre['categorie'], $categories)) {
                    config_set(
                        $parametre['cle'],
                        $parametre['valeur'],
                        $parametre['type'],
                        $parametre['categorie'],
                        $parametre['description'],
                        $parametre['modifiable']
                    );
                }
            }
            
            $message = 'Paramètres des catégories ' . implode(', ', $categories) . ' réinitialisés';
        }
        
        // Vider complètement le cache
        config_clear_cache();
        
        // Journaliser
        log_action('Configuration réinitialisée', 
                  ['categories' => $categories], 
                  'system');
        
        return [
            'success' => true,
            'message' => $message,
            'categories' => $categories,
            'date_reinitialisation' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        error_log("Erreur config_reset_to_defaults: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la réinitialisation'];
    }
}

// =============================================
// FONCTIONS UTILITAIRES
// =============================================

/**
 * Formater une valeur pour l'affichage
 */
function config_format_display($valeur, string $type): string
{
    switch ($type) {
        case PARAM_TYPE_BOOLEAN:
            return $valeur ? 'Oui' : 'Non';
        case PARAM_TYPE_ARRAY:
            return is_array($valeur) ? implode(', ', $valeur) : strval($valeur);
        case PARAM_TYPE_JSON:
            return is_array($valeur) || is_object($valeur) ? 
                   json_encode($valeur, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : 
                   strval($valeur);
        case PARAM_TYPE_DATETIME:
            if ($valeur instanceof DateTime) {
                return $valeur->format('d/m/Y H:i:s');
            }
            return strval($valeur);
        default:
            return strval($valeur);
    }
}

/**
 * Obtenir les statistiques de configuration
 */
function config_get_statistics(): array
{
    $stats = [];
    
    // Nombre total de paramètres
    $sql_total = "SELECT COUNT(*) as total FROM parametres";
    $total = db_query_single($sql_total);
    $stats['total'] = $total['total'] ?? 0;
    
    // Par catégorie
    $sql_categories = "SELECT categorie, COUNT(*) as nombre FROM parametres GROUP BY categorie";
    $categories = db_query($sql_categories);
    $stats['par_categorie'] = $categories;
    
    // Par type
    $sql_types = "SELECT type, COUNT(*) as nombre FROM parametres GROUP BY type";
    $types = db_query($sql_types);
    $stats['par_type'] = $types;
    
    // Modifiable vs non-modifiable
    $sql_modifiable = "SELECT modifiable, COUNT(*) as nombre FROM parametres GROUP BY modifiable";
    $modifiable = db_query($sql_modifiable);
    $stats['modifiable'] = $modifiable;
    
    // Dernière modification
    $sql_last_modif = "SELECT MAX(date_modif) as derniere_modification FROM parametres";
    $last_modif = db_query_single($sql_last_modif);
    $stats['derniere_modification'] = $last_modif['derniere_modification'] ?? null;
    
    return [
        'success' => true,
        'statistiques' => $stats,
        'date_calcul' => date('Y-m-d H:i:s')
    ];
}

/**
 * Générer un tableau HTML des paramètres
 */
function config_generate_html_table(array $parametres): string
{
    if (empty($parametres)) {
        return '<p>Aucun paramètre trouvé.</p>';
    }
    
    $html = '<table class="table table-striped table-hover">';
    $html .= '<thead><tr>';
    $html .= '<th>Clé</th>';
    $html .= '<th>Valeur</th>';
    $html .= '<th>Type</th>';
    $html .= '<th>Catégorie</th>';
    $html .= '<th>Description</th>';
    $html .= '<th>Modifiable</th>';
    $html .= '</tr></thead>';
    $html .= '<tbody>';
    
    foreach ($parametres as $parametre) {
        $valeur_affichage = config_format_display(
            $parametre['valeur_convertie'] ?? $parametre['valeur'], 
            $parametre['type']
        );
        
        $html .= '<tr>';
        $html .= '<td><code>' . htmlspecialchars($parametre['cle']) . '</code></td>';
        $html .= '<td><span class="config-value">' . htmlspecialchars($valeur_affichage) . '</span></td>';
        $html .= '<td><span class="badge bg-info">' . htmlspecialchars($parametre['type']) . '</span></td>';
        $html .= '<td><span class="badge bg-secondary">' . htmlspecialchars($parametre['categorie']) . '</span></td>';
        $html .= '<td>' . htmlspecialchars($parametre['description'] ?? '') . '</td>';
        $html .= '<td>' . ($parametre['modifiable'] ? '✓' : '✗') . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    return $html;
}

// =============================================
// INITIALISATION
// =============================================

// Charger les fonctions de configuration dans le contexte global
if (!function_exists('get_config')) {
    function get_config(string $cle, $default = null) {
        return config_get($cle, $default);
    }
}

if (!function_exists('set_config')) {
    function set_config(string $cle, $valeur, string $type = PARAM_TYPE_STRING): bool {
        return config_set($cle, $valeur, $type);
    }
}

// Journaliser le chargement du module
log_action('Module de configuration chargé', ['version' => '1.0.0'], 'system');