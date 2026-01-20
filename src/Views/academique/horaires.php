<?php
/**
 * Vue de gestion des horaires
 * Interface pour gérer les emplois du temps des classes
 * Interface responsive avec Bootstrap 5 et drag & drop
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
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        Gestion des Horaires
                    </h1>
                    <p class="text-muted mt-1">Organisation des emplois du temps</p>
                </div>
                <div>
                    <button type="button" class="btn btn-success" onclick="sauvegarderHoraires()">
                        <i class="fas fa-save me-2"></i>
                        Sauvegarder
                    </button>
                </div>
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

            <!-- Sélecteurs de classe et jour -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="get" action="<?php echo BASE_URL; ?>/academique/horaires" class="row g-3">
                        <div class="col-md-5">
                            <label for="classe" class="form-label">
                                <i class="fas fa-school me-1"></i>
                                Classe
                            </label>
                            <select class="form-select" id="classe" name="classe" onchange="this.form.submit()">
                                <option value="">Sélectionner une classe</option>
                                <?php foreach ($data['classes'] as $classe): ?>
                                    <option value="<?php echo $classe['id']; ?>"
                                            <?php echo ($data['id_classe_selectionnee'] == $classe['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($classe['nom_classe']); ?> -
                                        <?php echo htmlspecialchars($classe['nom_niveau']); ?> <?php echo htmlspecialchars($classe['nom_section']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="jour" class="form-label">
                                <i class="fas fa-calendar-day me-1"></i>
                                Jour de la semaine
                            </label>
                            <select class="form-select" id="jour" name="jour" onchange="this.form.submit()">
                                <?php foreach ($data['jours_semaine'] as $numero => $jour): ?>
                                    <option value="<?php echo $numero; ?>"
                                            <?php echo ($data['jour_selectionne'] == $numero) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($jour); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mode_edition" name="mode_edition" value="1"
                                       <?php echo (isset($_GET['mode_edition']) && $_GET['mode_edition'] == '1') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="mode_edition">
                                    Mode édition
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($data['id_classe_selectionnee']): ?>
                <!-- Grille des horaires -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Emploi du temps -
                            <?php
                            $classeSelectionnee = array_filter($data['classes'], fn($c) => $c['id'] == $data['id_classe_selectionnee']);
                            $classeSelectionnee = reset($classeSelectionnee);
                            echo htmlspecialchars($classeSelectionnee['nom_classe']);
                            ?> -
                            <?php echo htmlspecialchars($data['jours_semaine'][$data['jour_selectionne']]); ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="formHoraires" method="post" action="<?php echo BASE_URL; ?>/academique/horaires/sauvegarder">
                            <input type="hidden" name="csrf_token" value="<?php echo generer_csrf_token(); ?>">
                            <input type="hidden" name="id_classe" value="<?php echo $data['id_classe_selectionnee']; ?>">
                            <input type="hidden" name="jour_semaine" value="<?php echo $data['jour_selectionne']; ?>">

                            <div class="table-responsive">
                                <table class="table table-bordered" id="tableHoraires">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="15%">Heure</th>
                                            <th width="25%">Matière</th>
                                            <th width="25%">Professeur</th>
                                            <th width="20%">Salle</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $heureCourante = '08:00';
                                        for ($i = 0; $i < 8; $i++): // 8 créneaux horaires par défaut
                                            $horaireExistant = null;
                                            foreach ($data['horaires'] as $horaire) {
                                                if ($horaire['heure_debut'] === $heureCourante) {
                                                    $horaireExistant = $horaire;
                                                    break;
                                                }
                                            }
                                        ?>
                                            <tr class="horaire-row" data-index="<?php echo $i; ?>">
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="time" class="form-control form-control-sm heure-debut"
                                                               name="horaires[<?php echo $i; ?>][heure_debut]"
                                                               value="<?php echo $horaireExistant ? $horaireExistant['heure_debut'] : $heureCourante; ?>">
                                                        <span class="input-group-text">-</span>
                                                        <input type="time" class="form-control form-control-sm heure-fin"
                                                               name="horaires[<?php echo $i; ?>][heure_fin]"
                                                               value="<?php echo $horaireExistant ? $horaireExistant['heure_fin'] : date('H:i', strtotime($heureCourante . ' +1 hour')); ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm matiere-select"
                                                            name="horaires[<?php echo $i; ?>][id_matiere]">
                                                        <option value="">-- Sélectionner une matière --</option>
                                                        <?php foreach ($data['matieres'] as $matiere): ?>
                                                            <option value="<?php echo $matiere['id']; ?>"
                                                                    <?php echo ($horaireExistant && $horaireExistant['id_matiere'] == $matiere['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($matiere['nom_matiere']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm professeur-select"
                                                            name="horaires[<?php echo $i; ?>][id_professeur]">
                                                        <option value="">-- Sélectionner un professeur --</option>
                                                        <?php foreach ($data['professeurs'] as $professeur): ?>
                                                            <option value="<?php echo $professeur['id']; ?>"
                                                                    <?php echo ($horaireExistant && $horaireExistant['id_professeur'] == $professeur['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($professeur['nom'] . ' ' . $professeur['prenoms']); ?>
                                                                <?php if ($professeur['specialite']): ?>
                                                                    (<?php echo htmlspecialchars($professeur['specialite']); ?>)
                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm salle-input"
                                                           name="horaires[<?php echo $i; ?>][salle]"
                                                           placeholder="Salle..."
                                                           value="<?php echo $horaireExistant ? htmlspecialchars($horaireExistant['salle'] ?? '') : ''; ?>">
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($horaireExistant): ?>
                                                            <input type="hidden" name="horaires[<?php echo $i; ?>][id]" value="<?php echo $horaireExistant['id']; ?>">
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-success btn-sm ajouter-cours"
                                                                title="Ajouter ce cours">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm supprimer-cours"
                                                                title="Supprimer ce cours">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $heureCourante = date('H:i', strtotime($heureCourante . ' +1 hour'));
                                        endfor;
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="ajouterCreneau()">
                                        <i class="fas fa-plus me-1"></i>
                                        Ajouter un créneau
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>
                                        Sauvegarder les horaires
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Aperçu de la semaine -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calendar-week me-2"></i>
                            Aperçu de la semaine
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Heure</th>
                                        <?php foreach ($data['jours_semaine'] as $numero => $jour): ?>
                                            <th class="<?php echo ($numero == $data['jour_selectionne']) ? 'table-primary' : ''; ?>">
                                                <?php echo htmlspecialchars($jour); ?>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($heure = 8; $heure <= 17; $heure++): ?>
                                        <tr>
                                            <td class="text-center fw-bold">
                                                <?php echo str_pad($heure, 2, '0', STR_PAD_LEFT); ?>:00
                                            </td>
                                            <?php foreach ($data['jours_semaine'] as $numero => $jour): ?>
                                                <td class="text-center">
                                                    <?php
                                                    // Simuler la récupération des horaires pour ce jour/heure
                                                    // En production, ceci serait chargé depuis la base de données
                                                    $horaireDuJour = array_filter($data['horaires'], function($h) use ($numero, $heure) {
                                                        $heureDebut = (int)explode(':', $h['heure_debut'])[0];
                                                        return $h['jour_semaine'] == $numero && $heureDebut == $heure;
                                                    });
                                                    $horaireDuJour = reset($horaireDuJour);

                                                    if ($horaireDuJour) {
                                                        echo '<small class="text-truncate d-block">';
                                                        echo htmlspecialchars(substr($horaireDuJour['nom_matiere'], 0, 15));
                                                        if (strlen($horaireDuJour['nom_matiere']) > 15) echo '...';
                                                        echo '</small>';
                                                        echo '<small class="text-muted d-block">';
                                                        echo htmlspecialchars($horaireDuJour['nom_professeur'] . ' ' . substr($horaireDuJour['prenoms_professeur'], 0, 1) . '.');
                                                        echo '</small>';
                                                    } else {
                                                        echo '<span class="text-muted">-</span>';
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Message de sélection -->
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Sélectionnez une classe</h5>
                        <p class="text-muted">Choisissez une classe dans le menu déroulant ci-dessus pour afficher et modifier son emploi du temps.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts spécifiques -->
<script>
// Fonction pour sauvegarder les horaires
function sauvegarderHoraires() {
    document.getElementById('formHoraires').submit();
}

// Fonction pour ajouter un créneau horaire
function ajouterCreneau() {
    const table = document.getElementById('tableHoraires').getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length;
    const newRow = table.insertRow(rowCount);

    // Générer le HTML pour une nouvelle ligne (simplifié)
    newRow.innerHTML = `
        <td>
            <div class="input-group input-group-sm">
                <input type="time" class="form-control form-control-sm heure-debut" name="horaires[${rowCount}][heure_debut]" value="08:00">
                <span class="input-group-text">-</span>
                <input type="time" class="form-control form-control-sm heure-fin" name="horaires[${rowCount}][heure_fin]" value="09:00">
            </div>
        </td>
        <td>
            <select class="form-select form-select-sm matiere-select" name="horaires[${rowCount}][id_matiere]">
                <option value="">-- Sélectionner une matière --</option>
                <?php foreach ($data['matieres'] as $matiere): ?>
                <option value="<?php echo $matiere['id']; ?>"><?php echo htmlspecialchars($matiere['nom_matiere']); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm professeur-select" name="horaires[${rowCount}][id_professeur]">
                <option value="">-- Sélectionner un professeur --</option>
                <?php foreach ($data['professeurs'] as $professeur): ?>
                <option value="<?php echo $professeur['id']; ?>"><?php echo htmlspecialchars($professeur['nom'] . ' ' . $professeur['prenoms']); ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm salle-input" name="horaires[${rowCount}][salle]" placeholder="Salle...">
        </td>
        <td>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success btn-sm ajouter-cours" title="Ajouter ce cours">
                    <i class="fas fa-plus"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm supprimer-cours" title="Supprimer ce cours">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    `;

    // Attacher les événements aux nouveaux éléments
    attacherEvenementsLigne(newRow);
}

// Fonction pour attacher les événements aux lignes
function attacherEvenementsLigne(row) {
    // Bouton supprimer
    const btnSupprimer = row.querySelector('.supprimer-cours');
    if (btnSupprimer) {
        btnSupprimer.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce créneau horaire ?')) {
                row.remove();
                renumeroterIndices();
            }
        });
    }

    // Bouton ajouter (pour marquer comme actif)
    const btnAjouter = row.querySelector('.ajouter-cours');
    if (btnAjouter) {
        btnAjouter.addEventListener('click', function() {
            row.classList.toggle('table-success');
            this.innerHTML = row.classList.contains('table-success') ?
                '<i class="fas fa-check"></i>' : '<i class="fas fa-plus"></i>';
        });
    }
}

// Fonction pour renuméroter les indices après suppression
function renumeroterIndices() {
    const rows = document.querySelectorAll('#tableHoraires tbody tr');
    rows.forEach((row, index) => {
        // Mettre à jour les attributs name des inputs
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            }
        });
    });
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Attacher les événements aux lignes existantes
    const rows = document.querySelectorAll('#tableHoraires tbody tr');
    rows.forEach(row => {
        attacherEvenementsLigne(row);
    });

    // Validation des heures
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('heure-debut') || e.target.classList.contains('heure-fin')) {
            const row = e.target.closest('tr');
            const heureDebut = row.querySelector('.heure-debut').value;
            const heureFin = row.querySelector('.heure-fin').value;

            if (heureDebut && heureFin && heureDebut >= heureFin) {
                alert('L\'heure de fin doit être postérieure à l\'heure de début.');
                e.target.value = '';
            }
        }
    });
});
</script>

<?php
// Inclusion du template de pied de page
require_once TEMPLATES_PATH . '/footer.php';
?>