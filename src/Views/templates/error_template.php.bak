<?php
/**
 * Template d'erreur générique
 * LaVision - Système de gestion scolaire
 *
 * Ce template affiche les erreurs de manière conviviale
 * avec des informations de débogage si activé.
 */

// Définir les valeurs par défaut
$error_code = $error_code ?? 500;
$error_title = $error_title ?? 'Erreur interne du serveur';
$error_message = $error_message ?? 'Une erreur inattendue s\'est produite.';
$error_details = $error_details ?? null;
$show_debug = DEBUG_MODE ?? false;
$back_url = $back_url ?? BASE_URL . '/dashboard';
$back_text = $back_text ?? 'Retour au tableau de bord';

// Définir les icônes selon le code d'erreur
$error_icons = [
    400 => 'fas fa-exclamation-triangle',
    401 => 'fas fa-lock',
    403 => 'fas fa-ban',
    404 => 'fas fa-search',
    500 => 'fas fa-server',
    503 => 'fas fa-tools'
];

$error_icon = $error_icons[$error_code] ?? 'fas fa-exclamation-circle';

// Définir les couleurs selon le code d'erreur
$error_colors = [
    400 => 'warning',
    401 => 'danger',
    403 => 'danger',
    404 => 'info',
    500 => 'danger',
    503 => 'warning'
];

$error_color = $error_colors[$error_code] ?? 'secondary';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $error_code; ?> - <?php echo $error_title; ?> | LaVision</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_PATH; ?>/img/favicons/favicon.ico">

    <!-- Bootstrap CSS -->
    <link href="<?php echo ASSETS_PATH; ?>/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="<?php echo ASSETS_PATH; ?>/libs/fontawesome/css/all.min.css" rel="stylesheet">

    <!-- CSS personnalisé -->
    <link href="<?php echo ASSETS_PATH; ?>/css/theme.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_PATH; ?>/css/user.min.css" rel="stylesheet">

    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .error-header {
            background: linear-gradient(135deg, var(--bs-<?php echo $error_color; ?>) 0%, var(--bs-<?php echo $error_color; ?>-light) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .error-code {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .error-title {
            font-size: 1.25rem;
            margin-bottom: 0;
        }

        .error-body {
            padding: 2rem;
        }

        .error-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .error-actions {
            text-align: center;
        }

        .error-actions .btn {
            margin: 0 0.5rem 0.5rem 0;
            min-width: 120px;
        }

        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            margin-top: 1.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }

        .debug-title {
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 0.5rem;
        }

        .debug-content {
            max-height: 200px;
            overflow-y: auto;
        }

        @media (max-width: 576px) {
            .error-card {
                margin: 1rem;
            }

            .error-header {
                padding: 1.5rem;
            }

            .error-body {
                padding: 1.5rem;
            }

            .error-actions .btn {
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            <!-- En-tête de l'erreur -->
            <div class="error-header">
                <div class="error-icon">
                    <i class="<?php echo $error_icon; ?>"></i>
                </div>
                <div class="error-code"><?php echo $error_code; ?></div>
                <div class="error-title"><?php echo htmlspecialchars($error_title); ?></div>
            </div>

            <!-- Corps de l'erreur -->
            <div class="error-body">
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>

                <!-- Actions disponibles -->
                <div class="error-actions">
                    <a href="<?php echo $back_url; ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i><?php echo htmlspecialchars($back_text); ?>
                    </a>

                    <button onclick="window.history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-2"></i>Page précédente
                    </button>

                    <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-info">
                        <i class="fas fa-home me-2"></i>Accueil
                    </a>
                </div>

                <!-- Informations de débogage (si activé) -->
                <?php if ($show_debug && $error_details): ?>
                    <div class="debug-info">
                        <div class="debug-title">
                            <i class="fas fa-bug me-2"></i>Informations de débogage
                        </div>
                        <div class="debug-content">
                            <pre><?php echo htmlspecialchars(print_r($error_details, true)); ?></pre>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Informations système (si en mode debug) -->
                <?php if ($show_debug): ?>
                    <div class="debug-info">
                        <div class="debug-title">
                            <i class="fas fa-info-circle me-2"></i>Informations système
                        </div>
                        <div class="debug-content">
                            <strong>URL demandée:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A'); ?><br>
                            <strong>Méthode HTTP:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A'); ?><br>
                            <strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                            <strong>Utilisateur:</strong> <?php echo htmlspecialchars($_SESSION['user_nom'] ?? 'Non connecté'); ?><br>
                            <strong>IP:</strong> <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A'); ?><br>
                            <strong>User Agent:</strong> <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo ASSETS_PATH; ?>/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Script de gestion d'erreur -->
    <script>
        // Gestion des erreurs JavaScript
        window.addEventListener('error', function(e) {
            console.error('Erreur JavaScript sur la page d\'erreur:', e.error);
        });

        // Fonction pour recharger la page
        function reloadPage() {
            window.location.reload();
        }

        // Auto-rechargement après un délai (optionnel)
        <?php if ($error_code >= 500): ?>
            setTimeout(function() {
                // Ne pas auto-recharger pour les erreurs 5xx
            }, 30000);
        <?php endif; ?>

        // Logging de l'erreur (si service de monitoring configuré)
        if (typeof logError === 'function') {
            logError({
                code: <?php echo $error_code; ?>,
                message: '<?php echo addslashes($error_message); ?>',
                url: '<?php echo addslashes($_SERVER['REQUEST_URI'] ?? ''); ?>',
                timestamp: '<?php echo date('c'); ?>'
            });
        }
    </script>
</body>
</html>