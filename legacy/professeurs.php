<?php include 'header.php'; ?>

<div class="card mb-3">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">Gestion des Professeurs</h5>
            </div>
            <div class="col-auto">
                <!-- <button class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addProfessorModal">
                    <span class="fas fa-plus me-1"></span>Nouveau Professeur
                </button> -->
                <a href="nouveau-prof.php" class="btn btn-falcon-primary">
                    Nouveau professeur
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xxl-9">
        <div class="card">
            <div id="tableProfessors"
                data-list='{"valueNames":["name","matricule","subjects","classes","status"],"page":5,"pagination":true}'>
                <div class="card-header">
                    <div class="row justify-content-end g-0">
                        <div class="col">
                            <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Liste des professeurs</h5>
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
                                    <th class="text-900 fw-medium text-center sort" data-sort="name">Nom & Prénom</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="matricule">Matricule</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="subjects">Matières enseignées</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="classes">Classes</th>
                                    <th class="text-900 fw-medium text-center sort" data-sort="status">Statut</th>
                                    <th class="text-900 fw-medium text-center no-sort">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xl me-2">
                                                <img class="rounded-circle" src="assets/img/team/1.jpg" alt="M. Dupont">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0">M. Dupont</h6>
                                                <p class="mb-0 fs-10 text-600">dupont@ecole.edu</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle matricule text-center">PROF-001</td>
                                    <td class="align-middle subjects text-center">
                                        <span class="badge bg-primary me-1">Mathématiques</span>
                                    </td>
                                    <td class="align-middle classes text-center">
                                        <span class="badge bg-info me-1">6ème A</span>
                                        <span class="badge bg-info">5ème B</span>
                                    </td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Actif</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="professor-dropdown-0" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="professor-dropdown-0">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#viewProfessorModal">Voir profil</a>
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editProfessorModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xl me-2">
                                                <img class="rounded-circle" src="assets/img/team/2.jpg" alt="Mme. Martin">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0">Mme. Martin</h6>
                                                <p class="mb-0 fs-10 text-600">martin@ecole.edu</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle matricule text-center">PROF-002</td>
                                    <td class="align-middle subjects text-center">
                                        <span class="badge bg-info me-1">Physique</span>
                                        <span class="badge bg-danger">Chimie</span>
                                    </td>
                                    <td class="align-middle classes text-center">
                                        <span class="badge bg-info me-1">5ème B</span>
                                        <span class="badge bg-info">4ème C</span>
                                    </td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Actif</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="professor-dropdown-1" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="professor-dropdown-1">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#viewProfessorModal">Voir profil</a>
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editProfessorModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xl me-2">
                                                <img class="rounded-circle" src="assets/img/team/3.jpg" alt="M. Leroy">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0">M. Leroy</h6>
                                                <p class="mb-0 fs-10 text-600">leroy@ecole.edu</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle matricule text-center">PROF-003</td>
                                    <td class="align-middle subjects text-center">
                                        <span class="badge bg-success me-1">Français</span>
                                        <span class="badge bg-warning">Littérature</span>
                                    </td>
                                    <td class="align-middle classes text-center">
                                        <span class="badge bg-info me-1">4ème C</span>
                                        <span class="badge bg-info">3ème D</span>
                                    </td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Actif</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="professor-dropdown-2" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="professor-dropdown-2">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#viewProfessorModal">Voir profil</a>
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editProfessorModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xl me-2">
                                                <img class="rounded-circle" src="assets/img/team/4.jpg" alt="Mme. Bernard">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0">Mme. Bernard</h6>
                                                <p class="mb-0 fs-10 text-600">bernard@ecole.edu</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle matricule text-center">PROF-004</td>
                                    <td class="align-middle subjects text-center">
                                        <span class="badge bg-warning me-1">Histoire</span>
                                        <span class="badge bg-secondary">Géographie</span>
                                    </td>
                                    <td class="align-middle classes text-center">
                                        <span class="badge bg-info me-1">3ème D</span>
                                        <span class="badge bg-info">2nde T</span>
                                    </td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-success">Actif</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="professor-dropdown-3" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="professor-dropdown-3">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#viewProfessorModal">Voir profil</a>
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editProfessorModal">Modifier</a>
                                                    <a class="dropdown-item text-danger" href="#!">Désactiver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="btn-reveal-trigger">
                                    <td class="align-middle white-space-nowrap name text-center">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xl me-2">
                                                <div class="avatar-name rounded-circle bg-primary">
                                                    <span class="text-white">MP</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0">M. Petit</h6>
                                                <p class="mb-0 fs-10 text-600">petit@ecole.edu</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle matricule text-center">PROF-005</td>
                                    <td class="align-middle subjects text-center">
                                        <span class="badge bg-danger me-1">SVT</span>
                                        <span class="badge bg-info">Biologie</span>
                                    </td>
                                    <td class="align-middle classes text-center">
                                        <span class="badge bg-info me-1">6ème A</span>
                                        <span class="badge bg-info">5ème B</span>
                                    </td>
                                    <td class="align-middle status text-center">
                                        <span class="badge badge-subtle-warning">Inactif</span>
                                    </td>
                                    <td class="align-middle white-space-nowrap py-2 text-center">
                                        <div class="dropdown font-sans-serif position-static d-inline-block">
                                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                type="button" id="professor-dropdown-4" data-bs-toggle="dropdown"
                                                data-boundary="window" aria-haspopup="true" aria-expanded="false">
                                                <span class="fas fa-ellipsis-h fs-10"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end border py-0"
                                                aria-labelledby="professor-dropdown-4">
                                                <div class="py-2">
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#viewProfessorModal">Voir profil</a>
                                                    <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#editProfessorModal">Modifier</a>
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
    
    <div class="col-xxl-3">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Statistiques des professeurs</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded-2 p-3">
                            <h3 class="text-primary mb-0">4</h3>
                            <p class="fs-10 mb-0">Professeurs actifs</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded-2 p-3">
                            <h3 class="text-warning mb-0">1</h3>
                            <p class="fs-10 mb-0">Professeurs inactifs</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded-2 p-3">
                            <h3 class="text-info mb-0">12</h3>
                            <p class="fs-10 mb-0">Matières enseignées</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="mb-3">Répartition par matière</h6>
                    <div class="d-flex align-items-center mb-2">
                        <span class="fas fa-circle fs-11 text-primary me-2"></span>
                        <div class="flex-1">
                            <p class="mb-0 fs-10">Mathématiques</p>
                        </div>
                        <span class="fw-semi-bold">1 prof</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="fas fa-circle fs-11 text-info me-2"></span>
                        <div class="flex-1">
                            <p class="mb-0 fs-10">Physique/Chimie</p>
                        </div>
                        <span class="fw-semi-bold">1 prof</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="fas fa-circle fs-11 text-success me-2"></span>
                        <div class="flex-1">
                            <p class="mb-0 fs-10">Français/Littérature</p>
                        </div>
                        <span class="fw-semi-bold">1 prof</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="fas fa-circle fs-11 text-warning me-2"></span>
                        <div class="flex-1">
                            <p class="mb-0 fs-10">Histoire/Géographie</p>
                        </div>
                        <span class="fw-semi-bold">1 prof</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="fas fa-circle fs-11 text-danger me-2"></span>
                        <div class="flex-1">
                            <p class="mb-0 fs-10">SVT/Biologie</p>
                        </div>
                        <span class="fw-semi-bold">1 prof</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un nouveau professeur -->
