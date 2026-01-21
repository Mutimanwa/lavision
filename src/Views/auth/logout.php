<?php
/**
 * Page de déconnexion
 * Vue d'authentification pour confirmer la déconnexion
 */

// Vérifier si l'utilisateur n'est pas connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

// Récupérer les informations de l'utilisateur
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$user_photo = $_SESSION['user_photo'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - LaVision</title>

    <!-- Styles Bootstrap -->
    <link href="<?php echo ASSETS_URL; ?>/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/libs/fontawesome/css/all.min.css" rel="stylesheet">

    <!-- Styles personnalisés -->
    <link href="<?php echo ASSETS_URL; ?>/css/theme.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/user.min.css" rel="stylesheet">

    <style>
        .logout-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .logout-header {
            background: linear-gradient(135deg, #FF5722 0%, #D84315 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: #FF5722;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .logout-body {
            padding: 2rem;
        }

        .btn-logout {
            background: linear-gradient(135deg, #FF5722 0%, #D84315 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 87, 34, 0.4);
        }

        .btn-cancel {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .logout-footer {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .session-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .session-summary h6 {
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .session-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .session-item:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-card">
            <!-- En-tête -->
            <div class="logout-header">
                <div class="user-avatar">
                    <?php if ($user_photo): ?>
                        <img src="<?php echo ASSETS_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user_photo); ?>"
                             alt="Photo de profil" class="w-100 h-100 rounded-circle object-fit-cover">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <h3 class="mb-1"><?php echo htmlspecialchars($user_name); ?></h3>
                <p class="mb-0">Confirmer la déconnexion</p>
            </div>

            <!-- Corps -->
            <div class="logout-body">
                <div class="text-center mb-4">
                    <p class="text-muted">
                        Êtes-vous sûr de vouloir vous déconnecter du système LaVision ?
                    </p>
                </div>

                <!-- Résumé de session -->
                <div class="session-summary">
                    <h6><i class="fas fa-info-circle me-2"></i>Résumé de session</h6>
                    <div class="session-item">
                        <span>Utilisateur:</span>
                        <strong><?php echo htmlspecialchars($user_name); ?></strong>
                    </div>
                    <div class="session-item">
                        <span>Connexion:</span>
                        <span><?php echo date('d/m/Y H:i', $_SESSION['login_time'] ?? time()); ?></span>
                    </div>
                    <div class="session-item">
                        <span>Durée:</span>
                        <span id="sessionDuration">Calcul en cours...</span>
                    </div>
                    <div class="session-item">
                        <span>Navigateur:</span>
                        <span><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu'); ?></span>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-cancel w-100">
                            <i class="fas fa-times me-2"></i>
                            Annuler
                        </a>
                    </div>
                    <div class="col-6">
                        <form method="POST" action="<?php echo BASE_URL; ?>/auth/logout" id="logoutForm" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <button type="submit" class="btn btn-danger btn-logout w-100" id="btnLogout">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Options supplémentaires -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <a href="<?php echo BASE_URL; ?>/auth/lock-screen" class="text-decoration-none">
                            <i class="fas fa-lock me-1"></i>
                            Verrouiller l'écran
                        </a>
                        <span class="mx-2">•</span>
                        <a href="<?php echo BASE_URL; ?>/utilisateur/profile" class="text-decoration-none">
                            <i class="fas fa-user me-1"></i>
                            Mon profil
                        </a>
                    </small>
                </div>
            </div>

            <!-- Pied de page -->
            <div class="logout-footer">
                <small class="text-muted">
                    © <?php echo date('Y'); ?> LaVision - Système de Gestion Scolaire
                </small>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo ASSETS_URL; ?>/libs/jquery/jquery.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Calcul de la durée de session
            function updateSessionDuration() {
                const loginTime = <?php echo $_SESSION['login_time'] ?? time(); ?>;
                const now = Math.floor(Date.now() / 1000);
                const duration = now - loginTime;

                const hours = Math.floor(duration / 3600);
                const minutes = Math.floor((duration % 3600) / 60);

                let durationText = '';
                if (hours > 0) {
                    durationText += hours + 'h ';
                }
                durationText += minutes + 'min';

                $('#sessionDuration').text(durationText);
            }

            // Mettre à jour la durée immédiatement et toutes les minutes
            updateSessionDuration();
            setInterval(updateSessionDuration, 60000);

            // Gestion du formulaire de déconnexion
            $('#logoutForm').submit(function(e) {
                // Désactiver le bouton pendant la soumission
                $('#btnLogout').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Déconnexion...');

                // Animation de sortie
                $('.logout-card').fadeOut(500, function() {
                    // La soumission continue normalement
                });
            });

            // Gestion du bouton annuler avec animation
            $('.btn-cancel').click(function(e) {
                e.preventDefault();
                $('.logout-card').fadeOut(300, function() {
                    window.location.href = $(this).attr('href');
                });
            });

            // Animation d'entrée
            $('.logout-card').hide().fadeIn(500);
        });
    </script>
</body>
</html>