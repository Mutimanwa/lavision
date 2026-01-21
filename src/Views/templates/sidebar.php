<?php
/**
 * Template de la barre latérale (sidebar)
 * LaVision - Système de gestion scolaire
 *
 * Ce template affiche la navigation principale avec les modules disponibles
 * selon les permissions de l'utilisateur connecté.
 */

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    return;
}

// Récupérer les informations de l'utilisateur
$user_role = $_SESSION['user_role'] ?? 'eleve';
$user_permissions = $_SESSION['user_permissions'] ?? [];

// Définir les modules disponibles selon le rôle
$modules = [
    'dashboard' => [
        'icon' => 'fas fa-tachometer-alt',
        'title' => 'Tableau de bord',
        'url' => BASE_URL . '?page=dashboard',
        'roles' => ['admin', 'professeur', 'eleve', 'parent'],
        'permissions' => []
    ],
    'eleves' => [
        'icon' => 'fas fa-user-graduate',
        'title' => 'Élèves',
        'url' => BASE_URL . '?page=eleves',
        'roles' => ['admin', 'professeur'],
        'permissions' => ['eleves.view'],
        'submenu' => [
            'liste' => ['title' => 'Liste des élèves', 'url' => BASE_URL . '?page=eleves', 'permissions' => ['eleves.view']],
            'admission' => ['title' => 'Admission', 'url' => BASE_URL . '?page=eleves/admission', 'permissions' => ['eleves.create']],
            'parents' => ['title' => 'Parents', 'url' => BASE_URL . '?page=eleves/parents', 'permissions' => ['eleves.view']]
        ]
    ],
    'academique' => [
        'icon' => 'fas fa-graduation-cap',
        'title' => 'Académique',
        'url' => BASE_URL . '?page=academique/options',
        'roles' => ['admin', 'professeur'],
        'permissions' => ['academique.view'],
        'submenu' => [
            'classes' => ['title' => 'Classes', 'url' => BASE_URL . '?page=academique/classes', 'permissions' => ['academique.view']],
            'matieres' => ['title' => 'Matières', 'url' => BASE_URL . '?page=academique/matieres', 'permissions' => ['academique.view']],
            'horaires' => ['title' => 'Emploi du temps', 'url' => BASE_URL . '?page=academique/horaires', 'permissions' => ['academique.view']],
            'options' => ['title' => 'Options', 'url' => BASE_URL . '?page=academique/options', 'permissions' => ['academique.manage']]
        ]
    ],
    'personnel' => [
        'icon' => 'fas fa-users',
        'title' => 'Personnel',
        'url' => BASE_URL . '?page=personnel/professeurs',
        'roles' => ['admin'],
        'permissions' => ['personnel.view'],
        'submenu' => [
            'professeurs' => ['title' => 'Professeurs', 'url' => BASE_URL . '?page=personnel/professeurs', 'permissions' => ['personnel.view']],
            'utilisateurs' => ['title' => 'Utilisateurs', 'url' => BASE_URL . '?page=administration/utilisateurs', 'permissions' => ['admin.users']]
        ]
    ],
    'finance' => [
        'icon' => 'fas fa-money-bill-wave',
        'title' => 'Finance',
        'url' => BASE_URL . '?page=finance/paiements',
        'roles' => ['admin'],
        'permissions' => ['finance.view'],
        'submenu' => [
            'paiements' => ['title' => 'Paiements', 'url' => BASE_URL . '?page=finance/paiements', 'permissions' => ['finance.view']]
        ]
    ],
    'rapports' => [
        'icon' => 'fas fa-chart-bar',
        'title' => 'Rapports',
        'url' => BASE_URL . '?page=rapports/academique',
        'roles' => ['admin', 'professeur'],
        'permissions' => ['rapports.view'],
        'submenu' => [
            'academique' => ['title' => 'Rapport académique', 'url' => BASE_URL . '?page=rapports/academique', 'permissions' => ['rapports.view']],
            'financier' => ['title' => 'Rapport financier', 'url' => BASE_URL . '?page=rapports/financier', 'permissions' => ['rapports.view']],
            'personnel' => ['title' => 'Rapport personnel', 'url' => BASE_URL . '?page=rapports/personnel', 'permissions' => ['rapports.view']]
        ]
    ],
    'administration' => [
        'icon' => 'fas fa-cogs',
        'title' => 'Administration',
        'url' => BASE_URL . '?page=administration/utilisateurs',
        'roles' => ['admin'],
        'permissions' => ['admin.access'],
        'submenu' => [
            'utilisateurs' => ['title' => 'Gestion utilisateurs', 'url' => BASE_URL . '?page=administration/utilisateurs', 'permissions' => ['admin.users']],
            'parametres' => ['title' => 'Paramètres système', 'url' => BASE_URL . '?page=administration/parametres', 'permissions' => ['admin.settings']],
            'annee_scolaire' => ['title' => 'Année scolaire', 'url' => BASE_URL . '?page=administration/annee-scolaire', 'permissions' => ['admin.settings']],
            'logs' => ['title' => 'Journaux système', 'url' => BASE_URL . '?page=administration/logs', 'permissions' => ['admin.logs']],
            'backup' => ['title' => 'Sauvegarde', 'url' => BASE_URL . '?page=administration/backup', 'permissions' => ['admin.backup']]
        ]
    ]
];

