<?php
/**
 * Vue du rapport académique
 * Affiche les statistiques détaillées sur les classes, matières et professeurs
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
                        <i class="fas fa-graduation-cap text-success me-2"></i>
                        Rapport Académique
                    </h1>
                    <p class="text-muted">Statistiques détaillées sur les classes, matières et professeurs</p>
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
                        <input type="hidden" name="action" value="rapport_academique">
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
                            <label for="type_matiere" class="form-label">Type de matière</label>
                            <select class="form-select" id="type_matiere" name="type_matiere">
                                <option value="">Tous les types</option>
                                <option value="scientifique" <?php echo ($filtres['type_matiere'] ?? '') == 'scientifique' ? 'selected' : ''; ?>>Scientifique</option>
                                <option value="litteraire" <?php echo ($filtres['type_matiere'] ?? '') == 'litteraire' ? 'selected' : ''; ?>>Littéraire</option>
                                <option value="technique" <?php echo ($filtres['type_matiere'] ?? '') == 'technique' ? 'selected' : ''; ?>>Technique</option>
                                <option value="artistique" <?php echo ($filtres['type_matiere'] ?? '') == 'artistique' ? 'selected' : ''; ?>>Artistique</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statut_classe" class="form-label">Statut classe</label>
                            <select class="form-select" id="statut_classe" name="statut_classe">
                                <option value="">Tous les statuts</option>
                                <option value="active" <?php echo ($filtres['statut_classe'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($filtres['statut_classe'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                                <a href="/rapports?action=rapport_academique" class="btn btn-secondary ms-2">
                                    <i class="fas fa-undo me-2"></i>Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Classes Actives</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_academique['total_classes'] ?? 0); ?></h4>
                                    <small><?php echo $statistiques_academique['classes_annee_courante'] ?? 0; ?> cette année</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-school fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Matières</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_academique['total_matieres'] ?? 0); ?></h4>
                                    <small><?php echo $statistiques_academique['matieres_actives'] ?? 0; ?> actives</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-book fa-2x opacity-75"></i>
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
                                    <h6 class="card-title">Professeurs</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_academique['total_professeurs'] ?? 0); ?></h4>
                                    <small><?php echo $statistiques_academique['professeurs_actifs'] ?? 0; ?> actifs</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chalkboard-teacher fa-2x opacity-75"></i>
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
                                    <h6 class="card-title">Heures/Semaine</h6>
                                    <h4 class="mb-0"><?php echo number_format($statistiques_academique['total_heures_semaine'] ?? 0); ?></h4>
                                    <small><?php echo number_format($statistiques_academique['moyenne_heures_classe'] ?? 0, 1); ?>h/classe</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
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
                                <i class="fas fa-chart-bar me-2"></i>Répartition par Type de Matière
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-types-matieres" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Charge de Travail par Professeur
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-charge-professeurs" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Évolution des Classes
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-evolution-classes" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-area me-2"></i>Occupation des Salles
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="chart-occupation-salles" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails des classes -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-school me-2"></i>Détail des Classes
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Classe</th>
                                            <th>Niveau</th>
                                            <th>Année Scolaire</th>
                                            <th>Élèves</th>
                                            <th>Matières</th>
                                            <th>Professeurs</th>
                                            <th>Heures/Semaine</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($details_classes)): ?>
                                            <?php foreach ($details_classes as $classe): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($classe['nom_classe']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($classe['niveau']); ?></td>
                                                    <td><?php echo htmlspecialchars($classe['annee_scolaire']); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $classe['nombre_eleves']; ?> élèves</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo $classe['nombre_matieres']; ?> matières</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning"><?php echo $classe['nombre_professeurs']; ?> profs</span>
                                                    </td>
                                                    <td><?php echo $classe['heures_semaine']; ?>h</td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $classe['statut'] === 'active' ? 'success' : 'secondary'; ?>">
                                                            <?php echo ucfirst($classe['statut']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="/academique?action=detail_classe&id=<?php echo $classe['id']; ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>Aucune classe trouvée avec les filtres actuels.
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

            <!-- Détails des matières -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-book me-2"></i>Détail des Matières
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Matière</th>
                                            <th>Code</th>
                                            <th>Type</th>
                                            <th>Coefficient</th>
                                            <th>Classes</th>
                                            <th>Professeurs</th>
                                            <th>Heures Totales</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($details_matieres)): ?>
                                            <?php foreach ($details_matieres as $matiere): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($matiere['nom_matiere']); ?></strong>
                                                    </td>
                                                    <td><code><?php echo htmlspecialchars($matiere['code_matiere']); ?></code></td>
                                                    <td>
                                                        <span class="badge bg-<?php
                                                            echo match($matiere['type_matiere']) {
                                                                'scientifique' => 'primary',
                                                                'litteraire' => 'success',
                                                                'technique' => 'warning',
                                                                'artistique' => 'info',
                                                                default => 'secondary'
                                                            };
                                                        ?>">
                                                            <?php echo ucfirst($matiere['type_matiere']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $matiere['coefficient']; ?></td>
                                                    <td><?php echo $matiere['nombre_classes']; ?> classes</td>
                                                    <td><?php echo $matiere['nombre_professeurs']; ?> profs</td>
                                                    <td><?php echo $matiere['heures_totales']; ?>h</td>
                                                    <td>
                                                        <a href="/academique?action=detail_matiere&id=<?php echo $matiere['id']; ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>Aucune matière trouvée avec les filtres actuels.
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

            <!-- Détails des professeurs -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Détail des Professeurs
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Professeur</th>
                                            <th>Spécialité</th>
                                            <th>Classes</th>
                                            <th>Matières</th>
                                            <th>Heures/Semaine</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($details_professeurs)): ?>
                                            <?php foreach ($details_professeurs as $prof): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['prenoms']); ?></strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($prof['specialite'] ?? 'Non définie'); ?></td>
                                                    <td><?php echo $prof['nombre_classes']; ?> classes</td>
                                                    <td><?php echo $prof['nombre_matieres']; ?> matières</td>
                                                    <td><?php echo $prof['heures_semaine']; ?>h</td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $prof['statut'] === 'actif' ? 'success' : 'secondary'; ?>">
                                                            <?php echo ucfirst($prof['statut']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="/personnel?action=detail_professeur&id=<?php echo $prof['id']; ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>Aucun professeur trouvé avec les filtres actuels.
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
const typesMatieresData = <?php echo json_encode($graphiques['types_matieres'] ?? []); ?>;
const chargeProfesseursData = <?php echo json_encode($graphiques['charge_professeurs'] ?? []); ?>;
const evolutionClassesData = <?php echo json_encode($graphiques['evolution_classes'] ?? []); ?>;
const occupationSallesData = <?php echo json_encode($graphiques['occupation_salles'] ?? []); ?>;

// Graphique types de matières
const chartTypesMatieres = echarts.init(document.getElementById('chart-types-matieres'));
const optionTypesMatieres = {
    tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'shadow'
        }
    },
    xAxis: {
        type: 'category',
        data: typesMatieresData.map(item => item.type)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Nombre de matières',
        type: 'bar',
        data: typesMatieresData.map(item => item.nombre),
        itemStyle: { color: '#28a745' }
    }]
};
chartTypesMatieres.setOption(optionTypesMatieres);

// Graphique charge des professeurs
const chartChargeProfesseurs = echarts.init(document.getElementById('chart-charge-professeurs'));
const optionChargeProfesseurs = {
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} heures ({d}%)'
    },
    series: [{
        name: 'Charge de travail',
        type: 'pie',
        radius: '50%',
        data: chargeProfesseursData,
        emphasis: {
            itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
        }
    }]
};
chartChargeProfesseurs.setOption(optionChargeProfesseurs);

// Graphique évolution des classes
const chartEvolutionClasses = echarts.init(document.getElementById('chart-evolution-classes'));
const optionEvolutionClasses = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: evolutionClassesData.map(item => item.annee)
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        name: 'Nombre de classes',
        type: 'line',
        data: evolutionClassesData.map(item => item.nombre_classes),
        smooth: true,
        itemStyle: { color: '#ffc107' }
    }]
};
chartEvolutionClasses.setOption(optionEvolutionClasses);

// Graphique occupation des salles
const chartOccupationSalles = echarts.init(document.getElementById('chart-occupation-salles'));
const optionOccupationSalles = {
    tooltip: {
        trigger: 'axis'
    },
    xAxis: {
        type: 'category',
        data: occupationSallesData.map(item => item.jour)
    },
    yAxis: {
        type: 'value',
        axisLabel: {
            formatter: '{value}%'
        }
    },
    series: [{
        name: 'Taux d\'occupation',
        type: 'area',
        data: occupationSallesData.map(item => item.taux_occupation),
        smooth: true,
        itemStyle: { color: '#17a2b8' },
        areaStyle: { color: '#17a2b8', opacity: 0.3 }
    }]
};
chartOccupationSalles.setOption(optionOccupationSalles);

// Redimensionnement automatique
window.addEventListener('resize', function() {
    chartTypesMatieres.resize();
    chartChargeProfesseurs.resize();
    chartEvolutionClasses.resize();
    chartOccupationSalles.resize();
});

// Fonctions d'export
function exporterPDF() {
    window.open('/rapports?action=export_pdf&type=academique', '_blank');
}

function exporterExcel() {
    window.open('/rapports?action=export_excel&type=academique', '_blank');
}
</script>

<?php
// Inclusion du footer
require_once TEMPLATES_PATH . '/footer.php';
?>