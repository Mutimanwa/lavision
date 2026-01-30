<?php
/**
 * Script de test rapide - LaVision
 * Vérification du bon fonctionnement de l'application après refactorisation
 */

// Démarrer la session pour les tests
session_start();

// Inclure l'autochargement
require_once __DIR__ . '/src/Config/autoload.php';

// Fonction de test des constantes
function testConstants() {
    echo "=== TEST DES CONSTANTES ===\n";

    $required_constants = [
        'BASE_URL', 'SRC_PATH', 'PUBLIC_PATH', 'ASSETS_PATH',
        'CSS_PATH', 'JS_PATH', 'IMG_PATH', 'UPLOADS_PATH',
        'LOGS_PATH', 'BACKUPS_PATH', 'DB_HOST', 'DB_NAME'
    ];

    $all_defined = true;
    foreach ($required_constants as $const) {
        if (defined($const)) {
            echo "✅ $const : " . constant($const) . "\n";
        } else {
            echo "❌ $const : NON DÉFINI\n";
            $all_defined = false;
        }
    }

    echo "\n" . ($all_defined ? "✅ Toutes les constantes sont définies" : "❌ Certaines constantes sont manquantes") . "\n\n";
    return $all_defined;
}

// Fonction de test de la connexion BDD
function testDatabase() {
    echo "=== TEST DE LA BASE DE DONNÉES ===\n";

    try {
        $db = get_db_connection();
        echo "✅ Connexion à la base de données réussie\n";

        // Tester une requête simple
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['test'] == 1) {
            echo "✅ Requête de test exécutée avec succès\n";
            return true;
        } else {
            echo "❌ Requête de test échouée\n";
            return false;
        }

    } catch (Exception $e) {
        echo "❌ Erreur de connexion à la base de données : " . $e->getMessage() . "\n";
        return false;
    }

    echo "\n";
}

// Fonction de test des modules (procédural)
function testModules() {
    echo "=== TEST DES MODULES ===\n";

    $modules_to_test = [
        'EleveController' => 'src/Controllers/EleveController.php',
        'EleveModel' => 'src/Models/EleveModel.php',
        'AcademiqueController' => 'src/Controllers/AcademiqueController.php',
        'AcademiqueModel' => 'src/Models/AcademiqueModel.php',
        'PersonnelController' => 'src/Controllers/PersonnelController.php',
        'PersonnelModel' => 'src/Models/PersonnelModel.php',
        'FinanceController' => 'src/Controllers/FinanceController.php',
        'FinanceModel' => 'src/Models/FinanceModel.php',
        'RapportsController' => 'src/Controllers/RapportsController.php',
        'RapportsModel' => 'src/Models/RapportsModel.php'
    ];

    $all_loaded = true;
    foreach ($modules_to_test as $module => $file) {
        $full_path = ROOT_PATH . '/' . $file;
        if (file_exists($full_path)) {
            // Inclure le fichier pour vérifier qu'il n'y a pas d'erreurs de syntaxe
            ob_start();
            $included = include_once $full_path;
            ob_end_clean();

            if ($included !== false) {
                echo "✅ Module $module chargé ($file)\n";
            } else {
                echo "❌ Module $module erreur de chargement ($file)\n";
                $all_loaded = false;
            }
        } else {
            echo "❌ Module $module fichier manquant ($file)\n";
            $all_loaded = false;
        }
    }

    echo "\n" . ($all_loaded ? "✅ Tous les modules sont chargés" : "❌ Certains modules sont manquants") . "\n\n";
    return $all_loaded;
}

// Fonction de test des services
function testServices() {
    echo "=== TEST DES SERVICES ===\n";

    $services_to_test = [
        'get_db_connection',
        'logError',
        'logAction',
        'sanitizeInput',
        'validateCSRFToken',
        'generateCSRFToken',
        'buildUrl',
        'formatDate',
        'formatCurrency'
    ];

    $all_available = true;
    foreach ($services_to_test as $service) {
        if (function_exists($service)) {
            echo "✅ Fonction $service disponible\n";
        } else {
            echo "❌ Fonction $service non trouvée\n";
            $all_available = false;
        }
    }

    echo "\n" . ($all_available ? "✅ Tous les services sont disponibles" : "❌ Certains services sont manquants") . "\n\n";
    return $all_available;
}

