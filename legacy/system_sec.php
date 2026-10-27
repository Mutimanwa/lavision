<?php
// Démarrer la session
session_start();

// Connexion à la base de données SQLite
try {
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer la table 'users' si elle n'existe pas
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL
    )");
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction de validation pour un mot de passe fort
function isStrongPassword($password) {
    // Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

$message = '';
$is_login_page = true; // Variable pour déterminer le mode d'affichage

// Gérer l'inscription
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validation des entrées
    if (empty($username) || empty($password)) {
        $message = "Veuillez remplir tous les champs.";
    } elseif (!isStrongPassword($password)) {
        $message = "Le mot de passe n'est pas assez fort. Il doit contenir au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.";
    } else {
        // Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insertion de l'utilisateur dans la base de données
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            $is_login_page = true; // Afficher à nouveau le formulaire de connexion après l'inscription
        } catch (PDOException $e) {
            $message = "Erreur : Ce nom d'utilisateur existe déjà.";
        }
    }
}

// Gérer la connexion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Veuillez remplir tous les champs.";
    } else {
        // Préparer et exécuter la requête pour trouver l'utilisateur
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification de l'utilisateur et du mot de passe
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php"); // Redirection vers une page de tableau de bord
            exit();
        } else {
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .form-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            background-color: white;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center mb-4"><?php echo $is_login_page ? 'Connexion' : 'Inscription'; ?></h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo strpos($message, 'réussie') !== false ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <div class="row">
        <form action="" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">
                    Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.
                </div>
            </div>
            <?php if ($is_login_page): ?>
                <button type="submit" name="login" class="btn btn-primary w-100">Se connecter</button>
                <hr>
                <p class="text-center">Pas encore de compte ?</p>
                <button type="submit" name="register" class="btn btn-secondary w-100">S'inscrire</button>
            <?php else: ?>
                <button type="submit" name="register" class="btn btn-primary w-100">S'inscrire</button>
            <?php endif; ?>
        </form>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>