<?php
/**
 * Page d'écran verrouillé
 * Vue d'authentification pour déverrouiller la session
 */

// Vérifier si l'utilisateur n'est pas connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

// Récupérer les informations de l'utilisateur
$user_name = $_SESSION['user_name'] ?? 'Utilisateur';
$user_photo = $_SESSION['user_photo'] ?? '';

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Écran verrouillé - LaVision</title>

    <!-- Styles Bootstrap -->
    <link href="<?php echo ASSETS_URL; ?>/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/libs/fontawesome/css/all.min.css" rel="stylesheet">

    <!-- Styles personnalisés -->
    <link href="<?php echo ASSETS_URL; ?>/css/theme.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/user.min.css" rel="stylesheet">

    <style>
        .lock-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lock-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .lock-header {
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
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
            color: #9C27B0;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .lock-body {
            padding: 2rem;
        }

        .form-control:focus {
            border-color: #9C27B0;
            box-shadow: 0 0 0 0.2rem rgba(156, 39, 176, 0.25);
        }

        .btn-unlock {
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-unlock:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(156, 39, 176, 0.4);
        }

        .lock-footer {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .input-group-text {
            background: #f8f9fa;
            border-color: #dee2e6;
        }

        .session-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="lock-container">
        <div class="lock-card">
            <!-- En-tête -->
            <div class="lock-header">
                <div class="user-avatar">
                    <?php if ($user_photo): ?>
                        <img src="<?php echo ASSETS_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user_photo); ?>"
                             alt="Photo de profil" class="w-100 h-100 rounded-circle object-fit-cover">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <h3 class="mb-1"><?php echo htmlspecialchars($user_name); ?></h3>
                <p class="mb-0">Session verrouillée</p>
            </div>

            <!-- Corps du formulaire -->
            <div class="lock-body">
                <div class="session-info text-center">
                    <i class="fas fa-clock me-2"></i>
                    Session verrouillée le <?php echo date('d/m/Y à H:i'); ?>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mb-4">
                    <p class="text-muted">
                        Entrez votre mot de passe pour déverrouiller la session.
                    </p>
                </div>

                <form method="POST" action="<?php echo BASE_URL; ?>/auth/unlock" id="unlockForm">
                    <!-- Jeton CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Champ Mot de passe -->
                    <div class="mb-4">
                        <label for="mot_de_passe" class="form-label">
                            <i class="fas fa-lock me-2"></i>Mot de passe
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password"
                                   class="form-control"
                                   id="mot_de_passe"
                                   name="mot_de_passe"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Votre mot de passe">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Bouton de déverrouillage -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-unlock" id="btnUnlock">
                            <i class="fas fa-unlock me-2"></i>
                            Déverrouiller
                        </button>
                    </div>

                    <!-- Liens alternatifs -->
                    <div class="text-center">
                        <a href="<?php echo BASE_URL; ?>/auth/logout" class="text-decoration-none me-3">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Changer d'utilisateur
                        </a>
                        <a href="<?php echo BASE_URL; ?>/auth/forgot-password" class="text-decoration-none">
                            <i class="fas fa-key me-1"></i>
                            Mot de passe oublié
                        </a>
                    </div>
                </form>
            </div>

            <!-- Pied de page -->
            <div class="lock-footer">
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
            // Toggle visibilité mot de passe
            $('#togglePassword').click(function() {
                const passwordField = $('#mot_de_passe');
                const icon = $(this).find('i');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Validation du formulaire
            $('#unlockForm').submit(function(e) {
                const motDePasse = $('#mot_de_passe').val().trim();

                if (!motDePasse) {
                    e.preventDefault();
                    alert('Veuillez saisir votre mot de passe.');
                    $('#mot_de_passe').focus();
                    return false;
                }

                // Désactiver le bouton pendant la soumission
                $('#btnUnlock').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Déverrouillage...');
            });

            // Animation des messages d'alerte
            $('.alert').hide().fadeIn(500);

            // Focus automatique sur le champ mot de passe
            $('#mot_de_passe').focus();

            // Gestion de l'inactivité (verrouillage automatique après 30 minutes)
            let inactivityTimer;

            function resetInactivityTimer() {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(function() {
                    // Le verrouillage est déjà actif, pas besoin d'action supplémentaire
                }, 30 * 60 * 1000); // 30 minutes
            }

            // Réinitialiser le timer à chaque interaction
            $(document).on('mousemove keypress scroll', resetInactivityTimer);
            resetInactivityTimer();
        });
    </script>
</body>
</html>