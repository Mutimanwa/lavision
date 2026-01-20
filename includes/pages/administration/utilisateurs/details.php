<?php
/**
 * Module d'administration - Gestion des utilisateurs
 * 
 **/
require_once __DIR__ . '/../../../modules/administration/utilisateurs.php';
require_access("users");

// Récupérer l'UUID depuis l'URL
$uuid = $_GET['uuid'] ?? null;

if (!$uuid) {
  $_SESSION['error'] = 'UUID utilisateur invalide';
  redirect('administration/utilisateurs');
}

$utilisateur = admin_get_utilisateur($uuid);

if (!$utilisateur) {
  $_SESSION['error'] = 'Utilisateur introuvable';
  redirect('administration/utilisateurs');
}

?>
<div class="card mb-4">
  <div class="card-header ">
    <div class="row align-items-center">
      <div class="col-auto d-none d-sm-block">
        <div class="avatar avatar-4xl position-relative">
          <img class="rounded-circle border" src="uploads/profiles/<?= $utilisateur['photo'] ?>" alt="Super Admin"
            width="120" id="profilePhoto" onerror="this.src='assets/img/icons/user-avatar.png'">
        </div>
      </div>
      <div class="col">
        <h5 class="mb-1"><?= e($utilisateur['identifiant']) ?></h5>
        <p class="mb-1"><?= e($utilisateur['nom_complet']) ?></p>
        <a href="mailto:<?= e($utilisateur['email']) ?>"><?= e($utilisateur['email']) ?></a>
      </div>
      <div class="col-auto">
        <?php
        $status_class = '';
        switch ($utilisateur['statut']) {
          case STATUS_ACTIF:
            $status_class = 'success';
            break;
          case STATUS_INACTIF:
            $status_class = 'danger';
            break;
          case STATUS_SUSPENDU:
            $status_class = 'warning';
            break;
          case STATUS_BLOQUE:
            $status_class = 'dark';
            break;
          default:
            $status_class = 'info';
            break;
        }
        ?>
        <span class="badge bg-<?= $status_class; ?> rounded-pill"><?= e($utilisateur['statut']) ?></span>
      </div>
    </div>
  </div>
  <div class="card-body bg-body-tertiary border-top">
    <div class="row">
      <div class="col-md-4 col-12 border-end">
        <div class="d-flex align-items-center mb-2">
          <span class="fas fa-calendar-alt text-primary me-2"></span>
          <div>
            <p class="mb-0">Membre depuis</p>
            <p class="fs-10 mb-0 text-600"><?= e($utilisateur['date_creation']) ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-12 border-end">
        <div class="d-flex align-items-center mb-2">
          <span class="fas fa-clock text-primary me-2"></span>
          <div>
            <p class="mb-0">Dernière connexion</p>
            <p class="fs-10 mb-0 text-600"><?= time_ago($utilisateur['date_creation']) ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-4 col-12 border-end">
        <div class="d-flex align-items-center mb-2">
          <span class="fas fa-shield-alt text-primary me-2"></span>
          <div>
            <p class="mb-0">Niveau d'accès</p>
            <p class="fs-10 mb-0 text-600"><?= e($utilisateur['role']) ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <!-- Détails du compte -->
    <div class="card mb-4">
      <div class="card-header">
        <div class="row align-items-center">
          <div class="col">
            <h5 class="mb-0">Détails du compte</h5>
          </div>
          <div class="col-auto">
            <button class="btn btn-falcon-default btn-sm">
              <span class="fas fa-pencil-alt fs-11 me-1"></span> Modifier
            </button>
          </div>
        </div>
      </div>
      <div class="card-body bg-body-tertiary border-top">
        <div class="row">
          <div class="col-md-6">
            <h6 class="fw-semi-bold ls mb-3 text-uppercase">Informations personnelles</h6>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">ID Utilisateur</p>
              </div>
              <div class="col"><?= $utilisateur['user_id'] ?></div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Nom complet</p>
              </div>
              <div class="col"><?= $utilisateur['nom_complet'] ?></div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Email</p>
              </div>
              <div class="col"><a href="mailto:<?= $utilisateur['email'] ?>"><?= $utilisateur['email'] ?></a></div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Téléphone</p>
              </div>
              <div class="col"><a href="tel:<?= $utilisateur['telephone'] ?>"><?= $utilisateur['telephone'] ?></a></div>
            </div>
          </div>
          <div class="col-md-6 mt-4 mt-md-0">
            <h6 class="fw-semi-bold ls mb-3 text-uppercase">Informations de connexion</h6>
           
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Rôle</p>
              </div>
              <div class="col"><span class="badge bg-primary"><?= $utilisateur['role'] ?></span></div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Statut</p>
              </div>
              <div class="col">
                <?php
                $status_class = '';
                switch ($utilisateur['statut']) {
                  case STATUS_ACTIF:
                    $status_class = 'success';
                    break;
                  case STATUS_INACTIF:
                    $status_class = 'danger';
                    break;
                  case STATUS_SUSPENDU:
                    $status_class = 'warning';
                    break;
                  case STATUS_BLOQUE:
                    $status_class = 'dark';
                    break;
                  default:
                    $status_class = 'info';
                    break;
                }
                ?>
                <span class="badge bg-<?= $status_class; ?> rounded-pill"><?= e($utilisateur['statut']) ?></span>

              </div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Dernière connexion</p>
              </div>
              <div class="col"><?= time_ago($utilisateur['dernier_login'])  ?></div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-0">IP de connexion</p>
              </div>
              <!-- <div class="col"><?= get_client_ip() ?></div> -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Préférences -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Préférences et paramètres</h5>
      </div>
      <div class="card-body bg-body-tertiary border-top">
        <div class="row">
          <div class="col-md-6">
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Langue</p>
              </div>
              <div class="col">Français</div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Fuseau horaire</p>
              </div>
              <div class="col">Europe/Paris (UTC+1)</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Format de date</p>
              </div>
              <div class="col">DD/MM/YYYY</div>
            </div>
            <div class="row mb-2">
              <div class="col-5 col-sm-4">
                <p class="fw-semi-bold mb-1">Notifications</p>
              </div>
              <div class="col">
                <span class="badge bg-success">Activées</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <!-- Statuts et actions rapides -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Actions</h5>
      </div>
      <div class="card-body bg-body-tertiary border-top">
        <div class="d-grid gap-2">
          <button class="btn btn-falcon-default btn-sm">
            <span class="fas fa-envelope me-2"></span> Envoyer un message
          </button>
          <button class="btn btn-falcon-default btn-sm">
            <span class="fas fa-key me-2"></span> Réinitialiser le mot de passe
          </button>
          <button class="btn btn-falcon-default btn-sm">
            <span class="fas fa-user-cog me-2"></span> Modifier les permissions
          </button>
          <button class="btn btn-falcon-default btn-sm">
            <span class="fas fa-bell me-2"></span> Gérer les notifications
          </button>
        </div>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Activité récente</h5>
      </div>
      <div class="card-body bg-body-tertiary border-top">
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="fw-semi-bold">Connexions ce mois</span>
            <span class="text-primary">24</span>
          </div>
          <div class="progress mt-1" style="height: 5px;">
            <div class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0"
              aria-valuemax="100"></div>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="fw-semi-bold">Tâches complétées</span>
            <span class="text-primary">18/24</span>
          </div>
          <div class="progress mt-1" style="height: 5px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75"
              aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <span class="fw-semi-bold">Fichiers partagés</span>
            <span class="text-primary">7</span>
          </div>
          <div class="progress mt-1" style="height: 5px;">
            <div class="progress-bar bg-info" role="progressbar" style="width: 35%;" aria-valuenow="35"
              aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact -->
    <!-- <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Coordonnées</h5>
      </div>
      <div class="card-body bg-body-tertiary border-top">
        <div class="d-flex align-items-start mb-3">
          <span class="fas fa-map-marker-alt text-primary me-2 mt-1"></span>
          <div>
            <p class="fw-semi-bold mb-0">Adresse</p>
            <p class="mb-0">8962 Lafayette St.<br>Oswego, NY 13126</p>
          </div>
        </div>
        <div class="d-flex align-items-start mb-3">
          <span class="fas fa-phone text-primary me-2 mt-1"></span>
          <div>
            <p class="fw-semi-bold mb-0">Téléphone</p>
            <p class="mb-0"><a href="tel:+12025550110">+1-202-555-0110</a></p>
          </div>
        </div>
        <div class="d-flex align-items-start">
          <span class="fas fa-envelope text-primary me-2 mt-1"></span>
          <div>
            <p class="fw-semi-bold mb-0">Email</p>
            <p class="mb-0"><a href="mailto:tony@gmail.com">tonyrobbins@gmail.com</a></p>
          </div>
        </div>
      </div>
    </div> -->
  </div>
