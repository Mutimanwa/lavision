<?php
/**
 * Rapport - Liste des élèves
 * Vue détaillée pour la génération du rapport des élèves
 */

// Inclure l'en-tête
require_once __DIR__ . '/../templates/header.php';

// Récupérer les données du contexte
$eleves = $eleves ?? [];
$filters = $filters ?? [];
$classes = $classes ?? [];
$niveaux = $niveaux ?? [];
$sections = $sections ?? [];

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
                    <i class="fas fa-users me-2"></i>
                    Rapport - Liste des élèves
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Tableau de bord</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/reports">Rapports</a></li>
                        <li class="breadcrumb-item active">Liste des élèves</li>
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

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtres du rapport
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo BASE_URL; ?>/reports/academiques/eleves" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="classe_id" class="form-label">Classe</label>
                                <select class="form-select" id="classe_id" name="classe_id">
                                    <option value="">Toutes les classes</option>
                                    <?php foreach ($classes as $classe): ?>
                                        <option value="<?php echo $classe['classe_id']; ?>"
                                                <?php echo ($filters['classe_id'] ?? '') == $classe['classe_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($classe['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="niveau_id" class="form-label">Niveau</label>
                                <select class="form-select" id="niveau_id" name="niveau_id">
                                    <option value="">Tous les niveaux</option>
                                    <?php foreach ($niveaux as $niveau): ?>
                                        <option value="<?php echo $niveau['niveau_id']; ?>"
                                                <?php echo ($filters['niveau_id'] ?? '') == $niveau['niveau_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($niveau['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="section_id" class="form-label">Section</label>
                                <select class="form-select" id="section_id" name="section_id">
                                    <option value="">Toutes les sections</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo $section['section_id']; ?>"
                                                <?php echo ($filters['section_id'] ?? '') == $section['section_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($section['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" <?php echo ($filters['statut'] ?? '') === 'actif' ? 'selected' : ''; ?>>Actif</option>
                                    <option value="inactif" <?php echo ($filters['statut'] ?? '') === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                                    <option value="diplome" <?php echo ($filters['statut'] ?? '') === 'diplome' ? 'selected' : ''; ?>>Diplômé</option>
                                    <option value="exclu" <?php echo ($filters['statut'] ?? '') === 'exclu' ? 'selected' : ''; ?>>Exclu</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date_naissance_debut" class="form-label">Âge entre</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_naissance_debut"
                                       name="date_naissance_debut"
                                       value="<?php echo $filters['date_naissance_debut'] ?? ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="date_naissance_fin" class="form-label">et</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_naissance_fin"
                                       name="date_naissance_fin"
                                       value="<?php echo $filters['date_naissance_fin'] ?? ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="sexe" class="form-label">Sexe</label>
                                <select class="form-select" id="sexe" name="sexe">
                                    <option value="">Tous</option>
                                    <option value="M" <?php echo ($filters['sexe'] ?? '') === 'M' ? 'selected' : ''; ?>>Masculin</option>
                                    <option value="F" <?php echo ($filters['sexe'] ?? '') === 'F' ? 'selected' : ''; ?>>Féminin</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>
                                        Appliquer les filtres
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>/reports/academiques/eleves" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title"><?php echo count($eleves); ?></h5>
                            <p class="card-text">Élèves trouvés</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-mars fa-2x text-info"></i>
                            </div>
                            <h5 class="card-title">
                                <?php
                                $garcons = array_filter($eleves, function($e) {
                                    return $e['sexe'] === 'M';
                                });
                                echo count($garcons);
                                ?>
                            </h5>
                            <p class="card-text">Garçons</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-venus fa-2x text-danger"></i>
                            </div>
                            <h5 class="card-title">
                                <?php
                                $filles = array_filter($eleves, function($e) {
                                    return $e['sexe'] === 'F';
                                });
                                echo count($filles);
                                ?>
                            </h5>
                            <p class="card-text">Filles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-percentage fa-2x text-success"></i>
                            </div>
                            <h5 class="card-title">
                                <?php
                                $actifs = array_filter($eleves, function($e) {
                                    return $e['statut'] === 'actif';
                                });
                                $total = count($eleves);
                                echo $total > 0 ? round((count($actifs) / $total) * 100, 1) : 0;
                                ?>%
                            </h5>
                            <p class="card-text">Taux d'activité</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions d'export -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Liste des élèves</h4>
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

            <!-- Tableau des élèves -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="elevesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Matricule</th>
                                    <th>Nom complet</th>
                                    <th>Sexe</th>
                                    <th>Date naissance</th>
                                    <th>Âge</th>
                                    <th>Classe</th>
                                    <th>Statut</th>
                                    <th>Date inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($eleves)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Aucun élève trouvé avec les critères sélectionnés
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($eleves as $eleve): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($eleve['eleve_id']); ?></td>
                                            <td><?php echo htmlspecialchars($eleve['matricule']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($eleve['nom'] . ' ' . $eleve['prenom']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $eleve['sexe'] === 'M' ? 'info' : 'danger'; ?>">
                                                    <?php echo $eleve['sexe'] === 'M' ? 'Masculin' : 'Féminin'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($eleve['date_naissance'])); ?></td>
                                            <td>
                                                <?php
                                                $age = date_diff(date_create($eleve['date_naissance']), date_create('today'))->y;
                                                echo $age . ' ans';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($eleve['classe_nom'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $eleve['statut'] === 'actif' ? 'success' :
                                                         ($eleve['statut'] === 'inactif' ? 'warning' :
                                                         ($eleve['statut'] === 'diplome' ? 'info' : 'danger'));
                                                ?>">
                                                    <?php
                                                    echo $eleve['statut'] === 'actif' ? 'Actif' :
                                                         ($eleve['statut'] === 'inactif' ? 'Inactif' :
                                                         ($eleve['statut'] === 'diplome' ? 'Diplômé' : 'Exclu'));
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($eleve['date_inscription'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary"
                                                            onclick="viewEleveDetails(<?php echo $eleve['eleve_id']; ?>)"
                                                            title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-info"
                                                            onclick="printEleveCard(<?php echo $eleve['eleve_id']; ?>)"
                                                            title="Imprimer carte">
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
</style>

<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Initialiser DataTable si beaucoup de données
    if ($('#elevesTable tbody tr').length > 50) {
        $('#elevesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json'
            },
            pageLength: 25,
            order: [[1, 'asc']]
        });
    }
});

// Fonction pour voir les détails d'un élève
function viewEleveDetails(eleveId) {
    window.open('<?php echo BASE_URL; ?>/eleves/details/' + eleveId, '_blank');
}

// Fonction pour imprimer la carte d'un élève
function printEleveCard(eleveId) {
    window.open('<?php echo BASE_URL; ?>/reports/eleve/card/' + eleveId, '_blank');
}

// Fonction pour exporter le rapport
function exportReport(format) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);

    // Ajouter le format d'export
    params.append('export', format);

    // Construire l'URL
    const url = '<?php echo BASE_URL; ?>/reports/academiques/eleves?' + params.toString();

    // Ouvrir dans un nouvel onglet
    window.open(url, '_blank');
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>