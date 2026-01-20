<?php
/**
 * Test rapide de la vue liste_professeurs.php
 */

define('SERVICES_PATH', __DIR__ . '/src/Services');
define('SRC_PATH', __DIR__ . '/src');
define('DEBUG_MODE', true);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SCRIPT_NAME'] = '/test.php';

require_once 'src/Config/config.php';

echo "Test: Vue liste_professeurs.php se charge correctement\n";

// Tester que le fichier existe et est lisible
if (file_exists('src/Views/personnel/liste_professeurs.php')) {
    echo "Fichier vue existe: Oui\n";

    // Tester la syntaxe PHP
    $command = 'C:\xampp\php\php.exe -l src/Views/personnel/liste_professeurs.php 2>&1';
    $syntax_check = shell_exec($command);
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "Syntaxe PHP: Correcte\n";
    } else {
        echo "Syntaxe PHP: Erreurs détectées\n" . $syntax_check;
    }

    // Tester que le fichier contient les éléments attendus
    $content = file_get_contents('src/Views/personnel/liste_professeurs.php');
    $checks = [
        'extract($donnees_vue)' => strpos($content, 'extract($donnees_vue)') !== false,
        'table-responsive' => strpos($content, 'table-responsive') !== false,
        'btn-group' => strpos($content, 'btn-group') !== false,
        'modalSuppression' => strpos($content, 'modalSuppression') !== false,
        'build_url_with_params' => strpos($content, 'build_url_with_params') !== false
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