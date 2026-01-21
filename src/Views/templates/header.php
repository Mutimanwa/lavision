<?php
/**
 * Template d'en-tête principal de l'application LaVision
 * Inclut les balises HTML de base, CSS et JavaScript communs
 * Design responsive avec Bootstrap 5 et thème personnalisé
 * Version: 2.0.0 - Refactorisé pour scalabilité
 * Date: 20 janvier 2026
 */

// Définition des constantes de chemin si non définies
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', ASSETS_URL);
}

if (!defined('CSS_PATH')) {
    define('CSS_PATH', ASSETS_PATH . 'css/');
}

if (!defined('JS_PATH')) {
    define('JS_PATH', ASSETS_PATH . 'js/');
}

if (!defined('LIBS_PATH')) {
    define('LIBS_PATH', ASSETS_PATH . 'libs/');
}

// Titre de la page (défini par le contrôleur)
$titre_page = $titre_page ?? 'LaVision - Système de Gestion Scolaire';

// Utilisateur connecté
$utilisateur = $_SESSION['utilisateur_nom'] ?? 'Utilisateur';
$role_utilisateur = $_SESSION['utilisateur_role'] ?? '';

// Token CSRF
$csrf_token = generer_token_csrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Système de gestion scolaire LaVision - Gestion complète des établissements éducatifs">
    <meta name="author" content="LaVision Team">
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">

    <!-- Titre de la page -->
    <title><?php echo htmlspecialchars($titre_page); ?> - LaVision</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_PATH; ?>/img/favicons/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo ASSETS_PATH; ?>/img/favicons/apple-touch-icon.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Theme CSS personnalisé -->
    <link href="<?php echo CSS_PATH; ?>/theme.min.css" rel="stylesheet">
    <link href="<?php echo CSS_PATH; ?>/user.min.css" rel="stylesheet">

    <!-- Styles spécifiques à la page -->
    <?php if (isset($styles_supplementaires)): ?>
        <?php foreach ($styles_supplementaires as $style): ?>
            <link href="<?php echo $style; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Scripts communs -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Configuration JavaScript -->
    <script>
        // Configuration globale
        window.LAVISION_CONFIG = {
            baseUrl: '<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/'); ?>',
            csrfToken: '<?php echo $csrf_token; ?>',
            utilisateur: {
                nom: '<?php echo addslashes($utilisateur); ?>',
                role: '<?php echo $role_utilisateur; ?>'
            },
            lang: 'fr'
        };

        // Fonction utilitaire pour les requêtes AJAX
        window.ajaxRequest = function(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': window.LAVISION_CONFIG.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            };

            const config = { ...defaults, ...options };

            if (config.data && typeof config.data === 'object') {
                config.body = JSON.stringify(config.data);
            }

            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.json();
                });
        };

        // Fonction pour afficher les notifications
        window.showNotification = function(message, type = 'info', duration = 5000) {
            const alertClass = `alert-${type}`;
            const iconClass = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-triangle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            }[type] || 'fa-info-circle';

            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas ${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            if (duration > 0) {
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, duration);
            }
        };
    </script>
</head>
<body class="bg-light">
    <!-- Spinner de chargement -->
    <div id="loading-spinner" class="d-none">
        <div class="spinner-overlay position-fixed w-100 h-100 d-flex justify-content-center align-items-center"
             style="background: rgba(255,255,255,0.8); z-index: 9999;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    </div>

    <!-- Conteneur principal -->
    <div id="main-container">















