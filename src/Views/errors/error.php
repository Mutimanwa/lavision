<?php
/**
 * Page d'erreur générique
 * LaVision - Système de gestion scolaire
 */

// Les variables $data, $message et $code sont passées depuis afficher_erreur()
$message = $data['message'] ?? 'Une erreur inattendue s\'est produite.';
$code = $data['code'] ?? 500;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur <?= $code ?> - Falcon</title>
    <link rel="stylesheet" href="<?= CSS_URL ?>theme.min.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>user.min.css">
</head>
<body>
    <main class="main" id="top">
        <div class="container">
            <div class="row flex-center min-vh-100 py-6 text-center">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xxl-5">
                    <a class="d-flex flex-center mb-4" href="<?= BASE_URL ?>">
                        <img class="me-2" src="<?= IMAGES_URL ?>favicons/favicon.ico" alt="Falcon" width="58" />
                        <span class="font-sans-serif text-primary fw-bolder fs-4 d-inline-block">Falcon</span>
                    </a>
                    <div class="card">
                        <div class="card-body p-4 p-sm-5">
                            <div class="fw-black lh-1 text-300 fs-error"><?= $code ?></div>
                            <p class="lead mt-4 text-800 font-sans-serif fw-semi-bold">Erreur</p>
                            <hr />
                            <p class="text-danger"><?= htmlspecialchars($message) ?></p>
                            <p>Si ce problème persiste, <a href="mailto:support@lavision.edu">contactez le support</a>.</p>
                            <a href="<?= BASE_URL ?>" class="btn btn-primary">Retour à l'accueil</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>