<?php
/**
 * Page de réinitialisation du mot de passe
 * Vue d'authentification pour définir un nouveau mot de passe
 */

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: ' . BASE_URL . '?page=dashboard');
    exit;
}

// Récupérer le token depuis l'URL
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);

// Vérifier si le token est valide
$token_valid = false;
if ($token && $email) {
    // Ici nous vérifierions la validité du token avec la base de données
    // Pour l'instant, on suppose qu'il est valide
    $token_valid = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe - LaVision</title>

    <!-- Styles Bootstrap -->
    <link href="<?php echo ASSETS_URL; ?>/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/libs/fontawesome/css/all.min.css" rel="stylesheet">

    <!-- Styles personnalisés -->
    <link href="<?php echo ASSETS_URL; ?>/css/theme.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/user.min.css" rel="stylesheet">

    <style>
        .reset-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .reset-header {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .reset-body {
            padding: 2rem;
        }

        .form-control:focus {
            border-color: #2196F3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }

        .btn-reset {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
        }

        .reset-footer {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-meter {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <!-- En-tête -->
            <div class="reset-header">
                <i class="fas fa-unlock-alt fa-3x mb-3"></i>
                <h2 class="mb-0">Nouveau mot de passe</h2>
                <p class="mb-0">Définissez votre nouveau mot de passe</p>
            </div>

            <!-- Corps du formulaire -->
            <div class="reset-body">
                <?php if (!$token_valid): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Le lien de réinitialisation est invalide ou a expiré.
                        <br><a href="<?php echo BASE_URL; ?>/auth/forgot-password">Demander un nouveau lien</a>
                    </div>
                <?php else: ?>

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

                    <form method="POST" action="<?php echo BASE_URL; ?>/auth/reset-password" id="resetForm">
                        <!-- Jeton CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                        <!-- Champ Nouveau mot de passe -->
                        <div class="mb-3">
                            <label for="nouveau_mot_de_passe" class="form-label">
                                <i class="fas fa-lock me-2"></i>Nouveau mot de passe
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="nouveau_mot_de_passe"
                                       name="nouveau_mot_de_passe"
                                       required
                                       minlength="8"
                                       placeholder="Minimum 8 caractères">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-meter">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <small class="text-muted" id="strengthText">Force du mot de passe</small>
                            </div>
                        </div>

                        <!-- Champ Confirmation -->
                        <div class="mb-4">
                            <label for="confirmer_mot_de_passe" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="confirmer_mot_de_passe"
                                       name="confirmer_mot_de_passe"
                                       required
                                       minlength="8"
                                       placeholder="Répétez le mot de passe">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword2">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="form-text"></div>
                        </div>

                        <!-- Bouton de réinitialisation -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-reset" id="btnReset">
                                <i class="fas fa-save me-2"></i>
                                Réinitialiser le mot de passe
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

                <?php endif; ?>
            </div>

            <!-- Pied de page -->
            <div class="reset-footer">
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
            $('#togglePassword1, #togglePassword2').click(function() {
                const input = $(this).siblings('input');
                const icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Vérification de la force du mot de passe
            $('#nouveau_mot_de_passe').on('input', function() {
                const password = $(this).val();
                const strength = checkPasswordStrength(password);
                updateStrengthIndicator(strength);
            });

            // Vérification de la correspondance des mots de passe
            $('#confirmer_mot_de_passe').on('input', function() {
                const password1 = $('#nouveau_mot_de_passe').val();
                const password2 = $(this).val();
                const matchDiv = $('#passwordMatch');

                if (password2.length > 0) {
                    if (password1 === password2) {
                        matchDiv.html('<i class="fas fa-check text-success"></i> Les mots de passe correspondent').removeClass('text-danger').addClass('text-success');
                    } else {
                        matchDiv.html('<i class="fas fa-times text-danger"></i> Les mots de passe ne correspondent pas').removeClass('text-success').addClass('text-danger');
                    }
                } else {
                    matchDiv.html('');
                }
            });

            // Validation du formulaire
            $('#resetForm').submit(function(e) {
                const password1 = $('#nouveau_mot_de_passe').val();
                const password2 = $('#confirmer_mot_de_passe').val();

                if (password1.length < 8) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 8 caractères.');
                    $('#nouveau_mot_de_passe').focus();
                    return false;
                }

                if (password1 !== password2) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                    $('#confirmer_mot_de_passe').focus();
                    return false;
                }

                const strength = checkPasswordStrength(password1);
                if (strength < 2) {
                    e.preventDefault();
                    alert('Le mot de passe est trop faible. Veuillez choisir un mot de passe plus fort.');
                    return false;
                }

                // Désactiver le bouton pendant la soumission
                $('#btnReset').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Réinitialisation...');
            });

            // Animation des messages d'alerte
            $('.alert').hide().fadeIn(500);
        });

        // Fonction de vérification de la force du mot de passe
        function checkPasswordStrength(password) {
            let strength = 0;

            // Longueur
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;

            // Caractères spéciaux
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

            // Chiffres
            if (/\d/.test(password)) strength++;

            // Majuscules et minuscules
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;

            return strength;
        }

        // Mise à jour de l'indicateur de force
        function updateStrengthIndicator(strength) {
            const fill = $('#strengthFill');
            const text = $('#strengthText');

            fill.removeClass('strength-weak strength-medium strength-strong');

            if (strength <= 2) {
                fill.addClass('strength-weak');
                text.text('Faible').removeClass('text-success text-warning').addClass('text-danger');
            } else if (strength <= 4) {
                fill.addClass('strength-medium');
                text.text('Moyen').removeClass('text-success text-danger').addClass('text-warning');
            } else {
                fill.addClass('strength-strong');
                text.text('Fort').removeClass('text-warning text-danger').addClass('text-success');
            }
        }
    </script>
</body>
</html>