<?php
/**
 * Fonctions utilitaires générales de l'application LaVision
 * Contient les helpers et utilitaires réutilisables
 * Programmation procédurale pour cohérence
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

// =============================================
// FONCTIONS DE FORMATAGE ET AFFICHAGE
// =============================================

/**
 * Formate un montant en devise congolaise (CDF)
 *
 * @param float $montant Montant à formater
 * @param bool $avec_symbole Inclure le symbole FC
 * @return string Montant formaté
 */
function formater_montant(float $montant, bool $avec_symbole = true): string
{
    $format = number_format($montant, 0, ',', ' ');

    return $avec_symbole ? $format . ' FC' : $format;
}

/**
 * Formate une date en français
 *
 * @param string $date Date à formater (format MySQL)
 * @param bool $avec_heure Inclure l'heure
 * @return string Date formatée
 */
function formater_date(string $date, bool $avec_heure = false): string
{
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'Non défini';
    }

    $timestamp = strtotime($date);

    if ($avec_heure) {
        return date('d/m/Y H:i', $timestamp);
    }

    return date('d/m/Y', $timestamp);
}

/**
 * Formate un numéro de téléphone congolais
 *
 * @param string $telephone Numéro à formater
 * @return string Numéro formaté
 */
function formater_telephone(string $telephone): string
{
    if (empty($telephone)) {
        return 'Non défini';
    }

    // Suppression des espaces et caractères spéciaux
    $telephone = preg_replace('/\s+/', '', $telephone);

    // Format congolais: +243 XXX XXX XXX
    if (preg_match('/^(\+243|243|0)?([0-9]{3})([0-9]{3})([0-9]{3})$/', $telephone, $matches)) {
        $prefix = '+243';
        return $prefix . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4];
    }

    return $telephone;
}

/**
 * Calcule l'âge à partir d'une date de naissance
 *
 * @param string $date_naissance Date de naissance
 * @return int Âge en années
 */
function calculer_age(string $date_naissance): int
{
    if (empty($date_naissance) || $date_naissance === '0000-00-00') {
        return 0;
    }

    $date_naissance = new DateTime($date_naissance);
    $aujourd_hui = new DateTime();
    $age = $aujourd_hui->diff($date_naissance);

    return $age->y;
}

/**
 * Génère un identifiant unique
 *
 * @param string $prefix Préfixe optionnel
 * @return string ID unique
 */
function generer_id_unique(string $prefix = ''): string
{
    $timestamp = time();
    $random = bin2hex(random_bytes(4));

    return $prefix . $timestamp . $random;
}

/**
 * Tronque un texte à une longueur donnée
 *
 * @param string $texte Texte à tronquer
 * @param int $longueur Longueur maximale
 * @param string $suffixe Suffixe à ajouter
 * @return string Texte tronqué
 */
function tronquer_texte(string $texte, int $longueur = 100, string $suffixe = '...'): string
{
    if (strlen($texte) <= $longueur) {
        return $texte;
    }

    return substr($texte, 0, $longueur - strlen($suffixe)) . $suffixe;
}

// =============================================
// FONCTIONS DE VALIDATION
// =============================================

/**
 * Valide un numéro d'identification nationale congolaise
 *
 * @param string $numero Numéro à valider
 * @return bool True si valide
 */
function valider_numero_identite(string $numero): bool
{
    // Format de base: 12 chiffres pour la RDC
    return preg_match('/^[0-9]{12}$/', $numero) === 1;
}

// =============================================
// FONCTIONS DE SÉCURITÉ
// =============================================

/**
 * Nettoie une chaîne de caractères pour éviter les XSS
 *
 * @param string $chaine Chaîne à nettoyer
 * @return string Chaîne nettoyée
 */
function nettoyer_chaine(string $chaine): string
{
    return htmlspecialchars(trim($chaine), ENT_QUOTES, 'UTF-8');
}

/**
 * Génère un token CSRF
 *
 * @return string Token généré
 */
