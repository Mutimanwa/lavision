<?php
/**
 * Vue des options académiques
 * Permet de configurer les niveaux et sections disponibles
 * Interface responsive avec Bootstrap 5
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

// Inclusion du template d'en-tête
require_once TEMPLATES_PATH . '/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <?php require_once TEMPLATES_PATH . '/sidebar.php'; ?>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-cogs text-primary me-2"></i>
                        Options Académiques
                    </h1>
                    <p class="text-muted mt-1">Configuration des niveaux et sections scolaires</p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique">Académique</a></li>
                        <li class="breadcrumb-item active">Options</li>
                    </ol>
                </nav>
            </div>

            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['message_succes'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['message_succes']; unset($_SESSION['message_succes']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['message_erreur'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['message_erreur']; unset($_SESSION['message_erreur']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Niveaux Académiques -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-layer-group me-2"></i>
                                Niveaux Académiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Sélectionnez les niveaux scolaires disponibles dans l'établissement.
                            </p>

                            <form method="post" action="<?php echo BASE_URL; ?>/academique/options/modifier">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                                <div class="row">
                                    <?php foreach ($data['niveaux'] as $niveau): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="niveaux[<?php echo $niveau['id']; ?>][actif]"
                                                       value="1" id="niveau_<?php echo $niveau['id']; ?>"
                                                       <?php echo $niveau['actif'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="niveau_<?php echo $niveau['id']; ?>">
                                                    <strong><?php echo htmlspecialchars($niveau['nom_niveau']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($niveau['code_niveau']); ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-grid mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Sauvegarder les Niveaux
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sections Académiques -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-stream me-2"></i>
                                Sections Académiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Sélectionnez les sections disponibles dans l'établissement.
                            </p>

                            <form method="post" action="<?php echo BASE_URL; ?>/academique/options/modifier">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                                <div class="row">
                                    <?php foreach ($data['sections'] as $section): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="sections[<?php echo $section['id']; ?>][actif]"
                                                       value="1" id="section_<?php echo $section['id']; ?>"
                                                       <?php echo $section['actif'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="section_<?php echo $section['id']; ?>">
                                                    <strong><?php echo htmlspecialchars($section['nom_section']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($section['code_section']); ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-grid mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>
                                        Sauvegarder les Sections
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations sur l'année scolaire -->
            <?php if ($data['annee_scolaire']): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Année Scolaire Active
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 text-primary mb-0"><?php echo $data['annee_scolaire']['annee_debut']; ?> - <?php echo $data['annee_scolaire']['annee_fin']; ?></div>
                                            <small class="text-muted">Année en cours</small>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <small class="text-muted d-block">Date de début</small>
                                                <strong><?php echo date('d/m/Y', strtotime($data['annee_scolaire']['date_debut'])); ?></strong>
                                            </div>
                                            <div class="col-sm-4">
                                                <small class="text-muted d-block">Date de fin</small>
                                                <strong><?php echo date('d/m/Y', strtotime($data['annee_scolaire']['date_fin'])); ?></strong>
                                            </div>
                                            <div class="col-sm-4">
                                                <small class="text-muted d-block">Statut</small>
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Statistiques rapides -->
            <div class="row mt-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <div class="h3 mb-0"><?php echo count(array_filter($data['niveaux'], fn($n) => $n['actif'])); ?></div>
                            <small>Niveaux actifs</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <div class="h3 mb-0"><?php echo count(array_filter($data['sections'], fn($s) => $s['actif'])); ?></div>
                            <small>Sections actives</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <div class="h3 mb-0"><?php echo count($data['niveaux']); ?></div>
                            <small>Niveaux totaux</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <div class="h3 mb-0"><?php echo count($data['sections']); ?></div>
                            <small>Sections totales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts spécifiques -->
<script>
// Gestion des formulaires séparés
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit des formulaires lors de changement de checkbox
    document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                // Petite animation pour indiquer la sauvegarde
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde...';
                    button.disabled = true;

                    // Soumettre le formulaire après un court délai
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                }
            }
        });
    });
});
</script>

<?php
// Inclusion du template de pied de page
require_once TEMPLATES_PATH . '/footer.php';
?>