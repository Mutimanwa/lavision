<?php
/**
 * Vue : Formulaire d'ajout/modification d'un professeur
 * Formulaire complet avec validation côté client
 */

?>


<!-- En-tête de page -->
<div class="row mb-2 py-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-user-edit text-primary"></i>
                    <?php echo htmlspecialchars($titre_page); ?>
                </h1>
                <p class="text-muted"><?php echo htmlspecialchars($sous_titre); ?></p>
            </div>
            <div>
                <a href="<?php echo url('personnel/professeurs'); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>

    <!-- Affichage des erreurs -->
    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle"></i> Erreurs de validation :</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($erreurs as $champ => $message): ?>
                    <li><?php echo htmlspecialchars($message); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Affichage du succès -->
    <?php if (!empty($succes)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-check-circle"></i> Succès :</strong>
            <?php echo htmlspecialchars($succes); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit"></i> Informations du Professeur
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo url('personnel/professeurs/ajouter'); ?>" id="formProfesseur" novalidate>
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <!-- Informations personnelles -->
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-user"></i> Informations Personnelles
                        </h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="matricule_prof" class="form-label">
                                    Matricule <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php echo isset($erreurs['matricule_prof']) ? 'is-invalid' : ''; ?>"
                                       id="matricule_prof" name="matricule_prof"
                                       value="<?php echo htmlspecialchars($professeur['matricule_prof'] ?? ''); ?>"
                                       required maxlength="30" disabled>
                                <div class="invalid-feedback">
                                    <?php echo $erreurs['matricule_prof'] ?? 'Veuillez saisir un matricule valide.'; ?>
                                </div>
                                <div class="form-text">Ex: PROF001, MAT2024-001</div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="nom" class="form-label">
                                    Nom <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php echo isset($erreurs['nom']) ? 'is-invalid' : ''; ?>"
                                       id="nom" name="nom"
                                       value="<?php echo htmlspecialchars($professeur['nom'] ?? ''); ?>"
                                       required maxlength="100">
                                <div class="invalid-feedback">
                                    <?php echo $erreurs['nom'] ?? 'Veuillez saisir le nom.'; ?>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="post_nom" class="form-label">Post-nom</label>
                                <input type="text" class="form-control"
                                       id="post_nom" name="post_nom"
                                       value="<?php echo htmlspecialchars($professeur['post_nom'] ?? ''); ?>"
                                       maxlength="100">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">
                                    Prénom <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php echo isset($erreurs['prenom']) ? 'is-invalid' : ''; ?>"
                                       id="prenom" name="prenom"
                                       value="<?php echo htmlspecialchars($professeur['prenom'] ?? ''); ?>"
                                       required maxlength="100">
                                <div class="invalid-feedback">
                                    <?php echo $erreurs['prenom'] ?? 'Veuillez saisir le prénom.'; ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="genre" class="form-label">Genre</label>
                                <select class="form-select" id="genre" name="genre">
                                    <option value="">Sélectionner...</option>
                                    <?php foreach ($genres as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"
                                                <?php echo (isset($professeur['genre']) && $professeur['genre'] === $key) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_naissance" class="form-label">Date de Naissance</label>
                                <input type="date" class="form-control <?php echo isset($erreurs['date_naissance']) ? 'is-invalid' : ''; ?>"
                                       id="date_naissance" name="date_naissance"
                                       value="<?php echo htmlspecialchars($professeur['date_naissance'] ?? ''); ?>">
                                <div class="invalid-feedback">
                                    <?php echo $erreurs['date_naissance'] ?? 'Date de naissance invalide.'; ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lieu_naissance" class="form-label">Lieu de Naissance</label>
                                <input type="text" class="form-control"
                                       id="lieu_naissance" name="lieu_naissance"
                                       value="<?php echo htmlspecialchars($professeur['lieu_naissance'] ?? ''); ?>"
                                       maxlength="100">
                            </div>
                        </div>

                        <!-- Coordonnées -->
                        <h5 class="text-primary mb-3 mt-4">
                            <i class="fas fa-address-book"></i> Coordonnées
                        </h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">
                                    Téléphone <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control <?php echo isset($erreurs['telephone']) ? 'is-invalid' : ''; ?>"
                                       id="telephone" name="telephone"
                                       value="<?php echo htmlspecialchars($professeur['telephone'] ?? ''); ?>"
                                       required maxlength="20">
                                <div class="invalid-feedback">
                                    <?php echo $erreurs['telephone'] ?? 'Veuillez saisir un numéro de téléphone valide.'; ?>
                                </div>
                                <div class="form-text">Ex: +243 81 234 5678</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?php echo isset($erreurs['email']) ? 'is-invalid' : ''; ?>"
                                       id="email" name="email"
                                       value="<?php echo htmlspecialchars($professeur['email'] ?? ''); ?>"
                                       maxlength="255">
                                <div class="invalid-feedback">
                                    <?php echo $erreurs['email'] ?? 'Adresse email invalide.'; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3"
                                      maxlength="500"><?php echo htmlspecialchars($professeur['adresse'] ?? ''); ?></textarea>
                        </div>

                        <!-- Informations professionnelles -->
                        <h5 class="text-primary mb-3 mt-4">
                            <i class="fas fa-briefcase"></i> Informations Professionnelles
                        </h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specialite" class="form-label">Spécialité</label>
                                <input type="text" class="form-control"
                                       id="specialite" name="specialite"
                                       value="<?php echo htmlspecialchars($professeur['specialite'] ?? ''); ?>"
                                       maxlength="100" placeholder="Ex: Mathématiques, Français, Physique">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="diplome" class="form-label">Diplôme</label>
                                <input type="text" class="form-control"
                                       id="diplome" name="diplome"
                                       value="<?php echo htmlspecialchars($professeur['diplome'] ?? ''); ?>"
                                       maxlength="100" placeholder="Ex: Licence, Master, Doctorat">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="date_embauche" class="form-label">Date d'Embauche</label>
                                <input type="date" class="form-control <?php echo isset($erreurs['date_embauche']) ? 'is-invalid' : ''; ?>"
                                       id="date_embauche" name="date_embauche"
                                       value="<?php echo htmlspecialchars($professeur['date_embauche'] ?? ''); ?>">
                                <div class="invalid-feedback">
                                    <?php echo $erreurs['date_embauche'] ?? 'Date d\'embauche invalide.'; ?>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <?php foreach ($statuts_disponibles as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"
                                                <?php echo (isset($professeur['statut']) && $professeur['statut'] === $key) ? 'selected' : ($key === 'actif' && !isset($professeur['statut']) ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="type_contrat" class="form-label">Type de Contrat</label>
                                <select class="form-select" id="type_contrat" name="type_contrat">
                                    <?php foreach ($types_contrat as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"
                                                <?php echo (isset($professeur['type_contrat']) && $professeur['type_contrat'] === $key) ? 'selected' : ($key === 'contractuel' && !isset($professeur['type_contrat']) ? 'selected' : ''); ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salaire_base" class="form-label">Salaire de Base (CDF)</label>
                                <input type="number" class="form-control"
                                       id="salaire_base" name="salaire_base"
                                       value="<?php echo htmlspecialchars($professeur['salaire_base'] ?? ''); ?>"
                                       min="0" step="0.01">
                                <div class="form-text">Salaire mensuel en Francs Congolais</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nationalite" class="form-label">Nationalité</label>
                                <input type="text" class="form-control"
                                       id="nationalite" name="nationalite"
                                       value="<?php echo htmlspecialchars($professeur['nationalite'] ?? 'Congolaise'); ?>"
                                       maxlength="50">
                            </div>
                        </div>

                        <!-- Informations bancaires -->
                        <h5 class="text-info mb-3 mt-4">
                            <i class="fas fa-university"></i> Informations Bancaires (Optionnel)
                        </h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="banque" class="form-label">Banque</label>
                                <input type="text" class="form-control"
                                       id="banque" name="banque"
                                       value="<?php echo htmlspecialchars($professeur['banque'] ?? ''); ?>"
                                       maxlength="100">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="numero_compte" class="form-label">Numéro de Compte</label>
                                <input type="text" class="form-control"
                                       id="numero_compte" name="numero_compte"
                                       value="<?php echo htmlspecialchars($professeur['numero_compte'] ?? ''); ?>"
                                       maxlength="50">
                            </div>
                        </div>

                        <!-- Compte utilisateur -->
                        <?php if (!$est_modification): ?>
                            <h5 class="text-warning mb-3 mt-4">
                                <i class="fas fa-user-shield"></i> Création de Compte Utilisateur
                            </h5>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Un compte utilisateur permet au professeur d'accéder au système.
                                Le mot de passe sera généré automatiquement et pourra être changé ultérieurement.
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="creer_compte" name="creer_compte" checked>
                                    <label class="form-check-label" for="creer_compte">
                                        Créer un compte utilisateur pour ce professeur
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <a href="<?php echo BASE_URL; ?>personnel/professeurs" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $est_modification ? 'Modifier' : 'Créer'; ?> le Professeur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div class="col-lg-4">
            <!-- Aide et conseils -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-question-circle"></i> Aide
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Champs obligatoires</h6>
                    <ul class="small mb-3">
                        <li>Matricule</li>
                        <li>Nom</li>
                        <li>Prénom</li>
                        <li>Téléphone</li>
                    </ul>

                    <h6>Conseils</h6>
                    <ul class="small mb-0">
                        <li>Le matricule doit être unique</li>
                        <li>L'email doit être valide s'il est fourni</li>
                        <li>Les dates doivent être au format AAAA-MM-JJ</li>
                        <li>Le téléphone doit inclure l'indicatif (+243)</li>
                    </ul>
                </div>
            </div>

            <!-- Aperçu du matricule -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-eye"></i> Aperçu
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="avatar avatar-lg mb-3">
                            <div class="avatar-initial rounded-circle bg-primary text-white"
                                 style="width: 80px; height: 80px; font-size: 2rem; display: flex; align-items: center; justify-content: center;">
                                <span id="preview-initials">??</span>
                            </div>
                        </div>
                        <h6 id="preview-name">Nom Prénom</h6>
                        <p class="text-muted small mb-2" id="preview-matricule">MATRICULE</p>
                        <p class="text-muted small" id="preview-specialite">Spécialité</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
// Mise à jour de l'aperçu en temps réel
function updatePreview() {
    const nom = document.getElementById('nom').value || '?';
    const prenom = document.getElementById('prenom').value || '?';
    const matricule = document.getElementById('matricule_prof').value || 'MATRICULE';
    const specialite = document.getElementById('specialite').value || 'Spécialité non définie';

    // Initiales
    const initiales = (prenom.charAt(0) + nom.charAt(0)).toUpperCase();
    document.getElementById('preview-initials').textContent = initiales;

    // Nom complet
    document.getElementById('preview-name').textContent = `${nom} ${prenom}`;

    // Matricule et spécialité
    document.getElementById('preview-matricule').textContent = matricule.toUpperCase();
    document.getElementById('preview-specialite').textContent = specialite;
}

// Événements pour la mise à jour en temps réel
document.addEventListener('DOMContentLoaded', function() {
    ['nom', 'prenom', 'matricule_prof', 'specialite'].forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updatePreview);
        }
    });

    // Mise à jour initiale
    updatePreview();

    // Validation du formulaire
    const form = document.getElementById('formProfesseur');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Formatage automatique du téléphone
    const telephoneField = document.getElementById('telephone');
    telephoneField.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 0) {
            if (value.length <= 2) {
                value = '+' + value;
            } else if (value.length <= 4) {
                value = '+' + value.substring(0, 2) + ' ' + value.substring(2);
            } else {
                value = '+' + value.substring(0, 2) + ' ' + value.substring(2, 4) + ' ' + value.substring(4, 7) + ' ' + value.substring(7, 11);
            }
        }
        this.value = value;
    });
});
</script>