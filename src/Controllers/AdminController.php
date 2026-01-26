<?php
/**
 * Contrôleur d'administration - Version procédurale
 * Gestion des utilisateurs, paramètres système, logs, etc.
 */

// Inclure les modèles nécessaires
require_once __DIR__ . '/../Models/AdminModel.php';

// Variable globale pour le modèle admin
global $adminModel;
$adminModel = new AdminModel();

/**
 * Liste des utilisateurs
 */
function admin_utilisateurs() {
    global $adminModel;

    // Vérifier les permissions
    // if (!hasPermission('admin.users')) {
    //     afficher_erreur('Vous avez pas l\'autorisaion necessaire', 403);
    // }

    $page = $_GET['page'] ?? 1;
    $search = $_GET['search'] ?? '';
    $role = $_GET['role'] ?? '';
    $statut = $_GET['statut'] ?? '';

    $result = $adminModel->getUtilisateurs($page, $search, $role, $statut);

    render('administration/utilisateurs', [
        'utilisateurs' => $result['utilisateurs'],
        'pagination' => $result['pagination'],
        'search' => $search,
        'role' => $role,
        'statut' => $statut,
        'roles' => $adminModel->getRoles()
    ]);
}

/**
 * Gestion des paramètres système
 */
function admin_parametres() {
    global $adminModel;

    // Vérifier les permissions
    if (!hasPermission('admin.settings')) {
        load_error_page(403);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_handleParametresUpdate();
    }

    $parametres = $adminModel->getParametres();

    render('administration/parametres', [
        'parametres' => $parametres
    ]);
}

/**
 * Gestion de l'année scolaire
 */
function admin_anneeScolaire() {
    global $adminModel;

    // Vérifier les permissions
    if (!hasPermission('admin.settings')) {
        redirect('dashboard', ['access' =>'Accès non autorisé', 'success' => 'error']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_handleAnneeScolaireUpdate();
    }

    $annees = $adminModel->getAnneesScolaires();

    render('administration/annee-scolaire', [
        'annees' => $annees
    ]);
}

/**
 * Consultation des logs système
 */
function admin_logs() {
    global $adminModel;

    // Vérifier les permissions
    if (!hasPermission('admin.logs')) {
        redirect('dashboard', ['Accès non autorisé', 'error']);
    }

    $page = $_GET['page'] ?? 1;
    $niveau = $_GET['niveau'] ?? '';
    $categorie = $_GET['categorie'] ?? '';
    $date_debut = $_GET['date_debut'] ?? '';
    $date_fin = $_GET['date_fin'] ?? '';

    $result = $adminModel->getLogs($page, $niveau, $categorie, $date_debut, $date_fin);

    render('administration/logs', [
        'logs' => $result['logs'],
        'pagination' => $result['pagination'],
        'niveau' => $niveau,
        'categorie' => $categorie,
        'date_debut' => $date_debut,
        'date_fin' => $date_fin,
        'niveaux' => $adminModel->getNiveauxLog(),
        'categories' => $adminModel->getCategoriesLog()
    ]);
}

/**
 * Gestion des sauvegardes
 */
function admin_backup() {
    global $adminModel;

    // Vérifier les permissions
    if (!hasPermission('admin.backup')) {
        redirect('dashboard', ['Accès non autorisé', 'error']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_handleBackupAction();
    }

    $backups = $adminModel->getBackupLogs();

    render('administration/backup', [
        'backups' => $backups
    ]);
}

/**
 * Restauration d'une sauvegarde
 */
function admin_restore() {
    global $adminModel;

    // Vérifier les permissions
    if (!hasPermission('admin.backup')) {
        redirect('/dashboard', ['Accès non autorisé', 'error']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_handleRestoreAction();
    }

    $backups = $adminModel->getAvailableBackups();

    render('administration/restore', [
        'backups' => $backups
    ]);
}

/**
 * Gestion des utilisateurs - Actions CRUD
 */
function admin_handleParametresUpdate() {
    global $adminModel;

    validateCSRFToken($_POST['csrf_token'] ?? '');

    $parametres = $_POST['parametres'] ?? [];

    if ($adminModel->updateParametres($parametres)) {
        logAction('admin', 'Modification des paramètres système', ['count' => count($parametres)]);
        redirect('administration/parametres', ['Paramètres mis à jour avec succès', 'success']);
    } else {
        redirect('administration/parametres', ['Erreur lors de la mise à jour des paramètres', 'error']);
    }
}

function admin_handleAnneeScolaireUpdate() {
    global $adminModel;

    validateCSRFToken($_POST['csrf_token'] ?? '');

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'creer':
            $data = [
                'annee_libelle' => $_POST['annee_libelle'] ?? '',
                'date_debut' => $_POST['date_debut'] ?? '',
                'date_fin' => $_POST['date_fin'] ?? '',
                'statut' => $_POST['statut'] ?? 'inactive'
            ];

            if ($adminModel->creerAnneeScolaire($data)) {
                logAction('admin', 'Création d\'une année scolaire', $data);
                redirect('administration/annee-scolaire', ['Année scolaire créée avec succès', 'success']);
            }
            break;

        case 'modifier':
            $annee_id = $_POST['annee_id'] ?? 0;
            $data = [
                'annee_libelle' => $_POST['annee_libelle'] ?? '',
                'date_debut' => $_POST['date_debut'] ?? '',
                'date_fin' => $_POST['date_fin'] ?? '',
                'statut' => $_POST['statut'] ?? 'inactive'
            ];

            if ($adminModel->modifierAnneeScolaire($annee_id, $data)) {
                logAction('admin', 'Modification d\'une année scolaire', array_merge(['annee_id' => $annee_id], $data));
                redirect('administration/annee-scolaire', ['Année scolaire modifiée avec succès', 'success']);
            }
            break;

        case 'activer':
            $annee_id = $_POST['annee_id'] ?? 0;
            if ($adminModel->activerAnneeScolaire($annee_id)) {
                logAction('admin', 'Activation d\'une année scolaire', ['annee_id' => $annee_id]);
                redirect('administration/annee-scolaire', ['Année scolaire activée avec succès', 'success']);
            }
            break;
    }

    redirect('administration/annee-scolaire', ['Erreur lors de l\'opération', 'error']);
}

function admin_handleBackupAction() {
    global $adminModel;

    validateCSRFToken($_POST['csrf_token'] ?? '');

    $type = $_POST['type'] ?? 'manuel';

    if ($adminModel->creerSauvegarde($type)) {
        logAction('admin', 'Création d\'une sauvegarde', ['type' => $type]);
        redirect('administration/backup', ['Sauvegarde créée avec succès', 'success']);
    } else {
        redirect('administration/backup', ['Erreur lors de la création de la sauvegarde', 'error']);
    }
}

function admin_handleRestoreAction() {
    global $adminModel;

    validateCSRFToken($_POST['csrf_token'] ?? '');

    $backup_id = $_POST['backup_id'] ?? 0;

    if ($adminModel->restaurerSauvegarde($backup_id)) {
        logAction('admin', 'Restauration d\'une sauvegarde', ['backup_id' => $backup_id]);
        redirect('administration/backup', ['Sauvegarde restaurée avec succès', 'success']);
    } else {
        redirect('administration/backup', ['Erreur lors de la restauration', 'error']);
    }
}
?>