function generer_token_csrf(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 *
 * @param string $token Token à vérifier
 * @return bool True si valide
 */
function verifier_token_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Génère un mot de passe aléatoire
 *
 * @param int $longueur Longueur du mot de passe
 * @return string Mot de passe généré
 */
function generer_mot_de_passe(int $longueur = 12): string
{
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    $mot_de_passe = '';

    for ($i = 0; $i < $longueur; $i++) {
        $mot_de_passe .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    return $mot_de_passe;
}

// =============================================
// FONCTIONS DE FICHIERS ET UPLOAD
// =============================================

/**
 * Upload un fichier avec validation
 *
 * @param array $fichier Données du fichier ($_FILES)
 * @param string $dossier Dossier de destination
 * @param array $extensions Extensions autorisées
 * @param int $taille_max Taille maximale en octets
 * @return array Résultat avec chemin ou erreur
 */
function uploader_fichier(array $fichier, string $dossier, array $extensions = [], int $taille_max = 0): array
{
    // Vérification des erreurs d'upload
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        return ['succes' => false, 'message' => 'Erreur lors de l\'upload du fichier'];
    }

    // Vérification de la taille
    if ($taille_max > 0 && $fichier['size'] > $taille_max) {
        return ['succes' => false, 'message' => 'Fichier trop volumineux'];
    }

    // Vérification de l'extension
    if (!empty($extensions)) {
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensions)) {
            return ['succes' => false, 'message' => 'Type de fichier non autorisé'];
        }
    }

    // Création du dossier si nécessaire
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }

    // Génération du nom de fichier unique
    $nom_unique = generer_id_unique() . '.' . strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    $chemin_destination = $dossier . '/' . $nom_unique;

    // Déplacement du fichier
    if (move_uploaded_file($fichier['tmp_name'], $chemin_destination)) {
        return [
            'succes' => true,
            'chemin' => $chemin_destination,
            'nom_original' => $fichier['name'],
            'nom_unique' => $nom_unique,
            'taille' => $fichier['size']
        ];
    }

    return ['succes' => false, 'message' => 'Erreur lors de la sauvegarde du fichier'];
}

/**
 * Supprime un fichier s'il existe
 *
 * @param string $chemin Chemin du fichier
 * @return bool True si supprimé ou inexistant
 */
function supprimer_fichier(string $chemin): bool
{
    if (file_exists($chemin)) {
        return unlink($chemin);
    }

    return true;
}

// =============================================
// FONCTIONS DE PAGINATION
// =============================================

/**
 * Calcule les informations de pagination
 *
 * @param int $total_elements Nombre total d'éléments
 * @param int $elements_par_page Nombre d'éléments par page
 * @param int $page_actuelle Page actuelle
 * @return array Informations de pagination
 */
function calculer_pagination(int $total_elements, int $elements_par_page, int $page_actuelle): array
{
    $total_pages = ceil($total_elements / $elements_par_page);
    $page_actuelle = max(1, min($page_actuelle, $total_pages));

    $debut = ($page_actuelle - 1) * $elements_par_page;
    $fin = min($debut + $elements_par_page, $total_elements);

    return [
        'total_elements' => $total_elements,
        'total_pages' => $total_pages,
        'page_actuelle' => $page_actuelle,
        'elements_par_page' => $elements_par_page,
        'debut' => $debut,
        'fin' => $fin,
        'a_page_precedente' => $page_actuelle > 1,
        'a_page_suivante' => $page_actuelle < $total_pages
    ];
}

/**
 * Génère les liens de pagination HTML
 *
 * @param array $pagination Données de pagination
 * @param string $url_base URL de base pour les liens
 * @param array $parametres Paramètres supplémentaires
 * @return string HTML de pagination
 */
