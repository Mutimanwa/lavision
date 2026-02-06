<?php
require_once 'src/Config/config.php';

// Sécurité : Vérifier si l'utilisateur est un superadmin
// Note: La logique d'authentification et de gestion des rôles doit être implémentée
// if (!isset($_SESSION['utilisateur_role']) || $_SESSION['utilisateur_role'] !== 'superadmin') {
//     die("Accès non autorisé. Seul un super-administrateur peut exécuter cette action.");
// }

$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;
$charset = DB_CHARSET;

$sql_file = 'sql.sql';

if (!file_exists($sql_file)) {
    die("Erreur : Le fichier SQL '$sql_file' est introuvable.");
}

try {
    // Connexion au serveur MySQL sans sélectionner de base de données
    $pdo = new PDO("mysql:host=$db_host;charset=$charset", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    echo "Connexion au serveur MySQL réussie...<br>";

    // Lecture du contenu du fichier SQL
    $sql_content = file_get_contents($sql_file);

    if ($sql_content === false) {
        die("Erreur : Impossible de lire le fichier SQL.");
    }
    
    echo "Lecture du fichier SQL terminée...<br>";

    // Exécution des requêtes
    // PDO::exec peut gérer plusieurs requêtes séparées par des points-virgules
    $pdo->exec($sql_content);

    echo "<h3>Base de données réinitialisée avec succès !</h3>";
    echo "<p>La structure de la base de données et les données initiales ont été chargées depuis le fichier <strong>$sql_file</strong>.</p>";
    echo "<a href='index.php'>Retour à l'accueil</a>";

} catch (PDOException $e) {
    die("<h3>Erreur lors de la réinitialisation de la base de données :</h3><pre>" . $e->getMessage() . "</pre>");
} catch (Exception $e) {
    die("<h3>Une erreur inattendue est survenue :</h3><pre>" . $e->getMessage() . "</pre>");
}
