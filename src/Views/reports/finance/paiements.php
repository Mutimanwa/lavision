<?php
/**
 * Rapport - État des paiements
 * Vue détaillée pour la génération du rapport des paiements
 */

// Inclure l'en-tête
require_once __DIR__ . '/../templates/header.php';

// Récupérer les données du contexte
$paiements = $paiements ?? [];
$filters = $filters ?? [];
$stats_paiements = $stats_paiements ?? [];

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <?php require_once __DIR__ . '/../templates/sidebar.php'; ?>
        </nav>

        <!-- Contenu principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Rapport - État des paiements
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Tableau de bord</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/reports">Rapports</a></li>
                        <li class="breadcrumb-item active">Paiements</li>
                    </ol>
                </nav>
            </div>

            <!-- Messages d'alerte -->
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistiques financières -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-euro-sign fa-2x text-success"></i>
                            </div>
                            <h5 class="card-title">
                                <?php echo number_format($stats_paiements['total_recettes'] ?? 0, 0, ',', ' '); ?> €
                            </h5>
                            <p class="card-text">Total recettes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                            <h5 class="card-title">
                                <?php echo number_format($stats_paiements['total_impayes'] ?? 0, 0, ',', ' '); ?> €
                            </h5>
                            <p class="card-text">Impayés</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-percentage fa-2x text-info"></i>
                            </div>
                            <h5 class="card-title">
                                <?php
                                $total = ($stats_paiements['total_recettes'] ?? 0) + ($stats_paiements['total_impayes'] ?? 0);
                                $pourcentage = $total > 0 ? (($stats_paiements['total_recettes'] ?? 0) / $total) * 100 : 0;
                                echo round($pourcentage, 1);
                                ?>%
                            </h5>
                            <p class="card-text">Taux de paiement</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title">
                                <?php echo $stats_paiements['moyenne_mensuelle'] ?? 0; ?> €
                            </h5>
                            <p class="card-text">Moyenne mensuelle</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtres du rapport
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo BASE_URL; ?>/reports/finance/paiements" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="statut_paiement" class="form-label">Statut</label>
                                <select class="form-select" id="statut_paiement" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="paye" <?php echo ($filters['statut'] ?? '') === 'paye' ? 'selected' : ''; ?>>Payé</option>
                                    <option value="impaye" <?php echo ($filters['statut'] ?? '') === 'impaye' ? 'selected' : ''; ?>>Impayé</option>
                                    <option value="partiel" <?php echo ($filters['statut'] ?? '') === 'partiel' ? 'selected' : ''; ?>>Partiellement payé</option>
                                    <option value="remise" <?php echo ($filters['statut'] ?? '') === 'remise' ? 'selected' : ''; ?>>Remise accordée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="type_frais" class="form-label">Type de frais</label>
                                <select class="form-select" id="type_frais" name="type_frais">
                                    <option value="">Tous les types</option>
                                    <option value="scolarite" <?php echo ($filters['type_frais'] ?? '') === 'scolarite' ? 'selected' : ''; ?>>Scolarité</option>
                                    <option value="inscription" <?php echo ($filters['type_frais'] ?? '') === 'inscription' ? 'selected' : ''; ?>>Inscription</option>
                                    <option value="cantine" <?php echo ($filters['type_frais'] ?? '') === 'cantine' ? 'selected' : ''; ?>>Cantine</option>
                                    <option value="transport" <?php echo ($filters['type_frais'] ?? '') === 'transport' ? 'selected' : ''; ?>>Transport</option>
                                    <option value="activites" <?php echo ($filters['type_frais'] ?? '') === 'activites' ? 'selected' : ''; ?>>Activités</option>
                                    <option value="autres" <?php echo ($filters['type_frais'] ?? '') === 'autres' ? 'selected' : ''; ?>>Autres</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_debut" class="form-label">Date début</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_debut"
                                       name="date_debut"
                                       value="<?php echo $filters['date_debut'] ?? date('Y-m-01'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_fin" class="form-label">Date fin</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_fin"
                                       name="date_fin"
                                       value="<?php echo $filters['date_fin'] ?? date('Y-m-t'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="eleve_nom" class="form-label">Nom de l'élève</label>
                                <input type="text"
                                       class="form-control"
                                       id="eleve_nom"
                                       name="eleve_nom"
                                       value="<?php echo htmlspecialchars($filters['eleve_nom'] ?? ''); ?>"
                                       placeholder="Rechercher par nom">
                            </div>
                            <div class="col-md-4">
                                <label for="montant_min" class="form-label">Montant minimum</label>
                                <input type="number"
                                       class="form-control"
                                       id="montant_min"
                                       name="montant_min"
                                       min="0"
                                       step="0.01"
                                       value="<?php echo $filters['montant_min'] ?? ''; ?>"
                                       placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label for="montant_max" class="form-label">Montant maximum</label>
                                <input type="number"
                                       class="form-control"
                                       id="montant_max"
                                       name="montant_max"
                                       min="0"
                                       step="0.01"
                                       value="<?php echo $filters['montant_max'] ?? ''; ?>"
                                       placeholder="0.00">
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>
                                        Appliquer les filtres
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>/reports/finance/paiements" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Actions d'export -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Liste des paiements</h4>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf me-2"></i>
                        Exporter PDF
                    </button>
                    <button type="button" class="btn btn-outline-success me-2" onclick="exportReport('excel')">
                        <i class="fas fa-file-excel me-2"></i>
                        Exporter Excel
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="exportReport('csv')">
                        <i class="fas fa-file-csv me-2"></i>
                        Exporter CSV
                    </button>
                </div>
            </div>

            <!-- Tableau des paiements -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="paiementsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Élève</th>
                                    <th>Type de frais</th>
                                    <th>Montant dû</th>
                                    <th>Montant payé</th>
                                    <th>Reste à payer</th>
                                    <th>Date échéance</th>
                                    <th>Date paiement</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paiements)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Aucun paiement trouvé avec les critères sélectionnés
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($paiements as $paiement): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($paiement['paiement_id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($paiement['eleve_nom'] . ' ' . $paiement['eleve_prenom']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($paiement['matricule']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php
                                                    echo $paiement['type_frais'] === 'scolarite' ? 'Scolarité' :
                                                         ($paiement['type_frais'] === 'inscription' ? 'Inscription' :
                                                         ($paiement['type_frais'] === 'cantine' ? 'Cantine' :
                                                         ($paiement['type_frais'] === 'transport' ? 'Transport' :
                                                         ($paiement['type_frais'] === 'activites' ? 'Activités' : 'Autres'))));
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($paiement['montant_du'], 2, ',', ' '); ?> €</td>
                                            <td><?php echo number_format($paiement['montant_paye'] ?? 0, 2, ',', ' '); ?> €</td>
                                            <td>
                                                <?php
                                                $reste = $paiement['montant_du'] - ($paiement['montant_paye'] ?? 0);
                                                $class = $reste > 0 ? 'text-danger' : 'text-success';
                                                ?>
                                                <span class="<?php echo $class; ?>">
                                                    <?php echo number_format($reste, 2, ',', ' '); ?> €
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($paiement['date_echeance'])); ?></td>
                                            <td>
                                                <?php if ($paiement['date_paiement']): ?>
                                                    <?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $paiement['statut'] === 'paye' ? 'success' :
                                                         ($paiement['statut'] === 'impaye' ? 'danger' :
                                                         ($paiement['statut'] === 'partiel' ? 'warning' : 'info'));
                                                ?>">
                                                    <?php
                                                    echo $paiement['statut'] === 'paye' ? 'Payé' :
                                                         ($paiement['statut'] === 'impaye' ? 'Impayé' :
                                                         ($paiement['statut'] === 'partiel' ? 'Partiel' : 'Remise'));
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary"
                                                            onclick="viewPaiementDetails(<?php echo $paiement['paiement_id']; ?>)"
                                                            title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($paiement['statut'] !== 'paye'): ?>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-success"
                                                                onclick="recordPayment(<?php echo $paiement['paiement_id']; ?>)"
                                                                title="Enregistrer paiement">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-info"
                                                            onclick="printReceipt(<?php echo $paiement['paiement_id']; ?>)"
                                                            title="Imprimer reçu">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.text-success {
    color: #198754 !important;
}

.text-danger {
    color: #dc3545 !important;
}
</style>

<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Initialiser DataTable si beaucoup de données
    if ($('#paiementsTable tbody tr').length > 50) {
        $('#paiementsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json'
            },
            pageLength: 25,
            order: [[6, 'asc']] // Trier par date d'échéance
        });
    }
});

// Fonction pour voir les détails d'un paiement
function viewPaiementDetails(paiementId) {
    window.open('<?php echo BASE_URL; ?>/finance/paiements/details/' + paiementId, '_blank');
}

// Fonction pour enregistrer un paiement
function recordPayment(paiementId) {
    window.location.href = '<?php echo BASE_URL; ?>/finance/paiements/enregistrer/' + paiementId;
}

// Fonction pour imprimer un reçu
function printReceipt(paiementId) {
    window.open('<?php echo BASE_URL; ?>/reports/finance/receipt/' + paiementId, '_blank');
}

// Fonction pour exporter le rapport
function exportReport(format) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);

    // Ajouter le format d'export
    params.append('export', format);

    // Construire l'URL
    const url = '<?php echo BASE_URL; ?>/reports/finance/paiements?' + params.toString();

    // Ouvrir dans un nouvel onglet
    window.open(url, '_blank');
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>