<?php
/**
 * Vue - Détails d'un élève
 * Affiche toutes les informations détaillées d'un élève
 * Version: 2.0.0
 * Date: 20 janvier 2026
 */

// Récupération des données passées par le contrôleur
$data = $data ?? [];
$eleve = $data['eleve'] ?? [];
$notes = $data['notes'] ?? [];
$presences = $data['presences'] ?? [];
$paiements = $data['paiements'] ?? [];

// Messages de succès/erreur
$message_succes = $_SESSION['message_succes'] ?? null;
$message_erreur = $_SESSION['message_erreur'] ?? null;
unset($_SESSION['message_succes'], $_SESSION['message_erreur']);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Messages -->
            <?php if ($message_succes): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($message_succes) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($message_erreur): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($message_erreur) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- En-tête avec informations principales -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-auto d-none d-sm-block">
                            <img src="<?= IMAGES_URL ?>team/avatar.png"
                                 alt="Photo de l'élève"
                                 class="border border-3 border-white rounded-circle"
                                 width="80px" height="80px">
                        </div>
                        <div class="col">
                            <h4 class="mb-1 text-white">
                                <?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['post_nom'] . ' ' . $eleve['prenom']) ?>
                            </h4>
                            <p class="mb-1">
                                <i class="fas fa-graduation-cap me-2"></i>
                                <?= htmlspecialchars($eleve['nom_classe'] ?? 'Classe non assignée') ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-id-card me-2"></i>
                                ID Élève: <?= htmlspecialchars($eleve['id']) ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= url('eleves/modifier', ['id' => $eleve['id']]) ?>">
                                            <i class="fas fa-edit me-2"></i>Modifier les informations
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="imprimerFicheEleve()">
                                            <i class="fas fa-print me-2"></i>Imprimer la fiche
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="telechargerBulletin()">
                                            <i class="fas fa-file-pdf me-2"></i>Télécharger le bulletin
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#"
                                           onclick="confirmerDesinscription(<?= $eleve['id'] ?>, '<?= htmlspecialchars(addslashes($eleve['nom'] . ' ' . $eleve['prenom'])) ?>')">
                                            <i class="fas fa-user-times me-2"></i>Désinscrire
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="card-body bg-light">
                    <div class="row text-center">
                        <div class="col-md-3 border-end">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="fas fa-calendar-alt text-primary me-2 fs-4"></span>
                                <div>
                                    <p class="mb-0 fw-bold">Date d'inscription</p>
                                    <p class="mb-0 text-muted">
                                        <?= $eleve['date_inscription'] ? date('d/m/Y', strtotime($eleve['date_inscription'])) : 'Non définie' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 border-end">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="fas fa-birthday-cake text-success me-2 fs-4"></span>
                                <div>
                                    <p class="mb-0 fw-bold">Date de naissance</p>
                                    <p class="mb-0 text-muted">
                                        <?= $eleve['date_naissance'] ? date('d/m/Y', strtotime($eleve['date_naissance'])) . ' (' . date('Y') - date('Y', strtotime($eleve['date_naissance'])) . ' ans)' : 'Non définie' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 border-end">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="fas fa-chart-line text-warning me-2 fs-4"></span>
                                <div>
                                    <p class="mb-0 fw-bold">Moyenne générale</p>
                                    <p class="mb-0 text-muted">
                                        <?php
                                        if (!empty($notes)) {
                                            $moyenne = array_sum(array_column($notes, 'note')) / count($notes);
                                            echo number_format($moyenne, 2) . '/20';
                                        } else {
                                            echo 'Non disponible';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="fas fa-clock text-info me-2 fs-4"></span>
                                <div>
                                    <p class="mb-0 fw-bold">Taux de présence</p>
                                    <p class="mb-0 text-muted">
                                        <?php
                                        if (!empty($presences)) {
                                            $total_presences = count($presences);
                                            $presences_present = count(array_filter($presences, fn($p) => $p['statut'] === 'present'));
                                            $taux = $total_presences > 0 ? ($presences_present / $total_presences) * 100 : 0;
                                            echo number_format($taux, 1) . '%';
                                        } else {
                                            echo 'Non disponible';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations détaillées -->
            <div class="row">
                <!-- Informations personnelles -->
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">Informations Personnelles</h5>
                                </div>
                                <div class="col-auto">
                                    <a href="<?= url('eleves/modifier', ['id' => $eleve['id']]) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nom complet</label>
                                        <p class="mb-0">
                                            <?= htmlspecialchars($eleve['nom'] . ' ' . $eleve['post_nom'] . ' ' . $eleve['prenom']) ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Genre</label>
                                        <p class="mb-0">
                                            <span class="badge bg-<?= $eleve['genre'] === 'M' ? 'primary' : 'success' ?>">
                                                <?= $eleve['genre'] === 'M' ? 'Masculin' : 'Féminin' ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date de naissance</label>
                                        <p class="mb-0">
                                            <?= $eleve['date_naissance'] ? date('d F Y', strtotime($eleve['date_naissance'])) : 'Non définie' ?>
                                            <?php if ($eleve['date_naissance']): ?>
                                                <small class="text-muted">(<?= date('Y') - date('Y', strtotime($eleve['date_naissance'])) ?> ans)</small>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Lieu de naissance</label>
                                        <p class="mb-0">
                                            <?= htmlspecialchars($eleve['lieu_naissance'] ?: 'Non défini') ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Téléphone</label>
                                        <p class="mb-0">
                                            <?= htmlspecialchars($eleve['telephone'] ?: 'Non défini') ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email</label>
                                        <p class="mb-0">
                                            <?= htmlspecialchars($eleve['email'] ?: 'Non défini') ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Adresse</label>
                                        <p class="mb-0">
                                            <?= htmlspecialchars($eleve['adresse'] ?: 'Non définie') ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Groupe sanguin</label>
                                        <p class="mb-0">
                                            <?= htmlspecialchars($eleve['groupe_sanguin'] ?: 'Non défini') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations académiques -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Informations Académiques</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Classe actuelle</label>
                                        <p class="mb-0">
                                            <?php if ($eleve['nom_classe']): ?>
                                                <span class="badge bg-info fs-6">
                                                    <?= htmlspecialchars($eleve['nom_classe']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Non assigné</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Statut</label>
                                        <p class="mb-0">
                                            <?php
                                            $statut_classes = [
                                                'actif' => 'success',
                                                'en_attente' => 'warning',
                                                'suspendu' => 'danger',
                                                'desiste' => 'secondary'
                                            ];
                                            $statut_labels = [
                                                'actif' => 'Actif',
                                                'en_attente' => 'En attente',
                                                'suspendu' => 'Suspendu',
                                                'desiste' => 'Désisté'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $statut_classes[$eleve['statut']] ?? 'light' ?>">
                                                <?= $statut_labels[$eleve['statut']] ?? htmlspecialchars($eleve['statut']) ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Date d'inscription</label>
                                        <p class="mb-0">
                                            <?= $eleve['date_inscription'] ? date('d/m/Y', strtotime($eleve['date_inscription'])) : 'Non définie' ?>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Année scolaire</label>
                                        <p class="mb-0">
                                            <?= date('Y') . '-' . (date('Y') + 1) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes récentes -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="mb-0">Notes Récentes</h5>
                                </div>
                                <div class="col-auto">
                                    <a href="<?= url('cours/notes', ['eleve' => $eleve['id']]) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Voir tout
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($notes)): ?>
                                <p class="text-muted text-center mb-0">Aucune note disponible</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Matière</th>
                                                <th>Note</th>
                                                <th>Coefficient</th>
                                                <th>Date</th>
                                                <th>Professeur</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $notes_recents = array_slice($notes, 0, 5);
                                            foreach ($notes_recents as $note):
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($note['nom_matiere']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $note['note'] >= 10 ? 'success' : 'danger' ?>">
                                                            <?= number_format($note['note'], 2) ?>/20
                                                        </span>
                                                    </td>
                                                    <td><?= $note['coefficient'] ?></td>
                                                    <td><?= date('d/m/Y', strtotime($note['date_evaluation'])) ?></td>
                                                    <td><?= htmlspecialchars($note['nom_professeur'] ?: 'N/A') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Informations parentales et financières -->
                <div class="col-lg-4">
                    <!-- Informations parentales -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Informations Parentales</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($eleve['id_parent'])): ?>
                                <p class="text-muted text-center mb-0">Aucun parent défini</p>
                                <a href="<?= url('eleves/parents') ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Ajouter un parent
                                </a>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nom du parent</label>
                                    <p class="mb-0">
                                        <?= htmlspecialchars($eleve['nom_parent'] ?: 'Non défini') ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Téléphone</label>
                                    <p class="mb-0">
                                        <?= htmlspecialchars($eleve['tel_parent'] ?: 'Non défini') ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <p class="mb-0">
                                        <?= htmlspecialchars($eleve['email_parent'] ?: 'Non défini') ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Situation financière -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Situation Financière</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($paiements)): ?>
                                <p class="text-muted text-center mb-0">Aucun paiement enregistré</p>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Dernier paiement</label>
                                    <p class="mb-0">
                                        <?php
                                        $dernier_paiement = $paiements[0];
                                        echo date('d/m/Y', strtotime($dernier_paiement['date_paiement']));
                                        ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Montant</label>
                                    <p class="mb-0">
                                        <?= number_format($dernier_paiement['montant'], 2) ?> CDF
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Statut</label>
                                    <p class="mb-0">
                                        <span class="badge bg-success">Payé</span>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <a href="<?= url('finance/paiements', ['eleve' => $eleve['id']]) ?>" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-eye me-1"></i>Voir les paiements
                            </a>
                        </div>
                    </div>

                    <!-- Présences récentes -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Présences Récentes</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($presences)): ?>
                                <p class="text-muted text-center mb-0">Aucune présence enregistrée</p>
                            <?php else: ?>
                                <div class="mb-3">
                                    <?php
                                    $presences_recents = array_slice($presences, 0, 10);
                                    $present_count = count(array_filter($presences_recents, fn($p) => $p['statut'] === 'present'));
                                    $taux = count($presences_recents) > 0 ? ($present_count / count($presences_recents)) * 100 : 0;
                                    ?>
                                    <div class="text-center">
                                        <h4 class="text-<?= $taux >= 80 ? 'success' : ($taux >= 60 ? 'warning' : 'danger') ?>">
                                            <?= number_format($taux, 1) ?>%
                                        </h4>
                                        <small class="text-muted">Taux de présence (10 derniers jours)</small>
                                    </div>
                                </div>
                                <div class="small">
                                    <?php foreach ($presences_recents as $presence): ?>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><?= date('d/m', strtotime($presence['date_cours'])) ?></span>
                                            <span class="badge bg-<?= $presence['statut'] === 'present' ? 'success' : 'danger' ?> badge-sm">
                                                <?= $presence['statut'] === 'present' ? 'P' : 'A' ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de désinscription -->
<div class="modal fade" id="modalDesinscription" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la désinscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('eleves') ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="id_eleve" id="idEleveDesinscription">

                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir désinscrire <strong id="nomEleveDesinscription"></strong> ?</p>
                    <p class="text-muted">Cette action est irréversible et l'élève sera marqué comme "désisté".</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer la désinscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fonction pour confirmer la désinscription
function confirmerDesinscription(idEleve, nomEleve) {
    document.getElementById('idEleveDesinscription').value = idEleve;
    document.getElementById('nomEleveDesinscription').textContent = nomEleve;
    new bootstrap.Modal(document.getElementById('modalDesinscription')).show();
}

// Fonction pour imprimer la fiche élève
function imprimerFicheEleve() {
    window.print();
}

// Fonction pour télécharger le bulletin (simulation)
function telechargerBulletin() {
    alert('Fonctionnalité de téléchargement du bulletin en cours de développement');
}
</script>