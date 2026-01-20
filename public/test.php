<?php
// public/test-config.php
require_once '../includes/config/config.php';

echo '<h1>Test de configuration</h1>';
echo '<pre>';
echo 'BASE_URL: ' . BASE_URL . "\n";
echo 'ASSETS_URL: ' . ASSETS_URL . "\n";
echo 'IMAGES_URL: ' . IMAGES_URL . "\n";

// Test d'un chemin spécifique
$test_image = IMAGES_URL . 'icons/spot-illustrations/falcon.png';
echo "\nURL test image: " . $test_image . "\n";

// Chemin physique correspondant
$physical_path = PUBLIC_PATH . '/assets/img/icons/spot-illustrations/falcon.png';
echo "Chemin physique: " . $physical_path . "\n";
echo "Fichier existe: " . (file_exists($physical_path) ? '✅ OUI' : '❌ NON') . "\n";

// Liste les fichiers dans le dossier
echo "\nFichiers dans img/icons/spot-illustrations/:\n";
$scan_path = PUBLIC_PATH . '/assets/img/icons/spot-illustrations/';
if (is_dir($scan_path)) {
    $files = scandir($scan_path);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
} else {
    echo "  ❌ Dossier introuvable: $scan_path\n";
}
echo '</pre>';

// Afficher l'image pour test
echo '<h2>Test d\'affichage de l\'image</h2>';
echo '<img src="' . $test_image . '" alt="Test" style="border: 2px solid red;">';
?>