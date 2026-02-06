<?php
/**
 * Contrôleur de base
 * LaVision - Système de gestion scolaire
 *
 * Ce contrôleur fournit les fonctionnalités communes à tous les contrôleurs
 * de l'application (chargement des vues, gestion des données, etc.)
 */
require_once __DIR__ . "/AcademiqueController.php";
class BaseController {
    /**
     * Instance de la base de données
     * @var PDO
     */
    protected $db;

    /**
     * Données à passer à la vue
     * @var array
     */
    protected $data = [];

    /**
     * Constructeur
     */
    public function __construct() {
        // Initialiser la connexion à la base de données
        $this->db = get_db_connection();

        // Initialiser les données communes
        $this->initCommonData();
    }

    /**
     * Initialiser les données communes à toutes les vues
     */
    protected function initCommonData() {
        // Informations de l'utilisateur connecté
        if (isset($_SESSION['user_id'])) {
            $this->data['current_user'] = [
                'id' => $_SESSION['user_id'],
                'nom' => $_SESSION['user_nom'] ?? '',
                'prenom' => $_SESSION['user_prenom'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'role' => $_SESSION['utilisateur_role'] ?? 'eleve',
                'permissions' => $_SESSION['user_permissions'] ?? []
            ];
        }

        // Informations système
        $this->data['system_info'] = [
            'version' => APP_VERSION ?? '1.0.0',
            'annee_scolaire' => get_annee_scolaire_active(),
            'date_today' => date('Y-m-d'),
            'datetime_now' => date('Y-m-d H:i:s')
        ];

        // URLs de base
        $this->data['base_url'] = BASE_URL;
        $this->data['assets_url'] = ASSETS_PATH;
        $this->data['css_url'] = CSS_PATH;
        $this->data['js_url'] = JS_PATH;
        $this->data['img_url'] = IMG_PATH;

        // Configuration de pagination par défaut
        $this->data['pagination'] = [
            'page' => isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1,
            'per_page' => 25,
            'offset' => 0
        ];

        // Filtres de recherche par défaut
        $this->data['filters'] = [
            'search' => $_GET['search'] ?? '',
            'sort' => $_GET['sort'] ?? 'id',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Messages flash
        $this->loadFlashMessages();
    }

    /**
     * Charger les messages flash depuis la session
     */
    protected function loadFlashMessages() {
        $flash_messages = [
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null,
            'warning' => $_SESSION['flash_warning'] ?? null,
            'info' => $_SESSION['flash_info'] ?? null
        ];

        // Nettoyer les messages flash après les avoir chargés
        foreach ($flash_messages as $type => $message) {
            if ($message) {
                unset($_SESSION["flash_{$type}"]);
            }
        }

        $this->data['flash_messages'] = array_filter($flash_messages);
    }

    /**
     * Définir un message flash
     */
    protected function setFlashMessage($type, $message) {
        $_SESSION["flash_{$type}"] = $message;
    }

    /**
     * Rediriger vers une URL
     */
    protected function redirect($url, $message = null, $type = 'info') {
        if ($message) {
            $this->setFlashMessage($type, $message);
        }
        header("Location: $url");
        exit;
    }

    /**
     * Rediriger vers une route
     */
    protected function redirectToRoute($module, $action = 'index', $params = [], $message = null, $type = 'info') {
        $url = buildUrl($module, $action, $params);
        $this->redirect($url, $message, $type);
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirectToRoute('auth', 'login', [], 'Veuillez vous connecter pour accéder à cette page.', 'warning');
        }
    }

    /**
     * Vérifier les permissions
     */
    protected function requirePermission($permission) {
        $this->requireAuth();

        $user_permissions = $_SESSION['user_permissions'] ?? [];

        if (!in_array($permission, $user_permissions)) {
            $this->showError(403, 'Accès refusé', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
        }
    }

    /**
     * Vérifier le rôle
     */
    protected function requireRole($role) {
        $this->requireAuth();

        $user_role = $_SESSION['utilisateur_role'] ?? 'eleve';

        if ($user_role !== $role) {
            $this->showError(403, 'Accès refusé', 'Vous n\'avez pas le rôle nécessaire pour accéder à cette page.');
        }
    }

    /**
     * Afficher une erreur
     */
    protected function showError($code = 500, $title = 'Erreur', $message = 'Une erreur s\'est produite.', $details = null) {
        http_response_code($code);

        $error_data = [
            'error_code' => $code,
            'error_title' => $title,
            'error_message' => $message,
            'error_details' => DEBUG_MODE ? $details : null,
            'show_debug' => DEBUG_MODE
        ];

        $this->render('templates/error_template', $error_data, false);
        exit;
    }

    /**
     * Rendre une vue
     */
    protected function render($view, $data = [], $use_layout = true) {
        // Fusionner les données
        $view_data = array_merge($this->data, $data);

        // Extraire les variables pour la vue
        extract($view_data);

        // Chemin de la vue
        $view_file = SRC_PATH . '/Views/' . $view . '.php';

        if (!file_exists($view_file)) {
            $this->showError(500, 'Erreur de vue', "La vue '$view' n'existe pas.");
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
     * Rendre une vue partielle (sans layout)
     */
    protected function renderPartial($view, $data = []) {
        $this->render($view, $data, false);
    }

    /**
     * Rendre une vue d'authentification
     */
    protected function renderAuth($view, $data = []) {
        // Fusionner les données
        $view_data = array_merge($this->data, $data);

        // Extraire les variables pour la vue
        extract($view_data);

        // Chemin du template d'authentification
        $auth_template = SRC_PATH . '/Views/templates/auth_template.php';

        if (!file_exists($auth_template)) {
            $this->showError(500, 'Erreur de template', "Le template d'authentification n'existe pas.");
        }

        // Charger le template d'authentification
        include $auth_template;
    }

    /**
     * Retourner une réponse JSON
     */
    protected function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Valider les données d'un formulaire
     */
    protected function validateForm($rules, $data) {
        $errors = [];
        $sanitized_data = [];

        foreach ($rules as $field => $field_rules) {
            $value = $data[$field] ?? null;
            $field_errors = [];

            // Règles de validation
            foreach ($field_rules as $rule => $rule_value) {
                switch ($rule) {
                    case 'required':
                        if ($rule_value && (is_null($value) || $value === '')) {
                            $field_errors[] = "Le champ est obligatoire.";
                        }
                        break;

                    case 'email':
                        if ($rule_value && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $field_errors[] = "L'adresse email n'est pas valide.";
                        }
                        break;

                    case 'min_length':
                        if ($value && strlen($value) < $rule_value) {
                            $field_errors[] = "Le champ doit contenir au moins $rule_value caractères.";
                        }
                        break;

                    case 'max_length':
                        if ($value && strlen($value) > $rule_value) {
                            $field_errors[] = "Le champ ne peut pas contenir plus de $rule_value caractères.";
                        }
                        break;

                    case 'numeric':
                        if ($rule_value && $value && !is_numeric($value)) {
                            $field_errors[] = "Le champ doit être numérique.";
                        }
                        break;

                    case 'date':
                        if ($rule_value && $value) {
                            $date = DateTime::createFromFormat('Y-m-d', $value);
                            if (!$date || $date->format('Y-m-d') !== $value) {
                                $field_errors[] = "La date n'est pas valide.";
                            }
                        }
                        break;

                    case 'in':
                        if ($rule_value && $value && !in_array($value, $rule_value)) {
                            $field_errors[] = "La valeur n'est pas dans la liste des valeurs autorisées.";
                        }
                        break;
                }
            }

            // Ajouter les erreurs si elles existent
            if (!empty($field_errors)) {
                $errors[$field] = $field_errors;
            }

            // Nettoyer et sanitiser la valeur
            $sanitized_data[$field] = $this->sanitizeInput($value);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized_data
        ];
    }

    /**
     * Sanitiser une entrée utilisateur
     */
    protected function sanitizeInput($value) {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    /**
     * Gérer l'upload de fichiers
     */
    protected function handleFileUpload($field_name, $allowed_types = [], $max_size = null, $upload_dir = null) {
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Aucun fichier uploadé ou erreur lors de l\'upload.'];
        }

        $file = $_FILES[$field_name];

        // Vérifier la taille du fichier
        if ($max_size && $file['size'] > $max_size) {
            return ['success' => false, 'error' => 'Le fichier est trop volumineux.'];
        }

        // Vérifier le type de fichier
        if (!empty($allowed_types)) {
            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                return ['success' => false, 'error' => 'Type de fichier non autorisé.'];
            }
        }

        // Générer un nom de fichier unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;

        // Déterminer le répertoire d'upload
        $upload_dir = $upload_dir ?: UPLOADS_PATH . '/temp';

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filepath = $upload_dir . '/' . $filename;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type']
            ];
        } else {
            return ['success' => false, 'error' => 'Erreur lors de la sauvegarde du fichier.'];
        }
    }

