<?php
/**
 * Formulaire d'ajout/modification d'une matière
 * Interface responsive avec validation Bootstrap 5
 * Version: 2.0.0 - Refactorisée pour scalabilité
 * Date: 20 janvier 2026
 */

// Inclusion du template d'en-tête
require_once TEMPLATES_PATH . '/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <?php require_once TEMPLATES_PATH . '/sidebar.php'; ?>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10 px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        <?php echo $data['mode'] === 'ajout' ? 'Nouvelle Matière' : 'Modifier la Matière'; ?>
                    </h1>
                    <p class="text-muted mt-1">
                        <?php echo $data['mode'] === 'ajout' ? 'Ajouter une nouvelle matière au système' : 'Modifier les informations de la matière'; ?>
                    </p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique">Académique</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/academique/matieres">Matières</a></li>
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
                                <i class="fas fa-book me-2"></i>
                                Informations de la matière
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?php echo BASE_URL; ?>/academique/matieres/<?php echo $data['mode'] === 'ajout' ? 'ajouter' : 'modifier/' . ($data['valeurs']['id'] ?? ''); ?>" id="formMatiere">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                                <div class="row">
                                    <!-- Nom de la matière -->
                                    <div class="col-md-6 mb-3">
                                        <label for="nom_matiere" class="form-label">
                                            Nom de la matière <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control <?php echo isset($data['erreurs']['nom_matiere']) ? 'is-invalid' : ''; ?>"
                                               id="nom_matiere" name="nom_matiere"
                                               value="<?php echo htmlspecialchars($data['valeurs']['nom_matiere'] ?? ''); ?>"
                                               placeholder="Ex: Mathématiques, Français, Histoire..."
                                               required>
                                        <?php if (isset($data['erreurs']['nom_matiere'])): ?>
                                            <div class="invalid-feedback">
                                                <?php echo htmlspecialchars($data['erreurs']['nom_matiere']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Code de la matière -->
                                    <div class="col-md-6 mb-3">
                                        <label for="code_matiere" class="form-label">
                                            Code de la matière <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control <?php echo isset($data['erreurs']['code_matiere']) ? 'is-invalid' : ''; ?>"
                                               id="code_matiere" name="code_matiere"
                                               value="<?php echo htmlspecialchars($data['valeurs']['code_matiere'] ?? ''); ?>"
                                               placeholder="Ex: MATH, FRAN, HIST..."
                                               required>
                                        <?php if (isset($data['erreurs']['code_matiere'])): ?>
                                            <div class="invalid-feedback">
                                                <?php echo htmlspecialchars($data['erreurs']['code_matiere']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="form-text">Code unique en majuscules, sans espaces</div>
                                    </div>

                                    <!-- Type de matière -->
                                    <div class="col-md-6 mb-3">
                                        <label for="type_matiere" class="form-label">
                                            Type de matière <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select <?php echo isset($data['erreurs']['type_matiere']) ? 'is-invalid' : ''; ?>"
                                                id="type_matiere" name="type_matiere" required>
                                            <option value="">Sélectionner un type</option>
                                            <?php foreach ($data['types_matieres'] as $key => $type): ?>
                                                <option value="<?php echo $key; ?>"
                                                        <?php echo (isset($data['valeurs']['type_matiere']) && $data['valeurs']['type_matiere'] == $key) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($type); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($data['erreurs']['type_matiere'])): ?>
                                            <div class="invalid-feedback">
                                                <?php echo htmlspecialchars($data['erreurs']['type_matiere']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Niveau -->
                                    <div class="col-md-6 mb-3">
                                        <label for="id_niveau" class="form-label">
                                            Niveau <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select <?php echo isset($data['erreurs']['id_niveau']) ? 'is-invalid' : ''; ?>"
                                                id="id_niveau" name="id_niveau" required>
                                            <option value="">Sélectionner un niveau</option>
                                            <?php foreach ($data['niveaux'] as $niveau): ?>
                                                <option value="<?php echo $niveau['id']; ?>"
                                                        <?php echo (isset($data['valeurs']['id_niveau']) && $data['valeurs']['id_niveau'] == $niveau['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($niveau['nom_niveau']); ?> (<?php echo htmlspecialchars($niveau['code_niveau']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($data['erreurs']['id_niveau'])): ?>
                                            <div class="invalid-feedback">
                                                <?php echo htmlspecialchars($data['erreurs']['id_niveau']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Coefficient -->
                                    <div class="col-md-6 mb-3">
                                        <label for="coefficient" class="form-label">
                                            Coefficient
                                        </label>
                                        <input type="number" class="form-control" id="coefficient" name="coefficient"
                                               value="<?php echo htmlspecialchars($data['valeurs']['coefficient'] ?? '1'); ?>"
                                               placeholder="1" min="0.5" max="10" step="0.5">
                                        <div class="form-text">Coefficient pour le calcul des moyennes (défaut: 1)</div>
                                    </div>

                                    <!-- Heures par semaine -->
                                    <div class="col-md-6 mb-3">
                                        <label for="heures_semaine" class="form-label">
                                            Heures par semaine
                                        </label>
                                        <input type="number" class="form-control" id="heures_semaine" name="heures_semaine"
                                               value="<?php echo htmlspecialchars($data['valeurs']['heures_semaine'] ?? ''); ?>"
                                               placeholder="Ex: 4" min="1" max="40">
                                        <div class="form-text">Nombre d'heures hebdomadaires (optionnel)</div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        Description
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                              placeholder="Description détaillée de la matière, objectifs, contenu..."><?php echo htmlspecialchars($data['valeurs']['description'] ?? ''); ?></textarea>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="<?php echo BASE_URL; ?>/academique/matieres" class="btn btn-outline-secondary">
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
                                            <?php echo $data['mode'] === 'ajout' ? 'Créer la matière' : 'Modifier la matière'; ?>
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
                            <h6>Nom et Code</h6>
                            <p class="small text-muted mb-3">
                                Le nom doit être descriptif, le code unique en majuscules (MATH, FRAN, PHILO...).
                            </p>

                            <h6>Type de matière</h6>
                            <p class="small text-muted mb-3">
                                <strong>Fondamentale:</strong> Matières obligatoires<br>
                                <strong>Optionnelle:</strong> Matières au choix<br>
                                <strong>Spécialisée:</strong> Matières de spécialité
                            </p>

                            <h6>Coefficient</h6>
                            <p class="small text-muted mb-3">
                                Pondération pour le calcul des moyennes. Les matières importantes ont généralement un coefficient plus élevé.
                            </p>

                            <h6>Heures/semaine</h6>
                            <p class="small text-muted">
                                Volume horaire hebdomadaire prévu pour cette matière.
                            </p>
                        </div>
                    </div>

                    <!-- Aperçu des types -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-tags me-2"></i>
                                Types de matières
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($data['types_matieres'] as $key => $type): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <?php
                                    $badgeClass = match($key) {
                                        'fondamentale' => 'bg-primary',
                                        'optionnelle' => 'bg-warning text-dark',
                                        'specialisee' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> me-2"><?php echo htmlspecialchars($type); ?></span>
                                    <small class="text-muted">
                                        <?php
                                        echo match($key) {
                                            'fondamentale' => 'Obligatoire',
                                            'optionnelle' => 'Au choix',
                                            'specialisee' => 'Spécialité',
                                            default => ''
                                        };
                                        ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Statistiques
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h4 mb-0 text-primary"><?php echo count($data['niveaux']); ?></div>
                                    <small class="text-white">Niveaux</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 mb-0 text-warning"><?php echo count($data['types_matieres']); ?></div>
                                    <small class="text-white">Types</small>
                                </div>
                            </div>
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
        document.getElementById('formMatiere').reset();
        // Réinitialiser les classes d'erreur
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(element => {
            element.style.display = 'none';
        });
    }
}

// Fonction pour générer automatiquement le code
function genererCode() {
    const nomMatiere = document.getElementById('nom_matiere').value.trim();
    if (nomMatiere) {
        // Prendre les 4 premières lettres en majuscules
        const code = nomMatiere.substring(0, 4).toUpperCase().replace(/[^A-Z]/g, '');
        document.getElementById('code_matiere').value = code;
    }
}

// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formMatiere');

    // Générer automatiquement le code depuis le nom
    document.getElementById('nom_matiere').addEventListener('input', function() {
        if (!document.getElementById('code_matiere').value) {
            genererCode();
        }
    });

    // Validation du nom de la matière
    document.getElementById('nom_matiere').addEventListener('blur', function() {
        const nom = this.value.trim();
        if (nom.length < 2) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validation du code de la matière
    document.getElementById('code_matiere').addEventListener('blur', function() {
        const code = this.value.trim();
        const regex = /^[A-Z]{2,10}$/;
        if (!regex.test(code)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validation des sélections obligatoires
    ['type_matiere', 'id_niveau'].forEach(function(fieldId) {
        document.getElementById(fieldId).addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            } else {
                this.classList.add('is-invalid');
            }
        });
    });

    // Validation du coefficient
    document.getElementById('coefficient').addEventListener('input', function() {
        const coefficient = parseFloat(this.value);
        if (coefficient < 0.5 || coefficient > 10) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validation des heures
    document.getElementById('heures_semaine').addEventListener('input', function() {
        const heures = parseInt(this.value);
        if (heures < 1 || heures > 40) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Soumission du formulaire avec validation
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Vérifier les champs obligatoires
        const requiredFields = ['nom_matiere', 'code_matiere', 'type_matiere', 'id_niveau'];
        requiredFields.forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        // Vérifier la longueur du nom
        const nomMatiere = document.getElementById('nom_matiere');
        if (nomMatiere.value.trim().length < 2) {
            nomMatiere.classList.add('is-invalid');
            isValid = false;
        }

        // Vérifier le format du code
        const codeMatiere = document.getElementById('code_matiere');
        const regex = /^[A-Z]{2,10}$/;
        if (!regex.test(codeMatiere.value.trim())) {
            codeMatiere.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire avant de soumettre.');
        }
    });
});
</script>

<?php
// Inclusion du template de pied de page
require_once TEMPLATES_PATH . '/footer.php';
?>