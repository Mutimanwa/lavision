<?php include 'header.php'; ?>
<div class="col-lg-10 main-content">
                <!-- Header -->
                <div class="header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Gestion du Personnel Administratif</h4>
                    <div class="d-flex align-items-center">
                        <div class="search-box me-3">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Rechercher...">
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="fas fa-plus me-2"></i>Nouveau membre
                        </button>
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
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input class="form-check-input" type="checkbox">
                                        </th>
                                        <th>Personnel</th>
                                        <th>Poste</th>
                                        <th>Département</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name=Martin+Dubois&background=4e73df&color=fff" class="user-avatar me-3" alt="Martin Dubois">
                                                <div>
                                                    <div class="fw-bold">Martin Dubois</div>
                                                    <div class="text-muted small">#ADM-001</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Directeur</td>
                                        <td><span class="department-badge bg-primary">Direction</span></td>
                                        <td>martin.dubois@ecole.fr</td>
                                        <td><span class="status-badge bg-success">En service</span></td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="btn btn-sm btn-outline-primary action-btn me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success action-btn me-1">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger action-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name=Claire+Moreau&background=6f42c1&color=fff" class="user-avatar me-3" alt="Claire Moreau">
                                                <div>
                                                    <div class="fw-bold">Claire Moreau</div>
                                                    <div class="text-muted small">#ADM-012</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Secrétaire de direction</td>
                                        <td><span class="department-badge bg-info">Secrétariat</span></td>
                                        <td>claire.moreau@ecole.fr</td>
                                        <td><span class="status-badge bg-success">En service</span></td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="btn btn-sm btn-outline-primary action-btn me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success action-btn me-1">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger action-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name=Pierre+Lefevre&background=1cc88a&color=fff" class="user-avatar me-3" alt="Pierre Lefevre">
                                                <div>
                                                    <div class="fw-bold">Pierre Lefevre</div>
                                                    <div class="text-muted small">#ADM-023</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Comptable</td>
                                        <td><span class="department-badge bg-success">Comptabilité</span></td>
                                        <td>pierre.lefevre@ecole.fr</td>
                                        <td><span class="status-badge bg-warning text-dark">En congé</span></td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="btn btn-sm btn-outline-primary action-btn me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success action-btn me-1">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger action-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name=Sophie+Garnier&background=36b9cc&color=fff" class="user-avatar me-3" alt="Sophie Garnier">
                                                <div>
                                                    <div class="fw-bold">Sophie Garnier</div>
                                                    <div class="text-muted small">#ADM-034</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Responsable RH</td>
                                        <td><span class="department-badge bg-warning text-dark">Ressources Humaines</span></td>
                                        <td>sophie.garnier@ecole.fr</td>
                                        <td><span class="status-badge bg-success">En service</span></td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="btn btn-sm btn-outline-primary action-btn me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success action-btn me-1">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger action-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name=Thomas+Mercier&background=e74a3b&color=fff" class="user-avatar me-3" alt="Thomas Mercier">
                                                <div>
                                                    <div class="fw-bold">Thomas Mercier</div>
                                                    <div class="text-muted small">#ADM-045</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Technicien maintenance</td>
                                        <td><span class="department-badge bg-secondary">Maintenance</span></td>
                                        <td>thomas.mercier@ecole.fr</td>
                                        <td><span class="status-badge bg-danger">Absent</span></td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="btn btn-sm btn-outline-primary action-btn me-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success action-btn me-1">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger action-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Affichage de 1 à 5 sur 42 entrées
                            </div>
                            <nav>
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#">Précédent</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Suivant</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
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
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Basic search functionality
            $('.search-box input').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Select all checkbox
            $('thead input[type="checkbox"]').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('tbody input[type="checkbox"]').prop('checked', isChecked);
            });
            
            // Filter by department
            $('select').on('change', function() {
                const department = $(this).val();
                if (department) {
                    $('table tbody tr').each(function() {
                        const rowDepartment = $(this).find('.department-badge').text().toLowerCase();
                        $(this).toggle(rowDepartment.includes(department.toLowerCase()) || department === "");
                    });
                }
            });
        });
    </script>
<?php include 'footer.php'; ?>