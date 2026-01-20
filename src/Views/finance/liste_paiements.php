<?php
/**
 * Vue de liste des paiements
 * Affiche la liste paginée des paiements avec filtres et statistiques
 * Design responsive avec Bootstrap 5
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
                        <i class="fas fa-money-bill-wave text-primary me-2"></i>
                        Gestion Financière
                    </h1>
                    <p class="text-muted">Suivi des paiements et frais scolaires</p>
                </div>
                <div>
                    <?php if (a_permission('finance_create')): ?>
                        <a href="/finance?action=creer_paiement" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nouveau paiement
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total à payer</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques['total_a_payer'] ?? 0, 0, ',', ' '); ?> FC</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total payé</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques['total_paye'] ?? 0, 0, ',', ' '); ?> FC</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Restant à payer</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques['total_impaye'] ?? 0, 0, ',', ' '); ?> FC</h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Paiements complets</h6>
                                    <h4 class="mb-0"><?php echo $statistiques['paiements_complets'] ?? 0; ?></h4>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-trophy fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="/finance" class="row g-3">
                        <input type="hidden" name="action" value="lister_paiements">

                        <div class="col-md-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="">Tous les statuts</option>
                                <?php foreach ($statuts_paiement as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($filtres['statut'] ?? '') === $key ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="type_frais" class="form-label">Type de frais</label>
                            <select class="form-select" id="type_frais" name="type_frais">
                                <option value="">Tous les types</option>
                                <?php foreach ($types_frais as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($filtres['type_frais'] ?? '') === $key ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="annee_id" class="form-label">Année scolaire</label>
                            <select class="form-select" id="annee_id" name="annee_id">
                                <option value="">Toutes les années</option>
                                <?php foreach ($annees_scolaires as $annee): ?>
                                    <option value="<?php echo $annee['annee_id']; ?>" <?php echo ($filtres['annee_id'] ?? 0) == $annee['annee_id'] ? 'selected' : ''; ?>>
                                        <?php echo $annee['annee_libelle']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="recherche" class="form-label">Recherche</label>
                            <input type="text" class="form-control" id="recherche" name="recherche"
                                   value="<?php echo htmlspecialchars($filtres['recherche'] ?? ''); ?>"
                                   placeholder="Matricule, nom, référence...">
                        </div>

                        <div class="col-md-3">
                            <label for="date_debut" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut"
                                   value="<?php echo $filtres['date_debut'] ?? ''; ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="date_fin" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin"
                                   value="<?php echo $filtres['date_fin'] ?? ''; ?>">
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="/finance" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des paiements -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Liste des paiements
                        <span class="badge bg-primary ms-2"><?php echo $pagination['total']; ?> paiements</span>
                    </h5>
                    <div class="btn-group" role="group">
                        <a href="/finance?action=rapports_financiers" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar me-1"></i>Rapports
                        </a>
                        <a href="/finance?action=paiements_en_retard" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-exclamation-triangle me-1"></i>Retards
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($paiements)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun paiement trouvé</h5>
                            <p class="text-muted">Modifiez vos filtres ou créez un nouveau paiement.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Référence</th>
                                        <th>Élève</th>
                                        <th>Type</th>
                                        <th>Montant total</th>
                                        <th>Payé</th>
                                        <th>Reste</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paiements as $paiement): ?>
                                        <tr>
                                            <td>
                                                <code><?php echo htmlspecialchars($paiement['reference']); ?></code>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($paiement['nom_complet']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($paiement['matricule']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $types_frais[$paiement['type_frais']] ?? $paiement['type_frais']; ?></span>
                                            </td>
                                            <td class="text-end fw-bold">
                                                <?php echo number_format($paiement['montant_total'], 0, ',', ' '); ?> FC
                                            </td>
                                            <td class="text-end text-success">
                                                <?php echo number_format($paiement['montant_paye'], 0, ',', ' '); ?> FC
                                            </td>
                                            <td class="text-end text-warning">
                                                <?php echo number_format($paiement['reste_a_payer'], 0, ',', ' '); ?> FC
                                            </td>
                                            <td>
                                                <?php
                                                $badge_class = match($paiement['statut']) {
                                                    'paye' => 'bg-success',
                                                    'partiel' => 'bg-warning',
                                                    'impaye' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $statuts_paiement[$paiement['statut']] ?? $paiement['statut']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('d/m/Y', strtotime($paiement['date_creation'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/finance?action=voir_paiement&id=<?php echo $paiement['paiement_id']; ?>"
                                                       class="btn btn-sm btn-outline-primary" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (a_permission('finance_edit')): ?>
                                                        <a href="/finance?action=modifier_paiement&id=<?php echo $paiement['paiement_id']; ?>"
                                                           class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (a_permission('finance_create') && $paiement['statut'] !== 'paye'): ?>
                                                        <a href="/finance?action=generer_quittance&paiement_id=<?php echo $paiement['paiement_id']; ?>"
                                                           class="btn btn-sm btn-outline-success" title="Ajouter paiement">
                                                            <i class="fas fa-plus-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['pages'] > 1): ?>
                            <nav aria-label="Navigation des paiements" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['page_actuelle'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?action=lister_paiements&page=<?php echo $pagination['page_actuelle'] - 1; ?><?php echo http_build_query(array_diff_key($filtres, ['page' => ''])); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $pagination['page_actuelle'] - 2); $i <= min($pagination['pages'], $pagination['page_actuelle'] + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $pagination['page_actuelle'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?action=lister_paiements&page=<?php echo $i; ?><?php echo http_build_query(array_diff_key($filtres, ['page' => ''])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagination['page_actuelle'] < $pagination['pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?action=lister_paiements&page=<?php echo $pagination['page_actuelle'] + 1; ?><?php echo http_build_query(array_diff_key($filtres, ['page' => ''])); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts spécifiques -->
<script>
// Fonction de recherche en temps réel (optionnel)
document.getElementById('recherche')?.addEventListener('input', function() {
    // Implémentation de la recherche AJAX si nécessaire
});

// Validation des dates
document.getElementById('date_debut')?.addEventListener('change', function() {
    const dateFin = document.getElementById('date_fin');
    if (dateFin && this.value > dateFin.value) {
        dateFin.value = this.value;
    }
});

document.getElementById('date_fin')?.addEventListener('change', function() {
    const dateDebut = document.getElementById('date_debut');
    if (dateDebut && this.value < dateDebut.value) {
        dateDebut.value = this.value;
    }
});
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>