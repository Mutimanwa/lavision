<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<!-- ===============================================-->
<!--    Document Title-->
<!-- ===============================================-->
<title>Falcon</title>

<!-- ===============================================-->
<!--    Favicons-->
<!-- ===============================================-->
<link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicons/apple-touch-icon.png" />
<link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicons/favicon-32x32.png" />
<link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicons/favicon-16x16.png" />
<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicons/favicon.ico" />
<link rel="manifest" href="assets/img/favicons/manifest.json" />
<meta name="msapplication-TileImage" content="assets/img/favicons/mstile-150x150.png" />
<meta name="theme-color" content="#ffffff" />
<script src="assets/js/config.js"></script>
<script src="vendors/simplebar/simplebar.min.js"></script>


<!-- ===============================================-->
<!--    Stylesheets-->
<!-- ===============================================-->
<link rel="preconnect" href="https://fonts.gstatic.com" />
<link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap"
    rel="stylesheet" />
<link href="vendors/simplebar/simplebar.min.css" rel="stylesheet" />
<link href="assets/css/theme.min.css" rel="stylesheet" id="style-default" />
<link href="assets/css/user.min.css" rel="stylesheet" id="user-style-default" />
<link rel="stylesheet" href="vendors/flatpickr/flatpickr.min.css">
<link rel="stylesheet" href="vendors/dropzone/dropzone.css">
</head>