</div>

<?php
// Récupérer les logs de l'utilisateur
$logs_utilisateur = admin_rechercher_logs(['user_id' => $utilisateur['user_id']], 1, 10);
?>
<div class="card mt-4">
  <div class="card-header">
    <h5 class="mb-0">Sessions actives</h5>
  </div>
  <div class="card-body p-0">
    <?php if (!empty($utilisateur['sessions_actives'])): ?>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead class="bg-200">
            <tr>
              <th class="text-900">IP</th>
              <th class="text-900">Navigateur</th>
              <th class="text-900">Dernière activité</th>
              <th class="text-900">Statut</th>
              <th class="text-900">Expiration</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($utilisateur['sessions_actives'] as $session): ?>
              <tr>
                <td><?= e($session['ip_address']) ?></td>
                <td><?= e($session['navigateur']) ?></td>
                <td><?= e($session['derniere_activite']) ?></td>
                <td>
                  <span class="badge bg-<?= $session['actif'] ? 'success' : 'warning' ?>">
                    <?= $session['actif'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td><?= e($session['expires_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="p-3 mb-0">Aucune session active trouvée pour cet utilisateur.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Journal d'activité -->
  <div class="card mt-4">
  <div class="card-header">
    <h5 class="mb-0">Journal d'activité récente</h5>
  </div>
  <div class="card-body p-0">
    <?php if (!empty($logs_utilisateur['logs'])): ?>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead class="bg-200">
            <tr>
              <th class="text-900">Date</th>
              <th class="text-900">Action</th>
              <th class="text-900">Détails</th>
              <th class="text-900">IP</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs_utilisateur['logs'] as $log): ?>
              <tr>
                <td><?= format_date($log['date_action']) ?></td>
                <td><?= e($log['categorie']) ?></td>
                <td><?= e($log['action']) ?></td>
                <td><?= e($log['ip_address']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="p-3 mb-0">Aucune activité récente trouvée pour cet utilisateur.</p>
    <?php endif; ?>
  </div>
  <?php if (!empty($logs_utilisateur['logs'])): ?>
    <div class="card-footer bg-body-tertiary text-end">
      <a class="btn btn-falcon-default btn-sm" href="<?= url('administration/logs') ?>?user_id=<?= $utilisateur['user_id'] ?>">
        <span class="fas fa-list fs-11 me-1"></span> Voir tout l'historique
      </a>
    </div>
  <?php endif; ?>
</div> 