    /**
     * Journaliser une action
     */
    protected function logAction($action, $details = '', $module = null) {
        $user_id = $_SESSION['user_id'] ?? null;
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        logAction($action, $details,["user_id" => $user_id , "Addresse ip" =>  $user_ip , "User agent" => $user_agent , "Module" =>  $module]);
    }

    /**
     * Obtenir les paramètres de pagination
     */
    protected function getPaginationParams($total_items, $per_page = null) {
        $per_page = $per_page ?: $this->data['pagination']['per_page'];
        $current_page = $this->data['pagination']['page'];

        $total_pages = ceil($total_items / $per_page);
        $offset = ($current_page - 1) * $per_page;

        return [
            'current_page' => $current_page,
            'per_page' => $per_page,
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'offset' => $offset,
            'has_previous' => $current_page > 1,
            'has_next' => $current_page < $total_pages,
            'previous_page' => $current_page > 1 ? $current_page - 1 : null,
            'next_page' => $current_page < $total_pages ? $current_page + 1 : null
        ];
    }

    /**
     * Générer les liens de pagination
     */
    protected function generatePaginationLinks($pagination) {
        $links = [];

        // Lien précédent
        if ($pagination['has_previous']) {
            $links[] = [
                'text' => 'Précédent',
                'url' => $this->buildUrlWithParams(['page' => $pagination['previous_page']]),
                'active' => false,
                'disabled' => false
            ];
        }

        // Pages numérotées
        $start_page = max(1, $pagination['current_page'] - 2);
        $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);

