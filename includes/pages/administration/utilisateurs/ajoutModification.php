<?php
require_once __DIR__ . '/../../../modules/administration/utilisateurs.php';

// Récupérer l'UUID depuis l'URL
$uuid = $_GET['uuid'] ?? null;

if (isset($uuid)) {
    $utilisateur = admin_get_utilisateur($uuid);
}

$error = "";
$success = "";
$erreurs = [];

// Définir les permissions par rôle
$permissions_par_role = [
    ROLE_SUPERADMIN => [
        'users' => PERM_ALL,
        'eleves' => PERM_ALL,
        'professeurs' => PERM_ALL,
        'classes' => PERM_ALL,
        'notes' => PERM_ALL,
        'paiements' => PERM_ALL,
        'settings' => PERM_ALL
    ],
    ROLE_ADMIN => [
        'users' => PERM_ALL,
        'eleves' => PERM_ALL,
        'professeurs' => PERM_ALL,
        'classes' => PERM_ALL,
        'notes' => PERM_ALL,
        'paiements' => PERM_ALL,
        'settings' => PERM_MANAGE_SETTINGS
    ],
    ROLE_SECRETAIRE => [
        'eleves' => PERM_VIEW | PERM_CREATE | PERM_EDIT,
        'professeurs' => PERM_VIEW,
        'classes' => PERM_VIEW,
        'notes' => PERM_VIEW,
        'paiements' => PERM_VIEW | PERM_CREATE | PERM_EDIT
    ],
    ROLE_GESTIONNAIRE => [
        'paiements' => PERM_ALL,
        'eleves' => PERM_VIEW
    ],
    ROLE_PROVISEUR => [
        'eleves' => PERM_ALL,
        'professeurs' => PERM_ALL,
        'classes' => PERM_ALL,
        'notes' => PERM_ALL,
        'statistiques' => PERM_ALL
    ]
];

// Définir toutes les permissions disponibles pour la validation
$all_permissions = [];
foreach ($permissions_par_role as $role_perms) {
    $all_permissions = array_merge($all_permissions, array_keys($role_perms));
}
$all_permissions = array_unique($all_permissions);

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action'])) {

    // Vérification du token csrf
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide";
        log_action("Creation/Modification Utilisateur", ["error" => $error]);
    } else {
        // Traitement du formulaire par action
        if ($_POST['action'] == "modifier") {
            $data = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'identifiant' => trim($_POST['identifiant'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'role' => trim($_POST['role'] ?? ''),
                'statut' => trim($_POST['statut'] ?? STATUS_ACTIF)
            ];

            // Gestion du mot de passe (si fourni)
            if (!empty($_POST['mot_de_passe'])) {
                $data['mot_de_passe'] = $_POST['mot_de_passe'];
            }

            // Gestion des permissions
            if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
                // Filtrer les permissions pour n'avoir que des permissions valides
                $data['permissions'] = array_intersect($_POST['permissions'], $all_permissions);
            } else {
                $data['permissions'] = [];
            }

            $result = admin_modifier_utilisateur($utilisateur['user_id'], $data);
            var_dump($result);

            if ($result['success']) {
                $success = "Utilisateur modifié avec succès";
                // Recharger les données de l'utilisateur
                $utilisateur = admin_get_utilisateur($utilisateur['uuid']);
            } else {
                $error = $result['error'] ?? "Veuillez réessayer plus tard";
                if (isset($result['erreurs'])) {
                    $erreurs = $result['erreurs'];
                    $error .= ": " . implode(', ', $result['erreurs']);
                }
            }
        } else if ($_POST['action'] == "ajout") {
            $data = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'identifiant' => trim($_POST['identifiant'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'role' => trim($_POST['role'] ?? ''),
                'statut' => trim($_POST['statut'] ?? STATUS_ACTIF),
                'mot_de_passe' => $_POST['mot_de_passe'] ?? ''
            ];

            // Gestion des permissions
            if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
                // Filtrer les permissions pour n'avoir que des permissions valides
                $data['permissions'] = array_intersect($_POST['permissions'], $all_permissions);
            } else {
                $data['permissions'] = [];
            }

            $result = admin_creer_utilisateur($data);
            if ($result['success']) {
                $success = "Utilisateur ajouté avec succès";
                // Réinitialiser le formulaire
                $_POST = [];
                $erreurs = [];
            } else {
                $error = $result['error'] ?? "Erreur d'ajout de l'utilisateur";
                if (isset($result['erreurs'])) {
                    $erreurs = $result['erreurs'];
                    $error .= ": " . implode(', ', $result['erreurs']);
                }
            }
        }
    }
}

