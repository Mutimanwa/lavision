<?php
/**
 * Module d'administration - Gestion des utilisateurs et logs système
 * Sécurité: Rôles, permissions, audit complet
 * Version: 1.0.0
 */
require_once __DIR__ . '/../../../modules/administration/utilisateurs.php';

if (!is_logged_in() || !has_role(ROLE_SUPERADMIN)) {
    redirect("login");
}

// gestion d'affichage de statistique 
$utilisateurs = admin_get_all_utilisateurs();
$nombre_utilisateurs = count($utilisateurs);
$nombre_utilisateurs_actifs = count(admin_get_utilisateur_par_statut(STATUS_ACTIF));
$nombre_utilisateurs_inactifs = count(admin_get_utilisateur_par_statut(STATUS_INACTIF));
$nombre_utilisateurs_en_attente = count(admin_get_utilisateur_par_statut(STATUS_SUSPENDU));

?>
<!-- Header -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gestion des Utilisateurs</h5>
        <div class="d-flex align-items-center">
            <div class="search-box me-3">
                <div class="input-group">
                    <input id="search" class="form-control form-control-sm shadow-none search" type="text"
                        placeholder="Rechercher..." aria-label="search">
                    <div class="input-group-text bg-transparent">
                        <span class="fa fa-search fs-10 text-600"></span>
                    </div>
                </div>
            </div>

            <div class="dropdown">
                <a href="<?= url('administration/utilisateurs/ajout'); ?>" class="btn btn-falcon-default dropdown-toggle" type="button" id="addUserDropdown"
                   >
                    <i class="fas fa-plus me-2"></i>Nouvel utilisateur
                </a>
                <!-- <ul class="dropdown-menu" aria-labelledby="addUserDropdown">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Utilisateur standard</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-chalkboard-teacher me-2"></i>Enseignant</a>
                    </li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-tie me-2"></i>Administrateur</a></li>
                </ul> -->
            </div>
        </div>
    </div>
</div>


