<?php include 'header.php'; ?>

<div class="card mb-3">
  <div class="card-header ">
    <div class="row align-items-center">
      <div class="col-auto d-none d-sm-block">
        <img src="assets/img/team/avatar.png" alt="Photo de l'élève" 
             class="border border-3 border-white rounded-circle" width="100px">
      </div>
      <div class="col">
        <h5 class="mb-1">Tony Robbins</h5>
        <p class="mb-1">Élève en Classe de 3ème B</p>
        <a href="mailto:tony@gmail.com">tonyrobbins@gmail.com</a>
      </div>
      <div class="col-auto ">
        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
          <span class="fas fa-ellipsis-v"></span>
        </button>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="#"><span class="fas fa-edit me-2"></span>Modifier</a>
          <a class="dropdown-item" href="#"><span class="fas fa-print me-2"></span>Fiche scolaire</a>
          <a class="dropdown-item" href="#"><span class="fas fa-file-pdf me-2"></span>Bulletin</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-danger" href="#"><span class="fas fa-user-times me-2"></span>Désinscrire</a>
        </div>
      </div>
    </div>
  </div>
  <div class="card-body bg-body-tertiary border-top">
    <div class="row">
      <div class="col-md-4 border-end">
        <div class="d-flex align-items-center mb-2">
          <span class="fas fa-calendar-alt text-primary me-2"></span>
          <div>
            <p class="mb-0">Date d'inscription</p>
            <p class="fs-10 mb-0 text-600">12 Janvier 2023</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 border-end">
        <div class="d-flex align-items-center mb-2">
          <span class="fas fa-chalkboard-teacher text-primary me-2"></span>
          <div>
            <p class="mb-0">Professeur principal</p>
            <p class="fs-10 mb-0 text-600">M. Dupont</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="d-flex align-items-center mb-2">
          <span class="fas fa-graduation-cap text-primary me-2"></span>
          <div>
            <p class="mb-0">Moyenne générale</p>
            <p class="fs-10 mb-0 text-600">14.2/20</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Détails sur l'élève -->
<div class="card mb-3">
  <div class="card-header">
    <div class="row align-items-center">
      <div class="col">
        <h5 class="mb-0">Informations scolaires</h5>
      </div>
      <div class="col-auto">
        <a class="btn btn-falcon-default btn-sm" href="#!">
          <span class="fas fa-pencil-alt fs-11 me-1"></span> Modifier
        </a>
      </div>
    </div>
  </div>
  <div class="card-body bg-body-tertiary border-top">
    <div class="row">
      <div class="col-lg-6">
        <h6 class="fw-semi-bold ls mb-3 text-uppercase">Informations personnelles</h6>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">ID Élève</p>
          </div>
          <div class="col">ELV-2023-087</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Date de naissance</p>
          </div>
          <div class="col">15/05/2010</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Lieu de naissance</p>
          </div>
          <div class="col">Paris, France</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Email</p>
          </div>
          <div class="col"><a href="mailto:tony@gmail.com">tony@gmail.com</a></div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-0">Téléphone</p>
          </div>
          <div class="col"><a href="tel:+12025550110">+1-202-555-0110</a></div>
        </div>
      </div>
      <div class="col-lg-6 mt-4 mt-lg-0">
        <h6 class="fw-semi-bold ls mb-3 text-uppercase">Informations scolaires</h6>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Niveau</p>
          </div>
          <div class="col">3ème</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Classe</p>
          </div>
          <div class="col">3ème B</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Option</p>
          </div>
          <div class="col">Latin</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Régime</p>
          </div>
          <div class="col">Externe</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-0">Transport scolaire</p>
          </div>
          <div class="col">Ligne 12</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Informations des responsables -->
<div class="card mb-3">
  <div class="card-header">
    <div class="row align-items-center">
      <div class="col">
        <h5 class="mb-0">Responsables légaux</h5>
      </div>
      <div class="col-auto">
        <a class="btn btn-falcon-default btn-sm" href="#!">
          <span class="fas fa-pencil-alt fs-11 me-1"></span> Modifier
        </a>
      </div>
    </div>
  </div>
  <div class="card-body bg-body-tertiary border-top">
    <div class="row">
      <div class="col-lg-6">
        <h6 class="fw-semi-bold ls mb-3 text-uppercase">Parent 1</h6>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Nom</p>
          </div>
          <div class="col">John Robbins</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Email</p>
          </div>
          <div class="col"><a href="mailto:john.robbins@gmail.com">john.robbins@gmail.com</a></div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Téléphone</p>
          </div>
          <div class="col"><a href="tel:+12025550111">+1-202-555-0111</a></div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-0">Profession</p>
          </div>
          <div class="col">Ingénieur</div>
        </div>
      </div>
      <div class="col-lg-6 mt-4 mt-lg-0">
        <h6 class="fw-semi-bold ls mb-3 text-uppercase">Parent 2</h6>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Nom</p>
          </div>
          <div class="col">Maria Robbins</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Email</p>
          </div>
          <div class="col"><a href="mailto:maria.robbins@gmail.com">maria.robbins@gmail.com</a></div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Téléphone</p>
          </div>
          <div class="col"><a href="tel:+12025550112">+1-202-555-0112</a></div>
        </div>
        <div class="row mb-2">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-0">Profession</p>
          </div>
          <div class="col">Enseignante</div>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-12">
        <h6 class="fw-semi-bold ls mb-3 text-uppercase">Adresse</h6>
        <div class="row">
          <div class="col-5 col-sm-4">
            <p class="fw-semi-bold mb-1">Domicile</p>
          </div>
          <div class="col">
            <p class="mb-1">12 Rue des Écoles<br>75005 Paris, France</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Notes et évaluations -->
<div class="card mb-3">
  <div class="card-header">
    <h5 class="mb-0">Notes et évaluations</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-striped mb-0">
        <thead class="bg-200">
          <tr>
            <th class="text-900">Matière</th>
            <th class="text-900">Note</th>
            <th class="text-900">Appréciation</th>
            <th class="text-900">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Mathématiques</td>
            <td>15/20</td>
            <td>Très bon travail</td>
            <td>15/06/2023</td>
          </tr>
          <tr>
            <td>Français</td>
            <td>13/20</td>
            <td>Peut mieux faire</td>
            <td>16/06/2023</td>
          </tr>
          <tr>
            <td>Histoire-Géographie</td>
            <td>16/20</td>
            <td>Excellent</td>
            <td>14/06/2023</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer bg-body-tertiary text-end">
    <a class="btn btn-falcon-primary btn-sm" href="#!">
      <span class="fas fa-plus fs-11 me-1"></span> Ajouter une note
    </a>
    <a class="btn btn-falcon-default btn-sm ms-2" href="#!">
      <span class="fas fa-file-pdf fs-11 me-1"></span> Bulletin complet
    </a>
  </div>
</div>

<?php include 'footer.php'; ?>