// Fonction pour afficher les erreurs de champ
function show_field_error($field, $erreurs) {
    if (isset($erreurs[$field])) {
        return '<div class="invalid-feedback d-block">' . e($erreurs[$field]) . '</div>';
    }
    return '';
}
?>

<div class="col-xl-12 h-100">
    <div class="card mb-3">
        <div class="card-body bg-200 dark__bg-1100">
            <div class="d-flex mb-4">
                <span class="fa-stack me-2 ms-n1">
                    <i class="fas fa-circle fa-stack-2x text-300"></i>
                    <i class="fa-inverse fa-stack-1x text-primary fas fa-<?= isset($utilisateur) ? 'edit' : 'user-plus' ?>"></i>
                </span>
                <div class="col">
                    <h5 class="mb-0 text-primary position-relative">
                        <span class="pe-3">
                            <?= isset($utilisateur) ? 'Modification d\'un utilisateur' : 'Ajout d\'un utilisateur' ?>
                        </span>
                        <span class="border position-absolute top-50 translate-middle-y w-100 start-0 z-n1"></span>
                    </h5>
                    <p class="mb-0">
                        <?= isset($utilisateur)
                            ? 'Modifier les informations de l\'utilisateur dans le système'
                            : 'Ajouter facilement un utilisateur dans le système en utilisant ce formulaire'
                            ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-auto mb-3">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= e($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= e($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="card h-100 mb-5">
        <div class="card-body py-4" id="wizard-controller">
            <form method="POST" id="user-form">
                <?= csrf_field() ?>
                <?php if (isset($utilisateur)): ?>
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($utilisateur['user_id']) ?>">
                <?php endif; ?>
                <input type="hidden" name="action" value="<?= isset($utilisateur) ? 'modifier' : 'ajout' ?>">

                <div class="px-sm-3 px-md-5">
                    <h6 class="mb-3 text-primary"><i class="fas fa-user me-2"></i>Informations personnelles</h6>
                    
                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="nom">Nom <span class="text-danger">*</span></label>
                                <input class="form-control <?= isset($erreurs['nom']) ? 'is-invalid' : '' ?>" 
                                       type="text" name="nom" id="nom" required
                                    value="<?= htmlspecialchars($_POST['nom'] ?? $utilisateur['nom'] ?? '') ?>"
                                    placeholder="Nom de l'utilisateur">
                                <?= show_field_error('nom', $erreurs) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="prenom">Prénom <span class="text-danger">*</span></label>
                                <input class="form-control <?= isset($erreurs['prenom']) ? 'is-invalid' : '' ?>" 
                                       type="text" name="prenom" id="prenom" required
                                    value="<?= htmlspecialchars($_POST['prenom'] ?? $utilisateur['prenom'] ?? '') ?>"
                                    placeholder="Prénom de l'utilisateur">
                                <?= show_field_error('prenom', $erreurs) ?>
                            </div>
                        </div>
                    </div>

                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="identifiant">Identifiant <span class="text-danger">*</span></label>
                                <input class="form-control <?= isset($erreurs['identifiant']) ? 'is-invalid' : '' ?>" 
                                       type="text" name="identifiant" id="identifiant" required
                                    value="<?= htmlspecialchars($_POST['identifiant'] ?? $utilisateur['identifiant'] ?? '') ?>"
                                    placeholder="Identifiant de connexion">
                                <?= show_field_error('identifiant', $erreurs) ?>
                                <small class="text-muted">Utilisé pour se connecter</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                                <input class="form-control <?= isset($erreurs['email']) ? 'is-invalid' : '' ?>" 
                                       type="email" name="email" id="email" required
                                    value="<?= htmlspecialchars($_POST['email'] ?? $utilisateur['email'] ?? '') ?>"
                                    placeholder="exemple@domain.com">
                                <?= show_field_error('email', $erreurs) ?>
                                <small class="text-muted">Format: nom@domaine.com</small>
                            </div>
                        </div>
                    </div>

                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="telephone">Téléphone</label>
                                <input class="form-control <?= isset($erreurs['telephone']) ? 'is-invalid' : '' ?>" 
                                       type="text" name="telephone" id="telephone"
                                    value="<?= htmlspecialchars($_POST['telephone'] ?? $utilisateur['telephone'] ?? '') ?>"
                                    placeholder="+33 1 23 45 67 89">
                                <?= show_field_error('telephone', $erreurs) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="statut">Statut</label>
                                <select name="statut" id="statut" class="form-select <?= isset($erreurs['statut']) ? 'is-invalid' : '' ?>">
                                    <option value="actif" <?= ((isset($_POST['statut']) && $_POST['statut'] == 'actif') || (isset($utilisateur) && $utilisateur['statut'] == 'actif')) ? 'selected' : '' ?>>Actif</option>
                                    <option value="inactif" <?= ((isset($_POST['statut']) && $_POST['statut'] == 'inactif') || (isset($utilisateur) && $utilisateur['statut'] == 'inactif')) ? 'selected' : '' ?>>Inactif</option>
                                    <option value="suspendu" <?= ((isset($_POST['statut']) && $_POST['statut'] == 'suspendu') || (isset($utilisateur) && $utilisateur['statut'] == 'suspendu')) ? 'selected' : '' ?>>Suspendu</option>
                                </select>
                                <?= show_field_error('statut', $erreurs) ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 position-relative">
                        <label class="form-label" for="mot_de_passe">
                            <?= isset($utilisateur) ? 'Nouveau mot de passe' : 'Mot de passe' ?> 
                            <?php if (!isset($utilisateur)): ?><span class="text-danger">*</span><?php endif; ?>
                        </label>
                         <div class="input-group">
                            <input class="form-control <?= isset($erreurs['mot_de_passe']) ? 'is-invalid' : '' ?>" 
                               type="password" name="mot_de_passe" id="mot_de_passe"
                            placeholder="<?= isset($utilisateur) ? 'Laisser vide pour ne pas changer' : 'Mot de passe (min. 8 caractères)' ?>"
                            <?= !isset($utilisateur) ? 'required' : '' ?>
                            minlength="8">
                            <button class="btn btn-outline-secondary" type="button" id="passwordToggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        
                        <?= show_field_error('mot_de_passe', $erreurs) ?>
                        
                        <div class="mt-2">
                            <div class="form-text">
                                <strong>Exigences du mot de passe :</strong>
                                <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
                                    <li id="length-check" class="text-danger">✓ Minimum 8 caractères</li>
                                    <li id="lower-check" class="text-danger">✓ Au moins une lettre minuscule</li>
                                    <li id="upper-check" class="text-danger">✓ Au moins une lettre majuscule</li>
                                    <li id="special-check" class="text-danger">✓ Au moins un caractère spécial (@$!%*?&)</li>
                                </ul>
                            </div>
                        </div>
                        
                        <?php if (isset($utilisateur)): ?>
                            <small class="text-muted">Laisser vide pour conserver le mot de passe actuel</small>
                        <?php endif; ?>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3 text-primary"><i class="fas fa-user-tag me-2"></i>Rôle et permissions</h6>

                    <div class="row gx-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="role">Rôle <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select <?= isset($erreurs['role']) ? 'is-invalid' : '' ?>" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="<?= ROLE_SUPERADMIN ?>" <?= ((isset($_POST['role']) && $_POST['role'] == ROLE_SUPERADMIN) || (isset($utilisateur) && $utilisateur['role'] == ROLE_SUPERADMIN)) ? 'selected' : '' ?>>Super Admin</option>
                                    <option value="<?= ROLE_ADMIN ?>" <?= ((isset($_POST['role']) && $_POST['role'] == ROLE_ADMIN) || (isset($utilisateur) && $utilisateur['role'] == ROLE_ADMIN)) ? 'selected' : '' ?>>Administrateur</option>
                                    <option value="<?= ROLE_PROVISEUR ?>" <?= ((isset($_POST['role']) && $_POST['role'] == ROLE_PROVISEUR) || (isset($utilisateur) && $utilisateur['role'] == ROLE_PROVISEUR)) ? 'selected' : '' ?>>Proviseur</option>
                                    <option value="<?= ROLE_SECRETAIRE ?>" <?= ((isset($_POST['role']) && $_POST['role'] == ROLE_SECRETAIRE) || (isset($utilisateur) && $utilisateur['role'] == ROLE_SECRETAIRE)) ? 'selected' : '' ?>>Secrétaire</option>
                                    <option value="<?= ROLE_GESTIONNAIRE ?>" <?= ((isset($_POST['role']) && $_POST['role'] == ROLE_GESTIONNAIRE) || (isset($utilisateur) && $utilisateur['role'] == ROLE_GESTIONNAIRE)) ? 'selected' : '' ?>>Gestionnaire</option>
                                </select>
                                <?= show_field_error('role', $erreurs) ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="permissions-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Permissions spécifiques</label>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllPermissions(true)">
                                    <i class="fas fa-check-square me-1"></i>Tout cocher
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllPermissions(false)">
                                    <i class="fas fa-square me-1"></i>Tout décocher
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($erreurs['permissions'])): ?>
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= e($erreurs['permissions']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row" id="permissions-list">
                            <!-- Les permissions seront chargées dynamiquement -->
                        </div>
                        <small class="text-muted">Les permissions sont automatiquement suggérées selon le rôle</small>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary">
                    <div class="px-sm-3 px-md-5">
                        <div class="d-flex justify-content-between">
                            <a href="<?= url("administration/utilisateurs") ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-success px-5">
                                <i class="fas fa-save me-2"></i>
                                <?= isset($utilisateur) ? 'Mettre à jour' : 'Créer l\'utilisateur' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Permissions par rôle
const permissionsParRole = <?= json_encode($permissions_par_role) ?>;

// Toutes les permissions disponibles
const allPermissions = <?= json_encode($all_permissions) ?>;

// Permissions actuelles de l'utilisateur (si modification)
const currentPermissions = <?= isset($utilisateur) ? json_encode($utilisateur['permissions_array'] ?? []) : '[]' ?>;

// Permissions du POST (en cas d'erreur de soumission)
const postedPermissions = <?= isset($_POST['permissions']) ? json_encode(array_intersect($_POST['permissions'], $all_permissions)) : '[]' ?>;

// Rôle actuel
const currentRole = "<?= isset($_POST['role']) ? e($_POST['role']) : (isset($utilisateur) ? $utilisateur['role'] : '') ?>";

function updatePermissions() {
    const roleSelect = document.getElementById('role');
    const permissionsList = document.getElementById('permissions-list');
    const selectedRole = roleSelect.value;
    
    // Vider la liste actuelle
    permissionsList.innerHTML = '';
    
    if (selectedRole && permissionsParRole[selectedRole]) {
        // Rendre la section visible
        document.getElementById('permissions-section').style.display = 'block';
        
        // Ajouter les permissions du rôle sélectionné
        const permissions = permissionsParRole[selectedRole];
        
        // Utiliser les permissions du POST en priorité, sinon celles de l'utilisateur, sinon rien
        const permissionsToUse = (postedPermissions && postedPermissions.length > 0) ? 
            postedPermissions : currentPermissions;
        
        for (const [permission, description] of Object.entries(permissions)) {
            const isChecked = permissionsToUse.includes(permission);
            
            const col = document.createElement('div');
            col.className = 'col-md-6 mb-2';
            
            col.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input permission-checkbox" type="checkbox" 
                           name="permissions[]" 
                           value="${permission}"
                           id="perm-${permission}"
                           ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="perm-${permission}" style="font-size: 0.9rem;">
                        ${description}
                    </label>
                </div>
            `;
            
            permissionsList.appendChild(col);
        }
    } else {
        // Cacher la section si aucun rôle n'est sélectionné
        document.getElementById('permissions-section').style.display = 'none';
    }
}

// Fonction pour cocher/décocher toutes les permissions
function toggleAllPermissions(checked) {
    const checkboxes = document.querySelectorAll('#permissions-list .permission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
}

// Validation du mot de passe en temps réel
function validatePassword(password) {
    const hasMinLength = password.length >= 8;
    const hasLowerCase = /[a-z]/.test(password);
    const hasUpperCase = /[A-Z]/.test(password);
    const hasSpecialChar = /[@$!%*?&]/.test(password);
    
    // Mettre à jour les indicateurs visuels
    document.getElementById('length-check').className = hasMinLength ? 'text-success' : 'text-danger';
    document.getElementById('lower-check').className = hasLowerCase ? 'text-success' : 'text-danger';
    document.getElementById('upper-check').className = hasUpperCase ? 'text-success' : 'text-danger';
    document.getElementById('special-check').className = hasSpecialChar ? 'text-success' : 'text-danger';
    
    return hasMinLength && hasLowerCase && hasUpperCase && hasSpecialChar;
}

// Initialiser les permissions au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Définir le rôle si on a une valeur
    if (currentRole) {
        document.getElementById('role').value = currentRole;
    }
    
    updatePermissions();
    
    // Écouter les changements du rôle
    document.getElementById('role').addEventListener('change', updatePermissions);
    
    // Validation du mot de passe en temps réel
    const passwordField = document.getElementById('mot_de_passe');
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            validatePassword(this.value);
        });
        
        // Initialiser la validation
        if (passwordField.value) {
            validatePassword(passwordField.value);
        }
    }
    
    // Validation côté client pour les mots de passe
    const form = document.getElementById('user-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const isEditMode = <?= isset($utilisateur) ? 'true' : 'false' ?>;
            let hasError = false;
            let errorMessage = '';
            
            // Vérification du rôle
            const roleField = document.getElementById('role');
            if (!roleField.value) {
                hasError = true;
                errorMessage = 'Veuillez sélectionner un rôle.';
                roleField.focus();
            }
            
            // En mode création, le mot de passe est obligatoire et doit être valide
            if (!isEditMode) {
                const password = passwordField.value;
                if (!password) {
                    hasError = true;
                    errorMessage = 'Le mot de passe est requis.';
                    passwordField.focus();
                } else if (!validatePassword(password)) {
                    hasError = true;
                    errorMessage = 'Le mot de passe ne respecte pas toutes les exigences de sécurité.';
                    passwordField.focus();
                }
            }
            
            // En mode édition, si un mot de passe est fourni, il doit être valide
            if (isEditMode && passwordField.value && !validatePassword(passwordField.value)) {
                hasError = true;
                errorMessage = 'Le nouveau mot de passe ne respecte pas les exigences de sécurité.';
                passwordField.focus();
            }
            
            if (hasError) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }
            
            // Afficher un indicateur de chargement
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';
                submitBtn.disabled = true;
            }
            
            return true;
        });
    }
    
    // Toggle pour afficher/masquer le mot de passe
    const passwordToggle = document.getElementById('passwordToggle');
    
    if (passwordField) {
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    }
});
</script>
<style>
.invalid-feedback {
    display: block !important;
}

.form-text ul {
    margin-bottom: 0.5rem;
}

.form-text li {
    margin-bottom: 0.25rem;
}

.text-success {
    color: #198754 !important;
}

.text-danger {
    color: #dc3545 !important;
}
</style>