function generer_pagination_html(array $pagination, string $url_base, array $parametres = []): string
{
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';

    // Bouton précédent
    if ($pagination['a_page_precedente']) {
        $parametres['page'] = $pagination['page_actuelle'] - 1;
        $url = $url_base . '?' . http_build_query($parametres);
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($url) . '">&laquo;</a></li>';
    }

    // Numéros de page
    $debut = max(1, $pagination['page_actuelle'] - 2);
    $fin = min($pagination['total_pages'], $pagination['page_actuelle'] + 2);

    for ($i = $debut; $i <= $fin; $i++) {
        $parametres['page'] = $i;
        $url = $url_base . '?' . http_build_query($parametres);
        $active = $i == $pagination['page_actuelle'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . htmlspecialchars($url) . '">' . $i . '</a></li>';
    }

    // Bouton suivant
    if ($pagination['a_page_suivante']) {
        $parametres['page'] = $pagination['page_actuelle'] + 1;
        $url = $url_base . '?' . http_build_query($parametres);
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($url) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';

    return $html;
}

// =============================================
// FONCTIONS DE LOGGING
// =============================================

/**
 * Écrit un message dans le journal d'erreurs
 *
 * @param string $message Message d'erreur
 * @param array $contexte Contexte supplémentaire
 * @return void
 */
function logger_erreur(string $message, array $contexte = []): void
{
    $date = date('Y-m-d H:i:s');
    $contexte_str = !empty($contexte) ? ' | Contexte: ' . json_encode($contexte) : '';
    $message_complet = "[$date] ERREUR: $message$contexte_str\n";

    $fichier_log = LOGS_PATH . '/errors/' . date('Y-m-d') . '.log';

    // Création du dossier si nécessaire
    $dossier = dirname($fichier_log);
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }

    file_put_contents($fichier_log, $message_complet, FILE_APPEND | LOCK_EX);
}

/**
 * Écrit un message dans le journal d'actions
 *
 * @param string $message Message d'action
 * @param array $contexte Contexte supplémentaire
 * @return void
 */
function logger_action(string $message, array $contexte = []): void
{
    $date = date('Y-m-d H:i:s');
    $utilisateur = $_SESSION['utilisateur_nom'] ?? 'Système';
    $contexte_str = !empty($contexte) ? ' | Contexte: ' . json_encode($contexte) : '';
    $message_complet = "[$date] [$utilisateur] ACTION: $message$contexte_str\n";

    $fichier_log = LOGS_PATH . '/actions/' . date('Y-m-d') . '.log';

    // Création du dossier si nécessaire
    $dossier = dirname($fichier_log);
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }

    file_put_contents($fichier_log, $message_complet, FILE_APPEND | LOCK_EX);
}

// =============================================
// FONCTIONS DIVERS
// =============================================


/**
 * Génère une réponse JSON
 *
 * @param array $donnees Données à encoder
 * @param int $code_http Code HTTP (optionnel)
 * @return void
 */
function repondre_json(array $donnees, int $code_http = 200): void
{
    http_response_code($code_http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Vérifie si la requête est AJAX
 *
 * @return bool True si AJAX
 */
function est_requete_ajax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Obtient l'adresse IP réelle du client
 *
 * @return string Adresse IP
 */
function obtenir_ip_client(): string
{
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = trim(explode(',', $_SERVER[$header])[0]);

            // Vérification que c'est une IP valide
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

// =============================================
// FONCTIONS DE LOGGING ET SÉCURITÉ
// =============================================

/**
 * Définit un message de succès pour l'utilisateur
 *
 * @param string $message Message de succès
 */
function set_message_succes(string $message): void
{
    $_SESSION['message_succes'] = $message;
}

/**
 * Définit un message d'erreur pour l'utilisateur
 *
 * @param string $message Message d'erreur
 */
function set_message_erreur(string $message): void
{
    $_SESSION['message_erreur'] = $message;
}

/**
 * Obtient l'adresse IP du client
 *
 * @return string Adresse IP du client
 */
function getClientIP(): string
{
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];

            // Gérer les IPs multiples (dernière IP dans la liste)
            if (strpos($ip, ',') !== false) {
                $ip = trim(end(explode(',', $ip)));
            }

            // Valider l'IP
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return '127.0.0.1'; // IP par défaut
}

/**
 * Affiche une erreur à l'utilisateur
 *
 * @param string $message Message d'erreur
 * @param int $code Code HTTP (optionnel)
 */
function afficher_erreur(string $message, int $code = 500): void
{
    http_response_code($code);
    $data = ['message' => $message, 'code' => $code];
    require_once VIEWS_PATH . '/errors/error.php';
}

/**
 * Formate une date selon le format français
 *
 * @param string $date Date à formater (YYYY-MM-DD)
 * @param string $format Format de sortie (défaut: d/m/Y)
 * @return string Date formatée
 */
function formatDate(string $date, string $format = 'd/m/Y'): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }

    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return $date;
    }
}
{
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];

            // Gérer les IPs multiples (dernière IP dans la liste)
            if (strpos($ip, ',') !== false) {
                $ip = trim(end(explode(',', $ip)));
            }

            // Valider l'IP
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return '127.0.0.1'; // IP par défaut
}

