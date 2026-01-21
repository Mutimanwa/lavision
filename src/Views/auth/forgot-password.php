<?php
/**
 * Page de récupération de mot de passe
 * Vue d'authentification pour la réinitialisation du mot de passe
 */

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: ' . BASE_URL . '?page=dashboard');
    exit;
}

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
    <title>Mot de passe oublié - LaVision</title>

    <!-- Styles Bootstrap -->
    <link href="<?php echo ASSETS_URL; ?>/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/libs/fontawesome/css/all.min.css" rel="stylesheet">

    <!-- Styles personnalisés -->
    <link href="<?php echo ASSETS_URL; ?>/css/theme.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/user.min.css" rel="stylesheet">

    <style>
        .forgot-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .forgot-header {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .forgot-body {
            padding: 2rem;
        }

        .form-control:focus {
            border-color: #FF9800;
            box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
        }

        .btn-reset {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
        }

        .forgot-footer {
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
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <!-- En-tête -->
            <div class="forgot-header">
                <i class="fas fa-key fa-3x mb-3"></i>
                <h2 class="mb-0">Mot de passe oublié</h2>
                <p class="mb-0">Réinitialisez votre mot de passe</p>
            </div>

            <!-- Corps du formulaire -->
            <div class="forgot-body">
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
                        Entrez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.
                    </p>
                </div>

                <form method="POST" action="<?php echo BASE_URL; ?>/auth/forgot-password" id="forgotForm">
                    <!-- Jeton CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Champ Email -->
                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Adresse email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   required
                                   autocomplete="email"
                                   placeholder="votre.email@exemple.com">
                        </div>
                    </div>

                    <!-- Bouton de réinitialisation -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-warning btn-reset" id="btnReset">
                            <i class="fas fa-paper-plane me-2"></i>
                            Envoyer le lien de réinitialisation
                        </button>
                    </div>

                    <!-- Lien de retour -->
                    <div class="text-center">
                        <a href="<?php echo BASE_URL; ?>/auth/login" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>
                            Retour à la connexion
                        </a>
                    </div>
                </form>
            </div>

            <!-- Pied de page -->
            <div class="forgot-footer">
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
            // Validation du formulaire
            $('#forgotForm').submit(function(e) {
                const email = $('#email').val().trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!email) {
                    e.preventDefault();
                    alert('Veuillez saisir votre adresse email.');
                    $('#email').focus();
                    return false;
                }

                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Veuillez saisir une adresse email valide.');
                    $('#email').focus();
                    return false;
                }

                // Désactiver le bouton pendant la soumission
                $('#btnReset').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...');
            });

            // Animation des messages d'alerte
            $('.alert').hide().fadeIn(500);

            // Focus automatique sur le champ email
            $('#email').focus();
        });
    </script>
</body>
</html>