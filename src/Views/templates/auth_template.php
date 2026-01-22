<!DOCTYPE html>
<html data-bs-theme="light" lang="fr" dir="ltr">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Falcon</title>

  <!-- ===============================================-->
  <!--    Favicons-->
  <!-- ===============================================-->
  <link rel="apple-touch-icon" sizes="180x180" href="<?= IMAGES_URL ?>favicons/apple-touch-icon.png" />
  <link rel="icon" type="image/png" sizes="32x32" href="<?= IMAGES_URL ?>favicons/favicon-32x32.png" />
  <link rel="icon" type="image/png" sizes="16x16" href="<?= IMAGES_URL ?>favicons/favicon-16x16.png" />
  <link rel="shortcut icon" type="image/x-icon" href="<?= IMAGES_URL ?>favicons/favicon.ico" />
  <link rel="manifest" href="<?= IMAGES_URL ?>favicons/manifest.json" />
  <meta name="msapplication-TileImage" content="<?= IMAGES_URL ?>favicons/mstile-150x150.png" />
  <meta name="theme-color" content="#ffffff" />
  <script src="<?= JS_URL ?>config.js"></script>
  <script src="<?= LIBS_URL ?>jquery/jquery.min.js"></script>


  <!-- ===============================================-->
  <!--    Stylesheets-->
  <!-- ===============================================-->
  <!-- <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap"
    rel="stylesheet" /> -->
  <link href="<?= CSS_URL ?>/theme.min.css" rel="stylesheet" id="style-default" />
  <link href="<?= CSS_URL ?>/user.min.css" rel="stylesheet" id="user-style-default" />

</head>

<body>
  <!-- ===============================================-->
  <!--    Main Content-->
  <!-- ===============================================-->
  <?php
  // Inclure le contenu de la page d'auth spécifique
    $page = get_current_page();
    load_page($page);
  ?>

  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->


  <!-- ===============================================-->
  <!--    JavaScripts-->
<!-- ===============================================-->
  <script src="<?= JS_URL ?>theme.js"></script>
  <script src="<?= LIBS_URL ?>fontawesome/all.min.js"></script>

  <script src="<?= LIBS_URL ?>bootstrap/bootstrap.min.js"></script>
</body>

</html>