<?php
/**
 * Consultation des logs système
 * Vue d'administration pour la surveillance des activités
 */

// Inclure l'en-tête
require_once __DIR__ . '/../templates/header.php';

// Récupérer les données du contexte
$logs = $logs ?? [];
$filters = $filters ?? [];
$total_logs = $total_logs ?? 0;
$current_page = $current_page ?? 1;
$total_pages = $total_pages ?? 1;

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
                    <i class="fas fa-history me-2"></i>
                    Logs système
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Administration</li>
                        <li class="breadcrumb-item active">Logs</li>
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

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-file-alt fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title"><?php echo number_format($total_logs); ?></h5>
                            <p class="card-text">Total des logs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                            <h5 class="card-title">
                                <?php
                                $error_logs = array_filter($logs, function($log) {
                                    return strpos(strtolower($log['niveau']), 'error') !== false;
                                });
                                echo count($error_logs);
                                ?>
                            </h5>
                            <p class="card-text">Erreurs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-info-circle fa-2x text-info"></i>
                            </div>
                            <h5 class="card-title">
                                <?php
                                $info_logs = array_filter($logs, function($log) {
                                    return strpos(strtolower($log['niveau']), 'info') !== false;
                                });
                                echo count($info_logs);
                                ?>
                            </h5>
                            <p class="card-text">Informations</p>
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
                                <?php echo $logs ? date('H:i', strtotime($logs[0]['timestamp'])) : '--:--'; ?>
                            </h5>
                            <p class="card-text">Dernière activité</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo BASE_URL; ?>/administration/logs" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="niveau" class="form-label">Niveau</label>
                                <select class="form-select" id="niveau" name="niveau">
                                    <option value="">Tous les niveaux</option>
                                    <option value="DEBUG" <?php echo ($filters['niveau'] ?? '') === 'DEBUG' ? 'selected' : ''; ?>>DEBUG</option>
                                    <option value="INFO" <?php echo ($filters['niveau'] ?? '') === 'INFO' ? 'selected' : ''; ?>>INFO</option>
                                    <option value="WARNING" <?php echo ($filters['niveau'] ?? '') === 'WARNING' ? 'selected' : ''; ?>>WARNING</option>
                                    <option value="ERROR" <?php echo ($filters['niveau'] ?? '') === 'ERROR' ? 'selected' : ''; ?>>ERROR</option>
                                    <option value="CRITICAL" <?php echo ($filters['niveau'] ?? '') === 'CRITICAL' ? 'selected' : ''; ?>>CRITICAL</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="utilisateur" class="form-label">Utilisateur</label>
                                <input type="text"
                                       class="form-control"
                                       id="utilisateur"
                                       name="utilisateur"
                                       value="<?php echo htmlspecialchars($filters['utilisateur'] ?? ''); ?>"
                                       placeholder="Nom d'utilisateur">
                            </div>
                            <div class="col-md-3">
                                <label for="date_debut" class="form-label">Date début</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_debut"
                                       name="date_debut"
                                       value="<?php echo $filters['date_debut'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_fin" class="form-label">Date fin</label>
                                <input type="date"
                                       class="form-control"
                                       id="date_fin"
                                       name="date_fin"
                                       value="<?php echo $filters['date_fin'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="message" class="form-label">Message contient</label>
                                <input type="text"
                                       class="form-control"
                                       id="message"
                                       name="message"
                                       value="<?php echo htmlspecialchars($filters['message'] ?? ''); ?>"
                                       placeholder="Rechercher dans les messages">
                            </div>
                            <div class="col-md-3">
                                <label for="module" class="form-label">Module</label>
                                <input type="text"
                                       class="form-control"
                                       id="module"
                                       name="module"
                                       value="<?php echo htmlspecialchars($filters['module'] ?? ''); ?>"
                                       placeholder="Nom du module">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary me-md-2">
                                        <i class="fas fa-search me-2"></i>
                                        Filtrer
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>/administration/logs" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Liste des logs</h4>
                <div>
                    <button type="button" class="btn btn-outline-info me-2" onclick="exportLogs()">
                        <i class="fas fa-download me-2"></i>
                        Exporter
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="clearLogs()">
                        <i class="fas fa-trash me-2"></i>
                        Vider les logs
                    </button>
                </div>
            </div>

            <!-- Tableau des logs -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="logsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date/Heure</th>
                                    <th>Niveau</th>
                                    <th>Utilisateur</th>
                                    <th>Module</th>
                                    <th>Action</th>
                                    <th>Message</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Aucun log trouvé
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr class="log-row log-<?php echo strtolower($log['niveau']); ?>">
                                            <td>
                                                <small><?php echo date('d/m/Y H:i:s', strtotime($log['timestamp'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo strpos(strtolower($log['niveau']), 'error') !== false ? 'danger' :
                                                         (strpos(strtolower($log['niveau']), 'warning') !== false ? 'warning' :
                                                         (strpos(strtolower($log['niveau']), 'info') !== false ? 'info' : 'secondary'));
                                                ?>">
                                                    <?php echo htmlspecialchars($log['niveau']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['utilisateur'] ?? 'Système'); ?></td>
                                            <td><?php echo htmlspecialchars($log['module'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($log['action'] ?? '-'); ?></td>
                                            <td>
                                                <div class="message-cell">
                                                    <?php echo htmlspecialchars(substr($log['message'], 0, 100)); ?>
                                                    <?php if (strlen($log['message']) > 100): ?>
                                                        <button type="button"
                                                                class="btn btn-sm btn-link p-0 ms-1"
                                                                onclick="showFullMessage('<?php echo htmlspecialchars(addslashes($log['message'])); ?>')"
                                                                title="Voir le message complet">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Navigation des logs" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationUrl($current_page - 1); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationUrl($current_page + 1); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal pour le message complet -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Message complet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <pre id="fullMessage" class="bg-light p-3 rounded"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
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

.message-cell {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.log-error {
    background-color: rgba(220, 53, 69, 0.1);
}

.log-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.log-info {
    background-color: rgba(13, 202, 240, 0.1);
}

.badge {
    font-size: 0.75em;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

pre {
    font-size: 0.875rem;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>

<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Auto-refresh des logs (optionnel)
    let autoRefresh = false;

    $('#autoRefresh').change(function() {
        autoRefresh = $(this).is(':checked');
        if (autoRefresh) {
            setInterval(function() {
                location.reload();
            }, 30000); // Rafraîchir toutes les 30 secondes
        }
    });
});

// Fonction pour afficher le message complet
function showFullMessage(message) {
    $('#fullMessage').text(message);
    $('#messageModal').modal('show');
}

// Fonction pour construire l'URL de pagination
function buildPaginationUrl(page) {
    const url = new URL(window.location);
    url.searchParams.set('page', page);
    return url.toString();
}

// Fonction pour exporter les logs
function exportLogs() {
    const url = new URL(window.location);
    url.searchParams.set('export', 'csv');
    window.open(url.toString(), '_blank');
}

// Fonction pour vider les logs
function clearLogs() {
    if (confirm('Êtes-vous sûr de vouloir vider tous les logs ? Cette action est irréversible.')) {
        if (confirm('Dernière confirmation : tous les logs seront supprimés définitivement.')) {
            // Ici nous enverrions une requête AJAX
            alert('Fonctionnalité à implémenter : vidage des logs');
        }
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>