<div class="modal fade" id="addProfessorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un nouveau professeur</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="professorCivility">Civilité</label>
                            <select class="form-select" id="professorCivility" required>
                                <option value="" selected disabled>Sélectionner</option>
                                <option value="M.">M.</option>
                                <option value="Mme">Mme</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="professorLastName">Nom</label>
                            <input class="form-control" id="professorLastName" type="text" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="professorFirstName">Prénom</label>
                            <input class="form-control" id="professorFirstName" type="text" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="professorEmail">Email</label>
                            <input class="form-control" id="professorEmail" type="email" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="professorPhone">Téléphone</label>
                            <input class="form-control" id="professorPhone" type="tel" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="professorMatricule">Matricule</label>
                            <input class="form-control" id="professorMatricule" type="text" required />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="professorSubjects">Matières enseignées</label>
                        <select class="form-select" id="professorSubjects" multiple required>
                            <option value="Mathématiques">Mathématiques</option>
                            <option value="Physique">Physique</option>
                            <option value="Chimie">Chimie</option>
                            <option value="Français">Français</option>
                            <option value="Littérature">Littérature</option>
                            <option value="Histoire">Histoire</option>
                            <option value="Géographie">Géographie</option>
                            <option value="SVT">SVT</option>
                            <option value="Biologie">Biologie</option>
                            <option value="Anglais">Anglais</option>
                            <option value="Espagnol">Espagnol</option>
                            <option value="Philosophie">Philosophie</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="professorClasses">Classes assignées</label>
                        <select class="form-select" id="professorClasses" multiple>
                            <option value="6ème A">6ème A</option>
                            <option value="5ème B">5ème B</option>
                            <option value="4ème C">4ème C</option>
                            <option value="3ème D">3ème D</option>
                            <option value="2nde T">2nde T</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="professorStatus" type="checkbox" checked />
                            <label class="form-check-label" for="professorStatus">Professeur actif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="saveProfessor">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour voir le profil d'un professeur -->
