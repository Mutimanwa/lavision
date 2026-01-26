<?php
/**
 * Vue du formulaire d'ajout/modification d'une année scolaire
 * Interface responsive avec Bootstrap 5
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

$isModification = isset($data['action']) && $data['action'] === 'modifier';
$anneeScolaire = $data['annee_scolaire'] ?? null;
$title = $isModification ? 'Modifier une Année Scolaire' : 'Ajouter une Année Scolaire';
$subtitle = $isModification ? 'Modification d\'une année scolaire existante' : 'Création d\'une nouvelle année scolaire';
$breadcrumbText = $isModification ? 'Modifier Année Scolaire' : 'Ajouter Année Scolaire';
$formAction = $isModification ? BASE_URL . '/academique/annee-scolaire/traiter-modification' : BASE_URL . '/academique/annee-scolaire/traiter-ajout';
$submitText = $isModification ? 'Modifier l\'Année Scolaire' : 'Ajouter l\'Année Scolaire';
$submitIcon = $isModification ? 'fas fa-edit' : 'fas fa-save';
?>

<div class="row">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                <?php echo $title; ?>
            </h1>
            <p class="text-muted mt-1"><?php echo $subtitle; ?></p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique">Académique</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique/options">Options</a></li>
                <li class="breadcrumb-item active"><?php echo $breadcrumbText; ?></li>
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
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Informations de l'Année Scolaire
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo $formAction; ?>">
                        <?php if ($isModification): ?>
                            <input type="hidden" name="id" value="<?php echo $anneeScolaire['id']; ?>">
                        <?php endif; ?>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="annee_libelle" class="form-label">
                                Libellé de l'Année Scolaire <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control <?php echo isset($data['erreurs']['annee_libelle']) ? 'is-invalid' : ''; ?>"
                                   id="annee_libelle"
                                   name="annee_libelle"
                                   value="<?php echo htmlspecialchars($data['valeurs']['annee_libelle'] ?? ($anneeScolaire['annee_libelle'] ?? '')); ?>"
                                   placeholder="Ex: 2025-2026, Année Scolaire 2025-2026, etc."
                                   required>
                            <?php if (isset($data['erreurs']['annee_libelle'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($data['erreurs']['annee_libelle']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut" class="form-label">
                                    Date de Début <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control <?php echo isset($data['erreurs']['date_debut']) ? 'is-invalid' : ''; ?>"
                                       id="date_debut"
                                       name="date_debut"
                                       value="<?php echo htmlspecialchars($data['valeurs']['date_debut'] ?? ($anneeScolaire['date_debut'] ?? '')); ?>"
                                       required>
                                <?php if (isset($data['erreurs']['date_debut'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['date_debut']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date_fin" class="form-label">
                                    Date de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control <?php echo isset($data['erreurs']['date_fin']) ? 'is-invalid' : ''; ?>"
                                       id="date_fin"
                                       name="date_fin"
                                       value="<?php echo htmlspecialchars($data['valeurs']['date_fin'] ?? ($anneeScolaire['date_fin'] ?? '')); ?>"
                                       required>
                                <?php if (isset($data['erreurs']['date_fin'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['date_fin']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="statut" class="form-label">
                                Statut Initial
                            </label>
                            <select class="form-select <?php echo isset($data['erreurs']['statut']) ? 'is-invalid' : ''; ?>"
                                    id="statut"
                                    name="statut">
                                <option value="inactive" <?php echo ($data['valeurs']['statut'] ?? 'inactive') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="active" <?php echo ($data['valeurs']['statut'] ?? 'inactive') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="archive" <?php echo ($data['valeurs']['statut'] ?? 'inactive') === 'archive' ? 'selected' : ''; ?>>Archivée</option>
                            </select>
                            <?php if (isset($data['erreurs']['statut'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($data['erreurs']['statut']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Le statut peut être modifié ultérieurement</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description
                            </label>
                            <textarea class="form-control <?php echo isset($data['erreurs']['description']) ? 'is-invalid' : ''; ?>"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Description optionnelle de l'année scolaire..."><?php echo htmlspecialchars($data['valeurs']['description'] ?? ''); ?></textarea>
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
                            <button type="submit" class="btn btn-info">
                                <i class="<?php echo $submitIcon; ?> me-2"></i>
                                <?php echo $submitText; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>