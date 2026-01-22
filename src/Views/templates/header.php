<?php
/**
 * Template d'en-tête principal de l'application LaVision
 * Inclut les balises HTML de base, CSS et JavaScript communs
 * Design responsive avec Bootstrap 5 et thème personnalisé
 * Version: 1.0.0
 * 
 */
require_once __DIR__  ."/../../Config/Config.php";
require_once __DIR__ . "/../../Services/functions.php";

// Définition des constantes de chemin si non définies
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', ASSETS_URL);
}

if (!defined('CSS_PATH')) {
    define('CSS_PATH', ASSETS_PATH . 'css/');
}

if (!defined('JS_PATH')) {
    define('JS_PATH', ASSETS_PATH . 'js/');
}

if (!defined('LIBS_PATH')) {
    define('LIBS_PATH', ASSETS_PATH . 'libs/');
}

// Titre de la page (défini par le contrôleur)
$titre_page = $titre_page ?? 'LaVision - Système de Gestion Scolaire';

// Utilisateur connecté
$utilisateur = $_SESSION['utilisateur_nom'] ?? 'Utilisateur';
$role_utilisateur = $_SESSION['utilisateur_role'] ?? '';

// Token CSRF
$csrf_token = generer_token_csrf();
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Système de gestion scolaire lavision - Gestion complète des établissements éducatifs">
    <meta name="author" content="calvindev">
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Falcon</title>

    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= IMAGES_URL ?>favicons/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?= IMAGES_URL ?>favicons/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?= IMAGES_URL ?>favicons/favicon-16x16.png" />
    <link rel="shortcut icon" type="image/x-icon" href="<?= IMAGES_URL ?>favicons/favicon.ico" />
    <link rel="manifest" href="<?= IMAGES_URL ?>favicons/manifest.json" />
    <meta name="msapplication-TileImage" content="<?= IMAGES_URL ?>favicons/mstile-150x150.png" />
    <meta name="theme-color" content="#ffffff" />
    <script src="<?= JS_URL ?>config.js"></script>
    <script src="<?= LIBS_URL ?>simplebar/simplebar.min.js"></script>


    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap"
        rel="stylesheet" />
    <link href="<?= LIBS_URL ?>simplebar/simplebar.min.css" rel="stylesheet" />
    <link href="<?= CSS_URL ?>theme.min.css" rel="stylesheet" id="style-default" />
    <link href="<?= CSS_URL ?>user.min.css" rel="stylesheet" id="user-style-default" />


    <!-- Styles spécifiques à la page -->
    <?php if (isset($styles_supplementaires)): ?>
        <?php foreach ($styles_supplementaires as $style): ?>
            <link href="<?php echo $style; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>

</head>