/**
 * Log une erreur dans le fichier de logs
 *
 * @param string $message Message d'erreur
 * @param array $context Contexte supplémentaire
 * @return void
 */
function logError(string $message, array $context = []): void
{
    $log_file = LOGS_PATH . '/errors/' . date('Y-m-d') . '.log';

    // Créer le répertoire si nécessaire
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $user_id = $_SESSION['user_id'] ?? 'system';

    $log_entry = sprintf(
        "[%s] ERROR - IP: %s - User: %s - Message: %s",
        $timestamp,
        $ip,
        $user_id,
        $message
    );

    if (!empty($context)) {
        $log_entry .= " - Context: " . json_encode($context);
    }

    $log_entry .= "\n";

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Log une action utilisateur
 *
 * @param string $action Action effectuée
 * @param string $description Description détaillée
 * @param array $context Contexte supplémentaire
 * @return void
 */
function logAction(string $action, string $description = '', array $context = []): void
{
    $log_file = LOGS_PATH . '/actions/' . date('Y-m-d') . '.log';

    // Créer le répertoire si nécessaire
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $user_id = $_SESSION['user_id'] ?? 'system';

    $log_entry = sprintf(
        "[%s] ACTION - IP: %s - User: %s - Action: %s - Description: %s",
        $timestamp,
        $ip,
        $user_id,
        $action,
        $description
    );

    if (!empty($context)) {
        $log_entry .= " - Context: " . json_encode($context);
    }

    $log_entry .= "\n";

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Génère un token CSRF
 *
 * @return string Token CSRF
 */
function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valide un token CSRF
 *
 * @param string $token Token à valider
 * @return bool Token valide
 */
function validateCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Nettoie les données d'entrée
 *
 * @param mixed $data Données à nettoyer
 * @return mixed Données nettoyées
 */
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }

    if (is_string($data)) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    return $data;
}

/**
 * Construit une URL relative à l'application
 *
 * @param string $path Chemin relatif
 * @param array $params Paramètres GET
 * @return string URL complète
 */
function buildUrl(string $path, array $params = []): string
{
    $url = BASE_URL . ltrim($path, '/');

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

/**
 * Redirige vers une URL
 *
 * @param string $url URL de redirection
 * @param int $status_code Code de statut HTTP
 * @return void
 */
function redirect_custom(string $url, int $status_code = 302): void
{
    header("Location: $url", true, $status_code);
    exit;
}

/**
 * Formate une devise
 *
 * @param float $amount Montant
 * @param string $currency Devise
 * @return string Montant formaté
 */
function formatCurrency(float $amount, string $currency = 'FC'): string
{
    return number_format($amount, 0, ',', ' ') . ' ' . $currency;
}

/**
 * Vérifie si l'utilisateur est connecté
 *
 * @return bool Utilisateur connecté
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 *
 * @param string $role Rôle à vérifier
 * @return bool Utilisateur a le rôle
 */
function hasRole(string $role): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Vérifie si l'utilisateur a une permission
 *
 * @param string $permission Permission à vérifier
 * @return bool Utilisateur a la permission
 */
function hasPermission(string $permission): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    $user_permissions = $_SESSION['user_permissions'] ?? [];

    return in_array($permission, $user_permissions) || in_array('*', $user_permissions);
}

