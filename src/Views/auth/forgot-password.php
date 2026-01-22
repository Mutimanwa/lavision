<?php
/**
 * Page de connexion LaVision
 * Vue d'authentification pour la connexion utilisateur
 */

// Inclure l'en-tête
// require_once __DIR__ . '/../templates/auth_template.php';

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

<main class="main" id="top">
  <div class="container" data-layout="container">
    <div class="row flex-center min-vh-100 py-6">
      <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4"><a class="d-flex flex-center mb-4"
          href="index.php"><img class="me-2" src="assets/img/icons/spot-illustrations/falcon.png" alt=""
            width="58"><span class="font-sans-serif text-primary fw-bolder fs-4 d-inline-block">falcon</span></a>
        <div class="card">
          <div class="card-body p-4 p-sm-5">
            <div class="row flex-between-center mb-2">
              <div class="col-auto">
                <h5>Mot de passe oublié</h5>
                <!-- <p class="mb-0">Réinitialisez votre mot de passe</p> -->
              </div>

            </div>
            <div class="col-auto mb-3 ">
              <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="fas fa-exclamation-circle me-2"></i>
                  <?= nettoyer_chaine($error_message) ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              <?php endif; ?>

              <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="fas fa-check-circle me-2"></i>
                  <?= nettoyer_chaine($success_message) ?>
                </div>
              <?php endif; ?>
               </div>
                    <!-- <div class="text-center mb-4">
                        <p class="text-muted">
                            Entrez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.
                        </p>
                    </div> -->
            <!-- formulaire de connexion-->
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
                  <input type="email" class="form-control" id="email" name="email" required autocomplete="email"
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
                <a href="<?php echo BASE_URL; ?>/auth/login" class="btn btn-blue  btn-back text-decoration-none">
                  <i class="fas fa-arrow-left me-2"></i>
                  Retour à la connexion
                </a>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</main>