<?php
/**
 * Gestion des sauvegardes
 * Vue d'administration pour la création et gestion des sauvegardes
 */
// Récupérer les données du contexte
$sauvegardes = $sauvegardes ?? [];
$backup_stats = $backup_stats ?? [];

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>

<!-- Contenu principal -->
<div class="row">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h5 class="h3">
            <i class="fas fa-save me-2"></i>
            Sauvegardes système
        </h5>
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
                        <i class="fas fa-database fa-2x text-primary"></i>
                    </div>
                    <h5 class="card-title"><?php echo count($sauvegardes); ?></h5>
                    <p class="card-text">Sauvegardes totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <h5 class="card-title">
                        <?php
                        $reussies = array_filter($sauvegardes, function($s) {
                            return $s['statut'] === 'reussie';
                        });
                        echo count($reussies);
                        ?>
                    </h5>
                    <p class="card-text">Réussies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                    <h5 class="card-title">
                        <?php
                        $echouees = array_filter($sauvegardes, function($s) {
                            return $s['statut'] === 'echouee';
                        });
                        echo count($echouees);
                        ?>
                    </h5>
                    <p class="card-text">Échouées</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-clock fa-2x text-info"></i>
                    </div>
                    <h5 class="card-title">
                        <?php
                        $derniere = !empty($sauvegardes) ? $sauvegardes[0] : null;
                        echo $derniere ? date('d/m/Y', strtotime($derniere['date_creation'])) : 'Aucune';
                        ?>
                    </h5>
                    <p class="card-text">Dernière sauvegarde</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions principales -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Créer une sauvegarde
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/administration/backup" id="createBackupForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="backup_type" class="form-label">Type de sauvegarde</label>
                            <select class="form-select" id="backup_type" name="type" required>
                                <option value="complete">Sauvegarde complète</option>
                                <option value="database">Base de données uniquement</option>
                                <option value="files">Fichiers uniquement</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="backup_name" class="form-label">Nom de la sauvegarde</label>
                            <input type="text"
                                    class="form-control"
                                    id="backup_name"
                                    name="nom"
                                    placeholder="Sauvegarde automatique <?php echo date('Y-m-d'); ?>"
                                    value="Sauvegarde <?php echo date('Y-m-d H-i-s'); ?>">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input"
                                        type="checkbox"
                                        id="compress_backup"
                                        name="compresser"
                                        checked>
                                <label class="form-check-label" for="compress_backup">
                                    Compresser la sauvegarde (recommandé)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="btnCreateBackup">
                            <i class="fas fa-save me-2"></i>
                            Créer la sauvegarde
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Configuration automatique
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/administration/backup/config" id="configBackupForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="auto_backup" class="form-label">Sauvegarde automatique</label>
                            <select class="form-select" id="auto_backup" name="auto_backup">
                                <option value="disabled">Désactivée</option>
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="retention_days" class="form-label">Conservation (jours)</label>
                            <input type="number"
                                    class="form-control"
                                    id="retention_days"
                                    name="retention_days"
                                    min="1"
                                    max="365"
                                    value="30"
                                    placeholder="30">
                        </div>

                        <div class="mb-3">
                            <label for="backup_location" class="form-label">Emplacement des sauvegardes</label>
                            <input type="text"
                                    class="form-control"
                                    id="backup_location"
                                    name="emplacement"
                                    value="backups/"
                                    placeholder="backups/">
                        </div>

                        <button type="submit" class="btn btn-outline-primary" id="btnConfigBackup">
                            <i class="fas fa-save me-2"></i>
                            Enregistrer la configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des sauvegardes -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Historique des sauvegardes
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="backupsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Date création</th>
                            <th>Taille</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sauvegardes)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucune sauvegarde trouvée
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sauvegardes as $backup): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($backup['sauvegarde_id']); ?></td>
                                    <td><?php echo htmlspecialchars($backup['nom']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php
                                            echo $backup['type'] === 'complete' ? 'Complète' :
                                                    ($backup['type'] === 'database' ? 'Base de données' : 'Fichiers');
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($backup['date_creation'])); ?></td>
                                    <td>
                                        <?php
                                        $taille = $backup['taille'] ?? 0;
                                        if ($taille > 1024 * 1024 * 1024) {
                                            echo round($taille / (1024 * 1024 * 1024), 2) . ' GB';
                                        } elseif ($taille > 1024 * 1024) {
                                            echo round($taille / (1024 * 1024), 2) . ' MB';
                                        } elseif ($taille > 1024) {
                                            echo round($taille / 1024, 2) . ' KB';
                                        } else {
                                            echo $taille . ' B';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php
                                            echo $backup['statut'] === 'reussie' ? 'success' :
                                                    ($backup['statut'] === 'en_cours' ? 'warning' : 'danger');
                                        ?>">
                                            <?php
                                            echo $backup['statut'] === 'reussie' ? 'Réussie' :
                                                    ($backup['statut'] === 'en_cours' ? 'En cours' : 'Échouée');
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($backup['statut'] === 'reussie'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        onclick="downloadBackup(<?php echo $backup['sauvegarde_id']; ?>)"
                                                        title="Télécharger">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info"
                                                        onclick="restoreBackup(<?php echo $backup['sauvegarde_id']; ?>)"
                                                        title="Restaurer">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteBackup(<?php echo $backup['sauvegarde_id']; ?>)"
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

<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Soumission du formulaire de création
    $('#createBackupForm').submit(function(e) {
        $('#btnCreateBackup').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...');
    });

    // Soumission du formulaire de configuration
    $('#configBackupForm').submit(function(e) {
        $('#btnConfigBackup').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');
    });
});

// Fonction pour télécharger une sauvegarde
function downloadBackup(id) {
    window.open('<?php echo BASE_URL; ?>/administration/backup/download/' + id, '_blank');
}

// Fonction pour restaurer une sauvegarde
function restoreBackup(id) {
    if (confirm('Êtes-vous sûr de vouloir restaurer cette sauvegarde ? Toutes les données actuelles seront remplacées.')) {
        if (confirm('Dernière confirmation : cette action est irréversible. Continuer ?')) {
            // Redirection vers la page de restauration
            window.location.href = '<?php echo BASE_URL; ?>/administration/restore?id=' + id;
        }
    }
}

// Fonction pour supprimer une sauvegarde
function deleteBackup(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette sauvegarde ?')) {
        // Ici nous enverrions une requête AJAX
        alert('Fonctionnalité à implémenter : suppression de la sauvegarde ' + id);
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>