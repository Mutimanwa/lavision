<?php
/**
 * Vue - Liste des élèves
 * Affiche la liste paginée des élèves avec filtres et actions
 * Version: 2.0.0
 * Date: 20 janvier 2026
 */

// Récupération des données passées par le contrôleur
$data = $data ?? [];
$eleves = $data['eleves'] ?? [];
$filtres = $data['filtres'] ?? [];
$pagination = $data['pagination'] ?? [];
$tri = $data['tri'] ?? [];
$classes = $data['classes'] ?? [];
$statuts = $data['statuts'] ?? [];
$genres = $data['genres'] ?? [];

// Messages de succès/erreur
$message_succes = $_SESSION['message_succes'] ?? null;
$message_erreur = $_SESSION['message_erreur'] ?? null;
unset($_SESSION['message_succes'], $_SESSION['message_erreur']);
?>


<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Gestion des Élèves</h5>
                        <p class="text-muted mb-0">Liste et gestion des élèves inscrits</p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= url('eleves/ajouter') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ajouter un élève
                        </a>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message_succes): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($message_succes) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($message_erreur): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($message_erreur) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="card-body border-bottom">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="page" value="eleves">

                    <div class="col-md-3">
                        <label for="nom" class="form-label">Rechercher par nom</label>
                        <input type="text" class="form-control" id="nom" name="nom"
                                value="<?= htmlspecialchars($filtres['nom'] ?? '') ?>"
                                placeholder="Nom, post-nom ou prénom">
                    </div>

                    <div class="col-md-2">
                        <label for="classe" class="form-label">Classe</label>
                        <select class="form-select" id="classe" name="classe">
                            <option value="">Toutes les classes</option>
                            <?php foreach ($classes as $classe): ?>
                                <option value="<?= $classe['id'] ?>"
                                        <?= ($filtres['classe'] ?? '') == $classe['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classe['nom_classe']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuts as $key => $label): ?>
                                <option value="<?= $key ?>"
                                        <?= ($filtres['statut'] ?? '') == $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="genre" class="form-label">Genre</label>
                        <select class="form-select" id="genre" name="genre">
                            <option value="">Tous</option>
                            <?php foreach ($genres as $key => $label): ?>
                                <option value="<?= $key ?>"
                                        <?= ($filtres['genre'] ?? '') == $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search me-1"></i>Filtrer
                            </button>
                            <a href="<?= url('eleves') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Statistiques -->
            <div class="card-body border-bottom ">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-primary mb-0"><?= $pagination['total_eleves'] ?? 0 ?></h4>
                            <small class="text-muted">Total élèves</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-success mb-0">
                                <?= count(array_filter($eleves, fn($e) => $e['statut'] === 'actif')) ?>
                            </h4>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-warning mb-0">
                                <?= count(array_filter($eleves, fn($e) => $e['statut'] === 'en_attente')) ?>
                            </h4>
                            <small class="text-muted">En attente</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-danger mb-0">
                            <?= count(array_filter($eleves, fn($e) => $e['statut'] === 'suspendu')) ?>
                        </h4>
                        <small class="text-muted">Suspendus</small>
                    </div>
                </div>
            </div>

            <!-- Table des élèves -->
            <div class="card-body">
                <?php if (empty($eleves)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun élève trouvé</h5>
                        <p class="text-muted">Modifiez vos critères de recherche ou ajoutez un nouvel élève.</p>
                        <a href="<?= url('eleves/ajouter') ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ajouter le premier élève
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <a href="<?= url('eleves', ['tri' => 'nom', 'ordre' => ($tri['champ'] === 'nom' && $tri['ordre'] === 'ASC' ? 'DESC' : 'ASC')]) ?>"
                                            class="text-decoration-none text-dark">
                                            Nom complet
                                            <?php if ($tri['champ'] === 'nom'): ?>
                                                <i class="fas fa-sort-<?= $tri['ordre'] === 'ASC' ? 'up' : 'down' ?> ms-1"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Classe</th>
                                    <th>Genre</th>
                                    <th>
                                        <a href="<?= url('eleves', ['tri' => 'date_inscription', 'ordre' => ($tri['champ'] === 'date_inscription' && $tri['ordre'] === 'ASC' ? 'DESC' : 'ASC')]) ?>"
                                            class="text-decoration-none text-dark">
                                            Inscription
                                            <?php if ($tri['champ'] === 'date_inscription'): ?>
                                                <i class="fas fa-sort-<?= $tri['ordre'] === 'ASC' ? 'up' : 'down' ?> ms-1"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eleves as $eleve): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <img src="<?= IMAGES_URL ?>team/avatar.png"
                                                            alt="Photo" class="rounded-circle">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">
                                                        <a href="<?= url('eleves/detail', ['id' => $eleve['id']]) ?>"
                                                            class="text-decoration-none">
                                                            <?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['post_nom'] . ' ' . $eleve['prenom']) ?>
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        ID: <?= htmlspecialchars($eleve['id']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($eleve['nom_classe']): ?>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($eleve['nom_classe']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Non assigné</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $eleve['genre'] === 'M' ? 'primary' : 'success' ?>">
                                                <?= htmlspecialchars($genres[$eleve['genre']] ?? $eleve['genre']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($eleve['date_inscription'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = match($eleve['statut']) {
                                                'actif' => 'success',
                                                'en_attente' => 'warning',
                                                'suspendu' => 'danger',
                                                'desiste' => 'secondary',
                                                default => 'light'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $badge_class ?>">
                                                <?= htmlspecialchars($statuts[$eleve['statut']] ?? $eleve['statut']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="<?= url('eleves/detail', ['id' => $eleve['id']]) ?>">
                                                            <i class="fas fa-eye me-2"></i>Voir détails
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="<?= url('eleves/modifier', ['id' => $eleve['id']]) ?>">
                                                            <i class="fas fa-edit me-2"></i>Modifier
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger"
                                                            href="#"
                                                            onclick="confirmerDesinscription(<?= $eleve['id'] ?>, '<?= htmlspecialchars(addslashes($eleve['nom'] . ' ' . $eleve['prenom'])) ?>')">
                                                            <i class="fas fa-user-times me-2"></i>Désinscrire
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Navigation des élèves">
                            <ul class="pagination justify-content-center mt-4">
                                <!-- Précédent -->
                                <?php if ($pagination['page_actuelle'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= url('eleves', array_merge($_GET, ['page' => $pagination['page_actuelle'] - 1])) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Pages -->
                                <?php
                                $debut = max(1, $pagination['page_actuelle'] - 2);
                                $fin = min($pagination['total_pages'], $pagination['page_actuelle'] + 2);

                                for ($i = $debut; $i <= $fin; $i++):
                                ?>
                                    <li class="page-item <?= $i === $pagination['page_actuelle'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= url('eleves', array_merge($_GET, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Suivant -->
                                <?php if ($pagination['page_actuelle'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= url('eleves', array_merge($_GET, ['page' => $pagination['page_actuelle'] + 1])) ?>">
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
</div>


<!-- Modal de confirmation de désinscription -->
<div class="modal fade" id="modalDesinscription" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la désinscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="id_eleve" id="idEleveDesinscription">

                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir désinscrire <strong id="nomEleveDesinscription"></strong> ?</p>
                    <p class="text-muted">Cette action est irréversible et l'élève sera marqué comme "désisté".</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer la désinscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fonction pour confirmer la désinscription
function confirmerDesinscription(idEleve, nomEleve) {
    document.getElementById('idEleveDesinscription').value = idEleve;
    document.getElementById('nomEleveDesinscription').textContent = nomEleve;
    new bootstrap.Modal(document.getElementById('modalDesinscription')).show();
}
</script>