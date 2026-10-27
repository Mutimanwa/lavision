<?php include 'header.php'; ?>

<div class="card mb-3">
    <div class="card-header ">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">Gestion des Classes</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addClassModal">
                    <span class="fas fa-plus me-1"></span>Nouvelle Classe
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xxl-12">
        <div class="card">
            <div id="tableClasses"
                data-list='{"valueNames":["name","level","option","capacity","students","status"],"page":5,"pagination":true}'>
                <div class="card-header">
                    <div class="row justify-content-end g-0">
                        <div class="col">
                            <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Liste des classes</h5>
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
                        <table class="table table-sm table-bordered table-striped fs-10 mb-0">
                            <thead class="bg-200">
                                <tr>
                                    <th class="text-900 fw-medium text-center sort" data-sort="name">Nom de la classe</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="level">Niveau</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="option">Option</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="capacity">Capacité</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="students">Élèves inscrits</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="status">Statut</th>
                                    <th class="text-900 fw-medium text-center no-sort">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <a href="#!" class="fw-semi-bold">6ème A</a>
                                    </td>
                                    <td class="align-middle level text-center">6ème</td>
                                    <td class="align-middle option text-center">Générale</td>
                                    <td class="align-middle capacity text-center">35</td>
                                    <td class="align-middle students text-center">32</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="class-dropdown-0" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="class-dropdown-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editClassModal">Modifier</a>
                                                    <a class="dropdown-item" href="list-eleve.php">Voir les élèves</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <a href="#!" class="fw-semi-bold">5ème B</a>
                                    </td>
                                    <td class="align-middle level text-center">5ème</td>
                                    <td class="align-middle option text-center">Scientifique</td>
                                    <td class="align-middle capacity text-center">30</td>
                                    <td class="align-middle students text-center">28</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="class-dropdown-1" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="class-dropdown-1">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editClassModal">Modifier</a>
                                                    <a class="dropdown-item" href="list-eleve.php">Voir les élèves</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <a href="#!" class="fw-semi-bold">4ème C</a>
                                    </td>
                                    <td class="align-middle level text-center">4ème</td>
                                    <td class="align-middle option text-center">Littéraire</td>
                                    <td class="align-middle capacity text-center">30</td>
                                    <td class="align-middle students text-center">25</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="class-dropdown-2" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="class-dropdown-2">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editClassModal">Modifier</a>
                                                    <a class="dropdown-item" href="list-eleve.php">Voir les élèves</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <a href="#!" class="fw-semi-bold">3ème D</a>
                                    </td>
                                    <td class="align-middle level text-center">3ème</td>
                                    <td class="align-middle option text-center">Économique</td>
                                    <td class="align-middle capacity text-center">30</td>
                                    <td class="align-middle students text-center">22</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="class-dropdown-3" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="class-dropdown-3">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editClassModal">Modifier</a>
                                                    <a class="dropdown-item" href="list-eleve.php">Voir les élèves</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <a href="#!" class="fw-semi-bold">2nde T</a>
                                    </td>
                                    <td class="align-middle level text-center">2nde</td>
                                    <td class="align-middle option text-center">Technologique</td>
                                    <td class="align-middle capacity text-center">25</td>
                                    <td class="align-middle students text-center">15</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-warning">Inactive</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="class-dropdown-4" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="class-dropdown-4">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editClassModal">Modifier</a>
                                                    <a class="dropdown-item" href="list-eleve.php">Voir les élèves</a>
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

<!-- Modal pour ajouter une nouvelle classe -->
<div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une nouvelle classe</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="className">Nom de la classe</label>
                        <input class="form-control" id="className" type="text" placeholder="Ex: 6ème A" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="classLevel">Niveau</label>
                        <select class="form-select" id="classLevel" required>
                            <option value="" selected disabled>Sélectionner un niveau</option>
                            <option value="6ème">6ème</option>
                            <option value="5ème">5ème</option>
                            <option value="4ème">4ème</option>
                            <option value="3ème">3ème</option>
                            <option value="2nde">2nde</option>
                            <option value="1ère">1ère</option>
                            <option value="Terminale">Terminale</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="classOption">Option</label>
                        <select class="form-select" id="classOption" required>
                            <option value="" selected disabled>Sélectionner une option</option>
                            <option value="Générale">Générale</option>
                            <option value="Scientifique">Scientifique</option>
                            <option value="Littéraire">Littéraire</option>
                            <option value="Économique">Économique</option>
                            <option value="Technologique">Technologique</option>
                            <option value="Artistique">Artistique</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="classCapacity">Capacité maximale</label>
                        <input class="form-control" id="classCapacity" type="number" min="1" max="50" placeholder="Ex: 35" required />
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="classStatus" type="checkbox" checked />
                            <label class="form-check-label" for="classStatus">Classe active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="saveClass">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour modifier une classe -->
<div class="modal fade" id="editClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la classe</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="editClassName">Nom de la classe</label>
                        <input class="form-control" id="editClassName" type="text" value="6ème A" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editClassLevel">Niveau</label>
                        <select class="form-select" id="editClassLevel" required>
                            <option value="6ème" selected>6ème</option>
                            <option value="5ème">5ème</option>
                            <option value="4ème">4ème</option>
                            <option value="3ème">3ème</option>
                            <option value="2nde">2nde</option>
                            <option value="1ère">1ère</option>
                            <option value="Terminale">Terminale</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editClassOption">Option</label>
                        <select class="form-select" id="editClassOption" required>
                            <option value="Générale" selected>Générale</option>
                            <option value="Scientifique">Scientifique</option>
                            <option value="Littéraire">Littéraire</option>
                            <option value="Économique">Économique</option>
                            <option value="Technologique">Technologique</option>
                            <option value="Artistique">Artistique</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editClassCapacity">Capacité maximale</label>
                        <input class="form-control" id="editClassCapacity" type="number" min="1" max="50" value="35" required />
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="editClassStatus" type="checkbox" checked />
                            <label class="form-check-label" for="editClassStatus">Classe active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="updateClass">Mettre à jour</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialisation des tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Gestion de l'événement d'enregistrement
    document.getElementById('saveClass').addEventListener('click', function() {
        const className = document.getElementById('className').value;
        const classLevel = document.getElementById('classLevel').value;
        const classOption = document.getElementById('classOption').value;
        const classCapacity = document.getElementById('classCapacity').value;
        
        if (className && classLevel && classOption && classCapacity) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Classe "${className}" ajoutée avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('addClassModal')).hide();
            
            // Réinitialiser le formulaire
            document.getElementById('className').value = '';
            document.getElementById('classLevel').value = '';
            document.getElementById('classOption').value = '';
            document.getElementById('classCapacity').value = '';
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });

    // Gestion de l'événement de mise à jour
    document.getElementById('updateClass').addEventListener('click', function() {
        const className = document.getElementById('editClassName').value;
        const classLevel = document.getElementById('editClassLevel').value;
        const classOption = document.getElementById('editClassOption').value;
        const classCapacity = document.getElementById('editClassCapacity').value;
        
        if (className && classLevel && classOption && classCapacity) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Classe "${className}" mise à jour avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('editClassModal')).hide();
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });

    
</script>

<?php include 'footer.php'; ?>