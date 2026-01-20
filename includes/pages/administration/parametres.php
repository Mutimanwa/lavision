<?php
// require_once __DIR__ . '/../../modules/administration/utilisateurs.php';
require_once __DIR__ . "/../../modules/administration/parametres.php";

// Vérifier les permissions
if(!is_logged_in() || !has_role(ROLE_SUPERADMIN)){
    redirect("login");
}

// Traitement des formulaires
$message = '';
$message_type = '';

// Traitement des formulaires généraux
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    $form_type = $_POST['form_type'];
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Vérifier le token CSRF
    if (!verify_csrf_token($csrf_token)) {
        $message = 'Token de sécurité invalide';
        $message_type = 'error';
    } else {
        switch ($form_type) {
            case 'general':
                // Paramètres généraux
                $success = true;
                $errors = [];
                
                $params = [
                    'nom_ecole' => [
                        'value' => trim($_POST['nom_ecole'] ?? ''),
                        'type' => PARAM_TYPE_STRING
                    ],
                    'adresse_ecole' => [
                        'value' => trim($_POST['adresse_ecole'] ?? ''),
                        'type' => PARAM_TYPE_STRING
                    ],
                    'telephone_ecole' => [
                        'value' => trim($_POST['telephone_ecole'] ?? ''),
                        'type' => PARAM_TYPE_STRING
                    ],
                    'email_ecole' => [
                        'value' => trim($_POST['email_ecole'] ?? ''),
                        'type' => PARAM_TYPE_STRING
                    ],
                    'devise_ecole' => [
                        'value' => trim($_POST['devise_ecole'] ?? ''),
                        'type' => PARAM_TYPE_STRING
                    ],
                    'annee_courante' => [
                        'value' => intval($_POST['annee_courante'] ?? 1),
                        'type' => PARAM_TYPE_INTEGER
                    ]
                ];
                
                foreach ($params as $cle => $data) {
                    if (!config_set($cle, $data['value'], $data['type'], PARAM_CATEGORIE_GENERAL)) {
                        $success = false;
                        $errors[] = "Erreur lors de la sauvegarde de {$cle}";
                    }
                }
                
                if ($success) {
                    $message = 'Paramètres généraux enregistrés avec succès';
                    $message_type = 'success';
                    log_action('Paramètres généraux modifiés', $params, 'system');
                } else {
                    $message = 'Erreurs lors de l\'enregistrement: ' . implode(', ', $errors);
                    $message_type = 'error';
                }

                // reinitialiser le form

                break;
                
            case 'academique':
                // Paramètres académiques
                $success = true;
                
                $params = [
                    'seuil_reussite' => [
                        'value' => floatval($_POST['seuil_reussite'] ?? 10),
                        'type' => PARAM_TYPE_FLOAT
                    ],
                    'limite_absence' => [
                        'value' => intval($_POST['limite_absence'] ?? 10),
                        'type' => PARAM_TYPE_INTEGER
                    ],
                    'heure_debut_cours' => [
                        'value' => $_POST['heure_debut_cours'] ?? '08:00',
                        'type' => PARAM_TYPE_STRING
                    ],
                    'heure_fin_cours' => [
                        'value' => $_POST['heure_fin_cours'] ?? '16:00',
                        'type' => PARAM_TYPE_STRING
                    ]
                ];
                
                foreach ($params as $cle => $data) {
                    if (!config_set($cle, $data['value'], $data['type'], PARAM_CATEGORIE_ACADEMIQUE)) {
                        $success = false;
                    }
                }
                
                if ($success) {
                    $message = 'Paramètres académiques enregistrés avec succès';
                    $message_type = 'success';
                    log_action('Paramètres académiques modifiés', $params, 'system');
                } else {
                    $message = 'Erreur lors de l\'enregistrement des paramètres académiques';
                    $message_type = 'error';
                }
                break;
                
            case 'financier':
                // Paramètres financiers
                $success = true;
                
                $params = [
                    'frais_inscription' => [
                        'value' => intval($_POST['frais_inscription'] ?? 50000),
                        'type' => PARAM_TYPE_INTEGER
                    ],
                    'frais_scolarite' => [
                        'value' => intval($_POST['frais_scolarite'] ?? 300000),
                        'type' => PARAM_TYPE_INTEGER
                    ],
                    'delai_paiement' => [
                        'value' => intval($_POST['delai_paiement'] ?? 15),
                        'type' => PARAM_TYPE_INTEGER
                    ],
                    'taux_penalite' => [
                        'value' => floatval($_POST['taux_penalite'] ?? 5),
                        'type' => PARAM_TYPE_FLOAT
                    ]
                ];
                
                foreach ($params as $cle => $data) {
                    if (!config_set($cle, $data['value'], $data['type'], PARAM_CATEGORIE_FINANCIER)) {
                        $success = false;
                    }
                }
                
                if ($success) {
                    $message = 'Paramètres financiers enregistrés avec succès';
                    $message_type = 'success';
                    log_action('Paramètres financiers modifiés', $params, 'system');
                } else {
                    $message = 'Erreur lors de l\'enregistrement des paramètres financiers';
                    $message_type = 'error';
                }
                break;
                
            case 'systeme':
                // Paramètres système
                $success = true;
                
                $params = [
                    'session_timeout' => [
                        'value' => intval($_POST['session_timeout'] ?? 30),
                        'type' => PARAM_TYPE_INTEGER
                    ],
                    'max_login_attempts' => [
                        'value' => intval($_POST['max_login_attempts'] ?? 3),
                        'type' => PARAM_TYPE_INTEGER
                    ],
                    'backup_auto' => [
                        'value' => isset($_POST['backup_auto']) ? true : false,
                        'type' => PARAM_TYPE_BOOLEAN
                    ],
                    'backup_frequency' => [
                        'value' => $_POST['backup_frequency'] ?? 'daily',
                        'type' => PARAM_TYPE_STRING
                    ],
                    'log_errors' => [
                        'value' => isset($_POST['log_errors']) ? true : false,
                        'type' => PARAM_TYPE_BOOLEAN
                    ],
                    'log_login' => [
                        'value' => isset($_POST['log_login']) ? true : false,
                        'type' => PARAM_TYPE_BOOLEAN
                    ],
                    'log_actions' => [
                        'value' => isset($_POST['log_actions']) ? true : false,
                        'type' => PARAM_TYPE_BOOLEAN
                    ]
                ];
                
                foreach ($params as $cle => $data) {
                    if (!config_set($cle, $data['value'], $data['type'], PARAM_CATEGORIE_SYSTEME)) {
                        $success = false;
                    }
                }
                
                if ($success) {
                    $message = 'Paramètres système enregistrés avec succès';
                    $message_type = 'success';
                    log_action('Paramètres système modifiés', [], 'system');
                } else {
                    $message = 'Erreur lors de l\'enregistrement des paramètres système';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Récupérer les valeurs actuelles pour pré-remplir les formulaires
$parametres_generaux = config_get_all_by_category(PARAM_CATEGORIE_GENERAL);
$parametres_academiques = config_get_all_by_category(PARAM_CATEGORIE_ACADEMIQUE);
$parametres_financiers = config_get_all_by_category(PARAM_CATEGORIE_FINANCIER);
$parametres_systeme = config_get_all_by_category(PARAM_CATEGORIE_SYSTEME);

// Convertir en tableau associatif pour faciliter l'accès
$general_values = [];
foreach ($parametres_generaux as $param) {
    $general_values[$param['cle']] = $param['valeur_convertie'];
}

$academique_values = [];
foreach ($parametres_academiques as $param) {
    $academique_values[$param['cle']] = $param['valeur_convertie'];
}

$financier_values = [];
foreach ($parametres_financiers as $param) {
    $financier_values[$param['cle']] = $param['valeur_convertie'];
}

$systeme_values = [];
foreach ($parametres_systeme as $param) {
    $systeme_values[$param['cle']] = $param['valeur_convertie'];
}

// Récupérer les sauvegardes récentes
$recent_backups = [];
try {
    $sql = "SELECT * FROM backup_logs 
            WHERE statut = 'success' 
            ORDER BY date_execution DESC 
            LIMIT 3";
    $recent_backups = db_query($sql);
} catch (Exception $e) {
    // Table peut ne pas exister encore
}
// Traitement de backup



?>
<!-- ============================================= -->
<!-- PAGE DE PARAMÈTRES DU SYSTÈME -->
<!-- ============================================= -->
<div class="row">
  <div class="col-12">
    <div class="card mb-3">
      <div class="card-header position-relative">
        <h4 class="mb-0">Configuration du Système Scolaire</h4>
        <p class="text-700 mb-0">Gérez les paramètres généraux de l'application</p>
      </div>
    </div>
  </div>
</div>

<?php if ($message): ?>
<div class="row">
  <div class="col-12">
    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
      <?php echo e($message); ?>
      <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-0">
  <div class="col-lg-8 pe-lg-2">
    <!-- Section: Paramètres Généraux -->
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Paramètres Généraux</h5>
      </div>
      <div class="card-body bg-body-tertiary">
        <form class="row g-3" method="POST" action="">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="form_type" value="general">
          
          <div class="col-lg-6">
            <label class="form-label" for="nom_ecole">Nom de l'École</label>
            <input class="form-control" id="nom_ecole" name="nom_ecole" type="text" 
                   value="<?php echo e($general_values['nom_ecole'] ?? "École Secondaire d'Excellence"); ?>" required />
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="adresse_ecole">Adresse</label>
            <input class="form-control" id="adresse_ecole" name="adresse_ecole" type="text" 
                   value="<?php echo e($general_values['adresse_ecole'] ?? "123 Avenue de l'Éducation, Kinshasa"); ?>" required />
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="telephone_ecole">Téléphone</label>
            <input class="form-control" id="telephone_ecole" name="telephone_ecole" type="text" 
                   value="<?php echo e($general_values['telephone_ecole'] ?? "+243 81 234 5678"); ?>" required />
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="email_ecole">Email</label>
            <input class="form-control" id="email_ecole" name="email_ecole" type="email" 
                   value="<?php echo e($general_values['email_ecole'] ?? "contact@ecole-excellence.cd"); ?>" required />
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="devise_ecole">Devise</label>
            <input class="form-control" id="devise_ecole" name="devise_ecole" type="text" 
                   value="<?php echo e($general_values['devise_ecole'] ?? "Savoir, Excellence, Discipline"); ?>" />
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="annee_courante">Année Scolaire Courante</label>
            <select class="form-select" id="annee_courante" name="annee_courante">
              <?php 
              $annees = [
                '1' => '2024-2025',
                '2' => '2025-2026'
              ];
              $current_year = $general_values['annee_courante'] ?? '1';
              foreach ($annees as $id => $label): ?>
              <option value="<?php echo $id; ?>" <?php echo ($id == $current_year) ? 'selected' : ''; ?>>
                <?php echo e($label); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Enregistrer les Modifications</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Section: Paramètres Académiques -->
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Paramètres Académiques</h5>
      </div>
      <div class="card-body bg-body-tertiary">
        <form class="row g-3" method="POST" action="">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="form_type" value="academique">
          
          <div class="col-lg-6">
            <label class="form-label" for="seuil_reussite">Seuil de Réussite (sur 20)</label>
            <div class="input-group">
              <input class="form-control" id="seuil_reussite" name="seuil_reussite" type="number" min="0" max="20" step="0.5" 
                     value="<?php echo e($academique_values['seuil_reussite'] ?? 10); ?>" />
              <span class="input-group-text">/20</span>
            </div>
            <small class="text-700">Note minimale pour qu'un élève réussisse</small>
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="limite_absence">Limite d'Absences Non Justifiées</label>
            <input class="form-control" id="limite_absence" name="limite_absence" type="number" min="0" 
                   value="<?php echo e($academique_values['limite_absence'] ?? 10); ?>" />
            <small class="text-700">Nombre maximum d'absences non justifiées autorisées</small>
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="heure_debut_cours">Heure de Début des Cours</label>
            <input class="form-control" id="heure_debut_cours" name="heure_debut_cours" type="time" 
                   value="<?php echo e($academique_values['heure_debut_cours'] ?? '08:00'); ?>" />
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="heure_fin_cours">Heure de Fin des Cours</label>
            <input class="form-control" id="heure_fin_cours" name="heure_fin_cours" type="time" 
                   value="<?php echo e($academique_values['heure_fin_cours'] ?? '16:00'); ?>" />
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Enregistrer les Modifications</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Section: Paramètres Financiers -->
    <div class="card mb-3">
      <div class="card-header">
        <h5 class="mb-0">Paramètres Financiers</h5>
      </div>
      <div class="card-body bg-body-tertiary">
        <form class="row g-3" method="POST" action="">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="form_type" value="financier">
          
          <div class="col-lg-6">
            <label class="form-label" for="frais_inscription">Frais d'Inscription (FCFA)</label>
            <div class="input-group">
              <input class="form-control" id="frais_inscription" name="frais_inscription" type="number" min="0" 
                     value="<?php echo e($financier_values['frais_inscription'] ?? 50000); ?>" />
              <span class="input-group-text">FCFA</span>
            </div>
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="frais_scolarite">Frais de Scolarité/Trimestre (FCFA)</label>
            <div class="input-group">
              <input class="form-control" id="frais_scolarite" name="frais_scolarite" type="number" min="0" 
                     value="<?php echo e($financier_values['frais_scolarite'] ?? 300000); ?>" />
              <span class="input-group-text">FCFA</span>
            </div>
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="delai_paiement">Délai de Paiement (jours)</label>
            <div class="input-group">
              <input class="form-control" id="delai_paiement" name="delai_paiement" type="number" min="0" 
                     value="<?php echo e($financier_values['delai_paiement'] ?? 15); ?>" />
              <span class="input-group-text">jours</span>
            </div>
            <small class="text-700">Délai accordé après l'échéance</small>
          </div>
          <div class="col-lg-6">
            <label class="form-label" for="taux_penalite">Taux de Pénalité de Retard (%)</label>
            <div class="input-group">
              <input class="form-control" id="taux_penalite" name="taux_penalite" type="number" min="0" max="100" step="0.1" 
                     value="<?php echo e($financier_values['taux_penalite'] ?? 5); ?>" />
              <span class="input-group-text">%</span>
            </div>
            <small class="text-700">Pénalité mensuelle pour retard de paiement</small>
          </div>
          <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Enregistrer les Modifications</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4 ps-lg-2">
    <div class="sticky-sidebar">
      <!-- Section: Paramètres Système -->
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="mb-0">Paramètres Système</h5>
        </div>
        <div class="card-body bg-body-tertiary">
          <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="form_type" value="systeme">
            
            <h6 class="fw-bold">Sauvegarde Automatique</h6>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="backup_auto" name="backup_auto" 
                     <?php echo ($systeme_values['backup_auto'] ?? true) ? 'checked' : ''; ?> />
              <label class="form-check-label" for="backup_auto">Activer la sauvegarde automatique</label>
            </div>
            
            <div class="mb-3">
              <label class="form-label" for="backup_frequency">Fréquence de Sauvegarde</label>
              <select class="form-select" id="backup_frequency" name="backup_frequency">
                <?php 
                $frequencies = [
                  'daily' => 'Quotidienne',
                  'weekly' => 'Hebdomadaire',
                  'monthly' => 'Mensuelle'
                ];
                $current_freq = $systeme_values['backup_frequency'] ?? 'daily';
                foreach ($frequencies as $value => $label): ?>
                <option value="<?php echo $value; ?>" <?php echo ($value == $current_freq) ? 'selected' : ''; ?>>
                  <?php echo e($label); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="border-dashed-bottom my-3"></div>
            
            <h6 class="fw-bold">Journalisation</h6>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="log_errors" name="log_errors"
                     <?php echo ($systeme_values['log_errors'] ?? true) ? 'checked' : ''; ?> />
              <label class="form-check-label" for="log_errors">Journaliser les erreurs</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="log_login" name="log_login"
                     <?php echo ($systeme_values['log_login'] ?? true) ? 'checked' : ''; ?> />
              <label class="form-check-label" for="log_login">Journaliser les connexions</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="log_actions" name="log_actions"
                     <?php echo ($systeme_values['log_actions'] ?? true) ? 'checked' : ''; ?> />
              <label class="form-check-label" for="log_actions">Journaliser les actions importantes</label>
            </div>
            
            <div class="border-dashed-bottom my-3"></div>
            
            <h6 class="fw-bold">Sécurité</h6>
            <div class="mb-3">
              <label class="form-label" for="session_timeout">Durée de Session (minutes)</label>
              <input class="form-control" id="session_timeout" name="session_timeout" type="number" min="5" max="480" 
                     value="<?php echo e($systeme_values['session_timeout'] ?? 30); ?>" />
            </div>
            <div class="mb-3">
              <label class="form-label" for="max_login_attempts">Tentatives de Connexion Max</label>
              <input class="form-control" id="max_login_attempts" name="max_login_attempts" type="number" min="1" max="10" 
                     value="<?php echo e($systeme_values['max_login_attempts'] ?? 3); ?>" />
            </div>
            
            <div class="d-grid gap-2">
              <button class="btn btn-falcon-primary" type="submit">Appliquer les Paramètres Système</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Section: Maintenance -->
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="mb-0">Maintenance</h5>
        </div>
        <div class="card-body bg-body-tertiary">
          <div class="d-grid gap-2 mb-3">
            <button class="btn btn-falcon-info" type="button" id="btn-clear-cache">
              <span class="fas fa-broom me-2"></span>Vider le Cache
            </button>
            <button class="btn btn-falcon-info" type="button" id="btn-optimize-db">
              <span class="fas fa-database me-2"></span>Optimiser la Base de Données
            </button>
            <button class="btn btn-falcon-info" type="button" id="btn-backup-now">
              <span class="fas fa-save me-2"></span>Sauvegarde Manuelle
            </button>
          </div>
          
          <div class="border-dashed-bottom my-3"></div>
          
          <h6 class="fw-bold">Dernières Sauvegardes</h6>
          <div class="list-group list-group-flush">
            <?php if (!empty($recent_backups)): ?>
              <?php foreach ($recent_backups as $backup): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                  <div>
                    <span class="fas fa-check-circle text-success me-2"></span>
                    <small><?php echo e($backup['fichier'] ?? 'backup_' . date('Ymd_His', strtotime($backup['date_execution'])) . '.sql'); ?></small>
                  </div>
                  <small class="text-700"><?php echo time_ago($backup['date_execution']); ?></small>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="list-group-item px-0">
                <small class="text-700">Aucune sauvegarde trouvée</small>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Section: Zone Critique -->
      <div class="card">
        <div class="card-header bg-danger-soft">
          <h5 class="mb-0 text-danger">Zone Critique</h5>
        </div>
        <div class="card-body bg-body-tertiary">
          <h6 class="fs-9 text-danger">Réinitialisation des Données</h6>
          <p class="fs-10">Remet à zéro certaines données du système. Cette action ne peut pas être annulée.</p>
          
          <div class="d-grid gap-2 mb-3">
            <button class="btn btn-falcon-warning" type="button" id="btn-reset-notes">
              <span class="fas fa-eraser me-2"></span>Réinitialiser les Notes
            </button>
            <button class="btn btn-falcon-warning" type="button" id="btn-reset-attendance">
              <span class="fas fa-user-clock me-2"></span>Réinitialiser les Absences
            </button>
          </div>
          
          <div class="border-bottom border-dashed my-4"></div>
          
          <h6 class="fs-9 text-danger">Réinitialiser Tous les Paramètres</h6>
          <p class="fs-10">Restaure tous les paramètres aux valeurs par défaut. Toutes les modifications seront perdues.</p>
          
          <div class="d-grid">
            <button class="btn btn-falcon-danger" type="button" id="btn-reset-all">
              <span class="fas fa-exclamation-triangle me-2"></span>Réinitialiser Tous les Paramètres
            </button>
          </div>
          
          <div class="border-bottom border-dashed my-4"></div>
          
          <h6 class="fs-9 text-danger">Export/Import de Configuration</h6>
          <p class="fs-10">Exportez ou importez la configuration complète du système.</p>
          
          <div class="d-grid gap-2">
            <button class="btn btn-falcon-success" type="button" id="btn-export-config">
              <span class="fas fa-file-export me-2"></span>Exporter Configuration
            </button>
            <button class="btn btn-falcon-primary" type="button" id="btn-import-config">
              <span class="fas fa-file-import me-2"></span>Importer Configuration
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Script pour gérer les interactions -->
