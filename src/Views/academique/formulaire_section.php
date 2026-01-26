<?php
/**
 * Vue du formulaire d'ajout d'une section académique
 * Interface responsive avec Bootstrap 5
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

?>

<div class="row">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                Ajouter une Section Académique
            </h1>
            <p class="text-muted mt-1">Création d'une nouvelle section scolaire</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique">Académique</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique/options">Options</a></li>
                <li class="breadcrumb-item active">Ajouter Section</li>
            </ol>
        </nav>
    </div>

    <!-- Messages d'alerte -->
    <?php if (isset($data['erreurs']) && !empty($data['erreurs'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Erreurs de validation :</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($data['erreurs'] as $champ => $erreur): ?>
                    <li><?php echo htmlspecialchars($erreur); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-stream me-2"></i>
                        Informations de la Section
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo BASE_URL; ?>/academique/section/ajouter">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nom_section" class="form-label">
                                    Nom de la Section <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control <?php echo isset($data['erreurs']['nom_section']) ? 'is-invalid' : ''; ?>"
                                       id="nom_section"
                                       name="nom_section"
                                       value="<?php echo htmlspecialchars($data['valeurs']['nom_section'] ?? ''); ?>"
                                       placeholder="Ex: Générale, Technique, Littéraire, etc."
                                       required>
                                <?php if (isset($data['erreurs']['nom_section'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['nom_section']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="couleur" class="form-label">
                                    Couleur
                                </label>
                                <input type="color"
                                       class="form-control <?php echo isset($data['erreurs']['couleur']) ? 'is-invalid' : ''; ?>"
                                       id="couleur"
                                       name="couleur"
                                       value="<?php echo htmlspecialchars($data['valeurs']['couleur'] ?? '#007bff'); ?>"
                                       style="height: 38px;">
                                <?php if (isset($data['erreurs']['couleur'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['couleur']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">Couleur représentative de la section</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description
                            </label>
                            <textarea class="form-control <?php echo isset($data['erreurs']['description']) ? 'is-invalid' : ''; ?>"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Description optionnelle de la section..."><?php echo htmlspecialchars($data['valeurs']['description'] ?? ''); ?></textarea>
                            <?php if (isset($data['erreurs']['description'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($data['erreurs']['description']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="<?php echo BASE_URL; ?>/academique/options" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>
                                Ajouter la Section
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>