<div class="modal fade" id="viewProfessorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Profil du Professeur</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-3 text-center">
                        <div class="avatar avatar-4xl mb-3">
                            <img class="rounded-circle" src="assets/img/team/1.jpg" alt="M. Dupont">
                        </div>
                        <h5>M. Dupont</h5>
                        <p class="text-600">PROF-001</p>
                        <span class="badge badge-subtle-success">Actif</span>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informations personnelles</h6>
                                <p class="mb-1"><strong>Email:</strong> dupont@ecole.edu</p>
                                <p class="mb-1"><strong>Téléphone:</strong> +33 6 12 34 56 78</p>
                                <p class="mb-0"><strong>Date d'embauche:</strong> 01/09/2020</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Statistiques</h6>
                                <p class="mb-1"><strong>Matières:</strong> 1 matière</p>
                                <p class="mb-1"><strong>Classes:</strong> 2 classes</p>
                                <p class="mb-0"><strong>Heures/semaine:</strong> 18h</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Matières enseignées</h6>
                        <ul class="list-unstyled">
                            <li><span class="fas fa-book me-2 text-primary"></span>Mathématiques</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Classes assignées</h6>
                        <ul class="list-unstyled">
                            <li><span class="fas fa-users me-2 text-info"></span>6ème A</li>
                            <li><span class="fas fa-users me-2 text-info"></span>5ème B</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-primary" type="button" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour modifier un professeur -->
<div class="modal fade" id="editProfessorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le professeur</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editProfessorCivility">Civilité</label>
                            <select class="form-select" id="editProfessorCivility" required>
                                <option value="M." selected>M.</option>
                                <option value="Mme">Mme</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editProfessorLastName">Nom</label>
                            <input class="form-control" id="editProfessorLastName" type="text" value="Dupont" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editProfessorFirstName">Prénom</label>
                            <input class="form-control" id="editProfessorFirstName" type="text" value="Jean" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editProfessorEmail">Email</label>
                            <input class="form-control" id="editProfessorEmail" type="email" value="dupont@ecole.edu" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editProfessorPhone">Téléphone</label>
                            <input class="form-control" id="editProfessorPhone" type="tel" value="+33 6 12 34 56 78" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editProfessorMatricule">Matricule</label>
                            <input class="form-control" id="editProfessorMatricule" type="text" value="PROF-001" required />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editProfessorSubjects">Matières enseignées</label>
                        <select class="form-select" id="editProfessorSubjects" multiple required>
                            <option value="Mathématiques" selected>Mathématiques</option>
                            <option value="Physique">Physique</option>
                            <option value="Chimie">Chimie</option>
                            <option value="Français">Français</option>
                            <option value="Littérature">Littérature</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editProfessorClasses">Classes assignées</label>
                        <select class="form-select" id="editProfessorClasses" multiple>
                            <option value="6ème A" selected>6ème A</option>
                            <option value="5ème B" selected>5ème B</option>
                            <option value="4ème C">4ème C</option>
                            <option value="3ème D">3ème D</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="editProfessorStatus" type="checkbox" checked />
                            <label class="form-check-label" for="editProfessorStatus">Professeur actif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="updateProfessor">Mettre à jour</button>
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
    document.getElementById('saveProfessor').addEventListener('click', function() {
        const civility = document.getElementById('professorCivility').value;
        const lastName = document.getElementById('professorLastName').value;
        const firstName = document.getElementById('professorFirstName').value;
        const email = document.getElementById('professorEmail').value;
        const matricule = document.getElementById('professorMatricule').value;
        
        if (civility && lastName && firstName && email && matricule) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Professeur "${civility} ${lastName}" ajouté avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('addProfessorModal')).hide();
            
            // Réinitialiser le formulaire
            document.getElementById('professorCivility').value = '';
            document.getElementById('professorLastName').value = '';
            document.getElementById('professorFirstName').value = '';
            document.getElementById('professorEmail').value = '';
            document.getElementById('professorMatricule').value = '';
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });

    // Gestion de l'événement de mise à jour
    document.getElementById('updateProfessor').addEventListener('click', function() {
        const civility = document.getElementById('editProfessorCivility').value;
        const lastName = document.getElementById('editProfessorLastName').value;
        const firstName = document.getElementById('editProfessorFirstName').value;
        const email = document.getElementById('editProfessorEmail').value;
        const matricule = document.getElementById('editProfessorMatricule').value;
        
        if (civility && lastName && firstName && email && matricule) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Professeur "${civility} ${lastName}" mis à jour avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('editProfessorModal')).hide();
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
</script>

<?php include 'footer.php'; ?>