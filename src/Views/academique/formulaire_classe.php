<?php
/**
 * Formulaire d'ajout/modification d'une classe
 * Interface responsive avec validation Bootstrap 5
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

?>

<!-- Contenu principal -->
<div class="row py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="h4 mb-0">
                <i class="fas fa-plus text-primary me-2"></i>
                <?php echo $data['mode'] === 'ajout' ? 'Nouvelle Classe' : 'Modifier la Classe'; ?>
            </h3>
            <p class="text-muted mt-1">
                <?php echo $data['mode'] === 'ajout' ? 'Ajouter une nouvelle classe au système' : 'Modifier les informations de la classe'; ?>
            </p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique">Académique</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique/classes">Classes</a></li>
                <li class="breadcrumb-item active">
                    <?php echo $data['mode'] === 'ajout' ? 'Nouvelle' : 'Modification'; ?>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Messages d'alerte -->
    <?php if (isset($_SESSION['message_succes'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['message_succes']; unset($_SESSION['message_succes']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['message_erreur'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['message_erreur']; unset($_SESSION['message_erreur']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-school me-2"></i>
                        Informations de la classe
                    </h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo url($data['mode'] === 'ajout' ? 'academique/classes/ajouter' : 'academique/classes/modifier/' . ($data['valeurs']['id'] ?? '')); ?>" id="formClasse">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="row">
                            <!-- Nom de la classe -->
                            <div class="col-md-6 mb-3">
                                <label for="nom_classe" class="form-label">
                                    Nom de la classe <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php echo isset($data['erreurs']['nom_classe']) ? 'is-invalid' : ''; ?>"
                                        id="nom_classe" name="nom_classe"
                                        value="<?php echo htmlspecialchars($data['valeurs']['nom_classe'] ?? ''); ?>"
                                        placeholder="Ex: 6ème A, Terminale S1..."
                                        required>
                                <?php if (isset($data['erreurs']['nom_classe'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['nom_classe']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Niveau -->
                            <div class="col-md-6 mb-3">
                                <label for="id_niveau" class="form-label">
                                    Niveau <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($data['erreurs']['niveau_id']) ? 'is-invalid' : ''; ?>"
                                        id="id_niveau" name="id_niveau" required>
                                    <option value="">Sélectionner un niveau</option>
                                    <?php foreach ($data['niveaux'] as $niveau): ?>
                                        <option value="<?php echo $niveau['niveau_id']; ?>"
                                                <?php echo (isset($data['valeurs']['niveau_id']) && $data['valeurs']['niveau_id'] == $niveau['niveau_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($niveau['nom_niveau']); ?>
                                               
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($data['erreurs']['niveau_id'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['niveau_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Section -->
                            <div class="col-md-6 mb-3">
                                <label for="section_id" class="form-label">
                                    Section <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($data['erreurs']['section_id']) ? 'is-invalid' : ''; ?>"
                                        id="section_id" name="section_id" required>
                                    <option value="">Sélectionner une section</option>
                                    <?php foreach ($data['sections'] as $section): ?>
                                        <option value="<?php echo $section['section_id']; ?>"
                                                <?php echo (isset($data['valeurs']['section_id']) && $data['valeurs']['section_id'] == $section['section_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($section['nom_section']); ?>
                                               
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($data['erreurs']['section_id'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['section_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Année scolaire -->
                            <div class="col-md-6 mb-3">
                                <label for="annee_id" class="form-label">
                                    Année scolaire <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php echo isset($data['erreurs']['annee_id']) ? 'is-invalid' : ''; ?>"
                                        id="annee_id" name="annee_id" required>
                                    <option value="">Sélectionner une année</option>
                                    <?php foreach ($data['annees_scolaires'] as $annee): ?>
                                        <option value="<?php echo $annee['annee_id']; ?>"
                                                <?php echo (isset($data['valeurs']['annee_id']) && $data['valeurs']['annee_id'] == $annee['annee_id']) ? 'selected' : ''; ?>>
                                            <?php echo $annee['date_debut']; ?> - <?php echo $annee['date_fin']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($data['erreurs']['annee_id'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($data['erreurs']['annee_id']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Professeur principal -->
                            <div class="col-md-6 mb-3">
                                <label for="id_prof_principal" class="form-label">
                                    Professeur principal
                                </label>
                                <select class="form-select" id="id_prof_principal" name="id_prof_principal">
                                    <option value="">Sélectionner un professeur</option>
                                    <?php foreach ($data['professeurs'] as $professeur): ?>
                                        <option value="<?php echo $professeur['professeur_id']; ?>"
                                                <?php echo (isset($data['valeurs']['id_prof_principal']) && $data['valeurs']['id_prof_principal'] == $professeur['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($professeur['nom'] . ' ' . $professeur['prenom']); ?>
                                            <?php if ($professeur['specialite']): ?>
                                                (<?php echo htmlspecialchars($professeur['specialite']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Capacité maximale -->
                            <div class="col-md-6 mb-3">
                                <label for="capacite_max" class="form-label">
                                    Capacité maximale
                                </label>
                                <input type="number" class="form-control" id="capacite_max" name="capacite_max"
                                        value="<?php echo htmlspecialchars($data['valeurs']['capacite_max'] ?? ''); ?>"
                                        placeholder="Ex: 30" min="1" max="100">
                                <div class="form-text">Nombre maximum d'élèves dans cette classe</div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Description
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                        placeholder="Description optionnelle de la classe..."><?php echo htmlspecialchars($data['valeurs']['description'] ?? ''); ?></textarea>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo url('academique/classes'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour à la liste
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="reinitialiserFormulaire()">
                                    <i class="fas fa-undo me-2"></i>
                                    Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $data['mode'] === 'ajout' ? 'Créer la classe' : 'Modifier la classe'; ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar avec informations -->
        <div class="col-lg-4">
            <!-- Aide et conseils -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Conseils
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Nom de la classe</h6>
                    <p class="small text-muted mb-3">
                        Utilisez une nomenclature claire comme "6ème A", "Terminale S1", "CP B", etc.
                    </p>

                    <h6>Niveau et Section</h6>
                    <p class="small text-muted mb-3">
                        Le niveau détermine le cycle scolaire, la section l'orientation pédagogique.
                    </p>

                    <h6>Professeur principal</h6>
                    <p class="small text-muted mb-3">
                        Responsable pédagogique de la classe, peut être modifié ultérieurement.
                    </p>

                    <h6>Capacité</h6>
                    <p class="small text-muted">
                        Limite le nombre d'élèves pouvant être inscrits dans cette classe.
                    </p>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 text-primary"><?php echo count($data['niveaux']); ?></div>
                            <small class="text-muted">Niveaux</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success"><?php echo count($data['sections']); ?></div>
                            <small class="text-muted">Sections</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 text-warning"><?php echo count($data['annees_scolaires']); ?></div>
                            <small class="text-muted">Années</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-info"><?php echo count($data['professeurs']); ?></div>
                            <small class="text-muted">Professeurs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts spécifiques -->
<script>
// Fonction de réinitialisation du formulaire
function reinitialiserFormulaire() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ? Toutes les modifications non sauvegardées seront perdues.')) {
        document.getElementById('formClasse').reset();
        // Réinitialiser les classes d'erreur
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(element => {
            element.style.display = 'none';
        });
    }
}

// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formClasse');

    // Validation du nom de la classe
    document.getElementById('nom_classe').addEventListener('blur', function() {
        const nom = this.value.trim();
        if (nom.length < 2) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validation des sélections obligatoires
    ['id_niveau', 'section_id', 'annee_id'].forEach(function(fieldId) {
        document.getElementById(fieldId).addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            } else {
                this.classList.add('is-invalid');
            }
        });
    });

    // Validation de la capacité
    document.getElementById('capacite_max').addEventListener('input', function() {
        const capacite = parseInt(this.value);
        if (capacite < 1 || capacite > 100) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Soumission du formulaire avec validation
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Vérifier les champs obligatoires
        const requiredFields = ['nom_classe', 'id_niveau', 'section_id', 'annee_id'];
        requiredFields.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        // Vérifier la longueur du nom
        const nomClasse = document.getElementById('nom_classe');
        if (nomClasse.value.trim().length < 2) {
            nomClasse.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire avant de soumettre.');
        }
    });
});
</script>
