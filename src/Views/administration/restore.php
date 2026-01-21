<?php
/**
 * Restauration des sauvegardes
 * Vue d'administration pour la restauration du système
 */

// Inclure l'en-tête
require_once __DIR__ . '/../templates/header.php';

// Récupérer les données du contexte
$sauvegarde = $sauvegarde ?? null;
$backup_id = $backup_id ?? null;

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
                    <i class="fas fa-undo me-2"></i>
                    Restauration système
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Administration</li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/administration/backup">Sauvegardes</a></li>
                        <li class="breadcrumb-item active">Restauration</li>
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

            <?php if (!$sauvegarde): ?>
                <!-- Aucune sauvegarde sélectionnée -->
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Aucune sauvegarde sélectionnée pour la restauration.
                    <a href="<?php echo BASE_URL; ?>/administration/backup" class="alert-link">Retour aux sauvegardes</a>
                </div>
            <?php else: ?>
                <!-- Détails de la sauvegarde -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Détails de la sauvegarde
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <strong>ID:</strong> <?php echo htmlspecialchars($sauvegarde['sauvegarde_id']); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Nom:</strong> <?php echo htmlspecialchars($sauvegarde['nom']); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Type:</strong>
                                        <span class="badge bg-secondary ms-2">
                                            <?php
                                            echo $sauvegarde['type'] === 'complete' ? 'Sauvegarde complète' :
                                                 ($sauvegarde['type'] === 'database' ? 'Base de données' : 'Fichiers');
                                            ?>
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Date de création:</strong> <?php echo date('d/m/Y H:i:s', strtotime($sauvegarde['date_creation'])); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Taille:</strong>
                                        <?php
                                        $taille = $sauvegarde['taille'] ?? 0;
                                        if ($taille > 1024 * 1024 * 1024) {
                                            echo round($taille / (1024 * 1024 * 1024), 2) . ' GB';
                                        } elseif ($taille > 1024 * 1024) {
                                            echo round($taille / (1024 * 1024), 2) . ' MB';
                                        } elseif ($taille > 1024) {
                                            echo round($taille / 1024, 2) . ' KB';
                                        } else {
                                            echo $taille . ' octets';
                                        }
                                        ?>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Statut:</strong>
                                        <span class="badge bg-<?php echo $sauvegarde['statut'] === 'reussie' ? 'success' : 'danger'; ?> ms-2">
                                            <?php echo $sauvegarde['statut'] === 'reussie' ? 'Réussie' : 'Échouée'; ?>
                                        </span>
                                    </div>
                                    <?php if ($sauvegarde['description']): ?>
                                        <div class="col-12">
                                            <strong>Description:</strong><br>
                                            <?php echo htmlspecialchars($sauvegarde['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Avertissement de sécurité -->
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Avertissement important</h6>
                            <p class="mb-2">La restauration d'une sauvegarde va :</p>
                            <ul class="mb-2">
                                <li>Remplacer toutes les données actuelles par celles de la sauvegarde</li>
                                <li>Supprimer les données ajoutées après la date de la sauvegarde</li>
                                <li>Peut causer une perte de données si la sauvegarde est corrompue</li>
                                <li>Nécessiter un redémarrage du système dans certains cas</li>
                            </ul>
                            <p class="mb-0"><strong>Il est fortement recommandé de créer une sauvegarde actuelle avant de procéder à la restauration.</strong></p>
                        </div>

                        <!-- Formulaire de restauration -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-undo me-2"></i>
                                    Procéder à la restauration
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?php echo BASE_URL; ?>/administration/restore/process" id="restoreForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="sauvegarde_id" value="<?php echo $sauvegarde['sauvegarde_id']; ?>">

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="confirm_backup"
                                                   required>
                                            <label class="form-check-label" for="confirm_backup">
                                                J'ai créé une sauvegarde des données actuelles
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="confirm_restore"
                                                   required>
                                            <label class="form-check-label" for="confirm_restore">
                                                Je comprends que cette action est irréversible
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="restore_reason" class="form-label">Raison de la restauration</label>
                                        <textarea class="form-control"
                                                  id="restore_reason"
                                                  name="raison"
                                                  rows="3"
                                                  placeholder="Expliquez pourquoi vous effectuez cette restauration..."
                                                  required></textarea>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Durée estimée :</strong> La restauration peut prendre plusieurs minutes selon la taille de la sauvegarde.
                                        Ne fermez pas cette page pendant l'opération.
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="<?php echo BASE_URL; ?>/administration/backup" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Annuler
                                        </a>
                                        <button type="submit" class="btn btn-danger" id="btnRestore">
                                            <i class="fas fa-undo me-2"></i>
                                            Lancer la restauration
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Informations complémentaires -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Conseils de sécurité
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Créez toujours une sauvegarde avant la restauration
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Vérifiez l'intégrité de la sauvegarde
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Testez la restauration dans un environnement de développement
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Informez les utilisateurs avant la maintenance
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Planifiez la restauration pendant les heures creuses
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Historique récent
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($sauvegarde['date_creation'])); ?></small>
                                            <p class="mb-0">Sauvegarde créée avec succès</p>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <small class="text-muted"><?php echo date('d/m/Y H:i'); ?></small>
                                            <p class="mb-0">Préparation de la restauration</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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

.alert-danger {
    border-left: 4px solid #dc3545;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

.form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

.form-control:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 0.375rem;
    border: 1px solid #e9ecef;
}

.timeline-content small {
    display: block;
    margin-bottom: 5px;
}

.badge {
    font-size: 0.75em;
}

strong {
    color: #495057;
}
</style>

<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Validation du formulaire
    $('#restoreForm').submit(function(e) {
        const confirmBackup = $('#confirm_backup').is(':checked');
        const confirmRestore = $('#confirm_restore').is(':checked');
        const reason = $('#restore_reason').val().trim();

        if (!confirmBackup || !confirmRestore) {
            e.preventDefault();
            alert('Veuillez cocher toutes les cases de confirmation.');
            return false;
        }

        if (reason.length < 10) {
            e.preventDefault();
            alert('Veuillez fournir une raison détaillée (minimum 10 caractères).');
            return false;
        }

        if (!confirm('Dernière confirmation : êtes-vous absolument sûr de vouloir procéder à la restauration ?')) {
            e.preventDefault();
            return false;
        }

        // Désactiver le bouton et afficher le message de progression
        $('#btnRestore').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Restauration en cours...');

        // Désactiver tous les champs du formulaire
        $(this).find('input, textarea, button').prop('disabled', true);
    });

    // Validation en temps réel
    $('#confirm_backup, #confirm_restore').change(function() {
        const allChecked = $('#confirm_backup').is(':checked') && $('#confirm_restore').is(':checked');
        $('#btnRestore').prop('disabled', !allChecked);
    });

    $('#restore_reason').on('input', function() {
        const reason = $(this).val().trim();
        const allChecked = $('#confirm_backup').is(':checked') && $('#confirm_restore').is(':checked');
        $('#btnRestore').prop('disabled', !(allChecked && reason.length >= 10));
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>