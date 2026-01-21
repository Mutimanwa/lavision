<?php
/**
 * Formulaire d'ajout/modification d'horaire
 * Vue pour la gestion des emplois du temps
 */

// Inclure l'en-tête
require_once __DIR__ . '/../templates/header.php';

// Récupérer les données du contexte
$horaire = $horaire ?? null;
$is_edit = $horaire !== null;
$page_title = $is_edit ? 'Modifier l\'horaire' : 'Ajouter un horaire';
$submit_text = $is_edit ? 'Modifier' : 'Ajouter';

// Récupérer les données pour les listes déroulantes
$classes = $classes ?? [];
$matieres = $matieres ?? [];
$professeurs = $professeurs ?? [];
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
                    <i class="fas fa-calendar-alt me-2"></i>
                    <?php echo $page_title; ?>
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Tableau de bord</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique/horaires">Horaires</a></li>
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
                        Informations de l'horaire
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/academique/<?php echo $is_edit ? 'modifier-horaire/' . $horaire['edt_id'] : 'ajouter-horaire'; ?>" id="horaireForm">
                        <!-- Jeton CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <!-- Informations générales -->
                        <h6 class="section-title">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations générales
                        </h6>
                        <div class="row g-3 mb-4">
                            <!-- Classe -->
                            <div class="col-md-4">
                                <label for="class_id" class="form-label">
                                    Classe <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['class_id']) ? 'is-invalid' : ''; ?>"
                                        id="class_id"
                                        name="class_id"
                                        required>
                                    <option value="">Sélectionner une classe...</option>
                                    <?php foreach ($classes as $classe): ?>
                                        <option value="<?php echo $classe['class_id']; ?>"
                                                <?php echo ($horaire['class_id'] ?? '') == $classe['class_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($classe['libelle']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['class_id'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['class_id']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Matière -->
                            <div class="col-md-4">
                                <label for="matiere_id" class="form-label">
                                    Matière <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['matiere_id']) ? 'is-invalid' : ''; ?>"
                                        id="matiere_id"
                                        name="matiere_id"
                                        required>
                                    <option value="">Sélectionner une matière...</option>
                                    <?php foreach ($matieres as $matiere): ?>
                                        <option value="<?php echo $matiere['matiere_id']; ?>"
                                                <?php echo ($horaire['matiere_id'] ?? '') == $matiere['matiere_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($matiere['nom_matiere']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['matiere_id'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['matiere_id']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Professeur -->
                            <div class="col-md-4">
                                <label for="professeur_id" class="form-label">
                                    Professeur <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['professeur_id']) ? 'is-invalid' : ''; ?>"
                                        id="professeur_id"
                                        name="professeur_id"
                                        required>
                                    <option value="">Sélectionner un professeur...</option>
                                    <?php foreach ($professeurs as $professeur): ?>
                                        <option value="<?php echo $professeur['professeur_id']; ?>"
                                                <?php echo ($horaire['professeur_id'] ?? '') == $professeur['professeur_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($professeur['nom'] . ' ' . $professeur['prenom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['professeur_id'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['professeur_id']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Informations temporelles -->
                        <h6 class="section-title">
                            <i class="fas fa-clock me-2"></i>
                            Informations temporelles
                        </h6>
                        <div class="row g-3 mb-4">
                            <!-- Jour de la semaine -->
                            <div class="col-md-3">
                                <label for="jour_semaine" class="form-label">
                                    Jour de la semaine <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['jour_semaine']) ? 'is-invalid' : ''; ?>"
                                        id="jour_semaine"
                                        name="jour_semaine"
                                        required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Lundi" <?php echo ($horaire['jour_semaine'] ?? '') === 'Lundi' ? 'selected' : ''; ?>>Lundi</option>
                                    <option value="Mardi" <?php echo ($horaire['jour_semaine'] ?? '') === 'Mardi' ? 'selected' : ''; ?>>Mardi</option>
                                    <option value="Mercredi" <?php echo ($horaire['jour_semaine'] ?? '') === 'Mercredi' ? 'selected' : ''; ?>>Mercredi</option>
                                    <option value="Jeudi" <?php echo ($horaire['jour_semaine'] ?? '') === 'Jeudi' ? 'selected' : ''; ?>>Jeudi</option>
                                    <option value="Vendredi" <?php echo ($horaire['jour_semaine'] ?? '') === 'Vendredi' ? 'selected' : ''; ?>>Vendredi</option>
                                    <option value="Samedi" <?php echo ($horaire['jour_semaine'] ?? '') === 'Samedi' ? 'selected' : ''; ?>>Samedi</option>
                                </select>
                                <?php if (isset($errors['jour_semaine'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['jour_semaine']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Heure de début -->
                            <div class="col-md-3">
                                <label for="heure_debut" class="form-label">
                                    Heure de début <span class="text-danger">*</span>
                                </label>
                                <input type="time"
                                       class="form-control <?php echo isset($errors['heure_debut']) ? 'is-invalid' : ''; ?>"
                                       id="heure_debut"
                                       name="heure_debut"
                                       value="<?php echo htmlspecialchars($horaire['heure_debut'] ?? ''); ?>"
                                       required>
                                <?php if (isset($errors['heure_debut'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['heure_debut']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Heure de fin -->
                            <div class="col-md-3">
                                <label for="heure_fin" class="form-label">
                                    Heure de fin <span class="text-danger">*</span>
                                </label>
                                <input type="time"
                                       class="form-control <?php echo isset($errors['heure_fin']) ? 'is-invalid' : ''; ?>"
                                       id="heure_fin"
                                       name="heure_fin"
                                       value="<?php echo htmlspecialchars($horaire['heure_fin'] ?? ''); ?>"
                                       required>
                                <?php if (isset($errors['heure_fin'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['heure_fin']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Salle -->
                            <div class="col-md-3">
                                <label for="salle" class="form-label">
                                    Salle
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="salle"
                                       name="salle"
                                       value="<?php echo htmlspecialchars($horaire['salle'] ?? ''); ?>"
                                       placeholder="Ex: Salle 101">
                            </div>
                        </div>

                        <!-- Informations académiques -->
                        <h6 class="section-title">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Informations académiques
                        </h6>
                        <div class="row g-3 mb-4">
                            <!-- Type de cours -->
                            <div class="col-md-3">
                                <label for="type_cours" class="form-label">
                                    Type de cours <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['type_cours']) ? 'is-invalid' : ''; ?>"
                                        id="type_cours"
                                        name="type_cours"
                                        required>
                                    <option value="cours" <?php echo ($horaire['type_cours'] ?? 'cours') === 'cours' ? 'selected' : ''; ?>>Cours magistral</option>
                                    <option value="td" <?php echo ($horaire['type_cours'] ?? '') === 'td' ? 'selected' : ''; ?>>Travaux dirigés</option>
                                    <option value="tp" <?php echo ($horaire['type_cours'] ?? '') === 'tp' ? 'selected' : ''; ?>>Travaux pratiques</option>
                                    <option value="examen" <?php echo ($horaire['type_cours'] ?? '') === 'examen' ? 'selected' : ''; ?>>Examen</option>
                                </select>
                                <?php if (isset($errors['type_cours'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['type_cours']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Année scolaire -->
                            <div class="col-md-3">
                                <label for="annee_id" class="form-label">
                                    Année scolaire <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['annee_id']) ? 'is-invalid' : ''; ?>"
                                        id="annee_id"
                                        name="annee_id"
                                        required>
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($annees_scolaires as $annee): ?>
                                        <option value="<?php echo $annee['annee_id']; ?>"
                                                <?php echo ($horaire['annee_id'] ?? '') == $annee['annee_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($annee['annee_libelle']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['annee_id'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['annee_id']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Période -->
                            <div class="col-md-3">
                                <label for="periode" class="form-label">
                                    Période <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['periode']) ? 'is-invalid' : ''; ?>"
                                        id="periode"
                                        name="periode"
                                        required>
                                    <option value="trimestre1" <?php echo ($horaire['periode'] ?? 'trimestre1') === 'trimestre1' ? 'selected' : ''; ?>>1er Trimestre</option>
                                    <option value="trimestre2" <?php echo ($horaire['periode'] ?? '') === 'trimestre2' ? 'selected' : ''; ?>>2ème Trimestre</option>
                                    <option value="trimestre3" <?php echo ($horaire['periode'] ?? '') === 'trimestre3' ? 'selected' : ''; ?>>3ème Trimestre</option>
                                </select>
                                <?php if (isset($errors['periode'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['periode']); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Statut -->
                            <div class="col-md-3">
                                <label for="statut" class="form-label">
                                    Statut <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($errors['statut']) ? 'is-invalid' : ''; ?>"
                                        id="statut"
                                        name="statut"
                                        required>
                                    <option value="actif" <?php echo ($horaire['statut'] ?? 'actif') === 'actif' ? 'selected' : ''; ?>>Actif</option>
                                    <option value="suspendu" <?php echo ($horaire['statut'] ?? '') === 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                                </select>
                                <?php if (isset($errors['statut'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['statut']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Période de validité -->
                        <h6 class="section-title">
                            <i class="fas fa-calendar-check me-2"></i>
                            Période de validité
                        </h6>
                        <div class="row g-3 mb-4">
                            <!-- Date de début -->
                            <div class="col-md-4">
                                <label for="date_debut" class="form-label">
                                    Date de début
                                </label>
                                <input type="date"
                                       class="form-control"
                                       id="date_debut"
                                       name="date_debut"
                                       value="<?php echo htmlspecialchars($horaire['date_debut'] ?? ''); ?>">
                                <div class="form-text">
                                    Laissez vide pour commencer immédiatement
                                </div>
                            </div>

                            <!-- Date de fin -->
                            <div class="col-md-4">
                                <label for="date_fin" class="form-label">
                                    Date de fin
                                </label>
                                <input type="date"
                                       class="form-control"
                                       id="date_fin"
                                       name="date_fin"
                                       value="<?php echo htmlspecialchars($horaire['date_fin'] ?? ''); ?>">
                                <div class="form-text">
                                    Laissez vide pour une validité indéfinie
                                </div>
                            </div>
                        </div>

                        <!-- Vérification des conflits -->
                        <div id="conflictAlert" class="alert alert-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention :</strong> Un conflit d'horaire a été détecté !
                            <div id="conflictDetails"></div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo BASE_URL; ?>/academique/horaires" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo $submit_text; ?> l'horaire
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
    $('#horaireForm').submit(function(e) {
        let isValid = true;
        const requiredFields = ['class_id', 'matiere_id', 'professeur_id', 'jour_semaine', 'heure_debut', 'heure_fin', 'type_cours', 'annee_id', 'periode', 'statut'];

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

        // Validation des heures
        const heureDebut = $('#heure_debut').val();
        const heureFin = $('#heure_fin').val();

        if (heureDebut && heureFin) {
            if (heureDebut >= heureFin) {
                $('#heure_fin').addClass('is-invalid');
                alert('L\'heure de fin doit être postérieure à l\'heure de début.');
                isValid = false;
            } else {
                $('#heure_fin').removeClass('is-invalid');
            }
        }

        // Validation des dates
        const dateDebut = $('#date_debut').val();
        const dateFin = $('#date_fin').val();

        if (dateDebut && dateFin) {
            if (new Date(dateDebut) >= new Date(dateFin)) {
                $('#date_fin').addClass('is-invalid');
                alert('La date de fin doit être postérieure à la date de début.');
                isValid = false;
            } else {
                $('#date_fin').removeClass('is-invalid');
            }
        }

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        // Vérifier les conflits avant soumission
        checkConflicts(function(hasConflicts) {
            if (hasConflicts) {
                if (!confirm('Des conflits d\'horaire ont été détectés. Voulez-vous continuer ?')) {
                    e.preventDefault();
                    return false;
                }
            }

            // Désactiver le bouton pendant la soumission
            $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');
        });

        e.preventDefault(); // Toujours empêcher la soumission automatique
    });

    // Vérification des conflits en temps réel
    function checkConflicts(callback) {
        const formData = new FormData($('#horaireForm')[0]);

        $.ajax({
            url: '<?php echo BASE_URL; ?>/academique/verifier-conflits',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.conflits && response.conflits.length > 0) {
                    let conflictHtml = '<ul class="mb-0">';
                    response.conflits.forEach(function(conflit) {
                        conflictHtml += '<li>' + conflit.message + '</li>';
                    });
                    conflictHtml += '</ul>';

                    $('#conflictDetails').html(conflictHtml);
                    $('#conflictAlert').show();
                    callback(true);
                } else {
                    $('#conflictAlert').hide();
                    callback(false);
                }
            },
            error: function() {
                // En cas d'erreur, continuer sans vérification
                callback(false);
            }
        });
    }

    // Mise à jour automatique des professeurs selon la matière
    $('#matiere_id').change(function() {
        const matiereId = $(this).val();
        if (matiereId) {
            // Ici nous pourrions charger les professeurs spécialisés dans cette matière
            // Pour l'instant, on garde tous les professeurs
        }
    });

    // Calcul automatique de la durée
    $('#heure_debut, #heure_fin').change(function() {
        const debut = $('#heure_debut').val();
        const fin = $('#heure_fin').val();

        if (debut && fin) {
            const debutTime = new Date('1970-01-01T' + debut + ':00');
            const finTime = new Date('1970-01-01T' + fin + ':00');
            const diffMinutes = (finTime - debutTime) / (1000 * 60);

            if (diffMinutes > 0) {
                console.log('Durée du cours:', diffMinutes, 'minutes');
            }
        }
    });

    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>