/**
 * Affiche une vue d'authentification avec le template approprié
 *
 * @param string $view Nom de la vue (ex: 'auth/login')
 * @param array $data Données à passer à la vue
 * @return void
 */
function renderAuth(string $view, array $data = []): void
{
    extract($data);
    // Convertir les traits d'union en underscores seulement dans le nom du fichier (après le dernier /)
    $last_slash_pos = strrpos($view, '/');
    if ($last_slash_pos !== false) {
        $path = substr($view, 0, $last_slash_pos + 1);
        $filename = substr($view, $last_slash_pos + 1);
        $filename = str_replace('-', '_', $filename);
        $view_file = VIEWS_PATH . '/' . $path . $filename . '.php';
    } else {
        $view_file = VIEWS_PATH . '/' . str_replace('-', '_', $view) . '.php';
    }

    if (file_exists(VIEWS_PATH . '/templates/auth_template.php')) {
        include VIEWS_PATH . '/templates/auth_template.php';
    } elseif (file_exists($view_file)) {
        include $view_file;
    } else {
        redirect(buildUrl('errors/404'));
    }
    exit;
}

/**
 * Affiche une vue d'erreur avec le template approprié
 *
 * @param string $view Nom de la vue (ex: 'errors/404')
 * @param array $data Données à passer à la vue
 * @return void
 */
function renderError(string $view, array $data = []): void
{
    extract($data);
    // Convertir les traits d'union en underscores seulement dans le nom du fichier (après le dernier /)
    $last_slash_pos = strrpos($view, '/');
    if ($last_slash_pos !== false) {
        $path = substr($view, 0, $last_slash_pos + 1);
        $filename = substr($view, $last_slash_pos + 1);
        $filename = str_replace('-', '_', $filename);
        $view_file = VIEWS_PATH . '/' . $path . $filename . '.php';
    } else {
        $view_file = VIEWS_PATH . '/' . str_replace('-', '_', $view) . '.php';
    }

    if (file_exists(VIEWS_PATH . '/templates/error_template.php')) {
        include VIEWS_PATH . '/templates/error_template.php';
    } elseif (file_exists($view_file)) {
        include $view_file;
    } else {
        redirect(buildUrl('errors/404'));
    }
    exit;
}

/**
 * Fonction globale pour rendre une vue
 */
function render($view, $data = [] , $use_layout = true) {
    // chemin de vue
    extract($data);
    // Convertir les traits d'union en underscores seulement dans le nom du fichier (après le dernier /)
    $last_slash_pos = strrpos($view, '/');
    if ($last_slash_pos !== false) {
        $path = substr($view, 0, $last_slash_pos + 1);
        $filename = substr($view, $last_slash_pos + 1);
        $filename = str_replace('-', '_', $filename);
        $view_file = VIEWS_PATH . '/' . $path . $filename . '.php';
    } else {
        $view_file = VIEWS_PATH . '/' . str_replace('-', '_', $view) . '.php';
    }

    if (!file_exists($view_file)) {
        redirect(buildUrl('errors/500'));
    }
   
     if ($use_layout) {
            // Charger le layout principal
            $layout_file = SRC_PATH . '/Views/templates/header.php';
            if (file_exists($layout_file)) {
                include $layout_file;
            }

            // Charger la vue
            include $view_file;

            // Charger le footer
            $footer_file = SRC_PATH . '/Views/templates/footer.php';
            if (file_exists($footer_file)) {
                include $footer_file;
            }
        } else {
            // Charger seulement la vue
            include $view_file;
        }
}

/**
 * Vérifie si la page actuelle est active
 */
function is_active($page_name) {
    $current = $_GET['page'] ?? 'dashboard';
    
    // Pour les pages parentes (ex: "eleves" active pour "eleves/admission")
    if (strpos($current, $page_name . '/') === 0) {
        return 'active';
    }
    
    // Comparaison exacte
    if ($current === $page_name) {
        return 'active';
    }
    
    // Pour les URLs avec ID (ex: "eleves/123")
    $parts = explode('/', $current);
    if (isset($parts[0]) && $parts[0] === $page_name) {
        return 'active';
    }
    
    return '';
}

?>