<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card primary h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Utilisateurs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= e($nombre_utilisateurs) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card success h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Utilisateurs Actifs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= e($nombre_utilisateurs_actifs) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card warning h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En attente</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= e($nombre_utilisateurs_en_attente) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card danger h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Utilisateurs Inactifs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= e($nombre_utilisateurs_inactifs) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card" id="tableExample4"
    data-list='{"valueNames":["name","email","role","status"],page:10,pagination:true}'>
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Liste des utilisateurs</h6>
        <div class="d-flex">
            <select class="form-select form-select-sm me-2" data-list-filter="role">
                <option value="">Tous les rôles</option>
                <option value="<?= ROLE_ADMIN ?>">Administrateur</option>
                <option value="<?= ROLE_PROVISEUR ?>">Proviseur</option>
                <option value="<?= ROLE_SECRETAIRE ?>">Secretaire</option>
                <option value="<?= ROLE_GESTIONNAIRE ?>">Gestionnaire</option>
                <option value="<?= ROLE_PROFESSEUR ?>">Enseignant</option>
            </select>
            <select class="form-select form-select-sm" data-list-filter="status">
                <option value="">Tous les statuts</option>
                <option value="<?= STATUS_ACTIF ?>">Actif</option>
                <option value="<?= STATUS_INACTIF ?>">Inactif</option>
                <option value="<?= STATUS_SUSPENDU ?>">Suspemdu</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered fs-10 mb-0 overflow-hidden">
                <thead class="bg-200">
                    <tr>
                        <!-- <th>
                            <input class="form-check-input" type="checkbox">
                        </th> -->
                        <th class="text-900 sort pe-1 align-middle text-center" data-sort="name">Utilisateur</th>
                        <th class="text-900 sort pe-1 align-middle text-center" data-sort="email">Email</th>
                        <th class="text-900 sort pe-1 align-middle text-center" data-sort="role">Rôle</th>
                        <th class="text-900 sort pe-1 align-middle text-center" data-sort="tatus">Statut</th>
                        <th class="text-900 sort pe-1 align-middle text-center">Date de création</th>
                        <th class="text-900 sort pe-1 align-middle text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="list" id="table-purchase-body">
                    <?php
                    foreach ($utilisateurs as $utilisateur):
                        ?>
                        <tr class="btn-reveal-trigger">
                            <!-- <td>
                            <input class="form-check-input" type="checkbox">
                        </td> -->
                            <td class="name align-middle text-center py-2">
                                <div class="d-flex align-items-center">
                                    <img src="assets/img/team/avatar.png" class="user-avatar me-3" alt="Tony Robbins"
                                        width="30px">
                                    <div>
                                        <div class="fw-bold"><?= e($utilisateur['nom'] . " - " . $utilisateur['prenom']); ?>
                                        </div>
                                        <div class="text-muted small">@<?= e($utilisateur['identifiant']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="email align-middle text-center py-2"><?= e($utilisateur['email']); ?></td>
                            <td class="role align-middle text-center py-2"><span
                                    class="badge badge rounded-pill badge-subtle-primary"><?= e($utilisateur['role']); ?></span>
                            </td>
                            <td class="status align-middle text-center py-2">
                                <?php
                                $status_class = '';
                                switch ($utilisateur['statut']) {
                                    case STATUS_ACTIF:
                                        $status_class = 'badge-subtle-success';
                                        break;
                                    case STATUS_INACTIF:
                                        $status_class = 'badge-subtle-danger';
                                        break;
                                    case STATUS_SUSPENDU:
                                        $status_class = 'badge-subtle-warning';
                                        break;
                                    case STATUS_BLOQUE:
                                        $status_class = 'badge-subtle-dark';
                                        break;
                                    default:
                                        $status_class = 'badge-subtle-info';
                                        break;
                                }
                                ?>
                                <span
                                    class="badge badge rounded-pill <?= e($status_class); ?>"><?= e($utilisateur['statut']); ?></span>
                            </td>

                            <!-- date en format Jour moi annee -->
                            <td class="align-middle text-center py-2">
                                <?= format_date($utilisateur['date_creation'], "d/m/Y"); ?></td>
                            <td class="align-middle text-center">

                                <div class="dropdown font-sans-serif position-static">
                                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button"
                                        id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
                                        aria-haspopup="true" aria-expanded="false">
                                        <span class="fas fa-ellipsis-h fs-10"></span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end border py-0"
                                        aria-labelledby="customer-dropdown-0" style="">
                                        <div class="py-2">
                                       
                                            <a href="<?= url('administration/utilisateurs/view', ['uuid' => $utilisateur['uuid']]) ?>"
                                                class="dropdown-item">
                                                <span class="fas fa-eye"></span> Voir plus
                                            </a>

                                       
                                            <a href="<?= url('administration/utilisateurs/ajout', ['uuid' => $utilisateur['uuid']]) ?>"
                                                class="dropdown-item">
                                                <span class="fas fa-edit"></span> Modifier
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer d-flex align-items-center justify-content-center">
            <button class="btn btn-sm btn-falcon-default me-1 disabled" type="button" title="Previous"
                data-list-pagination="prev" disabled="">
                <span class="fas fa-chevron-left"></span>
            </button>
            <ul class="pagination mb-0">
                <li class="active"><button class="page" type="button" data-i="1" data-page="10">1</button></li>
                <li><button class="page" type="button" data-i="2" data-page="10">2</button></li>
                <li><button class="page" type="button" data-i="3" data-page="10">3</button></li>
            </ul>
            <button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next" data-list-pagination="next">
                <span class="fas fa-chevron-right"></span>
            </button>
        </div>
    </div>
</div>

<!-- Bootstrap & jQuery JS -->
<script src="<?= LIBS_URL ?>jquery/jquery.min.js"></script>

<script>
    $(document).ready(function () {
        // Basic search functionality
        $('#search').on('keyup', function () {
            const value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Select all checkbox
        $('thead input[type="checkbox"]').on('change', function () {
            const isChecked = $(this).prop('checked');
            $('tbody input[type="checkbox"]').prop('checked', isChecked);
        });
    });
</script>