        // Page 1 si nécessaire
        if ($start_page > 1) {
            $links[] = [
                'text' => '1',
                'url' => $this->buildUrlWithParams(['page' => 1]),
                'active' => false,
                'disabled' => false
            ];

            if ($start_page > 2) {
                $links[] = [
                    'text' => '...',
                    'url' => null,
                    'active' => false,
                    'disabled' => true
                ];
            }
        }

        // Pages autour de la page actuelle
        for ($i = $start_page; $i <= $end_page; $i++) {
            $links[] = [
                'text' => $i,
                'url' => $this->buildUrlWithParams(['page' => $i]),
                'active' => $i == $pagination['current_page'],
                'disabled' => false
            ];
        }

        // Dernière page si nécessaire
        if ($end_page < $pagination['total_pages']) {
            if ($end_page < $pagination['total_pages'] - 1) {
                $links[] = [
                    'text' => '...',
                    'url' => null,
                    'active' => false,
                    'disabled' => true
                ];
            }

            $links[] = [
                'text' => $pagination['total_pages'],
                'url' => $this->buildUrlWithParams(['page' => $pagination['total_pages']]),
                'active' => false,
                'disabled' => false
            ];
        }

        // Lien suivant
        if ($pagination['has_next']) {
            $links[] = [
                'text' => 'Suivant',
                'url' => $this->buildUrlWithParams(['page' => $pagination['next_page']]),
                'active' => false,
                'disabled' => false
            ];
        }

        return $links;
    }

    /**
     * Construire une URL avec des paramètres
     */
    protected function buildUrlWithParams($params = []) {
        $query_params = $_GET;

        foreach ($params as $key => $value) {
            if ($value === null) {
                unset($query_params[$key]);
            } else {
                $query_params[$key] = $value;
            }
        }

        $query_string = http_build_query($query_params);
        return $_SERVER['PHP_SELF'] . ($query_string ? '?' . $query_string : '');
    }

    /**
     * Méthode magique pour accéder aux propriétés
     */
    public function __get($name) {
        return $this->data[$name] ?? null;
    }

    /**
     * Méthode magique pour définir les propriétés
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }
}