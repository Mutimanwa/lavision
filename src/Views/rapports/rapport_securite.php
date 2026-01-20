<?php
/**
 * Vue du rapport de sécurité
 * Affiche les statistiques détaillées sur la sécurité et activité système
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
                        <i class="fas fa-shield-alt text-info me-2"></i>
                        Rapport de Sécurité
                    </h1>
                    <p class="text-muted">Statistiques détaillées sur la sécurité et activité système</p>
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
                        <input type="hidden" name="action" value="rapport_securite">
                        <div class="col-md-3">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut"
                                   value="<?php echo $filtres['date_debut'] ?? date('Y-m-d', strtotime('-30 days')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin"
                                   value="<?php echo $filtres['date_fin'] ?? date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="niveau_securite" class="form-label">Niveau de sécurité</label>
                            <select class="form-select" id="niveau_securite" name="niveau_securite">
                                <option value="">Tous les niveaux</option>
                                <option value="faible" <?php echo ($filtres['niveau_securite'] ?? '') == 'faible' ? 'selected' : ''; ?>>Faible</option>
                                <option value="moyen" <?php echo ($filtres['niveau_securite'] ?? '') == 'moyen' ? 'selected' : ''; ?>>Moyen</option>
                                <option value="eleve" <?php echo ($filtres['niveau_securite'] ?? '') == 'eleve' ? 'selected' : ''; ?>>Élevé</option>
                                <option value="critique" <?php echo ($filtres['niveau_securite'] ?? '') == 'critique' ? 'selected' : ''; ?>>Critique</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type_evenement" class="form-label">Type d'événement</label>
                            <select class="form-select" id="type_evenement" name="type_evenement">
                                <option value="">Tous les types</option>
                                <option value="connexion" <?php echo ($filtres['type_evenement'] ?? '') == 'connexion' ? 'selected' : ''; ?>>Connexion</option>
                                <option value="deconnexion" <?php echo ($filtres['type_evenement'] ?? '') == 'deconnexion' ? 'selected' : ''; ?>>Déconnexion</option>
                                <option value="action" <?php echo ($filtres['type_evenement'] ?? '') == 'action' ? 'selected' : ''; ?>>Action</option>
                                <option value="erreur" <?php echo ($filtres['type_evenement'] ?? '') == 'erreur' ? 'selected' : ''; ?>>Erreur</option>
                                <option value="securite" <?php echo ($filtres['type_evenement'] ?? '') == 'securite' ? 'selected' : ''; ?>>Sécurité</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="/rapports?action=rapport_securite" class="btn btn-secondary ms-2">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Alertes de sécurité -->
            <div class="row mb-4">
                <?php if (!empty($alertes_securite)): ?>
                    <?php foreach ($alertes_securite as $alerte): ?>
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-<?php echo $alerte['type']; ?> alert-dismissible fade show">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-<?php echo $alerte['icone']; ?> fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="alert-heading"><?php echo htmlspecialchars($alerte['titre']); ?></h6>
                                        <p class="mb-0"><?php echo htmlspecialchars($alerte['description']); ?></p>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($alerte['date'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Connexions (24h)</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_securite['connexions_24h'] ?? 0); ?></h4>
                                    <small><?php echo $statistiques_securite['utilisateurs_actifs'] ?? 0; ?> utilisateurs actifs</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-sign-in-alt fa-2x opacity-75"></i>
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
                                    <h6 class="card-title">Actions Système</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_securite['actions_total'] ?? 0); ?></h4>
                                    <small><?php echo number_format($statistiques_securite['actions_moyenne_jour'] ?? 0, 1); ?>/jour</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-cogs fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Échecs Connexion</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_securite['echecs_connexion'] ?? 0); ?></h4>
                                    <small><?php echo number_format($statistiques_securite['taux_echec_connexion'] ?? 0, 1); ?>% du total</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                                    <h6 class="card-title">Erreurs Système</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_securite['erreurs_total'] ?? 0); ?></h4>
                                    <small><?php echo $statistiques_securite['erreurs_critiques'] ?? 0; ?> critiques</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-bug fa-2x opacity-75"></i>
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
                                <i class="fas fa-chart-line me-2"></i>Activité Utilisateurs (7 derniers jours)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-activite-utilisateurs" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Répartition des Événements
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-types-evenements" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Échecs de Connexion par Jour
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-echecs-connexion" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-area me-2"></i>Erreurs Système par Type
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-erreurs-systeme" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journal des événements récents -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Événements Récents (24 dernières heures)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date/Heure</th>
                                            <th>Utilisateur</th>
                                            <th>Type</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Adresse IP</th>
                                            <th>Niveau</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($evenements_recents)): ?>
                                            <?php foreach ($evenements_recents as $evenement): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i:s', strtotime($evenement['date_heure'])); ?></td>
                                                    <td>
                                                        <?php if ($evenement['id_utilisateur']): ?>
                                                            <a href="/utilisateur?action=detail&id=<?php echo $evenement['id_utilisateur']; ?>">
                                                                <?php echo htmlspecialchars($evenement['nom_utilisateur']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">Système</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php
                                                            echo match($evenement['type_evenement']) {
                                                                'connexion' => 'success',
                                                                'deconnexion' => 'secondary',
                                                                'action' => 'primary',
                                                                'erreur' => 'warning',
                                                                'securite' => 'danger',
                                                                default => 'light'
                                                            };
                                                        ?>">
                                                            <?php echo ucfirst($evenement['type_evenement']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($evenement['action']); ?></td>
                                                    <td><?php echo htmlspecialchars($evenement['description']); ?></td>
                                                    <td><code><?php echo htmlspecialchars($evenement['adresse_ip']); ?></code></td>
                                                    <td>
                                                        <span class="badge bg-<?php
                                                            echo match($evenement['niveau_securite']) {
                                                                'faible' => 'success',
                                                                'moyen' => 'warning',
                                                                'eleve' => 'danger',
                                                                'critique' => 'dark',
                                                                default => 'light'
                                                            };
                                                        ?>">
                                                            <?php echo ucfirst($evenement['niveau_securite']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>Aucun événement récent trouvé.
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

            <!-- Statistiques de sécurité détaillées -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-shield me-2"></i>Utilisateurs les Plus Actifs
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Utilisateur</th>
                                            <th>Actions (24h)</th>
                                            <th>Dernière Activité</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($utilisateurs_actifs)): ?>
                                            <?php foreach ($utilisateurs_actifs as $utilisateur): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($utilisateur['nom'] . ' ' . $utilisateur['prenoms']); ?></td>
                                                    <td><?php echo number_format($utilisateur['nombre_actions']); ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($utilisateur['derniere_activite'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Aucune donnée disponible</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Heures de Connexion
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-heures-connexion" style="height: 250px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommandations de sécurité -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>Recommandations de Sécurité
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (!empty($recommandations_securite)): ?>
                                    <?php foreach ($recommandations_securite as $recommandation): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="alert alert-<?php echo $recommandation['type']; ?>">
                                                <h6><?php echo htmlspecialchars($recommandation['titre']); ?></h6>
                                                <p class="mb-2"><?php echo htmlspecialchars($recommandation['description']); ?></p>
                                                <div class="d-flex justify-content-between">
                                                    <small class="text-muted">
                                                        Priorité: <?php echo ucfirst($recommandation['priorite']); ?>
                                                    </small>
                                                    <small class="text-muted">
                                                        Impact: <?php echo ucfirst($recommandation['impact']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Toutes les mesures de sécurité sont conformes aux bonnes pratiques.
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
const activiteUtilisateursData = <?php echo json_encode($graphiques['activite_utilisateurs'] ?? []); ?>;
const typesEvenementsData = <?php echo json_encode($graphiques['types_evenements'] ?? []); ?>;
const echecsConnexionData = <?php echo json_encode($graphiques['echecs_connexion'] ?? []); ?>;
const erreursSystemeData = <?php echo json_encode($graphiques['erreurs_systeme'] ?? []); ?>;
const heuresConnexionData = <?php echo json_encode($graphiques['heures_connexion'] ?? []); ?>;

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
        type: 'line',
        data: activiteUtilisateursData.map(item => item.nombre_actions),
        smooth: true,
        itemStyle: { color: '#17a2b8' },
        areaStyle: { color: '#17a2b8', opacity: 0.3 }
    }]
};
chartActiviteUtilisateurs.setOption(optionActiviteUtilisateurs);

// Graphique types d'événements
const chartTypesEvenements = echarts.init(document.getElementById('chart-types-evenements'));
const optionTypesEvenements = {
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    series: [{
        name: 'Types d\'événements',
        type: 'pie',
        radius: '50%',
        data: typesEvenementsData,
        emphasis: {
            itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
        }
    }]
};
chartTypesEvenements.setOption(optionTypesEvenements);

// Graphique échecs de connexion
const chartEchecsConnexion = echarts.init(document.getElementById('chart-echecs-connexion'));
const optionEchecsConnexion = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: echecsConnexionData.map(item => item.jour)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Échecs',
        type: 'bar',
        data: echecsConnexionData.map(item => item.nombre_echecs),
        itemStyle: { color: '#ffc107' }
    }]
};
chartEchecsConnexion.setOption(optionEchecsConnexion);

// Graphique erreurs système
const chartErreursSysteme = echarts.init(document.getElementById('chart-erreurs-systeme'));
const optionErreursSysteme = {
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow'
        }
    },
    xAxis: {
        type: 'category',
        data: erreursSystemeData.map(item => item.type_erreur)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Nombre d\'erreurs',
        type: 'bar',
        data: erreursSystemeData.map(item => item.nombre),
        itemStyle: { color: '#dc3545' }
    }]
};
chartErreursSysteme.setOption(optionErreursSysteme);

// Graphique heures de connexion
const chartHeuresConnexion = echarts.init(document.getElementById('chart-heures-connexion'));
const optionHeuresConnexion = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: heuresConnexionData.map(item => item.heure)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Connexions',
        type: 'line',
        data: heuresConnexionData.map(item => item.nombre_connexions),
        smooth: true,
        itemStyle: { color: '#28a745' }
    }]
};
chartHeuresConnexion.setOption(optionHeuresConnexion);

// Redimensionnement automatique
window.addEventListener('resize', function() {
    chartActiviteUtilisateurs.resize();
    chartTypesEvenements.resize();
    chartEchecsConnexion.resize();
    chartErreursSysteme.resize();
    chartHeuresConnexion.resize();
});

// Fonctions d'export
function exporterPDF() {
    window.open('/rapports?action=export_pdf&type=securite', '_blank');
}

function exporterExcel() {
    window.open('/rapports?action=export_excel&type=securite', '_blank');
}
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>