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
                <h5>Connexion</h5>
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
            <!-- formulaire de connexion-->
            <form method="POST" id="loginForm">
              <!-- generaton du tokken   -->
              <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">

            <!-- Champ Identifiant -->
                    <div class="mb-3">
                        <label for="identifiant" class="form-label">
                            <i class="fas fa-user me-2"></i>Identifiant
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="identifiant"
                                   name="identifiant"
                                   required
                                   autocomplete="username"
                                   placeholder="Votre identifiant">
                        </div>
                    </div>

                    <!-- Champ Mot de passe -->
                    <div class="mb-3">
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
                    <!-- Options supplémentaires -->
              <div class="row flex-between-center">
                <div class="col-auto">
                  <div class="form-check mb-0">
                    <input class="form-check-input" name="remember" type="checkbox" id="basic-checkbox">
                    <label class="form-check-label mb-0" for="basic-checkbox">Se souvenir de moi</label>
                  </div>
                </div>
                <div class="col-auto"><a class="fs-10" href="<?= url("forgot_password");?>">Mot de passe oublié ?</a></div>
              </div>
              <div class="mb-3"><button class="btn btn-primary d-block w-100 mt-3" type="submit" name="submit">Se
                  connecter</button></div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</main>
