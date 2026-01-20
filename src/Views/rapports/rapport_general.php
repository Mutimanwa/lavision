<?php
/**
 * Vue du rapport général du système
 * Affiche une vue d'ensemble complète des statistiques
 * Design responsive avec Bootstrap 5 et export PDF/Excel
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
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Rapport Général du Système
                    </h1>
                    <p class="text-muted">Vue d'ensemble complète des statistiques et indicateurs</p>
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
                        <input type="hidden" name="action" value="rapport_general">
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
                            <label for="annee_scolaire" class="form-label">Année scolaire</label>
                            <select class="form-select" id="annee_scolaire" name="annee_scolaire">
                                <option value="">Toutes les années</option>
                                <?php foreach ($annees_scolaires as $annee): ?>
                                    <option value="<?php echo $annee['id']; ?>"
                                            <?php echo ($filtres['annee_scolaire'] ?? '') == $annee['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($annee['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <a href="/rapports?action=rapport_general" class="btn btn-secondary ms-2">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Résumé général -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>Résumé Général
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-primary"><?php echo number_format($rapport_general['resume']['total_eleves'] ?? 0); ?></h3>
                                        <p class="text-muted mb-0">Total Élèves</p>
                                        <small class="text-success">
                                            +<?php echo $rapport_general['resume']['nouveaux_eleves'] ?? 0; ?> ce mois
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-success"><?php echo $rapport_general['resume']['total_classes'] ?? 0; ?></h3>
                                        <p class="text-muted mb-0">Classes Actives</p>
                                        <small class="text-info">
                                            <?php echo $rapport_general['resume']['total_matieres'] ?? 0; ?> matières
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-warning"><?php echo number_format($rapport_general['resume']['total_paiements'] ?? 0, 0, ',', ' '); ?> FC</h3>
                                        <p class="text-muted mb-0">Paiements Totaux</p>
                                        <small class="text-success">
                                            <?php echo number_format($rapport_general['resume']['paiements_mois'] ?? 0, 0, ',', ' '); ?> FC ce mois
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-info"><?php echo $rapport_general['resume']['total_utilisateurs'] ?? 0; ?></h3>
                                        <p class="text-muted mb-0">Utilisateurs Actifs</p>
                                        <small class="text-secondary">
                                            <?php echo $rapport_general['resume']['connexions_jour'] ?? 0; ?> connexions/jour
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques détaillées -->
            <div class="row mb-4">
                <!-- Statistiques des élèves -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Statistiques des Élèves
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Élèves actifs</td>
                                            <td class="text-end fw-bold"><?php echo number_format($rapport_general['eleves']['actifs'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Élèves inactifs</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['eleves']['inactifs'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Nouveaux élèves (mois)</td>
                                            <td class="text-end text-success fw-bold"><?php echo number_format($rapport_general['eleves']['nouveaux_mois'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Élèves par classe (moyenne)</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['eleves']['moyenne_par_classe'] ?? 0, 1); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Taux de rétention</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['eleves']['taux_retention'] ?? 0, 1); ?>%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques académiques -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-graduation-cap me-2"></i>Statistiques Académiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Classes actives</td>
                                            <td class="text-end fw-bold"><?php echo number_format($rapport_general['academique']['classes_actives'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Matières enseignées</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['academique']['matieres_actives'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Professeurs actifs</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['academique']['professeurs_actifs'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Emploi du temps (heures/semaine)</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['academique']['total_heures_semaine'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Taux d'occupation salles</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['academique']['taux_occupation_salles'] ?? 0, 1); ?>%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Statistiques financières -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-money-bill-wave me-2"></i>Statistiques Financières
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Paiements totaux</td>
                                            <td class="text-end fw-bold"><?php echo number_format($rapport_general['finances']['total_paye'] ?? 0, 0, ',', ' '); ?> FC</td>
                                        </tr>
                                        <tr>
                                            <td>Montant restant dû</td>
                                            <td class="text-end text-danger"><?php echo number_format($rapport_general['finances']['total_impaye'] ?? 0, 0, ',', ' '); ?> FC</td>
                                        </tr>
                                        <tr>
                                            <td>Paiements ce mois</td>
                                            <td class="text-end text-success"><?php echo number_format($rapport_general['finances']['paiements_mois'] ?? 0, 0, ',', ' '); ?> FC</td>
                                        </tr>
                                        <tr>
                                            <td>Taux de paiement</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['finances']['taux_paiement'] ?? 0, 1); ?>%</td>
                                        </tr>
                                        <tr>
                                            <td>Paiements en retard</td>
                                            <td class="text-end text-warning"><?php echo number_format($rapport_general['finances']['paiements_retard'] ?? 0); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques système -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-server me-2"></i>Statistiques Système
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Utilisateurs actifs</td>
                                            <td class="text-end fw-bold"><?php echo number_format($rapport_general['systeme']['utilisateurs_actifs'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Connexions (24h)</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['systeme']['connexions_24h'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Actions système (24h)</td>
                                            <td class="text-end"><?php echo number_format($rapport_general['systeme']['actions_24h'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Erreurs système (7j)</td>
                                            <td class="text-end text-danger"><?php echo number_format($rapport_general['systeme']['erreurs_7j'] ?? 0); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Uptime système</td>
                                            <td class="text-end"><?php echo $rapport_general['systeme']['uptime'] ?? 'N/A'; ?>%</td>
                                        </tr>
                                    </tbody>
                                </table>
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
                                <i class="fas fa-chart-bar me-2"></i>Répartition par Classe
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-classes" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>État des Paiements
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-paiements" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommandations -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>Recommandations
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (!empty($recommandations)): ?>
                                    <?php foreach ($recommandations as $recommandation): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-<?php echo $recommandation['type']; ?>">
                                                <h6><?php echo htmlspecialchars($recommandation['titre']); ?></h6>
                                                <p class="mb-0"><?php echo htmlspecialchars($recommandation['description']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Toutes les métriques sont dans les normes. Aucun problème détecté.
                                        </div>
                                    </div>
                                <?php endif; ?>
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
const classesData = <?php echo json_encode($graphiques['classes'] ?? []); ?>;
const paiementsData = <?php echo json_encode($graphiques['paiements'] ?? []); ?>;

// Graphique répartition par classe
const chartClasses = echarts.init(document.getElementById('chart-classes'));
const optionClasses = {
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow'
        }
    },
    xAxis: {
        type: 'category',
        data: classesData.map(item => item.nom_classe)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Nombre d\'élèves',
        type: 'bar',
        data: classesData.map(item => item.nombre_eleves),
        itemStyle: { color: '#007bff' }
    }]
};
chartClasses.setOption(optionClasses);

// Graphique état des paiements
const chartPaiements = echarts.init(document.getElementById('chart-paiements'));
const optionPaiements = {
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    series: [{
        name: 'Paiements',
        type: 'pie',
        radius: '50%',
        data: paiementsData,
        emphasis: {
            itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
        }
    }]
};
chartPaiements.setOption(optionPaiements);

// Redimensionnement automatique
window.addEventListener('resize', function() {
    chartClasses.resize();
    chartPaiements.resize();
});

// Fonctions d'export
function exporterPDF() {
    window.open('/rapports?action=export_pdf&type=general', '_blank');
}

function exporterExcel() {
    window.open('/rapports?action=export_excel&type=general', '_blank');
}
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>