// Fonction de test des fichiers
function testFiles() {
    echo "=== TEST DES FICHIERS ===\n";

    $files_to_test = [
        'src/Config/config.php',
        'src/Config/routes.php',
        'src/Config/autoload.php',
        'src/Controllers/BaseController.php',
        'src/Models/BaseModel.php',
        'src/Services/database.php',
        'src/Services/auth.php',
        'src/Services/functions.php',
        'src/Views/templates/header.php',
        'src/Views/templates/footer.php',
        'src/Views/templates/sidebar.php',
        'index.php',
        '.htaccess'
    ];

    $all_exist = true;
    foreach ($files_to_test as $file) {
        $file_path = __DIR__ . '/' . $file;
        if (file_exists($file_path)) {
            echo "✅ Fichier $file existe\n";
        } else {
            echo "❌ Fichier $file manquant\n";
            $all_exist = false;
        }
    }

    echo "\n" . ($all_exist ? "✅ Tous les fichiers critiques sont présents" : "❌ Certains fichiers critiques sont manquants") . "\n\n";
    return $all_exist;
}

// Fonction de test des permissions
function testPermissions() {
    echo "=== TEST DES PERMISSIONS ===\n";

    $dirs_to_test = [
        'logs' => ['actions', 'errors'],
        'backups',
        'public/uploads' => ['documents', 'eleves', 'professeurs', 'profiles']
    ];

    $all_writable = true;
    foreach ($dirs_to_test as $dir => $subdirs) {
        $dir_path = __DIR__ . '/' . $dir;

        if (is_array($subdirs)) {
            foreach ($subdirs as $subdir) {
                $full_path = $dir_path . '/' . $subdir;
                if (is_writable($full_path)) {
                    echo "✅ Répertoire $full_path accessible en écriture\n";
                } else {
                    echo "❌ Répertoire $full_path non accessible en écriture\n";
                    $all_writable = false;
                }
            }
        } else {
            if (is_writable($dir_path)) {
                echo "✅ Répertoire $dir_path accessible en écriture\n";
            } else {
                echo "❌ Répertoire $dir_path non accessible en écriture\n";
                $all_writable = false;
            }
        }
    }

    echo "\n" . ($all_writable ? "✅ Toutes les permissions sont correctes" : "❌ Certaines permissions sont incorrectes") . "\n\n";
    return $all_writable;
}

// Exécuter tous les tests
function runAllTests() {
    echo "🚀 DÉMARRAGE DES TESTS DE LAVISION V2.0\n";
    echo "=======================================\n\n";

    $results = [];

    $results['constants'] = testConstants();
    $results['files'] = testFiles();
    $results['modules'] = testModules();
    $results['services'] = testServices();
    $results['database'] = testDatabase();
    $results['permissions'] = testPermissions();

    // Résumé final
    echo "=======================================\n";
    echo "📊 RÉSULTATS DES TESTS\n";
    echo "=======================================\n";

    $total_tests = count($results);
    $passed_tests = count(array_filter($results));

    foreach ($results as $test => $passed) {
        $status = $passed ? "✅ PASSÉ" : "❌ ÉCHOUÉ";
        echo ucfirst($test) . ": $status\n";
    }

    echo "\n📈 SCORE: $passed_tests/$total_tests tests réussis\n";

    if ($passed_tests === $total_tests) {
        echo "🎉 FÉLICITATIONS ! Tous les tests sont passés. LaVision est prêt pour la production !\n";
        return true;
    } else {
        echo "⚠️ Certains tests ont échoué. Veuillez corriger les problèmes avant le déploiement.\n";
        return false;
    }
}

// Lancer les tests
$success = runAllTests();

// Code de sortie pour les scripts automatisés
exit($success ? 0 : 1);
?>