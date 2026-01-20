<?php
/**
 * Contrôleur du tableau de bord
 * LaVision - Système de gestion scolaire
 *
 * Ce contrôleur gère l'affichage du tableau de bord principal
 * avec les statistiques et informations importantes.
 */

class DashboardController extends BaseController {
    /**
     * Affiche le tableau de bord principal
     */
    public function index() {
        // Vérifier l'authentification
        $this->requireAuth();

        // Récupérer les données du tableau de bord
        $stats = $this->getDashboardStats();
        $recent_activities = $this->getRecentActivities();
        $upcoming_events = $this->getUpcomingEvents();

        // Rendre la vue du tableau de bord
        $this->render('dashboard/index', [
            'stats' => $stats,
            'recent_activities' => $recent_activities,
            'upcoming_events' => $upcoming_events,
            'page_title' => 'Tableau de bord'
        ]);
    }

    /**
     * Récupère les statistiques du tableau de bord
     */
    private function getDashboardStats() {
        try {
            // Statistiques des élèves
            $total_eleves = $this->db->query("SELECT COUNT(*) as total FROM eleves WHERE statut = 'actif'")->fetch()['total'] ?? 0;
            $eleves_nouveaux = $this->db->query("SELECT COUNT(*) as total FROM eleves WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch()['total'] ?? 0;

            // Statistiques du personnel
            $total_personnel = $this->db->query("SELECT COUNT(*) as total FROM personnel WHERE actif = 1")->fetch()['total'] ?? 0;

            // Statistiques financières
            $paiements_mois = $this->db->query("SELECT SUM(montant) as total FROM paiements WHERE DATE(date_paiement) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch()['total'] ?? 0;

            // Statistiques académiques
            $total_classes = $this->db->query("SELECT COUNT(*) as total FROM classes WHERE actif = 1")->fetch()['total'] ?? 0;

            return [
                'eleves' => [
                    'total' => $total_eleves,
                    'nouveaux' => $eleves_nouveaux
                ],
                'personnel' => [
                    'total' => $total_personnel
                ],
                'finances' => [
                    'paiements_mois' => $paiements_mois
                ],
                'academique' => [
                    'classes' => $total_classes
                ]
            ];
        } catch (Exception $e) {
            $this->logAction('Erreur récupération stats dashboard', $e->getMessage());
            return [
                'eleves' => ['total' => 0, 'nouveaux' => 0],
                'personnel' => ['total' => 0],
                'finances' => ['paiements_mois' => 0],
                'academique' => ['classes' => 0]
            ];
        }
    }

    /**
     * Récupère les activités récentes
     */
    private function getRecentActivities() {
        try {
            $query = "SELECT a.*, u.nom, u.prenom
                     FROM actions a
                     LEFT JOIN utilisateurs u ON a.id_utilisateur = u.id
                     ORDER BY a.created_at DESC
                     LIMIT 10";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logAction('Erreur récupération activités récentes', $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les événements à venir
     */
    private function getUpcomingEvents() {
        try {
            $query = "SELECT * FROM evenements
                     WHERE date_evenement >= CURDATE()
                     ORDER BY date_evenement ASC
                     LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logAction('Erreur récupération événements', $e->getMessage());
            return [];
        }
    }
}
