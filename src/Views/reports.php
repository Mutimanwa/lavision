<?php
/**
 * Rapports et statistiques
 * Vue principale pour l'accès aux différents rapports du système
 */

// Inclure l'en-tête
require_once __DIR__ . '/../templates/header.php';

// Récupérer les données du contexte
$stats_generales = $stats_generales ?? [];

// Récupérer les messages d'erreur ou de succès
$error_message = $_SESSION['error_message'] ?? '';
$success_message = $_SESSION['success_message'] ?? '';

// Nettoyer les messages de session
unset($_SESSION['error_message'], $_SESSION['success_message']);
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
                    <i class="fas fa-chart-bar me-2"></i>
                    Rapports et statistiques
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard">Tableau de bord</a></li>
                        <li class="breadcrumb-item active">Rapports</li>
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

            <!-- Statistiques générales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-users fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title"><?php echo $stats_generales['total_eleves'] ?? 0; ?></h5>
                            <p class="card-text">Total élèves</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                            </div>
                            <h5 class="card-title"><?php echo $stats_generales['total_professeurs'] ?? 0; ?></h5>
                            <p class="card-text">Professeurs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-school fa-2x text-info"></i>
                            </div>
                            <h5 class="card-title"><?php echo $stats_generales['total_classes'] ?? 0; ?></h5>
                            <p class="card-text">Classes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="fas fa-book fa-2x text-warning"></i>
                            </div>
                            <h5 class="card-title"><?php echo $stats_generales['total_matieres'] ?? 0; ?></h5>
                            <p class="card-text">Matières</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catégories de rapports -->
            <div class="row">
                <!-- Rapports académiques -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-graduation-cap me-2"></i>
                                Rapports académiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="<?php echo BASE_URL; ?>/reports/academiques/eleves" class="list-group-item list-group-item-action">
                                    <i class="fas fa-users me-2"></i>
                                    Liste des élèves
                                    <small class="text-muted d-block">Répartition par classe, niveau, section</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/academiques/notes" class="list-group-item list-group-item-action">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Bulletin de notes
                                    <small class="text-muted d-block">Moyennes, classements, statistiques</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/academiques/presence" class="list-group-item list-group-item-action">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Rapport de présence
                                    <small class="text-muted d-block">Taux d'assiduité, absences justifiées</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/academiques/emploi-temps" class="list-group-item list-group-item-action">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Emploi du temps
                                    <small class="text-muted d-block">Planning des cours par classe</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rapports administratifs -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i>
                                Rapports administratifs
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="<?php echo BASE_URL; ?>/reports/administration/utilisateurs" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Gestion des utilisateurs
                                    <small class="text-muted d-block">Rôles, permissions, activité</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/administration/logs" class="list-group-item list-group-item-action">
                                    <i class="fas fa-history me-2"></i>
                                    Journal d'activité
                                    <small class="text-muted d-block">Actions système, erreurs, sécurité</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/administration/systeme" class="list-group-item list-group-item-action">
                                    <i class="fas fa-server me-2"></i>
                                    État du système
                                    <small class="text-muted d-block">Performance, utilisation, sauvegardes</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/administration/annee-scolaire" class="list-group-item list-group-item-action">
                                    <i class="fas fa-calendar me-2"></i>
                                    Année scolaire
                                    <small class="text-muted d-block">Statistiques annuelles, progression</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rapports financiers -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Rapports financiers
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="<?php echo BASE_URL; ?>/reports/finance/paiements" class="list-group-item list-group-item-action">
                                    <i class="fas fa-credit-card me-2"></i>
                                    État des paiements
                                    <small class="text-muted d-block">Frais de scolarité, impayés, échéanciers</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/finance/recettes" class="list-group-item list-group-item-action">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Recettes et dépenses
                                    <small class="text-muted d-block">Bilan financier, prévisions budgétaires</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/finance/bourses" class="list-group-item list-group-item-action">
                                    <i class="fas fa-hand-holding-usd me-2"></i>
                                    Bourses et aides
                                    <small class="text-muted d-block">Attribution, suivi des bénéficiaires</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/finance/statistiques" class="list-group-item list-group-item-action">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Statistiques financières
                                    <small class="text-muted d-block">Tendances, comparaisons, analyses</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rapports personnalisés -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-wrench me-2"></i>
                                Rapports personnalisés
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="<?php echo BASE_URL; ?>/reports/custom/eleve" class="list-group-item list-group-item-action">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    Fiche élève détaillée
                                    <small class="text-muted d-block">Historique complet d'un élève</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/custom/classe" class="list-group-item list-group-item-action">
                                    <i class="fas fa-users-cog me-2"></i>
                                    Rapport de classe
                                    <small class="text-muted d-block">Performance, discipline, participation</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/custom/professeur" class="list-group-item list-group-item-action">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>
                                    Évaluation professeur
                                    <small class="text-muted d-block">Charge de travail, résultats obtenus</small>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/reports/custom/export" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-export me-2"></i>
                                    Export de données
                                    <small class="text-muted d-block">Extraction personnalisée (PDF, Excel, CSV)</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outils de génération rapide -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Génération rapide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="quick_report_type" class="form-label">Type de rapport</label>
                            <select class="form-select" id="quick_report_type">
                                <option value="">Sélectionner un type</option>
                                <option value="eleves_liste">Liste des élèves</option>
                                <option value="notes_bulletin">Bulletin de notes</option>
                                <option value="presence_mensuel">Rapport de présence mensuel</option>
                                <option value="paiements_etat">État des paiements</option>
                                <option value="systeme_logs">Logs système</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="quick_date_debut" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="quick_date_debut">
                        </div>
                        <div class="col-md-3">
                            <label for="quick_date_fin" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="quick_date_fin">
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" onclick="generateQuickReport()">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    Générer PDF
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="generateQuickReport('excel')">
                                    <i class="fas fa-file-excel me-2"></i>
                                    Générer Excel
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="previewReport()">
                                    <i class="fas fa-eye me-2"></i>
                                    Aperçu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.list-group-item {
    border: none;
    padding: 1rem;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.list-group-item i {
    width: 20px;
}

.list-group-item small {
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn {
    border-radius: 0.375rem;
}

.h-100 {
    height: 100%;
}
</style>

<script>
$(document).ready(function() {
    // Animation des messages d'alerte
    $('.alert').hide().fadeIn(500);

    // Initialiser les dates par défaut
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    $('#quick_date_debut').val(firstDay.toISOString().split('T')[0]);
    $('#quick_date_fin').val(lastDay.toISOString().split('T')[0]);
});

// Fonction pour générer un rapport rapide
function generateQuickReport(format = 'pdf') {
    const reportType = $('#quick_report_type').val();
    const dateDebut = $('#quick_date_debut').val();
    const dateFin = $('#quick_date_fin').val();

    if (!reportType) {
        alert('Veuillez sélectionner un type de rapport.');
        return;
    }

    // Construire l'URL du rapport
    let url = '<?php echo BASE_URL; ?>/reports/quick/' + reportType + '?format=' + format;
    if (dateDebut) url += '&date_debut=' + dateDebut;
    if (dateFin) url += '&date_fin=' + dateFin;

    // Ouvrir dans un nouvel onglet
    window.open(url, '_blank');
}

// Fonction pour prévisualiser un rapport
function previewReport() {
    const reportType = $('#quick_report_type').val();
    const dateDebut = $('#quick_date_debut').val();
    const dateFin = $('#quick_date_fin').val();

    if (!reportType) {
        alert('Veuillez sélectionner un type de rapport.');
        return;
    }

    // Construire l'URL d'aperçu
    let url = '<?php echo BASE_URL; ?>/reports/preview/' + reportType;
    if (dateDebut) url += '?date_debut=' + dateDebut;
    if (dateFin) url += '&date_fin=' + dateFin;

    // Ouvrir dans un nouvel onglet
    window.open(url, '_blank');
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>