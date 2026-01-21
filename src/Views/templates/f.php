<?php
/**
 * Template d'authentification
 * LaVision - Système de gestion scolaire
 *
 * Ce template est utilisé pour les pages de connexion, inscription,
 * récupération de mot de passe, etc.
 */

// Définir les valeurs par défaut
$page_title = $page_title ?? 'LaVision - Connexion';
$form_title = $form_title ?? 'Connexion';
$form_subtitle = $form_subtitle ?? 'Connectez-vous à votre compte';
$show_logo = $show_logo ?? true;
$background_image = $background_image ?? ASSETS_PATH . '/img/backgrounds/auth-bg.jpg';
$custom_styles = $custom_styles ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | LaVision</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo ASSETS_PATH; ?>/img/favicons/favicon.ico">

    <!-- Bootstrap CSS -->
    <link href="<?php echo ASSETS_PATH; ?>/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="<?php echo ASSETS_PATH; ?>/libs/fontawesome/css/all.min.css" rel="stylesheet">

    <!-- CSS personnalisé -->
    <link href="<?php echo ASSETS_PATH; ?>/css/theme.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_PATH; ?>/css/user.min.css" rel="stylesheet">

    <!-- Styles spécifiques à l'authentification -->
    <style>
        :root {
            --auth-primary: #667eea;
            --auth-secondary: #764ba2;
            --auth-accent: #f093fb;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-secondary) 100%);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            position: relative;
        }

        .auth-header {
            background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-accent) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .auth-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            backdrop-filter: blur(10px);
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .auth-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--auth-primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            color: #6c757d;
        }

        .form-control:focus + .input-group-text,
        .input-group-text:focus-within {
            border-color: var(--auth-primary);
        }

        .btn-auth {
            background: linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-secondary) 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .auth-links a {
            color: var(--auth-primary);
            text-decoration: none;
            font-size: 0.875rem;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }

        .auth-links a:hover {
            color: var(--auth-secondary);
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }

        .alert-warning {
            background: linear-gradient(135deg, #ffd43b 0%, #fab005 100%);
            color: #212529;
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--auth-primary);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .auth-container {
                padding: 1rem;
            }

            .auth-header {
                padding: 1.5rem;
            }

            .auth-body {
                padding: 1.5rem;
            }

            .auth-title {
                font-size: 1.5rem;
            }
        }

        /* Styles personnalisés */
        <?php echo $custom_styles; ?>
    </style>
</head>
<body>
    <!-- Spinner de chargement -->
    <div class="loading-spinner" id="loading-spinner">
        <div class="spinner-border" role="status">
            <span class="sr-only">Chargement...</span>
        </div>
    </div>

    <div class="auth-container">
        <div class="auth-card">
            <!-- En-tête -->
            <?php if ($show_logo): ?>
                <div class="auth-header">
                    <div class="auth-logo">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h1 class="auth-title"><?php echo htmlspecialchars($form_title); ?></h1>
                    <p class="auth-subtitle"><?php echo htmlspecialchars($form_subtitle); ?></p>
                </div>
            <?php endif; ?>

            <!-- Corps du formulaire -->
            <div class="auth-body">
                <!-- Messages d'alerte -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['warning'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['warning']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['warning']); ?>
                <?php endif; ?>

                <!-- Contenu du formulaire (sera inséré ici) -->
                <?php
                // Cette section sera remplacée par le contenu spécifique de chaque page d'authentification
                if (isset($auth_content)) {
                    echo $auth_content;
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo ASSETS_PATH; ?>/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_PATH; ?>/libs/jquery/jquery.min.js"></script>

    <!-- Script d'authentification -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Masquer le spinner de chargement
            document.getElementById('loading-spinner').style.display = 'none';

            // Gestion des formulaires d'authentification
            const authForms = document.querySelectorAll('.auth-form');
            authForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Afficher le spinner
                    document.getElementById('loading-spinner').style.display = 'block';

                    // Désactiver le bouton de soumission
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion...';
                    }
                });
            });

            // Validation en temps réel des champs
            const emailFields = document.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                field.addEventListener('blur', function() {
                    const email = this.value.trim();
                    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

                    if (email && !isValid) {
                        this.classList.add('is-invalid');
                        let feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.textContent = 'Veuillez entrer une adresse email valide.';
                            this.parentNode.appendChild(feedback);
                        }
                    } else {
                        this.classList.remove('is-invalid');
                        const feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (feedback) {
                            feedback.remove();
                        }
                    }
                });
            });

            // Gestion des mots de passe
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const targetField = document.getElementById(targetId);

                    if (targetField) {
                        const type = targetField.getAttribute('type') === 'password' ? 'text' : 'password';
                        targetField.setAttribute('type', type);

                        const icon = this.querySelector('i');
                        if (icon) {
                            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                        }
                    }
                });
            });

            // Animation des éléments au chargement
            const elements = document.querySelectorAll('.form-group, .auth-links');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.3s ease';

                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Gestion des erreurs JavaScript
            window.addEventListener('error', function(e) {
                console.error('Erreur JavaScript:', e.error);
                document.getElementById('loading-spinner').style.display = 'none';
            });

            // Auto-focus sur le premier champ
            const firstInput = document.querySelector('input:not([type="hidden"])');
            if (firstInput) {
                firstInput.focus();
            }

            // Gestion du thème sombre/clair (si implémenté)
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.body.classList.toggle('dark-theme');
                    const isDark = document.body.classList.contains('dark-theme');
                    localStorage.setItem('auth-theme', isDark ? 'dark' : 'light');
                });

                // Restaurer le thème sauvegardé
                const savedTheme = localStorage.getItem('auth-theme');
                if (savedTheme === 'dark') {
                    document.body.classList.add('dark-theme');
                }
            }
        });

        // Fonction de validation de formulaire
        function validateAuthForm(form) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;

                    let feedback = field.parentNode.querySelector('.invalid-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'Ce champ est obligatoire.';
                        field.parentNode.appendChild(feedback);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const feedback = field.parentNode.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.remove();
                    }
                }
            });

            return isValid;
        }
    </script>
</body>
</html>