<body>
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <div class="container">
            <!-- bare de navigation  -->

            <?php include __DIR__ . "/sidebar.php"; ?>

            <div class="content">
                <nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">
                    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button"
                        data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse"
                        aria-controls="navbarVerticalCollapse" aria-expanded="false"
                        aria-label="Toggle Navigation"><span class="navbar-toggle-icon">
                            <span class="toggle-line"></span></span></button>
                    <a class="navbar-brand me-1 me-sm-3" href="index.php">
                        <div class="d-flex align-items-center">
                            <img class="me-2" src="<?= IMAGES_URL ?>icons/spot-illustrations/falcon.png" alt=""
                                width="40"><span class="font-sans-serif text-primary">falcon</span>
                        </div>
                    </a>
                    <!-- ============================================ -->
                    <!-- recherche -->
                    <!-- ============================================ -->
                    <ul class="navbar-nav align-items-center d-none d-lg-block">
                        <li class="nav-item">
                            <div class="search-box" data-list="{&quot;valueNames&quot;:[&quot;title&quot;]}">
                                <form class="position-relative" data-bs-toggle="search" data-bs-display="static"
                                    aria-expanded="false">
                                    <input class="form-control search-input fuzzy-search" type="search"
                                        placeholder="Recherche..." aria-label="Search">
                                    <span class="fas fa-search search-box-icon"></span>
                                </form>
                                <div class="btn-close-falcon-container position-absolute end-0 top-50 translate-middle shadow-none"
                                    data-bs-dismiss="search"><button class="btn btn-link btn-close-falcon p-0"
                                        aria-label="Close"></button>

                                </div>
                                <div class="dropdown-menu border font-base start-0 mt-2 py-0 overflow-hidden w-100">
                                    <div class="scrollbar list py-3" style="max-height: 24rem;">
                                        <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">
                                            Recently Browsed</h6><a class="dropdown-item fs-10 px-x1 py-1 hover-primary"
                                            href="events/event-detail.php">
                                            <div class="d-flex align-items-center">

                                                <span class="fas fa-circle me-2 text-300 fs-11"></span>
                                                <div class="fw-normal title">pages
                                                    <span class="fas fa-chevron-right mx-1 text-500 fs-11"
                                                        data-fa-transform="shrink-2"></span>
                                                    Events
                                                </div>
                                            </div>
                                        </a><a class="dropdown-item fs-10 px-x1 py-1 hover-primary"
                                            href="e-commerce/customers.php">
                                            <div class="d-flex align-items-center">
                                                <span class="fas fa-circle me-2 text-300 fs-11"></span>
                                                <div class="fw-normal title">E-commerce
                                                    <span class="fas fa-chevron-right mx-1 text-500 fs-11"
                                                        data-fa-transform="shrink-2"></span>
                                                    Customers
                                                </div>
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
                                                        src="<?= IMAGES_URL ?>products/3-thumb.png" alt=""></div>
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
                                                        src="<?= IMAGES_URL ?>icons/zip.png" alt="">
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
                                                    <img class="rounded-circle" src="<?= IMAGES_URL ?>team/1.jpg"
                                                        alt="">
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="mb-0 title">Anna Karinina</h6>
                                                    <p class="fs-11 mb-0 d-flex">Technext Limited</p>
                                                </div>
                                            </div>
                                        </a><a class="dropdown-item px-x1 py-2" href="user/profile.php">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-l me-2">
                                                    <img class="rounded-circle" src="<?= IMAGES_URL ?>team/2.jpg"
                                                        alt="">
                                                </div>
                                                <div class="flex-1">
                                                    <h6 class="mb-0 title">Antony Hopkins</h6>
                                                    <p class="fs-11 mb-0 d-flex">Brain Trust</p>
                                                </div>
                                            </div>
                                        </a><a class="dropdown-item px-x1 py-2" href="user/profile.php">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-l me-2">
                                                    <img class="rounded-circle" src="<?= IMAGES_URL ?>team/3.jpg"
                                                        alt="">
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
                         <!-- ============================================ -->
                    <!-- Theme switcher -->
                    <!-- =========================================== -->
                        <li class="nav-item ps-2 pe-0">
                            <div class="dropdown theme-control-dropdown"><a
                                    class="nav-link d-flex align-items-center dropdown-toggle fa-icon-wait fs-9 pe-1 py-0"
                                    href="#" role="button" id="themeSwitchDropdown" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">

                                    <span class="fas fa-sun fs-7 d-none" data-fa-transform="shrink-2"
                                        data-theme-dropdown-toggle-icon="light"></span>
                                    <span class="fas fa-moon fs-7 d-none" data-fa-transform="shrink-3"
                                        data-theme-dropdown-toggle-icon="dark"></span>
                                    <span class="fas fa-adjust fs-7 d-none" data-fa-transform="shrink-2"
                                        data-theme-dropdown-toggle-icon="auto"></span> </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-caret border py-0 mt-3"
                                    aria-labelledby="themeSwitchDropdown">
                                    <div class="bg-white dark__bg-1000 rounded-2 py-2"><button
                                            class="dropdown-item d-flex align-items-center gap-2" type="button"
                                            value="light" data-theme-control="theme">
                                            <span class="fas fa-sun"></span> Lumière
                                            <span class="fas fa-check dropdown-check-icon ms-auto text-600"></span>
                                        </button>
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button"
                                            value="dark" data-theme-control="theme">
                                            <span class="fas fa-moon" data-fa-transform=""></span> Sombre
                                            <span class="fas fa-check dropdown-check-icon ms-auto text-600"></span>
                                        </button>
                                        <button class="dropdown-item d-flex align-items-center gap-2" type="button"
                                            value="auto" data-theme-control="theme">
                                            <span class="fas fa-adjust" data-fa-transform=""></span> Auto
                                            <span class="fas fa-check dropdown-check-icon ms-auto text-600"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!-- ============================================= -->
                        <!-- notifications -->
                        <!-- ============================================= -->
                        <li class="nav-item dropdown">
                            <a class="nav-link notification-indicator notification-indicator-primary px-0 fa-icon-wait"
                                id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false"
                                data-hide-on-body-scroll="data-hide-on-body-scroll">
                                <span class="fas fa-bell" data-fa-transform="shrink-6" style="font-size: 33px;"></span>
                            </a>
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
                                                                                    src="<?= IMAGES_URL ?>team/1-thumb.png"
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
                                                                                    src="<?= IMAGES_URL ?>icons/weather-sm.jpg"
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
                                                                                    src="<?= IMAGES_URL ?>logos/oxford.png"
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
                                                                                    src="<?= IMAGES_URL ?>team/10.jpg"
                                                                                    alt="">
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
                        <!-- ============================================= -->
                        <!-- profile -->
                        <!-- ============================================ -->

                        <li class="nav-item dropdown"><a class="nav-link pe-0 ps-2" id="navbarDropdownUser"
                                role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="avatar avatar-xl">
                                          <img class="rounded-circle border" 
                                            src="" 
                                            alt="<?= $utilisateur ?>" 
                                            width="120"
                                            id="profilePhoto"
                                            onerror="this.src='<?= IMAGES_URL ?>icons/user-avatar.png'">
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end py-0"
                                aria-labelledby="navbarDropdownUser">
                                <div class="bg-white dark__bg-1000 rounded-2 py-2">

                                    <a class="dropdown-item" href="<?= url("profile") ;?>">Profile &amp;
                                        Compte</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?= url("administration/parametres") ?>">Parametre</a>
                                    <a class="dropdown-item " href="<?= url("logout") ;?>">
                                        Deconnexion
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>











