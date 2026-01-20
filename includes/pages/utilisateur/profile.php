<?php
// includes/pages/profile.php

// Vérifier que l'utilisateur est connecté
if (!is_logged_in()) {
    redirect('login');
}

$user = auth_get_current_user();

// Traiter la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = [];
    
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $response['error'] = 'Token de sécurité invalide';
        echo json_encode($response);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'update_profile':
            $data = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'adresse' => trim($_POST['adresse'] ?? ''),
                'date_naissance' => $_POST['date_naissance'] ?? null,
                'genre' => $_POST['genre'] ?? ''
            ];
            
            $result = update_user_profile($user['id'], $data);
            echo json_encode($result);
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            $result = change_user_password($user['id'], $current_password, $new_password, $confirm_password);
            echo json_encode($result);
            break;
            
        case 'upload_photo':
            $result = upload_profile_photo($user['id']);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
    }
    exit;
}

// Récupérer les statistiques réelles
$stats = get_user_statistics($user['id']);
?>

<div class="row g-3">
    <div class="col-xxl-4">
        <!-- Carte de profil -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Profil Utilisateur</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="avatar avatar-4xl position-relative">
                        <img class="rounded-circle border" 
                             src="<?= get_profile_photo_url($user['photo']) ?>" 
                             alt="<?= e($user['nom_complet']) ?>" 
                             width="120"
                             id="profilePhoto"
                             onerror="this.src='<?= IMAGES_URL ?>icons/user-avatar.png'">
                        <button class="btn btn-sm btn-falcon-primary position-absolute bottom-0 end-0 rounded-circle" 
                                data-bs-toggle="tooltip" 
                                title="Changer la photo"
                                onclick="openPhotoUpload()">
                            <span class="fas fa-camera"></span>
                        </button>
                    </div>
                </div>
                <h4 class="mb-1"><?= e($user['nom_complet']) ?></h4>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-primary"><?= e($user['role']) ?></span>
                    <span class="badge bg-<?= $user['statut'] === 'actif' ? 'success' : 'warning' ?>">
                        <?= e($user['statut']) ?>
                    </span>
                </div>
                <p class="fs-10 mb-0">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Membre depuis: <?= format_date($user['date_creation']) ?>
                </p>
                <p class="fs-10 mb-0">
                    <i class="fas fa-clock me-1"></i>
                    Dernière connexion: <?= $user['dernier_login'] ? format_date($user['dernier_login'], 'd/m/Y H:i') : 'Jamais' ?>
                </p>
            </div>
        </div>

        <!-- Informations de contact -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Informations de Contact</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-800 mb-2">
                        <i class="fas fa-envelope me-2 text-600"></i>Email
                    </h6>
                    <p class="mb-0">
                        <a href="mailto:<?= e($user['email']) ?>">
                            <?= e($user['email']) ?>
                        </a>
                    </p>
                </div>
                <?php if ($user['telephone']): ?>
                <div class="mb-3">
                    <h6 class="text-800 mb-2">
                        <i class="fas fa-phone me-2 text-600"></i>Téléphone
                    </h6>
                    <p class="mb-0">
                        <a href="tel:<?= e($user['telephone']) ?>">
                            <?= e($user['telephone']) ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Statistiques d'activité -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded-2 p-3">
                            <h3 class="text-primary mb-0"><?= $stats['eleves_count'] ?></h3>
                            <p class="fs-10 mb-0">Élèves gérés</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded-2 p-3">
                            <h3 class="text-success mb-0"><?= $stats['classes_count'] ?></h3>
                            <p class="fs-10 mb-0">Classes</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-2 p-3">
                            <h3 class="text-warning mb-0"><?= $stats['professeurs_count'] ?></h3>
                            <p class="fs-10 mb-0">Professeurs</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-2 p-3">
                            <h3 class="text-info mb-0"><?= $stats['actions_count'] ?></h3>
                            <p class="fs-10 mb-0">Actions/mois</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-8">
        <!-- Informations personnelles -->
        <div class="card mb-3">
            <div class="card-header">
                <div class="row flex-between-center">
                    <div class="col-auto">
                        <h5 class="mb-0">Informations Personnelles</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-falcon-primary btn-sm" type="button" 
                                data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <span class="fas fa-edit me-1"></span>Modifier
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-800 mb-2">Nom complet</h6>
                            <p class="mb-0"><?= e($user['nom_complet']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-800 mb-2">Identifiant</h6>
                            <p class="mb-0"><?= e($user['identifiant']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-800 mb-2">Email</h6>
                            <p class="mb-0"><?= e($user['email']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-800 mb-2">Rôle</h6>
                            <p class="mb-0">
                                <span class="badge bg-primary"><?= e($user['role']) ?></span>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Historique d'activité récente -->
        <!-- <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Activité Récente</h5>
            </div>
            <div class="card-body p-0">
                <div class="scrollbar" style="max-height: 400px;">
                    <div class="list-group list-group-flush" id="activityList">
                        <div class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <span class="ms-2">Chargement des activités...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Changer le mot de passe -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Sécurité</h5>
            </div>
            <div class="card-body">
                <form id="changePasswordForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label" for="currentPassword">Mot de passe actuel</label>
                        <div class="input-group">
                            <input class="form-control" id="currentPassword" name="current_password" type="password" required />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="newPassword">Nouveau mot de passe</label>
                        <div class="input-group">
                            <input class="form-control" id="newPassword" name="new_password" type="password" required />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            <div id="passwordStrength" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirmPassword">Confirmer le nouveau mot de passe</label>
                        <div class="input-group">
                            <input class="form-control" id="confirmPassword" name="confirm_password" type="password" required />
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text" id="passwordMatch"></div>
                    </div>
                    <button class="btn btn-falcon-primary" type="submit" id="submitPassword">
                        <span class="fas fa-key me-1"></span>Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour modifier le profil -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le profil</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="profileForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editPrenom">Prénom *</label>
                            <input class="form-control" id="editPrenom" name="prenom" 
                                   type="text" value="<?= e($user['prenom']) ?>" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editNom">Nom *</label>
                            <input class="form-control" id="editNom" name="nom" 
                                   type="text" value="<?= e($user['nom']) ?>" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editEmail">Email *</label>
                            <input class="form-control" id="editEmail" name="email" 
                                   type="email" value="<?= e($user['email']) ?>" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editTelephone">Téléphone</label>
                            <input class="form-control" id="editTelephone" name="telephone" 
                                   type="tel" value="<?= e($user['telephone'] ?? '') ?>" />
                        </div>
                    </div>
                
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="updateProfileBtn">
                    <span class="fas fa-save me-1"></span>Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour uploader une photo -->
<div class="modal fade" id="photoUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Changer la photo de profil</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="photoUploadForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="upload_photo">
                    
                    <div class="text-center mb-4">
                        <div class="avatar avatar-5xl mb-3">
                            <img id="photoPreview" 
                                 src="<?= get_profile_photo_url($user['photo']) ?>" 
                                 alt="Prévisualisation"
                                 class="rounded-circle border"
                                 width="150"
                                 onerror="this.src='<?= IMAGES_URL ?>icons/user-avatar.png'">
                        </div>
                        <p class="text-muted mb-0 fs-9">
                            Taille recommandée: 400x400 pixels<br>
                            Formats: JPG, PNG, GIF (max 5MB)
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="profilePhotoInput">Sélectionner une image</label>
                        <input class="form-control" id="profilePhotoInput" name="photo" type="file" accept="image/*" required>
                    </div>
                    
                    <div class="progress d-none" id="uploadProgress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="uploadPhotoBtn">
                    <span class="fas fa-upload me-1"></span>Télécharger
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= LIBS_URL ?>jquery/jquery.min.js"></script>
<script>
// Fonctions utilitaires
function showAlert(message, type = 'success', duration = 5000) {
    const alert = $(`
        <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" 
             role="alert" style="z-index: 1060; min-width: 300px;">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(alert);
    
    if (duration > 0) {
        setTimeout(() => alert.alert('close'), duration);
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}

// Vérification de la force du mot de passe
function checkPasswordStrength(password) {
    let strength = 0;
    const messages = [];
    
    if (password.length >= 8) strength++;
    else messages.push('Minimum 8 caractères');
    
    if (/[a-z]/.test(password)) strength++;
    else messages.push('Au moins une lettre minuscule');
    
    if (/[A-Z]/.test(password)) strength++;
    else messages.push('Au moins une lettre majuscule');
    
    if (/[0-9]/.test(password)) strength++;
    else messages.push('Au moins un chiffre');
    
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    else messages.push('Au moins un caractère spécial');
    
    let level, color;
    if (strength >= 4) {
        level = 'Fort';
        color = 'success';
    } else if (strength >= 3) {
        level = 'Moyen';
        color = 'warning';
    } else {
        level = 'Faible';
        color = 'danger';
    }
    
    return { strength, level, color, messages };
}
// Charger les activités récentes
// function loadRecentActivities() {
//     $.ajax({
//         url: '<?= url('profile') ?>',
//         method: 'POST',
//         data: {
//             action: 'get_activities',
//             csrf_token: '<?= generate_csrf_token() ?>'
//         },
//         success: function(response) {
//             const activities = response.data || [];
//             const activityList = $('#activityList');
            
//             if (activities.length === 0) {
//                 activityList.html(`
//                     <div class="text-center py-4">
//                         <i class="fas fa-history text-muted fa-2x mb-2"></i>
//                         <p class="text-muted mb-0">Aucune activité récente</p>
//                     </div>
//                 `);
//                 return;
//             }
            
//             let html = '';
//             activities.forEach(activity => {
//                 const iconMap = {
//                     'login': 'fa-sign-in-alt text-success',
//                     'logout': 'fa-sign-out-alt text-danger',
//                     'create': 'fa-plus-circle text-primary',
//                     'update': 'fa-edit text-warning',
//                     'delete': 'fa-trash-alt text-danger',
//                     'view': 'fa-eye text-info'
//                 };
                
//                 const icon = iconMap[activity.type] || 'fa-circle text-secondary';
                
//                 html += `
//                     <div class="list-group-item py-3">
//                         <div class="d-flex align-items-center">
//                             <div class="icon-circle icon-circle-${activity.type} me-3">
//                                 <span class="fas ${icon}"></span>
//                             </div>
//                             <div class="flex-1">
//                                 <h6 class="mb-1">${activity.title}</h6>
//                                 <p class="mb-0 fs-10">${activity.description}</p>
//                                 <small class="text-600">${activity.time_ago}</small>
//                             </div>
//                         </div>
//                     </div>
//                 `;
//             });
            
//             activityList.html(html);
//         },
//         error: function() {
//             $('#activityList').html(`
//                 <div class="text-center py-4">
//                     <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
//                     <p class="text-muted mb-0">Erreur lors du chargement des activités</p>
//                 </div>
//             `);
//         }
//     });
// }

// Événements DOM
$(document).ready(function() {
    // Charger les activités
    // loadRecentActivities();

    // Prévisualisation photo dans le modal
    $('#profilePhotoInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                showAlert('Le fichier est trop volumineux (max 5MB)', 'danger');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photoPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Upload de photo
    $('#uploadPhotoBtn').click(function() {
        const formData = new FormData($('#photoUploadForm')[0]);
        
        $('#uploadProgress').removeClass('d-none');
        $(this).prop('disabled', true).html('<span class="fas fa-spinner fa-spin me-1"></span>Téléchargement...');
        
        $.ajax({
            url: '<?= url('profile') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        $('#uploadProgress .progress-bar').css('width', percent + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Photo mise à jour avec succès');
                    $('#profilePhoto').attr('src', response.photo_url + '?t=' + new Date().getTime());
                    $('#photoUploadModal').modal('hide');
                } else {
                    showAlert(response.error || 'Erreur lors du téléchargement', 'danger');
                }
            },
            error: function() {
                showAlert('Erreur de connexion au serveur', 'danger');
            },
            complete: function() {
                $('#uploadProgress').addClass('d-none').find('.progress-bar').css('width', '0%');
                $('#uploadPhotoBtn').prop('disabled', false).html('<span class="fas fa-upload me-1"></span>Télécharger');
            }
        });
    });
    
    // Mise à jour du profil
    $('#updateProfileBtn').click(function() {
        const form = $('#profileForm');
        const formData = form.serialize();
        
        $(this).prop('disabled', true).html('<span class="fas fa-spinner fa-spin me-1"></span>Enregistrement...');
        
        $.ajax({
            url: '<?= url('profile') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('Profil mis à jour avec succès');
                    $('#editProfileModal').modal('hide');
                    
                    // Recharger la page pour voir les changements
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = response.error || 'Erreur lors de la mise à jour';
                    if (response.errors) {
                        errorMsg = Object.values(response.errors).join('<br>');
                    }
                    showAlert(errorMsg, 'danger');
                }
            },
            error: function() {
                showAlert('Erreur de connexion au serveur', 'danger');
            },
            complete: function() {
                $('#updateProfileBtn').prop('disabled', false).html('<span class="fas fa-save me-1"></span>Enregistrer');
            }
        });
    });
    
    // Changer le mot de passe
    $('#changePasswordForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $('#submitPassword');
        const formData = $(this).serialize();
        
        // Validation
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();
        
        if (newPassword !== confirmPassword) {
            showAlert('Les mots de passe ne correspondent pas', 'danger');
            return;
        }
        
        const strength = checkPasswordStrength(newPassword);
        if (strength.strength < 3) {
            showAlert('Le mot de passe est trop faible. ' + strength.messages.join(', '), 'warning');
            return;
        }
        
        submitBtn.prop('disabled', true).html('<span class="fas fa-spinner fa-spin me-1"></span>En cours...');
        
        $.ajax({
            url: '<?= url('profile') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                   showAlert('Mot de passe changé avec succès' , "success");
                    $('#changePasswordForm')[0].reset();
                    $('#passwordStrength, #passwordMatch').empty();
                    
                } else {
                    showAlert(response.error || 'Erreur lors du changement' , 'danger');
                }
            },
            error: function() {
                showAlert('Erreur de connexion au serveur', 'danger');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<span class="fas fa-key me-1"></span>Changer le mot de passe');
            }
        });
    });
    
    // Vérification en temps réel du mot de passe
    $('#newPassword').on('input', function() {
        const password = $(this).val();
        if (!password) {
            $('#passwordStrength').empty();
            return;
        }
        
        const strength = checkPasswordStrength(password);
        $('#passwordStrength').html(`
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <small>Force: <span class="text-${strength.color} fw-bold">${strength.level}</span></small>
                </div>
                <div class="d-flex gap-1">
                    ${[1,2,3,4,5].map(i => `
                        <div class="bg-${i <= strength.strength ? strength.color : 'secondary'} 
                             rounded" style="width: 10px; height: 5px;"></div>
                    `).join('')}
                </div>
            </div>
        `);
    });
    
    // Vérification de la correspondance des mots de passe
    $('#confirmPassword').on('input', function() {
        const newPassword = $('#newPassword').val();
        const confirmPassword = $(this).val();
        
        if (!newPassword || !confirmPassword) {
            $('#passwordMatch').empty();
            return;
        }
        
        if (newPassword === confirmPassword) {
            $('#passwordMatch').html('<small class="text-success"><i class="fas fa-check me-1"></i>Les mots de passe correspondent</small>');
        } else {
            $('#passwordMatch').html('<small class="text-danger"><i class="fas fa-times me-1"></i>Les mots de passe ne correspondent pas</small>');
        }
    });
});

// Fonctions globales
function openPhotoUpload() {
    $('#photoUploadModal').modal('show');
}

</script>