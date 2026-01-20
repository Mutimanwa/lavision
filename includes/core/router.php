<?php
/**
 * Routeur principal
 */

function get_current_page() {
    $page = $_GET['page'] ?? 'dashboard';
    $page = trim($page, '/');
    $page = filter_var($page, FILTER_SANITIZE_STRING);
    return $page;
}

function load_page($page) {
    $pages = [
        // Dashboard
        'dashboard' => 'dashboard/index.php',
        
        // Auth
        'login' => 'auth/login.php',
        'logout' => 'auth/logout.php',
        'register' => 'auth/register.php',
        'forgot_password' => 'auth/forgot-password.php',
        'reset_password' => 'auth/reset-password.php',
        
        // Élèves
        'eleves' => 'eleves/liste.php',
        'eleves/ajouter' => 'eleves/ajouter.php',
        'eleves/modifier' => 'eleves/modifier.php',
        'eleves/admission' => 'eleves/admission.php',
        'eleves/parents' => 'eleves/parents.php',
        'eleves/detail' => 'eleves/detail.php',
        
        // Académique
        'notes' => 'academique/notes.php',
        'bulletins' => 'academique/bulletins.php',
        'classes' => 'academique/classes.php',
        'matieres' => 'academique/matieres.php',
        
        // Finance
        'paiements' => 'finance/paiements.php',
        'frais-scolaires' => 'finance/frais-scolaires.php',
        
        // Administration
        'administration/utilisateurs' => 'administration/utilisateurs/liste.php',
        'administration/utilisateurs/view' => 'administration/utilisateurs/details.php',
        'administration/utilisateurs/ajout' => 'administration/utilisateurs/ajoutModification.php',
        // 'administration/utilisateurs/modifier' => 'administration/utilisateurs/ajoutModification.php',
        'administration/parametres' => 'administration/parametres.php',
        'administration/logs' => 'administration/logs.php',
        'administration/audit' => 'administration/audit.php',
        'administration/annee-scolaire' => 'administration/annee-scolaire.php',
        
        // Profile utilisateur
        'profile' => 'utilisateur/profile.php',
        
        // Erreurs
        '404' => 'errors/404.php',
        '403' => 'errors/403.php',
        '500' => 'errors/500.php'
    ];
    
    // Gestion des routes dynamiques avec UUID
    if (isset($pages[$page])) {
        $file = __DIR__ . '/../pages/' . $pages[$page];
    } else {
        // Gestion des URLs avec UUID 
        $parts = explode('/', $page);
        
        // Vérifier si c'est une route de détail avec UUID
        if (count($parts) >= 3 && $parts[0] === 'administration' && $parts[1] === 'utilisateurs') {
            $action = $parts[2] ?? 'view';
            
            // Si on a un UUID dans l'URL
            if (isset($parts[3]) && is_uuid($parts[3])) {
                $_GET['uuid'] = $parts[3];
                
                // Déterminer quel fichier charger selon l'action
                switch ($action) {
                    case 'view':
                        $file = __DIR__ . '/../pages/administration/utilisateurs/details.php';
                        break;
                    case 'modifier':
                        $file = __DIR__ . '/../pages/administration/utilisateurs/ajoutModification.php';
                        break;
                    default:
                        $file = __DIR__ . '/../pages/errors/404.php';
                }
            } else {
                // Sans UUID, charger la liste ou l'ajout
                $file = __DIR__ . '/../pages/administration/utilisateurs/' . 
                       ($action === 'ajout' ? 'ajoutModification.php' : 'liste.php');
            }
        }
        // Gestion des autres routes dynamiques
        elseif (count($parts) == 2) {
            $resource = $parts[0];
            $id = $parts[1];
            
            // Vérifier si c'est un UUID valide
            if (is_uuid($id)) {
                $_GET['uuid'] = $id;
                
                // Routes prédéfinies pour chaque ressource
                $resource_routes = [
                    'eleves' => 'eleves/detail.php',
                    'classes' => 'academique/classes/detail.php',
                    // Ajouter d'autres ressources ici
                ];
                
                if (isset($resource_routes[$resource])) {
                    $file = __DIR__ . '/../pages/' . $resource_routes[$resource];
                } else {
                    $file = __DIR__ . '/../pages/errors/404.php';
                }
            } else {
                // Ancien système avec ID numérique
                $_GET['id'] = $id;
                if ($resource == 'eleves') {
                    $file = __DIR__ . '/../pages/eleves/detail.php';
                } else {
                    $file = __DIR__ . '/../pages/errors/404.php';
                }
            }
        } else {
            $file = __DIR__ . '/../pages/errors/404.php';
        }
    }
    
    if (file_exists($file)) {
        require_once $file;
    } else {
        require_once __DIR__ . '/../pages/errors/404.php';
    }
}

