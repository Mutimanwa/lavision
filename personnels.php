<?php include 'header.php'; ?>
<!-- Header -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gestion du Personnel Administratif</h5>
        <div class="d-flex align-items-center">
            <div class="search-box me-3">
                <div class="input-group">
                    <input class="form-control form-control-sm shadow-none search" type="text"
                        placeholder="Rechercher..." aria-label="search">
                    <div class="input-group-text bg-transparent">
                        <span class="fa fa-search fs-10 text-600"></span>
                    </div>
                </div>
            </div>
            <button class="btn btn-falcon-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="fas fa-plus me-2"></i>Nouveau membre
            </button>
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
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Personnel</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">42</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">En service</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">35</div>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En congé</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">5</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-umbrella-beach fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card info h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Départements</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">7</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-sitemap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0">Liste du personnel administratif</h6>
        <div class="d-flex">
            <select class="form-select form-select-sm me-2">
                <option>Tous les départements</option>
                <option>Direction</option>
                <option>Secrétariat</option>
                <option>Comptabilité</option>
                <option>Ressources Humaines</option>
                <option>Maintenance</option>
            </select>
            <select class="form-select form-select-sm">
                <option>Tous les statuts</option>
                <option>En service</option>
                <option>En congé</option>
                <option>Absent</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered fs-10 mb-0 overflow-hidden">
                <thead class="bg-200">
                    <tr>
                        <th>
                            <input class="form-check-input" type="checkbox">
                        </th>
                        <th class="text-900 sort pe-1 align-middle text-center">Personnel</th>
                        <th class="text-900 sort pe-1 align-middle text-center">Poste</th>
                        <th class="text-900 sort pe-1 align-middle text-center">Département</th>
                        <th class="text-900 sort pe-1 align-middle text-center">Email</th>
                        <th class="text-900 sort pe-1 align-middle text-center">Statut</th>
                        <th class="text-900 sort pe-1 align-middle text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="list" id="table-purchase-body">
                    <tr class="btn-reveal-trigger">
                        <td>
                            <input class="form-check-input" type="checkbox">
                        </td>
                        <td class="align-middle py-2">
                            <div class="d-flex align-items-center">
                                <img src="assets/img/team/avatar.png" width="30px" class="user-avatar me-3"
                                    alt="Martin Dubois">
                                <div>
                                    <div class="fw-bold">Martin Dubois</div>
                                    <div class="text-muted small">#ADM-001</div>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle text-center py-2">Directeur</td>
                        <td class="align-middle text-center py-2"><span class="badge badge rounded-pill badge-subtle-primary department-badge">Direction</span></td>
                        <td class="align-middle text-center py-2">martin.dubois@ecole.fr</td>
                        <td class="align-middle text-center py-2"><span class="badge badge rounded-pill badge-subtle-success">En service</span></td>
                        <td class="align-middle text-center py-2">
                           
                            <div class="dropdown font-sans-serif position-static">
                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button" id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                    <span class="fas fa-ellipsis-h fs-10"></span> 
                                </button>
                                    <div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="customer-dropdown-0" style="">
                                        <div class="py-2">
                                            <a href="#" class="dropdown-item"><span class="fas fa-eye"></span> Voirs plus</a>
                                            <a class="dropdown-item" href="#!"><span class="fas fa-edit"></span> Modifier</a>
                                            <a class="dropdown-item text-danger" href="#!"><i class="fas fa-trash"></i> Delete</a>
                                        </div>
                                    </div>
                                </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
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
                <button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next"
                    data-list-pagination="next">
                    <span class="fas fa-chevron-right"></span> 
                </button>
            </div>

  
</div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStaffModalLabel">Ajouter un membre du personnel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Poste</label>
                            <input type="text" class="form-control" id="position" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Département</label>
                            <select class="form-select" id="department" required>
                                <option value="">Sélectionner...</option>
                                <option value="direction">Direction</option>
                                <option value="secretariat">Secrétariat</option>
                                <option value="comptabilite">Comptabilité</option>
                                <option value="rh">Ressources Humaines</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea class="form-control" id="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="startDate" class="form-label">Date d'embauche</label>
                            <input type="date" class="form-control" id="startDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" required>
                                <option value="active">En service</option>
                                <option value="leave">En congé</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            // Basic search functionality
            $('.search-box input').on('keyup', function () {
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

            // Filter by department
            $('select').on('change', function () {
                const department = $(this).val();
                if (department) {
                    $('table tbody tr').each(function () {
                        const rowDepartment = $(this).find('.department-badge').text().toLowerCase();
                        $(this).toggle(rowDepartment.includes(department.toLowerCase()) || department === "");
                    });
                }
            });
        });
    </script>
    <?php include 'footer.php'; ?>