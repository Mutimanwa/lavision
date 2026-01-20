<?php
/**
 * Vue du rapport des élèves
 * Affiche les statistiques détaillées sur les élèves
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
                        <i class="fas fa-users text-primary me-2"></i>
                        Rapport des Élèves
                    </h1>
                    <p class="text-muted">Statistiques détaillées sur les élèves inscrits</p>
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
                        <input type="hidden" name="action" value="rapport_eleves">
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
                            <label for="classe" class="form-label">Classe</label>
                            <select class="form-select" id="classe" name="classe">
                                <option value="">Toutes les classes</option>
                                <?php foreach ($classes as $classe): ?>
                                    <option value="<?php echo $classe['id']; ?>"
                                            <?php echo ($filtres['classe'] ?? '') == $classe['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($classe['nom_classe']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="actif" <?php echo ($filtres['statut'] ?? '') == 'actif' ? 'selected' : ''; ?>>Actif</option>
                                <option value="inactif" <?php echo ($filtres['statut'] ?? '') == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                                <option value="suspendu" <?php echo ($filtres['statut'] ?? '') == 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                            <a href="/rapports?action=rapport_eleves" class="btn btn-secondary ms-2">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Élèves</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_eleves['total_eleves'] ?? 0); ?></h4>
                                    <small><?php echo $statistiques_eleves['eleves_actifs'] ?? 0; ?> actifs</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
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
                                    <h6 class="card-title">Nouveaux Élèves</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_eleves['nouveaux_eleves'] ?? 0); ?></h4>
                                    <small>Ce mois</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-plus fa-2x opacity-75"></i>
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
                                    <h6 class="card-title">Taux de Rétention</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_eleves['taux_retention'] ?? 0, 1); ?>%</h4>
                                    <small>Année en cours</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
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
                                    <h6 class="card-title">Moyenne par Classe</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_eleves['moyenne_par_classe'] ?? 0, 1); ?></h4>
                                    <small>Élèves/classe</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-school fa-2x opacity-75"></i>
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
                                <i class="fas fa-chart-pie me-2"></i>Répartition par Genre
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-genre" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

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
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Évolution des Inscriptions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-evolution" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-area me-2"></i>Répartition par Âge
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-age" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table détaillée -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>Détail des Élèves
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="table-eleves">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom & Prénoms</th>
                                            <th>Genre</th>
                                            <th>Date de Naissance</th>
                                            <th>Classe</th>
                                            <th>Statut</th>
                                            <th>Date d'Inscription</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($liste_eleves)): ?>
                                            <?php foreach ($liste_eleves as $eleve): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($eleve['id']); ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenoms']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $eleve['genre'] === 'M' ? 'primary' : 'success'; ?>">
                                                            <?php echo $eleve['genre'] === 'M' ? 'Masculin' : 'Féminin'; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($eleve['date_naissance'])); ?></td>
                                                    <td><?php echo htmlspecialchars($eleve['nom_classe'] ?? 'Non assigné'); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php
                                                            echo match($eleve['statut']) {
                                                                'actif' => 'success',
                                                                'inactif' => 'secondary',
                                                                'suspendu' => 'warning',
                                                                default => 'light'
                                                            };
                                                        ?>">
                                                            <?php echo ucfirst($eleve['statut']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($eleve['date_inscription'])); ?></td>
                                                    <td>
                                                        <a href="/eleves?action=detail&id=<?php echo $eleve['id']; ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>Aucun élève trouvé avec les filtres actuels.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Pagination des élèves" class="mt-3">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?action=rapport_eleves&page=<?php echo $page - 1; ?>&<?php echo http_build_query($filtres); ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?action=rapport_eleves&page=<?php echo $i; ?>&<?php echo http_build_query($filtres); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?action=rapport_eleves&page=<?php echo $page + 1; ?>&<?php echo http_build_query($filtres); ?>">
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
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<script>
// Données pour les graphiques
const genreData = <?php echo json_encode($graphiques['genre'] ?? []); ?>;
const classesData = <?php echo json_encode($graphiques['classes'] ?? []); ?>;
const evolutionData = <?php echo json_encode($graphiques['evolution'] ?? []); ?>;
const ageData = <?php echo json_encode($graphiques['age'] ?? []); ?>;

// Graphique répartition par genre
const chartGenre = echarts.init(document.getElementById('chart-genre'));
const optionGenre = {
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    series: [{
        name: 'Genre',
        type: 'pie',
        radius: '50%',
        data: genreData.map(item => ({
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
chartGenre.setOption(optionGenre);

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

// Graphique évolution des inscriptions
const chartEvolution = echarts.init(document.getElementById('chart-evolution'));
const optionEvolution = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: evolutionData.map(item => item.mois)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Inscriptions',
        type: 'line',
        data: evolutionData.map(item => item.nombre_inscriptions),
        smooth: true,
        itemStyle: { color: '#28a745' }
    }]
};
chartEvolution.setOption(optionEvolution);

// Graphique répartition par âge
const chartAge = echarts.init(document.getElementById('chart-age'));
const optionAge = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: ageData.map(item => item.tranche_age)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Nombre d\'élèves',
        type: 'bar',
        data: ageData.map(item => item.nombre_eleves),
        itemStyle: { color: '#17a2b8' }
    }]
};
chartAge.setOption(optionAge);

// Redimensionnement automatique
window.addEventListener('resize', function() {
    chartGenre.resize();
    chartClasses.resize();
    chartEvolution.resize();
    chartAge.resize();
});

// Fonctions d'export
function exporterPDF() {
    window.open('/rapports?action=export_pdf&type=eleves', '_blank');
}

function exporterExcel() {
    window.open('/rapports?action=export_excel&type=eleves', '_blank');
}
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>