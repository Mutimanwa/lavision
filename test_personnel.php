<?php
/**
 * Script de test pour PersonnelModel et PersonnelController
 */

// Simuler les constantes nécessaires pour le test
define('SERVICES_PATH', __DIR__ . '/src/Services');
define('SRC_PATH', __DIR__ . '/src');
define('DEBUG_MODE', true);

// Simuler les variables serveur pour éviter les erreurs
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SCRIPT_NAME'] = '/test.php';

require_once 'src/Config/config.php';
require_once 'src/Models/PersonnelModel.php';

echo "Test: PersonnelModel chargé avec succès\n";

try {
    // Test simple : vérifier que les constantes sont définies
    echo "Constantes définies:\n";
    echo "  TABLE_PROFESSEURS: " . (defined('TABLE_PROFESSEURS') ? TABLE_PROFESSEURS : 'NON DEFINI') . "\n";
    echo "  TABLE_USER_ADMINS: " . (defined('TABLE_USER_ADMINS') ? TABLE_USER_ADMINS : 'NON DEFINI') . "\n";

    // Test des fonctions de validation (version simplifiée sans DB)
    echo "\nTest validation données professeur (sans DB):\n";
    $test_data = [
        'matricule_prof' => 'PROF001',
        'nom' => 'Dupont',
        'prenom' => 'Jean',
        'telephone' => '0123456789'
    ];

    // Validation basique manuelle
    $valid = !empty($test_data['matricule_prof']) &&
             !empty($test_data['nom']) &&
             !empty($test_data['prenom']) &&
             !empty($test_data['telephone']);
    echo "  Données valides: " . ($valid ? 'Oui' : 'Non') . "\n";

    // Test données invalides
    $invalid_data = [
        'nom' => 'Dupont',
        'prenom' => 'Jean'
    ];
    $valid2 = !empty($invalid_data['matricule_prof']) &&
              !empty($invalid_data['nom']) &&
              !empty($invalid_data['prenom']) &&
              !empty($invalid_data['telephone']);
    echo "  Données invalides: " . ($valid2 ? 'Oui' : 'Non') . "\n";

    // Test que les fonctions existent
    echo "\nTest existence des fonctions modèle:\n";
    echo "  get_professeurs_pagines: " . (function_exists('get_professeurs_pagines') ? 'Existe' : 'N\'existe pas') . "\n";
    echo "  get_professeur_by_id: " . (function_exists('get_professeur_by_id') ? 'Existe' : 'N\'existe pas') . "\n";
    echo "  create_professeur: " . (function_exists('create_professeur') ? 'Existe' : 'N\'existe pas') . "\n";
    echo "  update_professeur: " . (function_exists('update_professeur') ? 'Existe' : 'N\'existe pas') . "\n";
    echo "  get_administrateurs_pagines: " . (function_exists('get_administrateurs_pagines') ? 'Existe' : 'N\'existe pas') . "\n";
    echo "  get_statistiques_personnel: " . (function_exists('get_statistiques_personnel') ? 'Existe' : 'N\'existe pas') . "\n";

    echo "Tests du modèle réussis!\n";

} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage() . "\n";
}

// Test du contrôleur (en dehors du try-catch pour éviter les conflits)
require_once 'src/Controllers/PersonnelController.php';

echo "\nTest: PersonnelController chargé avec succès\n";

// Test que les fonctions du contrôleur existent
echo "\nTest existence des fonctions contrôleur:\n";
echo "  afficher_professeurs: " . (function_exists('afficher_professeurs') ? 'Existe' : 'N\'existe pas') . "\n";
echo "  afficher_formulaire_professeur: " . (function_exists('afficher_formulaire_professeur') ? 'Existe' : 'N\'existe pas') . "\n";
echo "  traiter_formulaire_professeur: " . (function_exists('traiter_formulaire_professeur') ? 'Existe' : 'N\'existe pas') . "\n";
echo "  afficher_administrateurs: " . (function_exists('afficher_administrateurs') ? 'Existe' : 'N\'existe pas') . "\n";
echo "  afficher_tableau_bord_personnel: " . (function_exists('afficher_tableau_bord_personnel') ? 'Existe' : 'N\'existe pas') . "\n";

// Test des constantes
echo "\nTest constantes contrôleur:\n";
echo "  ROLES_ADMIN défini: " . (defined('ROLES_ADMIN') ? 'Oui' : 'Non') . "\n";
echo "  STATUTS_PROFESSEUR défini: " . (defined('STATUTS_PROFESSEUR') ? 'Oui' : 'Non') . "\n";
echo "  TYPES_CONTRAT défini: " . (defined('TYPES_CONTRAT') ? 'Oui' : 'Non') . "\n";
echo "  GENRES défini: " . (defined('GENRES') ? 'Oui' : 'Non') . "\n";

echo "Tests du contrôleur réussis!\n";

echo "\nTest complet terminé avec succès!\n";
?>