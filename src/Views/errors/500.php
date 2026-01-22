 
<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ===============================================--><!--    Document Title--><!-- ===============================================-->
    <title>Falcon </title>

    <!-- ===============================================--><!--    Favicons--><!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= IMAGES_URL?>favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= IMAGES_URL?>favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= IMAGES_URL?>favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?= IMAGES_URL?>favicons/favicon.ico">
    <link rel="manifest" href="<?= IMAGES_URL?>favicons/manifest.json">
    <meta name="msapplication-TileImage" content="<?= IMAGES_URL?>favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">
       <script src="<?= JS_URL?>config.js"></script>

    <!-- ===============================================--><!--    Stylesheets--><!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap"
        rel="stylesheet">
    <link href="<?= CSS_URL ?>theme.min.css" rel="stylesheet" id="style-default">
    <link href="<?= CSS_URL ?>user.min.css" rel="stylesheet" id="user-style-default">
</head>

<body>
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
 <main class="main" id="top">
      <div class="container">
        <div class="row flex-center min-vh-100 py-6 text-center">
          <div class="col-sm-10 col-md-8 col-lg-6 col-xxl-5"><a class="d-flex flex-center mb-4" href="<?= BASE_URL ?>"><img class="me-2" src="<?= IMAGES_URL?>icons/spot-illustrations/falcon.png" alt="" width="58" /><span class="font-sans-serif text-primary fw-bolder fs-4 d-inline-block">falcon</span></a>
            <div class="card">
              <div class="card-body p-4 p-sm-5">
                <div class="fw-black lh-1 text-300 fs-error">500</div>
                <p class="lead mt-4 text-800 font-sans-serif fw-semi-bold">Oups, quelque chose n'a pas tourné !</p>
                <hr />
                <p>Essayez de rafraîchir la page, ou de revenir en arrière et de tenter à nouveau l'action. Si ce problème persiste, <a href="mailto:info@exmaple.com">contact nous</a>.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
</body>
</html>