<body>
<!-- ===============================================-->
<!--    Main Content-->
<!-- ===============================================-->
<main class="main" id="top">
    <div class="container" data-layout="container">
        <!-- bare de navigation  -->
        <nav class="navbar navbar-light navbar-vertical navbar-expand-xl" style="display: none">
            <script>
                var navbarStyle = localStorage.getItem("navbarStyle");
                if (navbarStyle && navbarStyle !== "transparent") {
                    document
                        .querySelector(".navbar-vertical")
                        .classList.add(`navbar-${navbarStyle}`);
                }
            </script>
            <div class="d-flex align-items-center">
                <div class="toggle-icon-wrapper">
                    <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle"
                        data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation">
                        <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
                    </button>
                </div>
                <a class="navbar-brand" href="index.php">
                    <div class="d-flex align-items-center py-3">
                        <img class="me-2" src="assets/img/icons/spot-illustrations/falcon.png" alt=""
                            width="40" /><span class="font-sans-serif text-primary">falcon</span>
                    </div>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
                <div class="navbar-vertical-content scrollbar">
                    <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
                        <li class="nav-item">

                            <a class="nav-link active " href="index.php">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-chart-pie"></span></span><span
                                        class="nav-link-text ps-1">Dashboard</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">Gestion des élèves</div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages-->
                            <a class="nav-link" href="inscription.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-user-plus"></span></span><span
                                        class="nav-link-text ps-1">Insciption</span>
                                </div>
                            </a><!-- parent pages-->
                            <a class="nav-link" href="list-eleve.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-user-graduate"></span></span><span
                                        class="nav-link-text ps-1">Eleves</span>
                                </div>
                            </a>
                            <!-- parent pages-->
                            <a class="nav-link" href="presences.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="far fa-calendar-check"></span></span><span
                                        class="nav-link-text ps-1">Presences</span>
                                </div>
                            </a>
                            <!-- parent pages-->

                        </li>
                        <li class="nav-item">
                            <div class="row navbaar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">Classes & matières</div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>

                            <!-- parent pages-->
                            <a class="nav-link" href="options.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-layer-group"></span></span><span
                                        class="nav-link-text ps-1">Options</span>
                                </div>
                            </a>


                            <a class="nav-link" href="classes.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-chalkboard"></span></span><span
                                        class="nav-link-text ps-1">Classes</span>
                                </div>
                            </a>
                            <a class="nav-link" href="matieres.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class=" fas fa-book-open"></span></span><span
                                        class="nav-link-text ps-1">Matières</span>
                                </div>
                            </a>
                            <a class="nav-link" href="horaires.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-clock"></span></span><span
                                        class="nav-link-text ps-1">Horaires</span>
                                </div>
                            </a>




                        </li>
                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">Gestion du personnel</div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages-->
                            <a class="nav-link" href="professeurs.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-chalkboard-teacher"></span></span><span
                                        class="nav-link-text ps-1">Professeurs</span>
                                </div>
                            </a>
                            <a class="nav-link" href="personnels.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-user-tie"></span></span><span
                                        class="nav-link-text ps-1">Personnel administratif</span>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">Cours et examens</div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages-->
                            <a class="nav-link" href="cours.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-book"></span></span><span
                                        class="nav-link-text ps-1">Planification des cours</span>
                                </div>
                            </a>
                            <a class="nav-link" href="absences" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-user-clock"></span></span><span
                                        class="nav-link-text ps-1">Absences des élèves</span>
                                </div>
                            </a>
                            <a class="nav-link" href="exames.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-pen-alt"></span></span><span
                                        class="nav-link-text ps-1">Examens/évaluations.</span>
                                </div>
                            </a>
                            <a class="nav-link" href="notes.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-file-signature"></span></span><span
                                        class="nav-link-text ps-1">Notes </span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">Finance</div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages-->
                            <a class="nav-link" href="frais-scolaire.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-coins"></span></span><span
                                        class="nav-link-text ps-1">Frais scolaires</span>
                                </div>
                            </a>
                            <a class="nav-link" href="paiement.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-cash-register"></span></span><span
                                        class="nav-link-text ps-1">Paiements</span>
                                </div>
                            </a>
                            <a class="nav-link" href="salaires.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-money-check-alt"></span></span><span
                                        class="nav-link-text ps-1">Salaires</span>
                                </div>
                            </a>
                        </li>
                        <!--  -->
                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">Rapports & Statistiques</div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages-->
                            <a class="nav-link" href="statistique-academique.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-chart-line"></span></span><span
                                        class="nav-link-text ps-1">Académique</span>
                                </div>
                            </a>
                            <a class="nav-link" href="statistique-financier.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-file-invoice-dollar"></span></span><span
                                        class="nav-link-text ps-1">Financier</span>
                                </div>
                            </a>
                            <a class="nav-link" href="statistique-personnel.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-id-badge"></span></span><span
                                        class="nav-link-text ps-1">Personnel</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">Administration / Paramètres</div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages-->
                            <a class="nav-link" href="utilisateurs.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-users-cog"></span></span><span
                                        class="nav-link-text ps-1">Utilisateurs & rôles</span>
                                </div>
                            </a>
                            <a class="nav-link" href="annee-scolaire" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-calendar-alt"></span></span><span
                                        class="nav-link-text ps-1">Année scolaire</span>
                                </div>
                            </a>
                            <a class="nav-link" href="configuration.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class=" fas fa-cogs"></span></span><span
                                        class="nav-link-text ps-1">Configuration générale</span>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">
                                    Documentation
                                </div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages--><a class="nav-link" href="documentation/getting-started.php"
                                role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-rocket"></span></span><span
                                        class="nav-link-text ps-1">Getting started</span>
                                </div>
                            </a>
                           
                            <!-- parent pages-->
                            <a class="nav-link" href="faq.php" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span
                                            class="fas fa-question-circle"></span></span><span
                                        class="nav-link-text ps-1">Faq</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <div class="settings my-3">
                        <div class="card shadow-none">
                            <div class="card-body alert mb-0" role="alert">
                                <div class="btn-close-falcon-container">
                                    <button class="btn btn-link btn-close-falcon p-0" aria-label="Close"
                                        data-bs-dismiss="alert"></button>
                                </div>
                                <div class="text-center">
                                    <img src="assets/img/icons/spot-illustrations/navbar-vertical.png" alt=""
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
        <nav class="navbar navbar-light navbar-glass navbar-top navbar-expand-lg" style="display: none">
        </nav>

        <div class="content">
            <nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">
                <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse"
                    aria-controls="navbarVerticalCollapse" aria-expanded="false"
                    aria-label="Toggle Navigation"><span class="navbar-toggle-icon"><span
                            class="toggle-line"></span></span></button>
                <a class="navbar-brand me-1 me-sm-3" href="index.php">
                    <div class="d-flex align-items-center">
                        <img class="me-2" src="assets/img/icons/spot-illustrations/falcon.png" alt=""
                            width="40"><span class="font-sans-serif text-primary">falcon</span>
                    </div>
                </a>
                <ul class="navbar-nav align-items-center d-none d-lg-block">
                    <li class="nav-item">
                        <div class="search-box" data-list="{&quot;valueNames&quot;:[&quot;title&quot;]}">
                            <form class="position-relative" data-bs-toggle="search" data-bs-display="static"
                                aria-expanded="false">
                                <input class="form-control search-input fuzzy-search" type="search"
                                    placeholder="Search..." aria-label="Search">
                               <span class="fas fa-search search-box-icon"></span> 
                            </form>
                            <div class="btn-close-falcon-container position-absolute end-0 top-50 translate-middle shadow-none"
                                data-bs-dismiss="search"><button class="btn btn-link btn-close-falcon p-0"
                                    aria-label="Close"></button></div>
                            <div class="dropdown-menu border font-base start-0 mt-2 py-0 overflow-hidden w-100">
                                <div class="scrollbar list py-3" style="max-height: 24rem;">
                                    <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">
                                        Recently Browsed</h6><a class="dropdown-item fs-10 px-x1 py-1 hover-primary"
                                        href="events/event-detail.php">
                                        <div class="d-flex align-items-center">
                                            <svg class="svg-inline--fa fa-circle fa-w-16 me-2 text-300 fs-11"
                                                aria-hidden="true" focusable="false" data-prefix="fas"
                                                data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 512 512" data-fa-i2svg="">
                                                <path fill="currentColor"
                                                    d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                </path>
                                            </svg><!-- <span class="fas fa-circle me-2 text-300 fs-11"></span>  -->
                                            <div class="fw-normal title">Pages <svg
                                                    class="svg-inline--fa fa-chevron-right fa-w-10 mx-1 text-500 fs-11"
                                                    data-fa-transform="shrink-2" aria-hidden="true"
                                                    focusable="false" data-prefix="fas" data-icon="chevron-right"
                                                    role="img" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 320 512" data-fa-i2svg=""
                                                    style="transform-origin: 0.3125em 0.5em;">
                                                    <g transform="translate(160 256)">
                                                        <g
                                                            transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)">
                                                            <path fill="currentColor"
                                                                d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"
                                                                transform="translate(-160 -256)"></path>
                                                        </g>
                                                    </g>
                                                </svg><!-- <span class="fas fa-chevron-right mx-1 text-500 fs-11" data-fa-transform="shrink-2"></span>  -->
                                                Events</div>
                                        </div>
                                    </a><a class="dropdown-item fs-10 px-x1 py-1 hover-primary"
                                        href="e-commerce/customers.php">
                                        <div class="d-flex align-items-center">
                                            <svg class="svg-inline--fa fa-circle fa-w-16 me-2 text-300 fs-11"
                                                aria-hidden="true" focusable="false" data-prefix="fas"
                                                data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 512 512" data-fa-i2svg="">
                                                <path fill="currentColor"
                                                    d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z">
                                                </path>
                                            </svg><!-- <span class="fas fa-circle me-2 text-300 fs-11"></span>  -->
                                            <div class="fw-normal title">E-commerce <svg
                                                    class="svg-inline--fa fa-chevron-right fa-w-10 mx-1 text-500 fs-11"
                                                    data-fa-transform="shrink-2" aria-hidden="true"
                                                    focusable="false" data-prefix="fas" data-icon="chevron-right"
                                                    role="img" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 320 512" data-fa-i2svg=""
                                                    style="transform-origin: 0.3125em 0.5em;">
                                                    <g transform="translate(160 256)">
                                                        <g
                                                            transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)">
                                                            <path fill="currentColor"
                                                                d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"
                                                                transform="translate(-160 -256)"></path>
                                                        </g>
                                                    </g>
                                                </svg><!-- <span class="fas fa-chevron-right mx-1 text-500 fs-11" data-fa-transform="shrink-2"></span>  -->
                                                Customers</div>
                                        </div>
                                    </a>
                                    <hr class="text-200 dark__text-900">
                                    <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">
                                        Suggested Filter</h6><a class="dropdown-item px-x1 py-1 fs-9"
                                        href="e-commerce/customers.php">
                                        <div class="d-flex align-items-center"><span
                                                class="badge fw-medium text-decoration-none me-2 badge-subtle-warning">customers:</span>
                                            <div class="flex-1 fs-10 title">All customers list</div>
                                        </div>
                                    </a><a class="dropdown-item px-x1 py-1 fs-9" href="events/event-detail.php">
                                        <div class="d-flex align-items-center"><span
                                                class="badge fw-medium text-decoration-none me-2 badge-subtle-success">events:</span>
                                            <div class="flex-1 fs-10 title">Latest events in current month</div>
                                        </div>
                                    </a><a class="dropdown-item px-x1 py-1 fs-9"
                                        href="e-commerce/product/product-grid.php">
                                        <div class="d-flex align-items-center"><span
                                                class="badge fw-medium text-decoration-none me-2 badge-subtle-info">products:</span>
                                            <div class="flex-1 fs-10 title">Most popular products</div>
                                        </div>
                                    </a>
                                    <hr class="text-200 dark__text-900">
                                    <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">Files
                                    </h6><a class="dropdown-item px-x1 py-2" href="#!">
                                        <div class="d-flex align-items-center">
                                            <div class="file-thumbnail me-2"><img
                                                    class="border h-100 w-100 object-fit-cover rounded-3"
                                                    src="assets/img/products/3-thumb.png" alt=""></div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 title">iPhone</h6>
                                                <p class="fs-11 mb-0 d-flex"><span
                                                        class="fw-semi-bold">Antony</span><span
                                                        class="fw-medium text-600 ms-2">27 Sep at 10:30 AM</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a><a class="dropdown-item px-x1 py-2" href="#!">
                                        <div class="d-flex align-items-center">
                                            <div class="file-thumbnail me-2"><img class="img-fluid"
                                                    src="assets/img/icons/zip.png" alt="">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 title">Falcon v1.8.2</h6>
                                                <p class="fs-11 mb-0 d-flex"><span
                                                        class="fw-semi-bold">John</span><span
                                                        class="fw-medium text-600 ms-2">30 Sep at 12:30 PM</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                    <hr class="text-200 dark__text-900">
                                    <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">
                                        Members</h6><a class="dropdown-item px-x1 py-2" href="user/profile.php">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-l status-online me-2">
                                                <img class="rounded-circle" src="assets/img/team/1.jpg" alt="">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 title">Anna Karinina</h6>
                                                <p class="fs-11 mb-0 d-flex">Technext Limited</p>
                                            </div>
                                        </div>
                                    </a><a class="dropdown-item px-x1 py-2" href="user/profile.php">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-l me-2">
                                                <img class="rounded-circle" src="assets/img/team/2.jpg" alt="">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 title">Antony Hopkins</h6>
                                                <p class="fs-11 mb-0 d-flex">Brain Trust</p>
                                            </div>
                                        </div>
                                    </a><a class="dropdown-item px-x1 py-2" href="user/profile.php">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-l me-2">
                                                <img class="rounded-circle" src="assets/img/team/3.jpg" alt="">
                                            </div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 title">Emma Watson</h6>
                                                <p class="fs-11 mb-0 d-flex">Google</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="text-center mt-n3">
                                    <p class="fallback fw-bold fs-8 d-none">No Result Found.</p>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">
                    <li class="nav-item ps-2 pe-0">
                        <div class="dropdown theme-control-dropdown"><a
                                class="nav-link d-flex align-items-center dropdown-toggle fa-icon-wait fs-9 pe-1 py-0"
                                href="#" role="button" id="themeSwitchDropdown" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false"><svg
                                    class="svg-inline--fa fa-sun fa-w-16 fs-7 d-none" data-fa-transform="shrink-2"
                                    data-theme-dropdown-toggle-icon="light" aria-hidden="true" focusable="false"
                                    data-prefix="fas" data-icon="sun" role="img" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512" data-fa-i2svg="" style="transform-origin: 0.5em 0.5em;">
                                    <g transform="translate(256 256)">
                                        <g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)">
                                            <path fill="currentColor"
                                                d="M256 160c-52.9 0-96 43.1-96 96s43.1 96 96 96 96-43.1 96-96-43.1-96-96-96zm246.4 80.5l-94.7-47.3 33.5-100.4c4.5-13.6-8.4-26.5-21.9-21.9l-100.4 33.5-47.4-94.8c-6.4-12.8-24.6-12.8-31 0l-47.3 94.7L92.7 70.8c-13.6-4.5-26.5 8.4-21.9 21.9l33.5 100.4-94.7 47.4c-12.8 6.4-12.8 24.6 0 31l94.7 47.3-33.5 100.5c-4.5 13.6 8.4 26.5 21.9 21.9l100.4-33.5 47.3 94.7c6.4 12.8 24.6 12.8 31 0l47.3-94.7 100.4 33.5c13.6 4.5 26.5-8.4 21.9-21.9l-33.5-100.4 94.7-47.3c13-6.5 13-24.7.2-31.1zm-155.9 106c-49.9 49.9-131.1 49.9-181 0-49.9-49.9-49.9-131.1 0-181 49.9-49.9 131.1-49.9 181 0 49.9 49.9 49.9 131.1 0 181z"
                                                transform="translate(-256 -256)"></path>
                                        </g>
                                    </g>
                                </svg><!-- <span class="fas fa-sun fs-7 d-none" data-fa-transform="shrink-2" data-theme-dropdown-toggle-icon="light"></span>  --><svg
                                    class="svg-inline--fa fa-moon fa-w-16 fs-7 d-none" data-fa-transform="shrink-3"
                                    data-theme-dropdown-toggle-icon="dark" aria-hidden="true" focusable="false"
                                    data-prefix="fas" data-icon="moon" role="img" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512" data-fa-i2svg="" style="transform-origin: 0.5em 0.5em;">
                                    <g transform="translate(256 256)">
                                        <g transform="translate(0, 0)  scale(0.8125, 0.8125)  rotate(0 0 0)">
                                            <path fill="currentColor"
                                                d="M283.211 512c78.962 0 151.079-35.925 198.857-94.792 7.068-8.708-.639-21.43-11.562-19.35-124.203 23.654-238.262-71.576-238.262-196.954 0-72.222 38.662-138.635 101.498-174.394 9.686-5.512 7.25-20.197-3.756-22.23A258.156 258.156 0 0 0 283.211 0c-141.309 0-256 114.511-256 256 0 141.309 114.511 256 256 256z"
                                                transform="translate(-256 -256)"></path>
                                        </g>
                                    </g>
                                </svg><!-- <span class="fas fa-moon fs-7 d-none" data-fa-transform="shrink-3" data-theme-dropdown-toggle-icon="dark"></span>  --><svg
                                    class="svg-inline--fa fa-adjust fa-w-16 fs-7 d-none"
                                    data-fa-transform="shrink-2" data-theme-dropdown-toggle-icon="auto"
                                    aria-hidden="true" focusable="false" data-prefix="fas" data-icon="adjust"
                                    role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                    data-fa-i2svg="" style="transform-origin: 0.5em 0.5em;">
                                    <g transform="translate(256 256)">
                                        <g transform="translate(0, 0)  scale(0.875, 0.875)  rotate(0 0 0)">
                                            <path fill="currentColor"
                                                d="M8 256c0 136.966 111.033 248 248 248s248-111.034 248-248S392.966 8 256 8 8 119.033 8 256zm248 184V72c101.705 0 184 82.311 184 184 0 101.705-82.311 184-184 184z"
                                                transform="translate(-256 -256)"></path>
                                        </g>
                                    </g>
                                </svg><!-- <span class="fas fa-adjust fs-7 d-none" data-fa-transform="shrink-2" data-theme-dropdown-toggle-icon="auto"></span>  --></a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-caret border py-0 mt-3"
                                aria-labelledby="themeSwitchDropdown">
                                <div class="bg-white dark__bg-1000 rounded-2 py-2"><button
                                        class="dropdown-item d-flex align-items-center gap-2" type="button"
                                        value="light" data-theme-control="theme"><svg
                                            class="svg-inline--fa fa-sun fa-w-16" aria-hidden="true"
                                            focusable="false" data-prefix="fas" data-icon="sun" role="img"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                            data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M256 160c-52.9 0-96 43.1-96 96s43.1 96 96 96 96-43.1 96-96-43.1-96-96-96zm246.4 80.5l-94.7-47.3 33.5-100.4c4.5-13.6-8.4-26.5-21.9-21.9l-100.4 33.5-47.4-94.8c-6.4-12.8-24.6-12.8-31 0l-47.3 94.7L92.7 70.8c-13.6-4.5-26.5 8.4-21.9 21.9l33.5 100.4-94.7 47.4c-12.8 6.4-12.8 24.6 0 31l94.7 47.3-33.5 100.5c-4.5 13.6 8.4 26.5 21.9 21.9l100.4-33.5 47.3 94.7c6.4 12.8 24.6 12.8 31 0l47.3-94.7 100.4 33.5c13.6 4.5 26.5-8.4 21.9-21.9l-33.5-100.4 94.7-47.3c13-6.5 13-24.7.2-31.1zm-155.9 106c-49.9 49.9-131.1 49.9-181 0-49.9-49.9-49.9-131.1 0-181 49.9-49.9 131.1-49.9 181 0 49.9 49.9 49.9 131.1 0 181z">
                                            </path>
                                        </svg><!-- <span class="fas fa-sun"></span>  -->Light<svg
                                            class="svg-inline--fa fa-check fa-w-16 dropdown-check-icon ms-auto text-600"
                                            aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check"
                                            role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                            data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z">
                                            </path>
                                        </svg><!-- <span class="fas fa-check dropdown-check-icon ms-auto text-600"></span>  --></button><button
                                        class="dropdown-item d-flex align-items-center gap-2" type="button"
                                        value="dark" data-theme-control="theme"><svg
                                            class="svg-inline--fa fa-moon fa-w-16" data-fa-transform=""
                                            aria-hidden="true" focusable="false" data-prefix="fas" data-icon="moon"
                                            role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                            data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M283.211 512c78.962 0 151.079-35.925 198.857-94.792 7.068-8.708-.639-21.43-11.562-19.35-124.203 23.654-238.262-71.576-238.262-196.954 0-72.222 38.662-138.635 101.498-174.394 9.686-5.512 7.25-20.197-3.756-22.23A258.156 258.156 0 0 0 283.211 0c-141.309 0-256 114.511-256 256 0 141.309 114.511 256 256 256z">
                                            </path>
                                        </svg><!-- <span class="fas fa-moon" data-fa-transform=""></span>  -->Dark<svg
                                            class="svg-inline--fa fa-check fa-w-16 dropdown-check-icon ms-auto text-600"
                                            aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check"
                                            role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                            data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z">
                                            </path>
                                        </svg><!-- <span class="fas fa-check dropdown-check-icon ms-auto text-600"></span>  --></button><button
                                        class="dropdown-item d-flex align-items-center gap-2" type="button"
                                        value="auto" data-theme-control="theme"><svg
                                            class="svg-inline--fa fa-adjust fa-w-16" data-fa-transform=""
                                            aria-hidden="true" focusable="false" data-prefix="fas"
                                            data-icon="adjust" role="img" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 512 512" data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M8 256c0 136.966 111.033 248 248 248s248-111.034 248-248S392.966 8 256 8 8 119.033 8 256zm248 184V72c101.705 0 184 82.311 184 184 0 101.705-82.311 184-184 184z">
                                            </path>
                                        </svg><!-- <span class="fas fa-adjust" data-fa-transform=""></span>  -->Auto<svg
                                            class="svg-inline--fa fa-check fa-w-16 dropdown-check-icon ms-auto text-600"
                                            aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check"
                                            role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                            data-fa-i2svg="">
                                            <path fill="currentColor"
                                                d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z">
                                            </path>
                                        </svg><!-- <span class="fas fa-check dropdown-check-icon ms-auto text-600"></span>  --></button>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!-- notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link notification-indicator notification-indicator-primary px-0 fa-icon-wait"
                            id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false"
                            data-hide-on-body-scroll="data-hide-on-body-scroll"><svg
                                class="svg-inline--fa fa-bell fa-w-14" data-fa-transform="shrink-6"
                                style="font-size: 33px;transform-origin: 0.4375em 0.5em;" aria-hidden="true"
                                focusable="false" data-prefix="fas" data-icon="bell" role="img"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="">
                                <g transform="translate(224 256)">
                                    <g transform="translate(0, 0)  scale(0.625, 0.625)  rotate(0 0 0)">
                                        <path fill="currentColor"
                                            d="M224 512c35.32 0 63.97-28.65 63.97-64H160.03c0 35.35 28.65 64 63.97 64zm215.39-149.71c-19.32-20.76-55.47-51.99-55.47-154.29 0-77.7-54.48-139.9-127.94-155.16V32c0-17.67-14.32-32-31.98-32s-31.98 14.33-31.98 32v20.84C118.56 68.1 64.08 130.3 64.08 208c0 102.3-36.15 133.53-55.47 154.29-6 6.45-8.66 14.16-8.61 21.71.11 16.4 12.98 32 32.1 32h383.8c19.12 0 32-15.6 32.1-32 .05-7.55-2.61-15.27-8.61-21.71z"
                                            transform="translate(-224 -256)"></path>
                                    </g>
                                </g>
                            </svg><!-- <span class="fas fa-bell" data-fa-transform="shrink-6" style="font-size: 33px;"></span>  --></a>
                        <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end dropdown-menu-card dropdown-menu-notification dropdown-caret-bg"
                            aria-labelledby="navbarDropdownNotification">
                            <div class="card card-notification shadow-none">
                                <div class="card-header">
                                    <div class="row justify-content-between align-items-center">
                                        <div class="col-auto">
                                            <h6 class="card-header-title mb-0">Notifications</h6>
                                        </div>
                                        <div class="col-auto ps-0 ps-sm-3"><a class="card-link fw-normal"
                                                href="#">Mark all as read</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="scrollbar-overlay" style="max-height:19rem" data-simplebar="init">
                                    <div class="simplebar-wrapper" style="margin: 0px;">
                                        <div class="simplebar-height-auto-observer-wrapper">
                                            <div class="simplebar-height-auto-observer"></div>
                                        </div>
                                        <div class="simplebar-mask">
                                            <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                                                <div class="simplebar-content-wrapper" tabindex="0" role="region"
                                                    aria-label="scrollable content"
                                                    style="height: auto; overflow: hidden;">
                                                    <div class="simplebar-content" style="padding: 0px;">
                                                        <div class="list-group list-group-flush fw-normal fs-10">
                                                            <div class="list-group-title border-bottom">NEW</div>
                                                            <div class="list-group-item">
                                                                <a class="notification notification-flush notification-unread"
                                                                    href="#!">
                                                                    <div class="notification-avatar">
                                                                        <div class="avatar avatar-2xl me-3">
                                                                            <img class="rounded-circle"
                                                                                src="assets/img/team/1-thumb.png"
                                                                                alt="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="notification-body">
                                                                        <p class="mb-1"><strong>Emma Watson</strong>
                                                                            replied to your comment : "Hello
                                                                            world 😍"</p>
                                                                        <span class="notification-time"><span
                                                                                class="me-2" role="img"
                                                                                aria-label="Emoji">💬</span>Just
                                                                            now</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="list-group-item">
                                                                <a class="notification notification-flush notification-unread"
                                                                    href="#!">
                                                                    <div class="notification-avatar">
                                                                        <div class="avatar avatar-2xl me-3">
                                                                            <div class="avatar-name rounded-circle">
                                                                                <span>AB</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="notification-body">
                                                                        <p class="mb-1"><strong>Albert
                                                                                Brooks</strong> reacted to
                                                                            <strong>Mia
                                                                                Khalifa's</strong> status
                                                                        </p>
                                                                        <span class="notification-time"><svg
                                                                                class="svg-inline--fa fa-gratipay fa-w-16 me-2 text-danger"
                                                                                aria-hidden="true" focusable="false"
                                                                                data-prefix="fab"
                                                                                data-icon="gratipay" role="img"
                                                                                xmlns="http://www.w3.org/2000/svg"
                                                                                viewBox="0 0 496 512"
                                                                                data-fa-i2svg="">
                                                                                <path fill="currentColor"
                                                                                    d="M248 8C111.1 8 0 119.1 0 256s111.1 248 248 248 248-111.1 248-248S384.9 8 248 8zm114.6 226.4l-113 152.7-112.7-152.7c-8.7-11.9-19.1-50.4 13.6-72 28.1-18.1 54.6-4.2 68.5 11.9 15.9 17.9 46.6 16.9 61.7 0 13.9-16.1 40.4-30 68.1-11.9 32.9 21.6 22.6 60 13.8 72z">
                                                                                </path>
                                                                            </svg><!-- <span class="me-2 fab fa-gratipay text-danger"></span>  -->9hr</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="list-group-title border-bottom">EARLIER
                                                            </div>
                                                            <div class="list-group-item">
                                                                <a class="notification notification-flush"
                                                                    href="#!">
                                                                    <div class="notification-avatar">
                                                                        <div class="avatar avatar-2xl me-3">
                                                                            <img class="rounded-circle"
                                                                                src="assets/img/icons/weather-sm.jpg"
                                                                                alt="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="notification-body">
                                                                        <p class="mb-1">The forecast today shows a
                                                                            low of 20℃ in California. See today's
                                                                            weather.</p>
                                                                        <span class="notification-time"><span
                                                                                class="me-2" role="img"
                                                                                aria-label="Emoji">🌤️</span>1d</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="list-group-item">
                                                                <a class="border-bottom-0 notification-unread  notification notification-flush"
                                                                    href="#!">
                                                                    <div class="notification-avatar">
                                                                        <div class="avatar avatar-xl me-3">
                                                                            <img class="rounded-circle"
                                                                                src="assets/img/logos/oxford.png"
                                                                                alt="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="notification-body">
                                                                        <p class="mb-1"><strong>University of
                                                                                Oxford</strong> created an event :
                                                                            "Causal
                                                                            Inference Hilary 2019"</p>
                                                                        <span class="notification-time"><span
                                                                                class="me-2" role="img"
                                                                                aria-label="Emoji">✌️</span>1w</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="list-group-item">
                                                                <a class="border-bottom-0 notification notification-flush"
                                                                    href="#!">
                                                                    <div class="notification-avatar">
                                                                        <div class="avatar avatar-xl me-3">
                                                                            <img class="rounded-circle"
                                                                                src="assets/img/team/10.jpg" alt="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="notification-body">
                                                                        <p class="mb-1"><strong>James
                                                                                Cameron</strong> invited to join the
                                                                            group: United
                                                                            Nations International Children's Fund
                                                                        </p>
                                                                        <span class="notification-time"><span
                                                                                class="me-2" role="img"
                                                                                aria-label="Emoji">🙋‍</span>2d</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="simplebar-placeholder" style="width: 0px; height: 0px;"></div>
                                    </div>
                                    <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
                                        <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
                                    </div>
                                    <div class="simplebar-track simplebar-vertical" style="visibility: hidden;">
                                        <div class="simplebar-scrollbar" style="height: 0px; display: none;"></div>
                                    </div>
                                </div>
                                <div class="card-footer text-center border-top"><a class="card-link d-block"
                                        href="social/notifications.php">View all</a></div>
                            </div>
                        </div>
                    </li>
                    <!-- profile -->
                    <li class="nav-item dropdown"><a class="nav-link pe-0 ps-2" id="navbarDropdownUser"
                            role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar avatar-xl">
                                <img class="rounded-circle" src="assets/img/team/3-thumb.png" alt="">
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end py-0"
                            aria-labelledby="navbarDropdownUser">
                            <div class="bg-white dark__bg-1000 rounded-2 py-2">


                                <a class="dropdown-item" href="user/profile.php">Profile &amp;
                                    account</a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="user/settings.php">Settings</a>
                                <a class="dropdown-item" href="login.php">

                                    Logout</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>

            <script>
                var navbarPosition = localStorage.getItem('navbarPosition');
                var navbarVertical = document.querySelector('.navbar-vertical');
                var navbarTopVertical = document.querySelector('.content .navbar-top');
                var navbarTop = document.querySelector('[data-layout] .navbar-top:not([data-double-top-nav');
                var navbarDoubleTop = document.querySelector('[data-double-top-nav]');
                var navbarTopCombo = document.querySelector('.content [data-navbar-top="combo"]');

                if (localStorage.getItem('navbarPosition') === 'double-top') {
                    document.documentElement.classList.toggle('double-top-nav-layout');
                }

                if (navbarPosition === 'top') {
                    navbarTop.removeAttribute('style');
                    navbarTopVertical.remove(navbarTopVertical);
                    navbarVertical.remove(navbarVertical);
                    navbarTopCombo.remove(navbarTopCombo);
                    navbarDoubleTop.remove(navbarDoubleTop);
                } else if (navbarPosition === 'combo') {
                    navbarVertical.removeAttribute('style');
                    navbarTopCombo.removeAttribute('style');
                    navbarTop.remove(navbarTop);
                    navbarTopVertical.remove(navbarTopVertical);
                    navbarDoubleTop.remove(navbarDoubleTop);
                } else if (navbarPosition === 'double-top') {
                    navbarDoubleTop.removeAttribute('style');
                    navbarTopVertical.remove(navbarTopVertical);
                    navbarVertical.remove(navbarVertical);
                    navbarTop.remove(navbarTop);
                    navbarTopCombo.remove(navbarTopCombo);
                } else {
                    navbarVertical.removeAttribute('style');
                    navbarTopVertical.removeAttribute('style');
                    navbarTop.remove(navbarTop);
                    navbarDoubleTop.remove(navbarDoubleTop);
                    navbarTopCombo.remove(navbarTopCombo);
                }
            </script>