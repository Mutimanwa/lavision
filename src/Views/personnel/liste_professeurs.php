<?php
/**
 * Vue : Liste des professeurs
 * Affiche la liste paginée des professeurs avec filtres et actions
 */

?>

    <!-- En-tête de page -->
    <div class="row mb-2 py-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-chalkboard-teacher text-primary"></i>
                        <?php echo htmlspecialchars($titre_page); ?>
                    </h1>
                    <p class="text-muted"><?php echo htmlspecialchars($sous_titre); ?></p>
                </div>
                <div>
                    <a href="<?= url('personnel/professeurs/ajouter') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau Professeur
                    </a>
                    <a href="<?= url('personnel/tableau-bord') ?>" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar"></i> Tableau de Bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-2">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Professeurs Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $statistiques['professeurs_actifs'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Professeurs Inactifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $statistiques['professeurs_inactifs'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Comptes Utilisateurs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $statistiques['comptes_professeurs'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Titulaires
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $statistiques['titulaires'] ?? 0; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtres
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>personnel/professeurs" class="row g-3">
                <div class="col-md-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <?php foreach ($statuts_disponibles as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($filtres['statut'] ?? '') === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="specialite" class="form-label">Spécialité</label>
                    <input type="text" name="specialite" id="specialite" class="form-control"
                           value="<?php echo htmlspecialchars($filtres['specialite'] ?? ''); ?>"
                           placeholder="Ex: Mathématiques, Français...">
                </div>

                <div class="col-md-3">
                    <label for="recherche" class="form-label">Recherche</label>
                    <input type="text" name="recherche" id="recherche" class="form-control"
                           value="<?php echo htmlspecialchars($filtres['recherche'] ?? ''); ?>"
                           placeholder="Nom, prénom ou matricule...">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2 ">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="<?php echo url('personnel/professeurs'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des professeurs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Liste des Professeurs
                <span class="badge bg-primary ms-2"><?php echo $pagination['total_professeurs']; ?></span>
            </h6>
            <div>
                <a href="<?php echo url('personnel/professeurs/exporter'); ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-download"></i> Exporter CSV
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($professeurs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun professeur trouvé</h5>
                    <p class="text-muted">Il n'y a encore aucun professeur enregistré ou correspondant aux critères de recherche.</p>
                    <a href="<?php echo url('personnel/professeurs/ajouter'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter le premier professeur
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>Matricule</th>
                                <th>Nom & Prénom</th>
                                <th>Spécialité</th>
                                <th>Statut</th>
                                <th>Type de Contrat</th>
                                <th>Classe Tuteur</th>
                                <th>Cours</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professeurs as $prof): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($prof['matricule_prof']); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <?php if (!empty($prof['photo'])): ?>
                                                    <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo htmlspecialchars($prof['photo']); ?>"
                                                         alt="Photo" class="rounded-circle">
                                                <?php else: ?>
                                                    <div class="avatar-initial rounded-circle bg-primary text-white">
                                                        <?php echo strtoupper(substr($prof['prenom'], 0, 1) . substr($prof['nom'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($prof['email'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($prof['specialite'] ?? 'Non spécifiée'); ?></td>
                                    <td>
                                        <?php
                                        $statut_class = match($prof['statut']) {
                                            'actif' => 'success',
                                            'inactif' => 'secondary',
                                            'conge' => 'warning',
                                            'retraite' => 'dark',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statut_class; ?>">
                                            <?php echo htmlspecialchars($statuts_disponibles[$prof['statut']] ?? $prof['statut']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($prof['type_contrat']); ?></td>
                                    <td><?php echo htmlspecialchars($prof['classe_tuteur'] ?? 'Aucune'); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo $prof['nombre_cours']; ?> cours</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo url('personnel/professeurs/voir',['prof_id'=> $prof['professeur_id'] ] ); ?>"
                                               class="btn btn-sm btn-outline-info" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo url('personnel/professeurs/modifier', ['prof_id'=> $prof['professeur_id'] ]); ?>"
                                               class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($prof['statut'] === 'actif'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmerSuppression(<?php echo $prof['professeur_id']; ?>, '<?php echo htmlspecialchars($prof['nom'] . ' ' . $prof['prenom']); ?>')"
                                                        title="Désactiver">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            <?php endif; ?>
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

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="modalSuppression" tabindex="-1" aria-labelledby="modalSuppressionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSuppressionLabel">Confirmer la désactivation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir désactiver le professeur <strong id="nomProfesseur"></strong> ?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Cette action rendra le professeur inactif mais conservera ses données.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <a id="lienSuppression" href="#" class="btn btn-danger">Désactiver</a>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour construire l'URL avec les paramètres actuels
function build_url_with_params(params) {
    const url = new URL(window.location);
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== '') {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    return url.toString();
}

// Fonction de confirmation de suppression
function confirmerSuppression(professeurId, nomProfesseur) {
    document.getElementById('nomProfesseur').textContent = nomProfesseur;
    document.getElementById('lienSuppression').href = '<?php echo BASE_URL; ?>personnel/professeurs/supprimer/' + professeurId;
    new bootstrap.Modal(document.getElementById('modalSuppression')).show();
}
</script>

