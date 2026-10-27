<?php include 'header.php'; ?>

<div class="card mb-3">
    <div class="card-header ">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">Gestion des Matières</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                    <span class="fas fa-plus me-1"></span>Nouvelle Matière
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card">
            <div id="tableSubjects"
                data-list='{"valueNames":["name","code","category","coefficient","status"],"page":10,"pagination":true}'>
                <div class="card-header">
                    <div class="row justify-content-end g-0">
                        <div class="col">
                            <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Liste des matières</h5>
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
                                    <th class="text-900   fw-medium text-center sort" data-sort="name">Nom de la matière</th>
                                    <th class="text-900  fw-medium text-center sort" data-sort="code">Code</th>
                                    <th class="text-900  fw-medium text-center sort" data-sort="category">Catégorie</th>
                                    <th class="text-900  fw-medium text-center sort" data-sort="coefficient">Coefficient</th>
                                    <th class="text-900  fw-medium text-center sort" data-sort="status">Statut</th>
                                    <th class="text-900  fw-medium text-center no-sort">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Mathématiques</a>
                                    </td>
                                    <td class="align-middle code text-center">MATH</td>
                                    <td class="align-middle category text-center">Scientifique</td>
                                    <td class="align-middle coefficient text-center">4</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-0" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Physique-Chimie</a>
                                    </td>
                                    <td class="align-middle code text-center">PHY</td>
                                    <td class="align-middle category text-center">Scientifique</td>
                                    <td class="align-middle coefficient text-center">3</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-1" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-1">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Français</a>
                                    </td>
                                    <td class="align-middle code text-center">FR</td>
                                    <td class="align-middle category text-center">Littéraire</td>
                                    <td class="align-middle coefficient text-center">3</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-2" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-2">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Histoire-Géographie</a>
                                    </td>
                                    <td class="align-middle code text-center">HIST</td>
                                    <td class="align-middle category text-center">Littéraire</td>
                                    <td class="align-middle coefficient text-center">2</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-3" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-3">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Éducation Musicale</a>
                                    </td>
                                    <td class="align-middle code text-center">MUS</td>
                                    <td class="align-middle category text-center">Artistique</td>
                                    <td class="align-middle coefficient text-center">1</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-warning">Inactive</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-4" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-4">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-success" href="#!">Activer</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Sciences Économiques et Sociales</a>
                                    </td>
                                    <td class="align-middle code text-center">SES</td>
                                    <td class="align-middle category text-center">Économique</td>
                                    <td class="align-middle coefficient text-center">2</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-5" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-5">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Technologie</a>
                                    </td>
                                    <td class="align-middle code text-center">TECH</td>
                                    <td class="align-middle category text-center">Technologique</td>
                                    <td class="align-middle coefficient text-center">2</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-6" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-6">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name ">
                                        <a href="#!" class="fw-semi-bold">Éducation Physique et Sportive</a>
                                    </td>
                                    <td class="align-middle code text-center">EPS</td>
                                    <td class="align-middle category text-center">Générale</td>
                                    <td class="align-middle coefficient text-center">1</td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Active</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="subject-dropdown-7" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="subject-dropdown-7">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
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

<!-- Modal pour ajouter une nouvelle matière -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une nouvelle matière</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="subjectName">Nom de la matière</label>
                        <input class="form-control" id="subjectName" type="text" placeholder="Ex: Mathématiques" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="subjectCode">Code</label>
                        <input class="form-control" id="subjectCode" type="text" placeholder="Ex: MATH" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="subjectCategory">Catégorie</label>
                        <select class="form-select" id="subjectCategory" required>
                            <option value="" selected disabled>Sélectionner une catégorie</option>
                            <option value="Scientifique">Scientifique</option>
                            <option value="Littéraire">Littéraire</option>
                            <option value="Économique">Économique</option>
                            <option value="Technologique">Technologique</option>
                            <option value="Artistique">Artistique</option>
                            <option value="Générale">Générale</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="subjectCoefficient">Coefficient</label>
                        <input class="form-control" id="subjectCoefficient" type="number" min="1" max="10" placeholder="Ex: 4" required />
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="subjectStatus" type="checkbox" checked />
                            <label class="form-check-label" for="subjectStatus">Matière active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="saveSubject">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour modifier une matière -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la matière</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label" for="editSubjectName">Nom de la matière</label>
                        <input class="form-control" id="editSubjectName" type="text" value="Mathématiques" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editSubjectCode">Code</label>
                        <input class="form-control" id="editSubjectCode" type="text" value="MATH" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editSubjectCategory">Catégorie</label>
                        <select class="form-select" id="editSubjectCategory" required>
                            <option value="Scientifique" selected>Scientifique</option>
                            <option value="Littéraire">Littéraire</option>
                            <option value="Économique">Économique</option>
                            <option value="Technologique">Technologique</option>
                            <option value="Artistique">Artistique</option>
                            <option value="Générale">Générale</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editSubjectCoefficient">Coefficient</label>
                        <input class="form-control" id="editSubjectCoefficient" type="number" min="1" max="10" value="4" required />
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="editSubjectStatus" type="checkbox" checked />
                            <label class="form-check-label" for="editSubjectStatus">Matière active</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="updateSubject">Mettre à jour</button>
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
    document.getElementById('saveSubject').addEventListener('click', function() {
        const subjectName = document.getElementById('subjectName').value;
        const subjectCode = document.getElementById('subjectCode').value;
        const subjectCategory = document.getElementById('subjectCategory').value;
        const subjectCoefficient = document.getElementById('subjectCoefficient').value;
        
        if (subjectName && subjectCode && subjectCategory && subjectCoefficient) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Matière "${subjectName}" ajoutée avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('addSubjectModal')).hide();
            
            // Réinitialiser le formulaire
            document.getElementById('subjectName').value = '';
            document.getElementById('subjectCode').value = '';
            document.getElementById('subjectCategory').value = '';
            document.getElementById('subjectCoefficient').value = '';
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });

    // Gestion de l'événement de mise à jour
    document.getElementById('updateSubject').addEventListener('click', function() {
        const subjectName = document.getElementById('editSubjectName').value;
        const subjectCode = document.getElementById('editSubjectCode').value;
        const subjectCategory = document.getElementById('editSubjectCategory').value;
        const subjectCoefficient = document.getElementById('editSubjectCoefficient').value;
        
        if (subjectName && subjectCode && subjectCategory && subjectCoefficient) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Matière "${subjectName}" mise à jour avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('editSubjectModal')).hide();
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
</script>

<?php include 'footer.php'; ?>