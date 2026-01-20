<?php
/**
 * Test de la vue formulaire_professeur.php
 */

define('SERVICES_PATH', __DIR__ . '/src/Services');
define('SRC_PATH', __DIR__ . '/src');
define('DEBUG_MODE', true);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SCRIPT_NAME'] = '/test.php';

require_once 'src/Config/config.php';

echo "Test: Vue formulaire_professeur.php se charge correctement\n";

if (file_exists('src/Views/personnel/formulaire_professeur.php')) {
    echo "Fichier vue existe: Oui\n";

    $command = 'C:\xampp\php\php.exe -l src/Views/personnel/formulaire_professeur.php 2>&1';
    $syntax_check = shell_exec($command);
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "Syntaxe PHP: Correcte\n";
    } else {
        echo "Syntaxe PHP: Erreurs détectées\n" . $syntax_check;
    }

    $content = file_get_contents('src/Views/personnel/formulaire_professeur.php');
    $checks = [
        'formProfesseur' => strpos($content, 'formProfesseur') !== false,
        'csrf_token' => strpos($content, 'csrf_token') !== false,
        'updatePreview' => strpos($content, 'updatePreview') !== false,
        'checkValidity' => strpos($content, 'checkValidity') !== false,
        'matricule_prof' => strpos($content, 'matricule_prof') !== false,
        'date_naissance' => strpos($content, 'date_naissance') !== false
    ];

    echo "\nÉléments présents dans la vue:\n";
    foreach ($checks as $element => $present) {
        echo "  {$element}: " . ($present ? 'Oui' : 'Non') . "\n";
    }
} else {
    echo "Fichier vue existe: Non\n";
}

echo "\nTest terminé.\n";
?>