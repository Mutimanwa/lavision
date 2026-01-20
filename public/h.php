<!-- public/final-test.html -->
<!DOCTYPE html>
<html>
<head>
    <title>Test Final</title>
</head>
<body>
    <h1>Test des images corrigées</h1>
    
    <?php
    require_once '../includes/config/config.php';
    ?>
    
    <h2>Test 1: Logo Falcon</h2>
    <img src="<?= IMAGES_URL ?>/icons/spot-illustrations/falcon.png" 
         alt="Test 1" 
         onerror="this.style.border='3px solid red'"
         onload="this.style.border='3px solid green'">
    
    <h2>Test 2: Navbar vertical</h2>
    <img src="<?= IMAGES_URL ?>/icons/spot-illustrations/navbar-vertical.png" 
         alt="Test 2"
         onerror="this.style.border='3px solid red'"
         onload="this.style.border='3px solid green'">
    
    <h2>Test 3: Team photo</h2>
    <img src="<?= IMAGES_URL ?>/team/1-thumb.png" 
         alt="Test 3"
         onerror="this.style.border='3px solid red'"
         onload="this.style.border='3px solid green'">
    
    <script>
    // Vérification JS
    console.log('IMAGES_URL:', '<?= IMAGES_URL ?>');
    
    // Vérifier toutes les images après chargement
    window.addEventListener('load', function() {
        document.querySelectorAll('img').forEach(img => {
            console.log(img.src, img.complete ? '✅ Chargé' : '❌ Erreur');
        });
    });
    </script>
</body>
</html>