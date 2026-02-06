<?php
/**
 * Gestion des paramètres système
 * Vue d'administration pour la configuration de l'application
 */

// Récupérer les données du contexte
$parametres = $parametres ?? [];

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);
?>


<!-- Contenu principal -->
<div class="row">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-cogs me-2"></i>
            Paramètres système
        </h1>
       
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

    <!-- Formulaire des paramètres -->
    <form method="POST" action="<?php echo BASE_URL; ?>/administration/parametres" id="parametresForm">
        <!-- Jeton CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <?php foreach ($parametres as $categorie => $params): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder me-2"></i>
                        <?php echo htmlspecialchars(ucfirst($categorie)); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($params as $param): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="param_<?php echo $param['parametre_id']; ?>" class="form-label">
                                        <?php echo htmlspecialchars($param['description'] ?: $param['cle']); ?>
                                        <?php if (!$param['modifiable']): ?>
                                            <i class="fas fa-lock text-muted ms-1" title="Paramètre non modifiable"></i>
                                        <?php endif; ?>
                                    </label>

                                    <?php if ($param['type'] === 'boolean'): ?>
                                        <!-- Case à cocher pour les booléens -->
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                    type="checkbox"
                                                    id="param_<?php echo $param['parametre_id']; ?>"
                                                    name="parametres[<?php echo $param['cle']; ?>]"
                                                    value="1"
                                                    <?php echo $param['valeur'] ? 'checked' : ''; ?>
                                                    <?php echo !$param['modifiable'] ? 'disabled' : ''; ?>>
                                            <label class="form-check-label" for="param_<?php echo $param['parametre_id']; ?>">
                                                <?php echo $param['valeur'] ? 'Activé' : 'Désactivé'; ?>
                                            </label>
                                        </div>

                                    <?php elseif ($param['type'] === 'json' || $param['type'] === 'array'): ?>
                                        <!-- Zone de texte pour JSON/Array -->
                                        <textarea class="form-control"
                                                    id="param_<?php echo $param['parametre_id']; ?>"
                                                    name="parametres[<?php echo $param['cle']; ?>]"
                                                    rows="3"
                                                    <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>
                                                    placeholder='<?php echo $param['type'] === 'json' ? '{"clé": "valeur"}' : '["valeur1", "valeur2"]'; ?>'><?php echo htmlspecialchars($param['valeur']); ?></textarea>

                                    <?php elseif (strpos($param['cle'], 'mot_de_passe') !== false || strpos($param['cle'], 'password') !== false): ?>
                                        <!-- Champ mot de passe -->
                                        <input type="password"
                                                class="form-control"
                                                id="param_<?php echo $param['parametre_id']; ?>"
                                                name="parametres[<?php echo $param['cle']; ?>]"
                                                value="<?php echo htmlspecialchars($param['valeur']); ?>"
                                                <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>>

                                    <?php elseif (strpos($param['cle'], 'email') !== false): ?>
                                        <!-- Champ email -->
                                        <input type="email"
                                                class="form-control"
                                                id="param_<?php echo $param['parametre_id']; ?>"
                                                name="parametres[<?php echo $param['cle']; ?>]"
                                                value="<?php echo htmlspecialchars($param['valeur']); ?>"
                                                <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>>

                                    <?php elseif (strpos($param['cle'], 'telephone') !== false || strpos($param['cle'], 'phone') !== false): ?>
                                        <!-- Champ téléphone -->
                                        <input type="tel"
                                                class="form-control"
                                                id="param_<?php echo $param['parametre_id']; ?>"
                                                name="parametres[<?php echo $param['cle']; ?>]"
                                                value="<?php echo htmlspecialchars($param['valeur']); ?>"
                                                <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>>

                                    <?php elseif (strpos($param['cle'], 'url') !== false): ?>
                                        <!-- Champ URL -->
                                        <input type="url"
                                                class="form-control"
                                                id="param_<?php echo $param['parametre_id']; ?>"
                                                name="parametres[<?php echo $param['cle']; ?>]"
                                                value="<?php echo htmlspecialchars($param['valeur']); ?>"
                                                <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>>

                                    <?php elseif ($param['type'] === 'integer' || is_numeric($param['valeur'])): ?>
                                        <!-- Champ numérique -->
                                        <input type="number"
                                                class="form-control"
                                                id="param_<?php echo $param['parametre_id']; ?>"
                                                name="parametres[<?php echo $param['cle']; ?>]"
                                                value="<?php echo htmlspecialchars($param['valeur']); ?>"
                                                <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>>

                                    <?php elseif (strlen($param['valeur']) > 100): ?>
                                        <!-- Zone de texte pour valeurs longues -->
                                        <textarea class="form-control"
                                                    id="param_<?php echo $param['parametre_id']; ?>"
                                                    name="parametres[<?php echo $param['cle']; ?>]"
                                                    rows="3"
                                                    <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>><?php echo htmlspecialchars($param['valeur']); ?></textarea>

                                    <?php else: ?>
                                        <!-- Champ texte standard -->
                                        <input type="text"
                                                class="form-control"
                                                id="param_<?php echo $param['parametre_id']; ?>"
                                                name="parametres[<?php echo $param['cle']; ?>]"
                                                value="<?php echo htmlspecialchars($param['valeur']); ?>"
                                                <?php echo !$param['modifiable'] ? 'readonly' : ''; ?>>
                                    <?php endif; ?>

                                    <small class="form-text text-muted">
                                        Clé: <code><?php echo htmlspecialchars($param['cle']); ?></code>
                                        | Type: <?php echo htmlspecialchars($param['type']); ?>
                                        | Modifiable: <?php echo $param['modifiable'] ? 'Oui' : 'Non'; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Boutons d'action -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="<?php echo BASE_URL; ?>/administration" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour
                    </a>
                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" onclick="resetToDefaults()">
                            <i class="fas fa-undo me-1"></i>
                            Valeurs par défaut
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSave">
                            <i class="fas fa-save me-2"></i>
                            Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Validation du formulaire
    $('#parametresForm').submit(function(e) {
        let isValid = true;

        // Validation des emails
        $('input[type="email"]').each(function() {
            const email = $(this).val().trim();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validation des URLs
        $('input[type="url"]').each(function() {
            const url = $(this).val().trim();
            if (url && !/^https?:\/\/.+/.test(url)) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validation des JSON
        $('textarea').each(function() {
            const value = $(this).val().trim();
            const paramId = $(this).attr('id').replace('param_', '');
            const paramType = $('#param_' + paramId).closest('.form-group').find('small code').text();

            if (paramType.includes('json') && value) {
                try {
                    JSON.parse(value);
                    $(this).removeClass('is-invalid');
                } catch (e) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
            return false;
        }

        // Désactiver le bouton pendant la soumission
        $('#btnSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');
    });

    // Mise à jour des labels des switches booléens
    $('.form-check-input[type="checkbox"]').change(function() {
        const label = $(this).next('.form-check-label');
        const isChecked = $(this).is(':checked');
        label.text(isChecked ? 'Activé' : 'Désactivé');
    });

    // Formatage automatique des JSON
    $('textarea').on('blur', function() {
        const value = $(this).val().trim();
        const paramId = $(this).attr('id').replace('param_', '');
        const paramType = $('#param_' + paramId).closest('.form-group').find('small code').text();

        if (paramType.includes('json') && value) {
            try {
                const parsed = JSON.parse(value);
                $(this).val(JSON.stringify(parsed, null, 2));
            } catch (e) {
                // Ne rien faire si ce n'est pas du JSON valide
            }
        }
    });
});

// Fonction pour réinitialiser aux valeurs par défaut
function resetToDefaults() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres à leurs valeurs par défaut ?')) {
        // Ici nous pourrions charger les valeurs par défaut depuis le serveur
        alert('Fonctionnalité à implémenter : chargement des valeurs par défaut');
    }
}
</script>