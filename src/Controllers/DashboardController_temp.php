<?php
/**
 * Contrôleur du tableau de bord - Version minimale
 * LaVision - Système de gestion scolaire
 */

require_once __DIR__ . "/../Services/auth.php";

/**
 * Affiche le tableau de bord principal
 */
function dashboard_index() {
    // Vérifier l'authentification
    if (!est_connecte()) {
        $_SESSION['error_message'] = 'Veuillez vous connecter pour accéder à cette page.';
        header('Location: ?page=login');
        exit;
    }

    // Contenu HTML simple pour tester
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord - LaVision</title>
    <link href='../public/assets/css/theme.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container mt-4'>
        <h1>Tableau de bord</h1>
        <p>Bienvenue sur le tableau de bord de LaVision !</p>
        <div class='alert alert-success'>
            <h4>Connexion réussie !</h4>
            <p>Utilisateur: " . ($_SESSION['utilisateur_nom'] ?? 'Inconnu') . "</p>
            <p>Rôle: " . ($_SESSION['utilisateur_role'] ?? 'Inconnu') . "</p>
        </div>
        <a href='?page=logout' class='btn btn-danger'>Déconnexion</a>
    </div>
</body>
</html>";
}
