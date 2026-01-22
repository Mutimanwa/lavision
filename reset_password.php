<?php
require_once 'src/Config/Config.php';

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Générer un nouveau hash pour le mot de passe "password"
    $new_password_hash = password_hash('password', PASSWORD_DEFAULT);

    // Mettre à jour le mot de passe de superadmin
    $stmt = $pdo->prepare("UPDATE user_admins SET mot_de_passe = ? WHERE identifiant = ?");
    $stmt->execute([$new_password_hash, 'superadmin']);

    echo "Mot de passe de superadmin mis à jour avec succès.\n";
    echo "Nouveau hash: $new_password_hash\n";

    // Vérifier que ça fonctionne
    if (password_verify('password', $new_password_hash)) {
        echo "Le nouveau mot de passe 'password' fonctionne !\n";
    }

} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>