<?php
/**
 * Vue du tableau de bord des rapports
 * Affiche les statistiques principales et graphiques du système
 * Design responsive avec Bootstrap 5 et graphiques ECharts
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
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Tableau de Bord & Rapports
                    </h1>
                    <p class="text-muted">Vue d'ensemble des statistiques et indicateurs clés</p>
                </div>
                <div>
                    <small class="text-muted">
                        Dernière mise à jour: <?php echo $date_mise_a_jour; ?>
                    </small>
                </div>
            </div>

            <!-- Alertes importantes -->
            <?php if (!empty($alertes)): ?>
                <div class="row mb-4">
                    <?php if ($alertes['eleves_sans_classe'] > 0): ?>
                        <div class="col-md-3">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong><?php echo $alertes['eleves_sans_classe']; ?> élève(s)</strong> sans classe assignée
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($alertes['paiements_en_retard'] > 0): ?>
                        <div class="col-md-3">
                            <div class="alert alert-danger">
                                <i class="fas fa-clock me-2"></i>
                                <strong><?php echo $alertes['paiements_en_retard']; ?> paiement(s)</strong> en retard
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($alertes['professeurs_sans_matiere'] > 0): ?>
                        <div class="col-md-3">
                            <div class="alert alert-info">
                                <i class="fas fa-user-graduate me-2"></i>
                                <strong><?php echo $alertes['professeurs_sans_matiere']; ?> professeur(s)</strong> sans matière
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($alertes['erreurs_recent'] > 0): ?>
                        <div class="col-md-3">
                            <div class="alert alert-dark">
                                <i class="fas fa-bug me-2"></i>
                                <strong><?php echo $alertes['erreurs_recent']; ?> erreur(s)</strong> système récente(s)
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Statistiques principales -->
            <div class="row mb-4">
                <!-- Élèves -->
                <div class="col-md-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Élèves</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_generales['statistiques']['eleves']['total_eleves'] ?? 0); ?></h4>
                                    <small>
                                        <?php echo $statistiques_generales['statistiques']['eleves']['eleves_actifs'] ?? 0; ?> actifs,
                                        <?php echo $statistiques_generales['statistiques']['eleves']['nouveaux_eleves_mois'] ?? 0; ?> ce mois
                                    </small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Académique -->
                <div class="col-md-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Classes & Matières</h6>
                                    <h4 class="mb-0"><?php echo $statistiques_generales['statistiques']['academique']['total_classes'] ?? 0; ?></h4>
                                    <small>
                                        <?php echo $statistiques_generales['statistiques']['academique']['total_matieres'] ?? 0; ?> matières,
                                        <?php echo $statistiques_generales['statistiques']['academique']['total_professeurs'] ?? 0; ?> profs
                                    </small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-graduation-cap fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Finances -->
                <div class="col-md-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Paiements</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_generales['statistiques']['finances']['total_paye'] ?? 0, 0, ',', ' '); ?> FC</h4>
                                    <small>
                                        Payé ce mois,
                                        <?php echo number_format($statistiques_generales['statistiques']['finances']['total_impaye'] ?? 0, 0, ',', ' '); ?> FC restant
                                    </small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Utilisateurs -->
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Utilisateurs Actifs</h6>
                                    <h4 class="mb-0"><?php echo $statistiques_generales['statistiques']['utilisateurs']['total_utilisateurs'] ?? 0; ?></h4>
                                    <small>
                                        <?php echo $statistiques_generales['statistiques']['utilisateurs']['connexions_recentes'] ?? 0; ?> connexions récentes
                                    </small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-shield fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="row mb-4">
                <!-- Évolution des élèves -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Évolution des Inscriptions (6 derniers mois)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-evolution-eleves" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Répartition par genre -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Répartition des Élèves par Genre
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-repartition-genre" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Paiements mensuels -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Paiements Mensuels (6 derniers mois)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-paiements-mensuels" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Activité utilisateurs -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-area me-2"></i>Activité Utilisateurs (7 derniers jours)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-activite-utilisateurs" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liens vers les rapports détaillés -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i>Rapports Détaillés
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                            <h5>Rapport des Élèves</h5>
                                            <p class="text-muted">Statistiques détaillées sur les élèves</p>
                                            <a href="/rapports?action=rapport_eleves" class="btn btn-primary">
                                                <i class="fas fa-eye me-2"></i>Voir le rapport
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-graduation-cap fa-3x text-success mb-3"></i>
                                            <h5>Rapport Académique</h5>
                                            <p class="text-muted">Classes, matières et professeurs</p>
                                            <a href="/rapports?action=rapport_academique" class="btn btn-success">
                                                <i class="fas fa-eye me-2"></i>Voir le rapport
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-money-bill-wave fa-3x text-warning mb-3"></i>
                                            <h5>Rapport Financier</h5>
                                            <p class="text-muted">Paiements et finances scolaires</p>
                                            <a href="/rapports?action=rapport_financier" class="btn btn-warning">
                                                <i class="fas fa-eye me-2"></i>Voir le rapport
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-shield-alt fa-3x text-info mb-3"></i>
                                            <h5>Rapport de Sécurité</h5>
                                            <p class="text-muted">Sécurité et activité système</p>
                                            <a href="/rapports?action=rapport_securite" class="btn btn-info">
                                                <i class="fas fa-eye me-2"></i>Voir le rapport
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-chart-pie fa-3x text-secondary mb-3"></i>
                                            <h5>Rapport Général</h5>
                                            <p class="text-muted">Vue d'ensemble complète du système</p>
                                            <a href="/rapports?action=rapport_general" class="btn btn-secondary">
                                                <i class="fas fa-eye me-2"></i>Voir le rapport
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
const evolutionElevesData = <?php echo json_encode($stats_graphiques['evolution_eleves'] ?? []); ?>;
const repartitionGenreData = <?php echo json_encode($stats_graphiques['repartition_genre'] ?? []); ?>;
const paiementsMensuelsData = <?php echo json_encode($stats_graphiques['paiements_mensuels'] ?? []); ?>;
const activiteUtilisateursData = <?php echo json_encode($stats_graphiques['activite_utilisateurs'] ?? []); ?>;

// Graphique évolution des élèves
const chartEvolutionEleves = echarts.init(document.getElementById('chart-evolution-eleves'));
const optionEvolutionEleves = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: evolutionElevesData.map(item => item.mois)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Inscriptions',
        type: 'line',
        data: evolutionElevesData.map(item => item.nombre_inscriptions),
        smooth: true,
        itemStyle: { color: '#007bff' }
    }]
};
chartEvolutionEleves.setOption(optionEvolutionEleves);

// Graphique répartition par genre
const chartRepartitionGenre = echarts.init(document.getElementById('chart-repartition-genre'));
const optionRepartitionGenre = {
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    series: [{
        name: 'Genre',
        type: 'pie',
        radius: '50%',
        data: repartitionGenreData.map(item => ({
            value: item.nombre,
            name: item.genre === 'M' ? 'Masculin' : 'Féminin'
        })),
        emphasis: {
            itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
        }
    }]
};
chartRepartitionGenre.setOption(optionRepartitionGenre);

// Graphique paiements mensuels
const chartPaiementsMensuels = echarts.init(document.getElementById('chart-paiements-mensuels'));
const optionPaiementsMensuels = {
    tooltip: {
        trigger: 'axis',
        formatter: function(params) {
            return params[0].name + '<br/>' +
                   'Montant: ' + params[0].value.toLocaleString('fr-FR') + ' FC';
        }
    },
    xAxis: {
        type: 'category',
        data: paiementsMensuelsData.map(item => item.mois)
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
        data: paiementsMensuelsData.map(item => item.total_paye),
        itemStyle: { color: '#ffc107' }
    }]
};
chartPaiementsMensuels.setOption(optionPaiementsMensuels);

// Graphique activité utilisateurs
const chartActiviteUtilisateurs = echarts.init(document.getElementById('chart-activite-utilisateurs'));
const optionActiviteUtilisateurs = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: activiteUtilisateursData.map(item => item.jour)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Actions',
        type: 'area',
        data: activiteUtilisateursData.map(item => item.nombre_actions),
        smooth: true,
        itemStyle: { color: '#17a2b8' },
        areaStyle: { color: '#17a2b8', opacity: 0.3 }
    }]
};
chartActiviteUtilisateurs.setOption(optionActiviteUtilisateurs);

// Redimensionnement automatique des graphiques
window.addEventListener('resize', function() {
    chartEvolutionEleves.resize();
    chartRepartitionGenre.resize();
    chartPaiementsMensuels.resize();
    chartActiviteUtilisateurs.resize();
});
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>