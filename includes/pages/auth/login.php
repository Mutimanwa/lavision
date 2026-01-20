<?php
// includes/pages/auth/login.php

// Si déjà connecté, rediriger vers le dashboard
if (is_logged_in()) {
  redirect('dashboard');
}

// Initialiser les messages d'erreur
$error = '';
$success = '';

// Traiter le formulaire s'il est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $identifiant = trim($_POST['identifiant'] ?? '');
  $mot_de_passe = $_POST['mot_de_passe'] ?? '';
  $remember = isset($_POST['remember']);

  // Authentifier l'utilisateur
  $result = auth_login($identifiant, $mot_de_passe, $remember);

  if ($result['success']) {
    // Connexion réussie
    $success = 'Connexion réussie ! Redirection...';

    // Rediriger vers la page demandée ou le dashboard
    $redirect_to = $_SESSION['redirect_to'] ?? 'dashboard';
    unset($_SESSION['redirect_to']);

    // Ajouter un délai pour afficher le message
    echo '<meta http-equiv="refresh" content="1;url=' . url($redirect_to) . '">';
  } else {
    // Échec de connexion
    $error = $result['error'] ?? 'Identifiant ou mot de passe incorrect';
  }
}
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
              <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="fas fa-exclamation-circle me-2"></i>
                  <?= e($error) ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
              <?php endif; ?>

              <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="fas fa-check-circle me-2"></i>
                  <?= e($success) ?>
                </div>
              <?php endif; ?>
            </div>
            <!-- formulaire de connexion-->
            <form method="POST" id="loginForm">
              <!-- generaton du tokken   -->
              <?= csrf_field() ?>
              <div class="mb-3">
                <input class="form-control" name="identifiant" type="text" placeholder="Identifiant" required>
              </div>
              <div class="mb-3">
                <input class="form-control" name="mot_de_passe" type="password"
                  placeholder="Mot de passe" required></div>
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

