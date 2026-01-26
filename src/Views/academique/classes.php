<?php
/**
 * Vue de gestion des classes
 * Interface complète pour gérer les classes scolaires
 * Interface responsive avec Bootstrap 5 et DataTables
 * Version: 1.0.0 - Refactorisée pour scalabilité
 */

?>

<div class="row py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-school text-primary me-2"></i>
                Gestion des Classes
            </h1>
            <p class="text-muted mt-1">Administration des classes scolaires</p>
        </div>
        <div>
            <a href="<?php echo url('academique/classes/ajouter'); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Nouvelle Classe
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
            <form method="get" action="<?php echo url('academique/classes'); ?>" class="row g-3">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label for="section" class="form-label">Section</label>
                    <select class="form-select" id="section" name="section">
                        <option value="">Toutes les sections</option>
                        <?php foreach ($data['sections'] as $section): ?>
                            <option value="<?php echo $section['section_id']; ?>"
                                    <?php echo ($data['filtres']['section'] == $section['section_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section['nom_section']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="annee_scolaire" class="form-label">Année Scolaire</label>
                    <select class="form-select" id="annee_scolaire" name="annee_scolaire">
                        <option value="">Toutes les années</option>
                        <?php foreach ($data['annees_scolaires'] as $annee): ?>
                            <option value="<?php echo $annee['annee_id']; ?>"
                                    <?php echo ($data['filtres']['annee_scolaire'] == $annee['annee_id']) ? 'selected' : ''; ?>>
                                <?php echo $annee['date_debut']; ?> - <?php echo $annee['date_fin']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="<?php echo url('academique/classes'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des classes -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Liste des Classes
                <span class="badge bg-primary ms-2"><?php echo $data['pagination']['total_classes']; ?></span>
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" onclick="exportClasses()">
                    <i class="fas fa-download me-1"></i>
                    Exporter
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($data['classes'])): ?>
                <div class="text-center py-5">
                    <i class="fas fa-school fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune classe trouvée</h5>
                    <p class="text-muted">Il n'y a pas de classes correspondant aux critères de recherche.</p>
                    <a href="<?php echo url('academique/classes/ajouter'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Créer la première classe
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="classesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Classe</th>
                                <th>Niveau</th>
                                <th>Section</th>
                                <th>Année Scolaire</th>
                                <th>Prof. Principal</th>
                                <th>Élèves</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['classes'] as $classe): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary text-white me-3">
                                                <i class="fas fa-school"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($classe['libelle']); ?></strong>
                                                <?php if ($classe['capacite_max']): ?>
                                                    <br>
                                                    <small class="text-muted">Capacité: <?php echo $classe['capacite_max']; ?> élèves</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($classe['nom_niveau']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo htmlspecialchars($classe['nom_section']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $classe['annee_libelle']; ?>
                                    </td>
                                    <td>
                                        <?php if ($classe['nom_prof_principal']): ?>
                                            <?php echo htmlspecialchars($classe['nom_prof_principal'] . ' ' . $classe['prenom_prof_principal']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non assigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <?php echo $classe['nombre_eleves']; ?> élève(s)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo url('academique/classes/voir',['class_id' =>$classe['class_id']]); ?>"
                                                class="btn btn-outline-info" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo url('academique/classes/modifier',['class_id' =>$classe['class_id']]); ?>"
                                                class="btn btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="confirmerSuppression(<?php echo $classe['class_id']; ?>, '<?= nettoyer_chaine($classe['libelle']) ?>')"
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
                <p>Êtes-vous sûr de vouloir supprimer la classe <strong id="nomClasse"></strong> ?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Cette action est irréversible et supprimera également tous les horaires associés.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="post" action="" id="formSuppression" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generer_csrf_token(); ?>">
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
function confirmerSuppression(idClasse, nomClasse) {
    document.getElementById('nomClasse').textContent = nomClasse;
    document.getElementById('formSuppression').action = '<?php echo BASE_URL; ?>/academique/classes/supprimer/' + idClasse;

    const modal = new bootstrap.Modal(document.getElementById('suppressionModal'));
    modal.show();
}

// Fonction d'export des classes
function exportClasses() {
    const params = new URLSearchParams(window.location.search);
    const url = '<?php echo url('academique/classes/exporter'); ?>' + '?' + params.toString();

    window.open(url, '_blank');
}

// Initialisation de DataTables si nécessaire
document.addEventListener('DOMContentLoaded', function() {
    // Activer DataTables seulement si le tableau n'est pas vide
    const table = document.getElementById('classesTable');
    if (table && table.rows.length > 1) {
        // Configuration simple pour la recherche et le tri
        // (DataTables peut être ajouté plus tard si nécessaire)
    }
});
</script>
