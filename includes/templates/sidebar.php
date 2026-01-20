<nav class="navbar navbar-light navbar-vertical navbar-expand-xl">
    <div class="d-flex align-items-center">
        <div class="toggle-icon-wrapper">
            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip"
                data-bs-placement="left" title="Basculer la navigation">
                <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
            </button>
        </div>
        <a class="navbar-brand" href="index.php">
            <div class="d-flex align-items-center py-3">
                <img class="me-2" src="<?= IMAGES_URL ?>icons/spot-illustrations/falcon.png" alt="" width="40" /><span
                    class="font-sans-serif text-primary">falcon</span>
            </div>
        </a>
    </div>
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content scrollbar">
            <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
                <!-- Tableau de bord -->
                <li class="nav-item">
                    <a class="nav-link <?= is_active('dashboard') ?>" href="<?= url('dashboard') ?>">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-chart-pie"></span></span>
                            <span class="nav-link-text ps-1">Tableau de bord</span>
                        </div>
                    </a>
                </li>

                <!-- Gestion des élèves -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Gestion des élèves</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Admission -->
                    <a class="nav-link <?= is_active('eleves/admission') ?>" href="<?= url('eleves/admission') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-user-plus"></span></span>
                            <span class="nav-link-text ps-1">Admission</span>
                        </div>
                    </a>

                    <!-- Liste élèves -->
                    <a class="nav-link <?= is_active('eleves') ?>" href="<?= url('eleves') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-user-graduate"></span></span>
                            <span class="nav-link-text ps-1">Élèves</span>
                        </div>
                    </a>

                    <!-- Parents -->
                    <a class="nav-link <?= is_active('eleves/parents') ?>" href="<?= url('eleves/parents') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="far fa-user-group"></span></span>
                            <span class="nav-link-text ps-1">Parents</span>
                        </div>
                    </a>
                </li>

                <!-- Gestion académique -->
                <li class="nav-item">
                    <div class="row navbaar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Gestion académique</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Options -->
                    <a class="nav-link <?= is_active('academique/options') ?>" href="<?= url('academique/options') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-layer-group"></span></span>
                            <span class="nav-link-text ps-1">Options</span>
                        </div>
                    </a>

                    <!-- Classes -->
                    <a class="nav-link <?= is_active('academique/classes') ?>" href="<?= url('academique/classes') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-chalkboard"></span></span>
                            <span class="nav-link-text ps-1">Classes</span>
                        </div>
                    </a>

                    <!-- Matières -->
                    <a class="nav-link <?= is_active('academique/matieres') ?>" href="<?= url('academique/matieres') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-book-open"></span></span>
                            <span class="nav-link-text ps-1">Matières</span>
                        </div>
                    </a>

                    <!-- Horaires -->
                    <a class="nav-link <?= is_active('academique/horaires') ?>" href="<?= url('academique/horaires') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-clock"></span></span>
                            <span class="nav-link-text ps-1">Horaires</span>
                        </div>
                    </a>
                </li>

                <!-- Gestion du personnel -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Gestion du personnel</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Professeurs -->
                    <a class="nav-link <?= is_active('personnel/professeurs') ?>"
                        href="<?= url('personnel/professeurs') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-chalkboard-teacher"></span></span>
                            <span class="nav-link-text ps-1">Professeurs</span>
                        </div>
                    </a>

                    <!-- Personnel administratif -->
                    <a class="nav-link <?= is_active('personnel/personnels') ?>"
                        href="<?= url('personnel/personnels') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-user-tie"></span></span>
                            <span class="nav-link-text ps-1">Personnel administratif</span>
                        </div>
                    </a>
                </li>

                <!-- Cours et examens -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Cours et examens</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Planification des cours -->
                    <a class="nav-link <?= is_active('academique/cours') ?>" href="<?= url('academique/cours') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-book"></span></span>
                            <span class="nav-link-text ps-1">Planification des cours</span>
                        </div>
                    </a>

                    <!-- Absences -->
                    <a class="nav-link <?= is_active('academique/absences') ?>" href="<?= url('academique/absences') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-user-clock"></span></span>
                            <span class="nav-link-text ps-1">Absences des élèves</span>
                        </div>
                    </a>

                    <!-- Examens -->
                    <a class="nav-link <?= is_active('academique/examens') ?>" href="<?= url('academique/examens') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-pen-alt"></span></span>
                            <span class="nav-link-text ps-1">Examens/évaluations</span>
                        </div>
                    </a>

                    <!-- Notes -->
                    <a class="nav-link <?= is_active('academique/notes') ?>" href="<?= url('academique/notes') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-file-signature"></span></span>
                            <span class="nav-link-text ps-1">Notes</span>
                        </div>
                    </a>
                </li>

                <!-- Finance -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Finance</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Frais scolaires -->
                    <a class="nav-link <?= is_active('finance/frais-scolaires') ?>"
                        href="<?= url('finance/frais-scolaires') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-coins"></span></span>
                            <span class="nav-link-text ps-1">Frais scolaires</span>
                        </div>
                    </a>

                    <!-- Paiements -->
                    <!-- <a class="nav-link <?= is_active('finance/paiements') ?>" href="<?= url('finance/paiements') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-cash-register"></span></span>
                            <span class="nav-link-text ps-1">Paiements</span>
                        </div>
                    </a> -->

                    <!-- Salaires -->
                    <!-- <a class="nav-link <?= is_active('finance/salaires') ?>" href="<?= url('finance/salaires') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-money-check-alt"></span></span>
                            <span class="nav-link-text ps-1">Salaires</span>
                        </div>
                    </a> -->
                </li>

                <!-- Rapports & Statistiques -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Rapports & Statistiques</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Académique -->
                    <a class="nav-link <?= is_active('rapports/academique') ?>" href="<?= url('rapports/academique') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-chart-line"></span></span>
                            <span class="nav-link-text ps-1">Académique</span>
                        </div>
                    </a>

                    <!-- Financier -->
                    <!-- <a class="nav-link <?= is_active('rapports/financier') ?>" href="<?= url('rapports/financier') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-file-invoice-dollar"></span></span>
                            <span class="nav-link-text ps-1">Financier</span>
                        </div>
                    </a> -->

                    <!-- Personnel -->
                    <!-- <a class="nav-link <?= is_active('rapports/personnel') ?>" href="<?= url('rapports/personnel') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-id-badge"></span></span>
                            <span class="nav-link-text ps-1">Personnel</span>
                        </div>
                    </a> -->
                </li>

                <!-- Administration / Paramètres -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Administration / Paramètres</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Utilisateurs & rôles -->
                    <a class="nav-link <?= is_active('administration/utilisateurs') ?>"
                        href="<?= url('administration/utilisateurs') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-users-cog"></span></span>
                            <span class="nav-link-text ps-1">Utilisateurs & rôles</span>
                        </div>
                    </a>

                    <!-- Année scolaire -->
                    <a class="nav-link <?= is_active('administration/annee-scolaire') ?>"
                        href="<?= url('administration/annee-scolaire') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-calendar-alt"></span></span>
                            <span class="nav-link-text ps-1">Année scolaire</span>
                        </div>
                    </a>

                    <!-- Configuration générale -->
                    <a class="nav-link <?= is_active('administration/parametres') ?>"
                        href="<?= url('administration/parametres') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-cogs"></span></span>
                            <span class="nav-link-text ps-1">Paramaetres</span>
                        </div>
                    </a>

                    <!-- Réinitialiser la base de données -->
                    <a class="nav-link text-danger" href="<?= url('administration/reinit-db') ?>" role="button"
                        onclick="return confirm('ATTENTION : Cette action est irréversible et va effacer toutes les données existantes. Voulez-vous vraiment réinitialiser la base de données ?');">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-database"></span></span>
                            <span class="nav-link-text ps-1">Backup </span>
                        </div>
                    </a>
                </li>

                <!-- Documentation -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">Documentation</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>

                    <!-- Getting started -->
                    <a class="nav-link <?= is_active('documentation/getting-started') ?>"
                        href="<?= url('documentation/getting-started') ?>" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-rocket"></span></span>
                            <span class="nav-link-text ps-1">Getting started</span>
                        </div>
                    </a>

                    <!-- FAQ -->
                    <a class="nav-link <?= is_active('documentation/faq') ?>" href="<?= url('documentation/faq') ?>"
                        role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-question-circle"></span></span>
                            <span class="nav-link-text ps-1">FAQ</span>
                        </div>
                    </a>
                </li>
            </ul>


            <!-- Section Falcon (optionnelle) -->
            <div class="settings my-3">
                <div class="card shadow-none">
                    <div class="card-body alert mb-0" role="alert">
                        <div class="btn-close-falcon-container">
                            <button class="btn btn-link btn-close-falcon p-0" aria-label="Close"
                                data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center">
                            <img src="<?= IMAGES_URL ?>icons/spot-illustrations/navbar-vertical.png" alt=""
                                width="80" />
                            <p class="fs-11 mt-2">
                                Loving what you see? <br />Get your copy of
                                <a href="#!">Falcon</a>
                            </p>
                            <div class="d-grid">
                                <a class="btn btn-sm btn-primary"
                                    href="https://themes.getbootstrap.com/product/falcon-admin-dashboard-webapp-template/"
                                    target="_blank">Purchase</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>