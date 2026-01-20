<?php
/**
 * Vue du rapport financier
 * Affiche les statistiques détaillées sur les paiements et finances
 * Design responsive avec Bootstrap 5 et export
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
                        <i class="fas fa-money-bill-wave text-warning me-2"></i>
                        Rapport Financier
                    </h1>
                    <p class="text-muted">Statistiques détaillées sur les paiements et finances scolaires</p>
                </div>
                <div>
                    <button type="button" class="btn btn-success me-2" onclick="exporterPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Exporter PDF
                    </button>
                    <button type="button" class="btn btn-primary" onclick="exporterExcel()">
                        <i class="fas fa-file-excel me-2"></i>Exporter Excel
                    </button>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres du Rapport
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="/rapports" class="row g-3">
                        <input type="hidden" name="action" value="rapport_financier">
                        <div class="col-md-3">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut"
                                   value="<?php echo $filtres['date_debut'] ?? date('Y-m-01'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin"
                                   value="<?php echo $filtres['date_fin'] ?? date('Y-m-t'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="type_paiement" class="form-label">Type de paiement</label>
                            <select class="form-select" id="type_paiement" name="type_paiement">
                                <option value="">Tous les types</option>
                                <option value="scolarite" <?php echo ($filtres['type_paiement'] ?? '') == 'scolarite' ? 'selected' : ''; ?>>Scolarité</option>
                                <option value="inscription" <?php echo ($filtres['type_paiement'] ?? '') == 'inscription' ? 'selected' : ''; ?>>Inscription</option>
                                <option value="transport" <?php echo ($filtres['type_paiement'] ?? '') == 'transport' ? 'selected' : ''; ?>>Transport</option>
                                <option value="cantine" <?php echo ($filtres['type_paiement'] ?? '') == 'cantine' ? 'selected' : ''; ?>>Cantine</option>
                                <option value="autre" <?php echo ($filtres['type_paiement'] ?? '') == 'autre' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statut_paiement" class="form-label">Statut</label>
                            <select class="form-select" id="statut_paiement" name="statut_paiement">
                                <option value="">Tous les statuts</option>
                                <option value="paye" <?php echo ($filtres['statut_paiement'] ?? '') == 'paye' ? 'selected' : ''; ?>>Payé</option>
                                <option value="partiel" <?php echo ($filtres['statut_paiement'] ?? '') == 'partiel' ? 'selected' : ''; ?>>Partiel</option>
                                <option value="impaye" <?php echo ($filtres['statut_paiement'] ?? '') == 'impaye' ? 'selected' : ''; ?>>Impayé</option>
                                <option value="retard" <?php echo ($filtres['statut_paiement'] ?? '') == 'retard' ? 'selected' : ''; ?>>En retard</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="/rapports?action=rapport_financier" class="btn btn-secondary ms-2">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Payé</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_finances['total_paye'] ?? 0, 0, ',', ' '); ?> FC</h4>
                                    <small><?php echo number_format($statistiques_finances['paiements_mois'] ?? 0, 0, ',', ' '); ?> FC ce mois</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Montant Restant</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_finances['total_impaye'] ?? 0, 0, ',', ' '); ?> FC</h4>
                                    <small><?php echo $statistiques_finances['paiements_retard'] ?? 0; ?> en retard</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Taux de Paiement</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_finances['taux_paiement'] ?? 0, 1); ?>%</h4>
                                    <small><?php echo $statistiques_finances['total_paiements'] ?? 0; ?> paiements</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-percentage fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Paiement Moyen</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_finances['paiement_moyen'] ?? 0, 0, ',', ' '); ?> FC</h4>
                                    <small>Par élève</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calculator fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Répartition par Type de Paiement
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-types-paiements" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Évolution Mensuelle des Paiements
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-evolution-paiements" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>État des Paiements
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-statut-paiements" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-area me-2"></i>Paiements par Classe
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-paiements-classes" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertes financières -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Alertes Financières
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (!empty($alertes_financieres)): ?>
                                    <?php foreach ($alertes_financieres as $alerte): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-<?php echo $alerte['type']; ?>">
                                                <h6><?php echo htmlspecialchars($alerte['titre']); ?></h6>
                                                <p class="mb-2"><?php echo htmlspecialchars($alerte['description']); ?></p>
                                                <small class="text-muted">
                                                    <?php echo $alerte['nombre_concernes']; ?> élève(s) concerné(s)
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Aucune alerte financière détectée.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détail des paiements -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Détail des Paiements
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-paiements">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Élève</th>
                                            <th>Type</th>
                                            <th>Montant Total</th>
                                            <th>Montant Payé</th>
                                            <th>Reste à Payer</th>
                                            <th>Statut</th>
                                            <th>Dernier Paiement</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($liste_paiements)): ?>
                                            <?php foreach ($liste_paiements as $paiement): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($paiement['id']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($paiement['nom_eleve'] . ' ' . $paiement['prenoms_eleve']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($paiement['nom_classe'] ?? 'Non assigné'); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php
                                                            echo match($paiement['type_paiement']) {
                                                                'scolarite' => 'primary',
                                                                'inscription' => 'success',
                                                                'transport' => 'warning',
                                                                'cantine' => 'info',
                                                                default => 'secondary'
                                                            };
                                                        ?>">
                                                            <?php echo ucfirst($paiement['type_paiement']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo number_format($paiement['montant_total'], 0, ',', ' '); ?> FC</td>
                                                    <td class="text-success fw-bold"><?php echo number_format($paiement['montant_paye'], 0, ',', ' '); ?> FC</td>
                                                    <td class="text-danger"><?php echo number_format($paiement['montant_restant'], 0, ',', ' '); ?> FC</td>
                                                    <td>
                                                        <span class="badge bg-<?php
                                                            echo match($paiement['statut']) {
                                                                'paye' => 'success',
                                                                'partiel' => 'warning',
                                                                'impaye' => 'danger',
                                                                'retard' => 'dark',
                                                                default => 'secondary'
                                                            };
                                                        ?>">
                                                            <?php echo ucfirst($paiement['statut']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $paiement['dernier_paiement'] ? date('d/m/Y', strtotime($paiement['dernier_paiement'])) : 'Aucun'; ?></td>
                                                    <td>
                                                        <a href="/finance?action=detail_paiement&id=<?php echo $paiement['id']; ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>Aucun paiement trouvé avec les filtres actuels.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Pagination des paiements" class="mt-3">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?action=rapport_financier&page=<?php echo $page - 1; ?>&<?php echo http_build_query($filtres); ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?action=rapport_financier&page=<?php echo $i; ?>&<?php echo http_build_query($filtres); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?action=rapport_financier&page=<?php echo $page + 1; ?>&<?php echo http_build_query($filtres); ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Résumé par période -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>Résumé par Période
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Période</th>
                                            <th>Nombre de Paiements</th>
                                            <th>Montant Total</th>
                                            <th>Montant Payé</th>
                                            <th>Montant Restant</th>
                                            <th>Taux de Paiement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($resume_periodes)): ?>
                                            <?php foreach ($resume_periodes as $periode): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($periode['periode']); ?></strong></td>
                                                    <td><?php echo number_format($periode['nombre_paiements']); ?></td>
                                                    <td><?php echo number_format($periode['montant_total'], 0, ',', ' '); ?> FC</td>
                                                    <td class="text-success"><?php echo number_format($periode['montant_paye'], 0, ',', ' '); ?> FC</td>
                                                    <td class="text-danger"><?php echo number_format($periode['montant_restant'], 0, ',', ' '); ?> FC</td>
                                                    <td><?php echo number_format($periode['taux_paiement'], 1); ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>Aucune donnée disponible pour les périodes.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<script>
// Données pour les graphiques
const typesPaiementsData = <?php echo json_encode($graphiques['types_paiements'] ?? []); ?>;
const evolutionPaiementsData = <?php echo json_encode($graphiques['evolution_paiements'] ?? []); ?>;
const statutPaiementsData = <?php echo json_encode($graphiques['statut_paiements'] ?? []); ?>;
const paiementsClassesData = <?php echo json_encode($graphiques['paiements_classes'] ?? []); ?>;

// Graphique types de paiements
const chartTypesPaiements = echarts.init(document.getElementById('chart-types-paiements'));
const optionTypesPaiements = {
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    series: [{
        name: 'Types de paiements',
        type: 'pie',
        radius: '50%',
        data: typesPaiementsData,
        emphasis: {
            itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
        }
    }]
};
chartTypesPaiements.setOption(optionTypesPaiements);

// Graphique évolution des paiements
const chartEvolutionPaiements = echarts.init(document.getElementById('chart-evolution-paiements'));
const optionEvolutionPaiements = {
    tooltip: {
        trigger: 'axis',
        formatter: function(params) {
            return params[0].name + '<br/>' +
                   'Montant: ' + params[0].value.toLocaleString('fr-FR') + ' FC';
        }
    },
    xAxis: {
        type: 'category',
        data: evolutionPaiementsData.map(item => item.mois)
    },
    yAxis: {
        type: 'value',
        axisLabel: {
            formatter: function(value) {
                return (value / 1000).toFixed(0) + 'k';
            }
        }
    },
    series: [{
        name: 'Paiements',
        type: 'bar',
        data: evolutionPaiementsData.map(item => item.montant_paye),
        itemStyle: { color: '#ffc107' }
    }]
};
chartEvolutionPaiements.setOption(optionEvolutionPaiements);

// Graphique statut des paiements
const chartStatutPaiements = echarts.init(document.getElementById('chart-statut-paiements'));
const optionStatutPaiements = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: statutPaiementsData.map(item => item.statut)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Nombre de paiements',
        type: 'line',
        data: statutPaiementsData.map(item => item.nombre),
        smooth: true,
        itemStyle: { color: '#28a745' }
    }]
};
chartStatutPaiements.setOption(optionStatutPaiements);

// Graphique paiements par classe
const chartPaiementsClasses = echarts.init(document.getElementById('chart-paiements-classes'));
const optionPaiementsClasses = {
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow'
        }
    },
    xAxis: {
        type: 'category',
        data: paiementsClassesData.map(item => item.nom_classe)
    },
    yAxis: {
        type: 'value',
        axisLabel: {
            formatter: function(value) {
                return (value / 1000).toFixed(0) + 'k';
            }
        }
    },
    series: [{
        name: 'Montant payé',
        type: 'bar',
        data: paiementsClassesData.map(item => item.montant_paye),
        itemStyle: { color: '#17a2b8' }
    }]
};
chartPaiementsClasses.setOption(optionPaiementsClasses);

// Redimensionnement automatique
window.addEventListener('resize', function() {
    chartTypesPaiements.resize();
    chartEvolutionPaiements.resize();
    chartStatutPaiements.resize();
    chartPaiementsClasses.resize();
});

// Fonctions d'export
function exporterPDF() {
    window.open('/rapports?action=export_pdf&type=financier', '_blank');
}

function exporterExcel() {
    window.open('/rapports?action=export_excel&type=financier', '_blank');
}
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>