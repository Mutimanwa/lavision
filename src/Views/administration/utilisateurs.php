<?php
/**
 * Gestion des utilisateurs
 * Vue d'administration pour la gestion des comptes utilisateur
 */

// Récupérer les données du contexte
$utilisateurs = $utilisateurs ?? [];
$pagination = $pagination ?? [];
$search = $search ?? '';
$role = $role ?? '';
$statut = $statut ?? '';
$roles = $roles ?? [];

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2"></i>
        Gestion des utilisateurs
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

<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtres et recherche
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>administration/utilisateurs" id="filterForm">
            <div class="row g-3">
                <!-- Recherche -->
                <div class="col-md-3">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search"
                        value="<?php echo htmlspecialchars($search); ?>" placeholder="Identifiant, email, nom...">
                </div>

                <!-- Rôle -->
                <div class="col-md-3">
                    <label for="role" class="form-label">Rôle</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">Tous les rôles</option>
                        <?php foreach ($roles as $role_key => $role_label): ?>
                            <option value="<?php echo $role_key; ?>" <?php echo $role === $role_key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Statut -->
                <div class="col-md-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="actif" <?php echo $statut === 'actif' ? 'selected' : ''; ?>>Actif</option>
                        <option value="inactif" <?php echo $statut === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                        <option value="suspendu" <?php echo $statut === 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                    </select>
                </div>

                <!-- Boutons -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                    <a href="<?php echo url('administration/utilisateurs'); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border  border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0"><?php echo count($utilisateurs); ?></h5>
                        <p class="card-text ">Utilisateurs</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">
                            <?php echo count(array_filter($utilisateurs, fn($u) => $u['statut'] === 'actif')); ?>
                        </h5>
                        <p class="card-text">Actifs</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">
                            <?php echo count(array_filter($utilisateurs, fn($u) => $u['statut'] === 'inactif')); ?>
                        </h5>
                        <p class="card-text">Inactifs</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border border-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">
                            <?php echo count(array_filter($utilisateurs, fn($u) => $u['statut'] === 'suspendu')); ?>
                        </h5>
                        <p class="card-text">Suspendus</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-times fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des utilisateurs -->
 <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            Liste des utilisateurs
        </h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-1"></i>
            Ajouter un utilisateur
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($utilisateurs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                <p class="text-muted">Modifiez vos critères de recherche ou ajoutez un nouvel utilisateur.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th><i class="fas fa-user me-1"></i>Utilisateur</th>
                            <th><i class="fas fa-envelope me-1"></i>Email</th>
                            <th><i class="fas fa-user-tag me-1"></i>Rôle</th>
                            <th><i class="fas fa-info-circle me-1"></i>Statut</th>
                            <th><i class="fas fa-clock me-1"></i>Dernière connexion</th>
                            <th><i class="fas fa-cogs me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $utilisateur): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            <?php if ($utilisateur['photo']): ?>
                                                <img src="<?php echo ASSETS_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($utilisateur['photo']); ?>"
                                                    alt="Photo" class="rounded-circle" style="width: 32px; height: 32px;">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                <?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?>
                                            </div>
                                            <small
                                                class="text-muted"><?php echo htmlspecialchars($utilisateur['identifiant']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($utilisateur['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                    echo match ($utilisateur['role']) {
                                        'superadmin' => 'danger',
                                        'admin' => 'warning',
                                        'secretaire' => 'info',
                                        'gestionnaire' => 'success',
                                        'proviseur' => 'primary',
                                        default => 'secondary'
                                    };
                                    ?>">
                                        <?php echo htmlspecialchars($roles[$utilisateur['role']] ?? $utilisateur['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php
                                    echo match ($utilisateur['statut']) {
                                        'actif' => 'success',
                                        'inactif' => 'warning',
                                        'suspendu' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($utilisateur['statut'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($utilisateur['dernier_login']): ?>
                                        <small><?php echo date('d/m/Y H:i', strtotime($utilisateur['dernier_login'])); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Jamais connecté</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="viewUser(<?php echo $utilisateur['user_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                            onclick="editUser(<?php echo $utilisateur['user_id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="resetPassword(<?php echo $utilisateur['user_id']; ?>)">
                                                        <i class="fas fa-key me-2"></i>Réinitialiser MDP
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        onclick="deleteUser(<?php echo $utilisateur['user_id']; ?>)">
                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['pages'] > 1): ?>
                <nav aria-label="Navigation des utilisateurs" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="<?php echo $pagination['page'] > 1 ? '?page=' . ($pagination['page'] - 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role) . '&statut=' . urlencode($statut) : '#'; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['pages'], $pagination['page'] + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&statut=<?php echo urlencode($statut); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo $pagination['page'] >= $pagination['pages'] ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="<?php echo $pagination['page'] < $pagination['pages'] ? '?page=' . ($pagination['page'] + 1) . '&search=' . urlencode($search) . '&role=' . urlencode($role) . '&statut=' . urlencode($statut) : '#'; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


<!-- Modal d'ajout d'utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un utilisateur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo url('administration/utilisateurs/ajouter'); ?>">
                <div class="modal-body">
                    <!-- Jeton CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="row g-3">
                        <!-- Identifiant -->
                        <div class="col-md-6">
                            <label for="identifiant" class="form-label">
                                Identifiant <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="identifiant" name="identifiant" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <!-- Nom -->
                        <div class="col-md-6">
                            <label for="nom" class="form-label">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>

                        <!-- Prénom -->
                        <div class="col-md-6">
                            <label for="prenom" class="form-label">
                                Prénom <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>

                        <!-- Rôle -->
                        <div class="col-md-6">
                            <label for="role" class="form-label">
                                Rôle <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Sélectionner un rôle</option>
                                <?php foreach ($roles as $role_key => $role_label): ?>
                                    <option value="<?php echo $role_key; ?>"><?php echo htmlspecialchars($role_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Téléphone -->
                        <div class="col-md-6">
                            <label for="telephone" class="form-label">
                                Téléphone
                            </label>
                            <input type="tel" class="form-control" id="telephone" name="telephone">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Créer l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        // Animation des messages d'alerte
        $('.alert').hide().fadeIn(500);

        // Auto-submit du formulaire de filtres après un délai
        let filterTimeout;
        $('#search, #role, #statut').on('input change', function () {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function () {
                $('#filterForm').submit();
            }, 500);
        });
    });

    // Fonctions pour les actions utilisateur
    function viewUser(userId) {
        window.location.href = '<?php echo BASE_URL; ?>/administration/utilisateurs/' + userId;
    }

    function editUser(userId) {
        window.location.href = '<?php echo BASE_URL; ?>/administration/utilisateurs/' + userId + '/modifier';
    }

    function resetPassword(userId) {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?')) {
            // Implémenter la réinitialisation
            alert('Fonctionnalité à implémenter');
        }
    }

    function deleteUser(userId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
            // Implémenter la suppression
            alert('Fonctionnalité à implémenter');
        }
    }
</script>

