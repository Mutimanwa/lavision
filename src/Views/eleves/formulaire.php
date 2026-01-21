<?php
/**
 * Formulaire d'ajout/modification d'élève
 * Vue pour la gestion des élèves (CRUD)
 */

// Inclure l'en-tête
require_once __DIR__ . '/../templates/header.php';

// Récupérer les données du contexte
$eleve = $eleve ?? null;
$is_edit = $eleve !== null;
$page_title = $is_edit ? 'Modifier l\'élève' : 'Ajouter un élève';
$submit_text = $is_edit ? 'Modifier' : 'Ajouter';

// Récupérer les données pour les listes déroulantes
$classes = $classes ?? [];
$niveaux = $niveaux ?? [];
$sections = $sections ?? [];
$annees_scolaires = $annees_scolaires ?? [];

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';
$errors = $_SESSION['form_errors'] ?? [];

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message'], $_SESSION['form_errors']);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <?php require_once __DIR__ . '/../templates/sidebar.php'; ?>
        </nav>

        <!-- Contenu principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-user-graduate me-2"></i>
                    <?php echo $page_title; ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Tableau de bord</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/eleves/liste">Élèves</a></li>
                        <li class="breadcrumb-item active"><?php echo $is_edit ? 'Modification' : 'Ajout'; ?></li>
                    </ol>
                </nav>
            </div>

            <!-- Messages d'alerte -->
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Informations de l'élève
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/eleves/<?php echo $is_edit ? 'modifier/' . $eleve['eleve_id'] : 'ajouter'; ?>" enctype="multipart/form-data" id="eleveForm">
                        <!-- Jeton CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <!-- Informations personnelles -->
                        <h6 class="section-title">
                            <i class="fas fa-id-card me-2"></i>
                            Informations personnelles
                        </h6>
                        <div class="row g-3 mb-4">
                            <!-- Matricule -->
                            <div class="col-md-4">
                                <label for="matricule" class="form-label">
                                    Matricule <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control <?php echo isset($errors['matricule']) ? 'is-invalid' : ''; ?>"
                                       id="matricule"
                                       name="matricule"
                                       value="<?php echo htmlspecialchars($eleve['matricule'] ?? ''); ?>"
                                       required
                                       placeholder="Ex: ELE2024001">
                                <?php if (isset($errors['matricule'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['matricule']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nom -->
                            <div class="col-md-4">
                                <label for="nom" class="form-label">
                                    Nom <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>"
                                       id="nom"
                                       name="nom"
                                       value="<?php echo htmlspecialchars($eleve['nom'] ?? ''); ?>"
                                       required
                                       placeholder="Nom de famille">
                                <?php if (isset($errors['nom'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['nom']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Post-nom -->
                            <div class="col-md-4">
                                <label for="post_nom" class="form-label">
                                    Post-nom <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control <?php echo isset($errors['post_nom']) ? 'is-invalid' : ''; ?>"
                                       id="post_nom"
                                       name="post_nom"
                                       value="<?php echo htmlspecialchars($eleve['post_nom'] ?? ''); ?>"
                                       required
                                       placeholder="Deuxième nom">
                                <?php if (isset($errors['post_nom'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['post_nom']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Prénom -->
                            <div class="col-md-4">
                                <label for="prenom" class="form-label">
                                    Prénom <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control <?php echo isset($errors['prenom']) ? 'is-invalid' : ''; ?>"
                                       id="prenom"
                                       name="prenom"
                                       value="<?php echo htmlspecialchars($eleve['prenom'] ?? ''); ?>"
                                       required
                                       placeholder="Prénom">
                                <?php if (isset($errors['prenom'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['prenom']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Date de naissance -->
                            <div class="col-md-4">
                                <label for="date_naissance" class="form-label">
                                    Date de naissance <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control <?php echo isset($errors['date_naissance']) ? 'is-invalid' : ''; ?>"
                                       id="date_naissance"
                                       name="date_naissance"
                                       value="<?php echo htmlspecialchars($eleve['date_naissance'] ?? ''); ?>"
                                       required>
                                <?php if (isset($errors['date_naissance'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['date_naissance']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Lieu de naissance -->
                            <div class="col-md-4">
                                <label for="lieu_naissance" class="form-label">
                                    Lieu de naissance
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="lieu_naissance"
                                       name="lieu_naissance"
                                       value="<?php echo htmlspecialchars($eleve['lieu_naissance'] ?? ''); ?>"
                                       placeholder="Ville de naissance">
                            </div>

                            <!-- Genre -->
                            <div class="col-md-3">
                                <label for="genre" class="form-label">
                                    Genre <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['genre']) ? 'is-invalid' : ''; ?>"
                                        id="genre"
                                        name="genre"
                                        required>
                                    <option value="">Sélectionner...</option>
                                    <option value="M" <?php echo ($eleve['genre'] ?? '') === 'M' ? 'selected' : ''; ?>>Masculin</option>
                                    <option value="F" <?php echo ($eleve['genre'] ?? '') === 'F' ? 'selected' : ''; ?>>Féminin</option>
                                    <option value="Autre" <?php echo ($eleve['genre'] ?? '') === 'Autre' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                                <?php if (isset($errors['genre'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['genre']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nationalité -->
                            <div class="col-md-3">
                                <label for="nationalite" class="form-label">
                                    Nationalité
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="nationalite"
                                       name="nationalite"
                                       value="<?php echo htmlspecialchars($eleve['nationalite'] ?? 'Congolaise'); ?>"
                                       placeholder="Nationalité">
                            </div>

                            <!-- Téléphone -->
                            <div class="col-md-3">
                                <label for="telephone" class="form-label">
                                    Téléphone
                                </label>
                                <input type="tel"
                                       class="form-control"
                                       id="telephone"
                                       name="telephone"
                                       value="<?php echo htmlspecialchars($eleve['telephone'] ?? ''); ?>"
                                       placeholder="+243 XX XXX XXXX">
                            </div>

                            <!-- Email -->
                            <div class="col-md-3">
                                <label for="email" class="form-label">
                                    Email
                                </label>
                                <input type="email"
                                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                       id="email"
                                       name="email"
                                       value="<?php echo htmlspecialchars($eleve['email'] ?? ''); ?>"
                                       placeholder="eleve@exemple.com">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Groupe sanguin -->
                            <div class="col-md-3">
                                <label for="groupe_sanguin" class="form-label">
                                    Groupe sanguin
                                </label>
                                <select class="form-select" id="groupe_sanguin" name="groupe_sanguin">
                                    <option value="">Sélectionner...</option>
                                    <option value="A+" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'A+' ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'A-' ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'B+' ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'B-' ? 'selected' : ''; ?>>B-</option>
                                    <option value="AB+" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                    <option value="O+" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'O+' ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?php echo ($eleve['groupe_sanguin'] ?? '') === 'O-' ? 'selected' : ''; ?>>O-</option>
                                </select>
                            </div>

                            <!-- Allergies -->
                            <div class="col-md-6">
                                <label for="allergies" class="form-label">
                                    Allergies
                                </label>
                                <textarea class="form-control"
                                          id="allergies"
                                          name="allergies"
                                          rows="2"
                                          placeholder="Liste des allergies (si applicable)"><?php echo htmlspecialchars($eleve['allergies'] ?? ''); ?></textarea>
                            </div>

                            <!-- Adresse -->
                            <div class="col-md-6">
                                <label for="adresse" class="form-label">
                                    Adresse complète
                                </label>
                                <textarea class="form-control"
                                          id="adresse"
                                          name="adresse"
                                          rows="2"
                                          placeholder="Adresse complète"><?php echo htmlspecialchars($eleve['adresse'] ?? ''); ?></textarea>
                            </div>

                            <!-- Photo -->
                            <div class="col-md-6">
                                <label for="photo" class="form-label">
                                    Photo de profil
                                </label>
                                <input type="file"
                                       class="form-control"
                                       id="photo"
                                       name="photo"
                                       accept="image/*">
                                <div class="form-text">
                                    Formats acceptés: JPG, PNG, GIF. Taille max: 2MB
                                </div>
                                <?php if ($is_edit && !empty($eleve['photo'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo ASSETS_URL; ?>/uploads/eleves/<?php echo htmlspecialchars($eleve['photo']); ?>"
                                             alt="Photo actuelle" class="img-thumbnail" style="max-width: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Informations académiques -->
                        <h6 class="section-title">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Informations académiques
                        </h6>
                        <div class="row g-3 mb-4">
                            <!-- Statut étudiant -->
                            <div class="col-md-4">
                                <label for="statut_etudiant" class="form-label">
                                    Statut <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['statut_etudiant']) ? 'is-invalid' : ''; ?>"
                                        id="statut_etudiant"
                                        name="statut_etudiant"
                                        required>
                                    <option value="en_attente" <?php echo ($eleve['statut_etudiant'] ?? 'en_attente') === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="actif" <?php echo ($eleve['statut_etudiant'] ?? '') === 'actif' ? 'selected' : ''; ?>>Actif</option>
                                    <option value="suspendu" <?php echo ($eleve['statut_etudiant'] ?? '') === 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                                    <option value="desiste" <?php echo ($eleve['statut_etudiant'] ?? '') === 'desiste' ? 'selected' : ''; ?>>Désisté</option>
                                </select>
                                <?php if (isset($errors['statut_etudiant'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['statut_etudiant']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Date d'inscription -->
                            <div class="col-md-4">
                                <label for="date_inscription" class="form-label">
                                    Date d'inscription
                                </label>
                                <input type="date"
                                       class="form-control"
                                       id="date_inscription"
                                       name="date_inscription"
                                       value="<?php echo htmlspecialchars($eleve['date_inscription'] ?? date('Y-m-d')); ?>">
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo BASE_URL; ?>/eleves/liste" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $submit_text; ?> l'élève
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.section-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.btn {
    border-radius: 0.375rem;
}

.alert {
    border-radius: 0.5rem;
    border: none;
}
</style>

<script>
$(document).ready(function() {
    // Validation du formulaire
    $('#eleveForm').submit(function(e) {
        let isValid = true;
        const requiredFields = ['matricule', 'nom', 'post_nom', 'prenom', 'date_naissance', 'genre', 'statut_etudiant'];

        // Vérifier les champs requis
        requiredFields.forEach(function(field) {
            const value = $('#' + field).val().trim();
            if (!value) {
                $('#' + field).addClass('is-invalid');
                isValid = false;
            } else {
                $('#' + field).removeClass('is-invalid');
            }
        });

        // Validation email si fourni
        const email = $('#email').val().trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#email').addClass('is-invalid');
            isValid = false;
        } else {
            $('#email').removeClass('is-invalid');
        }

        // Validation date de naissance
        const birthDate = new Date($('#date_naissance').val());
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        if (age < 5 || age > 25) {
            $('#date_naissance').addClass('is-invalid');
            alert('L\'âge de l\'élève doit être entre 5 et 25 ans.');
            isValid = false;
        } else {
            $('#date_naissance').removeClass('is-invalid');
        }

        if (!isValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
            return false;
        }

        // Désactiver le bouton pendant la soumission
        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');
    });

    // Générer automatiquement le matricule
    $('#nom, #prenom').on('input', function() {
        const nom = $('#nom').val().trim().toUpperCase();
        const prenom = $('#prenom').val().trim().charAt(0).toUpperCase();
        const annee = new Date().getFullYear();

        if (nom && prenom) {
            const matricule = `ELE${annee}${nom.substring(0, 3)}${prenom}`;
            $('#matricule').val(matricule);
        }
    });

    // Calcul automatique de l'âge
    $('#date_naissance').change(function() {
        const birthDate = new Date($(this).val());
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        // Afficher l'âge calculé (optionnel)
        console.log('Âge calculé:', age, 'ans');
    });

    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>