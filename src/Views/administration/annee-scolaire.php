<?php
/**
 * Gestion de l'année scolaire
 * Vue d'administration pour la configuration des périodes scolaires
 */

// Récupérer les données du contexte
$annees_scolaires = $annees_scolaires ?? [];
$annee_actuelle = $annee_actuelle ?? null;

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>


<div class="row">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-calendar-alt me-2"></i>
            Gestion de l'année scolaire
        </h1>

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

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title"><?php echo count($annees_scolaires); ?></h5>
                    <p class="card-text">Années scolaires</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-play-circle fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title">
                        <?php echo $annee_actuelle ? htmlspecialchars($annee_actuelle['nom']) : 'Aucune'; ?>
                    </h5>
                    <p class="card-text">Année en cours</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <h5 class="card-title">
                        <?php
                        $annees_actives = array_filter($annees_scolaires, function($a) {
                            return $a['statut'] === 'active';
                        });
                        echo count($annees_actives);
                        ?>
                    </h5>
                    <p class="card-text">Années actives</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-archive fa-2x text-secondary"></i>
                    </div>
                    <h5 class="card-title">
                        <?php
                        $annees_cloturees = array_filter($annees_scolaires, function($a) {
                            return $a['statut'] === 'cloturee';
                        });
                        echo count($annees_cloturees);
                        ?>
                    </h5>
                    <p class="card-text">Années clôturées</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton d'ajout -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Liste des années scolaires</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnneeModal">
            <i class="fas fa-plus me-2"></i>
            Nouvelle année scolaire
        </button>
    </div>

    <!-- Tableau des années scolaires -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="anneesTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($annees_scolaires)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucune année scolaire trouvée
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($annees_scolaires as $annee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($annee['annee_scolaire_id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($annee['nom']); ?></strong>
                                        <?php if ($annee['statut'] === 'active'): ?>
                                            <span class="badge bg-success ms-2">En cours</span>
                                        <?php elseif ($annee['statut'] === 'cloturee'): ?>
                                            <span class="badge bg-secondary ms-2">Clôturée</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning ms-2">Planifiée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($annee['date_debut'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($annee['date_fin'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            echo $annee['statut'] === 'active' ? 'success' :
                                                    ($annee['statut'] === 'cloturee' ? 'secondary' : 'warning');
                                        ?>">
                                            <?php echo ucfirst($annee['statut']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($annee['description'] ?? '', 0, 50)); ?>...</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="editAnnee(<?php echo $annee['annee_scolaire_id']; ?>)"
                                                    title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($annee['statut'] === 'planifiee'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success"
                                                        onclick="activateAnnee(<?php echo $annee['annee_scolaire_id']; ?>)"
                                                        title="Activer">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php elseif ($annee['statut'] === 'active'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-warning"
                                                        onclick="closeAnnee(<?php echo $annee['annee_scolaire_id']; ?>)"
                                                        title="Clôturer">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteAnnee(<?php echo $annee['annee_scolaire_id']; ?>)"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
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
</div>

<!-- Modal d'ajout/modification d'année scolaire -->
<div class="modal fade" id="addAnneeModal" tabindex="-1" aria-labelledby="addAnneeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAnneeModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle année scolaire
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>/administration/annee-scolaire" id="anneeForm">
                <div class="modal-body">
                    <!-- Jeton CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="annee_scolaire_id" id="annee_id" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom de l'année scolaire *</label>
                            <input type="text"
                                   class="form-control"
                                   id="nom"
                                   name="nom"
                                   required
                                   placeholder="Ex: 2024-2025">
                        </div>
                        <div class="col-md-6">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="planifiee">Planifiée</option>
                                <option value="active">Active</option>
                                <option value="cloturee">Clôturée</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_debut" class="form-label">Date de début *</label>
                            <input type="date"
                                   class="form-control"
                                   id="date_debut"
                                   name="date_debut"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="date_fin" class="form-label">Date de fin *</label>
                            <input type="date"
                                   class="form-control"
                                   id="date_fin"
                                   name="date_fin"
                                   required>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Description de l'année scolaire..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSaveAnnee">
                        <i class="fas fa-save me-2"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Validation des dates
    $('#date_debut, #date_fin').change(function() {
        const debut = new Date($('#date_debut').val());
        const fin = new Date($('#date_fin').val());

        if (debut && fin && debut >= fin) {
            alert('La date de fin doit être postérieure à la date de début.');
            $(this).val('');
        }
    });

    // Soumission du formulaire
    $('#anneeForm').submit(function(e) {
        const debut = new Date($('#date_debut').val());
        const fin = new Date($('#date_fin').val());

        if (debut >= fin) {
            e.preventDefault();
            alert('La date de fin doit être postérieure à la date de début.');
            return false;
        }

        $('#btnSaveAnnee').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');
    });

    // Réinitialiser le modal lors de l'ouverture
    $('#addAnneeModal').on('show.bs.modal', function() {
        $('#anneeForm')[0].reset();
        $('#annee_id').val('');
        $('#addAnneeModalLabel').html('<i class="fas fa-plus me-2"></i>Nouvelle année scolaire');
        $('#btnSaveAnnee').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Enregistrer');
    });
});

// Fonction pour modifier une année scolaire
function editAnnee(id) {
    // Ici nous chargerions les données depuis le serveur
    alert('Fonctionnalité à implémenter : chargement des données de l\'année scolaire ' + id);
}

// Fonction pour activer une année scolaire
function activateAnnee(id) {
    if (confirm('Êtes-vous sûr de vouloir activer cette année scolaire ?')) {
        // Ici nous enverrions une requête AJAX
        alert('Fonctionnalité à implémenter : activation de l\'année scolaire ' + id);
    }
}

// Fonction pour clôturer une année scolaire
function closeAnnee(id) {
    if (confirm('Êtes-vous sûr de vouloir clôturer cette année scolaire ? Toutes les opérations seront finalisées.')) {
        // Ici nous enverrions une requête AJAX
        alert('Fonctionnalité à implémenter : clôture de l\'année scolaire ' + id);
    }
}

// Fonction pour supprimer une année scolaire
function deleteAnnee(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette année scolaire ? Cette action est irréversible.')) {
        // Ici nous enverrions une requête AJAX
        alert('Fonctionnalité à implémenter : suppression de l\'année scolaire ' + id);
    }
}
</script>
