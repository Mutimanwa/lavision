<?php include 'header.php'; ?>

<div class="card mb-3">
    <div class="card-header ">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">Gestion des Options</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addOptionModal">
                    <span class="fas fa-plus me-1"></span>Nouvelle Option
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xxl-12">
        <div class="card ">
            <div id="tableExample3"
                data-list='{"valueNames":["name","description","students","status"],"page":5,"pagination":true}'>
                <div class="card-header">
                    <div class="row justify-content-end g-0">
                        <div class="col">
                            <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Liste des options disponibles</h5>
                        </div>
                        <div class="col-auto col-sm-5 mb-3">
                            <form>
                                <div class="input-group">
                                    <input class="form-control form-control-sm shadow-none search" type="search"
                                        placeholder="Rechercher..." aria-label="search" />
                                    <div class="input-group-text bg-transparent">
                                        <span class="fa fa-search fs-10 text-600"></span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-bordered table-striped fs-10 mb-0">
                            <thead class="bg-200">
                                <tr>
                                    <th class="text-900 fw-semi-bold text-center sort" data-sort="name">Nom de l'option</th>
                                    <th class="text-900 fw-semi-bold text-center sort" data-sort="description">Description</th>
                                    <th class="text-900 fw-semi-bold text-center sort" data-sort="students">Élèves inscrits</th>
                                    <th class="text-900 fw-semi-bold text-center sort" data-sort="status">Statut</th>
                                    <th class="text-900 fw-semi-bold text-center no-sort">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle  white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Scientifique</a>
                                    </td>
                                    <td class="align-middle text-center description">Option pour les sciences et mathématiques</td>
                                    <td class="align-middle text-center students text-center">25</td>
                                    <td class="align-middle text-center status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle text-center white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="customer-dropdown-0" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="customer-dropdown-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editOptionModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle  white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Littéraire</a>
                                    </td>
                                    <td class="align-middle text-center description">Option axée sur les langues et littérature</td>
                                    <td class="align-middle text-center students text-center">18</td>
                                    <td class="align-middle text-center status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle text-center white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="customer-dropdown-1" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="customer-dropdown-1">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editOptionModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle  white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Économique</a>
                                    </td>
                                    <td class="align-middle text-center description">Sciences économiques et sociales</td>
                                    <td class="align-middle text-center students text-center">12</td>
                                    <td class="align-middle text-center status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle text-center white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="customer-dropdown-2" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="customer-dropdown-2">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editOptionModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle  white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Technologique</a>
                                    </td>
                                    <td class="align-middle text-center description">Sciences et technologies industrielles</td>
                                    <td class="align-middle text-center students text-center">15</td>
                                    <td class="align-middle text-center status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle text-center white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="customer-dropdown-3" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="customer-dropdown-3">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editOptionModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle  white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Artistique</a>
                                    </td>
                                    <td class="align-middle text-center description">Arts visuels et arts du spectacle</td>
                                    <td class="align-middle text-center students text-center">8</td>
                                    <td class="align-middle text-center status text-center">
                                        <span class="badge badge-subtle-warning">Inactive</span>
                                    </td>
                                    <td class="align-middle text-center white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="customer-dropdown-4" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="customer-dropdown-4">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editOptionModal">Modifier</a>
                                                    <a class="dropdown-item text-success" href="#!">Activer</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-center mt-3">
                        <button class="btn btn-sm btn-falcon-default me-1" type="button" title="Previous"
                            data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
                        <ul class="pagination mb-0"></ul>
                        <button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next" 
                            data-list-pagination="next"><span class="fas fa-chevron-right"></span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Modal pour ajouter une nouvelle option -->
<div class="modal fade" id="addOptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une nouvelle option</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="optionName">Nom de l'option</label>
                        <input class="form-control" id="optionName" type="text" placeholder="Saisir le nom de l'option" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="optionDescription">Description</label>
                        <textarea class="form-control" id="optionDescription" rows="3" placeholder="Description de l'option" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="optionStatus" type="checkbox" checked />
                            <label class="form-check-label" for="optionStatus">Option active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="saveOption">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour modifier une option -->
<div class="modal fade" id="editOptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'option</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="editOptionName">Nom de l'option</label>
                        <input class="form-control" id="editOptionName" type="text" value="Scientifique" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editOptionDescription">Description</label>
                        <textarea class="form-control" id="editOptionDescription" rows="3" required>Option pour les sciences et mathématiques</textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="editOptionStatus" type="checkbox" checked />
                            <label class="form-check-label" for="editOptionStatus">Option active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="updateOption">Mettre à jour</button>
            </div>
        </div>
    </div>
</div>

<script src="vendors/echarts/echarts.min.js"></script>


<?php include 'footer.php'; ?>
<script>
    // Initialisation des tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Gestion de l'événement d'enregistrement
    document.getElementById('saveOption').addEventListener('click', function() {
        const optionName = document.getElementById('optionName').value;
        const optionDescription = document.getElementById('optionDescription').value;
        
        if (optionName && optionDescription) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Option "${optionName}" ajoutée avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('addOptionModal')).hide();
            
            // Réinitialiser le formulaire
            document.getElementById('optionName').value = '';
            document.getElementById('optionDescription').value = '';
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });

    // Gestion de l'événement de mise à jour
    document.getElementById('updateOption').addEventListener('click', function() {
        const optionName = document.getElementById('editOptionName').value;
        const optionDescription = document.getElementById('editOptionDescription').value;
        
        if (optionName && optionDescription) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Option "${optionName}" mise à jour avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('editOptionModal')).hide();
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
=
</script>