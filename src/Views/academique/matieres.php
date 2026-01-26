<?php
/**
 * Vue de gestion des matières
 * Interface complète pour gérer les matières scolaires
 * Interface responsive avec Bootstrap 5 et DataTables
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */
?>
<!-- Contenu principal -->
<div class="row">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-book text-primary me-2"></i>
                Gestion des Matières
            </h1>
            <p class="text-muted mt-1">Administration des matières enseignées</p>
        </div>
        <div>
            <a href="<?= url('academique/matieres/ajouter') ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Nouvelle Matière
            </a>
        </div>
    </div>

    <!-- Messages d'alerte -->
    <?php if (isset($_SESSION['message_succes'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['message_succes']; unset($_SESSION['message_succes']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['message_erreur'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['message_erreur']; unset($_SESSION['message_erreur']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtres de recherche
            </h6>
        </div>
        <div class="card-body">
            <form method="get" action="<?= url('academique/matieres'); ?>" id="form/academique/matieres" class="row g-3">
                <div class="col-md-4">
                    <label for="type" class="form-label">Type de matière</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tous les types</option>
                        <?php foreach ($data['types_matieres'] as $key => $type): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo ($data['filtres']['type'] == $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="niveau" class="form-label">Niveau</label>
                    <select class="form-select" id="niveau" name="niveau">
                        <option value="">Tous les niveaux</option>
                        <?php foreach ($data['niveaux'] as $niveau): ?>
                            <option value="<?php echo $niveau['niveau_id']; ?>"
                                    <?php echo ($data['filtres']['niveau'] == $niveau['niveau_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($niveau['nom_niveau']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="<?= url('academique/matieres') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des matières -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Liste des Matières
                <span class="badge bg-primary ms-2"><?php echo $data['pagination']['total_matieres']; ?></span>
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" onclick="exportMatieres()">
                    <i class="fas fa-download me-1"></i>
                    Exporter
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($data['matieres'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune matière trouvée</h5>
                    <p class="text-muted">Il n'y a pas de matières correspondant aux critères de recherche.</p>
                    <a href="<?php echo BASE_URL; ?>/academique/matieres/ajouter" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Créer la première matière
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="matieresTable">
                        <thead class="table-light">
                            <tr>
                                <th>Matière</th>
                                <th>Code</th>
                                <!-- <th>Type</th> -->
                                <th>Niveau</th>
                                <th>Coefficient</th>
                                <th>Heures/Semaine</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['matieres'] as $matiere): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-success text-white me-3">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($matiere['nom_matiere']); ?></strong>
                                                <?php if ($matiere['description']): ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($matiere['description'], 0, 50)); ?>...</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($matiere['code_matiere']); ?></code>
                                    </td>
                                    <!-- <td>
                                        <?php
                                        $badgeClass = match($matiere['type_matiere']) {
                                            'fondamentale' => 'bg-primary',
                                            'optionnelle' => 'bg-warning text-dark',
                                            'specialisee' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($data['types_matieres'][$matiere['type_matiere']] ?? $matiere['type_matiere']); ?>
                                        </span>
                                    </td> -->
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($matiere['nom_niveau'] ?? 'Non défini'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo $matiere['coefficient']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($matiere['heures_semaine']): ?>
                                            <span class="text-success">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo $matiere['heures_semaine']; ?>h
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo url('academique/matieres/voir', ['matiere_id' => $matiere['matiere_id']]) ?>"
                                                class="btn btn-outline-info" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo url('academique/matieres/modifier',['matiere_id' => $matiere['matiere_id']]); ?>"
                                                class="btn btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="confirmerSuppression(<?php echo $matiere['matiere_id']; ?>, '<?php echo htmlspecialchars($matiere['nom_matiere']); ?>')"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($data['pagination']['total_pages'] > 1): ?>
                    <nav aria-label="Pagination des matières" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($data['pagination']['page_actuelle'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo url('academique/matieres', array_merge($data['filtres'], ['page' => $data['pagination']['page_actuelle'] - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $data['pagination']['page_actuelle'] - 2); $i <= min($data['pagination']['total_pages'], $data['pagination']['page_actuelle'] + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $data['pagination']['page_actuelle']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo url('academique/matieres', array_merge($data['filtres'], ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($data['pagination']['page_actuelle'] < $data['pagination']['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo url('academique/matieres', array_merge($data['filtres'], ['page' => $data['pagination']['page_actuelle'] + 1])); ?>">
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

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="suppressionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer la matière <strong id="nomMatiere"></strong> ?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Cette action est irréversible et peut affecter les emplois du temps existants.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="post" action="" id="formSuppression" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts spécifiques -->
<script>
// Fonction de construction des URLs de pagination
function buildPaginationUrl(page, filtres) {
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);

    // Conserver les filtres
    Object.keys(filtres).forEach(key => {
        if (filtres[key]) {
            params.set(key, filtres[key]);
        } else {
            params.delete(key);
        }
    });

    return window.location.pathname + '?' + params.toString();
}

// Fonction de confirmation de suppression
function confirmerSuppression(idMatiere, nomMatiere) {
    document.getElementById('nomMatiere').textContent = nomMatiere;
    document.getElementById('formSuppression').action = '<?php echo BASE_URL; ?>/academique/matieres/supprimer/' + idMatiere;

    const modal = new bootstrap.Modal(document.getElementById('suppressionModal'));
    modal.show();
}

// Fonction d'export des matières
function exportMatieres() {
    const params = new URLSearchParams(window.location.search);
    const url = '<?php echo BASE_URL; ?>/academique/matieres/exporter?' + params.toString();

    window.open(url, '_blank');
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Activer DataTables seulement si le tableau n'est pas vide
    const table = document.getElementById('matieresTable');
    if (table && table.rows.length > 1) {
        // Configuration simple pour la recherche et le tri
        // (DataTables peut être ajouté plus tard si nécessaire)
    }
});
</script>