// Fonction pour vérifier les permissions
function hasPermission($required_permissions, $user_permissions) {
    if (empty($required_permissions)) {
        return true;
    }

    foreach ($required_permissions as $permission) {
        if (in_array($permission, $user_permissions)) {
            return true;
        }
    }

    return false;
}

// Fonction pour vérifier le rôle
function hasRole($required_roles, $user_role) {
    return in_array($user_role, $required_roles);
}

// Fonction pour déterminer si un élément de menu est actif
function isMenuActive($url) {
    $current_url = $_SERVER['REQUEST_URI'] ?? '';
    $base_url = str_replace(BASE_URL, '', $url);

    // Vérifier si l'URL actuelle commence par l'URL du menu
    return strpos($current_url, $base_url) === 0;
}
?>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>/dashboard" class="sidebar-brand">
            <img src="<?php echo ASSETS_PATH; ?>/img/logos/logo.png" alt="LaVision" class="sidebar-logo">
            <span class="sidebar-title">LaVision</span>
        </a>
        <button class="sidebar-toggle" id="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <?php foreach ($modules as $module_key => $module): ?>
                <?php
                // Vérifier si l'utilisateur a accès à ce module
                $has_role = hasRole($module['roles'], $user_role);
                $has_perm = hasPermission($module['permissions'], $user_permissions);

                if (!$has_role && !$has_perm) {
                    continue;
                }

                $is_active = isMenuActive($module['url']);
                $has_submenu = isset($module['submenu']) && !empty($module['submenu']);
                ?>

                <li class="nav-item <?php echo $is_active ? 'active' : ''; ?>">
                    <a href="<?php echo $has_submenu ? '#' : $module['url']; ?>"
                       class="nav-link <?php echo $has_submenu ? 'dropdown-toggle' : ''; ?>"
                       <?php echo $has_submenu ? 'data-bs-toggle="collapse" data-bs-target="#submenu-' . $module_key . '" aria-expanded="' . ($is_active ? 'true' : 'false') . '"' : ''; ?>>
                        <i class="<?php echo $module['icon']; ?> nav-icon"></i>
                        <span class="nav-text"><?php echo $module['title']; ?></span>
                        <?php if ($has_submenu): ?>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        <?php endif; ?>
                    </a>

                    <?php if ($has_submenu): ?>
                        <div class="collapse <?php echo $is_active ? 'show' : ''; ?>" id="submenu-<?php echo $module_key; ?>">
                            <ul class="nav flex-column submenu">
                                <?php foreach ($module['submenu'] as $submenu_key => $submenu_item): ?>
                                    <?php
                                    // Vérifier les permissions du sous-menu
                                    $submenu_has_perm = hasPermission($submenu_item['permissions'], $user_permissions);
                                    if (!$submenu_has_perm) {
                                        continue;
                                    }

                                    $submenu_active = isMenuActive($submenu_item['url']);
                                    ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $submenu_item['url']; ?>"
                                           class="nav-link <?php echo $submenu_active ? 'active' : ''; ?>">
                                            <i class="fas fa-circle nav-icon"></i>
                                            <span class="nav-text"><?php echo $submenu_item['title']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Pied de sidebar -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php
                $user_initials = strtoupper(substr($_SESSION['user_nom'] ?? 'U', 0, 1) . substr($_SESSION['user_prenom'] ?? '', 0, 1));
                ?>
                <span class="avatar-text"><?php echo $user_initials; ?></span>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom']); ?></div>
                <div class="user-role"><?php echo ucfirst($user_role); ?></div>
            </div>
        </div>

        <div class="sidebar-actions">
            <a href="<?php echo BASE_URL; ?>/utilisateur/profile" class="action-link" title="Profil">
                <i class="fas fa-user"></i>
            </a>
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="action-link text-danger" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>

<!-- Overlay pour mobile -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
// Gestion de la sidebar responsive
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    // Toggle sidebar
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
    }

    // Fermer sidebar en cliquant sur l'overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // Fermer sidebar sur redimensionnement
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    });

    // Gestion des menus déroulants
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();

            const target = document.querySelector(this.getAttribute('data-bs-target'));
            if (target) {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Fermer tous les autres sous-menus
                document.querySelectorAll('.collapse.show').forEach(collapse => {
                    if (collapse !== target) {
                        collapse.classList.remove('show');
                        const relatedToggle = document.querySelector(`[data-bs-target="#${collapse.id}"]`);
                        if (relatedToggle) {
                            relatedToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                });

                // Toggle le sous-menu actuel
                target.classList.toggle('show');
                this.setAttribute('aria-expanded', !isExpanded);
            }
        });
    });
});
</script>