<?php
/**
 * Vue du formulaire de paiement
 * Formulaire de création et modification des paiements
 * Design responsive avec Bootstrap 5 et validation côté client
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

// Inclusion du header
require_once TEMPLATES_PATH . '/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2">
            <?php require_once TEMPLATES_PATH . '/sidebar.php'; ?>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-<?php echo $paiement ? 'edit' : 'plus'; ?> text-primary me-2"></i>
                        <?php echo $paiement ? 'Modifier le paiement' : 'Nouveau paiement'; ?>
                    </h1>
                    <p class="text-muted">
                        <?php echo $paiement ? 'Modifiez les informations du paiement' : 'Créez un nouveau paiement pour un élève'; ?>
                    </p>
                </div>
                <div>
                    <a href="/finance" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Affichage des erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <h5 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Erreurs de validation
                    </h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="row">
                <div class="col-lg-8">
                    <form method="POST" action="/finance" id="paiementForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="<?php echo $paiement ? 'modifier_paiement&id=' . $paiement['paiement_id'] : 'creer_paiement'; ?>">

                        <!-- Informations de base -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Informations générales
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if (!$paiement): // Uniquement pour la création ?>
                                        <div class="col-md-6 mb-3">
                                            <label for="eleve_id" class="form-label">
                                                Élève <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="eleve_id" name="eleve_id" required>
                                                <option value="">Sélectionnez un élève</option>
                                                <?php foreach ($eleves as $eleve): ?>
                                                    <option value="<?php echo $eleve['eleve_id']; ?>"
                                                            <?php echo ($paiement['eleve_id'] ?? '') == $eleve['eleve_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($eleve['nom_complet'] . ' (' . $eleve['matricule'] . ')'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Veuillez sélectionner un élève.
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="annee_id" class="form-label">
                                                Année scolaire <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="annee_id" name="annee_id" required>
                                                <option value="">Sélectionnez une année</option>
                                                <?php foreach ($annees_scolaires as $annee): ?>
                                                    <option value="<?php echo $annee['annee_id']; ?>"
                                                            <?php echo ($paiement['annee_id'] ?? '') == $annee['annee_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($annee['annee_libelle']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Veuillez sélectionner une année scolaire.
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Élève</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($paiement['nom_complet'] . ' (' . $paiement['matricule'] . ')'); ?>" readonly>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Année scolaire</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($paiement['annee_libelle']); ?>" readonly>
                                        </div>
                                    <?php endif; ?>

                                    <div class="col-md-6 mb-3">
                                        <label for="type_frais" class="form-label">
                                            Type de frais <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="type_frais" name="type_frais" required>
                                            <option value="">Sélectionnez un type</option>
                                            <?php foreach ($types_frais as $key => $label): ?>
                                                <option value="<?php echo $key; ?>"
                                                        <?php echo ($paiement['type_frais'] ?? '') === $key ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Veuillez sélectionner un type de frais.
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="libelle" class="form-label">
                                            Libellé <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="libelle" name="libelle"
                                               value="<?php echo htmlspecialchars($paiement['libelle'] ?? ''); ?>"
                                               placeholder="Ex: Frais de scolarité - 1ère tranche" required>
                                        <div class="invalid-feedback">
                                            Le libellé est obligatoire.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations financières -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calculator me-2"></i>Informations financières
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="montant_total" class="form-label">
                                            Montant total (FC) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="montant_total" name="montant_total"
                                               value="<?php echo $paiement['montant_total'] ?? ''; ?>"
                                               min="0" step="0.01" placeholder="0.00" required>
                                        <div class="invalid-feedback">
                                            Le montant total doit être positif.
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="montant_paye" class="form-label">
                                            Montant payé (FC)
                                        </label>
                                        <input type="number" class="form-control" id="montant_paye" name="montant_paye"
                                               value="<?php echo $paiement['montant_paye'] ?? 0; ?>"
                                               min="0" step="0.01" placeholder="0.00">
                                        <div class="invalid-feedback">
                                            Le montant payé ne peut pas être négatif.
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Restant à payer (FC)</label>
                                        <input type="text" class="form-control" id="reste_a_payer" readonly
                                               value="<?php echo number_format(($paiement['montant_total'] ?? 0) - ($paiement['montant_paye'] ?? 0), 2, ',', ' '); ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="date_echeance" class="form-label">
                                            Date d'échéance
                                        </label>
                                        <input type="date" class="form-control" id="date_echeance" name="date_echeance"
                                               value="<?php echo $paiement['date_echeance'] ?? ''; ?>">
                                        <div class="form-text">
                                            Laissez vide si pas d'échéance spécifique
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="mode_paiement" class="form-label">
                                            Mode de paiement
                                        </label>
                                        <select class="form-select" id="mode_paiement" name="mode_paiement">
                                            <?php foreach ($modes_paiement as $key => $label): ?>
                                                <option value="<?php echo $key; ?>"
                                                        <?php echo ($paiement['mode_paiement'] ?? 'Espece') === $key ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Aperçu du statut -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-info" id="statutPreview">
                                            <strong>Statut estimé:</strong> <span id="statutText">Impayé</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>Notes complémentaires
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <textarea class="form-control" id="notes" name="notes" rows="3"
                                              placeholder="Ajoutez des notes sur ce paiement..."><?php echo htmlspecialchars($paiement['notes'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        Informations supplémentaires sur le paiement (optionnel)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <a href="/finance" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $paiement ? 'Modifier le paiement' : 'Créer le paiement'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Informations complémentaires -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Aide
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Types de frais disponibles:</h6>
                            <ul class="list-unstyled">
                                <?php foreach ($types_frais as $key => $label): ?>
                                    <li><strong><?php echo $label; ?>:</strong> Frais liés à <?php echo strtolower($label); ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <hr>

                            <h6>Statuts de paiement:</h6>
                            <ul class="list-unstyled">
                                <li><span class="badge bg-danger">Impayé:</span> Aucun paiement effectué</li>
                                <li><span class="badge bg-warning">Partiel:</span> Paiement partiellement effectué</li>
                                <li><span class="badge bg-success">Payé:</span> Paiement complètement effectué</li>
                            </ul>
                        </div>
                    </div>

                    <?php if ($paiement): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>Informations du paiement
                                </h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-5">Référence:</dt>
                                    <dd class="col-sm-7"><code><?php echo htmlspecialchars($paiement['reference']); ?></code></dd>

                                    <dt class="col-sm-5">Date création:</dt>
                                    <dd class="col-sm-7"><?php echo date('d/m/Y H:i', strtotime($paiement['date_creation'])); ?></dd>

                                    <dt class="col-sm-5">Dernière modif:</dt>
                                    <dd class="col-sm-7"><?php echo $paiement['date_modif'] ? date('d/m/Y H:i', strtotime($paiement['date_modif'])) : 'Aucune'; ?></dd>

                                    <dt class="col-sm-5">Caissier:</dt>
                                    <dd class="col-sm-7"><?php echo htmlspecialchars(($paiement['caissier_nom'] ?? 'N/A') . ' ' . ($paiement['caissier_prenom'] ?? '')); ?></dd>
                                </dl>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts spécifiques -->
<script>
// Calcul automatique du restant à payer
function calculerReste() {
    const total = parseFloat(document.getElementById('montant_total').value) || 0;
    const paye = parseFloat(document.getElementById('montant_paye').value) || 0;
    const reste = total - paye;

    document.getElementById('reste_a_payer').value = reste.toLocaleString('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // Mise à jour du statut estimé
    mettreAJourStatut(total, paye);
}

// Mise à jour du statut estimé
function mettreAJourStatut(total, paye) {
    let statut = 'Impayé';
    let badgeClass = 'bg-danger';

    if (paye === 0) {
        statut = 'Impayé';
        badgeClass = 'bg-danger';
    } else if (paye >= total) {
        statut = 'Payé';
        badgeClass = 'bg-success';
    } else {
        statut = 'Partiellement payé';
        badgeClass = 'bg-warning';
    }

    document.getElementById('statutText').textContent = statut;
    document.getElementById('statutPreview').className = `alert alert-info ${badgeClass.replace('bg-', 'text-')}`;
}

// Événements
document.getElementById('montant_total').addEventListener('input', calculerReste);
document.getElementById('montant_paye').addEventListener('input', calculerReste);

// Calcul initial
calculerReste();

// Validation du formulaire
document.getElementById('paiementForm').addEventListener('submit', function(event) {
    if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Validation personnalisée
    const total = parseFloat(document.getElementById('montant_total').value) || 0;
    const paye = parseFloat(document.getElementById('montant_paye').value) || 0;

    if (paye > total) {
        event.preventDefault();
        alert('Le montant payé ne peut pas dépasser le montant total.');
        return;
    }

    this.classList.add('was-validated');
});

// Initialisation des tooltips Bootstrap si nécessaire
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>